(function(window) {
    'use strict';

    var CampaignService = {
        config: {
            baseUrl: '/api/campaigns'
        },

        _headers: function() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            };
        },

        _handleResponse: function(response) {
            if (response.status === 422) {
                return response.json().then(function(err) {
                    var error = new Error(err.message || 'Validation failed');
                    error.validationErrors = err.errors || {};
                    throw error;
                });
            }
            if (response.status === 429) {
                var error = new Error('Too many requests. Please wait a moment and try again.');
                throw error;
            }
            if (!response.ok) {
                return response.json().then(function(err) {
                    throw new Error(err.message || 'Request failed: ' + response.status);
                });
            }
            return response.json();
        },

        list: function(params) {
            var qs = new URLSearchParams(params || {}).toString();
            return fetch(this.config.baseUrl + (qs ? '?' + qs : ''), {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        create: function(data) {
            return fetch(this.config.baseUrl, {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify(data)
            }).then(this._handleResponse);
        },

        get: function(id) {
            return fetch(this.config.baseUrl + '/' + id, {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        update: function(id, data) {
            return fetch(this.config.baseUrl + '/' + id, {
                method: 'PUT',
                headers: this._headers(),
                body: JSON.stringify(data)
            }).then(this._handleResponse);
        },

        delete: function(id) {
            return fetch(this.config.baseUrl + '/' + id, {
                method: 'DELETE',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        applyTemplate: function(campaignId, templateId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/apply-template', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ template_id: templateId })
            }).then(this._handleResponse);
        },

        previewRecipients: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/recipients/preview', {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        resolveRecipients: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/recipients/resolve', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        listRecipients: function(campaignId, params) {
            var qs = new URLSearchParams(params || {}).toString();
            return fetch(this.config.baseUrl + '/' + campaignId + '/recipients' + (qs ? '?' + qs : ''), {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        estimateCost: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/estimate-cost', {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        validate: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/validate', {
                headers: this._headers()
            }).then(this._handleResponse);
        },

        sendNow: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/send', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        schedule: function(campaignId, scheduledAt, timezone) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/schedule', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ scheduled_at: scheduledAt, timezone: timezone })
            }).then(this._handleResponse);
        },

        pause: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/pause', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        resume: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/resume', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        cancel: function(campaignId) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/cancel', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        clone: function(campaignId, newName) {
            return fetch(this.config.baseUrl + '/' + campaignId + '/clone', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ name: newName })
            }).then(this._handleResponse);
        }
    };

    window.CampaignService = CampaignService;
})(window);
