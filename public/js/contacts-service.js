/**
 * ContactsService
 * Backend-ready abstraction layer for Contact Management
 * 
 * All methods return Promises for async operation.
 * Mock data mode for development (configurable via ContactsService.config.useMockData)
 * Easy swap to real endpoints by changing config only.
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
            useMockData: true,
            baseUrl: '/api/contacts',
            mockDelay: { min: 300, max: 800 }
        },

        _mockDelay: function() {
            var delay = Math.random() * (this.config.mockDelay.max - this.config.mockDelay.min) + this.config.mockDelay.min;
            return new Promise(function(resolve) { setTimeout(resolve, delay); });
        },

        bulkAddToList: function(contactIds, listName) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    console.log('[ContactsService] bulkAddToList:', { contactIds: contactIds, listName: listName });
                    return {
                        success: true,
                        message: 'Added ' + contactIds.length + ' contact(s) to "' + listName + '"',
                        affectedCount: contactIds.length
                    };
                });
            }

            return fetch(this.config.baseUrl + '/bulk/add-to-list', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: JSON.stringify({ contact_ids: contactIds, list_name: listName })
            }).then(function(response) { return response.json(); });
        },

        bulkRemoveFromList: function(contactIds, listName) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    console.log('[ContactsService] bulkRemoveFromList:', { contactIds: contactIds, listName: listName });
                    return {
                        success: true,
                        message: 'Removed ' + contactIds.length + ' contact(s) from "' + listName + '"',
                        affectedCount: contactIds.length
                    };
                });
            }

            return fetch(this.config.baseUrl + '/bulk/remove-from-list', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: JSON.stringify({ contact_ids: contactIds, list_name: listName })
            }).then(function(response) { return response.json(); });
        },

        bulkAddTags: function(contactIds, tags) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    console.log('[ContactsService] bulkAddTags:', { contactIds: contactIds, tags: tags });
                    return {
                        success: true,
                        message: 'Added tag(s) "' + tags.join(', ') + '" to ' + contactIds.length + ' contact(s)',
                        affectedCount: contactIds.length
                    };
                });
            }

            return fetch(this.config.baseUrl + '/bulk/add-tags', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: JSON.stringify({ contact_ids: contactIds, tags: tags })
            }).then(function(response) { return response.json(); });
        },

        bulkRemoveTags: function(contactIds, tags) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    console.log('[ContactsService] bulkRemoveTags:', { contactIds: contactIds, tags: tags });
                    return {
                        success: true,
                        message: 'Removed tag(s) "' + tags.join(', ') + '" from ' + contactIds.length + ' contact(s)',
                        affectedCount: contactIds.length
                    };
                });
            }

            return fetch(this.config.baseUrl + '/bulk/remove-tags', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: JSON.stringify({ contact_ids: contactIds, tags: tags })
            }).then(function(response) { return response.json(); });
        },

        bulkDelete: function(contactIds) {
            var self = this;

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    console.log('[ContactsService] bulkDelete:', { contactIds: contactIds });
                    return {
                        success: true,
                        message: 'Deleted ' + contactIds.length + ' contact(s)',
                        affectedCount: contactIds.length
                    };
                });
            }

            return fetch(this.config.baseUrl + '/bulk/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: JSON.stringify({ contact_ids: contactIds })
            }).then(function(response) { return response.json(); });
        },

        bulkExport: function(contactIds, fields, format) {
            var self = this;
            format = format || 'csv';

            if (this.config.useMockData) {
                return this._mockDelay().then(function() {
                    console.log('[ContactsService] bulkExport:', { contactIds: contactIds, fields: fields, format: format });
                    return {
                        success: true,
                        message: 'Export of ' + contactIds.length + ' contact(s) initiated',
                        downloadUrl: '/downloads/contacts-export-' + Date.now() + '.' + format,
                        affectedCount: contactIds.length
                    };
                });
            }

            return fetch(this.config.baseUrl + '/bulk/export', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
                body: JSON.stringify({ contact_ids: contactIds, fields: fields, format: format })
            }).then(function(response) { return response.json(); });
        }
    };

    window.ContactsService = ContactsService;

})(window);
