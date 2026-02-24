(function(window) {
    'use strict';

    var TemplateService = {
        config: {
            baseUrl: '/api/message-templates'
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

        toggleFavourite: function(id) {
            return fetch(this.config.baseUrl + '/' + id + '/toggle-favourite', {
                method: 'POST',
                headers: this._headers()
            }).then(this._handleResponse);
        },

        analyseContent: function(content) {
            return fetch(this.config.baseUrl + '/analyse-content', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ content: content })
            }).then(this._handleResponse);
        }
    };

    window.TemplateService = TemplateService;
})(window);
