@extends('layouts.quicksms')

@section('title', $page_title)

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages') }}">Messages</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages.send') }}">Send Message</a></li>
            <li class="breadcrumb-item active">{{ !empty($is_editing_existing) ? 'Update & Send' : 'Confirm & Send' }}</li>
        </ol>
    </div>

    <div class="row align-items-start">
        <div class="col-xl-8 col-lg-10">

            <div class="card mb-3">
                <div class="card-header py-3">
                    <h4 class="card-title mb-0"><i class="fas fa-clipboard-list me-2" style="color: #886CC0;"></i>Campaign Summary</h4>
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
                                <span class="badge" style="background-color: #d4edda; color: #155724;">{{ $campaign['scheduled_time'] }}</span>
                            @else
                                <span class="badge" style="background-color: #f0ebf8; color: #886CC0;">Scheduled: {{ $campaign['scheduled_time'] }}</span>
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
                <div class="card-header py-3">
                    <h4 class="card-title mb-0"><i class="fas fa-broadcast-tower me-2" style="color: #886CC0;"></i>Channel & Delivery</h4>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Channel</div>
                        <div class="col-sm-8">
                            @if($channel['type'] === 'sms_only')
                                <span class="badge" style="background-color: #e9ecef; color: #495057;"><i class="fas fa-sms me-1"></i>SMS only</span>
                            @elseif($channel['type'] === 'basic_rcs')
                                <span class="badge" style="background-color: #d4edda; color: #155724;"><i class="fas fa-comment-dots me-1"></i>Basic RCS with SMS fallback</span>
                            @else
                                <span class="badge" style="background-color: #f0ebf8; color: #886CC0;"><i class="fas fa-images me-1"></i>Rich RCS with SMS fallback</span>
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
                                <img src="{{ $channel['rcs_agent']['logo'] }}" alt="{{ $channel['rcs_agent']['name'] }}" class="rounded me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                <span class="fw-medium">{{ $channel['rcs_agent']['name'] }}</span>
                                <i class="fas fa-check-circle text-primary ms-2" title="Verified"></i>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($channel['type'] !== 'sms_only')
                    <div class="py-2 mb-0 mt-2 rounded" style="background-color: #f0ebf8; color: #6b5b95; padding: 12px;">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>
                            @if($channel['type'] === 'basic_rcs')
                                Messages over 160 characters will be delivered as a single RCS message where supported. SMS fallback for non-RCS devices.
                            @else
                                Rich RCS will be delivered where supported. SMS will be used as a fallback.
                            @endif
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header py-3">
                    <h4 class="card-title mb-0"><i class="fas fa-users me-2" style="color: #886CC0;"></i>Recipients</h4>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center mb-3">
                        <div class="{{ !empty($is_test_standard) && ($blocked_count ?? 0) > 0 ? 'col-6 col-md' : 'col-6 col-md-3' }} mb-3 mb-md-0">
                            <div class="h3 text-primary mb-1">{{ number_format($recipients['total_selected']) }}</div>
                            <small class="text-muted">Total Selected</small>
                        </div>
                        <div class="{{ !empty($is_test_standard) && ($blocked_count ?? 0) > 0 ? 'col-6 col-md' : 'col-6 col-md-3' }} mb-3 mb-md-0">
                            <div class="h3 text-success mb-1">{{ number_format($recipients['valid']) }}</div>
                            <small class="text-muted">Valid</small>
                        </div>
                        <div class="{{ !empty($is_test_standard) && ($blocked_count ?? 0) > 0 ? 'col-6 col-md' : 'col-6 col-md-3' }}">
                            <div class="h3 text-danger mb-1">{{ number_format($recipients['invalid']) }}</div>
                            <small class="text-muted">Invalid</small>
                        </div>
                        <div class="{{ !empty($is_test_standard) && ($blocked_count ?? 0) > 0 ? 'col-6 col-md' : 'col-6 col-md-3' }}">
                            <div class="h3 text-warning mb-1">{{ number_format($recipients['opted_out']) }}</div>
                            <small class="text-muted">Opted-out</small>
                        </div>
                        @if(!empty($is_test_standard) && ($blocked_count ?? 0) > 0)
                        <div class="col-6 col-md">
                            <div class="h3 mb-1" style="color: #dc3545;">{{ number_format($blocked_count) }}</div>
                            <small class="text-muted">Blocked <a href="#" data-bs-toggle="modal" data-bs-target="#blockedInfoModal" title="Why are recipients blocked?"><i class="fas fa-info-circle" style="color: #886CC0;"></i></a></small>
                        </div>
                        @endif
                    </div>

                    <div class="border-top pt-3">
                        <p class="text-muted small mb-2">Source Breakdown</p>
                        @if(($recipients['sources']['manual_input'] ?? 0) > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-keyboard text-muted me-2"></i>Manual input</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['manual_input']) }}</span>
                        </div>
                        @endif
                        @if(($recipients['sources']['file_upload'] ?? 0) > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-file-upload text-muted me-2"></i>File upload</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['file_upload']) }}</span>
                        </div>
                        @endif
                        @if(($recipients['sources']['contacts'] ?? 0) > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-address-book text-muted me-2"></i>Contacts</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['contacts']) }}</span>
                        </div>
                        @endif
                        @if(($recipients['sources']['lists'] ?? 0) > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-list text-muted me-2"></i>Lists</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['lists']) }}</span>
                        </div>
                        @endif
                        @if(($recipients['sources']['dynamic_lists'] ?? 0) > 0)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fas fa-magic text-muted me-2"></i>Dynamic lists</span>
                            <span class="fw-medium">{{ number_format($recipients['sources']['dynamic_lists']) }}</span>
                        </div>
                        @endif
                        @if(($recipients['sources']['tags'] ?? 0) > 0)
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
                    <h4 class="card-title mb-0"><i class="fas fa-pound-sign me-2" style="color: #886CC0;"></i>Pricing</h4>
                </div>
                <div class="card-body p-4">
                    @if($channel['type'] === 'sms_only')
                        @if(!empty($realEstimate))
                            {{-- Real estimate from backend billing engine --}}
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Messages @if(!empty($is_test_standard))<a href="#" data-bs-toggle="modal" data-bs-target="#blockedInfoModal" title="Test Mode info"><i class="fas fa-info-circle" style="color: #886CC0;"></i></a>@endif</div>
                                <div class="col-6 text-end">{{ number_format($deliverable_count ?? $recipients['valid']) }}</div>
                            </div>
                            @if(!empty($is_test_standard) && ($test_mode_sms_parts ?? 0) > 0)
                            <div class="row mb-2">
                                <div class="col-6 text-muted">SMS Parts (inc. test disclaimer)</div>
                                <div class="col-6 text-end">{{ number_format($test_mode_sms_parts) }}</div>
                            </div>
                            @endif
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Estimated Cost (ex VAT)</div>
                                <div class="col-6 text-end">&pound;{{ number_format((float) $realEstimate['total_cost'], 2) }}</div>
                            </div>
                            @if(!$realEstimate['has_sufficient_balance'])
                            <div class="alert alert-warning py-2 px-3 mt-2" style="font-size: 13px;">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Insufficient balance. Available: &pound;{{ number_format((float) $realEstimate['available_balance'], 2) }}
                            </div>
                            @endif
                            <hr>
                            <div class="row">
                                <div class="col-6 fw-bold">Estimated Total</div>
                                <div class="col-6 text-end fw-bold h5 mb-0">&pound;{{ number_format((float) $realEstimate['total_cost'], 2) }}</div>
                            </div>
                        @else
                            {{-- Fallback: simple estimate from session data --}}
                            @php
                                $messageCount = !empty($is_test_standard) ? ($deliverable_count ?? $recipients['valid'] ?? 0) : ($recipients['valid'] ?? 0);
                                $smsUnitPrice = is_object($pricing['sms_unit_price']) ? (float) $pricing['sms_unit_price']->unitPrice : (float) ($pricing['sms_unit_price'] ?? 0);
                                $resolvedParts = !empty($is_test_standard) ? ($test_mode_sms_parts ?? $total_sms_parts ?? 0) : ($total_sms_parts ?? 0);
                                $subtotal = $resolvedParts * $smsUnitPrice;
                                $vatRate = (float) ($pricing['vat_rate'] ?? 0);
                                $vatAmount = $pricing['vat_applicable'] ? $subtotal * ($vatRate / 100) : 0;
                                $total = $subtotal + $vatAmount;
                                $hasBreakdown = !empty($segment_breakdown ?? []);
                            @endphp
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Recipients @if(!empty($is_test_standard))<a href="#" data-bs-toggle="modal" data-bs-target="#blockedInfoModal" title="Test Mode info"><i class="fas fa-info-circle" style="color: #886CC0;"></i></a>@endif</div>
                                <div class="col-6 text-end">{{ number_format($messageCount) }}</div>
                            </div>
                            @if($hasBreakdown && count($segment_breakdown) > 1)
                            <div class="mb-2">
                                <p class="text-muted small mb-1">Segment breakdown (personalised per recipient)</p>
                                @foreach($segment_breakdown as $group)
                                <div class="d-flex justify-content-between py-1 ps-3">
                                    <span class="small text-muted">{{ number_format($group->recipient_count) }} recipients &times; {{ $group->segments }} {{ $group->segments === 1 ? 'segment' : 'segments' }}</span>
                                    <span class="small">{{ number_format($group->recipient_count * $group->segments) }} parts</span>
                                </div>
                                @endforeach
                            </div>
                            @elseif($hasBreakdown && count($segment_breakdown) === 1)
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Segments per message</div>
                                <div class="col-6 text-end">{{ $segment_breakdown[0]->segments }}</div>
                            </div>
                            @else
                                @php $segmentCount = $campaign['segment_count'] ?? 1; @endphp
                                @if($segmentCount > 1)
                                <div class="row mb-2">
                                    <div class="col-6 text-muted">Segments per message</div>
                                    <div class="col-6 text-end">{{ $segmentCount }}</div>
                                </div>
                                @endif
                            @endif
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Total SMS parts</div>
                                <div class="col-6 text-end">{{ number_format($resolvedParts) }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6 text-muted">Price per SMS part</div>
                                <div class="col-6 text-end">&pound;{{ number_format($smsUnitPrice, 4) }}</div>
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
                        @endif
                    @else
                        @php
                            $validRecipients = $recipients['valid'] ?? 0;
                            $penetration = (float) ($pricing['rcs_penetration'] ?? 0.65);
                            $smsPrice = (float) ($pricing['sms_unit_price'] ?? 0);
                            $isBasicRcs = ($channel['type'] === 'basic_rcs');
                            $rcsBasicPrice = (float) ($pricing['rcs_basic_price'] ?? 0);
                            $rcsSinglePrice = (float) ($pricing['rcs_single_price'] ?? 0);

                            $hasSegBreakdown = !empty($segment_breakdown ?? []) && count($segment_breakdown) > 0;
                            $hasMixedSegments = $hasSegBreakdown && count($segment_breakdown) > 1;

                            $maxSmsPartsTotal = $hasSegBreakdown
                                ? array_reduce($segment_breakdown, fn($c, $g) => $c + $g->segments * $g->recipient_count, 0)
                                : $validRecipients;

                            $estRcsBasicCount = 0;
                            $estRcsSingleCount = 0;
                            $estSmsLines = [];
                            $estSmsCostTotal = 0;

                            if ($hasSegBreakdown) {
                                foreach ($segment_breakdown as $grp) {
                                    $grpRcsCount = (int) round($grp->recipient_count * $penetration);
                                    $grpSmsCount = $grp->recipient_count - $grpRcsCount;

                                    if ($isBasicRcs && $grp->segments <= 1) {
                                        $estRcsBasicCount += $grpRcsCount;
                                    } else {
                                        $estRcsSingleCount += $grpRcsCount;
                                    }

                                    $grpSmsParts = $grpSmsCount * $grp->segments;
                                    $grpSmsCost = $grpSmsParts * $smsPrice;
                                    $estSmsCostTotal += $grpSmsCost;

                                    $estSmsLines[] = (object) [
                                        'segments' => $grp->segments,
                                        'recipient_count' => $grp->recipient_count,
                                        'sms_count' => $grpSmsCount,
                                        'sms_parts' => $grpSmsParts,
                                        'sms_cost' => $grpSmsCost,
                                        'rcs_count' => $grpRcsCount,
                                    ];
                                }
                            } else {
                                $estRcsCount = (int) round($validRecipients * $penetration);
                                $estSmsCount = $validRecipients - $estRcsCount;
                                if ($isBasicRcs) {
                                    $estRcsBasicCount = $estRcsCount;
                                } else {
                                    $estRcsSingleCount = $estRcsCount;
                                }
                                $estSmsCostTotal = $estSmsCount * $smsPrice;
                                $estSmsLines[] = (object) [
                                    'segments' => 1,
                                    'recipient_count' => $validRecipients,
                                    'sms_count' => $estSmsCount,
                                    'sms_parts' => $estSmsCount,
                                    'sms_cost' => $estSmsCostTotal,
                                    'rcs_count' => $estRcsCount,
                                ];
                            }

                            $estRcsBasicCost = $estRcsBasicCount * $rcsBasicPrice;
                            $estRcsSingleCost = $estRcsSingleCount * $rcsSinglePrice;
                            $estTotal = $estRcsBasicCost + $estRcsSingleCost + $estSmsCostTotal;

                            $totalEstSmsParts = array_reduce($estSmsLines, fn($c, $l) => $c + $l->sms_parts, 0);

                            $maxSmsCost = $maxSmsPartsTotal * $smsPrice;

                            $maxRcsCost = 0;
                            if ($isBasicRcs && $hasMixedSegments) {
                                foreach ($segment_breakdown as $grp) {
                                    $grpRcsRate = $grp->segments <= 1 ? $rcsBasicPrice : $rcsSinglePrice;
                                    $maxRcsCost += $grp->recipient_count * $grpRcsRate;
                                }
                            } else {
                                $rcsRate = $isBasicRcs ? $rcsBasicPrice : $rcsSinglePrice;
                                $maxRcsCost = $validRecipients * $rcsRate;
                            }
                            $maxTotal = max($maxSmsCost, $maxRcsCost);

                            $vatRate = (float) ($pricing['vat_rate'] ?? 0);
                            $estVat = $pricing['vat_applicable'] ? $estTotal * ($vatRate / 100) : 0;
                            $maxVat = $pricing['vat_applicable'] ? $maxTotal * ($vatRate / 100) : 0;
                        @endphp

                        <div class="py-3 mb-3 rounded" style="background-color: #f0ebf8; color: #6b5b95; padding: 12px;">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Pricing Notice:</strong> This campaign includes RCS delivery. Because RCS availability varies by handset and network, the final cost cannot be calculated in advance.
                        </div>
                        <p class="text-muted small mb-3">SMS fallback messages will be charged at your agreed SMS rate. RCS messages are billed based on actual delivery.</p>

                        <div class="row g-3 mb-3">
                            <div class="{{ $hasMixedSegments && $isBasicRcs ? 'col-6 col-md-3' : 'col-4' }}">
                                <div class="p-3 rounded text-center" style="background-color: #e9ecef;">
                                    <div class="small text-dark">SMS Rate</div>
                                    <div class="fw-bold text-dark">&pound;{{ number_format($smsPrice, 4) }}</div>
                                </div>
                            </div>
                            @if($hasMixedSegments && $isBasicRcs)
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded text-center" style="background-color: #e9ecef;">
                                    <div class="small text-dark">RCS Basic Rate</div>
                                    <div class="fw-bold text-dark">&pound;{{ number_format($rcsBasicPrice, 4) }}</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded text-center" style="background-color: #e9ecef;">
                                    <div class="small text-dark">RCS Single Rate</div>
                                    <div class="fw-bold text-dark">&pound;{{ number_format($rcsSinglePrice, 4) }}</div>
                                </div>
                            </div>
                            @else
                            <div class="col-4">
                                <div class="p-3 rounded text-center" style="background-color: #e9ecef;">
                                    <div class="small text-dark">{{ $isBasicRcs ? 'RCS Basic' : 'RCS Single' }} Rate</div>
                                    <div class="fw-bold text-dark">&pound;{{ number_format($isBasicRcs ? $rcsBasicPrice : $rcsSinglePrice, 4) }}</div>
                                </div>
                            </div>
                            @endif
                            <div class="{{ $hasMixedSegments && $isBasicRcs ? 'col-6 col-md-3' : 'col-4' }}">
                                <div class="p-3 rounded text-center" style="background-color: #e9ecef;">
                                    <div class="small text-dark">RCS Penetration</div>
                                    <div class="fw-bold text-dark">{{ number_format($penetration * 100, 0) }}%</div>
                                </div>
                            </div>
                        </div>

                        @if($hasMixedSegments)
                        <div class="mb-3 p-3 rounded" style="background-color: #f8f9fa;">
                            <p class="small fw-bold mb-2 text-dark">Per-recipient segment breakdown</p>
                            @foreach($segment_breakdown as $grp)
                            <div class="d-flex justify-content-between small py-1 ps-2">
                                <span class="text-muted">{{ number_format($grp->recipient_count) }} recipients &times; {{ $grp->segments }} {{ $grp->segments === 1 ? 'segment' : 'segments' }}
                                    @if($isBasicRcs)
                                    &rarr; {{ $grp->segments <= 1 ? 'RCS Basic' : 'RCS Single' }}
                                    @endif
                                </span>
                                <span>{{ number_format($grp->recipient_count * $grp->segments) }} SMS parts</span>
                            </div>
                            @endforeach
                            <div class="d-flex justify-content-between small pt-1 ps-2 fw-bold border-top mt-1">
                                <span>Total SMS parts (if all via SMS)</span>
                                <span>{{ number_format($maxSmsPartsTotal) }}</span>
                            </div>
                        </div>
                        @endif

                        <div class="row g-3" style="align-items: stretch;">
                            <div class="col-6 d-flex">
                                <div class="p-3 rounded d-flex flex-column w-100" style="background-color: #f0ebf8;">
                                    <div class="small fw-bold mb-2 text-dark">
                                        Estimated Cost
                                        <i class="fas fa-info-circle ms-1" style="cursor: pointer; color: #886CC0;" data-bs-toggle="modal" data-bs-target="#estimatedCostInfoModal"></i>
                                    </div>
                                    @if($estRcsBasicCount > 0)
                                    <div class="d-flex justify-content-between small mb-1 text-dark">
                                        <span>RCS Basic: {{ number_format($estRcsBasicCount) }} &times; &pound;{{ number_format($rcsBasicPrice, 4) }}</span>
                                        <span>&pound;{{ number_format($estRcsBasicCost, 2) }}</span>
                                    </div>
                                    @endif
                                    @if($estRcsSingleCount > 0)
                                    <div class="d-flex justify-content-between small mb-1 text-dark">
                                        <span>RCS Single: {{ number_format($estRcsSingleCount) }} &times; &pound;{{ number_format($rcsSinglePrice, 4) }}</span>
                                        <span>&pound;{{ number_format($estRcsSingleCost, 2) }}</span>
                                    </div>
                                    @endif
                                    <div class="d-flex justify-content-between small mb-1 text-dark">
                                        <span>SMS fallback: {{ number_format($totalEstSmsParts) }} parts &times; &pound;{{ number_format($smsPrice, 4) }}</span>
                                        <span>&pound;{{ number_format($estSmsCostTotal, 2) }}</span>
                                    </div>
                                    @if($pricing['vat_applicable'])
                                    <div class="d-flex justify-content-between small mb-1 text-dark">
                                        <span>VAT ({{ $vatRate }}%)</span>
                                        <span>&pound;{{ number_format($estVat, 2) }}</span>
                                    </div>
                                    @endif
                                    <div class="mt-auto">
                                        <hr class="my-1">
                                        <div class="d-flex justify-content-between fw-bold text-dark">
                                            <span>Total</span>
                                            <span>&pound;{{ number_format($estTotal + $estVat, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 d-flex">
                                <div class="p-3 rounded d-flex flex-column w-100" style="background-color: #e9ecef;">
                                    <div class="small fw-bold mb-2 text-dark">
                                        Maximum Cost
                                        <i class="fas fa-info-circle ms-1" style="cursor: pointer; color: #6c757d;" data-bs-toggle="modal" data-bs-target="#maximumCostInfoModal"></i>
                                    </div>
                                    @if($maxSmsCost >= $maxRcsCost)
                                    <div class="d-flex justify-content-between small mb-1 text-dark">
                                        <span>All SMS: {{ number_format($maxSmsPartsTotal) }} parts &times; &pound;{{ number_format($smsPrice, 4) }}</span>
                                        <span>&pound;{{ number_format($maxSmsCost, 2) }}</span>
                                    </div>
                                    @else
                                    <div class="d-flex justify-content-between small mb-1 text-dark">
                                        <span>All RCS: {{ number_format($validRecipients) }} msgs</span>
                                        <span>&pound;{{ number_format($maxRcsCost, 2) }}</span>
                                    </div>
                                    @endif
                                    @if($pricing['vat_applicable'])
                                    <div class="d-flex justify-content-between small mb-1 text-dark">
                                        <span>VAT ({{ $vatRate }}%)</span>
                                        <span>&pound;{{ number_format($maxVat, 2) }}</span>
                                    </div>
                                    @endif
                                    <div class="mt-auto">
                                        <hr class="my-1">
                                        <div class="d-flex justify-content-between fw-bold text-dark">
                                            <span>Total</span>
                                            <span>&pound;{{ number_format($maxTotal + $maxVat, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 bg-transparent">
                <div class="card-body px-0">
                    <div class="d-flex gap-2">
                        <a href="{{ route('messages.send') }}{{ !empty($campaign['id']) ? '?campaign_id=' . $campaign['id'] : '' }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="button" class="btn btn-primary flex-grow-1" id="sendCampaignBtn" onclick="confirmSend()"
                            @if(!empty($is_test_standard) && empty($approved_test_numbers)) disabled title="Configure approved test numbers before sending" @endif>
                            <i class="fas fa-paper-plane me-2"></i>{{ !empty($is_editing_existing) ? 'Update & Send' : 'Confirm & Send' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $penetration = $penetration ?? (float) ($pricing['rcs_penetration'] ?? 0.65);
    $maxRcsCost = $maxRcsCost ?? 0;
    $maxSmsCost = $maxSmsCost ?? 0;
@endphp
<div class="modal fade" id="estimatedCostInfoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2" style="color: #886CC0;"></i>Estimated Cost</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">This estimate is based on <strong>{{ number_format($penetration * 100, 0) }}%</strong> of recipients receiving RCS and the remaining <strong>{{ number_format((1 - $penetration) * 100, 0) }}%</strong> receiving SMS fallback.</p>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="maximumCostInfoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2" style="color: #6c757d;"></i>Maximum Cost</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">This shows the highest possible cost if every recipient were charged via the most expensive channel (<strong>{{ $maxSmsCost >= $maxRcsCost ? 'SMS' : 'RCS' }}</strong>).</p>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
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

@if(!empty($is_test_standard))
<div class="modal fade" id="blockedInfoModal" tabindex="-1" aria-labelledby="blockedInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="blockedInfoModalLabel"><i class="fas fa-flask me-2" style="color: #886CC0;"></i>Test Mode — Standard</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Your account is in <strong>Test Standard</strong> mode. During this mode, messages can only be delivered to your approved test numbers.</p>

                <div class="rounded-3 p-3 mb-3" style="background-color: #f0ebf8;">
                    <h6 class="mb-2" style="color: #5a3d8a;"><i class="fas fa-phone-alt me-2"></i>Approved Test Numbers</h6>
                    @if(!empty($approved_test_numbers) && count($approved_test_numbers) > 0)
                        <ul class="mb-0 ps-3">
                            @foreach($approved_test_numbers as $number)
                                <li class="small">{{ $number }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mb-0 small text-muted">No approved test numbers configured. <a href="{{ route('account.details') }}">Configure them in Account Settings</a>.</p>
                    @endif
                </div>

                @if(($blocked_count ?? 0) > 0)
                <div class="rounded-3 p-3 mb-3" style="background-color: #fff3cd;">
                    <h6 class="mb-2" style="color: #856404;"><i class="fas fa-ban me-2"></i>Blocked Recipients</h6>
                    <p class="mb-0 small"><strong>{{ number_format($blocked_count) }}</strong> {{ $blocked_count === 1 ? 'recipient is' : 'recipients are' }} not on your approved test numbers list and will not receive this message.</p>
                </div>
                @endif

                <div class="rounded-3 p-3" style="background-color: #e8f4e8;">
                    <h6 class="mb-2" style="color: #2d6a2d;"><i class="fas fa-stamp me-2"></i>Test Disclaimer</h6>
                    <p class="mb-0 small">The text <em>"QuickSMS TEST message..."</em> (+68 chars inc. space) will be prepended to each SMS sent.</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm" style="background-color: #886CC0; color: #fff;" data-bs-dismiss="modal">Understood</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
function confirmSend() {
    var btn = document.getElementById('sendCampaignBtn');
    btn.disabled = true;

    var sendingModal = new bootstrap.Modal(document.getElementById('sendingModal'));
    sendingModal.show();

    fetch('{{ route("messages.confirm-send") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ campaign_id: '{{ $campaign_id ?? '' }}' })
    })
    .then(function(response) { return response.json().then(function(data) { return { ok: response.ok, data: data }; }); })
    .then(function(result) {
        sendingModal.hide();

        if (result.ok && result.data.success) {
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        } else {
            btn.disabled = false;
            var msg = result.data.message || 'Failed to send campaign.';
            if (result.data.errors) {
                var errorList = Object.values(result.data.errors).flat().join('\n');
                msg += '\n\n' + errorList;
            }
            alert(msg);
        }
    })
    .catch(function(error) {
        sendingModal.hide();
        btn.disabled = false;
        console.error('Error:', error);
        alert('Network error. Please try again.');
    });
}

</script>
@endpush
