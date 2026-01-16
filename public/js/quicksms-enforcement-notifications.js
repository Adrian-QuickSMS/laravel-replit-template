(function() {
    'use strict';

    const EnforcementNotifications = {
        NOTIFICATION_TYPES: {
            SPEND_80_PERCENT: 'spend_80_percent',
            SPEND_100_PERCENT: 'spend_100_percent',
            ENFORCEMENT_TRIGGERED: 'enforcement_triggered',
            CAMPAIGN_BLOCKED: 'campaign_blocked'
        },

        SEVERITY: {
            WARNING: 'warning',
            CRITICAL: 'critical',
            BLOCKED: 'blocked'
        },

        notificationHistory: new Map(),
        observers: [],
        pendingNotifications: [],

        init: function() {
            this.loadNotificationHistory();
            this.setupNotificationUI();
            this.checkEnforcementStatus();
            console.log('[EnforcementNotifications] Initialized');
        },

        loadNotificationHistory: function() {
            try {
                const stored = localStorage.getItem('qsms_notification_history');
                if (stored) {
                    const data = JSON.parse(stored);
                    this.notificationHistory = new Map(Object.entries(data));
                }
            } catch (e) {
                console.warn('[EnforcementNotifications] Could not load history:', e);
            }
        },

        saveNotificationHistory: function() {
            try {
                const data = Object.fromEntries(this.notificationHistory);
                localStorage.setItem('qsms_notification_history', JSON.stringify(data));
            } catch (e) {
                console.warn('[EnforcementNotifications] Could not save history:', e);
            }
        },

        generateNotificationKey: function(type, subAccountId, date) {
            const dateKey = date || new Date().toISOString().split('T')[0];
            return `${type}_${subAccountId}_${dateKey}`;
        },

        isDuplicate: function(type, subAccountId) {
            const key = this.generateNotificationKey(type, subAccountId);
            return this.notificationHistory.has(key);
        },

        markAsSent: function(type, subAccountId) {
            const key = this.generateNotificationKey(type, subAccountId);
            this.notificationHistory.set(key, {
                sentAt: new Date().toISOString(),
                type: type,
                subAccountId: subAccountId
            });
            this.saveNotificationHistory();
        },

        getRecipients: function(subAccountId) {
            return {
                subAccountAdmins: [
                    { id: 'user-sa-001', email: 'admin@subaccount.com', name: 'Sub-Account Admin' }
                ],
                mainAccountAdmins: [
                    { id: 'user-ma-001', email: 'admin@mainaccount.com', name: 'Main Account Admin' },
                    { id: 'user-ma-002', email: 'owner@mainaccount.com', name: 'Account Owner' }
                ]
            };
        },

        sendNotification: function(notification) {
            if (this.isDuplicate(notification.type, notification.subAccountId)) {
                console.log('[EnforcementNotifications] Duplicate notification suppressed:', notification.type);
                return false;
            }

            const recipients = this.getRecipients(notification.subAccountId);
            const allRecipients = [...recipients.subAccountAdmins, ...recipients.mainAccountAdmins];

            this.sendEmailNotification(notification, allRecipients);
            this.sendPortalNotification(notification);
            this.logNotification(notification, allRecipients);
            this.markAsSent(notification.type, notification.subAccountId);

            return true;
        },

        sendEmailNotification: function(notification, recipients) {
            const emailPayload = {
                type: 'enforcement_notification',
                template: this.getEmailTemplate(notification.type),
                recipients: recipients.map(r => r.email),
                data: {
                    subAccountName: notification.subAccountName,
                    type: notification.type,
                    currentValue: notification.currentValue,
                    limitValue: notification.limitValue,
                    percentage: notification.percentage,
                    timestamp: new Date().toISOString()
                }
            };

            console.log('[EnforcementNotifications] Email sent (TODO: Backend integration):', emailPayload);
        },

        getEmailTemplate: function(type) {
            const templates = {
                'spend_80_percent': 'enforcement_warning_80',
                'spend_100_percent': 'enforcement_limit_reached',
                'enforcement_triggered': 'enforcement_action_taken',
                'campaign_blocked': 'campaign_blocked_notification'
            };
            return templates[type] || 'enforcement_generic';
        },

        sendPortalNotification: function(notification) {
            const portalNotification = {
                id: 'notif-' + Date.now(),
                type: notification.type,
                severity: notification.severity,
                title: this.getNotificationTitle(notification.type),
                message: this.getNotificationMessage(notification),
                subAccountName: notification.subAccountName,
                timestamp: new Date().toISOString(),
                read: false
            };

            this.pendingNotifications.unshift(portalNotification);
            this.updateNotificationBadge();
            this.showToastNotification(portalNotification);
            this.notifyObservers(portalNotification);
        },

        getNotificationTitle: function(type) {
            const titles = {
                'spend_80_percent': 'Spend Cap Warning',
                'spend_100_percent': 'Spend Cap Reached',
                'enforcement_triggered': 'Enforcement Triggered',
                'campaign_blocked': 'Campaign Blocked'
            };
            return titles[type] || 'Enforcement Alert';
        },

        getNotificationMessage: function(notification) {
            const messages = {
                'spend_80_percent': `${notification.subAccountName} has reached 80% of monthly spend cap (£${notification.currentValue} of £${notification.limitValue})`,
                'spend_100_percent': `${notification.subAccountName} has reached 100% of monthly spend cap (£${notification.limitValue}). Sending is now restricted.`,
                'enforcement_triggered': `Enforcement action triggered for ${notification.subAccountName}: ${notification.enforcementType || 'Limit exceeded'}`,
                'campaign_blocked': `Campaign "${notification.campaignName}" was blocked for ${notification.subAccountName}. Reason: ${notification.reason || 'Enforcement limit reached'}`
            };
            return messages[notification.type] || 'An enforcement event has occurred.';
        },

        logNotification: function(notification, recipients) {
            const auditEntry = {
                action: 'ENFORCEMENT_NOTIFICATION_SENT',
                notificationType: notification.type,
                severity: notification.severity,
                subAccountId: notification.subAccountId,
                subAccountName: notification.subAccountName,
                recipients: recipients.map(r => ({ id: r.id, email: r.email })),
                channels: ['email', 'portal'],
                data: {
                    currentValue: notification.currentValue,
                    limitValue: notification.limitValue,
                    percentage: notification.percentage,
                    campaignName: notification.campaignName,
                    enforcementType: notification.enforcementType,
                    reason: notification.reason
                },
                timestamp: new Date().toISOString(),
                deduplicated: false
            };

            console.log('[AUDIT] Enforcement notification logged:', auditEntry);
        },

        showToastNotification: function(notification) {
            const toastContainer = document.getElementById('enforcement-toast-container');
            if (!toastContainer) return;

            const severityColors = {
                'warning': { bg: '#fef3c7', border: '#f59e0b', icon: 'fa-exclamation-triangle', iconColor: '#d97706' },
                'critical': { bg: '#fee2e2', border: '#ef4444', icon: 'fa-exclamation-circle', iconColor: '#dc2626' },
                'blocked': { bg: '#fce7f3', border: '#ec4899', icon: 'fa-ban', iconColor: '#be185d' }
            };

            const style = severityColors[notification.severity] || severityColors.warning;

            const toast = document.createElement('div');
            toast.className = 'enforcement-toast';
            toast.style.cssText = `
                position: relative;
                background: ${style.bg};
                border-left: 4px solid ${style.border};
                border-radius: 8px;
                padding: 1rem;
                margin-bottom: 0.75rem;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideInRight 0.3s ease;
                max-width: 360px;
            `;

            toast.innerHTML = `
                <div style="display: flex; gap: 0.75rem;">
                    <div style="flex-shrink: 0;">
                        <i class="fas ${style.icon}" style="color: ${style.iconColor}; font-size: 1.25rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; font-size: 0.875rem; color: #374151; margin-bottom: 0.25rem;">
                            ${notification.title}
                        </div>
                        <div style="font-size: 0.8rem; color: #6b7280; line-height: 1.4;">
                            ${notification.message}
                        </div>
                        <div style="font-size: 0.7rem; color: #9ca3af; margin-top: 0.5rem;">
                            Just now
                        </div>
                    </div>
                    <button class="toast-close" style="
                        position: absolute;
                        top: 0.5rem;
                        right: 0.5rem;
                        background: none;
                        border: none;
                        color: #9ca3af;
                        cursor: pointer;
                        padding: 0.25rem;
                    ">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            toast.querySelector('.toast-close').addEventListener('click', function() {
                toast.style.animation = 'slideOutRight 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            });

            toastContainer.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.animation = 'slideOutRight 0.3s ease forwards';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 8000);
        },

        updateNotificationBadge: function() {
            const unreadCount = this.pendingNotifications.filter(n => !n.read).length;
            const badge = document.getElementById('enforcement-notification-badge');
            if (badge) {
                badge.textContent = unreadCount;
                badge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
            }
        },

        setupNotificationUI: function() {
            if (!document.getElementById('enforcement-toast-container')) {
                const container = document.createElement('div');
                container.id = 'enforcement-toast-container';
                container.style.cssText = `
                    position: fixed;
                    top: 80px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 380px;
                `;
                document.body.appendChild(container);
            }

            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        },

        checkEnforcementStatus: function() {
            const mockEnforcementData = {
                subAccounts: [
                    {
                        id: 'sa-001',
                        name: 'Marketing Department',
                        monthlySpendCap: 500,
                        currentSpend: 425,
                        dailySendLimit: 10000,
                        currentDailySends: 8500
                    },
                    {
                        id: 'sa-002',
                        name: 'Customer Support',
                        monthlySpendCap: 200,
                        currentSpend: 180,
                        dailySendLimit: 5000,
                        currentDailySends: 4200
                    }
                ]
            };

            mockEnforcementData.subAccounts.forEach(sa => {
                const spendPercentage = (sa.currentSpend / sa.monthlySpendCap) * 100;

                if (spendPercentage >= 100) {
                    this.triggerSpend100Notification(sa);
                } else if (spendPercentage >= 80) {
                    this.triggerSpend80Notification(sa);
                }
            });
        },

        triggerSpend80Notification: function(subAccount) {
            this.sendNotification({
                type: this.NOTIFICATION_TYPES.SPEND_80_PERCENT,
                severity: this.SEVERITY.WARNING,
                subAccountId: subAccount.id,
                subAccountName: subAccount.name,
                currentValue: subAccount.currentSpend,
                limitValue: subAccount.monthlySpendCap,
                percentage: Math.round((subAccount.currentSpend / subAccount.monthlySpendCap) * 100)
            });
        },

        triggerSpend100Notification: function(subAccount) {
            this.sendNotification({
                type: this.NOTIFICATION_TYPES.SPEND_100_PERCENT,
                severity: this.SEVERITY.CRITICAL,
                subAccountId: subAccount.id,
                subAccountName: subAccount.name,
                currentValue: subAccount.currentSpend,
                limitValue: subAccount.monthlySpendCap,
                percentage: 100
            });
        },

        triggerEnforcementNotification: function(subAccountId, subAccountName, enforcementType, details) {
            this.sendNotification({
                type: this.NOTIFICATION_TYPES.ENFORCEMENT_TRIGGERED,
                severity: this.SEVERITY.CRITICAL,
                subAccountId: subAccountId,
                subAccountName: subAccountName,
                enforcementType: enforcementType,
                ...details
            });
        },

        triggerCampaignBlockedNotification: function(subAccountId, subAccountName, campaignName, reason) {
            this.sendNotification({
                type: this.NOTIFICATION_TYPES.CAMPAIGN_BLOCKED,
                severity: this.SEVERITY.BLOCKED,
                subAccountId: subAccountId,
                subAccountName: subAccountName,
                campaignName: campaignName,
                reason: reason
            });
        },

        subscribe: function(callback) {
            this.observers.push(callback);
            return () => {
                this.observers = this.observers.filter(cb => cb !== callback);
            };
        },

        notifyObservers: function(notification) {
            this.observers.forEach(callback => callback(notification));
        },

        getNotifications: function() {
            return [...this.pendingNotifications];
        },

        markAsRead: function(notificationId) {
            const notification = this.pendingNotifications.find(n => n.id === notificationId);
            if (notification) {
                notification.read = true;
                this.updateNotificationBadge();
            }
        },

        markAllAsRead: function() {
            this.pendingNotifications.forEach(n => n.read = true);
            this.updateNotificationBadge();
        },

        clearHistory: function() {
            this.notificationHistory.clear();
            this.saveNotificationHistory();
        },

        simulateNotification: function(type) {
            const testNotifications = {
                'spend_80': () => this.triggerSpend80Notification({
                    id: 'test-sa',
                    name: 'Test Sub-Account',
                    currentSpend: 400,
                    monthlySpendCap: 500
                }),
                'spend_100': () => this.triggerSpend100Notification({
                    id: 'test-sa',
                    name: 'Test Sub-Account',
                    currentSpend: 500,
                    monthlySpendCap: 500
                }),
                'enforcement': () => this.triggerEnforcementNotification(
                    'test-sa',
                    'Test Sub-Account',
                    'Daily Send Limit',
                    { currentValue: 10000, limitValue: 10000 }
                ),
                'blocked': () => this.triggerCampaignBlockedNotification(
                    'test-sa',
                    'Test Sub-Account',
                    'January Promo Blast',
                    'Monthly spend cap exceeded'
                )
            };

            const testKey = `test_${type}_${Date.now()}`;
            this.notificationHistory.delete(this.generateNotificationKey(type, 'test-sa'));

            if (testNotifications[type]) {
                testNotifications[type]();
            }
        }
    };

    window.EnforcementNotifications = EnforcementNotifications;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => EnforcementNotifications.init());
    } else {
        EnforcementNotifications.init();
    }
})();
