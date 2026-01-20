{{-- 
    Admin-only SenderID Extra Information
    ADMIN ONLY: Section E - Submission Metadata
    Include AFTER the canonical review summary partial (Sections A-D)
--}}
<style>
.senderid-admin-metadata-section {
    background: linear-gradient(135deg, #e8f4fd 0%, #fff 100%);
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
}

.senderid-admin-section-header {
    font-weight: 600;
    color: var(--admin-primary, #1e3a5f);
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.senderid-admin-section-header .section-letter {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background: #1e3a5f;
    color: #fff;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 700;
}

.senderid-admin-section-header .admin-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    background: #fef3c7;
    color: #92400e;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.6rem;
    font-weight: 600;
    margin-left: auto;
}

.senderid-metadata-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
}

.senderid-metadata-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.senderid-metadata-label {
    font-size: 0.65rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.senderid-metadata-value {
    font-size: 0.85rem;
    color: #1e293b;
    font-weight: 500;
}

.senderid-metadata-value.mono {
    font-family: 'SF Mono', monospace;
    font-size: 0.75rem;
    background: #f1f5f9;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    display: inline-block;
}

.senderid-validation-status {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.senderid-validation-status.pending { background: #fef3c7; color: #92400e; }
.senderid-validation-status.validated { background: #d9f99d; color: #3f6212; }
.senderid-validation-status.failed { background: #fecaca; color: #991b1b; }
.senderid-validation-status.not-started { background: #f1f5f9; color: #64748b; }
</style>

<div class="senderid-admin-metadata-section">
    <div class="senderid-admin-section-header">
        <span class="section-letter">E</span>
        <i class="fas fa-database"></i> Submission Metadata
        <span class="admin-badge"><i class="fas fa-lock"></i> Admin Only</span>
    </div>
    
    <div class="senderid-metadata-grid">
        <div class="senderid-metadata-item">
            <span class="senderid-metadata-label">Version ID</span>
            <span class="senderid-metadata-value mono">{{ $metadata['versionId'] ?? '-' }}</span>
        </div>
        <div class="senderid-metadata-item">
            <span class="senderid-metadata-label">Submitted By</span>
            <span class="senderid-metadata-value">{{ $metadata['submittedBy'] ?? '-' }}</span>
        </div>
        <div class="senderid-metadata-item">
            <span class="senderid-metadata-label">Account</span>
            <span class="senderid-metadata-value">{{ $metadata['account'] ?? '-' }}</span>
        </div>
        <div class="senderid-metadata-item">
            <span class="senderid-metadata-label">Sub-Account</span>
            <span class="senderid-metadata-value">{{ $metadata['subAccount'] ?? '-' }}</span>
        </div>
        <div class="senderid-metadata-item">
            <span class="senderid-metadata-label">Created At</span>
            <span class="senderid-metadata-value">{{ $metadata['createdAt'] ?? '-' }}</span>
        </div>
        <div class="senderid-metadata-item">
            <span class="senderid-metadata-label">Submitted At</span>
            <span class="senderid-metadata-value">{{ $metadata['submittedAt'] ?? '-' }}</span>
        </div>
        <div class="senderid-metadata-item">
            <span class="senderid-metadata-label">Last Updated</span>
            <span class="senderid-metadata-value">{{ $metadata['lastUpdatedAt'] ?? '-' }}</span>
        </div>
        <div class="senderid-metadata-item">
            <span class="senderid-metadata-label">External Validation Status</span>
            <span class="senderid-metadata-value">
                @php
                    $validationStatus = $metadata['externalValidationStatus'] ?? 'not-started';
                    $statusClass = match(strtolower($validationStatus)) {
                        'pending', 'in progress', 'in-progress' => 'pending',
                        'validated', 'approved', 'passed' => 'validated',
                        'failed', 'rejected' => 'failed',
                        default => 'not-started'
                    };
                    $statusIcon = match($statusClass) {
                        'pending' => 'fa-clock',
                        'validated' => 'fa-check-circle',
                        'failed' => 'fa-times-circle',
                        default => 'fa-minus-circle'
                    };
                @endphp
                <span class="senderid-validation-status {{ $statusClass }}">
                    <i class="fas {{ $statusIcon }}"></i>
                    {{ ucwords(str_replace('-', ' ', $validationStatus)) }}
                </span>
            </span>
        </div>
    </div>
    
    @if(!empty($metadata['externalReferenceIds']))
    <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
        <div class="senderid-metadata-label" style="margin-bottom: 0.5rem;">External Reference IDs</div>
        <div class="senderid-metadata-grid">
            @foreach($metadata['externalReferenceIds'] as $provider => $refId)
            <div class="senderid-metadata-item">
                <span class="senderid-metadata-label">{{ $provider }}</span>
                <span class="senderid-metadata-value mono">{{ $refId }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
