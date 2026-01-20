{{-- 
    Compare Versions Component
    Side-by-side comparison of submission versions for admin review
    
    Usage:
    @include('partials.admin.compare-versions', [
        'submissionId' => 'SID-001',
        'submissionType' => 'sender-id', // or 'rcs-agent'
        'versions' => [
            ['id' => 'v2', 'label' => 'Version 2 (Current)', 'date' => '20 Jan 2026'],
            ['id' => 'v1', 'label' => 'Version 1', 'date' => '15 Jan 2026'],
        ]
    ])
--}}

@php
    $submissionId = $submissionId ?? '';
    $submissionType = $submissionType ?? 'sender-id';
    $versions = $versions ?? [];
@endphp

<style>
.compare-versions-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.compare-versions-btn:hover {
    background: var(--admin-secondary, #2d5a87);
}

.compare-versions-modal .modal-dialog {
    max-width: 95%;
    width: 1400px;
}

.compare-versions-modal .modal-header {
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
}

.compare-versions-modal .modal-header .btn-close {
    filter: brightness(0) invert(1);
}

.compare-header-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.version-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.version-selector label {
    font-weight: 600;
    font-size: 0.8rem;
    color: #64748b;
    white-space: nowrap;
}

.version-selector select {
    min-width: 180px;
    font-size: 0.85rem;
}

.compare-swap-btn {
    background: #e2e8f0;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}

.compare-swap-btn:hover {
    background: var(--admin-primary, #1e3a5f);
    color: #fff;
}

.compare-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    height: 600px;
    overflow: hidden;
}

.compare-panel {
    border-right: 1px solid #e2e8f0;
    overflow-y: auto;
    padding: 1rem;
}

.compare-panel:last-child {
    border-right: none;
}

.compare-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: #f1f5f9;
    border-radius: 6px;
    margin-bottom: 1rem;
    position: sticky;
    top: 0;
    z-index: 10;
}

.compare-panel-header.left-panel {
    background: #fee2e2;
}

.compare-panel-header.right-panel {
    background: #dcfce7;
}

.panel-version-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.6rem;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.75rem;
}

.panel-version-badge.old {
    background: #991b1b;
    color: #fff;
}

.panel-version-badge.new {
    background: #166534;
    color: #fff;
}

.compare-section {
    margin-bottom: 1.25rem;
}

.compare-section-header {
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--admin-primary, #1e3a5f);
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.compare-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    border-radius: 4px;
}

.compare-field.changed {
    background: #fef3c7;
    border-left: 3px solid #f59e0b;
}

.compare-field.added {
    background: #dcfce7;
    border-left: 3px solid #22c55e;
}

.compare-field.removed {
    background: #fee2e2;
    border-left: 3px solid #ef4444;
}

.compare-field-label {
    font-size: 0.7rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.compare-field-value {
    font-size: 0.85rem;
    color: #1e293b;
}

.compare-field-value.empty {
    color: #94a3b8;
    font-style: italic;
}

.diff-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.65rem;
    padding: 0.1rem 0.35rem;
    border-radius: 3px;
    margin-left: 0.5rem;
}

.diff-indicator.changed {
    background: #fef3c7;
    color: #92400e;
}

.diff-indicator.added {
    background: #dcfce7;
    color: #166534;
}

.diff-indicator.removed {
    background: #fee2e2;
    color: #991b1b;
}

.compare-summary {
    display: flex;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.summary-stat {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.8rem;
}

.summary-stat .count {
    font-weight: 700;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

.summary-stat .count.changed { background: #fef3c7; color: #92400e; }
.summary-stat .count.added { background: #dcfce7; color: #166534; }
.summary-stat .count.removed { background: #fee2e2; color: #991b1b; }
.summary-stat .count.unchanged { background: #e2e8f0; color: #64748b; }
</style>

<button type="button" class="compare-versions-btn" onclick="openCompareVersions()" id="compareVersionsBtn">
    <i class="fas fa-columns"></i>
    Compare Versions
</button>

<div class="modal fade compare-versions-modal" id="compareVersionsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-columns me-2"></i>Compare Submission Versions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="compare-header-controls">
                <div class="version-selector">
                    <label>Older Version:</label>
                    <select class="form-select form-select-sm" id="compareVersionOld" onchange="updateComparison()">
                        @foreach($versions as $version)
                        <option value="{{ $version['id'] }}">{{ $version['label'] }} ({{ $version['date'] }})</option>
                        @endforeach
                    </select>
                </div>
                
                <button type="button" class="compare-swap-btn" onclick="swapVersions()" title="Swap versions">
                    <i class="fas fa-exchange-alt"></i>
                </button>
                
                <div class="version-selector">
                    <label>Newer Version:</label>
                    <select class="form-select form-select-sm" id="compareVersionNew" onchange="updateComparison()">
                        @foreach($versions as $version)
                        <option value="{{ $version['id'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $version['label'] }} ({{ $version['date'] }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div style="margin-left: auto;">
                    <label class="form-check" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" class="form-check-input" id="showOnlyChanges" onchange="updateComparison()">
                        <span style="font-size: 0.85rem;">Show only changes</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-body p-0">
                <div class="compare-container" id="compareContainer">
                    <div class="compare-panel" id="leftPanel">
                        <div class="compare-panel-header left-panel">
                            <span class="panel-version-badge old">
                                <i class="fas fa-arrow-left"></i> v1
                            </span>
                            <span style="font-size: 0.8rem; color: #64748b;">15 Jan 2026, 14:30</span>
                        </div>
                        <div class="compare-content" id="leftContent"></div>
                    </div>
                    <div class="compare-panel" id="rightPanel">
                        <div class="compare-panel-header right-panel">
                            <span class="panel-version-badge new">
                                v2 <i class="fas fa-arrow-right"></i>
                            </span>
                            <span style="font-size: 0.8rem; color: #64748b;">20 Jan 2026, 10:15</span>
                        </div>
                        <div class="compare-content" id="rightContent"></div>
                    </div>
                </div>
            </div>
            
            <div class="compare-summary" id="compareSummary">
                <div class="summary-stat">
                    <span class="count changed" id="changedCount">0</span>
                    <span>Changed</span>
                </div>
                <div class="summary-stat">
                    <span class="count added" id="addedCount">0</span>
                    <span>Added</span>
                </div>
                <div class="summary-stat">
                    <span class="count removed" id="removedCount">0</span>
                    <span>Removed</span>
                </div>
                <div class="summary-stat">
                    <span class="count unchanged" id="unchangedCount">0</span>
                    <span>Unchanged</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var CompareVersions = (function() {
    var versionData = {};
    var versionMeta = {};
    
    function init() {
        if (typeof UNIFIED_APPROVAL !== 'undefined') {
            var history = UNIFIED_APPROVAL.getVersionHistory();
            if (history && history.length > 0) {
                history.forEach(function(v) {
                    var vKey = 'v' + v.version;
                    versionData[vKey] = v.data || {};
                    versionMeta[vKey] = {
                        version: v.version,
                        timestamp: v.timestamp,
                        status: v.status
                    };
                });
            }
        }
        
        if (Object.keys(versionData).length === 0) {
            versionData = {
                'v1': {
                    senderId: 'ACMEBNK',
                    type: 'Alphanumeric',
                    brand: 'Acme Bank Ltd',
                    explanation: 'We are registering ACMEBNK as our sender ID for banking notifications.',
                    channels: { portal: true, inbox: false, emailToSms: false, api: true }
                },
                'v2': {
                    senderId: 'ACMEBANK',
                    type: 'Alphanumeric',
                    brand: 'Acme Bank Ltd',
                    explanation: 'We are registering ACMEBANK as our official sender ID for transactional banking notifications including balance alerts, payment confirmations, and security notifications to our customers.',
                    channels: { portal: true, inbox: true, emailToSms: false, api: true }
                }
            };
            versionMeta = {
                'v1': { version: 1, timestamp: '2026-01-15T14:30:00Z', status: 'returned' },
                'v2': { version: 2, timestamp: '2026-01-20T10:15:00Z', status: 'submitted' }
            };
        }
    }
    
    function compareObjects(oldObj, newObj) {
        var changes = { changed: 0, added: 0, removed: 0, unchanged: 0 };
        var allKeys = new Set([...Object.keys(oldObj || {}), ...Object.keys(newObj || {})]);
        var results = [];
        
        allKeys.forEach(function(key) {
            var oldVal = oldObj ? oldObj[key] : undefined;
            var newVal = newObj ? newObj[key] : undefined;
            
            var status = 'unchanged';
            if (oldVal === undefined) {
                status = 'added';
                changes.added++;
            } else if (newVal === undefined) {
                status = 'removed';
                changes.removed++;
            } else if (JSON.stringify(oldVal) !== JSON.stringify(newVal)) {
                status = 'changed';
                changes.changed++;
            } else {
                changes.unchanged++;
            }
            
            results.push({ key: key, oldValue: oldVal, newValue: newVal, status: status });
        });
        
        return { results: results, summary: changes };
    }
    
    function renderField(key, value, status) {
        var displayValue = value;
        if (typeof value === 'object' && value !== null) {
            if (Array.isArray(value)) {
                displayValue = value.join(', ');
            } else {
                displayValue = Object.keys(value).filter(function(k) { return value[k]; }).join(', ') || '(none)';
            }
        }
        if (displayValue === undefined || displayValue === null || displayValue === '') {
            displayValue = '<span class="empty">(empty)</span>';
        }
        
        var label = key.replace(/([A-Z])/g, ' $1').replace(/^./, function(str) { return str.toUpperCase(); });
        
        return '<div class="compare-field ' + status + '">' +
            '<span class="compare-field-label">' + escapeHtml(label) + '</span>' +
            '<span class="compare-field-value">' + (displayValue.includes('<span') ? displayValue : escapeHtml(displayValue)) + '</span>' +
            '</div>';
    }
    
    function escapeHtml(text) {
        if (typeof text !== 'string') return String(text);
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function formatTimestamp(timestamp) {
        if (!timestamp) return '';
        try {
            var date = new Date(timestamp);
            return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ', ' +
                   date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
        } catch (e) {
            return timestamp;
        }
    }
    
    function render(oldVersion, newVersion, showOnlyChanges) {
        init();
        
        var oldData = versionData[oldVersion] || {};
        var newData = versionData[newVersion] || {};
        var oldMeta = versionMeta[oldVersion] || { version: oldVersion.replace('v', '') };
        var newMeta = versionMeta[newVersion] || { version: newVersion.replace('v', '') };
        
        var leftHeader = document.querySelector('.compare-panel-header.left-panel');
        var rightHeader = document.querySelector('.compare-panel-header.right-panel');
        
        if (leftHeader) {
            leftHeader.innerHTML = '<span class="panel-version-badge old"><i class="fas fa-arrow-left"></i> v' + oldMeta.version + '</span>' +
                '<span style="font-size: 0.8rem; color: #64748b;">' + formatTimestamp(oldMeta.timestamp) + '</span>';
        }
        if (rightHeader) {
            rightHeader.innerHTML = '<span class="panel-version-badge new">v' + newMeta.version + ' <i class="fas fa-arrow-right"></i></span>' +
                '<span style="font-size: 0.8rem; color: #64748b;">' + formatTimestamp(newMeta.timestamp) + '</span>';
        }
        
        var comparison = compareObjects(oldData, newData);
        
        var leftHtml = '';
        var rightHtml = '';
        
        comparison.results.forEach(function(item) {
            if (showOnlyChanges && item.status === 'unchanged') return;
            
            leftHtml += renderField(item.key, item.oldValue, item.status);
            rightHtml += renderField(item.key, item.newValue, item.status);
        });
        
        document.getElementById('leftContent').innerHTML = leftHtml || '<p class="text-muted text-center py-4">No differences to display</p>';
        document.getElementById('rightContent').innerHTML = rightHtml || '<p class="text-muted text-center py-4">No differences to display</p>';
        
        document.getElementById('changedCount').textContent = comparison.summary.changed;
        document.getElementById('addedCount').textContent = comparison.summary.added;
        document.getElementById('removedCount').textContent = comparison.summary.removed;
        document.getElementById('unchangedCount').textContent = comparison.summary.unchanged;
    }
    
    function getVersionData() {
        init();
        return versionData;
    }
    
    return { render: render, init: init, getVersionData: getVersionData };
})();

function openCompareVersions() {
    var modal = new bootstrap.Modal(document.getElementById('compareVersionsModal'));
    modal.show();
    
    var oldSelect = document.getElementById('compareVersionOld');
    var newSelect = document.getElementById('compareVersionNew');
    if (oldSelect.options.length >= 2) {
        oldSelect.value = oldSelect.options[1].value;
        newSelect.value = newSelect.options[0].value;
    }
    
    updateComparison();
}

function updateComparison() {
    var oldVersion = document.getElementById('compareVersionOld').value;
    var newVersion = document.getElementById('compareVersionNew').value;
    var showOnlyChanges = document.getElementById('showOnlyChanges').checked;
    
    CompareVersions.render(oldVersion, newVersion, showOnlyChanges);
}

function swapVersions() {
    var oldSelect = document.getElementById('compareVersionOld');
    var newSelect = document.getElementById('compareVersionNew');
    var temp = oldSelect.value;
    oldSelect.value = newSelect.value;
    newSelect.value = temp;
    updateComparison();
}
</script>
