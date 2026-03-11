@extends('layouts.quicksms')

@section('title', 'Flow Builder')

@push('styles')
<style>
.flow-card {
    border: 1px solid #e6e6e6;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    background: #fff;
    transition: box-shadow 0.2s, border-color 0.2s;
    cursor: pointer;
}
.flow-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    border-color: #886CC0;
}
.flow-status {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.flow-status-draft { background: #f0f0f0; color: #666; }
.flow-status-active { background: #e8f5e9; color: #2e7d32; }
.flow-status-paused { background: #fff3e0; color: #e65100; }
.flow-status-archived { background: #f3e5f5; color: #7b1fa2; }
.flow-actions .btn { padding: 4px 10px; font-size: 0.8rem; }
.empty-flows {
    text-align: center;
    padding: 80px 20px;
    color: #999;
}
.empty-flows i {
    font-size: 64px;
    margin-bottom: 20px;
    color: #ddd;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1" style="font-weight: 600;">Flow Builder</h3>
            <p class="text-muted mb-0">Create and manage automated messaging journeys</p>
        </div>
        <button class="btn btn-primary" id="btn-create-flow" style="background: #886CC0; border-color: #886CC0;">
            <i class="fas fa-plus me-1"></i> Create Flow
        </button>
    </div>

    @if($flows->isEmpty())
        <div class="empty-flows">
            <i class="fas fa-project-diagram"></i>
            <h4>No flows yet</h4>
            <p class="mb-4">Create your first automated messaging flow to get started.</p>
            <button class="btn btn-primary" id="btn-create-flow-empty" style="background: #886CC0; border-color: #886CC0;">
                <i class="fas fa-plus me-1"></i> Create Your First Flow
            </button>
        </div>
    @else
        <div class="row">
            @foreach($flows as $flow)
            <div class="col-md-6 col-lg-4">
                <div class="flow-card" onclick="window.location='{{ route('flows.builder', $flow->id) }}'">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="mb-0" style="font-weight: 600;">{{ $flow->name }}</h5>
                        <span class="flow-status flow-status-{{ $flow->status }}">{{ $flow->status }}</span>
                    </div>
                    @if($flow->description)
                        <p class="text-muted small mb-3">{{ Str::limit($flow->description, 100) }}</p>
                    @endif
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Updated {{ $flow->updated_at->diffForHumans() }}
                        </small>
                        <div class="flow-actions" onclick="event.stopPropagation();">
                            <button class="btn btn-outline-secondary btn-sm" onclick="duplicateFlow({{ $flow->id }})" title="Duplicate">
                                <i class="fas fa-copy"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteFlow({{ $flow->id }}, '{{ addslashes($flow->name) }}')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Create Flow Modal -->
<div class="modal fade" id="createFlowModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #eee;">
                <h5 class="modal-title" style="font-weight: 600;">Create New Flow</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Flow Name</label>
                    <input type="text" class="form-control" id="flow-name" placeholder="e.g. Welcome Journey" maxlength="255">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Description <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea class="form-control" id="flow-description" rows="3" placeholder="What does this flow do?" maxlength="1000"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Start from template</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center template-option" data-template="blank" style="cursor:pointer; border-color: #886CC0 !important; background: #f9f6ff;">
                                <i class="fas fa-file-alt mb-2" style="font-size: 24px; color: #886CC0;"></i>
                                <div class="small fw-bold">Blank Flow</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center template-option" data-template="welcome" style="cursor:pointer;">
                                <i class="fas fa-hand-sparkles mb-2" style="font-size: 24px; color: #886CC0;"></i>
                                <div class="small fw-bold">Welcome Journey</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center template-option" data-template="reminder" style="cursor:pointer;">
                                <i class="fas fa-bell mb-2" style="font-size: 24px; color: #886CC0;"></i>
                                <div class="small fw-bold">Appointment Reminder</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center template-option" data-template="delivery" style="cursor:pointer;">
                                <i class="fas fa-truck mb-2" style="font-size: 24px; color: #886CC0;"></i>
                                <div class="small fw-bold">Delivery Updates</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #eee;">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-submit-flow" style="background: #886CC0; border-color: #886CC0;">
                    Create Flow
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var selectedTemplate = 'blank';
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Template selection
    document.querySelectorAll('.template-option').forEach(function(el) {
        el.addEventListener('click', function() {
            document.querySelectorAll('.template-option').forEach(function(o) {
                o.style.borderColor = '#dee2e6';
                o.style.background = '#fff';
            });
            this.style.borderColor = '#886CC0';
            this.style.background = '#f9f6ff';
            selectedTemplate = this.dataset.template;
        });
    });

    // Create flow buttons
    document.getElementById('btn-create-flow').addEventListener('click', function() {
        new bootstrap.Modal(document.getElementById('createFlowModal')).show();
    });

    var emptyBtn = document.getElementById('btn-create-flow-empty');
    if (emptyBtn) {
        emptyBtn.addEventListener('click', function() {
            new bootstrap.Modal(document.getElementById('createFlowModal')).show();
        });
    }

    // Submit new flow
    document.getElementById('btn-submit-flow').addEventListener('click', function() {
        var name = document.getElementById('flow-name').value.trim();
        if (!name) {
            document.getElementById('flow-name').classList.add('is-invalid');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Creating...';

        fetch('{{ route("flows.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                description: document.getElementById('flow-description').value.trim(),
                template: selectedTemplate
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.redirect) {
                window.location.href = data.redirect + '?template=' + selectedTemplate;
            }
        })
        .catch(function(err) {
            console.error(err);
            document.getElementById('btn-submit-flow').disabled = false;
            document.getElementById('btn-submit-flow').innerHTML = 'Create Flow';
        });
    });
})();

function duplicateFlow(id) {
    if (!confirm('Duplicate this flow?')) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/flows/' + id + '/duplicate', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) window.location.reload();
    });
}

function deleteFlow(id, name) {
    if (!confirm('Delete flow "' + name + '"? This cannot be undone.')) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    fetch('/flows/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) window.location.reload();
    });
}
</script>
@endpush
