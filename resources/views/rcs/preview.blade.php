@extends('layouts.quicksms')

@section('page-title', 'RCS Preview Demo')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item active">RCS Preview Demo</li>
        </ol>
    </div>

    <div class="row" x-data="createRcsPreviewController()" x-init="init()">
        <div class="col-xl-4 col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Example Selector</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Select RCS Message Type</label>
                        <select 
                            class="form-select" 
                            x-model="selectedExample" 
                            @change="loadExample(selectedExample)"
                        >
                            <template x-for="example in examples" :key="example">
                                <option :value="example" x-text="getExampleLabel(example)"></option>
                            </template>
                        </select>
                    </div>

                    <template x-if="hasErrors()">
                        <div class="rcs-validation-errors">
                            <h4>Validation Errors</h4>
                            <ul>
                                <template x-for="error in validationResult.errors" :key="error.field">
                                    <li x-text="error.message"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <template x-if="hasWarnings()">
                        <div class="rcs-validation-warnings">
                            <h4>Warnings</h4>
                            <ul>
                                <template x-for="warning in validationResult.warnings" :key="warning.field">
                                    <li x-text="warning.message"></li>
                                </template>
                            </ul>
                        </div>
                    </template>

                    <div class="mb-3">
                        <label class="form-label">Current Payload</label>
                        <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow: auto; font-size: 12px;"><code x-text="JSON.stringify(currentPayload, null, 2)"></code></pre>
                    </div>

                    <div class="alert alert-info">
                        <strong>About this preview:</strong>
                        <p class="mb-0 mt-2">This is a schema-driven RCS message renderer. Content is loaded from JSON payloads and styled using design tokens derived from Google's RCS specifications.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">RCS Preview</h4>
                </div>
                <div class="card-body d-flex justify-content-center align-items-start" style="min-height: 800px; background: #f8f9fa;">
                    <div id="rcs-preview-container"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/rcs-preview.css') }}">
@endpush

@push('scripts')
<script>
const samplePayloads = {
  'rich-card-short': {
    type: 'rich_card',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true,
      tagline: 'Fast messaging for everyone'
    },
    content: {
      media: {
        url: 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=400&h=200&fit=crop',
        mimeType: 'image/jpeg',
        height: 'short',
        altText: 'Running shoes'
      },
      title: 'New arrivals just dropped!',
      description: 'Check out our latest collection of premium running shoes.',
      buttons: [
        { label: 'Shop now', action: { type: 'url', url: 'https://example.com/shop' } },
        { label: 'Call us', action: { type: 'dial', phoneNumber: '+44 20 1234 5678' } }
      ]
    }
  },
  'rich-card-medium': {
    type: 'rich_card',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true
    },
    content: {
      media: {
        url: 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=400&h=300&fit=crop',
        mimeType: 'image/jpeg',
        height: 'medium',
        altText: 'Banking app'
      },
      title: 'Secure mobile banking',
      description: 'Manage your accounts securely from anywhere. Check balances, make payments, and more.',
      buttons: [
        { label: 'Check balance', action: { type: 'url', url: 'https://example.com/balance' } },
        { label: 'View activity', action: { type: 'url', url: 'https://example.com/activity' } },
        { label: 'Call support', action: { type: 'dial', phoneNumber: '+44 800 123 456' } }
      ]
    }
  },
  'rich-card-tall': {
    type: 'rich_card',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true
    },
    content: {
      media: {
        url: 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=400&fit=crop',
        mimeType: 'image/jpeg',
        height: 'tall',
        altText: 'Mountain landscape'
      },
      title: 'Adventure awaits',
      buttons: [
        { label: 'Book now', action: { type: 'url', url: 'https://example.com/book' } }
      ]
    }
  },
  'rich-card-no-media': {
    type: 'rich_card',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true
    },
    content: {
      title: 'Your order has shipped!',
      description: 'Your package is on its way. Track your delivery to see when it will arrive.',
      buttons: [
        { label: 'Track package', action: { type: 'url', url: 'https://example.com/track' } }
      ]
    }
  },
  'carousel-medium': {
    type: 'carousel',
    agent: {
      name: 'QuickSMS',
      logo: 'https://ui-avatars.com/api/?name=QS&background=7c3aed&color=fff&size=80',
      verified: true,
      tagline: 'Your travel partner'
    },
    content: {
      cardWidth: 'medium',
      cards: [
        {
          media: { url: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=300&h=200&fit=crop', mimeType: 'image/jpeg', height: 'medium' },
          title: 'Beach Paradise',
          description: 'Relax on pristine beaches. From $299/night.',
          buttons: [{ label: 'Book now', action: { type: 'url', url: 'https://example.com/beach' } }]
        },
        {
          media: { url: 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=300&h=200&fit=crop', mimeType: 'image/jpeg', height: 'medium' },
          title: 'Mountain Retreat',
          description: 'Escape to the mountains. From $199/night.',
          buttons: [{ label: 'Book now', action: { type: 'url', url: 'https://example.com/mountain' } }]
        },
        {
          media: { url: 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=300&h=200&fit=crop', mimeType: 'image/jpeg', height: 'medium' },
          title: 'City Explorer',
          description: 'Discover urban adventures. From $149/night.',
          buttons: [{ label: 'Book now', action: { type: 'url', url: 'https://example.com/city' } }]
        }
      ]
    }
  }
};

const RCS_CONSTRAINTS = {
  maxCarouselCards: 10,
  maxButtonsPerCard: 4,
  maxTitleLength: 200,
  maxDescriptionLength: 2000,
  maxButtonLabelLength: 25
};

function validateRcsMessage(message) {
  const errors = [];
  const warnings = [];
  
  if (!message) {
    return { valid: false, errors: [{ field: 'message', message: 'Message is required' }], warnings: [] };
  }
  
  if (message.type === 'carousel') {
    const carousel = message.content;
    if (carousel.cards && carousel.cards.length > RCS_CONSTRAINTS.maxCarouselCards) {
      errors.push({ field: 'content.cards', message: `Too many cards. Maximum is ${RCS_CONSTRAINTS.maxCarouselCards}` });
    }
    if (carousel.cards) {
      carousel.cards.forEach((card, i) => {
        if (card.buttons && card.buttons.length > RCS_CONSTRAINTS.maxButtonsPerCard) {
          errors.push({ field: `content.cards[${i}].buttons`, message: `Too many buttons. Maximum is ${RCS_CONSTRAINTS.maxButtonsPerCard}` });
        }
      });
    }
  } else if (message.type === 'rich_card') {
    const card = message.content;
    if (card.buttons && card.buttons.length > RCS_CONSTRAINTS.maxButtonsPerCard) {
      errors.push({ field: 'content.buttons', message: `Too many buttons. Maximum is ${RCS_CONSTRAINTS.maxButtonsPerCard}` });
    }
  }
  
  return { valid: errors.length === 0, errors, warnings };
}

function getButtonIcon(action) {
  const icons = {
    url: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>',
    dial: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>',
    calendar: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>',
    reply: '<svg class="rcs-button-icon" viewBox="0 0 24 24" fill="currentColor"><path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/></svg>'
  };
  return icons[action.type] || '';
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

function getMediaHeight(height) {
  const heights = { short: '112px', medium: '168px', tall: '264px' };
  return heights[height] || '0';
}

function renderButton(button) {
  const icon = getButtonIcon(button.action);
  return `<button type="button" class="rcs-button">${icon ? `<span class="rcs-button-icon-wrapper">${icon}</span>` : ''}<span class="rcs-button-label">${escapeHtml(button.label)}</span></button>`;
}

function renderButtons(buttons) {
  if (!buttons || buttons.length === 0) return '';
  return `<div class="rcs-buttons">${buttons.map(b => renderButton(b)).join('')}</div>`;
}

function renderMedia(media) {
  if (!media || media.height === 'none') return '';
  return `<div class="rcs-media rcs-media--${media.height}" style="height: ${getMediaHeight(media.height)};"><img src="${escapeHtml(media.url)}" alt="${escapeHtml(media.altText || '')}" class="rcs-media-image" loading="lazy"/></div>`;
}

function renderRichCard(card, isCarousel = false) {
  const mediaHtml = renderMedia(card.media);
  const titleHtml = card.title ? `<h3 class="rcs-card-title">${escapeHtml(card.title)}</h3>` : '';
  const descHtml = card.description ? `<p class="rcs-card-description">${escapeHtml(card.description)}</p>` : '';
  const buttonsHtml = renderButtons(card.buttons);
  const cardClass = isCarousel ? 'rcs-card rcs-carousel-card' : 'rcs-card';
  const mediaClass = card.media && card.media.height !== 'none' ? 'rcs-card--has-media' : 'rcs-card--no-media';
  return `<div class="${cardClass} ${mediaClass}">${mediaHtml}<div class="rcs-card-content">${titleHtml}${descHtml}</div>${buttonsHtml}</div>`;
}

function renderCarousel(carousel) {
  const cardWidth = carousel.cardWidth === 'small' ? '200px' : '256px';
  const cardsHtml = carousel.cards.map(card => `<div class="rcs-carousel-item" style="min-width: ${cardWidth}; max-width: ${cardWidth};">${renderRichCard(card, true)}</div>`).join('');
  const dots = carousel.cards.map((_, i) => `<button class="rcs-carousel-dot ${i === 0 ? 'active' : ''}" data-index="${i}"></button>`).join('');
  return `<div class="rcs-carousel"><div class="rcs-carousel-track">${cardsHtml}</div><div class="rcs-carousel-indicators">${dots}</div></div>`;
}

function renderMessage(message) {
  if (message.type === 'rich_card') return `<div class="rcs-message">${renderRichCard(message.content)}</div>`;
  if (message.type === 'carousel') return `<div class="rcs-message">${renderCarousel(message.content)}</div>`;
  return '';
}

function renderPhoneFrame(agent, messageContent) {
  const badge = agent.verified !== false ? '<svg class="rcs-verified-badge" viewBox="0 0 24 24" fill="#1a73e8"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>' : '';
  const tagline = agent.tagline ? `<span class="rcs-agent-tagline">${escapeHtml(agent.tagline)}</span>` : '';
  
  return `
    <div class="rcs-phone-frame">
      <div class="rcs-status-bar"><span class="rcs-status-time">9:30</span><div class="rcs-status-icons"><span class="rcs-status-5g">5G</span><svg class="rcs-status-signal" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M2 22h20V2L2 22zm18-2H6.83L20 6.83V20z"/></svg><svg class="rcs-status-battery" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z"/></svg></div></div>
      <div class="rcs-header">
        <button class="rcs-back-button"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg></button>
        <div class="rcs-agent-info">
          <div class="rcs-agent-logo-wrapper"><img src="${escapeHtml(agent.logo)}" alt="${escapeHtml(agent.name)}" class="rcs-agent-logo"/>${badge}</div>
          <div class="rcs-agent-details"><span class="rcs-agent-name">${escapeHtml(agent.name)}</span>${tagline}</div>
        </div>
        <div class="rcs-header-actions"><button class="rcs-header-btn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg></button><button class="rcs-header-btn"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg></button></div>
      </div>
      <div class="rcs-chat-area"><div class="rcs-timestamp">Today</div>${messageContent}</div>
      <div class="rcs-input-bar"><button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg></button><input type="text" class="rcs-input-field" placeholder="RCS message" readonly/><button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/></svg></button><button class="rcs-input-action"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg></button><button class="rcs-send-button"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 14c1.66 0 2.99-1.34 2.99-3L15 5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5.3-3c0 3-2.54 5.1-5.3 5.1S6.7 14 6.7 11H5c0 3.41 2.72 6.23 6 6.72V21h2v-3.28c3.28-.48 6-3.3 6-6.72h-1.7z"/></svg></button></div>
    </div>
  `;
}

function createRcsPreviewController() {
  return {
    currentPayload: null,
    selectedExample: 'rich-card-short',
    validationResult: null,
    examples: Object.keys(samplePayloads),
    
    init() {
      this.loadExample(this.selectedExample);
    },
    
    loadExample(exampleId) {
      this.selectedExample = exampleId;
      const payload = samplePayloads[exampleId];
      if (payload) {
        this.currentPayload = payload;
        this.validationResult = validateRcsMessage(payload);
        this.render();
      }
    },
    
    render() {
      const container = document.getElementById('rcs-preview-container');
      if (!container || !this.currentPayload) return;
      const messageHtml = renderMessage(this.currentPayload);
      container.innerHTML = renderPhoneFrame(this.currentPayload.agent, messageHtml);
      this.initCarouselBehavior();
    },
    
    initCarouselBehavior() {
      const carousel = document.querySelector('.rcs-carousel-track');
      if (!carousel) return;
      const dots = document.querySelectorAll('.rcs-carousel-dot');
      carousel.addEventListener('scroll', () => {
        const scrollLeft = carousel.scrollLeft;
        const itemWidth = carousel.firstElementChild?.clientWidth || 256;
        const currentIndex = Math.round(scrollLeft / (itemWidth + 8));
        dots.forEach((dot, i) => dot.classList.toggle('active', i === currentIndex));
      });
      dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
          const itemWidth = carousel.firstElementChild?.clientWidth || 256;
          carousel.scrollTo({ left: i * (itemWidth + 8), behavior: 'smooth' });
        });
      });
    },
    
    getExampleLabel(id) {
      const labels = {
        'rich-card-short': 'Rich Card - Short Media',
        'rich-card-medium': 'Rich Card - Medium Media',
        'rich-card-tall': 'Rich Card - Tall Media',
        'rich-card-no-media': 'Rich Card - No Media',
        'carousel-medium': 'Carousel - Medium Cards'
      };
      return labels[id] || id;
    },
    
    hasErrors() { return this.validationResult?.errors?.length > 0; },
    hasWarnings() { return this.validationResult?.warnings?.length > 0; }
  };
}
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
