@extends('layouts.default')

@push('styles')
<link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/flow-builder.css') }}" rel="stylesheet" type="text/css"/>
@endpush

@section('sidebar')
    @include('elements.quicksms-sidebar')
@endsection

@section('title', $page_title)

@section('body_class', 'flow-builder-page')

@section('content')
<div class="flow-builder-container" id="flow-builder">
    <!-- Top Toolbar -->
    <div class="flow-toolbar">
        <div class="flow-toolbar-left">
            <a href="{{ route('flows.index') }}" class="btn btn-sm btn-light me-2" title="Back to flows">
                <i class="fas fa-arrow-left"></i>
            </a>
            <input type="text" class="flow-name-input" id="flow-name-input"
                   value="{{ $flow->name ?? 'Untitled Flow' }}"
                   placeholder="Flow name...">
            <span class="flow-status-badge" id="flow-status-badge">
                {{ $flow->status ?? 'draft' }}
            </span>
        </div>
        <div class="flow-toolbar-center">
            <div class="zoom-controls">
                <button class="btn btn-sm btn-light" id="btn-zoom-out" title="Zoom out">
                    <i class="fas fa-minus"></i>
                </button>
                <span class="zoom-level" id="zoom-level">100%</span>
                <button class="btn btn-sm btn-light" id="btn-zoom-in" title="Zoom in">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="btn btn-sm btn-light" id="btn-zoom-fit" title="Fit to screen">
                    <i class="fas fa-expand"></i>
                </button>
            </div>
        </div>
        <div class="flow-toolbar-right">
            <button class="btn btn-sm btn-outline-secondary me-2" id="btn-undo" title="Undo" disabled>
                <i class="fas fa-undo"></i>
            </button>
            <button class="btn btn-sm btn-outline-secondary me-2" id="btn-redo" title="Redo" disabled>
                <i class="fas fa-redo"></i>
            </button>
            <button class="btn btn-sm btn-light me-2" id="btn-test-flow" title="Test flow">
                <i class="fas fa-play me-1"></i> Test
            </button>
            <button class="btn btn-sm btn-primary" id="btn-save-flow" style="background: #886CC0; border-color: #886CC0;">
                <i class="fas fa-save me-1"></i> Save
            </button>
        </div>
    </div>

    <!-- Main Canvas Area -->
    <div class="flow-workspace">
        <!-- Node Palette (left sidebar) -->
        <div class="flow-palette" id="flow-palette">
            <div class="palette-header">
                <h6>Nodes</h6>
                <input type="text" class="palette-search" id="palette-search" placeholder="Search nodes...">
            </div>

            <div class="palette-section">
                <div class="palette-section-title">
                    <i class="fas fa-bolt"></i> Triggers
                </div>
                <div class="palette-node" draggable="true" data-type="trigger_api">
                    <div class="palette-node-icon trigger"><i class="fas fa-plug"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">API Trigger</div>
                        <div class="palette-node-desc">Start via API call</div>
                    </div>
                </div>
                <div class="palette-node" draggable="true" data-type="trigger_sms_keyword">
                    <div class="palette-node-icon trigger"><i class="fas fa-comment-dots"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">SMS Keyword</div>
                        <div class="palette-node-desc">Trigger on inbound SMS</div>
                    </div>
                </div>
                <div class="palette-node" draggable="true" data-type="trigger_rcs_button">
                    <div class="palette-node-icon trigger"><i class="fas fa-hand-pointer"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">RCS Button</div>
                        <div class="palette-node-desc">Trigger on button tap</div>
                    </div>
                </div>
                <div class="palette-node" draggable="true" data-type="trigger_schedule">
                    <div class="palette-node-icon trigger"><i class="fas fa-clock"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">Schedule</div>
                        <div class="palette-node-desc">Time-based trigger</div>
                    </div>
                </div>
            </div>

            <div class="palette-section">
                <div class="palette-section-title">
                    <i class="fas fa-paper-plane"></i> Actions
                </div>
                <div class="palette-node" draggable="true" data-type="send_message">
                    <div class="palette-node-icon action"><i class="fas fa-paper-plane"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">Send Message</div>
                        <div class="palette-node-desc">SMS or RCS message</div>
                    </div>
                </div>
                <div class="palette-node" draggable="true" data-type="webhook">
                    <div class="palette-node-icon action"><i class="fas fa-globe"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">Webhook</div>
                        <div class="palette-node-desc">Call external URL</div>
                    </div>
                </div>
                <div class="palette-node" draggable="true" data-type="tag">
                    <div class="palette-node-icon action"><i class="fas fa-tag"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">Tag / List</div>
                        <div class="palette-node-desc">Add or remove tags</div>
                    </div>
                </div>
            </div>

            <div class="palette-section">
                <div class="palette-section-title">
                    <i class="fas fa-code-branch"></i> Logic
                </div>
                <div class="palette-node" draggable="true" data-type="wait">
                    <div class="palette-node-icon logic"><i class="fas fa-hourglass-half"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">Wait / Delay</div>
                        <div class="palette-node-desc">Pause for duration</div>
                    </div>
                </div>
                <div class="palette-node" draggable="true" data-type="decision">
                    <div class="palette-node-icon logic"><i class="fas fa-random"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">Decision</div>
                        <div class="palette-node-desc">If/else branching</div>
                    </div>
                </div>
            </div>

            <div class="palette-section">
                <div class="palette-section-title">
                    <i class="fas fa-flag-checkered"></i> End
                </div>
                <div class="palette-node" draggable="true" data-type="inbox_handoff">
                    <div class="palette-node-icon end"><i class="fas fa-headset"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">Inbox Handoff</div>
                        <div class="palette-node-desc">Transfer to agent</div>
                    </div>
                </div>
                <div class="palette-node" draggable="true" data-type="end">
                    <div class="palette-node-icon end"><i class="fas fa-stop-circle"></i></div>
                    <div class="palette-node-info">
                        <div class="palette-node-name">End Flow</div>
                        <div class="palette-node-desc">Complete the flow</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="flow-canvas-wrapper" id="flow-canvas-wrapper">
            <div class="flow-canvas" id="flow-canvas">
                <svg class="flow-connections-svg" id="flow-connections-svg"></svg>
                <div class="flow-nodes-layer" id="flow-nodes-layer">
                    <!-- Nodes are rendered here dynamically -->
                </div>
            </div>
            <!-- Canvas empty state -->
            <div class="canvas-empty-state" id="canvas-empty-state">
                <i class="fas fa-project-diagram"></i>
                <h5>Start building your flow</h5>
                <p>Drag a trigger node from the left panel to begin, or use a template.</p>
            </div>
        </div>

        <!-- Properties Panel (right sidebar) -->
        <div class="flow-properties" id="flow-properties" style="display: none;">
            <div class="properties-header">
                <h6 id="properties-title">Node Settings</h6>
                <button class="btn btn-sm btn-light" id="btn-close-properties">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="properties-body" id="properties-body">
                <!-- Dynamic properties form rendered here -->
            </div>
            <div class="properties-footer">
                <button class="btn btn-sm btn-outline-danger" id="btn-delete-node">
                    <i class="fas fa-trash me-1"></i> Delete Node
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden data for JS -->
<input type="hidden" id="flow-id" value="{{ $flow->id ?? '' }}">
<input type="hidden" id="flow-data" value="{{ $flow ? json_encode([
    'nodes' => $flow->nodes->map(fn($n) => [
        'node_uid' => $n->node_uid,
        'type' => $n->type,
        'label' => $n->label,
        'config' => $n->config,
        'position_x' => $n->position_x,
        'position_y' => $n->position_y,
    ]),
    'connections' => $flow->connections->map(fn($c) => [
        'source_node_uid' => $c->source_node_uid,
        'target_node_uid' => $c->target_node_uid,
        'source_handle' => $c->source_handle,
        'label' => $c->label,
    ]),
    'canvas_meta' => $flow->canvas_meta,
]) : '' }}">

<!-- Pass sender/agent data to JS -->
<script>
    window.__flowBuilderData = {
        senderIds: @json($sender_ids),
        rcsAgents: @json($rcs_agents)
    };
</script>

{{-- ==========================================
     Full-Screen Send Message Embed Modal
     Opens the real send-message page in an iframe
     ========================================== --}}
<div class="modal fade" id="flowMessageComposerModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="border: none; border-radius: 0;">
            <div class="modal-header py-2 px-3" style="background: #f8f6fc; border-bottom: 1px solid #e8e0f0;">
                <h6 class="modal-title mb-0"><i class="fas fa-paper-plane me-2" style="color: #886CC0;"></i>Configure Message</h6>
                <button type="button" class="btn-close" id="flowMessageComposerClose"></button>
            </div>
            <div class="modal-body p-0" style="overflow: hidden;">
                <div class="d-flex align-items-center justify-content-center h-100" id="flowMessageComposerLoading">
                    <div class="text-center">
                        <div class="spinner-border mb-2" style="color: #886CC0;" role="status"></div>
                        <p class="text-muted mb-0">Loading message composer...</p>
                    </div>
                </div>
                <iframe id="flowMessageComposerIframe" class="d-none" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
     Preview Modal - Phone preview of SMS/RCS
     ========================================== --}}
<div class="modal fade" id="flowPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header py-2 px-3" style="background: #f8f6fc; border-bottom: 1px solid #e8e0f0;">
                <h6 class="modal-title mb-0"><i class="fas fa-mobile-alt me-2" style="color: #886CC0;"></i>Message Preview</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" style="background: #f5f5f5; max-height: 70vh; overflow-y: auto;">
                {{-- Channel toggle --}}
                <div class="text-center mb-3 d-none" id="flowPreviewToggle">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary active" data-preview-channel="sms">SMS</button>
                        <button class="btn btn-outline-primary" data-preview-channel="rcs">RCS</button>
                    </div>
                </div>
                {{-- Preview render target --}}
                <div id="flowPreviewContainer"></div>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
     Keyword Modal - Manage interaction keywords
     ========================================== --}}
<div class="modal fade" id="flowKeywordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header py-2 px-3" style="background: #f8f6fc; border-bottom: 1px solid #e8e0f0;">
                <h6 class="modal-title mb-0"><i class="fas fa-reply me-2" style="color: #886CC0;"></i>Manage Keywords</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <p class="text-muted mb-3" style="font-size: 0.82rem;">
                    Define keywords that recipients can reply with. Each keyword creates a separate output branch on the node.
                </p>

                {{-- Warning about sender ID --}}
                <div class="alert alert-warning py-2 px-3 mb-3" id="flowKeywordWarning" style="font-size: 0.78rem; display: none;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Keyword replies require a sender ID that supports two-way messaging.
                </div>

                {{-- Keyword list --}}
                <div id="flowKeywordList" class="mb-3">
                    {{-- Keywords rendered dynamically --}}
                </div>

                {{-- Add keyword input --}}
                <div class="input-group input-group-sm mb-3">
                    <input type="text" class="form-control" id="flowKeywordInput" placeholder="Enter keyword..." maxlength="50">
                    <button class="btn btn-outline-primary" type="button" id="flowKeywordAddBtn">
                        <i class="fas fa-plus me-1"></i> Add
                    </button>
                </div>

                {{-- Catch-all checkbox --}}
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="flowKeywordCatchAll">
                    <label class="form-check-label" for="flowKeywordCatchAll" style="font-size: 0.82rem;">
                        Enable catch-all branch <span class="text-muted">(any reply that doesn't match a keyword)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer py-2 px-3">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="flowKeywordApplyBtn" style="background: #886CC0; border-color: #886CC0;">
                    <i class="fas fa-check me-1"></i> Apply Keywords
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="{{ asset('css/rcs-preview.css') }}" rel="stylesheet" type="text/css"/>
@endpush

@push('scripts')
<script src="{{ asset('js/rcs-preview-renderer.js') }}"></script>
<script src="{{ asset('js/flow-builder.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var cb = document.querySelector('.content-body.flow-builder-page');
    function sizeFlowBuilder() {
        if (!cb) return;
        window.scrollTo(0, 0);
        var top = cb.getBoundingClientRect().top;
        if (top < 1) {
            var header = document.querySelector('.header');
            top = header ? header.offsetHeight : 72;
        }
        cb.style.height = Math.max(window.innerHeight - top, 200) + 'px';
    }
    sizeFlowBuilder();
    setTimeout(sizeFlowBuilder, 300);
    window.addEventListener('resize', sizeFlowBuilder);
    var banner = document.getElementById('test-mode-activation-banner');
    if (banner) {
        new MutationObserver(function() { setTimeout(sizeFlowBuilder, 20); })
            .observe(banner, { attributes: true, attributeFilter: ['style'] });
    }

    var flowId = document.getElementById('flow-id').value;
    var flowDataRaw = document.getElementById('flow-data').value;
    var flowData = flowDataRaw ? JSON.parse(flowDataRaw) : null;
    var template = new URLSearchParams(window.location.search).get('template');

    window.flowBuilder = new FlowBuilder('flow-canvas', {
        flowId: flowId || null,
        initialData: flowData,
        template: template,
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        saveUrl: flowId ? '/flows/' + flowId + '/save' : null,
        senderIds: window.__flowBuilderData.senderIds,
        rcsAgents: window.__flowBuilderData.rcsAgents,
    });
});
</script>
@endpush
