/**
 * RCS Content Wizard - Shared JavaScript
 * Used by both Send Message and Inbox pages
 */

var rcsCardCount = 1;
var rcsCurrentCard = 1;
var rcsMaxCards = 10;
var rcsMaxButtons = 4;

var rcsCardsData = {};
var rcsPersistentPayload = null;

var rcsMediaData = {
    source: null,
    url: null,
    file: null,
    dimensions: null,
    fileSize: 0,
    assetUuid: null,
    hostedUrl: null,
    originalUrl: null
};

var rcsAllowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
var rcsMaxFileSize = 100 * 1024 * 1024;
var rcsWarnFileSize = 2 * 1024 * 1024;
var rcsDraftSession = generateDraftSession();
var rcsEditDebounceTimer = null;

var rcsCropState = {
    imageWidth: 0,
    imageHeight: 0,
    displayScale: 1,
    zoom: 100,
    offsetX: 0,
    offsetY: 0,
    isDragging: false,
    startX: 0,
    startY: 0,
    startOffsetX: 0,
    startOffsetY: 0,
    frameWidth: 280,
    frameHeight: 112,
    orientation: 'vertical_short'
};

var rcsSingleCardFrameSizes = {
    'vertical_short': { width: 200, height: 100, aspectRatio: 2 },
    'vertical_medium': { width: 240, height: 120, aspectRatio: 2 },
    'vertical_tall': { width: 280, height: 140, aspectRatio: 2 }
};

var rcsCarouselFrameSizes = {
    'small': {
        'vertical_short': { width: 180, height: 112 },
        'vertical_medium': { width: 180, height: 168 }
    },
    'medium': {
        'vertical_short': { width: 296, height: 112 },
        'vertical_medium': { width: 296, height: 168 },
        'vertical_tall': { width: 296, height: 264 }
    }
};

var rcsCurrentCardWidth = 'medium';
var rcsCarouselHeight = 'vertical_short';
var rcsCarouselWidth = 'medium';

function isRcsCarouselMode() {
    var carouselEl = document.getElementById('rcsTypeCarousel');
    return carouselEl ? carouselEl.checked : false;
}

var rcsImageDirtyState = {
    isDirty: false,
    baselineZoom: 100,
    baselineCropPosition: 'center',
    baselineOrientation: 'vertical_short',
    baselineOffsetX: 0,
    baselineOffsetY: 0,
    pendingNavigation: null,
    hasBeenEdited: false
};

var rcsButtons = [];
var rcsEditingButtonIndex = -1;
var rcsActiveTextField = null;
var rcsCrosshairHideTimer = null;

function generateDraftSession() {
    return 'draft_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

function initializeRcsCard(cardNum) {
    if (!rcsCardsData[cardNum]) {
        rcsCardsData[cardNum] = {
            media: {
                source: null,
                url: null,
                file: null,
                fileName: null,
                fileSize: 0,
                dimensions: null,
                orientation: 'vertical_short',
                cardWidth: 'medium',
                zoom: 100,
                cropOffsetX: 0,
                cropOffsetY: 0,
                assetUuid: null,
                hostedUrl: null,
                originalUrl: null
            },
            description: '',
            textBody: '',
            buttons: []
        };
    }
    return rcsCardsData[cardNum];
}

function openRcsWizard() {
    if (!rcsPersistentPayload && Object.keys(rcsCardsData).length === 0) {
        var hasStoredDraft = loadRcsFromStorage();
        if (!hasStoredDraft) {
            initializeRcsCard(1);
            rcsCurrentCard = 1;
            rcsCardCount = 1;
        }
    }
    
    hideRcsValidationErrors();
    
    var modal = new bootstrap.Modal(document.getElementById('rcsWizardModal'));
    modal.show();
    
    var applyBtn = document.getElementById('rcsApplyContentBtn');
    if (applyBtn) applyBtn.disabled = false;
    
    setTimeout(function() {
        initRcsCropEditor();
        initializeMessageTypeUI();
        updateCarouselOrientationWarning();
        updateRcsWizardPreview();
    }, 100);
}

function initializeMessageTypeUI() {
    var isCarousel = isRcsCarouselMode();
    var cardWidthSection = document.getElementById('rcsCardWidthSection');
    var carouselWidthHint = document.getElementById('rcsCarouselWidthHint');
    var carouselHeightHint = document.getElementById('rcsCarouselHeightHint');
    var singleCardResolutionHint = document.getElementById('rcsSingleCardResolutionHint');
    var carouselHeightNotice = document.getElementById('rcsCarouselHeightNotice');
    var carouselWidthNotice = document.getElementById('rcsCarouselWidthNotice');
    var cardWidthHeightWarning = document.getElementById('rcsCardWidthHeightWarning');
    
    if (cardWidthSection) cardWidthSection.classList.toggle('d-none', !isCarousel);
    if (carouselWidthHint) carouselWidthHint.classList.toggle('d-none', !isCarousel);
    if (carouselHeightHint) carouselHeightHint.classList.toggle('d-none', !isCarousel);
    if (singleCardResolutionHint) singleCardResolutionHint.classList.toggle('d-none', isCarousel);
    
    if (!isCarousel) {
        if (carouselHeightNotice) carouselHeightNotice.classList.add('d-none');
        if (carouselWidthNotice) carouselWidthNotice.classList.add('d-none');
        if (cardWidthHeightWarning) cardWidthHeightWarning.classList.add('d-none');
    }
    
    updateCardWidthAndHeightRestrictions();
    
    var currentOrientation = document.querySelector('input[name="rcsOrientation"]:checked');
    var orientation = currentOrientation ? currentOrientation.value : 'vertical_short';
    updateRcsCropFrame(orientation);
}

function clearRcsContent() {
    resetRcsWizard();
    var summary = document.getElementById('rcsConfiguredSummary');
    if (summary) summary.classList.add('d-none');
    var summaryInbox = document.getElementById('rcsConfiguredSummaryInbox');
    if (summaryInbox) summaryInbox.classList.add('d-none');
    var clearBtn = document.getElementById('rcsClearBtnInbox');
    if (clearBtn) clearBtn.classList.add('d-none');
    var wizardBtnText = document.getElementById('rcsWizardBtnTextInbox');
    if (wizardBtnText) wizardBtnText.textContent = 'Create RCS Message';
    sessionStorage.removeItem('quicksms_rcs_draft');
}

function saveCurrentCardData() {
    var card = initializeRcsCard(rcsCurrentCard);
    
    card.media.source = rcsMediaData.source;
    card.media.url = rcsMediaData.url;
    card.media.file = rcsMediaData.file;
    card.media.fileName = rcsMediaData.file ? rcsMediaData.file.name : null;
    card.media.fileSize = rcsMediaData.fileSize;
    card.media.dimensions = rcsMediaData.dimensions;
    card.media.assetUuid = rcsMediaData.assetUuid;
    card.media.hostedUrl = rcsMediaData.hostedUrl;
    card.media.originalUrl = rcsMediaData.originalUrl;
    
    var orientChecked = document.querySelector('input[name="rcsOrientation"]:checked');
    card.media.orientation = orientChecked ? orientChecked.value : 'vertical_short';
    var widthChecked = document.querySelector('input[name="rcsCardWidth"]:checked');
    card.media.cardWidth = widthChecked ? widthChecked.value : 'medium';
    
    rcsCarouselHeight = card.media.orientation;
    rcsCarouselWidth = card.media.cardWidth;
    
    card.media.zoom = rcsCropState.zoom;
    card.media.cropOffsetX = rcsCropState.offsetX;
    card.media.cropOffsetY = rcsCropState.offsetY;
    
    var descEl = document.getElementById('rcsDescription');
    var bodyEl = document.getElementById('rcsTextBody');
    card.description = descEl ? descEl.value : '';
    card.textBody = bodyEl ? bodyEl.value : '';
    card.buttons = JSON.parse(JSON.stringify(rcsButtons));
}

function loadCardData(cardNum) {
    var card = initializeRcsCard(cardNum);
    
    rcsMediaData.source = card.media.source;
    rcsMediaData.url = card.media.url;
    rcsMediaData.file = card.media.file;
    rcsMediaData.fileSize = card.media.fileSize;
    rcsMediaData.dimensions = card.media.dimensions;
    rcsMediaData.assetUuid = card.media.assetUuid;
    rcsMediaData.hostedUrl = card.media.hostedUrl;
    rcsMediaData.originalUrl = card.media.originalUrl;
    
    if (card.media.url) {
        var cardWidth = card.media.cardWidth || 'medium';
        var widthRadio = document.getElementById('rcsCardWidth' + cardWidth.charAt(0).toUpperCase() + cardWidth.slice(1));
        if (widthRadio) widthRadio.checked = true;
        updateRcsCardWidth(cardWidth);
        
        var orientId = getOrientationRadioId(card.media.orientation);
        var orientRadio = document.getElementById(orientId);
        if (orientRadio && !orientRadio.disabled) orientRadio.checked = true;
        updateRcsCropFrame(card.media.orientation);
        
        showRcsMediaPreview(card.media.hostedUrl || card.media.url);
        updateRcsImageInfo();
        
        setTimeout(function() {
            rcsCropState.zoom = card.media.zoom || 100;
            rcsCropState.offsetX = card.media.cropOffsetX || 0;
            rcsCropState.offsetY = card.media.cropOffsetY || 0;
            var zoomSlider = document.getElementById('rcsZoomSlider');
            var zoomValue = document.getElementById('rcsZoomValue');
            if (zoomSlider) zoomSlider.value = rcsCropState.zoom;
            if (zoomValue) zoomValue.textContent = rcsCropState.zoom + '%';
            applyRcsCropTransform();
            initRcsImageBaseline();
        }, 150);
    } else {
        removeRcsMedia();
    }
    
    function getOrientationRadioId(orientation) {
        switch(orientation) {
            case 'vertical_short': return 'rcsOrientVertShort';
            case 'vertical_medium': return 'rcsOrientVertMed';
            case 'vertical_tall': return 'rcsOrientVertTall';
            default: return 'rcsOrientVertShort';
        }
    }
    
    var descEl = document.getElementById('rcsDescription');
    var bodyEl = document.getElementById('rcsTextBody');
    if (descEl) descEl.value = card.description;
    if (bodyEl) bodyEl.value = card.textBody;
    updateRcsDescriptionCount();
    updateRcsTextBodyCount();
    
    rcsButtons = JSON.parse(JSON.stringify(card.buttons));
    renderRcsButtons();
    updateRcsButtonsPreview();
}

function validateRcsContent() {
    var errors = [];
    var warnings = [];
    
    for (var i = 1; i <= rcsCardCount; i++) {
        var card = rcsCardsData[i];
        if (!card) {
            errors.push('Card ' + i + ': No data configured');
            continue;
        }
        
        if (card.media.source === 'upload' && card.media.file) {
            if (card.media.fileSize > 250 * 1024) {
                errors.push('Card ' + i + ': Media file exceeds 250KB limit (' + (card.media.fileSize / 1024).toFixed(1) + 'KB)');
            }
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (card.media.file.type && !allowedTypes.includes(card.media.file.type)) {
                errors.push('Card ' + i + ': Invalid media type. Only JPEG, PNG, GIF allowed.');
            }
        }
        
        if (card.description.length > 120) {
            warnings.push('Card ' + i + ': Description exceeds recommended 120 characters (' + card.description.length + ')');
        }
        if (card.textBody.length > 2000) {
            warnings.push('Card ' + i + ': Text body exceeds recommended 2000 characters (' + card.textBody.length + ')');
        }
        
        if (card.buttons.length > rcsMaxButtons) {
            errors.push('Card ' + i + ': Maximum ' + rcsMaxButtons + ' buttons allowed per card');
        }
        
        card.buttons.forEach(function(btn, btnIndex) {
            if (btn.label.length > 25) {
                errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Label exceeds 25 character limit');
            }
            if (btn.type === 'url' && !/^https?:\/\/.+/i.test(btn.url || '')) {
                errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Invalid URL format');
            }
            if (btn.type === 'phone' && !/^\+?[0-9\s\-()]{7,20}$/.test(btn.phone || '')) {
                errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Invalid phone number format');
            }
            if (btn.type === 'calendar') {
                if (!btn.eventTitle) errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Calendar event requires a title');
                if (!btn.eventStart) errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Calendar event requires start date/time');
                if (!btn.eventEnd) errors.push('Card ' + i + ', Button ' + (btnIndex + 1) + ': Calendar event requires end date/time');
            }
        });
    }
    
    if (rcsCardCount > rcsMaxCards) {
        errors.push('Maximum ' + rcsMaxCards + ' cards allowed in carousel');
    }
    
    return { valid: errors.length === 0, errors: errors, warnings: warnings };
}

function buildRcsPayload() {
    var isCarousel = document.getElementById('rcsTypeCarousel').checked;
    var payload = {
        type: isCarousel ? 'carousel' : 'single',
        cardCount: rcsCardCount,
        cards: [],
        placeholders: [],
        createdAt: new Date().toISOString(),
        userId: null
    };
    
    for (var i = 1; i <= rcsCardCount; i++) {
        var card = rcsCardsData[i];
        if (!card) continue;
        
        var cardPayload = {
            order: i,
            media: {
                source: card.media.source,
                url: card.media.url,
                fileName: card.media.fileName,
                fileSize: card.media.fileSize,
                dimensions: card.media.dimensions,
                orientation: card.media.orientation,
                zoom: card.media.zoom,
                cropOffsetX: card.media.cropOffsetX,
                cropOffsetY: card.media.cropOffsetY
            },
            description: card.description,
            textBody: card.textBody,
            buttons: card.buttons.map(function(btn, idx) {
                return {
                    order: idx + 1,
                    label: btn.label,
                    type: btn.type,
                    action: btn.type === 'url' ? { url: btn.url } :
                            btn.type === 'phone' ? { phoneNumber: btn.phone } :
                            btn.type === 'calendar' ? {
                                title: btn.eventTitle,
                                startTime: btn.eventStart,
                                endTime: btn.eventEnd,
                                description: btn.eventDesc || ''
                            } : null
                };
            })
        };
        
        var placeholderRegex = /\{\{([^}]+)\}\}/g;
        var match;
        while ((match = placeholderRegex.exec(card.description)) !== null) {
            if (!payload.placeholders.includes(match[1])) payload.placeholders.push(match[1]);
        }
        while ((match = placeholderRegex.exec(card.textBody)) !== null) {
            if (!payload.placeholders.includes(match[1])) payload.placeholders.push(match[1]);
        }
        
        payload.cards.push(cardPayload);
    }
    
    return payload;
}

function persistRcsPayload(payload) {
    rcsPersistentPayload = payload;
    console.log('RCS Payload persisted:', JSON.stringify(payload, null, 2));
    sessionStorage.setItem('quicksms_rcs_draft', JSON.stringify(payload));
}

function applyRcsContent() {
    saveCurrentCardData();
    
    var validation = validateRcsContent();
    
    hideRcsValidationErrors();
    
    if (!validation.valid) {
        showRcsValidationErrors(validation.errors, validation.warnings);
        return;
    }
    
    if (validation.warnings.length > 0) {
        showRcsValidationWarnings(validation.warnings);
    }
    
    var payload = buildRcsPayload();
    persistRcsPayload(payload);
    
    var summaryText = payload.type === 'carousel' 
        ? 'RCS Carousel (' + payload.cardCount + ' cards) configured'
        : 'RCS Rich Card configured';
    
    var totalButtons = payload.cards.reduce(function(sum, c) { return sum + c.buttons.length; }, 0);
    if (totalButtons > 0) {
        summaryText += ' with ' + totalButtons + ' action button' + (totalButtons > 1 ? 's' : '');
    }
    
    var configuredText = document.getElementById('rcsConfiguredText');
    var configuredSummary = document.getElementById('rcsConfiguredSummary');
    if (configuredText) configuredText.textContent = summaryText;
    if (configuredSummary) configuredSummary.classList.remove('d-none');
    
    var configuredTextInbox = document.getElementById('rcsConfiguredTextInbox');
    var configuredSummaryInbox = document.getElementById('rcsConfiguredSummaryInbox');
    var clearBtnInbox = document.getElementById('rcsClearBtnInbox');
    var wizardBtnTextInbox = document.getElementById('rcsWizardBtnTextInbox');
    if (configuredTextInbox) configuredTextInbox.textContent = summaryText;
    if (configuredSummaryInbox) configuredSummaryInbox.classList.remove('d-none');
    if (clearBtnInbox) clearBtnInbox.classList.remove('d-none');
    if (wizardBtnTextInbox) wizardBtnTextInbox.textContent = 'Edit RCS Message';
    
    bootstrap.Modal.getInstance(document.getElementById('rcsWizardModal')).hide();
    
    setTimeout(function() {
        if (typeof updateRcsWizardPreviewInMain === 'function') {
            updateRcsWizardPreviewInMain();
        }
    }, 100);
}

function showRcsValidationErrors(errors, warnings) {
    var container = document.getElementById('rcsValidationErrors');
    if (!container) return;
    
    var html = '<div class="alert alert-danger mb-3"><h6 class="mb-2"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following errors:</h6><ul class="mb-0 ps-3">';
    errors.forEach(function(err) {
        html += '<li>' + escapeHtmlRcs(err) + '</li>';
    });
    html += '</ul></div>';
    
    if (warnings.length > 0) {
        html += '<div class="alert alert-warning mb-3"><h6 class="mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Warnings:</h6><ul class="mb-0 ps-3">';
        warnings.forEach(function(warn) {
            html += '<li>' + escapeHtmlRcs(warn) + '</li>';
        });
        html += '</ul></div>';
    }
    
    container.innerHTML = html;
    container.classList.remove('d-none');
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function showRcsValidationWarnings(warnings) {
    var container = document.getElementById('rcsValidationErrors');
    if (!container || warnings.length === 0) return;
    
    var html = '<div class="alert alert-warning mb-3"><h6 class="mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Warnings (content saved with warnings):</h6><ul class="mb-0 ps-3">';
    warnings.forEach(function(warn) {
        html += '<li>' + escapeHtmlRcs(warn) + '</li>';
    });
    html += '</ul></div>';
    
    container.innerHTML = html;
    container.classList.remove('d-none');
    
    setTimeout(function() {
        container.classList.add('d-none');
    }, 5000);
}

function hideRcsValidationErrors() {
    var container = document.getElementById('rcsValidationErrors');
    if (container) {
        container.classList.add('d-none');
        container.innerHTML = '';
    }
}

function escapeHtmlRcs(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateRcsWizardPreview() {
    var container = document.getElementById('rcsWizardPreviewContainer');
    if (!container) return;
    
    var agentSelect = document.getElementById('rcsAgent');
    var agentName = 'QuickSMS Brand';
    var agentTagline = 'Business messaging';
    var agentLogo = '/images/rcs-agents/quicksms-brand.svg';
    
    if (agentSelect && agentSelect.selectedIndex > 0) {
        var selectedOption = agentSelect.options[agentSelect.selectedIndex];
        agentName = selectedOption.getAttribute('data-name') || selectedOption.text;
        agentTagline = selectedOption.getAttribute('data-tagline') || '';
        agentLogo = selectedOption.getAttribute('data-logo') || agentLogo;
    }
    
    var isCarousel = document.querySelector('input[name="rcsMessageType"]:checked')?.value === 'carousel';
    var messageHtml = '';
    
    if (isCarousel && rcsCardCount > 1) {
        messageHtml = renderRcsCarouselPreview();
    } else {
        messageHtml = renderRcsCardPreview(rcsCurrentCard);
    }
    
    container.innerHTML = renderRcsPhoneFrame({
        name: agentName,
        logo: agentLogo,
        verified: true,
        tagline: agentTagline
    }, messageHtml);
    
    initRcsCarouselBehavior();
}

function renderRcsPhoneFrame(agent, messageContent) {
    return RcsPreviewRenderer.renderPhoneFrame(agent, '<div class="rcs-message">' + messageContent + '</div>');
}

function getWizardCardSchema(cardNum) {
    var card = rcsCardsData[cardNum] || initializeRcsCard(cardNum);
    var orientChecked = document.querySelector('input[name="rcsOrientation"]:checked');
    var orientation = orientChecked ? orientChecked.value : 'vertical_short';
    var heights = { 'vertical_short': 'short', 'vertical_medium': 'medium', 'vertical_tall': 'tall' };
    var heightClass = heights[orientation] || 'medium';
    
    var descEl = document.getElementById('rcsDescription');
    var bodyEl = document.getElementById('rcsTextBody');
    var description = descEl ? descEl.value : (card.description || '');
    var textBody = bodyEl ? bodyEl.value : (card.textBody || '');
    
    var btns = rcsButtons.length > 0 ? rcsButtons : (card.buttons || []);
    
    var isCurrentCard = (cardNum === rcsCurrentCard);
    var mediaUrl = null;
    
    if (isCurrentCard) {
        mediaUrl = rcsMediaData.hostedUrl || rcsMediaData.url || null;
    } else {
        mediaUrl = (card.media && card.media.hostedUrl) ? card.media.hostedUrl : 
                   (card.media && card.media.url) ? card.media.url : null;
    }
    
    return {
        media: mediaUrl ? { url: mediaUrl, height: heightClass } : null,
        title: description || null,
        description: textBody || null,
        buttons: btns.map(function(btn) {
            return { label: btn.label || 'Button', action: { type: btn.type || 'url' } };
        })
    };
}

function getWizardCarouselCardSchema(cardNum) {
    var card = rcsCardsData[cardNum] || {};
    var mediaUrl = (card.media && card.media.hostedUrl) ? card.media.hostedUrl :
                   (card.media && card.media.url) ? card.media.url : null;
    var btns = card.buttons || [];
    
    var orientationToHeight = { 'vertical_short': 'short', 'vertical_medium': 'medium', 'vertical_tall': 'tall' };
    var mediaHeight = orientationToHeight[rcsCarouselHeight] || 'medium';
    
    return {
        media: mediaUrl ? { url: mediaUrl, height: mediaHeight } : null,
        title: card.description || null,
        description: card.textBody ? (card.textBody.length > 80 ? card.textBody.substring(0, 80) + '...' : card.textBody) : null,
        buttons: btns.map(function(btn) {
            return { label: btn.label || 'Button', action: { type: btn.type || 'url' } };
        })
    };
}

function renderRcsCardPreview(cardNum) {
    var cardSchema = getWizardCardSchema(cardNum);
    var orientChecked = document.querySelector('input[name="rcsOrientation"]:checked');
    var orientation = orientChecked ? orientChecked.value : 'vertical_short';
    var heights = { 'vertical_short': 'short', 'vertical_medium': 'medium', 'vertical_tall': 'tall' };
    var heightClass = heights[orientation] || 'medium';
    
    return RcsPreviewRenderer.renderRichCard(cardSchema, { heightOverride: heightClass });
}

function renderRcsCarouselPreview() {
    var cards = [];
    for (var i = 1; i <= rcsCardCount; i++) {
        cards.push(getWizardCarouselCardSchema(i));
    }
    
    var orientationToHeight = { 'vertical_short': 'short', 'vertical_medium': 'medium', 'vertical_tall': 'tall' };
    var mediaHeight = orientationToHeight[rcsCarouselHeight] || 'medium';
    
    return RcsPreviewRenderer.renderCarousel({ 
        cardWidth: rcsCarouselWidth, 
        mediaHeight: mediaHeight,
        cards: cards 
    });
}

function getRcsButtonIcon(type) {
    return RcsPreviewRenderer.getButtonIcon(type);
}

function initRcsCarouselBehavior() {
    RcsPreviewRenderer.initCarouselBehavior('#rcsWizardPreviewContainer');
}

function resetRcsWizard() {
    rcsCardsData = {};
    rcsCardCount = 1;
    rcsCurrentCard = 1;
    rcsButtons = [];
    rcsPersistentPayload = null;
    
    var typeSingle = document.getElementById('rcsTypeSingle');
    if (typeSingle) typeSingle.checked = true;
    toggleRcsMessageType();
    removeRcsMedia();
    var descEl = document.getElementById('rcsDescription');
    var bodyEl = document.getElementById('rcsTextBody');
    if (descEl) descEl.value = '';
    if (bodyEl) bodyEl.value = '';
    updateRcsDescriptionCount();
    updateRcsTextBodyCount();
    renderRcsButtons();
    updateRcsButtonsPreview();
    hideRcsValidationErrors();
    
    initializeRcsCard(1);
}

function loadRcsFromStorage() {
    var stored = sessionStorage.getItem('quicksms_rcs_draft');
    if (!stored) return false;
    
    try {
        var payload = JSON.parse(stored);
        
        var typeEl = document.getElementById('rcsType' + (payload.type === 'carousel' ? 'Carousel' : 'Single'));
        if (typeEl) typeEl.checked = true;
        toggleRcsMessageType();
        
        rcsCardCount = payload.cardCount;
        payload.cards.forEach(function(cardData) {
            var cardNum = cardData.order;
            rcsCardsData[cardNum] = {
                media: {
                    source: cardData.media.source,
                    url: cardData.media.url,
                    file: null,
                    fileName: cardData.media.fileName,
                    fileSize: cardData.media.fileSize,
                    dimensions: cardData.media.dimensions,
                    orientation: cardData.media.orientation,
                    zoom: cardData.media.zoom,
                    cropOffsetX: cardData.media.cropOffsetX,
                    cropOffsetY: cardData.media.cropOffsetY
                },
                description: cardData.description,
                textBody: cardData.textBody,
                buttons: cardData.buttons.map(function(btn) {
                    var buttonObj = { label: btn.label, type: btn.type };
                    if (btn.type === 'url') buttonObj.url = btn.action.url;
                    if (btn.type === 'phone') buttonObj.phone = btn.action.phoneNumber;
                    if (btn.type === 'calendar') {
                        buttonObj.eventTitle = btn.action.title;
                        buttonObj.eventStart = btn.action.startTime;
                        buttonObj.eventEnd = btn.action.endTime;
                        buttonObj.eventDesc = btn.action.description;
                    }
                    return buttonObj;
                })
            };
        });
        
        loadCardData(1);
        rcsPersistentPayload = payload;
        return true;
    } catch (e) {
        console.error('Failed to load RCS draft:', e);
        return false;
    }
}

function toggleRcsMessageType() {
    saveCurrentCardData();
    
    var isCarouselEl = document.getElementById('rcsTypeCarousel');
    var isCarousel = isCarouselEl ? isCarouselEl.checked : false;
    var carouselNav = document.getElementById('rcsCarouselNav');
    var cardLabel = document.getElementById('rcsCurrentCardLabel');
    var cardWidthSection = document.getElementById('rcsCardWidthSection');
    var carouselWidthHint = document.getElementById('rcsCarouselWidthHint');
    var carouselHeightHint = document.getElementById('rcsCarouselHeightHint');
    var singleCardResolutionHint = document.getElementById('rcsSingleCardResolutionHint');
    var carouselHeightNotice = document.getElementById('rcsCarouselHeightNotice');
    var carouselWidthNotice = document.getElementById('rcsCarouselWidthNotice');
    var cardWidthHeightWarning = document.getElementById('rcsCardWidthHeightWarning');
    
    if (carouselNav) carouselNav.classList.toggle('d-none', !isCarousel);
    if (cardLabel) cardLabel.classList.toggle('d-none', !isCarousel);
    if (cardWidthSection) cardWidthSection.classList.toggle('d-none', !isCarousel);
    if (carouselWidthHint) carouselWidthHint.classList.toggle('d-none', !isCarousel);
    if (carouselHeightHint) carouselHeightHint.classList.toggle('d-none', !isCarousel);
    if (singleCardResolutionHint) singleCardResolutionHint.classList.toggle('d-none', isCarousel);
    
    if (!isCarousel) {
        if (carouselHeightNotice) carouselHeightNotice.classList.add('d-none');
        if (carouselWidthNotice) carouselWidthNotice.classList.add('d-none');
        if (cardWidthHeightWarning) cardWidthHeightWarning.classList.add('d-none');
    }
    
    updateCardWidthAndHeightRestrictions();
    
    var currentOrientation = document.querySelector('input[name="rcsOrientation"]:checked');
    var orientation = currentOrientation ? currentOrientation.value : 'vertical_short';
    updateRcsCropFrame(orientation);
    
    if (!isCarousel) {
        for (var i = 2; i <= rcsCardCount; i++) {
            delete rcsCardsData[i];
        }
        rcsCardCount = 1;
        rcsCurrentCard = 1;
        resetRcsCardTabs();
        loadCardData(1);
    }
    updateRcsCardCount();
}

function updateCardWidthAndHeightRestrictions() {
    var isCarousel = isRcsCarouselMode();
    var tallInput = document.getElementById('rcsOrientVertTall');
    var tallLabel = document.getElementById('rcsOrientVertTallLabel');
    var warningEl = document.getElementById('rcsCardWidthHeightWarning');
    
    if (!isCarousel) {
        if (tallInput) tallInput.disabled = false;
        if (tallLabel) {
            tallLabel.classList.remove('disabled');
            tallLabel.style.opacity = '1';
            tallLabel.style.pointerEvents = 'auto';
        }
        if (warningEl) warningEl.classList.add('d-none');
    } else {
        if (rcsCurrentCardWidth === 'small') {
            if (tallInput) tallInput.disabled = true;
            if (tallLabel) {
                tallLabel.classList.add('disabled');
                tallLabel.style.opacity = '0.5';
                tallLabel.style.pointerEvents = 'none';
            }
            if (tallInput && tallInput.checked) {
                var medOrient = document.getElementById('rcsOrientVertMed');
                if (medOrient) medOrient.checked = true;
            }
        } else {
            if (tallInput) tallInput.disabled = false;
            if (tallLabel) {
                tallLabel.classList.remove('disabled');
                tallLabel.style.opacity = '1';
                tallLabel.style.pointerEvents = 'auto';
            }
        }
    }
}

function resetRcsCardTabs() {
    var tabsContainer = document.getElementById('rcsCardTabs');
    var addBtn = document.getElementById('rcsAddCardBtn');
    if (!tabsContainer) return;
    tabsContainer.querySelectorAll('.rcs-card-tab').forEach(function(tab, index) {
        if (index > 0) tab.remove();
    });
    var firstTab = tabsContainer.querySelector('.rcs-card-tab');
    if (firstTab) {
        firstTab.classList.add('active');
        firstTab.classList.remove('btn-outline-primary');
        firstTab.classList.add('btn-primary');
    }
    if (addBtn) addBtn.disabled = false;
}

function addRcsCard() {
    if (rcsCardCount >= rcsMaxCards) return;
    
    saveCurrentCardData();
    
    rcsCardCount++;
    initializeRcsCard(rcsCardCount);
    
    rcsCardsData[rcsCardCount].media.orientation = rcsCarouselHeight;
    rcsCardsData[rcsCardCount].media.cardWidth = rcsCarouselWidth;
    
    var tabsContainer = document.getElementById('rcsCardTabs');
    var addBtn = document.getElementById('rcsAddCardBtn');
    
    var newTab = document.createElement('button');
    newTab.type = 'button';
    newTab.className = 'btn btn-outline-primary btn-sm rcs-card-tab';
    newTab.setAttribute('data-card', rcsCardCount);
    newTab.textContent = 'Card ' + rcsCardCount;
    newTab.onclick = function() { selectRcsCard(rcsCardCount); };
    
    tabsContainer.insertBefore(newTab, addBtn);
    
    updateRcsCardCount();
    selectRcsCard(rcsCardCount);
    
    if (rcsCardCount >= rcsMaxCards) {
        addBtn.disabled = true;
    }
}

function deleteRcsCard(cardNum) {
    if (rcsCardCount <= 1) return;
    
    delete rcsCardsData[cardNum];
    
    var newCardsData = {};
    var newIndex = 1;
    for (var i = 1; i <= rcsCardCount; i++) {
        if (i !== cardNum && rcsCardsData[i]) {
            newCardsData[newIndex] = rcsCardsData[i];
            newIndex++;
        }
    }
    rcsCardsData = newCardsData;
    rcsCardCount--;
    
    rebuildCardTabs();
    
    if (rcsCurrentCard > rcsCardCount) {
        rcsCurrentCard = rcsCardCount;
    }
    selectRcsCard(rcsCurrentCard);
    updateRcsCardCount();
}

function rebuildCardTabs() {
    var tabsContainer = document.getElementById('rcsCardTabs');
    var addBtn = document.getElementById('rcsAddCardBtn');
    if (!tabsContainer) return;
    
    tabsContainer.querySelectorAll('.rcs-card-tab').forEach(function(tab) {
        tab.remove();
    });
    
    for (var i = 1; i <= rcsCardCount; i++) {
        var tab = document.createElement('button');
        tab.type = 'button';
        tab.className = 'btn btn-sm rcs-card-tab ' + (i === rcsCurrentCard ? 'btn-primary active' : 'btn-outline-primary');
        tab.setAttribute('data-card', i);
        tab.textContent = 'Card ' + i;
        (function(cardNum) {
            tab.onclick = function() { selectRcsCard(cardNum); };
        })(i);
        tabsContainer.insertBefore(tab, addBtn);
    }
    
    if (addBtn) addBtn.disabled = rcsCardCount >= rcsMaxCards;
}

function selectRcsCard(cardNum) {
    if (rcsCurrentCard !== cardNum && isRcsImageDirty()) {
        showRcsUnsavedChangesModal({ type: 'selectCard', cardNum: cardNum });
        return;
    }
    
    selectRcsCardDirect(cardNum);
}

function selectRcsCardDirect(cardNum) {
    if (rcsCurrentCard !== cardNum) {
        saveCurrentCardData();
        clearRcsImageDirtyState();
    }
    
    rcsCurrentCard = cardNum;
    
    document.querySelectorAll('.rcs-card-tab').forEach(function(tab) {
        var tabCard = parseInt(tab.getAttribute('data-card'));
        if (tabCard === cardNum) {
            tab.classList.remove('btn-outline-primary');
            tab.classList.add('btn-primary', 'active');
        } else {
            tab.classList.remove('btn-primary', 'active');
            tab.classList.add('btn-outline-primary');
        }
    });
    
    var cardName = document.getElementById('rcsCurrentCardName');
    if (cardName) cardName.textContent = 'Card ' + cardNum;
    loadCardData(cardNum);
}

function updateRcsCardCount() {
    var countEl = document.getElementById('rcsCardCount');
    if (countEl) countEl.textContent = rcsCardCount + ' / ' + rcsMaxCards;
}

function initRcsImageBaseline() {
    var current = getCurrentEditParams();
    rcsImageDirtyState.baselineZoom = current.zoom;
    rcsImageDirtyState.baselineCropPosition = 'center';
    rcsImageDirtyState.baselineOrientation = current.orientation;
    rcsImageDirtyState.baselineOffsetX = rcsCropState.offsetX;
    rcsImageDirtyState.baselineOffsetY = rcsCropState.offsetY;
    rcsImageDirtyState.isDirty = false;
    rcsImageDirtyState.hasBeenEdited = false;
    updateRcsSaveButtonVisibility();
}

function markRcsImageDirty() {
    if (rcsMediaData.source === 'url' && rcsMediaData.originalUrl && !rcsMediaData.hostedUrl) {
        var current = getCurrentEditParams();
        var hasChanges = current.zoom !== rcsImageDirtyState.baselineZoom ||
                         current.orientation !== rcsImageDirtyState.baselineOrientation ||
                         Math.abs(rcsCropState.offsetX - rcsImageDirtyState.baselineOffsetX) > 1 ||
                         Math.abs(rcsCropState.offsetY - rcsImageDirtyState.baselineOffsetY) > 1;
        
        rcsImageDirtyState.isDirty = hasChanges;
        if (hasChanges) rcsImageDirtyState.hasBeenEdited = true;
        updateRcsSaveButtonVisibility();
    }
}

function clearRcsImageDirtyState() {
    rcsImageDirtyState.isDirty = false;
    rcsImageDirtyState.baselineZoom = 100;
    rcsImageDirtyState.baselineCropPosition = 'center';
    rcsImageDirtyState.baselineOrientation = 'vertical_short';
    rcsImageDirtyState.baselineOffsetX = 0;
    rcsImageDirtyState.baselineOffsetY = 0;
    rcsImageDirtyState.pendingNavigation = null;
    rcsImageDirtyState.hasBeenEdited = false;
    updateRcsSaveButtonVisibility();
}

function updateRcsSaveButtonVisibility() {
    var saveBtn = document.getElementById('rcsImageSaveBtn');
    if (saveBtn) {
        var shouldShow = rcsMediaData.source === 'url' && 
                         rcsMediaData.originalUrl && 
                         !rcsMediaData.hostedUrl &&
                         rcsImageDirtyState.isDirty;
        saveBtn.classList.toggle('d-none', !shouldShow);
    }
}

function isRcsImageDirty() {
    return rcsMediaData.source === 'url' && 
           rcsMediaData.originalUrl && 
           !rcsMediaData.hostedUrl &&
           rcsImageDirtyState.isDirty;
}

function showRcsUnsavedChangesModal(pendingAction) {
    rcsImageDirtyState.pendingNavigation = pendingAction;
    var modal = new bootstrap.Modal(document.getElementById('rcsUnsavedChangesModal'));
    modal.show();
}

function hideRcsUnsavedChangesModal() {
    var modalEl = document.getElementById('rcsUnsavedChangesModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

function getCurrentEditParams() {
    var zoom = rcsCropState.zoom;
    
    var orientation = 'vertical_short';
    var orientMed = document.getElementById('rcsOrientVertMed');
    var orientTall = document.getElementById('rcsOrientVertTall');
    if (orientMed && orientMed.checked) {
        orientation = 'vertical_medium';
    } else if (orientTall && orientTall.checked) {
        orientation = 'vertical_tall';
    }
    
    return {
        zoom: zoom,
        cropOffsetX: rcsCropState.offsetX,
        cropOffsetY: rcsCropState.offsetY,
        frameWidth: rcsCropState.frameWidth,
        frameHeight: rcsCropState.frameHeight,
        displayScale: rcsCropState.displayScale,
        imageWidth: rcsCropState.imageWidth,
        imageHeight: rcsCropState.imageHeight,
        orientation: orientation
    };
}

function saveRcsImageEdits() {
    if (!rcsMediaData.originalUrl || rcsMediaData.source !== 'url') return;
    
    showRcsProcessingIndicator();
    var editParams = getCurrentEditParams();
    
    fetch('/api/rcs/assets/process-url', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            url: rcsMediaData.originalUrl,
            edit_params: editParams,
            draft_session: rcsDraftSession
        })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideRcsProcessingIndicator();
        if (data.success && data.asset) {
            rcsMediaData.assetUuid = data.asset.uuid;
            rcsMediaData.hostedUrl = data.asset.public_url;
            rcsMediaData.url = data.asset.public_url;
            rcsMediaData.dimensions = { width: data.asset.width, height: data.asset.height };
            rcsMediaData.fileSize = data.asset.file_size;
            showRcsMediaPreview(data.asset.public_url);
            updateRcsImageInfo();
            clearRcsImageDirtyState();
            initRcsImageBaseline();
            updateRcsWizardPreview();
        } else if (data.error) {
            showRcsMediaError(data.error);
        }
    })
    .catch(function(err) {
        hideRcsProcessingIndicator();
        showRcsMediaError('Failed to process image. Please try again.');
    });
}

function saveRcsImageEditsAndContinue() {
    hideRcsUnsavedChangesModal();
    
    if (!rcsMediaData.originalUrl || rcsMediaData.source !== 'url') {
        executePendingNavigation();
        return;
    }
    
    showRcsProcessingIndicator();
    var editParams = getCurrentEditParams();
    
    fetch('/api/rcs/assets/process-url', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            url: rcsMediaData.originalUrl,
            edit_params: editParams,
            draft_session: rcsDraftSession
        })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideRcsProcessingIndicator();
        if (data.success && data.asset) {
            rcsMediaData.assetUuid = data.asset.uuid;
            rcsMediaData.hostedUrl = data.asset.public_url;
            rcsMediaData.url = data.asset.public_url;
            rcsMediaData.dimensions = { width: data.asset.width, height: data.asset.height };
            rcsMediaData.fileSize = data.asset.file_size;
            showRcsMediaPreview(data.asset.public_url);
            updateRcsImageInfo();
            clearRcsImageDirtyState();
            updateRcsWizardPreview();
            executePendingNavigation();
        } else if (data.error) {
            showRcsMediaError(data.error);
        }
    })
    .catch(function(err) {
        hideRcsProcessingIndicator();
        showRcsMediaError('Failed to process image. Please try again.');
    });
}

function discardRcsImageEdits() {
    hideRcsUnsavedChangesModal();
    
    var baselineZoom = rcsImageDirtyState.baselineZoom || 100;
    var baselineOrient = rcsImageDirtyState.baselineOrientation || 'vertical_short';
    var baselineOffsetX = rcsImageDirtyState.baselineOffsetX || 0;
    var baselineOffsetY = rcsImageDirtyState.baselineOffsetY || 0;
    
    rcsCropState.zoom = baselineZoom;
    rcsCropState.offsetX = baselineOffsetX;
    rcsCropState.offsetY = baselineOffsetY;
    
    var zoomSlider = document.getElementById('rcsZoomSlider');
    var zoomValue = document.getElementById('rcsZoomValue');
    if (zoomSlider) zoomSlider.value = baselineZoom;
    if (zoomValue) zoomValue.textContent = baselineZoom + '%';
    
    var orientId = getOrientationRadioIdForDiscard(baselineOrient);
    var orientRadio = document.getElementById(orientId);
    if (orientRadio) orientRadio.checked = true;
    else {
        var defaultOrient = document.getElementById('rcsOrientVertShort');
        if (defaultOrient) defaultOrient.checked = true;
    }
    
    updateRcsCropFrame(baselineOrient);
    applyRcsCropTransform();
    
    if (rcsMediaData.originalUrl) {
        rcsMediaData.url = rcsMediaData.originalUrl;
        rcsMediaData.hostedUrl = null;
        rcsMediaData.assetUuid = null;
        showRcsMediaPreview(rcsMediaData.originalUrl);
    }
    
    clearRcsImageDirtyState();
    initRcsImageBaseline();
    updateRcsWizardPreview();
    
    executePendingNavigation();
}

function getOrientationRadioIdForDiscard(orientation) {
    switch(orientation) {
        case 'vertical_short': return 'rcsOrientVertShort';
        case 'vertical_medium': return 'rcsOrientVertMed';
        case 'vertical_tall': return 'rcsOrientVertTall';
        default: return 'rcsOrientVertShort';
    }
}

function cancelRcsUnsavedChanges() {
    hideRcsUnsavedChangesModal();
    rcsImageDirtyState.pendingNavigation = null;
}

function executePendingNavigation() {
    var pendingAction = rcsImageDirtyState.pendingNavigation;
    rcsImageDirtyState.pendingNavigation = null;
    
    if (!pendingAction) return;
    
    if (pendingAction.type === 'selectCard') {
        selectRcsCardDirect(pendingAction.cardNum);
    } else if (pendingAction.type === 'closeWizard') {
        bootstrap.Modal.getInstance(document.getElementById('rcsWizardModal')).hide();
    } else if (pendingAction.type === 'applyContent') {
        applyRcsContent();
    } else if (pendingAction.type === 'changeType') {
        if (pendingAction.targetValue) {
            var typeEl = document.getElementById(pendingAction.targetValue === 'single' ? 'rcsTypeSingle' : 'rcsTypeCarousel');
            if (typeEl) typeEl.checked = true;
        }
        toggleRcsMessageType();
        updateCarouselOrientationWarning();
        updateRcsWizardPreview();
    }
}

function handleRcsWizardClose() {
    if (isRcsImageDirty()) {
        showRcsUnsavedChangesModal({ type: 'closeWizard' });
    } else {
        bootstrap.Modal.getInstance(document.getElementById('rcsWizardModal')).hide();
    }
}

function handleRcsApplyContent() {
    if (isRcsImageDirty()) {
        showRcsUnsavedChangesModal({ type: 'applyContent' });
    } else {
        applyRcsContent();
    }
}

function showRcsProcessingIndicator() {
    var preview = document.getElementById('rcsMediaPreview');
    if (preview && !preview.querySelector('.rcs-processing-overlay')) {
        var overlay = document.createElement('div');
        overlay.className = 'rcs-processing-overlay';
        overlay.innerHTML = '<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Processing...</span></div>';
        overlay.style.cssText = 'position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(255,255,255,0.8);display:flex;align-items:center;justify-content:center;z-index:10;';
        preview.style.position = 'relative';
        preview.appendChild(overlay);
    }
}

function hideRcsProcessingIndicator() {
    var overlay = document.querySelector('.rcs-processing-overlay');
    if (overlay) {
        overlay.remove();
    }
}

function toggleRcsMediaSource() {
    var isUpload = document.getElementById('rcsMediaUpload').checked;
    var urlSection = document.getElementById('rcsMediaUrlSection');
    var uploadSection = document.getElementById('rcsMediaUploadSection');
    if (urlSection) urlSection.classList.toggle('d-none', isUpload);
    if (uploadSection) uploadSection.classList.toggle('d-none', !isUpload);
    hideRcsMediaError();
}

function loadRcsMediaUrl() {
    var urlInput = document.getElementById('rcsMediaUrlInput');
    var url = urlInput ? urlInput.value.trim() : '';
    if (!url) return;
    
    hideRcsMediaError();
    
    var urlLower = url.toLowerCase();
    var validExtensions = ['.jpg', '.jpeg', '.png', '.gif'];
    var hasValidExtension = validExtensions.some(function(ext) {
        return urlLower.includes(ext);
    });
    
    var loadBtn = document.querySelector('#rcsMediaUrlSection button');
    if (loadBtn) {
        loadBtn.disabled = true;
        loadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    fetch(url, { method: 'HEAD', mode: 'cors' })
        .then(function(response) {
            if (!response.ok) {
                throw new Error('URL not publicly accessible. Please check the URL returns a valid response (HTTP ' + response.status + ').');
            }
            
            var contentType = response.headers.get('Content-Type') || '';
            var contentLength = response.headers.get('Content-Length');
            var fileSize = contentLength ? parseInt(contentLength, 10) : 0;
            
            var validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            var isValidType = validTypes.some(function(t) {
                return contentType.toLowerCase().includes(t);
            }) || hasValidExtension;
            
            if (!isValidType) {
                throw new Error('File type not supported. Only JPEG, PNG, and GIF images are allowed.');
            }
            
            if (fileSize > 0 && fileSize > rcsMaxFileSize) {
                var sizeMB = (fileSize / (1024 * 1024)).toFixed(1);
                throw new Error('File size exceeds limit. This file is ' + sizeMB + ' MB but the maximum allowed is 100 MB.');
            }
            
            var warnLargeFile = fileSize > 0 && fileSize > rcsWarnFileSize;
            return { fileSize: fileSize, contentType: contentType, warnLargeFile: warnLargeFile, validationPassed: true };
        })
        .catch(function(err) {
            if (err.message === 'Failed to fetch') {
                return { fileSize: 0, contentType: '', corsBlocked: true, validationPassed: true };
            }
            return { validationPassed: false, errorMessage: err.message };
        })
        .then(function(metadata) {
            if (!metadata.validationPassed) {
                showRcsMediaError(metadata.errorMessage);
                if (loadBtn) {
                    loadBtn.disabled = false;
                    loadBtn.innerHTML = '<i class="fas fa-check"></i>';
                }
                return;
            }
            
            var img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                rcsMediaData.source = 'url';
                rcsMediaData.url = url;
                rcsMediaData.originalUrl = url;
                rcsMediaData.dimensions = { width: img.width, height: img.height };
                rcsMediaData.fileSize = metadata.fileSize || 0;
                rcsMediaData.assetUuid = null;
                rcsMediaData.hostedUrl = null;
                showRcsMediaPreview(url);
                updateRcsImageInfo();
                initRcsImageBaseline();
                
                if (metadata.warnLargeFile) {
                    var sizeMB = (metadata.fileSize / (1024 * 1024)).toFixed(1);
                    showRcsMediaWarning('This file is ' + sizeMB + ' MB. Large media may load slowly and may not render optimally on handsets.');
                } else if (metadata.corsBlocked) {
                    showRcsMediaWarning('File size could not be verified. Very large images may not render optimally on handsets.');
                }
                
                if (loadBtn) {
                    loadBtn.disabled = false;
                    loadBtn.innerHTML = '<i class="fas fa-check"></i>';
                }
            };
            img.onerror = function() {
                showRcsMediaError('Media could not be fetched. The URL may not be publicly accessible or may not point to a valid image.');
                if (loadBtn) {
                    loadBtn.disabled = false;
                    loadBtn.innerHTML = '<i class="fas fa-check"></i>';
                }
            };
            img.src = url;
        });
}

function showRcsMediaWarning(message) {
    var errorEl = document.getElementById('rcsMediaError');
    var errorTextEl = document.getElementById('rcsMediaErrorText');
    if (errorTextEl) errorTextEl.textContent = message;
    if (errorEl) {
        errorEl.classList.remove('d-none');
        errorEl.classList.remove('alert-danger');
        errorEl.classList.add('alert-warning');
    }
}

function handleRcsFileUpload(file) {
    hideRcsMediaError();
    
    if (!file) return;
    
    if (!rcsAllowedTypes.includes(file.type)) {
        showRcsMediaError('Unsupported file type. Only JPEG, PNG, and GIF images are allowed.');
        return;
    }
    
    if (file.size > rcsMaxFileSize) {
        var sizeMB = (file.size / (1024 * 1024)).toFixed(1);
        showRcsMediaError('File size (' + sizeMB + ' MB) exceeds 100 MB limit. Please choose a smaller file.');
        return;
    }
    
    var warnLargeFile = file.size > rcsWarnFileSize;
    
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = new Image();
        img.onload = function() {
            rcsMediaData.source = 'upload';
            rcsMediaData.file = file;
            rcsMediaData.url = e.target.result;
            rcsMediaData.dimensions = { width: img.width, height: img.height };
            rcsMediaData.fileSize = file.size;
            showRcsMediaPreview(e.target.result);
            updateRcsImageInfo();
            
            if (warnLargeFile) {
                var sizeMB = (file.size / (1024 * 1024)).toFixed(1);
                showRcsMediaWarning('This file is ' + sizeMB + ' MB. Large media may load slowly and may not render optimally on handsets.');
            }
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

function showRcsMediaPreview(src) {
    var img = document.getElementById('rcsMediaPreviewImg');
    if (img) img.src = src;
    var preview = document.getElementById('rcsMediaPreview');
    if (preview) preview.classList.remove('d-none');
    
    if (img) {
        img.onload = function() {
            initRcsCropImage(img);
            updateCarouselOrientationWarning();
            updateRcsWizardPreview();
        };
        
        if (img.complete && img.naturalWidth) {
            initRcsCropImage(img);
            updateCarouselOrientationWarning();
            updateRcsWizardPreview();
        }
    }
}

function removeRcsMedia() {
    rcsMediaData = { source: null, url: null, file: null, dimensions: null, fileSize: 0, assetUuid: null, hostedUrl: null, originalUrl: null };
    var preview = document.getElementById('rcsMediaPreview');
    if (preview) preview.classList.add('d-none');
    var img = document.getElementById('rcsMediaPreviewImg');
    if (img) {
        img.src = '';
        img.style.width = '';
        img.style.height = '';
        img.style.left = '';
        img.style.top = '';
    }
    var urlInput = document.getElementById('rcsMediaUrlInput');
    var fileInput = document.getElementById('rcsMediaFileInput');
    var zoomSlider = document.getElementById('rcsZoomSlider');
    if (urlInput) urlInput.value = '';
    if (fileInput) fileInput.value = '';
    if (zoomSlider) zoomSlider.value = 100;
    
    rcsCropState.zoom = 100;
    rcsCropState.offsetX = 0;
    rcsCropState.offsetY = 0;
    rcsCropState.imageWidth = 0;
    rcsCropState.imageHeight = 0;
    var zoomValue = document.getElementById('rcsZoomValue');
    if (zoomValue) zoomValue.textContent = '100%';
    updateRcsWizardPreview();
    var dimensions = document.getElementById('rcsImageDimensions');
    var fileSize = document.getElementById('rcsImageFileSize');
    if (dimensions) dimensions.textContent = '--';
    if (fileSize) fileSize.textContent = '--';
    var orientShort = document.getElementById('rcsOrientVertShort');
    if (orientShort) orientShort.checked = true;
    updateRcsCropFrame('vertical_short');
    hideRcsMediaError();
}

function showRcsMediaError(message) {
    var errorEl = document.getElementById('rcsMediaError');
    var errorTextEl = document.getElementById('rcsMediaErrorText');
    if (errorTextEl) errorTextEl.textContent = message;
    if (errorEl) {
        errorEl.classList.remove('d-none');
        errorEl.classList.remove('alert-warning');
        errorEl.classList.add('alert-danger');
    }
}

function hideRcsMediaError() {
    var errorEl = document.getElementById('rcsMediaError');
    if (errorEl) {
        errorEl.classList.add('d-none');
        errorEl.classList.remove('alert-warning');
        errorEl.classList.add('alert-danger');
    }
}

function updateRcsImageInfo() {
    var dimensionsEl = document.getElementById('rcsImageDimensions');
    var fileSizeEl = document.getElementById('rcsImageFileSize');
    if (rcsMediaData.dimensions && dimensionsEl) {
        dimensionsEl.textContent = rcsMediaData.dimensions.width + ' x ' + rcsMediaData.dimensions.height + ' px';
    }
    if (fileSizeEl) {
        if (rcsMediaData.fileSize > 0) {
            var sizeText = (rcsMediaData.fileSize / 1024).toFixed(1) + ' KB';
            if (rcsMediaData.hostedUrl) {
                sizeText += ' (QuickSMS hosted)';
            } else if (rcsMediaData.source === 'url') {
                sizeText += ' (from URL)';
            }
            fileSizeEl.textContent = sizeText;
        } else if (rcsMediaData.source === 'url') {
            fileSizeEl.textContent = 'External URL (size unknown)';
        } else {
            fileSizeEl.textContent = '--';
        }
    }
}

function initRcsCropEditor() {
    var workspace = document.getElementById('rcsCropWorkspace');
    if (!workspace) return;
    
    workspace.addEventListener('mousedown', startRcsCropDrag);
    workspace.addEventListener('touchstart', startRcsCropDrag, { passive: false });
    document.addEventListener('mousemove', doRcsCropDrag);
    document.addEventListener('touchmove', doRcsCropDrag, { passive: false });
    document.addEventListener('mouseup', endRcsCropDrag);
    document.addEventListener('touchend', endRcsCropDrag);
    
    document.querySelectorAll('input[name="rcsOrientation"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            applyCarouselHeightToAllCards(this.value);
        });
    });
    
    document.querySelectorAll('input[name="rcsCardWidth"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            applyCarouselWidthToAllCards(this.value);
        });
    });
    
    updateRcsCropFrame('vertical_short');
}

function applyCarouselHeightToAllCards(height) {
    var isCarouselEl = document.getElementById('rcsTypeCarousel');
    var isCarousel = isCarouselEl ? isCarouselEl.checked : false;
    
    rcsCarouselHeight = height;
    updateRcsCropFrame(height);
    
    if (isCarousel && rcsCardCount > 1) {
        saveCurrentCardData();
        
        for (var i = 1; i <= rcsCardCount; i++) {
            if (rcsCardsData[i]) {
                rcsCardsData[i].media.orientation = height;
            }
        }
        
        showCarouselHeightNotice();
    }
}

function applyCarouselWidthToAllCards(width) {
    var isCarouselEl = document.getElementById('rcsTypeCarousel');
    var isCarousel = isCarouselEl ? isCarouselEl.checked : false;
    
    rcsCarouselWidth = width;
    updateRcsCardWidth(width);
    
    if (isCarousel && rcsCardCount > 1) {
        saveCurrentCardData();
        
        for (var i = 1; i <= rcsCardCount; i++) {
            if (rcsCardsData[i]) {
                rcsCardsData[i].media.cardWidth = width;
            }
        }
        
        showCarouselWidthNotice();
    }
}

function showCarouselHeightNotice() {
    var notice = document.getElementById('rcsCarouselHeightNotice');
    if (notice) {
        notice.classList.remove('d-none');
        setTimeout(function() {
            notice.classList.add('d-none');
        }, 4000);
    }
}

function showCarouselWidthNotice() {
    var notice = document.getElementById('rcsCarouselWidthNotice');
    if (notice) {
        notice.classList.remove('d-none');
        setTimeout(function() {
            notice.classList.add('d-none');
        }, 4000);
    }
}

function updateRcsCropFrame(orientation) {
    rcsCropState.orientation = orientation;
    var frame = document.getElementById('rcsCropFrame');
    if (!frame) return;
    
    var sizes;
    if (isRcsCarouselMode()) {
        var widthSizes = rcsCarouselFrameSizes[rcsCurrentCardWidth] || rcsCarouselFrameSizes['medium'];
        sizes = widthSizes[orientation] || widthSizes['vertical_short'];
    } else {
        sizes = rcsSingleCardFrameSizes[orientation] || rcsSingleCardFrameSizes['vertical_short'];
    }
    
    rcsCropState.frameWidth = sizes.width;
    rcsCropState.frameHeight = sizes.height;
    
    frame.className = 'rcs-crop-frame';
    if (orientation === 'vertical_short') {
        frame.classList.add('rcs-crop-frame--short');
    } else if (orientation === 'vertical_medium') {
        frame.classList.add('rcs-crop-frame--medium');
    } else if (orientation === 'vertical_tall') {
        frame.classList.add('rcs-crop-frame--tall');
    }
    
    frame.style.width = sizes.width + 'px';
    frame.style.height = sizes.height + 'px';
    
    constrainRcsCropPosition();
    applyRcsCropTransform();
    markRcsImageDirty();
}

function updateRcsCardWidth(cardWidth) {
    rcsCurrentCardWidth = cardWidth;
    
    var tallInput = document.getElementById('rcsOrientVertTall');
    var warningEl = document.getElementById('rcsCardWidthHeightWarning');
    
    updateCardWidthAndHeightRestrictions();
    
    if (isRcsCarouselMode() && cardWidth === 'small' && tallInput && tallInput.checked) {
        var medOrient = document.getElementById('rcsOrientVertMed');
        if (medOrient) medOrient.checked = true;
        if (warningEl) {
            warningEl.classList.remove('d-none');
            var warningText = document.getElementById('rcsCardWidthHeightWarningText');
            if (warningText) warningText.textContent = 'Tall media height is not available with Small card width. Height has been reset to Medium.';
        }
        updateRcsCropFrame('vertical_medium');
    } else {
        var currentOrientation = document.querySelector('input[name="rcsOrientation"]:checked');
        if (currentOrientation) {
            updateRcsCropFrame(currentOrientation.value);
        }
    }
}

function initRcsCropImage(imgElement) {
    var img = imgElement || document.getElementById('rcsMediaPreviewImg');
    if (!img || !img.naturalWidth) return;
    
    rcsCropState.imageWidth = img.naturalWidth;
    rcsCropState.imageHeight = img.naturalHeight;
    
    var workspace = document.getElementById('rcsCropWorkspace');
    var workspaceWidth = workspace ? workspace.clientWidth : 300;
    var workspaceHeight = workspace ? workspace.clientHeight : 220;
    
    var scaleX = workspaceWidth / img.naturalWidth;
    var scaleY = workspaceHeight / img.naturalHeight;
    rcsCropState.displayScale = Math.max(scaleX, scaleY) * 0.8;
    
    rcsCropState.zoom = 100;
    rcsCropState.offsetX = 0;
    rcsCropState.offsetY = 0;
    
    var zoomSlider = document.getElementById('rcsZoomSlider');
    var zoomValue = document.getElementById('rcsZoomValue');
    if (zoomSlider) zoomSlider.value = 100;
    if (zoomValue) zoomValue.textContent = '100%';
    
    applyRcsCropTransform();
    resetRcsCropPosition();
}

function applyRcsCropTransform() {
    var img = document.getElementById('rcsMediaPreviewImg');
    if (!img) return;
    
    var workspace = document.getElementById('rcsCropWorkspace');
    var workspaceWidth = workspace ? workspace.clientWidth : 300;
    var workspaceHeight = workspace ? workspace.clientHeight : 220;
    
    var scale = rcsCropState.displayScale * (rcsCropState.zoom / 100);
    var displayWidth = rcsCropState.imageWidth * scale;
    var displayHeight = rcsCropState.imageHeight * scale;
    
    img.style.width = displayWidth + 'px';
    img.style.height = displayHeight + 'px';
    
    var centerX = (workspaceWidth - displayWidth) / 2;
    var centerY = (workspaceHeight - displayHeight) / 2;
    
    img.style.left = (centerX + rcsCropState.offsetX) + 'px';
    img.style.top = (centerY + rcsCropState.offsetY) + 'px';
}

function constrainRcsCropPosition() {
    var workspace = document.getElementById('rcsCropWorkspace');
    if (!workspace) return;
    
    var workspaceWidth = workspace.clientWidth;
    var workspaceHeight = workspace.clientHeight;
    
    var scale = rcsCropState.displayScale * (rcsCropState.zoom / 100);
    var displayWidth = rcsCropState.imageWidth * scale;
    var displayHeight = rcsCropState.imageHeight * scale;
    
    var frameHalfW = rcsCropState.frameWidth / 2;
    var frameHalfH = rcsCropState.frameHeight / 2;
    
    var minOffsetX = frameHalfW - (workspaceWidth / 2 + displayWidth / 2);
    var maxOffsetX = (workspaceWidth / 2 + displayWidth / 2) - workspaceWidth + frameHalfW;
    var minOffsetY = frameHalfH - (workspaceHeight / 2 + displayHeight / 2);
    var maxOffsetY = (workspaceHeight / 2 + displayHeight / 2) - workspaceHeight + frameHalfH;
    
    if (displayWidth >= rcsCropState.frameWidth) {
        rcsCropState.offsetX = Math.max(minOffsetX, Math.min(maxOffsetX, rcsCropState.offsetX));
    } else {
        rcsCropState.offsetX = 0;
    }
    
    if (displayHeight >= rcsCropState.frameHeight) {
        rcsCropState.offsetY = Math.max(minOffsetY, Math.min(maxOffsetY, rcsCropState.offsetY));
    } else {
        rcsCropState.offsetY = 0;
    }
}

function startRcsCropDrag(e) {
    e.preventDefault();
    rcsCropState.isDragging = true;
    
    var point = e.touches ? e.touches[0] : e;
    rcsCropState.startX = point.clientX;
    rcsCropState.startY = point.clientY;
    rcsCropState.startOffsetX = rcsCropState.offsetX;
    rcsCropState.startOffsetY = rcsCropState.offsetY;
    
    var workspace = document.getElementById('rcsCropWorkspace');
    if (workspace) workspace.classList.add('dragging');
    
    showRcsCropCrosshair();
}

function doRcsCropDrag(e) {
    if (!rcsCropState.isDragging) return;
    e.preventDefault();
    
    var point = e.touches ? e.touches[0] : e;
    var deltaX = point.clientX - rcsCropState.startX;
    var deltaY = point.clientY - rcsCropState.startY;
    
    rcsCropState.offsetX = rcsCropState.startOffsetX + deltaX;
    rcsCropState.offsetY = rcsCropState.startOffsetY + deltaY;
    
    constrainRcsCropPosition();
    applyRcsCropTransform();
}

function endRcsCropDrag() {
    if (rcsCropState.isDragging) {
        rcsCropState.isDragging = false;
        var workspace = document.getElementById('rcsCropWorkspace');
        if (workspace) workspace.classList.remove('dragging');
        markRcsImageDirty();
        hideRcsCropCrosshair();
    }
}

function updateRcsCropZoom(value) {
    rcsCropState.zoom = parseInt(value);
    var zoomValue = document.getElementById('rcsZoomValue');
    if (zoomValue) zoomValue.textContent = value + '%';
    
    constrainRcsCropPosition();
    applyRcsCropTransform();
    markRcsImageDirty();
    
    showRcsCropCrosshair();
    hideRcsCropCrosshairDelayed();
}

function showRcsCropCrosshair() {
    if (rcsCrosshairHideTimer) {
        clearTimeout(rcsCrosshairHideTimer);
        rcsCrosshairHideTimer = null;
    }
    var els = document.querySelectorAll('.rcs-crop-crosshair');
    els.forEach(function(el) { el.classList.add('active'); });
}

function hideRcsCropCrosshair() {
    hideRcsCropCrosshairDelayed();
}

function hideRcsCropCrosshairDelayed() {
    if (rcsCrosshairHideTimer) clearTimeout(rcsCrosshairHideTimer);
    rcsCrosshairHideTimer = setTimeout(function() {
        var els = document.querySelectorAll('.rcs-crop-crosshair');
        els.forEach(function(el) { el.classList.remove('active'); });
        rcsCrosshairHideTimer = null;
    }, 400);
}

function resetRcsCropPosition() {
    rcsCropState.offsetX = 0;
    rcsCropState.offsetY = 0;
    applyRcsCropTransform();
    markRcsImageDirty();
}

function resetRcsCropToFit() {
    var workspace = document.getElementById('rcsCropWorkspace');
    if (!workspace || !rcsCropState.imageWidth) return;
    
    var scaleX = rcsCropState.frameWidth / rcsCropState.imageWidth;
    var scaleY = rcsCropState.frameHeight / rcsCropState.imageHeight;
    var fitScale = Math.max(scaleX, scaleY);
    
    var fitZoom = Math.round((fitScale / rcsCropState.displayScale) * 100);
    fitZoom = Math.max(25, Math.min(200, fitZoom));
    
    rcsCropState.zoom = fitZoom;
    rcsCropState.offsetX = 0;
    rcsCropState.offsetY = 0;
    
    var zoomSlider = document.getElementById('rcsZoomSlider');
    var zoomValue = document.getElementById('rcsZoomValue');
    if (zoomSlider) zoomSlider.value = fitZoom;
    if (zoomValue) zoomValue.textContent = fitZoom + '%';
    
    applyRcsCropTransform();
    markRcsImageDirty();
}

function updateCarouselOrientationWarning() {
    var isCarouselEl = document.getElementById('rcsTypeCarousel');
    var isCarousel = isCarouselEl ? isCarouselEl.checked : false;
    var horizInput = document.getElementById('rcsOrientHoriz');
    var horizLabel = document.getElementById('rcsOrientHorizLabel');
    var warning = document.getElementById('rcsCarouselOrientWarning');
    
    if (isCarousel) {
        if (horizInput) horizInput.style.display = 'none';
        if (horizLabel) horizLabel.style.display = 'none';
        if (horizInput) horizInput.disabled = true;
        if (warning) warning.classList.remove('d-none');
        if (horizInput && horizInput.checked) {
            var shortOrient = document.getElementById('rcsOrientVertShort');
            if (shortOrient) shortOrient.checked = true;
            updateRcsCropFrame('vertical_short');
        }
    } else {
        if (horizInput) horizInput.style.display = '';
        if (horizLabel) horizLabel.style.display = '';
        if (horizInput) horizInput.disabled = false;
        if (warning) warning.classList.add('d-none');
    }
}

function updateRcsDescriptionCount() {
    var input = document.getElementById('rcsDescription');
    var countEl = document.getElementById('rcsDescriptionCount');
    var warning = document.getElementById('rcsDescriptionWarning');
    if (input && countEl) {
        var count = input.value.length;
        countEl.textContent = count;
        if (warning) warning.classList.toggle('d-none', count <= 120);
        updateRcsWizardPreview();
    }
}

function updateRcsTextBodyCount() {
    var textarea = document.getElementById('rcsTextBody');
    var countEl = document.getElementById('rcsTextBodyCount');
    var warning = document.getElementById('rcsTextBodyWarning');
    if (textarea && countEl) {
        var count = textarea.value.length;
        countEl.textContent = count;
        if (warning) warning.classList.toggle('d-none', count <= 2000);
        updateRcsWizardPreview();
    }
}

function openRcsPlaceholderPicker(field) {
    rcsActiveTextField = field;
    var modal = new bootstrap.Modal(document.getElementById('personalisationModal'));
    modal.show();
}

function openRcsEmojiPicker(field) {
    rcsActiveTextField = field;
    var modal = new bootstrap.Modal(document.getElementById('emojiPickerModal'));
    modal.show();
}

function getRcsTextElement(field) {
    if (field === 'description') return document.getElementById('rcsDescription');
    if (field === 'textBody') return document.getElementById('rcsTextBody');
    if (field === 'rcsButtonLabel') return document.getElementById('rcsButtonLabel');
    if (field === 'rcsButtonPhone') return document.getElementById('rcsButtonPhone');
    if (field === 'rcsButtonEventTitle') return document.getElementById('rcsButtonEventTitle');
    if (field === 'rcsButtonEventDesc') return document.getElementById('rcsButtonEventDesc');
    return null;
}

function openRcsButtonFieldPlaceholder(fieldId) {
    rcsActiveTextField = fieldId;
    var modal = new bootstrap.Modal(document.getElementById('personalisationModal'));
    modal.show();
}

function openRcsButtonFieldEmoji(fieldId) {
    rcsActiveTextField = fieldId;
    var modal = new bootstrap.Modal(document.getElementById('emojiPickerModal'));
    modal.show();
}

function validateRcsPhoneNoEmoji() {
    var input = document.getElementById('rcsButtonPhone');
    var errorEl = document.getElementById('rcsButtonPhoneEmojiError');
    var emojiRegex = /[\uD83C-\uDBFF\uDC00-\uDFFF]+|[\u2600-\u27BF]|[\uFE00-\uFE0F]|[\u2000-\u206F]/g;
    
    if (input && emojiRegex.test(input.value)) {
        emojiRegex.lastIndex = 0;
        input.value = input.value.replace(emojiRegex, '');
        if (errorEl) {
            errorEl.classList.remove('d-none');
            setTimeout(function() {
                errorEl.classList.add('d-none');
            }, 3000);
        }
    }
}

function addRcsButton() {
    if (rcsButtons.length >= rcsMaxButtons) return;
    rcsEditingButtonIndex = -1;
    resetRcsButtonForm();
    var modal = new bootstrap.Modal(document.getElementById('rcsButtonConfigModal'));
    modal.show();
}

function editRcsButton(index) {
    rcsEditingButtonIndex = index;
    var btn = rcsButtons[index];
    var labelEl = document.getElementById('rcsButtonLabel');
    if (labelEl) labelEl.value = btn.label;
    updateRcsButtonLabelCount();
    
    var typeEl = document.getElementById('rcsButtonType' + btn.type.charAt(0).toUpperCase() + btn.type.slice(1));
    if (typeEl) typeEl.checked = true;
    toggleRcsButtonType();
    
    if (btn.type === 'url') {
        var urlEl = document.getElementById('rcsButtonUrl');
        if (urlEl) urlEl.value = btn.url || '';
    } else if (btn.type === 'phone') {
        var phoneEl = document.getElementById('rcsButtonPhone');
        if (phoneEl) phoneEl.value = btn.phone || '';
    } else if (btn.type === 'calendar') {
        var titleEl = document.getElementById('rcsButtonEventTitle');
        var startEl = document.getElementById('rcsButtonEventStart');
        var endEl = document.getElementById('rcsButtonEventEnd');
        var descEl = document.getElementById('rcsButtonEventDesc');
        if (titleEl) titleEl.value = btn.eventTitle || '';
        if (startEl) startEl.value = btn.eventStart || '';
        if (endEl) endEl.value = btn.eventEnd || '';
        if (descEl) descEl.value = btn.eventDesc || '';
    }
    
    var modal = new bootstrap.Modal(document.getElementById('rcsButtonConfigModal'));
    modal.show();
}

function deleteRcsButton(index) {
    rcsButtons.splice(index, 1);
    renderRcsButtons();
    updateRcsButtonsPreview();
}

function resetRcsButtonForm() {
    var labelEl = document.getElementById('rcsButtonLabel');
    var labelCount = document.getElementById('rcsButtonLabelCount');
    var typeUrl = document.getElementById('rcsButtonTypeUrl');
    if (labelEl) labelEl.value = '';
    if (labelCount) labelCount.textContent = '0';
    if (typeUrl) typeUrl.checked = true;
    toggleRcsButtonType();
    var urlEl = document.getElementById('rcsButtonUrl');
    var phoneEl = document.getElementById('rcsButtonPhone');
    var titleEl = document.getElementById('rcsButtonEventTitle');
    var startEl = document.getElementById('rcsButtonEventStart');
    var endEl = document.getElementById('rcsButtonEventEnd');
    var descEl = document.getElementById('rcsButtonEventDesc');
    if (urlEl) urlEl.value = '';
    if (phoneEl) phoneEl.value = '';
    if (titleEl) titleEl.value = '';
    if (startEl) startEl.value = '';
    if (endEl) endEl.value = '';
    if (descEl) descEl.value = '';
    hideAllRcsButtonErrors();
}

function hideAllRcsButtonErrors() {
    ['rcsButtonLabelError', 'rcsButtonUrlError', 'rcsButtonPhoneError', 
     'rcsButtonEventTitleError', 'rcsButtonEventStartError', 'rcsButtonEventEndError'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.add('d-none');
    });
}

function toggleRcsButtonType() {
    var checkedType = document.querySelector('input[name="rcsButtonType"]:checked');
    var type = checkedType ? checkedType.value : 'url';
    var urlConfig = document.getElementById('rcsButtonUrlConfig');
    var phoneConfig = document.getElementById('rcsButtonPhoneConfig');
    var calendarConfig = document.getElementById('rcsButtonCalendarConfig');
    if (urlConfig) urlConfig.classList.toggle('d-none', type !== 'url');
    if (phoneConfig) phoneConfig.classList.toggle('d-none', type !== 'phone');
    if (calendarConfig) calendarConfig.classList.toggle('d-none', type !== 'calendar');
    hideAllRcsButtonErrors();
}

function updateRcsButtonLabelCount() {
    var labelEl = document.getElementById('rcsButtonLabel');
    var countEl = document.getElementById('rcsButtonLabelCount');
    if (labelEl && countEl) {
        countEl.textContent = labelEl.value.length;
    }
}

function validateRcsButton() {
    hideAllRcsButtonErrors();
    var valid = true;
    var labelEl = document.getElementById('rcsButtonLabel');
    var label = labelEl ? labelEl.value.trim() : '';
    var checkedType = document.querySelector('input[name="rcsButtonType"]:checked');
    var type = checkedType ? checkedType.value : 'url';
    
    if (!label) {
        var labelError = document.getElementById('rcsButtonLabelError');
        if (labelError) labelError.classList.remove('d-none');
        valid = false;
    }
    
    if (type === 'url') {
        var urlEl = document.getElementById('rcsButtonUrl');
        var url = urlEl ? urlEl.value.trim() : '';
        var urlPattern = /^https?:\/\/.+/i;
        if (!url || !urlPattern.test(url)) {
            var urlError = document.getElementById('rcsButtonUrlError');
            if (urlError) urlError.classList.remove('d-none');
            valid = false;
        }
    } else if (type === 'phone') {
        var phoneEl = document.getElementById('rcsButtonPhone');
        var phone = phoneEl ? phoneEl.value.trim() : '';
        var phonePattern = /^\+?[0-9\s\-()]{7,20}$/;
        if (!phone || !phonePattern.test(phone)) {
            var phoneError = document.getElementById('rcsButtonPhoneError');
            if (phoneError) phoneError.classList.remove('d-none');
            valid = false;
        }
    } else if (type === 'calendar') {
        var titleEl = document.getElementById('rcsButtonEventTitle');
        var startEl = document.getElementById('rcsButtonEventStart');
        var endEl = document.getElementById('rcsButtonEventEnd');
        var eventTitle = titleEl ? titleEl.value.trim() : '';
        var eventStart = startEl ? startEl.value : '';
        var eventEnd = endEl ? endEl.value : '';
        
        if (!eventTitle) {
            var titleError = document.getElementById('rcsButtonEventTitleError');
            if (titleError) titleError.classList.remove('d-none');
            valid = false;
        }
        if (!eventStart) {
            var startError = document.getElementById('rcsButtonEventStartError');
            if (startError) startError.classList.remove('d-none');
            valid = false;
        }
        if (!eventEnd) {
            var endError = document.getElementById('rcsButtonEventEndError');
            if (endError) endError.classList.remove('d-none');
            valid = false;
        }
    }
    
    return valid;
}

function saveRcsButton() {
    if (!validateRcsButton()) return;
    
    var labelEl = document.getElementById('rcsButtonLabel');
    var label = labelEl ? labelEl.value.trim() : '';
    var checkedType = document.querySelector('input[name="rcsButtonType"]:checked');
    var type = checkedType ? checkedType.value : 'url';
    
    var buttonData = { label: label, type: type };
    
    if (type === 'url') {
        var urlEl = document.getElementById('rcsButtonUrl');
        buttonData.url = urlEl ? urlEl.value.trim() : '';
    } else if (type === 'phone') {
        var phoneEl = document.getElementById('rcsButtonPhone');
        buttonData.phone = phoneEl ? phoneEl.value.trim() : '';
    } else if (type === 'calendar') {
        var titleEl = document.getElementById('rcsButtonEventTitle');
        var startEl = document.getElementById('rcsButtonEventStart');
        var endEl = document.getElementById('rcsButtonEventEnd');
        var descEl = document.getElementById('rcsButtonEventDesc');
        buttonData.eventTitle = titleEl ? titleEl.value.trim() : '';
        buttonData.eventStart = startEl ? startEl.value : '';
        buttonData.eventEnd = endEl ? endEl.value : '';
        buttonData.eventDesc = descEl ? descEl.value.trim() : '';
    }
    
    if (rcsEditingButtonIndex >= 0) {
        rcsButtons[rcsEditingButtonIndex] = buttonData;
    } else {
        rcsButtons.push(buttonData);
    }
    
    bootstrap.Modal.getInstance(document.getElementById('rcsButtonConfigModal')).hide();
    renderRcsButtons();
    updateRcsButtonsPreview();
}

function renderRcsButtons() {
    var container = document.getElementById('rcsButtonsList');
    if (!container) return;
    container.innerHTML = '';
    
    rcsButtons.forEach(function(btn, index) {
        var typeIcon = btn.type === 'url' ? 'fa-link' : btn.type === 'phone' ? 'fa-phone' : 'fa-calendar-plus';
        var typeLabel = btn.type === 'url' ? 'URL' : btn.type === 'phone' ? 'Call' : 'Calendar';
        
        var html = '<div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2 bg-light">';
        html += '<div class="d-flex align-items-center">';
        html += '<span class="badge bg-secondary me-2"><i class="fas ' + typeIcon + '"></i></span>';
        html += '<span class="small fw-medium">' + escapeHtmlRcs(btn.label) + '</span>';
        html += '<span class="badge bg-light text-muted ms-2 small">' + typeLabel + '</span>';
        html += '</div>';
        html += '<div class="btn-group btn-group-sm">';
        html += '<button type="button" class="btn btn-outline-secondary" onclick="editRcsButton(' + index + ')"><i class="fas fa-edit"></i></button>';
        html += '<button type="button" class="btn btn-outline-danger" onclick="deleteRcsButton(' + index + ')"><i class="fas fa-trash"></i></button>';
        html += '</div>';
        html += '</div>';
        
        container.innerHTML += html;
    });
    
    var countEl = document.getElementById('rcsButtonCount');
    var addBtn = document.getElementById('rcsAddButtonBtn');
    if (countEl) countEl.textContent = rcsButtons.length + ' / ' + rcsMaxButtons;
    if (addBtn) addBtn.disabled = rcsButtons.length >= rcsMaxButtons;
    updateRcsWizardPreview();
}

function updateRcsButtonsPreview() {
    var previewContainer = document.querySelector('.rcs-preview-buttons .d-grid');
    if (!previewContainer) return;
    
    previewContainer.innerHTML = '';
    
    if (rcsButtons.length === 0) {
        previewContainer.innerHTML = '<button class="btn btn-outline-primary btn-sm" disabled>Action Button 1</button>';
        previewContainer.innerHTML += '<button class="btn btn-outline-secondary btn-sm" disabled>Action Button 2</button>';
        return;
    }
    
    rcsButtons.forEach(function(btn, index) {
        var btnClass = index === 0 ? 'btn-outline-primary' : 'btn-outline-secondary';
        var icon = btn.type === 'url' ? 'fa-external-link-alt' : btn.type === 'phone' ? 'fa-phone' : 'fa-calendar-plus';
        previewContainer.innerHTML += '<button class="btn ' + btnClass + ' btn-sm" disabled><i class="fas ' + icon + ' me-1"></i>' + escapeHtmlRcs(btn.label) + '</button>';
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="rcsMediaSource"]').forEach(function(radio) {
        radio.addEventListener('change', toggleRcsMediaSource);
    });
    
    var fileInput = document.getElementById('rcsMediaFileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handleRcsFileUpload(e.target.files[0]);
            }
        });
    }
    
    var dropzone = document.getElementById('rcsMediaDropzone');
    if (dropzone) {
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropzone.classList.add('border-primary');
        });
        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropzone.classList.remove('border-primary');
        });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.classList.remove('border-primary');
            if (e.dataTransfer.files.length > 0) {
                handleRcsFileUpload(e.dataTransfer.files[0]);
            }
        });
    }
    
    document.querySelectorAll('input[name="rcsButtonType"]').forEach(function(radio) {
        radio.addEventListener('change', toggleRcsButtonType);
    });
    
    var buttonConfigModal = document.getElementById('rcsButtonConfigModal');
    if (buttonConfigModal) {
        buttonConfigModal.addEventListener('hidden.bs.modal', function() {
            rcsEditingButtonIndex = -1;
        });
    }
});
