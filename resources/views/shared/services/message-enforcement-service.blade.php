<script>
var MessageEnforcementService = (function() {
    var DECISIONS = {
        ALLOW: 'ALLOW',
        BLOCK: 'BLOCK',
        QUARANTINE: 'QUARANTINE'
    };

    var ENGINES = {
        NORMALISATION: 'normalisation',
        SENDERID: 'senderid',
        CONTENT: 'content',
        URL: 'url'
    };

    var activeRules = {
        normalisation: [],
        senderid: [],
        content: [],
        url: []
    };

    var domainAgeCache = {};

    function initialize() {
        loadActiveRules();
        console.log('[MessageEnforcementService] Initialized with rule counts:', {
            normalisation: activeRules.normalisation.length,
            senderid: activeRules.senderid.length,
            content: activeRules.content.length,
            url: activeRules.url.length
        });
    }

    function loadActiveRules() {
        activeRules.normalisation = [
            { id: 'NORM-001', name: 'UK Number Format', type: 'phone', pattern: '^0([0-9]{10})$', replacement: '+44$1', priority: 1, status: 'active' },
            { id: 'NORM-002', name: 'UTF-8 Encoding', type: 'encoding', charset: 'UTF-8', fallback: 'GSM-7', priority: 2, status: 'active' },
            { id: 'NORM-003', name: 'Strip Non-GSM', type: 'format', pattern: '[^\x20-\x7E\u00A0-\u00FF]', replacement: '', priority: 3, status: 'active' }
        ];

        activeRules.senderid = [
            { id: 'SID-001', name: 'Block Bank Names', type: 'pattern', pattern: 'HSBC|BARCLAYS|LLOYDS|NATWEST|SANTANDER', action: 'block', status: 'active' },
            { id: 'SID-002', name: 'Block HMRC', type: 'exact', value: 'HMRC', action: 'block', status: 'active' },
            { id: 'SID-003', name: 'Block Gov Impersonation', type: 'pattern', pattern: 'GOV\\.UK|DVLA|NHS', action: 'quarantine', status: 'active' },
            { id: 'SID-004', name: 'Block Lottery', type: 'keyword', keywords: ['WINNER', 'PRIZE', 'LOTTERY', 'JACKPOT'], action: 'block', status: 'active' }
        ];

        activeRules.content = [
            { id: 'CNT-001', name: 'Phishing Keywords', category: 'fraud', pattern: 'verify your account|click here immediately|urgent action required|suspended.*account', action: 'block', status: 'active' },
            { id: 'CNT-002', name: 'Financial Scam', category: 'fraud', pattern: 'you have won|claim your prize|transfer fee|inheritance', action: 'block', status: 'active' },
            { id: 'CNT-003', name: 'Adult Content', category: 'adult', pattern: '(adult content patterns)', action: 'quarantine', status: 'active' },
            { id: 'CNT-004', name: 'Gambling Promotion', category: 'gambling', pattern: 'bet now|free spins|casino bonus|betting odds', action: 'quarantine', status: 'active' },
            { id: 'CNT-005', name: 'Crypto Scam', category: 'fraud', pattern: 'bitcoin.*double|crypto.*guaranteed|invest.*returns', action: 'block', status: 'active' }
        ];

        activeRules.url = [
            { id: 'URL-001', name: 'Bit.ly Allowed', domain: 'bit.ly', type: 'whitelist', category: 'shortener', status: 'active' },
            { id: 'URL-002', name: 'TinyURL Allowed', domain: 'tinyurl.com', type: 'whitelist', category: 'shortener', status: 'active' },
            { id: 'URL-003', name: 'Known Phishing', domain: 'secure-bank-verify.com', type: 'blacklist', category: 'phishing', status: 'active' },
            { id: 'URL-004', name: 'Known Malware', domain: 'free-download-now.net', type: 'blacklist', category: 'malware', status: 'active' },
            { id: 'URL-005', name: 'Block Raw IP URLs', pattern: '^https?://\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}', type: 'pattern_blacklist', category: 'suspicious', status: 'active' }
        ];

        activeRules.url.push({
            id: 'URL-AGE-001',
            name: 'New Domain Check',
            type: 'domain_age',
            minAgeDays: 30,
            action: 'quarantine',
            status: 'active'
        });
    }

    function evaluate(message) {
        var startTime = Date.now();
        var context = {
            originalMessage: JSON.parse(JSON.stringify(message)),
            normalisedMessage: null,
            triggeredRules: [],
            matchedTokens: [],
            decision: DECISIONS.ALLOW,
            reason: null,
            processingOrder: [],
            processingTimeMs: 0
        };

        context.normalisedMessage = applyNormalisationRules(message, context);
        context.processingOrder.push(ENGINES.NORMALISATION);

        var senderIdResult = evaluateSenderIdRules(context.normalisedMessage, context);
        context.processingOrder.push(ENGINES.SENDERID);
        if (senderIdResult.decision === DECISIONS.BLOCK) {
            context.decision = DECISIONS.BLOCK;
            context.reason = senderIdResult.reason;
            context.processingTimeMs = Date.now() - startTime;
            return buildResult(context);
        }
        if (senderIdResult.decision === DECISIONS.QUARANTINE && context.decision !== DECISIONS.BLOCK) {
            context.decision = DECISIONS.QUARANTINE;
            context.reason = senderIdResult.reason;
        }

        var contentResult = evaluateContentRules(context.normalisedMessage, context);
        context.processingOrder.push(ENGINES.CONTENT);
        if (contentResult.decision === DECISIONS.BLOCK) {
            context.decision = DECISIONS.BLOCK;
            context.reason = contentResult.reason;
            context.processingTimeMs = Date.now() - startTime;
            return buildResult(context);
        }
        if (contentResult.decision === DECISIONS.QUARANTINE && context.decision !== DECISIONS.BLOCK) {
            context.decision = DECISIONS.QUARANTINE;
            context.reason = context.reason || contentResult.reason;
        }

        var urlResult = evaluateUrlRules(context.normalisedMessage, context);
        context.processingOrder.push(ENGINES.URL);
        if (urlResult.decision === DECISIONS.BLOCK) {
            context.decision = DECISIONS.BLOCK;
            context.reason = urlResult.reason;
        } else if (urlResult.decision === DECISIONS.QUARANTINE && context.decision !== DECISIONS.BLOCK) {
            context.decision = DECISIONS.QUARANTINE;
            context.reason = context.reason || urlResult.reason;
        }

        context.processingTimeMs = Date.now() - startTime;
        return buildResult(context);
    }

    function applyNormalisationRules(message, context) {
        var normalised = JSON.parse(JSON.stringify(message));
        var sortedRules = activeRules.normalisation
            .filter(function(r) { return r.status === 'active'; })
            .sort(function(a, b) { return a.priority - b.priority; });

        sortedRules.forEach(function(rule) {
            if (rule.type === 'phone' && normalised.recipient) {
                var regex = new RegExp(rule.pattern);
                if (regex.test(normalised.recipient)) {
                    var before = normalised.recipient;
                    normalised.recipient = normalised.recipient.replace(regex, rule.replacement);
                    if (before !== normalised.recipient) {
                        context.triggeredRules.push({
                            ruleId: rule.id,
                            ruleName: rule.name,
                            engine: ENGINES.NORMALISATION,
                            action: 'transform',
                            before: before,
                            after: normalised.recipient
                        });
                    }
                }
            }

            if (rule.type === 'format' && normalised.body) {
                var regex = new RegExp(rule.pattern, 'g');
                var before = normalised.body;
                normalised.body = normalised.body.replace(regex, rule.replacement);
                if (before !== normalised.body) {
                    context.triggeredRules.push({
                        ruleId: rule.id,
                        ruleName: rule.name,
                        engine: ENGINES.NORMALISATION,
                        action: 'sanitize',
                        charactersRemoved: before.length - normalised.body.length
                    });
                }
            }
        });

        return normalised;
    }

    function evaluateSenderIdRules(message, context) {
        var result = { decision: DECISIONS.ALLOW, reason: null };
        var senderId = (message.senderId || '').toUpperCase();

        if (!senderId) {
            return result;
        }

        var sortedRules = activeRules.senderid
            .filter(function(r) { return r.status === 'active'; });

        for (var i = 0; i < sortedRules.length; i++) {
            var rule = sortedRules[i];
            var matched = false;
            var matchedValue = null;

            if (rule.type === 'exact' && senderId === rule.value.toUpperCase()) {
                matched = true;
                matchedValue = rule.value;
            }

            if (rule.type === 'pattern') {
                var regex = new RegExp(rule.pattern, 'i');
                var match = senderId.match(regex);
                if (match) {
                    matched = true;
                    matchedValue = match[0];
                }
            }

            if (rule.type === 'keyword' && rule.keywords) {
                for (var j = 0; j < rule.keywords.length; j++) {
                    if (senderId.indexOf(rule.keywords[j].toUpperCase()) !== -1) {
                        matched = true;
                        matchedValue = rule.keywords[j];
                        break;
                    }
                }
            }

            if (matched) {
                context.triggeredRules.push({
                    ruleId: rule.id,
                    ruleName: rule.name,
                    engine: ENGINES.SENDERID,
                    action: rule.action,
                    matchedValue: matchedValue
                });
                context.matchedTokens.push({
                    engine: ENGINES.SENDERID,
                    token: matchedValue,
                    ruleId: rule.id
                });

                if (rule.action === 'block') {
                    result.decision = DECISIONS.BLOCK;
                    result.reason = 'SenderID blocked by rule: ' + rule.name;
                    return result;
                }
                if (rule.action === 'quarantine' && result.decision !== DECISIONS.BLOCK) {
                    result.decision = DECISIONS.QUARANTINE;
                    result.reason = 'SenderID flagged for review: ' + rule.name;
                }
            }
        }

        return result;
    }

    function evaluateContentRules(message, context) {
        var result = { decision: DECISIONS.ALLOW, reason: null };
        var body = (message.body || '').toLowerCase();

        if (!body) {
            return result;
        }

        var sortedRules = activeRules.content
            .filter(function(r) { return r.status === 'active'; });

        for (var i = 0; i < sortedRules.length; i++) {
            var rule = sortedRules[i];
            var regex = new RegExp(rule.pattern, 'i');
            var match = body.match(regex);

            if (match) {
                context.triggeredRules.push({
                    ruleId: rule.id,
                    ruleName: rule.name,
                    engine: ENGINES.CONTENT,
                    category: rule.category,
                    action: rule.action,
                    matchedValue: match[0]
                });
                context.matchedTokens.push({
                    engine: ENGINES.CONTENT,
                    token: match[0],
                    ruleId: rule.id,
                    category: rule.category
                });

                if (rule.action === 'block') {
                    result.decision = DECISIONS.BLOCK;
                    result.reason = 'Content blocked (' + rule.category + '): ' + rule.name;
                    return result;
                }
                if (rule.action === 'quarantine' && result.decision !== DECISIONS.BLOCK) {
                    result.decision = DECISIONS.QUARANTINE;
                    result.reason = 'Content flagged (' + rule.category + '): ' + rule.name;
                }
            }
        }

        return result;
    }

    function evaluateUrlRules(message, context) {
        var result = { decision: DECISIONS.ALLOW, reason: null };
        var body = message.body || '';

        var urls = extractUrls(body);
        if (urls.length === 0) {
            return result;
        }

        var sortedRules = activeRules.url
            .filter(function(r) { return r.status === 'active'; });

        for (var i = 0; i < urls.length; i++) {
            var url = urls[i];
            var domain = extractDomain(url);

            for (var j = 0; j < sortedRules.length; j++) {
                var rule = sortedRules[j];
                var matched = false;

                if (rule.type === 'whitelist' && domain === rule.domain) {
                    context.triggeredRules.push({
                        ruleId: rule.id,
                        ruleName: rule.name,
                        engine: ENGINES.URL,
                        action: 'allow',
                        matchedUrl: url,
                        domain: domain
                    });
                    continue;
                }

                if (rule.type === 'blacklist' && domain === rule.domain) {
                    matched = true;
                }

                if (rule.type === 'pattern_blacklist') {
                    var regex = new RegExp(rule.pattern, 'i');
                    if (regex.test(url)) {
                        matched = true;
                    }
                }

                if (rule.type === 'domain_age') {
                    var domainAge = getDomainAge(domain);
                    if (domainAge !== null && domainAge < rule.minAgeDays) {
                        context.triggeredRules.push({
                            ruleId: rule.id,
                            ruleName: rule.name,
                            engine: ENGINES.URL,
                            action: rule.action,
                            matchedUrl: url,
                            domain: domain,
                            domainAgeDays: domainAge,
                            minRequired: rule.minAgeDays
                        });
                        context.matchedTokens.push({
                            engine: ENGINES.URL,
                            token: domain,
                            ruleId: rule.id,
                            reason: 'Domain age: ' + domainAge + ' days (min: ' + rule.minAgeDays + ')'
                        });

                        if (rule.action === 'block') {
                            result.decision = DECISIONS.BLOCK;
                            result.reason = 'URL blocked - domain too new: ' + domain;
                            return result;
                        }
                        if (rule.action === 'quarantine' && result.decision !== DECISIONS.BLOCK) {
                            result.decision = DECISIONS.QUARANTINE;
                            result.reason = 'URL flagged - domain too new: ' + domain;
                        }
                    }
                    continue;
                }

                if (matched) {
                    context.triggeredRules.push({
                        ruleId: rule.id,
                        ruleName: rule.name,
                        engine: ENGINES.URL,
                        category: rule.category,
                        action: 'block',
                        matchedUrl: url,
                        domain: domain
                    });
                    context.matchedTokens.push({
                        engine: ENGINES.URL,
                        token: url,
                        ruleId: rule.id,
                        category: rule.category
                    });

                    result.decision = DECISIONS.BLOCK;
                    result.reason = 'URL blocked (' + rule.category + '): ' + domain;
                    return result;
                }
            }
        }

        return result;
    }

    function extractUrls(text) {
        var urlPattern = /https?:\/\/[^\s<>"{}|\\^`\[\]]+/gi;
        return text.match(urlPattern) || [];
    }

    function extractDomain(url) {
        try {
            var match = url.match(/^https?:\/\/([^\/\?#]+)/i);
            return match ? match[1].toLowerCase() : null;
        } catch (e) {
            return null;
        }
    }

    function getDomainAge(domain) {
        if (domainAgeCache[domain] !== undefined) {
            return domainAgeCache[domain];
        }

        var knownDomains = {
            'bit.ly': 5000,
            'tinyurl.com': 6000,
            'google.com': 9000,
            'secure-bank-verify.com': 15,
            'free-download-now.net': 7,
            'new-promo-site.xyz': 5
        };

        if (knownDomains[domain] !== undefined) {
            domainAgeCache[domain] = knownDomains[domain];
            return knownDomains[domain];
        }

        if (domain.match(/\.(xyz|top|work|click|loan|download)$/i)) {
            domainAgeCache[domain] = Math.floor(Math.random() * 60);
            return domainAgeCache[domain];
        }

        domainAgeCache[domain] = null;
        return null;
    }

    function buildResult(context) {
        return {
            decision: context.decision,
            reason: context.reason,
            triggeredRules: context.triggeredRules,
            matchedTokens: context.matchedTokens,
            processingOrder: context.processingOrder,
            processingTimeMs: context.processingTimeMs,
            originalMessage: {
                senderId: context.originalMessage.senderId,
                recipientCount: context.originalMessage.recipient ? 1 : 0,
                bodyLength: (context.originalMessage.body || '').length
            },
            normalisedMessage: {
                senderId: context.normalisedMessage.senderId,
                bodyLength: (context.normalisedMessage.body || '').length
            },
            timestamp: new Date().toISOString(),
            serviceVersion: '1.0.0'
        };
    }

    function updateRules(ruleType, rules) {
        if (activeRules[ruleType] !== undefined) {
            activeRules[ruleType] = rules;
            console.log('[MessageEnforcementService] Updated', ruleType, 'rules:', rules.length);
            return true;
        }
        return false;
    }

    function getRules(ruleType) {
        if (ruleType) {
            return activeRules[ruleType] ? JSON.parse(JSON.stringify(activeRules[ruleType])) : [];
        }
        return JSON.parse(JSON.stringify(activeRules));
    }

    function getStats() {
        return {
            normalisation: {
                total: activeRules.normalisation.length,
                active: activeRules.normalisation.filter(function(r) { return r.status === 'active'; }).length
            },
            senderid: {
                total: activeRules.senderid.length,
                active: activeRules.senderid.filter(function(r) { return r.status === 'active'; }).length,
                blocking: activeRules.senderid.filter(function(r) { return r.action === 'block'; }).length
            },
            content: {
                total: activeRules.content.length,
                active: activeRules.content.filter(function(r) { return r.status === 'active'; }).length,
                byCategory: activeRules.content.reduce(function(acc, r) {
                    acc[r.category] = (acc[r.category] || 0) + 1;
                    return acc;
                }, {})
            },
            url: {
                total: activeRules.url.length,
                whitelist: activeRules.url.filter(function(r) { return r.type === 'whitelist'; }).length,
                blacklist: activeRules.url.filter(function(r) { return r.type === 'blacklist' || r.type === 'pattern_blacklist'; }).length
            }
        };
    }

    function testMessage(senderId, body, recipient) {
        var message = {
            senderId: senderId || 'TestSender',
            body: body || 'Test message',
            recipient: recipient || '07700900000'
        };
        var result = evaluate(message);
        console.log('[MessageEnforcementService] Test result:', result);
        return result;
    }

    return {
        DECISIONS: DECISIONS,
        ENGINES: ENGINES,
        initialize: initialize,
        evaluate: evaluate,
        updateRules: updateRules,
        getRules: getRules,
        getStats: getStats,
        testMessage: testMessage
    };
})();

MessageEnforcementService.initialize();
window.MessageEnforcementService = MessageEnforcementService;

console.log('[MessageEnforcementService] Global enforcement service ready');
console.log('[MessageEnforcementService] Rule stats:', MessageEnforcementService.getStats());

var MessageSubmissionHandler = (function() {
    var SOURCES = {
        CAMPAIGNS: 'campaigns',
        INBOX: 'inbox',
        API: 'api',
        EMAIL_TO_SMS: 'email_to_sms',
        TEMPLATES: 'templates',
        RCS: 'rcs'
    };

    var OUTCOMES = {
        SENT: 'sent',
        BLOCKED: 'blocked',
        QUARANTINED: 'quarantined',
        ERROR: 'error'
    };

    var quarantineStore = [];

    function submit(message, source, context) {
        context = context || {};
        
        var submissionId = 'SUB-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        var timestamp = new Date().toISOString();

        console.log('[MessageSubmissionHandler] Processing submission:', {
            submissionId: submissionId,
            source: source,
            senderId: message.senderId,
            recipientCount: Array.isArray(message.recipients) ? message.recipients.length : 1
        });

        var enforcementResult = MessageEnforcementService.evaluate(message);

        var result = {
            submissionId: submissionId,
            source: source,
            timestamp: timestamp,
            outcome: null,
            message: null,
            error: null,
            enforcementResult: enforcementResult,
            context: context
        };

        switch (enforcementResult.decision) {
            case MessageEnforcementService.DECISIONS.BLOCK:
                result.outcome = OUTCOMES.BLOCKED;
                result.error = buildBlockError(enforcementResult);
                logSubmissionEvent('MESSAGE_BLOCKED', result);
                break;

            case MessageEnforcementService.DECISIONS.QUARANTINE:
                result.outcome = OUTCOMES.QUARANTINED;
                result.message = 'Message quarantined for review';
                addToQuarantine(submissionId, message, source, enforcementResult, context);
                logSubmissionEvent('MESSAGE_QUARANTINED', result);
                break;

            case MessageEnforcementService.DECISIONS.ALLOW:
                result.outcome = OUTCOMES.SENT;
                result.message = 'Message accepted for delivery';
                logSubmissionEvent('MESSAGE_ALLOWED', result);
                break;

            default:
                result.outcome = OUTCOMES.ERROR;
                result.error = { code: 'UNKNOWN_DECISION', message: 'Unknown enforcement decision' };
                break;
        }

        return result;
    }

    function buildBlockError(enforcementResult) {
        var triggeredRule = enforcementResult.triggeredRules.find(function(r) {
            return r.action === 'block';
        });

        var errorDetails = {
            code: 'MESSAGE_BLOCKED',
            message: enforcementResult.reason || 'Message blocked by security policy',
            category: triggeredRule ? triggeredRule.category : 'security',
            ruleName: triggeredRule ? triggeredRule.ruleName : 'Unknown Rule',
            ruleId: triggeredRule ? triggeredRule.ruleId : null,
            engine: triggeredRule ? triggeredRule.engine : null,
            matchedValue: triggeredRule ? triggeredRule.matchedValue : null,
            userFriendlyMessage: buildUserFriendlyBlockMessage(triggeredRule)
        };

        return errorDetails;
    }

    function buildUserFriendlyBlockMessage(triggeredRule) {
        if (!triggeredRule) {
            return 'This message cannot be sent due to security policy restrictions.';
        }

        var messages = {
            'senderid': 'The SenderID "' + (triggeredRule.matchedValue || 'specified') + '" is not permitted. Please use an approved SenderID.',
            'content': 'The message content contains restricted terms. Please review and modify your message.',
            'url': 'The message contains a blocked URL. Please remove or replace the link.'
        };

        return messages[triggeredRule.engine] || 'This message cannot be sent due to policy restrictions.';
    }

    function addToQuarantine(submissionId, message, source, enforcementResult, context) {
        var quarantineEntry = {
            id: 'QRN-' + Date.now(),
            submissionId: submissionId,
            message: {
                senderId: message.senderId,
                body: message.body ? message.body.substring(0, 200) + (message.body.length > 200 ? '...' : '') : '',
                recipientCount: Array.isArray(message.recipients) ? message.recipients.length : 1
            },
            source: source,
            accountId: context.accountId || null,
            accountName: context.accountName || null,
            submittedBy: context.userId || null,
            reason: enforcementResult.reason,
            triggeredRules: enforcementResult.triggeredRules,
            matchedTokens: enforcementResult.matchedTokens,
            status: 'pending',
            createdAt: new Date().toISOString(),
            reviewedAt: null,
            reviewedBy: null,
            reviewDecision: null
        };

        quarantineStore.push(quarantineEntry);
        
        console.log('[QuarantineStore] Message added:', quarantineEntry.id, {
            source: source,
            accountId: context.accountId,
            reason: enforcementResult.reason
        });

        return quarantineEntry;
    }

    function getQuarantineQueue(filters) {
        filters = filters || {};
        var results = quarantineStore.filter(function(entry) {
            if (filters.status && entry.status !== filters.status) return false;
            if (filters.source && entry.source !== filters.source) return false;
            if (filters.accountId && entry.accountId !== filters.accountId) return false;
            return true;
        });
        
        return results.sort(function(a, b) {
            return new Date(b.createdAt) - new Date(a.createdAt);
        });
    }

    function reviewQuarantinedMessage(quarantineId, decision, reviewerId, reason) {
        var entry = quarantineStore.find(function(e) { return e.id === quarantineId; });
        if (!entry) {
            return { success: false, error: 'Quarantine entry not found' };
        }

        entry.status = decision === 'release' ? 'released' : 'rejected';
        entry.reviewedAt = new Date().toISOString();
        entry.reviewedBy = reviewerId;
        entry.reviewDecision = decision;
        entry.reviewReason = reason || null;

        logSubmissionEvent('QUARANTINE_REVIEWED', {
            quarantineId: quarantineId,
            submissionId: entry.submissionId,
            decision: decision,
            reviewerId: reviewerId
        });

        return { success: true, entry: entry };
    }

    function logSubmissionEvent(eventType, data) {
        var logEntry = {
            eventType: eventType,
            timestamp: new Date().toISOString(),
            data: data
        };
        console.log('[MessageSubmissionHandler][' + eventType + ']', JSON.stringify(logEntry));
    }

    function submitFromCampaigns(message, campaignContext) {
        return submit(message, SOURCES.CAMPAIGNS, {
            accountId: campaignContext.accountId,
            accountName: campaignContext.accountName,
            userId: campaignContext.userId,
            campaignId: campaignContext.campaignId,
            campaignName: campaignContext.campaignName
        });
    }

    function submitFromInbox(message, inboxContext) {
        return submit(message, SOURCES.INBOX, {
            accountId: inboxContext.accountId,
            accountName: inboxContext.accountName,
            userId: inboxContext.userId,
            conversationId: inboxContext.conversationId,
            isReply: true
        });
    }

    function submitFromApi(message, apiContext) {
        return submit(message, SOURCES.API, {
            accountId: apiContext.accountId,
            accountName: apiContext.accountName,
            apiKeyId: apiContext.apiKeyId,
            clientIp: apiContext.clientIp
        });
    }

    function submitFromEmailToSms(message, emailContext) {
        return submit(message, SOURCES.EMAIL_TO_SMS, {
            accountId: emailContext.accountId,
            accountName: emailContext.accountName,
            senderEmail: emailContext.senderEmail,
            emailSubject: emailContext.emailSubject,
            parsedAt: emailContext.parsedAt
        });
    }

    function submitFromTemplates(message, templateContext) {
        return submit(message, SOURCES.TEMPLATES, {
            accountId: templateContext.accountId,
            accountName: templateContext.accountName,
            userId: templateContext.userId,
            templateId: templateContext.templateId,
            templateName: templateContext.templateName
        });
    }

    function submitFromRcs(message, rcsContext) {
        return submit(message, SOURCES.RCS, {
            accountId: rcsContext.accountId,
            accountName: rcsContext.accountName,
            userId: rcsContext.userId,
            agentId: rcsContext.agentId,
            agentName: rcsContext.agentName
        });
    }

    function getQuarantineStats() {
        return {
            total: quarantineStore.length,
            pending: quarantineStore.filter(function(e) { return e.status === 'pending'; }).length,
            released: quarantineStore.filter(function(e) { return e.status === 'released'; }).length,
            rejected: quarantineStore.filter(function(e) { return e.status === 'rejected'; }).length,
            bySource: quarantineStore.reduce(function(acc, e) {
                acc[e.source] = (acc[e.source] || 0) + 1;
                return acc;
            }, {})
        };
    }

    return {
        SOURCES: SOURCES,
        OUTCOMES: OUTCOMES,
        submit: submit,
        submitFromCampaigns: submitFromCampaigns,
        submitFromInbox: submitFromInbox,
        submitFromApi: submitFromApi,
        submitFromEmailToSms: submitFromEmailToSms,
        submitFromTemplates: submitFromTemplates,
        submitFromRcs: submitFromRcs,
        getQuarantineQueue: getQuarantineQueue,
        reviewQuarantinedMessage: reviewQuarantinedMessage,
        getQuarantineStats: getQuarantineStats
    };
})();

window.MessageSubmissionHandler = MessageSubmissionHandler;

console.log('[MessageSubmissionHandler] Unified submission handler ready');
console.log('[MessageSubmissionHandler] Supported sources:', Object.keys(MessageSubmissionHandler.SOURCES));
</script>
