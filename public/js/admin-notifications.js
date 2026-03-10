var ADMIN_NOTIFICATIONS = (function() {
    'use strict';

    var CUSTOMER_NOTIFICATION_TYPES = {
        'RETURNED': {
            subject: 'Action Required: Your {assetType} Registration Needs Updates',
            icon: 'fa-reply',
            color: '#f59e0b',
            template: 'returned'
        },
        'APPROVED': {
            subject: 'Your {assetType} Has Been Approved',
            icon: 'fa-check-circle',
            color: '#22c55e',
            template: 'approved'
        },
        'REJECTED': {
            subject: 'Your {assetType} Registration Could Not Be Approved',
            icon: 'fa-times-circle',
            color: '#ef4444',
            template: 'rejected'
        },
        'LIVE': {
            subject: 'Your {assetType} Is Now Live',
            icon: 'fa-broadcast-tower',
            color: '#3b82f6',
            template: 'live'
        }
    };

    var INTERNAL_ALERT_TYPES = {
        'HIGH_RISK': {
            title: 'High-Risk Submission Detected',
            icon: 'fa-exclamation-triangle',
            color: '#ef4444',
            priority: 'CRITICAL',
            sound: true
        },
        'SLA_BREACH': {
            title: 'SLA Breach Warning',
            icon: 'fa-clock',
            color: '#f59e0b',
            priority: 'HIGH',
            sound: true
        },
        'SLA_IMMINENT': {
            title: 'SLA Breach Imminent',
            icon: 'fa-hourglass-half',
            color: '#eab308',
            priority: 'MEDIUM',
            sound: false
        },
        'VALIDATION_FAILED': {
            title: 'External Validation Failed',
            icon: 'fa-shield-alt',
            color: '#dc2626',
            priority: 'HIGH',
            sound: true
        },
        'RESUBMISSION': {
            title: 'Customer Resubmission Received',
            icon: 'fa-redo',
            color: '#3b82f6',
            priority: 'MEDIUM',
            sound: false
        }
    };

    var SLA_THRESHOLDS = {
        senderIdReview: 24 * 60 * 60 * 1000,
        rcsAgentReview: 48 * 60 * 60 * 1000,
        warningPercent: 0.75
    };

    var notificationQueue = [];
    var alertQueue = [];

    function init() {
        console.log('[AdminNotifications] Initialized');
        renderNotificationBell();
        startSlaMonitoring();
    }

    function sendCustomerNotification(type, requestId, assetType, customerEmail, customMessage) {
        var notificationType = CUSTOMER_NOTIFICATION_TYPES[type];
        if (!notificationType) {
            console.error('[AdminNotifications] Unknown notification type:', type);
            return;
        }

        var notification = {
            id: 'NOTIF-' + Date.now(),
            type: type,
            requestId: requestId,
            assetType: assetType,
            customerEmail: customerEmail,
            subject: notificationType.subject.replace('{assetType}', assetType),
            customMessage: customMessage || '',
            sentAt: new Date().toISOString(),
            status: 'SENT'
        };

        notificationQueue.push(notification);

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('CUSTOMER_NOTIFICATION_SENT', requestId, {
                type: type,
                recipientEmail: maskEmail(customerEmail)
            }, 'MEDIUM');
        }

        console.log('[AdminNotifications] Customer notification sent:', notification);
        showNotificationSentToast(notification);

        return notification;
    }

    function triggerInternalAlert(type, requestId, details) {
        var alertType = INTERNAL_ALERT_TYPES[type];
        if (!alertType) {
            console.error('[AdminNotifications] Unknown alert type:', type);
            return;
        }

        var alert = {
            id: 'ALERT-' + Date.now(),
            type: type,
            requestId: requestId,
            title: alertType.title,
            details: details,
            priority: alertType.priority,
            createdAt: new Date().toISOString(),
            acknowledged: false,
            acknowledgedBy: null,
            acknowledgedAt: null
        };

        alertQueue.unshift(alert);

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('INTERNAL_ALERT_TRIGGERED', requestId, {
                alertType: type,
                priority: alertType.priority
            }, alertType.priority === 'CRITICAL' ? 'CRITICAL' : 'HIGH');
        }

        updateAlertBadge();
        showInternalAlertToast(alert, alertType);

        if (alertType.sound) {
            playAlertSound(alertType.priority);
        }

        return alert;
    }

    function acknowledgeAlert(alertId) {
        var alert = alertQueue.find(function(a) { return a.id === alertId; });
        if (!alert) return;

        alert.acknowledged = true;
        alert.acknowledgedBy = 'admin@quicksms.com';
        alert.acknowledgedAt = new Date().toISOString();

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('ALERT_ACKNOWLEDGED', alert.requestId, {
                alertId: alertId,
                alertType: alert.type
            }, 'LOW');
        }

        updateAlertBadge();
        renderAlertPanel();
    }

    function renderNotificationBell() {
        var headerRight = document.querySelector('.header-right');
        if (!headerRight) return;

        var existingBell = document.getElementById('alertBellContainer');
        if (existingBell) existingBell.remove();

        var bellHtml = '\
        <div id="alertBellContainer" class="alert-bell-container">\
            <button class="alert-bell-btn" onclick="ADMIN_NOTIFICATIONS.toggleAlertPanel()">\
                <i class="fas fa-bell"></i>\
                <span class="alert-badge" id="alertBadge" style="display:none;">0</span>\
            </button>\
            <div class="alert-panel" id="alertPanel" style="display:none;">\
                <div class="alert-panel-header">\
                    <span>Internal Alerts</span>\
                    <button class="alert-panel-close" onclick="ADMIN_NOTIFICATIONS.toggleAlertPanel()">\
                        <i class="fas fa-times"></i>\
                    </button>\
                </div>\
                <div class="alert-panel-body" id="alertPanelBody">\
                    <div class="alert-empty">No alerts</div>\
                </div>\
                <div class="alert-panel-footer">\
                    <button class="btn-acknowledge-all" onclick="ADMIN_NOTIFICATIONS.acknowledgeAll()">Acknowledge All</button>\
                </div>\
            </div>\
        </div>';

        headerRight.insertAdjacentHTML('afterbegin', bellHtml);
    }

    function toggleAlertPanel() {
        var panel = document.getElementById('alertPanel');
        if (panel) {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
            if (panel.style.display === 'block') {
                renderAlertPanel();
            }
        }
    }

    function renderAlertPanel() {
        var body = document.getElementById('alertPanelBody');
        if (!body) return;

        var unacknowledged = alertQueue.filter(function(a) { return !a.acknowledged; });

        if (unacknowledged.length === 0) {
            body.innerHTML = '<div class="alert-empty"><i class="fas fa-check-circle"></i><p>No pending alerts</p></div>';
            return;
        }

        var html = '';
        unacknowledged.forEach(function(alert) {
            var alertType = INTERNAL_ALERT_TYPES[alert.type];
            html += '\
            <div class="alert-item priority-' + alert.priority.toLowerCase() + '">\
                <div class="alert-item-icon" style="color:' + alertType.color + '">\
                    <i class="fas ' + alertType.icon + '"></i>\
                </div>\
                <div class="alert-item-content">\
                    <div class="alert-item-title">' + alert.title + '</div>\
                    <div class="alert-item-detail">' + alert.requestId + '</div>\
                    <div class="alert-item-time">' + formatTimeAgo(alert.createdAt) + '</div>\
                </div>\
                <button class="alert-item-ack" onclick="ADMIN_NOTIFICATIONS.acknowledgeAlert(\'' + alert.id + '\')">\
                    <i class="fas fa-check"></i>\
                </button>\
            </div>';
        });

        body.innerHTML = html;
    }

    function updateAlertBadge() {
        var badge = document.getElementById('alertBadge');
        if (!badge) return;

        var unacknowledged = alertQueue.filter(function(a) { return !a.acknowledged; });
        var count = unacknowledged.length;

        badge.textContent = count > 9 ? '9+' : count;
        badge.style.display = count > 0 ? 'flex' : 'none';

        var critical = unacknowledged.some(function(a) { return a.priority === 'CRITICAL'; });
        badge.classList.toggle('critical', critical);
    }

    function acknowledgeAll() {
        alertQueue.forEach(function(alert) {
            if (!alert.acknowledged) {
                alert.acknowledged = true;
                alert.acknowledgedBy = 'admin@quicksms.com';
                alert.acknowledgedAt = new Date().toISOString();
            }
        });

        if (typeof AdminControlPlane !== 'undefined') {
            AdminControlPlane.logAdminAction('ALL_ALERTS_ACKNOWLEDGED', 'BATCH', {
                count: alertQueue.length
            }, 'LOW');
        }

        updateAlertBadge();
        renderAlertPanel();
    }

    function showNotificationSentToast(notification) {
        var notificationType = CUSTOMER_NOTIFICATION_TYPES[notification.type];
        showToast('Customer Notified', 'Email sent to ' + maskEmail(notification.customerEmail), notificationType.color, notificationType.icon);
    }

    function showInternalAlertToast(alert, alertType) {
        showToast(alertType.title, alert.requestId + ' - ' + (alert.details || ''), alertType.color, alertType.icon);
    }

    function showToast(title, message, color, icon) {
        var existingToasts = document.querySelectorAll('.notification-toast');
        var topOffset = 20 + (existingToasts.length * 90);

        var toastHtml = '\
        <div class="notification-toast" style="border-left-color:' + color + '; top:' + topOffset + 'px">\
            <div class="toast-icon" style="color:' + color + '"><i class="fas ' + icon + '"></i></div>\
            <div class="toast-content">\
                <div class="toast-title">' + title + '</div>\
                <div class="toast-message">' + message + '</div>\
            </div>\
            <button class="toast-close" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>\
        </div>';

        document.body.insertAdjacentHTML('beforeend', toastHtml);

        var toast = document.querySelector('.notification-toast:last-child');
        setTimeout(function() { toast.classList.add('show'); }, 50);
        setTimeout(function() { 
            toast.classList.remove('show');
            setTimeout(function() { toast.remove(); }, 300);
        }, 5000);
    }

    function playAlertSound(priority) {
        console.log('[AdminNotifications] Alert sound:', priority);
    }

    function startSlaMonitoring() {
        console.log('[AdminNotifications] SLA monitoring started');
    }

    function checkSlaBreach(requestId, submittedAt, assetType) {
        var now = new Date().getTime();
        var submitted = new Date(submittedAt).getTime();
        var elapsed = now - submitted;
        var threshold = assetType === 'RCS Agent' ? SLA_THRESHOLDS.rcsAgentReview : SLA_THRESHOLDS.senderIdReview;

        if (elapsed >= threshold) {
            triggerInternalAlert('SLA_BREACH', requestId, 'Review SLA exceeded for ' + assetType);
            return 'BREACHED';
        } else if (elapsed >= threshold * SLA_THRESHOLDS.warningPercent) {
            triggerInternalAlert('SLA_IMMINENT', requestId, 'SLA breach imminent for ' + assetType);
            return 'WARNING';
        }
        return 'OK';
    }

    function formatTimeAgo(isoString) {
        var diff = Date.now() - new Date(isoString).getTime();
        var minutes = Math.floor(diff / 60000);
        if (minutes < 1) return 'Just now';
        if (minutes < 60) return minutes + 'm ago';
        var hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + 'h ago';
        return Math.floor(hours / 24) + 'd ago';
    }

    function maskEmail(email) {
        if (!email) return '';
        var parts = email.split('@');
        if (parts.length !== 2) return email;
        var local = parts[0];
        var masked = local.charAt(0) + '***' + local.charAt(local.length - 1);
        return masked + '@' + parts[1];
    }

    function showCustomerNotificationModal(type, requestId, assetType, customerEmail, onSend) {
        var notificationType = CUSTOMER_NOTIFICATION_TYPES[type];
        if (!notificationType) return;

        var existingModal = document.getElementById('customerNotificationModal');
        if (existingModal) existingModal.remove();

        var templateContent = getNotificationTemplate(type, assetType, requestId);

        var modalHtml = '\
        <div class="modal fade" id="customerNotificationModal" tabindex="-1">\
            <div class="modal-dialog modal-lg">\
                <div class="modal-content">\
                    <div class="modal-header" style="background:' + notificationType.color + ';color:#fff;">\
                        <h5 class="modal-title"><i class="fas ' + notificationType.icon + ' me-2"></i>Send Customer Notification</h5>\
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>\
                    </div>\
                    <div class="modal-body">\
                        <div class="notification-preview">\
                            <div class="preview-header">\
                                <div class="preview-label">To:</div>\
                                <div class="preview-value">' + customerEmail + '</div>\
                            </div>\
                            <div class="preview-header">\
                                <div class="preview-label">Subject:</div>\
                                <div class="preview-value">' + notificationType.subject.replace('{assetType}', assetType) + '</div>\
                            </div>\
                            <div class="preview-body">\
                                ' + templateContent + '\
                            </div>\
                            <div class="custom-message-section">\
                                <label class="form-label">Additional Message (Optional)</label>\
                                <textarea id="customNotificationMessage" class="form-control" rows="3" placeholder="Add a personal note to the customer..."></textarea>\
                            </div>\
                        </div>\
                    </div>\
                    <div class="modal-footer">\
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>\
                        <button type="button" class="btn btn-primary" onclick="ADMIN_NOTIFICATIONS.confirmSendNotification(\'' + type + '\', \'' + requestId + '\', \'' + assetType + '\', \'' + customerEmail + '\')" style="background:' + notificationType.color + ';border-color:' + notificationType.color + '">\
                            <i class="fas fa-paper-plane me-1"></i> Send Notification\
                        </button>\
                    </div>\
                </div>\
            </div>\
        </div>';

        document.body.insertAdjacentHTML('beforeend', modalHtml);
        new bootstrap.Modal(document.getElementById('customerNotificationModal')).show();
    }

    function getNotificationTemplate(type, assetType, requestId) {
        var templates = {
            'RETURNED': '\
                <p>Dear Customer,</p>\
                <p>Your <strong>' + assetType + '</strong> registration (Reference: <code>' + requestId + '</code>) requires some updates before we can proceed with approval.</p>\
                <div class="template-section">\
                    <strong>Reason for Return:</strong>\
                    <p>[Reason will be inserted here]</p>\
                </div>\
                <p>Please log in to your QuickSMS portal to review and update your submission.</p>\
                <p>If you have questions, please contact our support team.</p>',
            'APPROVED': '\
                <p>Dear Customer,</p>\
                <p>Great news! Your <strong>' + assetType + '</strong> registration (Reference: <code>' + requestId + '</code>) has been approved.</p>\
                <div class="template-section success">\
                    <strong>What happens next?</strong>\
                    <p>Your ' + assetType + ' is now active and ready to use in your messaging campaigns.</p>\
                </div>\
                <p>Log in to your QuickSMS portal to start using your new ' + assetType + '.</p>',
            'REJECTED': '\
                <p>Dear Customer,</p>\
                <p>We regret to inform you that your <strong>' + assetType + '</strong> registration (Reference: <code>' + requestId + '</code>) could not be approved at this time.</p>\
                <div class="template-section error">\
                    <strong>Reason for Rejection:</strong>\
                    <p>[Reason will be inserted here]</p>\
                </div>\
                <p>You may submit a new registration after addressing the issues mentioned above.</p>\
                <p>If you believe this decision was made in error, please contact our support team.</p>',
            'LIVE': '\
                <p>Dear Customer,</p>\
                <p>Your <strong>' + assetType + '</strong> (Reference: <code>' + requestId + '</code>) is now live and fully operational!</p>\
                <div class="template-section success">\
                    <strong>Your ' + assetType + ' is ready to use</strong>\
                    <p>You can now use this ' + assetType + ' in your messaging campaigns through the QuickSMS portal or API.</p>\
                </div>\
                <p>Thank you for choosing QuickSMS.</p>'
        };

        return templates[type] || '<p>Notification content</p>';
    }

    function confirmSendNotification(type, requestId, assetType, customerEmail) {
        var customMessage = document.getElementById('customNotificationMessage').value;
        sendCustomerNotification(type, requestId, assetType, customerEmail, customMessage);
        bootstrap.Modal.getInstance(document.getElementById('customerNotificationModal')).hide();
    }

    function getAlertQueue() {
        return alertQueue;
    }

    function getNotificationQueue() {
        return notificationQueue;
    }

    return {
        init: init,
        sendCustomerNotification: sendCustomerNotification,
        triggerInternalAlert: triggerInternalAlert,
        acknowledgeAlert: acknowledgeAlert,
        acknowledgeAll: acknowledgeAll,
        toggleAlertPanel: toggleAlertPanel,
        showCustomerNotificationModal: showCustomerNotificationModal,
        confirmSendNotification: confirmSendNotification,
        checkSlaBreach: checkSlaBreach,
        getAlertQueue: getAlertQueue,
        getNotificationQueue: getNotificationQueue,
        CUSTOMER_NOTIFICATION_TYPES: CUSTOMER_NOTIFICATION_TYPES,
        INTERNAL_ALERT_TYPES: INTERNAL_ALERT_TYPES
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    if (document.body.classList.contains('admin-layout') || document.querySelector('[data-admin-page]')) {
        ADMIN_NOTIFICATIONS.init();
    }
});
