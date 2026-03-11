@extends('layouts.default')

@push('styles')
<link href="{{ asset('css/quicksms-global-layout.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/flow-builder.css') }}" rel="stylesheet" type="text/css"/>
<link href="{{ asset('css/rcs-preview.css') }}" rel="stylesheet" type="text/css"/>
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
     SMS Composer Modal
     Mirrors the real Send Message content editor
     ========================================== --}}
<div class="modal fade" id="smsComposerModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #eee;">
                <h5 class="modal-title"><i class="fas fa-paper-plane me-2" style="color: #886CC0;"></i>Message Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- SMS Fallback label for Rich RCS --}}
                <div class="d-none mb-2" id="smsFallbackNote">
                    <div class="alert py-2 mb-0" style="background-color: #f0ebf8; color: #6b5b95; border: none;">
                        <i class="fas fa-info-circle me-1"></i>
                        This text is used as the SMS fallback when the recipient's device doesn't support RCS.
                    </div>
                </div>

                <label class="form-label fw-bold mb-2" id="smsComposerLabel">SMS Content</label>

                <div class="position-relative border rounded mb-2">
                    <textarea class="form-control border-0" id="flowSmsContent" rows="6"
                              placeholder="Type your message here... Use {{first_name}} for personalisation."
                              oninput="flowHandleContentChange()"
                              style="padding-bottom: 40px; font-size: 0.9rem;"></textarea>
                    <div class="position-absolute d-flex gap-2" style="bottom: 8px; right: 12px; z-index: 10;">
                        <button type="button" class="btn btn-sm btn-light border" onclick="flowOpenPersonalisationModal()" title="Insert personalisation">
                            <i class="fas fa-user-tag"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border" id="flowEmojiPickerBtn" title="Insert emoji">
                            <i class="fas fa-smile"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="text-muted me-3">Characters: <strong id="flowCharCount">0</strong></span>
                        <span class="text-muted me-3">Encoding: <strong id="flowEncodingType">GSM-7</strong></span>
                        <span class="text-muted" id="flowSegmentDisplay">Segments: <strong id="flowSmsPartCount">1</strong></span>
                    </div>
                    <span class="badge d-none" id="flowUnicodeWarning" style="background:#f0ebf8;color:#5a3d8a;">
                        <i class="fas fa-exclamation-triangle me-1"></i>Unicode
                    </span>
                </div>

                <div class="d-none mb-2" id="flowRcsTextHelper">
                    <div class="alert py-2 mb-0" style="background-color: #f0ebf8; color: #6b5b95; border: none;">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="flowRcsHelperText">Messages over 160 characters will be sent as a single RCS message.</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #eee;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnApplySmsContent" style="background: #886CC0; border-color: #886CC0;">
                    <i class="fas fa-check me-1"></i> Apply Content
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ==========================================
     Personalisation Modal
     ========================================== --}}
<div class="modal fade" id="flowPersonalisationModal" tabindex="-1" style="z-index: 1070;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="fas fa-user-tag me-2"></i>Insert Personalisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Click a placeholder to insert it at the cursor position in your message.</p>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Contact Book Fields</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="flowInsertPlaceholder('first_name')">@{{first_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="flowInsertPlaceholder('last_name')">@{{last_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="flowInsertPlaceholder('full_name')">@{{full_name}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="flowInsertPlaceholder('mobile_number')">@{{mobile_number}}</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="flowInsertPlaceholder('email')">@{{email}}</button>
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Flow Variables</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="flowInsertPlaceholder('trigger_data')">@{{trigger_data}}</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="flowInsertPlaceholder('webhook_response')">@{{webhook_response}}</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Include the existing RCS Wizard Modal and supporting components --}}
@include('quicksms.partials.rcs-wizard-modal')
@include('quicksms.partials.emoji-picker')

@endsection

@push('scripts')
<script src="{{ asset('js/rcs-preview-renderer.js') }}?v=20260227a"></script>
<script src="{{ asset('js/rcs-wizard.js') }}?v=20260227b"></script>
<script src="{{ asset('js/flow-builder.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
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
