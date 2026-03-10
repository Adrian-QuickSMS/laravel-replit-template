/**
 * Security Helpers
 * Shared XSS prevention utilities for all Blade views.
 */
(function(window) {
    'use strict';

    /**
     * Escape HTML entities to prevent XSS when inserting user-controlled
     * strings into innerHTML. Use this for ANY value that originates from
     * user input (contact names, tag names, list names, mobile numbers, etc.).
     *
     * @param {string|number|null|undefined} str
     * @returns {string} Escaped string safe for innerHTML insertion
     */
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Mask a mobile number for display (e.g., "+44 77** ***123").
     * Use this instead of storing full PII in data-* attributes.
     *
     * @param {string} mobile Full mobile number
     * @returns {string} Masked mobile number
     */
    function maskMobile(mobile) {
        if (!mobile || mobile.length < 6) return mobile || '';
        var last3 = mobile.slice(-3);
        var prefix = mobile.slice(0, 4);
        return prefix + '** ***' + last3;
    }

    window.escapeHtml = escapeHtml;
    window.maskMobile = maskMobile;
})(window);
