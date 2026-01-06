/**
 * RCS Preview Renderer - Shared Component
 * 
 * This file provides unified rendering functions for RCS message previews.
 * Used by both the Send Message screen and RCS Preview Demo page.
 */

var RcsPreviewRenderer = (function() {
    'use strict';

    var RCS_CONSTRAINTS = {
        maxCarouselCards: 10,
        maxButtonsPerCard: 4,
        maxTitleLength: 200,
        maxDescriptionLength: 2000,
        maxButtonLabelLength: 25
    };

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getMediaHeight(height) {
        var heights = { short: '112px', medium: '168px', tall: '264px' };
        return heights[height] || '168px';
    }

    function getButtonIcon(actionType) {
        var icons = {
            url: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>',
            dial: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
            phone: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
            calendar: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>',
            reply: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>'
        };
        return icons[actionType] || icons.url;
    }

    function renderButton(button) {
        var actionType = button.action ? button.action.type : (button.type || 'url');
        var icon = getButtonIcon(actionType);
        var label = button.label || 'Button';
        return '<button type="button" class="rcs-button">' + 
            (icon ? '<span class="rcs-button-icon-wrapper">' + icon + '</span>' : '') + 
            '<span class="rcs-button-label">' + escapeHtml(label) + '</span></button>';
    }

    function renderButtons(buttons) {
        if (!buttons || buttons.length === 0) return '';
        var html = '<div class="rcs-buttons">';
        buttons.forEach(function(btn) {
            html += renderButton(btn);
        });
        html += '</div>';
        return html;
    }

    function renderMedia(media, heightOverride) {
        if (!media) return '';
        var height = heightOverride || media.height || 'medium';
        if (height === 'none') return '';
        var heightPx = getMediaHeight(height);
        
        // Prefer hostedUrl (saved/cropped image) over original url
        var imageUrl = media.hostedUrl || media.url;
        if (imageUrl) {
            return '<div class="rcs-media rcs-media--' + height + '" style="height: ' + heightPx + ';">' +
                '<img src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(media.altText || '') + '" class="rcs-media-image" loading="lazy"/>' +
                '</div>';
        } else {
            return '<div class="rcs-media rcs-media--' + height + '" style="height: ' + heightPx + '; background: #e0e0e0; display: flex; align-items: center; justify-content: center;">' +
                '<span style="color: #888; font-size: 12px;">No media</span></div>';
        }
    }

    function renderRichCard(card, options) {
        options = options || {};
        var isCarousel = options.isCarousel || false;
        var heightOverride = options.heightOverride || null;
        
        var mediaHtml = renderMedia(card.media, heightOverride);
        var titleHtml = card.title ? '<h3 class="rcs-card-title">' + escapeHtml(card.title) + '</h3>' : '';
        var descHtml = card.description ? '<p class="rcs-card-description">' + escapeHtml(card.description) + '</p>' : '';
        var buttonsHtml = renderButtons(card.buttons);
        
        var cardClass = isCarousel ? 'rcs-card rcs-carousel-card' : 'rcs-card';
        var mediaClass = card.media && (card.media.hostedUrl || card.media.url) ? 'rcs-card--has-media' : 'rcs-card--no-media';
        
        return '<div class="' + cardClass + ' ' + mediaClass + '">' + 
            mediaHtml + 
            '<div class="rcs-card-content">' + titleHtml + descHtml + '</div>' + 
            buttonsHtml + 
            '</div>';
    }

    function renderCarousel(carousel) {
        var cardWidth = carousel.cardWidth === 'small' ? '200px' : '256px';
        var cards = carousel.cards || [];
        
        var cardsHtml = '';
        cards.forEach(function(card) {
            cardsHtml += '<div class="rcs-carousel-item" style="min-width: ' + cardWidth + '; max-width: ' + cardWidth + ';">' + 
                renderRichCard(card, { isCarousel: true }) + 
                '</div>';
        });
        
        var dotsHtml = '';
        cards.forEach(function(_, i) {
            dotsHtml += '<button class="rcs-carousel-dot ' + (i === 0 ? 'active' : '') + '" data-index="' + i + '"></button>';
        });
        
        return '<div class="rcs-carousel">' +
            '<div class="rcs-carousel-track">' + cardsHtml + '</div>' +
            '<div class="rcs-carousel-indicators">' + dotsHtml + '</div>' +
            '</div>';
    }

    function renderTextBubble(text, options) {
        options = options || {};
        var variant = options.variant || 'rcs';
        var bubbleClass = variant === 'sms' ? 'message-bubble message-bubble--sms' : 'message-bubble message-bubble--rcs';
        
        if (!text) {
            return '<div class="' + bubbleClass + ' message-bubble--placeholder">Your message...</div>';
        }
        return '<div class="' + bubbleClass + '">' + escapeHtml(text) + '</div>';
    }

    function renderMessage(message, options) {
        options = options || {};
        if (!message) return '';
        
        if (message.type === 'rich_card' && message.content) {
            return '<div class="rcs-message">' + renderRichCard(message.content) + '</div>';
        } else if (message.type === 'carousel' && message.content) {
            return '<div class="rcs-message">' + renderCarousel(message.content) + '</div>';
        } else if (message.type === 'text') {
            var bubbleVariant = options.channel === 'sms' ? 'sms' : 'rcs';
            return '<div class="rcs-message">' + renderTextBubble(message.content ? message.content.body : '', { variant: bubbleVariant }) + '</div>';
        }
        return '';
    }

    function renderAgentHeader(agent) {
        var badge = agent.verified !== false ? 
            '<svg class="rcs-verified-badge" viewBox="0 0 24 24" fill="#1a73e8"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>' : '';
        var tagline = agent.tagline ? '<span class="rcs-agent-tagline">' + escapeHtml(agent.tagline) + '</span>' : '';
        
        return '<div class="rcs-header">' +
            '<button class="rcs-back-button"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg></button>' +
            '<div class="rcs-agent-info">' +
                '<div class="rcs-agent-logo-wrapper">' +
                    '<img src="' + escapeHtml(agent.logo) + '" alt="' + escapeHtml(agent.name) + '" class="rcs-agent-logo"/>' + badge +
                '</div>' +
                '<div class="rcs-agent-details">' +
                    '<span class="rcs-agent-name">' + escapeHtml(agent.name) + '</span>' + tagline +
                '</div>' +
            '</div>' +
            '<div class="rcs-header-actions">' +
                '<button class="rcs-header-btn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg></button>' +
                '<button class="rcs-header-btn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg></button>' +
            '</div>' +
        '</div>';
    }

    function renderSmsHeader(senderId) {
        return '<div class="rcs-header rcs-header--sms">' +
            '<button class="rcs-back-button"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg></button>' +
            '<div class="rcs-agent-info">' +
                '<div class="rcs-agent-details">' +
                    '<span class="rcs-agent-name rcs-sender-id">' + escapeHtml(senderId || 'Sender') + '</span>' +
                '</div>' +
            '</div>' +
            '<div class="rcs-header-actions">' +
                '<button class="rcs-header-btn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg></button>' +
                '<button class="rcs-header-btn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg></button>' +
            '</div>' +
        '</div>';
    }

    function renderSmsInputBar() {
        return '<div class="rcs-input-bar rcs-input-bar--sms">' +
            '<input type="text" class="rcs-input-field" placeholder="SMS message" readonly/>' +
            '<button class="rcs-send-button rcs-send-button--sms"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg></button>' +
        '</div>';
    }

    function renderPhoneFrame(agent, messageContent, options) {
        options = options || {};
        var channel = options.channel || 'rcs';
        var senderId = options.senderId || 'Sender';
        var inputPlaceholder = options.inputPlaceholder || (channel === 'sms' ? 'SMS message' : 'RCS message');
        
        var frameClass = channel === 'sms' ? 'rcs-phone-frame rcs-phone-frame--sms' : 'rcs-phone-frame';
        var headerHtml = channel === 'sms' ? renderSmsHeader(senderId) : renderAgentHeader(agent || { name: 'Agent', logo: '', verified: true });
        var inputBarHtml = channel === 'sms' ? renderSmsInputBar() : 
            '<div class="rcs-input-bar">' +
                '<button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg></button>' +
                '<input type="text" class="rcs-input-field" placeholder="' + escapeHtml(inputPlaceholder) + '" readonly/>' +
                '<button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg></button>' +
                '<button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg></button>' +
                '<button class="rcs-send-button"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 14c1.66 0 2.99-1.34 2.99-3L15 5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/></svg></button>' +
            '</div>';
        
        return '<div class="' + frameClass + '">' +
            '<div class="rcs-status-bar">' +
                '<span class="rcs-status-time">9:30</span>' +
                '<div class="rcs-status-icons">' +
                    '<span class="rcs-status-5g">5G</span>' +
                    '<svg class="rcs-status-signal" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M2 22h20V2L2 22zm18-2H6.83L20 6.83V20z"/></svg>' +
                    '<svg class="rcs-status-battery" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z"/></svg>' +
                '</div>' +
            '</div>' +
            headerHtml +
            '<div class="rcs-chat-area">' +
                '<div class="rcs-timestamp">Today</div>' +
                messageContent +
            '</div>' +
            inputBarHtml +
        '</div>';
    }

    function renderPreview(config) {
        config = config || {};
        var channel = config.channel || 'rich_rcs';
        var message = config.message || null;
        var agent = config.agent || null;
        var senderId = config.senderId || 'Sender';
        
        var messageHtml = '';
        var frameOptions = {};
        
        if (channel === 'sms') {
            frameOptions = { channel: 'sms', senderId: senderId, inputPlaceholder: 'SMS message' };
            if (message && message.type === 'text') {
                messageHtml = renderMessage(message, { channel: 'sms' });
            } else {
                messageHtml = '<div class="rcs-message">' + renderTextBubble(message ? message.body : '', { variant: 'sms' }) + '</div>';
            }
        } else if (channel === 'basic_rcs') {
            frameOptions = { channel: 'rcs', inputPlaceholder: 'RCS message' };
            if (message && message.type === 'text') {
                messageHtml = renderMessage(message, { channel: 'rcs' });
            } else {
                messageHtml = '<div class="rcs-message">' + renderTextBubble(message ? message.body : '', { variant: 'rcs' }) + '</div>';
            }
        } else {
            frameOptions = { channel: 'rcs', inputPlaceholder: 'RCS message' };
            messageHtml = message ? renderMessage(message, { channel: 'rcs' }) : '';
        }
        
        return renderPhoneFrame(agent, messageHtml, frameOptions);
    }

    function initCarouselBehavior(containerSelector) {
        var container = containerSelector ? document.querySelector(containerSelector) : document;
        var carousel = container.querySelector('.rcs-carousel-track');
        if (!carousel) return;
        
        var dots = container.querySelectorAll('.rcs-carousel-dot');
        
        carousel.addEventListener('scroll', function() {
            var scrollLeft = carousel.scrollLeft;
            var itemWidth = carousel.firstElementChild ? carousel.firstElementChild.clientWidth : 256;
            var currentIndex = Math.round(scrollLeft / (itemWidth + 8));
            dots.forEach(function(dot, i) {
                dot.classList.toggle('active', i === currentIndex);
            });
        });
        
        dots.forEach(function(dot, i) {
            dot.addEventListener('click', function() {
                var itemWidth = carousel.firstElementChild ? carousel.firstElementChild.clientWidth : 256;
                carousel.scrollTo({ left: i * (itemWidth + 8), behavior: 'smooth' });
            });
        });
    }

    function validateRcsMessage(message) {
        var errors = [];
        var warnings = [];
        
        if (!message) {
            return { valid: false, errors: [{ field: 'message', message: 'Message is required' }], warnings: [] };
        }
        
        if (message.type === 'carousel') {
            var carousel = message.content;
            if (carousel.cards && carousel.cards.length > RCS_CONSTRAINTS.maxCarouselCards) {
                errors.push({ field: 'content.cards', message: 'Too many cards. Maximum is ' + RCS_CONSTRAINTS.maxCarouselCards });
            }
            if (carousel.cards) {
                carousel.cards.forEach(function(card, i) {
                    if (card.buttons && card.buttons.length > RCS_CONSTRAINTS.maxButtonsPerCard) {
                        errors.push({ field: 'content.cards[' + i + '].buttons', message: 'Too many buttons. Maximum is ' + RCS_CONSTRAINTS.maxButtonsPerCard });
                    }
                });
            }
        } else if (message.type === 'rich_card') {
            var card = message.content;
            if (card.buttons && card.buttons.length > RCS_CONSTRAINTS.maxButtonsPerCard) {
                errors.push({ field: 'content.buttons', message: 'Too many buttons. Maximum is ' + RCS_CONSTRAINTS.maxButtonsPerCard });
            }
        }
        
        return { valid: errors.length === 0, errors: errors, warnings: warnings };
    }

    return {
        escapeHtml: escapeHtml,
        getMediaHeight: getMediaHeight,
        getButtonIcon: getButtonIcon,
        renderButton: renderButton,
        renderButtons: renderButtons,
        renderMedia: renderMedia,
        renderRichCard: renderRichCard,
        renderCarousel: renderCarousel,
        renderTextBubble: renderTextBubble,
        renderMessage: renderMessage,
        renderAgentHeader: renderAgentHeader,
        renderSmsHeader: renderSmsHeader,
        renderPhoneFrame: renderPhoneFrame,
        renderPreview: renderPreview,
        initCarouselBehavior: initCarouselBehavior,
        validateRcsMessage: validateRcsMessage,
        RCS_CONSTRAINTS: RCS_CONSTRAINTS
    };
})();
