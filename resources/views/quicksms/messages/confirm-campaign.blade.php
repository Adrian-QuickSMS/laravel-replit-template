@extends('layouts.quicksms')

@section('title', $page_title)

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
        <div class="col-xl-8 col-lg-10">
            <div class="card mb-3">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i>Campaign Summary</h4>
                    <a href="{{ route('messages.send') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Campaign Name</div>
                        <div class="col-sm-8 fw-medium">{{ $campaign['name'] }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Created by</div>
                        <div class="col-sm-8">{{ $campaign['created_by'] }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Created date / time</div>
                        <div class="col-sm-8">{{ $campaign['created_at'] }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Scheduled send time</div>
                        <div class="col-sm-8">
                            @if($campaign['scheduled_time'] === 'Immediate')
                                <span class="badge bg-success">{{ $campaign['scheduled_time'] }}</span>
                            @else
                                <span class="badge bg-info">Scheduled: {{ $campaign['scheduled_time'] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Message validity</div>
                        <div class="col-sm-8">{{ $campaign['message_validity'] }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Sending Window</div>
                        <div class="col-sm-8">{{ $campaign['sending_window'] }}</div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-broadcast-tower me-2 text-info"></i>Channel & Delivery</h4>
                    <a href="{{ route('messages.send') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Channel</div>
                        <div class="col-sm-8">
                            @if($channel['type'] === 'sms_only')
                                <span class="badge bg-secondary"><i class="fas fa-sms me-1"></i>SMS only</span>
                            @elseif($channel['type'] === 'basic_rcs')
                                <span class="badge bg-success"><i class="fas fa-comment-dots me-1"></i>Basic RCS with SMS fallback</span>
                            @else
                                <span class="badge bg-primary"><i class="fas fa-images me-1"></i>Rich RCS with SMS fallback</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">SMS Sender ID</div>
                        <div class="col-sm-8 fw-medium">{{ $channel['sms_sender_id'] }}</div>
                    </div>
                    @if($channel['type'] !== 'sms_only')
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">RCS Agent</div>
                        <div class="col-sm-8">
                            <div class="d-flex align-items-center">
                                <img src="{{ $channel['rcs_agent']['logo'] }}" alt="Agent" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                <span class="fw-medium">{{ $channel['rcs_agent']['name'] }}</span>
                                <i class="fas fa-check-circle text-primary ms-2" title="Verified"></i>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="alert alert-info py-2 mb-0 mt-2">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>
                            @if($channel['type'] === 'sms_only')
                                Messages will be sent via SMS only.
                            @elseif($channel['type'] === 'basic_rcs')
                                Messages over 160 characters will be delivered as a single RCS message where supported. SMS fallback for non-RCS devices.
                            @else
                                Rich RCS will be delivered where supported. SMS will be used as a fallback.
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0"><i class="fas fa-users me-2 text-success"></i>Recipients</h4>
                    <a href="{{ route('messages.send') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center mb-3">
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <div class="h3 text-primary mb-1">{{ number_format($recipients['total_selected']) }}</div>
                            <small class="text-muted">Total Selected</small>
                        </div>
                        <div class="col-6 col-md-3 mb-3 mb-md-0">
                            <div class="h3 text-success mb-1">{{ number_format($recipients['valid']) }}</div>
                            <small class="text-muted">Valid</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="h3 text-danger mb-1">{{ number_format($recipients['invalid']) }}</div>
                            <small class="text-muted">Invalid</small>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="h3 text-warning mb-1">{{ number_format($recipients['opted_out']) }}</div>
                            <small class="text-muted">Opted-out</small>
                        </div>
                    </div>

                    <div class="border-top pt-3">
                        <p class="text-muted small mb-2">Source Breakdown</p>
                        @if($recipients['sources']['manual_input'] > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-keyboard text-muted me-2"></i>Manual input</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['manual_input']) }}</span>
                        </div>
                        @endif
                        @if($recipients['sources']['file_upload'] > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-file-upload text-muted me-2"></i>File upload</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['file_upload']) }}</span>
                        </div>
                        @endif
                        @if($recipients['sources']['contacts'] > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-address-book text-muted me-2"></i>Contacts</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['contacts']) }}</span>
                        </div>
                        @endif
                        @if($recipients['sources']['lists'] > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-list text-muted me-2"></i>Lists</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['lists']) }}</span>
                        </div>
                        @endif
                        @if($recipients['sources']['dynamic_lists'] > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-magic text-muted me-2"></i>Dynamic lists</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['dynamic_lists']) }}</span>
                        </div>
                        @endif
                        @if($recipients['sources']['tags'] > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-tags text-muted me-2"></i>Tags</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['tags']) }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-3">
                    <h4 class="card-title mb-0"><i class="fas fa-pound-sign me-2 text-warning"></i>Pricing</h4>
                </div>
                <div class="card-body p-4">
                    @if($channel['type'] === 'sms_only')
                        @php
                            $messageCount = $recipients['valid'];
                            $subtotal = $messageCount * $pricing['sms_unit_price'];
                            $vatAmount = $pricing['vat_applicable'] ? $subtotal * ($pricing['vat_rate'] / 100) : 0;
                            $total = $subtotal + $vatAmount;
                        @endphp
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Messages</div>
                            <div class="col-6 text-end">{{ number_format($messageCount) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Price per SMS</div>
                            <div class="col-6 text-end">&pound;{{ number_format($pricing['sms_unit_price'], 3) }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6 text-muted">Subtotal (ex VAT)</div>
                            <div class="col-6 text-end">&pound;{{ number_format($subtotal, 2) }}</div>
                        </div>
                        @if($pricing['vat_applicable'])
                        <div class="row mb-2">
                            <div class="col-6 text-muted">VAT ({{ $pricing['vat_rate'] }}%)</div>
                            <div class="col-6 text-end">&pound;{{ number_format($vatAmount, 2) }}</div>
                        </div>
                        @endif
                        <hr>
                        <div class="row">
                            <div class="col-6 fw-bold">Total</div>
                            <div class="col-6 text-end fw-bold h5 mb-0">&pound;{{ number_format($total, 2) }}</div>
                        </div>
                    @else
                        <div class="alert alert-warning py-3 mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Pricing Notice:</strong> This campaign includes RCS delivery. Because RCS availability varies by handset and network, the final cost cannot be calculated in advance.
                        </div>
                        <p class="text-muted small mb-3">SMS fallback messages will be charged at your agreed SMS rate. RCS messages are billed based on actual delivery.</p>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="bg-light p-3 rounded text-center">
                                    <div class="small text-muted">SMS Rate</div>
                                    <div class="fw-bold">&pound;{{ number_format($pricing['sms_unit_price'], 3) }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light p-3 rounded text-center">
                                    <div class="small text-muted">RCS Basic</div>
                                    <div class="fw-bold">&pound;{{ number_format($pricing['rcs_basic_price'], 3) }}</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="bg-light p-3 rounded text-center">
                                    <div class="small text-muted">RCS Single</div>
                                    <div class="fw-bold">&pound;{{ number_format($pricing['rcs_single_price'], 3) }}</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 bg-transparent">
                <div class="card-body px-0">
                    <div class="d-flex gap-2">
                        <a href="{{ route('messages.send') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="button" class="btn btn-primary flex-grow-1" id="sendCampaignBtn" onclick="confirmSend()">
                            <i class="fas fa-paper-plane me-2"></i>Confirm & Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sendingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Sending...</span>
                </div>
                <h5>Sending Campaign</h5>
                <p class="text-muted mb-0">Please wait while we process your campaign...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center py-5">
                <div class="text-success mb-3">
                    <i class="fas fa-check-circle fa-4x"></i>
                </div>
                <h4>Campaign Sent!</h4>
                <p class="text-muted">Your campaign has been queued for delivery.</p>
                <a href="{{ route('messages.campaign-history') }}" class="btn btn-primary">View Campaign History</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmSend() {
    var btn = document.getElementById('sendCampaignBtn');
    btn.disabled = true;
    
    var sendingModal = new bootstrap.Modal(document.getElementById('sendingModal'));
    sendingModal.show();
    
    setTimeout(function() {
        sendingModal.hide();
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    }, 2000);
}
</script>
@endpush
