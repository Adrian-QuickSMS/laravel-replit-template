/**
 * RCS Content Wizard - Shared JavaScript
 * Used by both Send Message and Inbox pages
 */
console.log('[RCS Wizard] Script loading...');

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
    frameHeight: 106,
    orientation: 'vertical_short'
};

var rcsSingleCardFrameSizes = {
    'vertical_short': { width: 280, height: 106, aspectRatio: 2.64 },
    'vertical_medium': { width: 280, height: 159, aspectRatio: 1.76 },
    'vertical_tall': { width: 280, height: 250, aspectRatio: 1.12 }
};

var rcsCarouselFrameSizes = {
    'small': {
        'vertical_short': { width: 180, height: 68 },
        'vertical_medium': { width: 180, height: 102 }
    },
    'medium': {
        'vertical_short': { width: 280, height: 106 },
        'vertical_medium': { width: 280, height: 159 },
        'vertical_tall': { width: 280, height: 250 }
    }
};

var rcsCurrentCardWidth = 'medium';
var rcsCarouselHeight = 'vertical_short';
var rcsCarouselWidth = 'medium';

window.onrcsMediaLoadSuccess = function(data) {
    if (data.source === 'file') {
        var img = new Image();
        img.onload = function() {
            rcsMediaData.source = 'upload';
            rcsMediaData.file = data.file;
            rcsMediaData.url = data.dataUrl;
            rcsMediaData.dimensions = { width: img.width, height: img.height };
            rcsMediaData.fileSize = data.file.size;
            showRcsMediaPreview(data.dataUrl);
            updateRcsImageInfo();
            initRcsImageBaseline();
            
            if (data.file.size > rcsWarnFileSize) {
                var sizeMB = (data.file.size / (1024 * 1024)).toFixed(1);
                showRcsMediaWarning('This file is ' + sizeMB + ' MB. Large media may load slowly and may not render optimally on handsets.');
            }
        };
        img.src = data.dataUrl;
    } else if (data.source === 'url') {
        var img = new Image();
        var onUrlImageLoaded = function(previewSrc) {
            rcsMediaData.source = 'url';
            rcsMediaData.url = previewSrc || data.url;
            rcsMediaData.originalUrl = data.url;
            rcsMediaData.dimensions = { width: img.width, height: img.height };
            rcsMediaData.fileSize = 0;
            rcsMediaData.assetUuid = null;
            rcsMediaData.hostedUrl = null;
            showRcsMediaPreview(previewSrc || data.url);
            updateRcsImageInfo();
            initRcsImageBaseline();
        };
        img.crossOrigin = 'anonymous';
        img.onload = function() {
            onUrlImageLoaded(data.url);
        };
        img.onerror = function() {
            console.log('[RCS Load] CORS blocked, proxying through server:', data.url);
            proxyRcsImageThroughServer(data.url, function(proxyResult) {
                if (proxyResult && proxyResult.dataUrl) {
                    var proxyImg = new Image();
                    proxyImg.onload = function() {
                        img = proxyImg;
                        onUrlImageLoaded(proxyResult.dataUrl);
                    };
                    proxyImg.onerror = function() {
                        showRcsMediaError('Media could not be fetched. The URL may not be publicly accessible or may not point to a valid image.');
                        if (typeof window.rcsMediaReset === 'function') {
                            window.rcsMediaReset();
                        }
                    };
                    proxyImg.src = proxyResult.dataUrl;
                } else {
                    showRcsMediaError('Media could not be fetched. The URL may not be publicly accessible or may not point to a valid image.');
                    if (typeof window.rcsMediaReset === 'function') {
                        window.rcsMediaReset();
                    }
                }
            });
        };
        img.src = data.url;
    }
};

window.onrcsMediaRemove = function() {
    removeRcsMedia();
};

window.onrcsMediaError = function(message) {
    showRcsMediaError(message);
};

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
        var configCol = document.getElementById('rcsConfigColumn');
        if (configCol) configCol.focus();
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
    card.media.savedDataUrl = rcsMediaData.savedDataUrl;
    
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
    rcsMediaData.savedDataUrl = card.media.savedDataUrl;
    
    // Show/hide hosted URL section based on whether this card has a saved URL
    if (card.media.hostedUrl) {
        showRcsHostedUrl(card.media.hostedUrl);
    } else {
        hideRcsHostedUrl();
    }
    
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
            if (card.media.fileSize > 100 * 1024 * 1024) {
                errors.push('Card ' + i + ': Media file exceeds 100MB limit (' + (card.media.fileSize / (1024 * 1024)).toFixed(1) + 'MB)');
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
                hostedUrl: card.media.hostedUrl || null,
                assetUuid: card.media.assetUuid || null,
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
    console.log('[RCS Apply] applyRcsContent started');
    saveCurrentCardData();
    
    var validation = validateRcsContent();
    console.log('[RCS Apply] Validation result:', JSON.stringify(validation));
    
    hideRcsValidationErrors();
    
    if (!validation.valid) {
        console.log('[RCS Apply] Validation failed, showing errors');
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
    
    console.log('[RCS Apply] Closing wizard modal');
    closeRcsWizardModal();
    
    setTimeout(function() {
        console.log('[RCS Apply] Calling updateRcsWizardPreviewInMain');
        if (typeof updateRcsWizardPreviewInMain === 'function') {
            updateRcsWizardPreviewInMain();
            console.log('[RCS Apply] Preview updated successfully');
        } else {
            console.log('[RCS Apply] updateRcsWizardPreviewInMain not available');
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
    var hostedUrl = null;
    var displayUrl = null;
    
    if (isCurrentCard) {
        // hostedUrl is the payload URL (single source of truth)
        hostedUrl = rcsMediaData.hostedUrl || null;
        // For preview display, use savedDataUrl first (immediate after save), 
        // then try resolver, then fall back to original url
        console.log('[RCS Schema] isCurrentCard, checking savedDataUrl:', !!rcsMediaData.savedDataUrl);
        if (rcsMediaData.savedDataUrl) {
            displayUrl = rcsMediaData.savedDataUrl;
            console.log('[RCS Schema] Using savedDataUrl, length:', displayUrl.length);
        } else if (hostedUrl) {
            displayUrl = resolveRcsMediaUrl(hostedUrl);
            console.log('[RCS Schema] Using resolved hostedUrl');
        } else {
            displayUrl = rcsMediaData.url;
            console.log('[RCS Schema] Using original url');
        }
    } else {
        // For other cards, use hostedUrl as source of truth
        hostedUrl = (card.media && card.media.hostedUrl) ? card.media.hostedUrl : null;
        // Use savedDataUrl first, then resolver, then original url
        if (card.media && card.media.savedDataUrl) {
            displayUrl = card.media.savedDataUrl;
        } else if (hostedUrl) {
            displayUrl = resolveRcsMediaUrl(hostedUrl);
        } else {
            displayUrl = (card.media && card.media.url) ? card.media.url : null;
        }
    }
    
    return {
        media: displayUrl ? { 
            url: displayUrl,  // Renderable URL for preview
            hostedUrl: hostedUrl,  // Payload URL (source of truth)
            height: heightClass 
        } : null,
        title: description || null,
        description: textBody || null,
        buttons: btns.map(function(btn) {
            return { label: btn.label || 'Button', action: { type: btn.type || 'url' } };
        })
    };
}

function getWizardCarouselCardSchema(cardNum) {
    var card = rcsCardsData[cardNum] || {};
    // hostedUrl is the payload URL (single source of truth)
    var hostedUrl = (card.media && card.media.hostedUrl) ? card.media.hostedUrl : null;
    // Use savedDataUrl first, then resolver, then original url
    var displayUrl = null;
    if (card.media && card.media.savedDataUrl) {
        displayUrl = card.media.savedDataUrl;
    } else if (hostedUrl) {
        displayUrl = resolveRcsMediaUrl(hostedUrl);
    } else {
        displayUrl = (card.media && card.media.url) ? card.media.url : null;
    }
    var btns = card.buttons || [];
    
    var orientationToHeight = { 'vertical_short': 'short', 'vertical_medium': 'medium', 'vertical_tall': 'tall' };
    var mediaHeight = orientationToHeight[rcsCarouselHeight] || 'medium';
    
    return {
        media: displayUrl ? { 
            url: displayUrl,  // Renderable URL for preview
            hostedUrl: hostedUrl,  // Payload URL (source of truth)
            height: mediaHeight 
        } : null,
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
    
    rebuildCardTabs();
    updateRcsCardCount();
    selectRcsCard(rcsCardCount);
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
    
    tabsContainer.querySelectorAll('.rcs-card-tab-wrapper').forEach(function(wrapper) {
        wrapper.remove();
    });
    tabsContainer.querySelectorAll('.rcs-card-tab').forEach(function(tab) {
        tab.remove();
    });
    
    for (var i = 1; i <= rcsCardCount; i++) {
        if (rcsCardCount > 1) {
            // Create wrapper with tab and remove button
            var wrapper = document.createElement('div');
            wrapper.className = 'btn-group btn-group-sm rcs-card-tab-wrapper';
            
            var tab = document.createElement('button');
            tab.type = 'button';
            tab.className = 'btn rcs-card-tab ' + (i === rcsCurrentCard ? 'btn-primary active' : 'btn-outline-primary');
            tab.setAttribute('data-card', i);
            tab.textContent = 'Card ' + i;
            (function(cardNum) {
                tab.onclick = function() { selectRcsCard(cardNum); };
            })(i);
            
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn ' + (i === rcsCurrentCard ? 'btn-primary' : 'btn-outline-primary');
            removeBtn.innerHTML = '<i class="fas fa-times" style="font-size: 10px;"></i>';
            removeBtn.title = 'Remove Card ' + i;
            (function(cardNum) {
                removeBtn.onclick = function(e) { 
                    e.stopPropagation();
                    if (confirm('Remove Card ' + cardNum + '?')) {
                        deleteRcsCard(cardNum);
                    }
                };
            })(i);
            
            wrapper.appendChild(tab);
            wrapper.appendChild(removeBtn);
            tabsContainer.insertBefore(wrapper, addBtn);
        } else {
            // Single card - no remove button needed
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
    // Check dirty state for any image - even after it's been saved once
    var hasImage = rcsMediaData.url || rcsMediaData.file || rcsMediaData.hostedUrl;
    
    if (hasImage) {
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
        // Show save button when:
        // 1. We have an image loaded (from URL, file upload, or previously saved)
        // 2. AND either: it's dirty from edits, OR it's a file upload that hasn't been saved yet
        var hasImage = rcsMediaData.url || rcsMediaData.file || rcsMediaData.hostedUrl;
        var isFileUpload = rcsMediaData.source === 'upload' || rcsMediaData.source === 'file';
        var notYetSaved = !rcsMediaData.hostedUrl;
        var needsInitialSave = isFileUpload && notYetSaved;
        var hasUnsavedChanges = rcsImageDirtyState.isDirty;
        
        var shouldShow = hasImage && (needsInitialSave || hasUnsavedChanges);
        saveBtn.classList.toggle('d-none', !shouldShow);
    }
}

function isRcsImageDirty() {
    var hasImage = rcsMediaData.url || rcsMediaData.file || rcsMediaData.hostedUrl;
    var isFileUpload = rcsMediaData.source === 'upload' || rcsMediaData.source === 'file';
    var notYetSaved = !rcsMediaData.hostedUrl;
    var needsInitialSave = isFileUpload && notYetSaved;
    var hasUnsavedChanges = rcsImageDirtyState.isDirty;
    return hasImage && (needsInitialSave || hasUnsavedChanges);
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

function proxyRcsImageThroughServer(url, callback) {
    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    var headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
    if (csrfToken) {
        headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
    }

    fetch('/api/rcs/assets/proxy-image', {
        method: 'POST',
        headers: headers,
        body: JSON.stringify({ url: url })
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success && data.dataUrl) {
            console.log('[RCS Proxy] Image proxied successfully, size:', data.size);
            callback(data);
        } else {
            console.error('[RCS Proxy] Server returned error:', data.error);
            callback(null);
        }
    })
    .catch(function(err) {
        console.error('[RCS Proxy] Fetch failed:', err);
        callback(null);
    });
}

/**
 * Mock Media Service - stores edited images and generates URLs
 * TODO: Replace with real backend implementation
 */
var rcsMockMediaStore = {};

function generateRcsMockMediaUrl() {
    var uuid = 'rcs-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
    return 'https://qsms.uk/rcs/media/' + uuid;
}

/**
 * Resolve a hosted URL to its actual image data for preview rendering.
 * In production, this would be a direct URL. For mock, we look up stored data.
 * This ensures preview uses the same URL reference as the payload.
 */
function resolveRcsMediaUrl(hostedUrl) {
    if (!hostedUrl) return null;
    
    // Extract UUID from hosted URL
    var uuid = hostedUrl.split('/').pop();
    
    // Look up in mock store
    if (rcsMockMediaStore[uuid] && rcsMockMediaStore[uuid].dataUrl) {
        return rcsMockMediaStore[uuid].dataUrl;
    }
    
    // If not in store (production case), return the URL as-is
    return hostedUrl;
}

function generateCroppedImageDataUrl() {
    return new Promise(function(resolve, reject) {
        try {
            var img = document.getElementById('rcsMediaPreviewImg');
            if (!img || !img.src) {
                console.error('[RCS Crop] No image loaded');
                reject(new Error('No image loaded'));
                return;
            }
            
            // Output canvas matches the crop frame aspect ratio at 2x resolution
            var outputWidth = 720;
            var outputHeight = Math.round(720 * (rcsCropState.frameHeight / rcsCropState.frameWidth)) || 360;
            
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            canvas.width = outputWidth;
            canvas.height = outputHeight;
            
            // Get the natural image dimensions
            var naturalWidth = img.naturalWidth || rcsCropState.imageWidth;
            var naturalHeight = img.naturalHeight || rcsCropState.imageHeight;
            
            // Total display scale combines displayScale (fit to workspace) and zoom
            var totalScale = rcsCropState.displayScale * (rcsCropState.zoom / 100);
            
            // Displayed image size in CSS pixels
            var displayWidth = naturalWidth * totalScale;
            var displayHeight = naturalHeight * totalScale;
            
            // Both the image and crop frame are centered in the workspace when offset=0
            // The frame's left edge relative to image's left edge (in display coords):
            // = (displayWidth - frameWidth) / 2 when centered
            // When offset is applied, image moves by offset, so frame sees: base - offset
            var frameLeftRelativeToImage = (displayWidth - rcsCropState.frameWidth) / 2 - rcsCropState.offsetX;
            var frameTopRelativeToImage = (displayHeight - rcsCropState.frameHeight) / 2 - rcsCropState.offsetY;
            
            // Convert display coordinates to natural image coordinates
            var sourceX = frameLeftRelativeToImage / totalScale;
            var sourceY = frameTopRelativeToImage / totalScale;
            var sourceWidth = rcsCropState.frameWidth / totalScale;
            var sourceHeight = rcsCropState.frameHeight / totalScale;
            
            console.log('[RCS Crop] Natural:', naturalWidth, 'x', naturalHeight);
            console.log('[RCS Crop] Display:', displayWidth.toFixed(1), 'x', displayHeight.toFixed(1), 'totalScale:', totalScale.toFixed(4));
            console.log('[RCS Crop] Frame:', rcsCropState.frameWidth, 'x', rcsCropState.frameHeight);
            console.log('[RCS Crop] Offset:', rcsCropState.offsetX, ',', rcsCropState.offsetY);
            console.log('[RCS Crop] FrameRel:', frameLeftRelativeToImage.toFixed(1), ',', frameTopRelativeToImage.toFixed(1));
            console.log('[RCS Crop] Source rect:', sourceX.toFixed(1), sourceY.toFixed(1), sourceWidth.toFixed(1), sourceHeight.toFixed(1));
            
            // Clamp source coordinates to valid image bounds
            var clampedSourceX = Math.max(0, sourceX);
            var clampedSourceY = Math.max(0, sourceY);
            var clampedSourceWidth = Math.min(sourceWidth, naturalWidth - clampedSourceX);
            var clampedSourceHeight = Math.min(sourceHeight, naturalHeight - clampedSourceY);
            
            // Calculate corresponding destination rectangle
            var destX = (clampedSourceX - sourceX) / sourceWidth * outputWidth;
            var destY = (clampedSourceY - sourceY) / sourceHeight * outputHeight;
            var destWidth = clampedSourceWidth / sourceWidth * outputWidth;
            var destHeight = clampedSourceHeight / sourceHeight * outputHeight;
            
            // Fill with background color for any areas outside the image
            ctx.fillStyle = '#f0f0f0';
            ctx.fillRect(0, 0, outputWidth, outputHeight);
            
            // Draw the cropped region
            if (clampedSourceWidth > 0 && clampedSourceHeight > 0) {
                ctx.drawImage(
                    img,
                    clampedSourceX, clampedSourceY, clampedSourceWidth, clampedSourceHeight,
                    destX, destY, destWidth, destHeight
                );
            }
            
            try {
                var dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                console.log('[RCS Crop] Generated data URL, length:', dataUrl.length);
                resolve(dataUrl);
            } catch (canvasError) {
                console.error('[RCS Crop] Canvas toDataURL failed (CORS?):', canvasError);
                if (rcsMediaData.source === 'upload' && rcsMediaData.url && rcsMediaData.url.startsWith('data:')) {
                    console.log('[RCS Crop] Using original data URL for file upload');
                    resolve(rcsMediaData.url);
                } else {
                    console.log('[RCS Crop] Fallback to original URL');
                    resolve(rcsMediaData.url || rcsMediaData.originalUrl);
                }
            }
        } catch (e) {
            console.error('[RCS Crop] Unexpected error:', e);
            resolve(rcsMediaData.url || rcsMediaData.originalUrl);
        }
    });
}

function saveRcsImageEdits() {
    if (!rcsMediaData.url && !rcsMediaData.originalUrl) return;
    
    showRcsProcessingIndicator();
    var editParams = getCurrentEditParams();
    
    // Generate cropped image and save
    generateCroppedImageDataUrl().then(function(croppedDataUrl) {
        // Generate mock hosted URL
        var mockUrl = generateRcsMockMediaUrl();
        var uuid = mockUrl.split('/').pop();
        
        // Store in mock media store for preview
        rcsMockMediaStore[uuid] = {
            dataUrl: croppedDataUrl,
            originalUrl: rcsMediaData.originalUrl || rcsMediaData.url,
            editParams: editParams,
            timestamp: Date.now()
        };
        
        // Update media data
        rcsMediaData.assetUuid = uuid;
        rcsMediaData.hostedUrl = mockUrl;
        rcsMediaData.savedDataUrl = croppedDataUrl; // Store for preview
        
        console.log('[RCS Save] savedDataUrl set, length:', croppedDataUrl ? croppedDataUrl.length : 0);
        console.log('[RCS Save] rcsMediaData.savedDataUrl exists:', !!rcsMediaData.savedDataUrl);
        
        // Show the hosted URL section
        showRcsHostedUrl(mockUrl);
        
        // Update image info
        updateRcsImageInfo();
        
        // Clear dirty state
        clearRcsImageDirtyState();
        initRcsImageBaseline();
        
        // Save to current card and update preview
        saveCurrentCardData();
        updateRcsWizardPreview();
        
        hideRcsProcessingIndicator();
        
        console.log('[RCS Save] Image saved with URL:', mockUrl);
    }).catch(function(err) {
        hideRcsProcessingIndicator();
        console.error('[RCS Save Error]', err);
        showRcsMediaError('Failed to process image: ' + (err.message || 'Please try again.'));
    });
}

function showRcsHostedUrl(url) {
    var section = document.getElementById('rcsHostedUrlSection');
    var input = document.getElementById('rcsHostedUrlDisplay');
    var saveBtn = document.getElementById('rcsImageSaveBtn');
    
    if (section && input) {
        input.value = url;
        section.classList.remove('d-none');
    }
    
    // Hide save button since already saved
    if (saveBtn) {
        saveBtn.classList.add('d-none');
    }
}

function hideRcsHostedUrl() {
    var section = document.getElementById('rcsHostedUrlSection');
    if (section) {
        section.classList.add('d-none');
    }
}

function copyRcsHostedUrl() {
    var input = document.getElementById('rcsHostedUrlDisplay');
    if (!input || !input.value) return;
    
    navigator.clipboard.writeText(input.value).then(function() {
        // Show brief success feedback
        var btn = input.nextElementSibling;
        if (btn) {
            var originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-success"></i>';
            setTimeout(function() {
                btn.innerHTML = originalIcon;
            }, 1500);
        }
    }).catch(function(err) {
        console.error('[Copy Error]', err);
        // Fallback for older browsers
        input.select();
        document.execCommand('copy');
    });
}

function saveRcsImageEditsAndContinue() {
    if (!rcsMediaData.url && !rcsMediaData.originalUrl) {
        hideRcsUnsavedChangesModal();
        executePendingNavigation();
        return;
    }
    
    showRcsProcessingIndicator();
    var editParams = getCurrentEditParams();
    
    // Generate cropped image and save using mock service
    generateCroppedImageDataUrl().then(function(croppedDataUrl) {
        var mockUrl = generateRcsMockMediaUrl();
        var uuid = mockUrl.split('/').pop();
        
        rcsMockMediaStore[uuid] = {
            dataUrl: croppedDataUrl,
            originalUrl: rcsMediaData.originalUrl || rcsMediaData.url,
            editParams: editParams,
            timestamp: Date.now()
        };
        
        rcsMediaData.assetUuid = uuid;
        rcsMediaData.hostedUrl = mockUrl;
        rcsMediaData.savedDataUrl = croppedDataUrl;
        
        showRcsHostedUrl(mockUrl);
        updateRcsImageInfo();
        clearRcsImageDirtyState();
        initRcsImageBaseline();
        saveCurrentCardData();
        updateRcsWizardPreview();
        
        hideRcsProcessingIndicator();
        hideRcsUnsavedChangesModal();
        executePendingNavigation();
        
        console.log('[RCS Save] Image saved with URL:', mockUrl);
    }).catch(function(err) {
        hideRcsProcessingIndicator();
        console.error('[RCS Save Error]', err);
        showRcsMediaError('Failed to process image: ' + (err.message || 'Please try again.'));
    });
}

function discardRcsImageEdits() {
    hideRcsUnsavedChangesModal();
    
    // Save pending navigation before clearing dirty state (which clears it)
    var savedPendingAction = rcsImageDirtyState.pendingNavigation;
    
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
    
    // Restore and execute the pending navigation
    rcsImageDirtyState.pendingNavigation = savedPendingAction;
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
        closeRcsWizardModal();
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
        closeRcsWizardModal();
    }
}

function closeRcsWizardModal() {
    var modalEl = document.getElementById('rcsWizardModal');
    if (!modalEl) {
        console.error('[RCS Wizard] Modal element not found');
        return;
    }
    var modalInstance = bootstrap.Modal.getInstance(modalEl);
    if (modalInstance) {
        modalInstance.hide();
    } else {
        console.warn('[RCS Wizard] Modal instance not found, trying to create one');
        try {
            var newModal = new bootstrap.Modal(modalEl);
            newModal.hide();
        } catch (e) {
            console.error('[RCS Wizard] Failed to close modal:', e);
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            document.body.classList.remove('modal-open');
            var backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }
    }
}

function handleRcsApplyContent() {
    console.log('[RCS Apply] handleRcsApplyContent called');
    var dirty = isRcsImageDirty();
    console.log('[RCS Apply] isRcsImageDirty:', dirty);
    console.log('[RCS Apply] rcsMediaData:', JSON.stringify(rcsMediaData));
    console.log('[RCS Apply] rcsImageDirtyState:', JSON.stringify(rcsImageDirtyState));
    
    if (dirty) {
        console.log('[RCS Apply] Showing unsaved changes modal');
        showRcsUnsavedChangesModal({ type: 'applyContent' });
    } else {
        console.log('[RCS Apply] Calling applyRcsContent directly');
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
            var onImageLoaded = function(previewSrc) {
                rcsMediaData.source = 'url';
                rcsMediaData.url = previewSrc || url;
                rcsMediaData.originalUrl = url;
                rcsMediaData.dimensions = { width: img.width, height: img.height };
                rcsMediaData.fileSize = metadata.fileSize || 0;
                rcsMediaData.assetUuid = null;
                rcsMediaData.hostedUrl = null;
                showRcsMediaPreview(previewSrc || url);
                updateRcsImageInfo();
                initRcsImageBaseline();
                
                if (metadata.warnLargeFile) {
                    var sizeMB = (metadata.fileSize / (1024 * 1024)).toFixed(1);
                    showRcsMediaWarning('This file is ' + sizeMB + ' MB. Large media may load slowly and may not render optimally on handsets.');
                }
                
                if (loadBtn) {
                    loadBtn.disabled = false;
                    loadBtn.innerHTML = '<i class="fas fa-check"></i>';
                }
            };
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                onImageLoaded(url);
            };
            img.onerror = function() {
                console.log('[RCS Load] CORS blocked in loadRcsMediaUrl, proxying:', url);
                proxyRcsImageThroughServer(url, function(proxyResult) {
                    if (proxyResult && proxyResult.dataUrl) {
                        var proxyImg = new Image();
                        proxyImg.onload = function() {
                            img = proxyImg;
                            onImageLoaded(proxyResult.dataUrl);
                        };
                        proxyImg.onerror = function() {
                            showRcsMediaError('Media could not be fetched. The URL may not be publicly accessible or may not point to a valid image.');
                            if (loadBtn) {
                                loadBtn.disabled = false;
                                loadBtn.innerHTML = '<i class="fas fa-check"></i>';
                            }
                        };
                        proxyImg.src = proxyResult.dataUrl;
                    } else {
                        showRcsMediaError('Media could not be fetched. The URL may not be publicly accessible or may not point to a valid image.');
                        if (loadBtn) {
                            loadBtn.disabled = false;
                            loadBtn.innerHTML = '<i class="fas fa-check"></i>';
                        }
                    }
                });
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
    rcsMediaData = { source: null, url: null, file: null, dimensions: null, fileSize: 0, assetUuid: null, hostedUrl: null, originalUrl: null, savedDataUrl: null };
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
    if (urlInput) urlInput.value = 'https://';
    if (fileInput) fileInput.value = '';
    if (zoomSlider) zoomSlider.value = 100;
    
    // Hide the hosted URL section
    hideRcsHostedUrl();
    
    if (typeof window.rcsMediaReset === 'function') {
        window.rcsMediaReset();
    }
    
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
    
    var scale = rcsCropState.displayScale * (rcsCropState.zoom / 100);
    var displayWidth = rcsCropState.imageWidth * scale;
    var displayHeight = rcsCropState.imageHeight * scale;
    
    var frameWidth = rcsCropState.frameWidth;
    var frameHeight = rcsCropState.frameHeight;
    
    if (displayWidth > frameWidth) {
        var maxOffsetX = (displayWidth - frameWidth) / 2;
        var minOffsetX = -maxOffsetX;
        rcsCropState.offsetX = Math.max(minOffsetX, Math.min(maxOffsetX, rcsCropState.offsetX));
    } else {
        rcsCropState.offsetX = 0;
    }
    
    if (displayHeight > frameHeight) {
        var maxOffsetY = (displayHeight - frameHeight) / 2;
        var minOffsetY = -maxOffsetY;
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
    
    setRcsButtonTrackingData(btn.tracking, btn.callback_data, btn.callback_data_mode);
    updateRcsButtonUtmVisibility();
    updateRcsCallbackDataPreview();
    
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
    if (urlEl) urlEl.value = 'https://';
    if (phoneEl) phoneEl.value = '';
    if (titleEl) titleEl.value = '';
    if (startEl) startEl.value = '';
    if (endEl) endEl.value = '';
    if (descEl) descEl.value = '';
    hideAllRcsButtonErrors();
    resetRcsButtonTracking();
}

function resetRcsButtonTracking() {
    var trackingConfig = document.getElementById('rcsButtonTrackingConfig');
    var chevron = document.getElementById('rcsAdvancedChevron');
    var trackingEnabled = document.getElementById('rcsButtonTrackingEnabled');
    var trackingId = document.getElementById('rcsButtonTrackingId');
    var utmSource = document.getElementById('rcsButtonUtmSource');
    var utmMedium = document.getElementById('rcsButtonUtmMedium');
    var utmCampaign = document.getElementById('rcsButtonUtmCampaign');
    var utmContent = document.getElementById('rcsButtonUtmContent');
    var trackConversion = document.getElementById('rcsButtonTrackConversion');
    var callbackDataEl = document.getElementById('rcsButtonCallbackData');
    var callbackDataAuto = document.getElementById('rcsCallbackDataAuto');
    var callbackDataCustom = document.getElementById('rcsButtonCallbackDataCustom');
    var autoPreview = document.getElementById('rcsCallbackDataAutoPreview');
    var customInput = document.getElementById('rcsCallbackDataCustomInput');
    
    if (trackingConfig) trackingConfig.classList.add('d-none');
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (trackingEnabled) trackingEnabled.checked = true;
    if (trackingId) trackingId.value = '';
    if (utmSource) utmSource.value = '';
    if (utmMedium) utmMedium.value = '';
    if (utmCampaign) utmCampaign.value = '';
    if (utmContent) utmContent.value = '';
    if (trackConversion) trackConversion.checked = false;
    if (callbackDataEl) callbackDataEl.value = '';
    if (callbackDataAuto) callbackDataAuto.checked = true;
    if (callbackDataCustom) callbackDataCustom.value = '';
    if (autoPreview) autoPreview.classList.remove('d-none');
    if (customInput) customInput.classList.add('d-none');
    clearRcsCallbackDataError();
    
    var lengthEl = document.getElementById('rcsCallbackDataLength');
    if (lengthEl) lengthEl.textContent = '0';
}

function toggleRcsButtonAdvanced() {
    var trackingConfig = document.getElementById('rcsButtonTrackingConfig');
    var chevron = document.getElementById('rcsAdvancedChevron');
    
    if (trackingConfig) {
        var isHidden = trackingConfig.classList.contains('d-none');
        trackingConfig.classList.toggle('d-none', !isHidden);
        if (chevron) {
            chevron.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
        }
    }
    updateRcsButtonUtmVisibility();
    updateRcsCallbackDataPreview();
}

function toggleRcsCallbackDataMode() {
    var autoRadio = document.getElementById('rcsCallbackDataAuto');
    var autoPreview = document.getElementById('rcsCallbackDataAutoPreview');
    var customInput = document.getElementById('rcsCallbackDataCustomInput');
    
    var isAuto = autoRadio && autoRadio.checked;
    
    if (autoPreview) autoPreview.classList.toggle('d-none', !isAuto);
    if (customInput) customInput.classList.toggle('d-none', isAuto);
    
    if (isAuto) {
        updateRcsCallbackDataPreview();
        clearRcsCallbackDataError();
    } else {
        validateRcsCallbackData();
        updateRcsCallbackDataLength();
    }
}

function validateRcsCallbackData() {
    var customInput = document.getElementById('rcsButtonCallbackDataCustom');
    var errorEl = document.getElementById('rcsCallbackDataCustomError');
    var helpEl = document.getElementById('rcsCallbackDataCustomHelp');
    var inputField = customInput;
    
    if (!customInput || !errorEl) return true;
    
    var value = customInput.value;
    updateRcsCallbackDataLength();
    
    var error = null;
    
    if (value.length > 64) {
        error = 'Maximum 64 characters allowed.';
    }
    else if (!/^[\x00-\x7F]*$/.test(value)) {
        error = 'Only ASCII characters are allowed.';
    }
    else if (/\{\{[^}]+\}\}/.test(value)) {
        error = 'Personalisation placeholders like {{firstName}} are not allowed.';
    }
    else if (/%[A-Z_]+%/.test(value)) {
        error = 'Variable patterns like %VAR% are not allowed.';
    }
    else if (/\[\[[^\]]+\]\]/.test(value)) {
        error = 'Placeholder patterns like [[field]] are not allowed.';
    }
    
    if (error) {
        errorEl.textContent = error;
        errorEl.classList.remove('d-none');
        if (helpEl) helpEl.classList.add('d-none');
        if (inputField) inputField.classList.add('is-invalid');
        return false;
    } else {
        clearRcsCallbackDataError();
        return true;
    }
}

function clearRcsCallbackDataError() {
    var errorEl = document.getElementById('rcsCallbackDataCustomError');
    var helpEl = document.getElementById('rcsCallbackDataCustomHelp');
    var inputField = document.getElementById('rcsButtonCallbackDataCustom');
    
    if (errorEl) {
        errorEl.textContent = '';
        errorEl.classList.add('d-none');
    }
    if (helpEl) helpEl.classList.remove('d-none');
    if (inputField) inputField.classList.remove('is-invalid');
}

function updateRcsCallbackDataLength() {
    var customInput = document.getElementById('rcsButtonCallbackDataCustom');
    var lengthEl = document.getElementById('rcsCallbackDataLength');
    
    if (customInput && lengthEl) {
        lengthEl.textContent = customInput.value.length;
    }
}

function updateRcsCallbackDataPreview() {
    var previewEl = document.getElementById('rcsButtonCallbackDataPreview');
    if (!previewEl) return;
    
    var buttonIndex = rcsEditingButtonIndex >= 0 ? rcsEditingButtonIndex : rcsButtons.length;
    var callbackData = generateRcsCallbackData(rcsCurrentCard, buttonIndex);
    previewEl.textContent = callbackData;
}

function updateRcsButtonUtmVisibility() {
    var checkedType = document.querySelector('input[name="rcsButtonType"]:checked');
    var type = checkedType ? checkedType.value : 'url';
    var utmSection = document.getElementById('rcsButtonUtmSection');
    
    if (utmSection) {
        utmSection.classList.toggle('d-none', type !== 'url');
    }
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
    updateRcsButtonUtmVisibility();
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
    
    var autoRadio = document.getElementById('rcsCallbackDataAuto');
    var isAutoCallback = autoRadio ? autoRadio.checked : true;
    if (!isAutoCallback && !validateRcsCallbackData()) {
        valid = false;
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
    
    buttonData.tracking = getRcsButtonTrackingData(type);
    
    var buttonIndex;
    if (rcsEditingButtonIndex >= 0) {
        buttonIndex = rcsEditingButtonIndex;
        rcsButtons[rcsEditingButtonIndex] = buttonData;
    } else {
        buttonIndex = rcsButtons.length;
        rcsButtons.push(buttonData);
    }
    
    if (buttonData.tracking && buttonData.tracking.enabled) {
        buttonData.callback_data = getRcsButtonCallbackData(rcsCurrentCard, buttonIndex);
        buttonData.callback_data_mode = document.getElementById('rcsCallbackDataAuto').checked ? 'auto' : 'custom';
    } else {
        buttonData.callback_data = null;
        buttonData.callback_data_mode = null;
    }
    
    bootstrap.Modal.getInstance(document.getElementById('rcsButtonConfigModal')).hide();
    renderRcsButtons();
    updateRcsButtonsPreview();
}

function migrateRcsButtonData(buttonData, cardIndex, buttonIndex) {
    if (!buttonData) return null;
    
    var hasTrackingField = buttonData.hasOwnProperty('tracking') && buttonData.tracking !== null;
    var hasCallbackMode = buttonData.hasOwnProperty('callback_data_mode');
    
    if (!hasTrackingField) {
        buttonData.tracking = {
            enabled: true,
            trackingId: '',
            events: { click: true, conversion: false },
            utm: null
        };
    }
    
    if (!hasCallbackMode) {
        buttonData.callback_data_mode = 'auto';
    }
    
    var trackingEnabled = buttonData.tracking ? buttonData.tracking.enabled !== false : true;
    if (trackingEnabled && !buttonData.callback_data) {
        buttonData.callback_data = generateRcsCallbackData(cardIndex || 1, buttonIndex || 0);
    }
    
    return buttonData;
}

function normalizeRcsButtonData(buttonData) {
    var normalized = {
        label: buttonData.label || '',
        type: buttonData.type || 'url',
        
        tracking_enabled: buttonData.tracking ? buttonData.tracking.enabled !== false : true,
        callback_mode: buttonData.callback_data_mode || 'auto',
        callback_data: buttonData.callback_data || null,
        
        tracking: buttonData.tracking || null
    };
    
    if (buttonData.type === 'url') {
        normalized.url = buttonData.url || '';
    } else if (buttonData.type === 'phone') {
        normalized.phone = buttonData.phone || '';
    } else if (buttonData.type === 'calendar') {
        normalized.eventTitle = buttonData.eventTitle || '';
        normalized.eventStart = buttonData.eventStart || '';
        normalized.eventEnd = buttonData.eventEnd || '';
        normalized.eventDesc = buttonData.eventDesc || '';
    }
    
    return normalized;
}

function loadRcsButtonsFromSaved(savedButtons, cardIndex) {
    if (!savedButtons || !Array.isArray(savedButtons)) {
        return [];
    }
    
    return savedButtons.map(function(btn, index) {
        return migrateRcsButtonData(btn, cardIndex || 1, index);
    });
}

function loadRcsCardsFromSaved(savedCards) {
    if (!savedCards || !Array.isArray(savedCards)) {
        return {};
    }
    
    var cardsData = {};
    savedCards.forEach(function(card, index) {
        var cardIndex = card.cardIndex || (index + 1);
        cardsData[cardIndex] = {
            title: card.title || '',
            description: card.description || '',
            media: card.media || null,
            buttons: loadRcsButtonsFromSaved(card.buttons, cardIndex)
        };
    });
    
    return cardsData;
}

function getRcsButtonsPayload() {
    return rcsButtons.map(function(btn) {
        return normalizeRcsButtonData(btn);
    });
}

function serializeRcsButtonForSend(buttonData, cardIndex, buttonIndex) {
    var payload = {
        label: buttonData.label || '',
        type: buttonData.type || 'url'
    };
    
    if (buttonData.type === 'url') {
        payload.url = buttonData.url || '';
    } else if (buttonData.type === 'phone') {
        payload.phone = buttonData.phone || '';
    } else if (buttonData.type === 'calendar') {
        payload.calendar = {
            title: buttonData.eventTitle || '',
            start: buttonData.eventStart || '',
            end: buttonData.eventEnd || '',
            description: buttonData.eventDesc || ''
        };
    }
    
    var trackingEnabled = buttonData.tracking ? buttonData.tracking.enabled !== false : true;
    
    if (trackingEnabled) {
        var callbackMode = buttonData.callback_data_mode || 'auto';
        
        if (callbackMode === 'auto') {
            payload.callback_data = generateRcsCallbackData(cardIndex, buttonIndex);
        } else if (callbackMode === 'custom' && buttonData.callback_data) {
            payload.callback_data = buttonData.callback_data;
        } else {
            payload.callback_data = generateRcsCallbackData(cardIndex, buttonIndex);
        }
        
        payload.tracking = {
            enabled: true,
            trackingId: buttonData.tracking ? buttonData.tracking.trackingId || '' : '',
            events: buttonData.tracking ? buttonData.tracking.events : { click: true, conversion: false }
        };
        
        if (buttonData.type === 'url' && buttonData.tracking && buttonData.tracking.utm) {
            payload.tracking.utm = buttonData.tracking.utm;
        }
    } else {
        payload.tracking = {
            enabled: false
        };
    }
    
    return payload;
}

function serializeRcsButtonsForSend(cardIndex) {
    cardIndex = cardIndex || rcsCurrentCard;
    return rcsButtons.map(function(btn, index) {
        return serializeRcsButtonForSend(btn, cardIndex, index);
    });
}

function getRcsSendPayload() {
    var payload = {
        messageType: document.querySelector('input[name="rcsMessageType"]:checked')?.value || 'single',
        cards: []
    };
    
    if (payload.messageType === 'single') {
        payload.cards.push({
            cardIndex: 1,
            title: document.getElementById('rcsCardTitle')?.value || '',
            description: document.getElementById('rcsCardDescription')?.value || '',
            media: rcsMediaData.hostedUrl || rcsMediaData.url || null,
            buttons: serializeRcsButtonsForSend(1)
        });
    } else {
        for (var i = 1; i <= rcsCardCount; i++) {
            var cardData = rcsCardsData[i] || {};
            payload.cards.push({
                cardIndex: i,
                title: cardData.title || '',
                description: cardData.description || '',
                media: cardData.media?.hostedUrl || cardData.media?.url || null,
                buttons: (cardData.buttons || []).map(function(btn, btnIndex) {
                    return serializeRcsButtonForSend(btn, i, btnIndex);
                })
            });
        }
    }
    
    return payload;
}

function getRcsReportingMetadata(containerId, containerType) {
    containerType = containerType || 'campaign';
    var metadata = {
        container_id: containerId || getRcsContainerId(),
        container_type: containerType,
        created_at: new Date().toISOString(),
        buttons: []
    };
    
    var messageType = document.querySelector('input[name="rcsMessageType"]:checked')?.value || 'single';
    
    if (messageType === 'single') {
        rcsButtons.forEach(function(btn, btnIndex) {
            metadata.buttons.push({
                card_index: 1,
                button_index: btnIndex + 1,
                button_label: btn.label || '',
                button_type: btn.type || 'url',
                tracking_enabled: btn.tracking ? btn.tracking.enabled !== false : true,
                callback_data: btn.callback_data || null
            });
        });
    } else {
        for (var i = 1; i <= rcsCardCount; i++) {
            var cardData = rcsCardsData[i] || {};
            (cardData.buttons || []).forEach(function(btn, btnIndex) {
                metadata.buttons.push({
                    card_index: i,
                    button_index: btnIndex + 1,
                    button_label: btn.label || '',
                    button_type: btn.type || 'url',
                    tracking_enabled: btn.tracking ? btn.tracking.enabled !== false : true,
                    callback_data: btn.callback_data || null
                });
            });
        }
    }
    
    metadata.total_buttons = metadata.buttons.length;
    metadata.tracked_buttons = metadata.buttons.filter(function(b) { return b.tracking_enabled; }).length;
    
    return metadata;
}

function getButtonClickReportingPayload(containerId, cardIndex, buttonIndex, buttonData) {
    return {
        container_id: containerId || getRcsContainerId(),
        card_index: cardIndex,
        button_index: buttonIndex,
        button_label: buttonData.label || '',
        button_type: buttonData.type || 'url',
        tracking_enabled: buttonData.tracking ? buttonData.tracking.enabled !== false : true,
        callback_data: buttonData.callback_data || null,
        tracking_id: buttonData.tracking ? buttonData.tracking.trackingId || null : null
    };
}

function generateRcsCallbackData(cardIndex, buttonIndex) {
    var containerId = getRcsContainerId();
    var callbackData = 'qsms:c' + containerId + ':card' + cardIndex + ':btn' + (buttonIndex + 1);
    
    if (callbackData.length > 64) {
        callbackData = callbackData.substring(0, 64);
    }
    
    return callbackData;
}

function getRcsContainerId() {
    if (window.rcsContainerId) {
        return window.rcsContainerId;
    }
    
    if (window.rcsCampaignId) {
        return window.rcsCampaignId;
    }
    
    if (window.rcsTemplateId) {
        return 't' + window.rcsTemplateId;
    }
    
    var sessionHash = rcsDraftSession.replace(/[^a-zA-Z0-9]/g, '').substring(0, 12);
    return 'd' + sessionHash;
}

function getRcsButtonTrackingData(buttonType) {
    var trackingEnabled = document.getElementById('rcsButtonTrackingEnabled');
    var trackingId = document.getElementById('rcsButtonTrackingId');
    var trackConversion = document.getElementById('rcsButtonTrackConversion');
    
    var isEnabled = trackingEnabled ? trackingEnabled.checked : true;
    
    var trackingData = {
        enabled: isEnabled,
        trackingId: trackingId ? trackingId.value.trim() : '',
        events: {
            click: isEnabled,
            conversion: trackConversion ? trackConversion.checked : false
        }
    };
    
    if (buttonType === 'url') {
        var utmSource = document.getElementById('rcsButtonUtmSource');
        var utmMedium = document.getElementById('rcsButtonUtmMedium');
        var utmCampaign = document.getElementById('rcsButtonUtmCampaign');
        var utmContent = document.getElementById('rcsButtonUtmContent');
        
        trackingData.utm = {
            source: utmSource ? utmSource.value.trim() : '',
            medium: utmMedium ? utmMedium.value.trim() : '',
            campaign: utmCampaign ? utmCampaign.value.trim() : '',
            content: utmContent ? utmContent.value.trim() : ''
        };
    }
    
    return trackingData;
}

function getRcsButtonCallbackData(cardIndex, buttonIndex) {
    var autoRadio = document.getElementById('rcsCallbackDataAuto');
    var customInput = document.getElementById('rcsButtonCallbackDataCustom');
    
    var isAuto = autoRadio ? autoRadio.checked : true;
    
    if (isAuto) {
        return generateRcsCallbackData(cardIndex, buttonIndex);
    } else {
        var customValue = customInput ? customInput.value.trim() : '';
        return customValue || generateRcsCallbackData(cardIndex, buttonIndex);
    }
}

function setRcsButtonTrackingData(tracking, callbackData, callbackDataMode) {
    var trackingConfig = document.getElementById('rcsButtonTrackingConfig');
    var chevron = document.getElementById('rcsAdvancedChevron');
    var trackingEnabled = document.getElementById('rcsButtonTrackingEnabled');
    var trackingId = document.getElementById('rcsButtonTrackingId');
    var utmSource = document.getElementById('rcsButtonUtmSource');
    var utmMedium = document.getElementById('rcsButtonUtmMedium');
    var utmCampaign = document.getElementById('rcsButtonUtmCampaign');
    var utmContent = document.getElementById('rcsButtonUtmContent');
    var trackConversion = document.getElementById('rcsButtonTrackConversion');
    var callbackDataAuto = document.getElementById('rcsCallbackDataAuto');
    var callbackDataCustomRadio = document.getElementById('rcsCallbackDataCustom');
    var callbackDataCustomInput = document.getElementById('rcsButtonCallbackDataCustom');
    var autoPreview = document.getElementById('rcsCallbackDataAutoPreview');
    var customInput = document.getElementById('rcsCallbackDataCustomInput');
    var previewEl = document.getElementById('rcsButtonCallbackDataPreview');
    
    resetRcsButtonTracking();
    
    if (callbackDataMode === 'custom' && callbackData) {
        if (callbackDataCustomRadio) callbackDataCustomRadio.checked = true;
        if (callbackDataCustomInput) callbackDataCustomInput.value = callbackData;
        if (autoPreview) autoPreview.classList.add('d-none');
        if (customInput) customInput.classList.remove('d-none');
    } else if (previewEl && callbackData) {
        previewEl.textContent = callbackData;
    }
    
    if (!tracking) return;
    
    if (trackingEnabled) {
        trackingEnabled.checked = tracking.enabled !== false;
    }
    if (trackingId) {
        trackingId.value = tracking.trackingId || '';
    }
    if (tracking.utm) {
        if (utmSource) utmSource.value = tracking.utm.source || '';
        if (utmMedium) utmMedium.value = tracking.utm.medium || '';
        if (utmCampaign) utmCampaign.value = tracking.utm.campaign || '';
        if (utmContent) utmContent.value = tracking.utm.content || '';
    }
    if (trackConversion && tracking.events) {
        trackConversion.checked = tracking.events.conversion || false;
    }
}

function renderRcsButtons() {
    var container = document.getElementById('rcsButtonsList');
    if (!container) return;
    container.innerHTML = '';
    
    rcsButtons.forEach(function(btn, index) {
        var typeIcon = btn.type === 'url' ? 'fa-link' : btn.type === 'phone' ? 'fa-phone' : 'fa-calendar-plus';
        var typeLabel = btn.type === 'url' ? 'URL' : btn.type === 'phone' ? 'Call' : 'Calendar';
        var hasTracking = btn.tracking && btn.tracking.enabled;
        var trackingDisabled = btn.tracking && btn.tracking.enabled === false;
        
        var html = '<div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2" style="background: rgba(136, 108, 192, 0.15);">';
        html += '<div class="d-flex align-items-center flex-wrap gap-1">';
        html += '<span class="me-2" style="color: #6c5b9e;"><i class="fas ' + typeIcon + '"></i></span>';
        html += '<span class="small fw-medium" style="color: #6c5b9e;">' + escapeHtmlRcs(btn.label) + '</span>';
        html += '<span class="badge small" style="background: rgba(136, 108, 192, 0.25); color: #6c5b9e;">' + typeLabel + '</span>';
        if (hasTracking) {
            html += '<span class="badge small" style="background: rgba(40, 167, 69, 0.2); color: #28a745;" title="Click tracking enabled"><i class="fas fa-chart-line me-1"></i>Tracked</span>';
        } else if (trackingDisabled) {
            html += '<span class="badge small" style="background: rgba(108, 117, 125, 0.15); color: #6c757d;" title="Tracking disabled"><i class="fas fa-chart-line me-1"></i>No tracking</span>';
        }
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
    // Close button handler - properly manages unsaved changes
    var closeBtn = document.getElementById('rcsWizardCloseBtn');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleRcsWizardClose();
        });
    }
    
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
    
    // Initialize https:// prefill behavior for all URL inputs
    initRcsHttpsPrefill();
    
    // Footer button handlers - using addEventListener for reliable event binding
    var cancelBtn = document.getElementById('rcsWizardCancelBtn');
    if (cancelBtn) {
        console.log('[RCS Wizard] Cancel button found, binding event');
        cancelBtn.addEventListener('click', function(e) {
            console.log('[RCS Wizard] Cancel button clicked');
            e.preventDefault();
            e.stopPropagation();
            handleRcsWizardClose();
        });
    } else {
        console.log('[RCS Wizard] Cancel button not found');
    }
    
    var applyBtn = document.getElementById('rcsApplyContentBtn');
    if (applyBtn) {
        console.log('[RCS Wizard] Apply button found, binding event');
        applyBtn.addEventListener('click', function(e) {
            console.log('[RCS Wizard] Apply button clicked');
            e.preventDefault();
            e.stopPropagation();
            handleRcsApplyContent();
        });
    } else {
        console.log('[RCS Wizard] Apply button not found');
    }
    
    console.log('[RCS Wizard] DOMContentLoaded complete');
});

/**
 * Initialize https:// prefix protection for URL inputs
 * Prevents deletion of prefix, handles paste sensibly
 */
function initRcsHttpsPrefill() {
    document.querySelectorAll('.rcs-https-prefill').forEach(function(input) {
        var prefix = input.getAttribute('data-prefix') || 'https://';
        
        // Ensure prefix is present on focus
        input.addEventListener('focus', function() {
            if (!this.value || this.value.length < prefix.length) {
                this.value = prefix;
            }
            // Position cursor after prefix
            setTimeout(function() {
                if (input.value === prefix) {
                    input.setSelectionRange(prefix.length, prefix.length);
                }
            }, 0);
        });
        
        // Protect prefix on input
        input.addEventListener('input', function() {
            protectHttpsPrefix(this, prefix);
        });
        
        // Handle paste - strip duplicate https://
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            var pastedText = (e.clipboardData || window.clipboardData).getData('text').trim();
            
            // Remove any existing http:// or https:// from pasted text
            var cleanUrl = pastedText.replace(/^https?:\/\//i, '');
            
            // Set the full URL with our prefix
            this.value = prefix + cleanUrl;
            
            // Trigger input event for validation
            this.dispatchEvent(new Event('input', { bubbles: true }));
        });
        
        // Prevent selecting/deleting prefix via keyboard
        input.addEventListener('keydown', function(e) {
            var selStart = this.selectionStart;
            var selEnd = this.selectionEnd;
            
            // Prevent backspace from deleting into prefix
            if (e.key === 'Backspace' && selStart <= prefix.length && selEnd <= prefix.length) {
                e.preventDefault();
                return;
            }
            
            // Prevent delete from deleting into prefix when cursor is at prefix end
            if (e.key === 'Delete' && selStart < prefix.length) {
                e.preventDefault();
                this.setSelectionRange(prefix.length, prefix.length);
                return;
            }
            
            // Prevent selecting before prefix
            if ((e.key === 'Home' || (e.key === 'a' && (e.ctrlKey || e.metaKey)))) {
                // Allow but we'll fix selection on keyup
            }
        });
        
        // Fix cursor position after keyboard navigation
        input.addEventListener('keyup', function() {
            if (this.selectionStart < prefix.length) {
                this.setSelectionRange(prefix.length, Math.max(prefix.length, this.selectionEnd));
            }
        });
        
        // Fix selection on click
        input.addEventListener('click', function() {
            if (this.selectionStart < prefix.length) {
                this.setSelectionRange(prefix.length, Math.max(prefix.length, this.selectionEnd));
            }
        });
    });
}

/**
 * Protect the https:// prefix from being deleted
 */
function protectHttpsPrefix(input, prefix) {
    var value = input.value;
    
    // If value doesn't start with prefix, restore it
    if (!value.toLowerCase().startsWith(prefix.toLowerCase())) {
        // Try to salvage any URL content after the prefix was damaged
        var cleanValue = value.replace(/^https?:?\/?\/?/i, '');
        input.value = prefix + cleanValue;
        input.setSelectionRange(prefix.length, prefix.length + cleanValue.length);
    }
}

/**
 * Validate HTTPS URL and show error
 */
function validateRcsHttpsUrl(input) {
    var value = input.value.trim();
    var errorEl = input.parentElement.querySelector('.text-danger') || 
                  document.getElementById(input.id + 'Error') ||
                  input.parentElement.parentElement.querySelector('.text-danger');
    
    // Check if it's just the prefix (empty URL)
    if (value === 'https://' || value === '') {
        return false; // Not valid but don't show error yet
    }
    
    // Must start with https://
    if (!value.toLowerCase().startsWith('https://')) {
        if (errorEl) {
            errorEl.classList.remove('d-none');
            errorEl.textContent = 'URL must start with https://';
        }
        input.classList.add('is-invalid');
        return false;
    }
    
    // Must have something after https://
    if (value.length <= 8) {
        return false;
    }
    
    // Valid URL format check
    try {
        new URL(value);
        if (errorEl) errorEl.classList.add('d-none');
        input.classList.remove('is-invalid');
        return true;
    } catch (e) {
        if (errorEl) {
            errorEl.classList.remove('d-none');
            errorEl.textContent = 'Please enter a valid URL';
        }
        input.classList.add('is-invalid');
        return false;
    }
}

/**
 * Load RCS payload from a template into the wizard
 * Used when selecting a Rich RCS template from the template selector
 */
function loadRcsPayloadIntoWizard(payload) {
    if (!payload) return;
    
    console.log('[RCS Wizard] Loading template payload:', payload);
    
    rcsCardsData = {};
    rcsCardCount = 1;
    rcsCurrentCard = 1;
    
    var type = payload.type || 'standalone';
    var isCarousel = type === 'carousel';
    
    var standaloneRadio = document.getElementById('rcsTypeStandalone');
    var carouselRadio = document.getElementById('rcsTypeCarousel');
    if (standaloneRadio && carouselRadio) {
        if (isCarousel) {
            carouselRadio.checked = true;
        } else {
            standaloneRadio.checked = true;
        }
    }
    
    if (isCarousel && payload.cards && Array.isArray(payload.cards)) {
        rcsCardCount = payload.cards.length;
        payload.cards.forEach(function(card, index) {
            var cardNum = index + 1;
            rcsCardsData[cardNum] = {
                title: card.title || '',
                description: card.description || '',
                media: card.media || null,
                buttons: card.suggestions || []
            };
        });
        
        if (typeof rebuildCardTabs === 'function') rebuildCardTabs();
        if (typeof selectRcsCard === 'function') selectRcsCard(1);
    } else if (payload.card) {
        var card = payload.card;
        rcsCardsData[1] = {
            title: card.title || '',
            description: card.description || '',
            media: card.media || null,
            buttons: card.suggestions || []
        };
        
        var titleInput = document.getElementById('rcsCardTitle');
        var descInput = document.getElementById('rcsCardDescription');
        
        if (titleInput) titleInput.value = card.title || '';
        if (descInput) descInput.value = card.description || '';
        
        if (card.suggestions && Array.isArray(card.suggestions)) {
            rcsButtons = card.suggestions.map(function(s) {
                return {
                    label: s.text || s.label || '',
                    type: s.type || 'url',
                    url: s.url || '',
                    phone: s.phone || '',
                    title: s.title || '',
                    start: s.start || '',
                    end: s.end || '',
                    description: s.description || ''
                };
            });
            if (typeof updateButtonsList === 'function') updateButtonsList();
        }
    }
    
    var fallbackTextarea = document.getElementById('rcsFallbackText');
    if (fallbackTextarea && payload.fallback) {
        fallbackTextarea.value = payload.fallback;
    }
    
    if (typeof updateRcsPreview === 'function') {
        setTimeout(updateRcsPreview, 100);
    }
    
    console.log('[RCS Wizard] Template loaded successfully');
}
