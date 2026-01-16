var PermissionEngine = (function() {
    'use strict';

    var permissionCache = new Map();
    var userIndexBySubAccount = new Map();
    var subAccountIndex = new Map();
    var cacheTimeout = 5 * 60 * 1000;
    var batchQueue = [];
    var batchTimeout = null;

    var API_ENDPOINTS = {
        getUser: '/api/v1/users/{userId}',
        getUsers: '/api/v1/users',
        getUsersBySubAccount: '/api/v1/sub-accounts/{subAccountId}/users',
        getPermissions: '/api/v1/users/{userId}/permissions',
        updatePermission: '/api/v1/users/{userId}/permissions/{permission}',
        checkPermission: '/api/v1/permissions/check',
        batchCheckPermissions: '/api/v1/permissions/batch-check',
        getHierarchy: '/api/v1/hierarchy',
        getSubAccounts: '/api/v1/sub-accounts'
    };

    function init(options) {
        options = options || {};
        
        if (options.preloadHierarchy) {
            preloadHierarchy();
        }

        return { initialized: true, cacheEnabled: true };
    }

    function buildUserIndex(users) {
        userIndexBySubAccount.clear();
        
        users.forEach(function(user) {
            var subAccountId = user.subAccountId || 'main';
            
            if (!userIndexBySubAccount.has(subAccountId)) {
                userIndexBySubAccount.set(subAccountId, new Map());
            }
            
            userIndexBySubAccount.get(subAccountId).set(user.id, user);
        });

        console.log('[PermissionEngine] Indexed', users.length, 'users across', userIndexBySubAccount.size, 'sub-accounts');
    }

    function buildSubAccountIndex(subAccounts) {
        subAccountIndex.clear();
        
        subAccounts.forEach(function(sa) {
            subAccountIndex.set(sa.id, sa);
        });
    }

    function getCacheKey(userId, permission) {
        return userId + ':' + permission;
    }

    function getCached(userId, permission) {
        var key = getCacheKey(userId, permission);
        var cached = permissionCache.get(key);
        
        if (!cached) return null;
        
        if (Date.now() - cached.timestamp > cacheTimeout) {
            permissionCache.delete(key);
            return null;
        }
        
        return cached.value;
    }

    function setCache(userId, permission, value) {
        var key = getCacheKey(userId, permission);
        permissionCache.set(key, {
            value: value,
            timestamp: Date.now()
        });
        
        if (permissionCache.size > 10000) {
            pruneCache();
        }
    }

    function pruneCache() {
        var now = Date.now();
        var toDelete = [];
        
        permissionCache.forEach(function(value, key) {
            if (now - value.timestamp > cacheTimeout) {
                toDelete.push(key);
            }
        });
        
        toDelete.forEach(function(key) {
            permissionCache.delete(key);
        });

        if (permissionCache.size > 8000) {
            var entries = Array.from(permissionCache.entries());
            entries.sort(function(a, b) { return a[1].timestamp - b[1].timestamp; });
            
            var removeCount = entries.length - 5000;
            for (var i = 0; i < removeCount; i++) {
                permissionCache.delete(entries[i][0]);
            }
        }
    }

    function invalidateUserCache(userId) {
        var toDelete = [];
        
        permissionCache.forEach(function(value, key) {
            if (key.startsWith(userId + ':')) {
                toDelete.push(key);
            }
        });
        
        toDelete.forEach(function(key) {
            permissionCache.delete(key);
        });
    }

    function invalidatePermissionCache(permission) {
        var toDelete = [];
        
        permissionCache.forEach(function(value, key) {
            if (key.endsWith(':' + permission)) {
                toDelete.push(key);
            }
        });
        
        toDelete.forEach(function(key) {
            permissionCache.delete(key);
        });
    }

    function checkPermission(userId, permission, context) {
        var cached = getCached(userId, permission);
        if (cached !== null) {
            return Promise.resolve(cached);
        }

        return apiCheckPermission(userId, permission, context).then(function(result) {
            setCache(userId, permission, result);
            return result;
        });
    }

    function checkPermissionSync(userId, permission, context) {
        var cached = getCached(userId, permission);
        if (cached !== null) {
            return cached;
        }

        var user = getUserFromIndex(userId);
        if (!user) {
            return { allowed: false, reason: 'User not found', cached: false };
        }

        var result = evaluatePermissionLocally(user, permission, context);
        setCache(userId, permission, result);
        
        return result;
    }

    function getUserFromIndex(userId) {
        var user = null;
        
        userIndexBySubAccount.forEach(function(users) {
            if (users.has(userId)) {
                user = users.get(userId);
            }
        });
        
        return user;
    }

    function evaluatePermissionLocally(user, permission, context) {
        context = context || {};
        
        var result = {
            allowed: false,
            userId: user.id,
            permission: permission,
            evaluatedAt: new Date().toISOString(),
            decisionPath: []
        };

        if (user.suspended) {
            result.allowed = false;
            result.decisionPath.push({ layer: 'USER_STATUS', blocked: true });
            return result;
        }

        var accountState = context.accountState || 'ACTIVE';
        if (accountState === 'SUSPENDED' || accountState === 'PENDING_ACTIVATION') {
            result.allowed = false;
            result.decisionPath.push({ layer: 'ACCOUNT_SCOPE', blocked: true, state: accountState });
            return result;
        }
        result.decisionPath.push({ layer: 'ACCOUNT_SCOPE', passed: true });

        var roleDefaults = getRoleDefaults(user.role);
        if (roleDefaults[permission] === false) {
            result.allowed = false;
            result.decisionPath.push({ layer: 'ROLE', denied: true, role: user.role });
            return result;
        }
        result.decisionPath.push({ layer: 'ROLE', passed: true });

        if (user.senderCapability === 'restricted') {
            var restrictedBlocks = ['send_sms', 'send_rcs', 'create_templates', 'upload_csv'];
            if (restrictedBlocks.includes(permission)) {
                result.allowed = false;
                result.decisionPath.push({ layer: 'SENDER_CAPABILITY', blocked: true });
                return result;
            }
        }
        result.decisionPath.push({ layer: 'SENDER_CAPABILITY', passed: true });

        if (user.permissionOverrides && user.permissionOverrides[permission] !== undefined) {
            result.allowed = user.permissionOverrides[permission];
            result.decisionPath.push({ layer: 'PERMISSION_TOGGLE', value: result.allowed });
            return result;
        }

        result.allowed = roleDefaults[permission] === true;
        result.decisionPath.push({ layer: 'ROLE_DEFAULT', value: result.allowed });
        
        return result;
    }

    function getRoleDefaults(role) {
        if (typeof PermissionManager !== 'undefined' && PermissionManager.getRoleDefaults) {
            return PermissionManager.getRoleDefaults(role) || {};
        }
        return {};
    }

    function batchCheckPermissions(requests) {
        var results = [];
        var uncached = [];
        
        requests.forEach(function(req, index) {
            var cached = getCached(req.userId, req.permission);
            if (cached !== null) {
                results[index] = cached;
            } else {
                uncached.push({ index: index, request: req });
            }
        });

        if (uncached.length === 0) {
            return Promise.resolve(results);
        }

        return apiBatchCheckPermissions(uncached.map(function(u) { return u.request; }))
            .then(function(apiResults) {
                apiResults.forEach(function(result, i) {
                    var originalIndex = uncached[i].index;
                    var req = uncached[i].request;
                    
                    setCache(req.userId, req.permission, result);
                    results[originalIndex] = result;
                });
                
                return results;
            });
    }

    function getUsersInSubAccount(subAccountId, options) {
        options = options || {};
        
        if (userIndexBySubAccount.has(subAccountId)) {
            var users = Array.from(userIndexBySubAccount.get(subAccountId).values());
            
            if (options.page && options.pageSize) {
                var start = (options.page - 1) * options.pageSize;
                return Promise.resolve({
                    users: users.slice(start, start + options.pageSize),
                    total: users.length,
                    page: options.page,
                    pageSize: options.pageSize
                });
            }
            
            return Promise.resolve({ users: users, total: users.length });
        }

        return apiGetUsersBySubAccount(subAccountId, options);
    }

    function getHierarchy(options) {
        options = options || {};

        if (userIndexBySubAccount.size > 0 && subAccountIndex.size > 0) {
            return Promise.resolve(buildHierarchyFromIndex(options));
        }

        return apiGetHierarchy(options);
    }

    function buildHierarchyFromIndex(options) {
        var hierarchy = {
            mainAccount: {
                id: 'main',
                name: 'Main Account',
                users: [],
                subAccounts: []
            },
            statistics: {
                totalSubAccounts: subAccountIndex.size,
                totalUsers: 0
            }
        };

        if (userIndexBySubAccount.has('main')) {
            hierarchy.mainAccount.users = Array.from(userIndexBySubAccount.get('main').values());
        }

        subAccountIndex.forEach(function(sa, id) {
            var saUsers = userIndexBySubAccount.has(id) 
                ? Array.from(userIndexBySubAccount.get(id).values()) 
                : [];
            
            hierarchy.mainAccount.subAccounts.push({
                id: id,
                name: sa.name,
                userCount: saUsers.length,
                users: options.includeUsers !== false ? saUsers : undefined
            });
            
            hierarchy.statistics.totalUsers += saUsers.length;
        });

        hierarchy.statistics.totalUsers += hierarchy.mainAccount.users.length;

        return hierarchy;
    }

    function preloadHierarchy() {
        return apiGetHierarchy({ includeUsers: true }).then(function(data) {
            var allUsers = [];
            var subAccounts = [];
            
            if (data.mainAccount) {
                if (data.mainAccount.users) {
                    data.mainAccount.users.forEach(function(u) {
                        u.subAccountId = 'main';
                        allUsers.push(u);
                    });
                }
                
                if (data.mainAccount.subAccounts) {
                    data.mainAccount.subAccounts.forEach(function(sa) {
                        subAccounts.push({ id: sa.id, name: sa.name });
                        
                        if (sa.users) {
                            sa.users.forEach(function(u) {
                                u.subAccountId = sa.id;
                                allUsers.push(u);
                            });
                        }
                    });
                }
            }
            
            buildUserIndex(allUsers);
            buildSubAccountIndex(subAccounts);
            
            return { usersLoaded: allUsers.length, subAccountsLoaded: subAccounts.length };
        });
    }

    function apiCheckPermission(userId, permission, context) {
        return new Promise(function(resolve) {
            setTimeout(function() {
                var user = getUserFromIndex(userId);
                if (user) {
                    resolve(evaluatePermissionLocally(user, permission, context));
                } else {
                    resolve({ allowed: false, reason: 'User not found' });
                }
            }, 5);
        });
    }

    function apiBatchCheckPermissions(requests) {
        return new Promise(function(resolve) {
            setTimeout(function() {
                var results = requests.map(function(req) {
                    var user = getUserFromIndex(req.userId);
                    if (user) {
                        return evaluatePermissionLocally(user, req.permission, req.context);
                    }
                    return { allowed: false, reason: 'User not found' };
                });
                resolve(results);
            }, 10);
        });
    }

    function apiGetUsersBySubAccount(subAccountId, options) {
        return new Promise(function(resolve) {
            setTimeout(function() {
                resolve({ users: [], total: 0 });
            }, 20);
        });
    }

    function apiGetHierarchy(options) {
        return new Promise(function(resolve) {
            setTimeout(function() {
                resolve({
                    mainAccount: { users: [], subAccounts: [] },
                    statistics: { totalSubAccounts: 0, totalUsers: 0 }
                });
            }, 50);
        });
    }

    function getStats() {
        return {
            cacheSize: permissionCache.size,
            indexedSubAccounts: userIndexBySubAccount.size,
            indexedUsers: Array.from(userIndexBySubAccount.values()).reduce(function(sum, map) {
                return sum + map.size;
            }, 0),
            cacheHitRate: 'N/A'
        };
    }

    function loadMockData(userCount, subAccountCount) {
        var users = [];
        var subAccounts = [];
        var roles = ['admin', 'messaging-manager', 'finance', 'developer', 'auditor'];
        
        for (var s = 1; s <= subAccountCount; s++) {
            subAccounts.push({ id: 'sub-' + String(s).padStart(3, '0'), name: 'Sub-Account ' + s });
        }
        
        for (var u = 1; u <= userCount; u++) {
            var subAccountId = 'sub-' + String((u % subAccountCount) + 1).padStart(3, '0');
            users.push({
                id: 'user-' + String(u).padStart(4, '0'),
                name: 'User ' + u,
                email: 'user' + u + '@example.com',
                role: roles[u % roles.length],
                senderCapability: u % 3 === 0 ? 'restricted' : 'advanced',
                subAccountId: subAccountId,
                suspended: false,
                permissionOverrides: {}
            });
        }
        
        buildUserIndex(users);
        buildSubAccountIndex(subAccounts);
        
        return { usersLoaded: users.length, subAccountsLoaded: subAccounts.length };
    }

    return {
        init: init,
        checkPermission: checkPermission,
        checkPermissionSync: checkPermissionSync,
        batchCheckPermissions: batchCheckPermissions,
        invalidateUserCache: invalidateUserCache,
        invalidatePermissionCache: invalidatePermissionCache,
        getUsersInSubAccount: getUsersInSubAccount,
        getHierarchy: getHierarchy,
        preloadHierarchy: preloadHierarchy,
        buildUserIndex: buildUserIndex,
        buildSubAccountIndex: buildSubAccountIndex,
        loadMockData: loadMockData,
        getStats: getStats,
        API_ENDPOINTS: API_ENDPOINTS
    };
})();

if (typeof module !== 'undefined' && module.exports) {
    module.exports = PermissionEngine;
}
