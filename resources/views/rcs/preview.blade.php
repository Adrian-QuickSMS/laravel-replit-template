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
                <div class="card-body d-flex justify-content-center align-items-start" style="min-height: 800px; background: rgba(136, 108, 192, 0.1);">
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
<script src="{{ asset('js/rcs-preview-renderer.js') }}"></script>
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
        this.validationResult = RcsPreviewRenderer.validateRcsMessage(payload);
        this.render();
      }
    },
    
    render() {
      const container = document.getElementById('rcs-preview-container');
      if (!container || !this.currentPayload) return;
      const messageHtml = RcsPreviewRenderer.renderMessage(this.currentPayload);
      container.innerHTML = RcsPreviewRenderer.renderPhoneFrame(this.currentPayload.agent, messageHtml);
      RcsPreviewRenderer.initCarouselBehavior('#rcs-preview-container');
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
