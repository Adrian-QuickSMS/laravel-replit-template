/**
 * ContactsService
 * Backend-connected abstraction layer for Contact Management
 *
 * All methods return Promises for async operation.
 * Wired to /api/contacts/* endpoints (ContactBookApiController).
 *
 * Bulk Operations:
 * - bulkAddToList(contactIds, listName)
 * - bulkRemoveFromList(contactIds, listName)
 * - bulkAddTags(contactIds, tags)
 * - bulkRemoveTags(contactIds, tags)
 * - bulkDelete(contactIds)
 * - bulkExport(contactIds, fields, format)
 */

(function(window) {
    'use strict';

    var ContactsService = {
        config: {
            useMockData: false,
            baseUrl: '/api/contacts'
        },

        _headers: function() {
            return {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            };
        },

        _handleResponse: function(response) {
            if (!response.ok) {
                return response.json().then(function(err) {
                    throw new Error(err.message || 'Request failed: ' + response.status);
                });
            }
            return response.json();
        },

        bulkAddToList: function(contactIds, listName) {
            return fetch(this.config.baseUrl + '/bulk/add-to-list', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ contact_ids: contactIds, list_name: listName })
            }).then(this._handleResponse);
        },

        bulkRemoveFromList: function(contactIds, listName) {
            return fetch(this.config.baseUrl + '/bulk/remove-from-list', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ contact_ids: contactIds, list_name: listName })
            }).then(this._handleResponse);
        },

        bulkAddTags: function(contactIds, tags) {
            return fetch(this.config.baseUrl + '/bulk/add-tags', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ contact_ids: contactIds, tags: tags })
            }).then(this._handleResponse);
        },

        bulkRemoveTags: function(contactIds, tags) {
            return fetch(this.config.baseUrl + '/bulk/remove-tags', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ contact_ids: contactIds, tags: tags })
            }).then(this._handleResponse);
        },

        bulkDelete: function(contactIds) {
            return fetch(this.config.baseUrl + '/bulk/delete', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ contact_ids: contactIds })
            }).then(this._handleResponse);
        },

        bulkExport: function(contactIds, fields, format) {
            format = format || 'csv';

            return fetch(this.config.baseUrl + '/bulk/export', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ contact_ids: contactIds, fields: fields, format: format })
            }).then(this._handleResponse);
        },

        bulkAddToOptOut: function(contactIds, optOutListId) {
            return fetch(this.config.baseUrl + '/bulk/add-to-optout', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ contact_ids: contactIds, opt_out_list_id: optOutListId })
            }).then(this._handleResponse);
        },

        bulkRemoveFromOptOut: function(contactIds, optOutListId) {
            return fetch(this.config.baseUrl + '/bulk/remove-from-optout', {
                method: 'POST',
                headers: this._headers(),
                body: JSON.stringify({ contact_ids: contactIds, opt_out_list_id: optOutListId })
            }).then(this._handleResponse);
        }
    };

    window.ContactsService = ContactsService;

})(window);
