{{-- 
    Version History Dropdown Component
    Allows selecting older versions of a submission
    
    Usage:
    @include('partials.admin.version-history-dropdown', [
        'currentVersion' => 'v3',
        'submissionId' => 'RCS-2026-00142',
        'submissionType' => 'rcs-agent', // or 'sender-id'
        'versions' => [
            ['id' => 'v3', 'label' => 'Version 3 (Current)', 'date' => '20 Jan 2026', 'status' => 'submitted'],
            ['id' => 'v2', 'label' => 'Version 2', 'date' => '18 Jan 2026', 'status' => 'returned'],
            ['id' => 'v1', 'label' => 'Version 1', 'date' => '15 Jan 2026', 'status' => 'returned'],
        ]
    ])
--}}

@php
    $currentVersion = $currentVersion ?? 'v1';
    $submissionId = $submissionId ?? '';
    $submissionType = $submissionType ?? 'sender-id';
    $versions = $versions ?? [];
@endphp

<style>
.version-history-dropdown {
    position: relative;
    display: inline-block;
}

.version-history-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    color: var(--admin-primary, #1e3a5f);
    cursor: pointer;
    transition: all 0.2s;
}

.version-history-btn:hover {
    border-color: var(--admin-primary, #1e3a5f);
    background: #f8fafc;
}

.version-history-btn .version-badge {
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.version-history-btn .current-label {
    font-size: 0.7rem;
    color: #059669;
    background: #d9f99d;
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
}

.version-history-menu {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 280px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    z-index: 100;
    margin-top: 0.25rem;
    display: none;
}

.version-history-menu.show {
    display: block;
}

.version-history-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: var(--admin-primary, #1e3a5f);
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.version-history-list {
    max-height: 300px;
    overflow-y: auto;
}

.version-history-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background 0.15s;
    border-bottom: 1px solid #f8fafc;
}

.version-history-item:last-child {
    border-bottom: none;
}

.version-history-item:hover {
    background: #f8fafc;
}

.version-history-item.active {
    background: rgba(30, 58, 95, 0.05);
    border-left: 3px solid var(--admin-primary, #1e3a5f);
}

.version-history-item .version-info {
    flex: 1;
}

.version-history-item .version-label {
    font-weight: 600;
    font-size: 0.85rem;
    color: #1e293b;
}

.version-history-item .version-date {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.125rem;
}

.version-history-item .version-status {
    font-size: 0.65rem;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.version-status.submitted { background: #dbeafe; color: #1e40af; }
.version-status.returned { background: #fef3c7; color: #92400e; }
.version-status.approved { background: #d9f99d; color: #3f6212; }
.version-status.rejected { background: #fecaca; color: #991b1b; }

.version-history-item .current-indicator {
    font-size: 0.65rem;
    color: #059669;
    background: #d9f99d;
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
    margin-left: 0.5rem;
}

.version-history-item .check-icon {
    color: var(--admin-primary, #1e3a5f);
    font-size: 0.85rem;
}
</style>

<div class="version-history-dropdown" id="versionHistoryDropdown">
    <button class="version-history-btn" type="button" onclick="toggleVersionHistory()">
        <i class="fas fa-history"></i>
        <span>Version History</span>
        <span class="version-badge">{{ $currentVersion }}</span>
        <span class="current-label">Current</span>
        <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 0.25rem;"></i>
    </button>
    
    <div class="version-history-menu" id="versionHistoryMenu">
        <div class="version-history-header">
            <i class="fas fa-code-branch"></i>
            Submission Versions
        </div>
        <div class="version-history-list">
            @forelse($versions as $version)
            <div class="version-history-item {{ $version['id'] === $currentVersion ? 'active' : '' }}" 
                 onclick="selectVersion('{{ $version['id'] }}', '{{ $submissionType }}', '{{ $submissionId }}')">
                <div class="version-info">
                    <div class="version-label">
                        {{ $version['label'] }}
                        @if($version['id'] === $currentVersion)
                        <span class="current-indicator">Current</span>
                        @endif
                    </div>
                    <div class="version-date">{{ $version['date'] }}</div>
                </div>
                <span class="version-status {{ $version['status'] }}">{{ ucfirst($version['status']) }}</span>
                @if($version['id'] === $currentVersion)
                <i class="fas fa-check check-icon ms-2"></i>
                @endif
            </div>
            @empty
            <div class="version-history-item">
                <div class="version-info">
                    <div class="version-label text-muted">No previous versions</div>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function toggleVersionHistory() {
    const menu = document.getElementById('versionHistoryMenu');
    menu.classList.toggle('show');
}

function selectVersion(versionId, submissionType, submissionId) {
    const basePath = submissionType === 'rcs-agent' 
        ? '/admin/approvals/rcs-agent/' 
        : '/admin/approvals/sender-id/';
    window.location.href = basePath + submissionId + '?version=' + versionId;
}

document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('versionHistoryDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById('versionHistoryMenu').classList.remove('show');
    }
});
</script>
