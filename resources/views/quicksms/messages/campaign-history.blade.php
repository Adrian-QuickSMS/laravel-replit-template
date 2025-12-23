@extends('layouts.quicksms')

@section('title', 'Campaign History')

@section('content')
<div class="container-fluid">
    <div class="row page-titles">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('messages') }}">Messages</a></li>
            <li class="breadcrumb-item active">Campaign History</li>
        </ol>
    </div>
    
    <div class="row align-items-start">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Campaign History</h5>
                    <a href="{{ route('messages.send') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Create Campaign
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="campaignsTable">
                            <thead>
                                <tr>
                                    <th>Campaign Name</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Recipients</th>
                                    <th>Send Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $campaign)
                                <tr>
                                    <td class="fw-medium">{{ $campaign['name'] }}</td>
                                    <td>
                                        @if($campaign['channel'] === 'sms_only')
                                            <span class="badge bg-secondary">SMS</span>
                                        @elseif($campaign['channel'] === 'basic_rcs')
                                            <span class="badge bg-success">Basic RCS</span>
                                        @else
                                            <span class="badge bg-primary">Rich RCS</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($campaign['status'] === 'scheduled')
                                            <span class="badge bg-info">Scheduled</span>
                                        @elseif($campaign['status'] === 'sending')
                                            <span class="badge bg-warning text-dark">Sending</span>
                                        @else
                                            <span class="badge bg-success">Complete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($campaign['recipients_delivered'] !== null)
                                            {{ number_format($campaign['recipients_delivered']) }}/{{ number_format($campaign['recipients_total']) }}
                                        @else
                                            {{ number_format($campaign['recipients_total']) }}
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($campaign['send_date'])->format('d/m/Y H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                                        <p class="mb-2">No campaigns to display yet.</p>
                                        <a href="{{ route('messages.send') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-plus me-1"></i> Create your first campaign
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(count($campaigns) > 0)
                    <div class="mt-3">
                        <small class="text-muted">Showing {{ count($campaigns) }} campaign(s)</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
