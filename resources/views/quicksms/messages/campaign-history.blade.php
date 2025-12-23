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
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="card-title mb-2 mb-md-0">Campaign History</h5>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('messages.send') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i> New Campaign
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="campaignSearch" placeholder="Search campaigns by name, sender ID, or channel...">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="campaignsTable">
                            <thead>
                                <tr>
                                    <th>Campaign Name</th>
                                    <th>Channel</th>
                                    <th>Sender ID</th>
                                    <th>Recipients</th>
                                    <th>Status</th>
                                    <th>Sent Date</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($campaigns as $campaign)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $campaign['name'] }}</div>
                                    </td>
                                    <td>
                                        @if($campaign['channel'] === 'sms_only')
                                            <span class="badge bg-secondary"><i class="fas fa-sms me-1"></i>SMS</span>
                                        @elseif($campaign['channel'] === 'basic_rcs')
                                            <span class="badge bg-success"><i class="fas fa-comment-dots me-1"></i>Basic RCS</span>
                                        @else
                                            <span class="badge bg-primary"><i class="fas fa-images me-1"></i>Rich RCS</span>
                                        @endif
                                    </td>
                                    <td>{{ $campaign['sender_id'] }}</td>
                                    <td>{{ number_format($campaign['recipients']) }}</td>
                                    <td>
                                        @if($campaign['status'] === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($campaign['status'] === 'sending')
                                            <span class="badge bg-warning text-dark">Sending</span>
                                        @elseif($campaign['status'] === 'scheduled')
                                            <span class="badge bg-info">Scheduled</span>
                                        @elseif($campaign['status'] === 'failed')
                                            <span class="badge bg-danger">Failed</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                    <td>{{ $campaign['sent_date'] }}</td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" onclick="viewCampaign('{{ $campaign['id'] }}'); return false;"><i class="fas fa-eye me-2"></i>View Details</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="duplicateCampaign('{{ $campaign['id'] }}'); return false;"><i class="fas fa-copy me-2"></i>Duplicate</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCampaign('{{ $campaign['id'] }}'); return false;"><i class="fas fa-trash me-2"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No campaigns found. <a href="{{ route('messages.send') }}">Create your first campaign</a>.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(count($campaigns) > 0)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">Showing {{ count($campaigns) }} campaign(s)</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var campaignsData = @json($campaigns);

document.getElementById('campaignSearch').addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var rows = document.querySelectorAll('#campaignsTable tbody tr');
    
    rows.forEach(function(row) {
        var text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

function viewCampaign(id) {
    // TODO: Implement view campaign details modal
    alert('View campaign details for ID: ' + id + ' (TODO: Implement modal)');
    console.log('TODO: GET /api/campaigns/' + id);
}

function duplicateCampaign(id) {
    // TODO: Implement duplicate campaign
    alert('Duplicate campaign ID: ' + id + ' (TODO: Implement)');
    console.log('TODO: POST /api/campaigns/' + id + '/duplicate');
}

function deleteCampaign(id) {
    if (confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
        // TODO: Implement delete campaign
        alert('Delete campaign ID: ' + id + ' (TODO: Implement)');
        console.log('TODO: DELETE /api/campaigns/' + id);
    }
}
</script>
@endpush
