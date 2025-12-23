@extends('layouts.quicksms')

@section('title', $page_title)

@push('styles')
<style>
.summary-card {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}
.summary-card .card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
}
.summary-card .card-header h5 {
    margin: 0;
    font-weight: 600;
    color: #212529;
}
.summary-card .card-body {
    padding: 1.25rem;
}
.summary-table {
    width: 100%;
}
.summary-table td {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
    vertical-align: middle;
}
.summary-table tr:last-child td {
    border-bottom: none;
}
.summary-table .summary-label {
    color: #6c757d;
    font-size: 0.875rem;
    width: 40%;
}
.summary-table .summary-value {
    font-weight: 500;
    color: #212529;
    text-align: right;
}
.edit-btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
}
.recipient-metric {
    text-align: center;
    padding: 1rem;
    border-radius: 0.5rem;
    background: #f8f9fa;
}
.recipient-metric .number {
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.2;
}
.recipient-metric .label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.recipient-metric.primary .number { color: #6f42c1; }
.recipient-metric.success .number { color: #198754; }
.recipient-metric.danger .number { color: #dc3545; }
.recipient-metric.warning .number { color: #ffc107; }
.source-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
}
.source-item .source-name {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.source-item .source-count {
    font-weight: 600;
    color: #212529;
}
.pricing-notice {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    border: 1px solid #ffc107;
    border-radius: 0.5rem;
    padding: 1rem;
}
.pricing-table {
    width: 100%;
}
.pricing-table tr td {
    padding: 0.5rem 0;
}
.pricing-table tr td:last-child {
    text-align: right;
    font-weight: 500;
}
.pricing-table .total-row {
    border-top: 2px solid #212529;
    font-weight: 700;
}
.pricing-table .total-row td {
    padding-top: 0.75rem;
    font-size: 1.1rem;
}
.channel-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-weight: 500;
}
.channel-badge.sms { background: #e9ecef; color: #495057; }
.channel-badge.basic-rcs { background: #d1e7dd; color: #0f5132; }
.channel-badge.rich-rcs { background: #e7d5ff; color: #6f42c1; }
.agent-preview {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}
.agent-preview img {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
}
.delivery-note {
    background: #e7f1ff;
    border-left: 4px solid #0d6efd;
    padding: 1rem;
    border-radius: 0 0.5rem 0.5rem 0;
    font-size: 0.875rem;
    color: #0d6efd;
}
.action-bar {
    background: #fff;
    border-top: 1px solid #e9ecef;
    padding: 1.5rem;
    position: sticky;
    bottom: 0;
    margin: 0 -1.875rem -1.875rem;
}
.validation-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0;
    font-size: 0.875rem;
}
.validation-item.valid { color: #198754; }
.validation-item.invalid { color: #dc3545; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages') }}">Messages</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages.send') }}">Send Message</a></li>
            <li class="breadcrumb-item active">Confirm & Send</li>
        </ol>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card summary-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-clipboard-list me-2 text-primary"></i>Campaign Summary</h5>
                    <a href="{{ route('messages.send') }}" class="btn btn-outline-secondary edit-btn">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body">
                    <table class="summary-table">
                        <tr>
                            <td class="summary-label">Campaign Name</td>
                            <td class="summary-value">{{ $campaign['name'] }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Created by</td>
                            <td class="summary-value">{{ $campaign['created_by'] }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Created date / time</td>
                            <td class="summary-value">{{ $campaign['created_at'] }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Scheduled send time</td>
                            <td class="summary-value">
                                @if($campaign['scheduled_time'] === 'Immediate')
                                    <span class="badge bg-success">{{ $campaign['scheduled_time'] }}</span>
                                @else
                                    <span class="badge bg-info">Scheduled: {{ $campaign['scheduled_time'] }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="summary-label">Message validity</td>
                            <td class="summary-value">{{ $campaign['message_validity'] }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">Sending Window</td>
                            <td class="summary-value">{{ $campaign['sending_window'] }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card summary-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-broadcast-tower me-2 text-info"></i>Channel & Delivery Summary</h5>
                    <a href="{{ route('messages.send') }}" class="btn btn-outline-secondary edit-btn">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="summary-label d-block mb-2">Channel</span>
                        @if($channel['type'] === 'sms_only')
                            <span class="channel-badge sms"><i class="fas fa-sms"></i> SMS only</span>
                        @elseif($channel['type'] === 'basic_rcs')
                            <span class="channel-badge basic-rcs"><i class="fas fa-comment-dots"></i> Basic RCS with SMS fallback</span>
                        @else
                            <span class="channel-badge rich-rcs"><i class="fas fa-images"></i> Rich RCS with SMS fallback</span>
                        @endif
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="summary-label mb-2">SMS Sender ID</div>
                            <div class="fw-medium">{{ $channel['sms_sender_id'] }}</div>
                        </div>
                        @if($channel['type'] !== 'sms_only')
                        <div class="col-md-6">
                            <div class="summary-label mb-2">RCS Agent</div>
                            <div class="agent-preview">
                                <img src="{{ $channel['rcs_agent']['logo'] }}" alt="Agent Logo">
                                <div>
                                    <div class="fw-medium">{{ $channel['rcs_agent']['name'] }}</div>
                                    <small class="text-muted">Verified Agent</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="delivery-note mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        @if($channel['type'] === 'sms_only')
                            Messages will be sent via SMS only.
                        @elseif($channel['type'] === 'basic_rcs')
                            Messages over 160 characters will be automatically delivered as a single RCS message where supported. SMS will be used as a fallback.
                        @else
                            Rich RCS will be delivered where supported. SMS will be used as a fallback.
                        @endif
                    </div>
                </div>
            </div>

            <div class="card summary-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-users me-2 text-success"></i>Recipient Summary</h5>
                    <a href="{{ route('messages.send') }}" class="btn btn-outline-secondary edit-btn">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="recipient-metric primary">
                                <div class="number">{{ number_format($recipients['total_selected']) }}</div>
                                <div class="label">Total Selected</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="recipient-metric success">
                                <div class="number">{{ number_format($recipients['valid']) }}</div>
                                <div class="label">Valid</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="recipient-metric danger">
                                <div class="number">{{ number_format($recipients['invalid']) }}</div>
                                <div class="label">Invalid (Excluded)</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="recipient-metric warning">
                                <div class="number">{{ number_format($recipients['opted_out']) }}</div>
                                <div class="label">Opted-out</div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-medium">Source Breakdown</span>
                            <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#sourceBreakdown">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="collapse show" id="sourceBreakdown">
                            @if($recipients['sources']['manual_input'] > 0)
                            <div class="source-item">
                                <span class="source-name"><i class="fas fa-keyboard text-muted"></i> Manual input</span>
                                <span class="source-count">{{ number_format($recipients['sources']['manual_input']) }}</span>
                            </div>
                            @endif
                            @if($recipients['sources']['file_upload'] > 0)
                            <div class="source-item">
                                <span class="source-name"><i class="fas fa-file-upload text-muted"></i> File upload</span>
                                <span class="source-count">{{ number_format($recipients['sources']['file_upload']) }}</span>
                            </div>
                            @endif
                            @if($recipients['sources']['contacts'] > 0)
                            <div class="source-item">
                                <span class="source-name"><i class="fas fa-address-book text-muted"></i> Contacts</span>
                                <span class="source-count">{{ number_format($recipients['sources']['contacts']) }}</span>
                            </div>
                            @endif
                            @if($recipients['sources']['lists'] > 0)
                            <div class="source-item">
                                <span class="source-name"><i class="fas fa-list text-muted"></i> Lists</span>
                                <span class="source-count">{{ number_format($recipients['sources']['lists']) }}</span>
                            </div>
                            @endif
                            @if($recipients['sources']['dynamic_lists'] > 0)
                            <div class="source-item">
                                <span class="source-name"><i class="fas fa-magic text-muted"></i> Dynamic lists</span>
                                <span class="source-count">{{ number_format($recipients['sources']['dynamic_lists']) }}</span>
                            </div>
                            @endif
                            @if($recipients['sources']['tags'] > 0)
                            <div class="source-item">
                                <span class="source-name"><i class="fas fa-tags text-muted"></i> Tags</span>
                                <span class="source-count">{{ number_format($recipients['sources']['tags']) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card summary-card">
                <div class="card-header">
                    <h5><i class="fas fa-pound-sign me-2 text-warning"></i>Pricing Summary</h5>
                </div>
                <div class="card-body">
                    @if($channel['type'] === 'sms_only')
                        @php
                            $messageCount = $recipients['valid'];
                            $subtotal = $messageCount * $pricing['sms_unit_price'];
                            $vatAmount = $pricing['vat_applicable'] ? $subtotal * ($pricing['vat_rate'] / 100) : 0;
                            $total = $subtotal + $vatAmount;
                        @endphp
                        <table class="pricing-table">
                            <tr>
                                <td>Messages</td>
                                <td>{{ number_format($messageCount) }}</td>
                            </tr>
                            <tr>
                                <td>Price per SMS</td>
                                <td>&pound;{{ number_format($pricing['sms_unit_price'], 3) }}</td>
                            </tr>
                            <tr>
                                <td>Subtotal (ex VAT)</td>
                                <td>&pound;{{ number_format($subtotal, 2) }}</td>
                            </tr>
                            @if($pricing['vat_applicable'])
                            <tr>
                                <td>VAT ({{ $pricing['vat_rate'] }}%)</td>
                                <td>&pound;{{ number_format($vatAmount, 2) }}</td>
                            </tr>
                            @else
                            <tr>
                                <td colspan="2" class="text-muted small">VAT not applicable for this account.</td>
                            </tr>
                            @endif
                            <tr class="total-row">
                                <td>Total</td>
                                <td>&pound;{{ number_format($total, 2) }}</td>
                            </tr>
                        </table>
                    @else
                        <div class="pricing-notice">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-info-circle me-3 mt-1 text-warning"></i>
                                <div>
                                    <p class="mb-2 fw-medium">Pricing Notice</p>
                                    <p class="mb-0">This campaign includes RCS delivery. Because RCS availability varies by handset and network, the final cost cannot be calculated in advance.</p>
                                    <p class="mt-2 mb-0">SMS fallback messages will be charged at your agreed SMS rate (&pound;{{ number_format($pricing['sms_unit_price'], 3) }}/msg). RCS messages are billed based on actual delivery.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="summary-label mb-2">Reference Pricing</div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="bg-light p-3 rounded text-center">
                                        <div class="small text-muted">SMS Rate</div>
                                        <div class="fw-bold text-dark">&pound;{{ number_format($pricing['sms_unit_price'], 3) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light p-3 rounded text-center">
                                        <div class="small text-muted">RCS Basic Rate</div>
                                        <div class="fw-bold text-dark">&pound;{{ number_format($pricing['rcs_basic_price'], 3) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light p-3 rounded text-center">
                                        <div class="small text-muted">RCS Single Rate</div>
                                        <div class="fw-bold text-dark">&pound;{{ number_format($pricing['rcs_single_price'], 3) }}</div>
                                    </div>
                                </div>
                            </div>
                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-clock me-1"></i>Final charges will be available in reporting after delivery completes.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle me-2 text-success"></i>Pre-Send Validation</h5>
                </div>
                <div class="card-body">
                    <div class="validation-item valid">
                        <i class="fas fa-check-circle"></i>
                        <span>Campaign name set</span>
                    </div>
                    <div class="validation-item valid">
                        <i class="fas fa-check-circle"></i>
                        <span>Valid channel selected</span>
                    </div>
                    <div class="validation-item valid">
                        <i class="fas fa-check-circle"></i>
                        <span>Sender ID validated</span>
                    </div>
                    @if($channel['type'] !== 'sms_only')
                    <div class="validation-item valid">
                        <i class="fas fa-check-circle"></i>
                        <span>RCS Agent verified</span>
                    </div>
                    @endif
                    <div class="validation-item valid">
                        <i class="fas fa-check-circle"></i>
                        <span>{{ number_format($recipients['valid']) }} valid recipients</span>
                    </div>
                    <div class="validation-item valid">
                        <i class="fas fa-check-circle"></i>
                        <span>Message content present</span>
                    </div>
                    <div class="validation-item valid">
                        <i class="fas fa-check-circle"></i>
                        <span>Opt-out rules validated</span>
                    </div>
                    <div class="validation-item valid">
                        <i class="fas fa-check-circle"></i>
                        <span>Pricing rules resolved</span>
                    </div>
                </div>
                <div class="card-footer bg-white border-top">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-lg" id="sendCampaignBtn" onclick="confirmSend()">
                            <i class="fas fa-paper-plane me-2"></i>Send Campaign
                        </button>
                        <a href="{{ route('messages.send') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Edit
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="fas fa-times me-2"></i>Cancel Campaign
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Cancel Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this campaign? All unsaved changes will be lost.</p>
                <p class="mb-0 text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Editing</button>
                <a href="{{ route('messages.send') }}" class="btn btn-danger">Discard Campaign</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sendingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Sending Campaign...</h5>
                <p class="text-muted mb-0">Please wait while we queue your messages.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmSend() {
    var sendingModal = new bootstrap.Modal(document.getElementById('sendingModal'));
    sendingModal.show();
    
    setTimeout(function() {
        sendingModal.hide();
        window.location.href = '{{ route("messages.campaign-history") }}';
    }, 2000);
}
</script>
@endpush
