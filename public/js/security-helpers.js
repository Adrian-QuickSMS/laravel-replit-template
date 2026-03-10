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

    /**
     * Sanitize a string for safe use in HTML attributes (data-*, href, etc.).
     * Escapes quotes and HTML entities.
     *
     * @param {string|number|null|undefined} str
     * @returns {string} Escaped string safe for attribute insertion
     */
    function escapeAttr(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/`/g, '&#96;');
    }

    /**
     * Create a DOM element safely with text content (not innerHTML).
     *
     * @param {string} tag HTML tag name
     * @param {Object} attrs Attributes to set
     * @param {string} textContent Optional text content
     * @returns {HTMLElement}
     */
    function createElement(tag, attrs, textContent) {
        var el = document.createElement(tag);
        if (attrs) {
            Object.keys(attrs).forEach(function(key) {
                if (key === 'className') {
                    el.className = attrs[key];
                } else if (key === 'style' && typeof attrs[key] === 'object') {
                    Object.assign(el.style, attrs[key]);
                } else {
                    el.setAttribute(key, attrs[key]);
                }
            });
        }
        if (textContent !== undefined) {
            el.textContent = textContent;
        }
        return el;
    }

    window.escapeHtml = escapeHtml;
    window.escapeAttr = escapeAttr;
    window.maskMobile = maskMobile;
    window.safeCreateElement = createElement;
})(window);
