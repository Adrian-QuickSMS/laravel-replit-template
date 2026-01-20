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

.changes-summary-bar {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-bottom: 1px solid #bae6fd;
    padding: 0.75rem 1rem;
}

.changes-summary-title {
    font-weight: 600;
    font-size: 0.8rem;
    color: var(--admin-primary, #1e3a5f);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.changed-fields-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.changed-field-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
}

.changed-field-chip:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.changed-field-chip.changed {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
}

.changed-field-chip.added {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #86efac;
}

.changed-field-chip.removed {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.changed-field-chip i {
    font-size: 0.65rem;
}

.compare-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    height: 550px;
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
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.compare-section-header {
    font-weight: 600;
    font-size: 0.8rem;
    color: var(--admin-primary, #1e3a5f);
    padding: 0.6rem 0.75rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.compare-section-body {
    padding: 0.75rem;
}

.compare-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    border-radius: 4px;
    background: #fff;
}

.compare-field:last-child {
    margin-bottom: 0;
}

.compare-field.changed {
    background: #fffbeb;
    border-left: 3px solid #f59e0b;
}

.compare-field.added {
    background: #f0fdf4;
    border-left: 3px solid #22c55e;
}

.compare-field.removed {
    background: #fef2f2;
    border-left: 3px solid #ef4444;
}

.compare-field-label {
    font-size: 0.7rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.compare-field-label .field-type-icon {
    font-size: 0.6rem;
    opacity: 0.7;
}

.compare-field-value {
    font-size: 0.85rem;
    color: #1e293b;
    line-height: 1.5;
}

.compare-field-value.empty {
    color: #94a3b8;
    font-style: italic;
}

.diff-text-removed {
    background: #fecaca;
    color: #991b1b;
    text-decoration: line-through;
    padding: 0.1rem 0.2rem;
    border-radius: 2px;
}

.diff-text-added {
    background: #bbf7d0;
    color: #166534;
    padding: 0.1rem 0.2rem;
    border-radius: 2px;
}

.url-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.url-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f1f5f9;
    padding: 0.35rem 0.5rem;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.75rem;
    word-break: break-all;
}

.url-display.changed {
    background: #fef3c7;
}

.url-display i {
    color: #64748b;
    flex-shrink: 0;
}

.tile-field {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.75rem;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.8rem;
}

.tile-field.changed {
    background: #fef3c7;
    color: #92400e;
    border: 2px dashed #f59e0b;
}

.tile-field i {
    font-size: 0.75rem;
}

.image-field {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.image-preview {
    width: 80px;
    height: 80px;
    border-radius: 6px;
    background: #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e2e8f0;
    overflow: hidden;
}

.image-preview.changed {
    border-color: #f59e0b;
    border-style: dashed;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-preview .placeholder {
    color: #94a3b8;
    font-size: 1.5rem;
}

.image-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    font-size: 0.7rem;
}

.image-meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    background: #f1f5f9;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
}

.image-meta-item.changed {
    background: #fef3c7;
    color: #92400e;
}

.list-field {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.list-item {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.5rem;
    background: #f1f5f9;
    border-radius: 4px;
    font-size: 0.75rem;
    font-family: monospace;
}

.list-item.added {
    background: #dcfce7;
    color: #166534;
}

.list-item.removed {
    background: #fee2e2;
    color: #991b1b;
    text-decoration: line-through;
}

.list-item.unchanged {
    background: #f1f5f9;
    color: #64748b;
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
            
            <div class="changes-summary-bar" id="changesSummaryBar">
                <div class="changes-summary-title">
                    <i class="fas fa-list-check"></i>
                    Changed Fields
                </div>
                <div class="changed-fields-list" id="changedFieldsList"></div>
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
    
    var FIELD_TYPES = {
        text: ['senderId', 'agentName', 'description', 'explanation', 'brand', 'type', 'optInExplanation', 'optOutExplanation'],
        url: ['privacyUrl', 'tosUrl', 'websiteUrl', 'logoUrl', 'heroUrl', 'supportEmail'],
        tile: ['billingCategory', 'useCase', 'vertical', 'messageType'],
        image: ['logoUrl', 'heroUrl'],
        list: ['testNumbers', 'channels', 'capabilities'],
        crop: ['logoCrop', 'heroCrop']
    };
    
    function getFieldType(key) {
        for (var type in FIELD_TYPES) {
            if (FIELD_TYPES[type].indexOf(key) !== -1) return type;
        }
        if (key.toLowerCase().indexOf('url') !== -1) return 'url';
        if (key.toLowerCase().indexOf('crop') !== -1) return 'crop';
        return 'text';
    }
    
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
                    billingCategory: 'Standard',
                    useCase: 'Marketing',
                    privacyUrl: 'https://acme.com/privacy',
                    tosUrl: 'https://acme.com/terms',
                    logoUrl: '/images/logo-v1.png',
                    logoCrop: { x: 0, y: 0, width: 200, height: 200 },
                    testNumbers: ['+447700900001', '+447700900002'],
                    channels: { portal: true, inbox: false, emailToSms: false, api: true }
                },
                'v2': {
                    senderId: 'ACMEBANK',
                    type: 'Alphanumeric',
                    brand: 'Acme Bank Ltd',
                    explanation: 'We are registering ACMEBANK as our official sender ID for transactional banking notifications including balance alerts, payment confirmations, and security notifications to our customers.',
                    billingCategory: 'Financial Services',
                    useCase: 'Transactional',
                    privacyUrl: 'https://acmebank.com/privacy-policy',
                    tosUrl: 'https://acmebank.com/terms-of-service',
                    logoUrl: '/images/logo-v2.png',
                    logoCrop: { x: 10, y: 10, width: 180, height: 180 },
                    testNumbers: ['+447700900001', '+447700900003', '+447700900004'],
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
            
            results.push({ 
                key: key, 
                oldValue: oldVal, 
                newValue: newVal, 
                status: status,
                fieldType: getFieldType(key)
            });
        });
        
        return { results: results, summary: changes };
    }
    
    function escapeHtml(text) {
        if (typeof text !== 'string') return String(text);
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function formatLabel(key) {
        return key.replace(/([A-Z])/g, ' $1').replace(/^./, function(str) { return str.toUpperCase(); });
    }
    
    function renderTextDiff(oldText, newText, status) {
        if (status === 'unchanged') return escapeHtml(newText || '');
        if (status === 'added') return '<span class="diff-text-added">' + escapeHtml(newText) + '</span>';
        if (status === 'removed') return '<span class="diff-text-removed">' + escapeHtml(oldText) + '</span>';
        
        var oldStr = String(oldText || '');
        var newStr = String(newText || '');
        
        if (oldStr.length < 50 && newStr.length < 50) {
            return '<span class="diff-text-removed">' + escapeHtml(oldStr) + '</span> → <span class="diff-text-added">' + escapeHtml(newStr) + '</span>';
        }
        
        return escapeHtml(newStr);
    }
    
    function renderUrlField(value, status, isNew) {
        if (!value) return '<span class="empty">(no URL)</span>';
        var url = String(value);
        var changedClass = status === 'changed' ? ' changed' : '';
        return '<div class="url-display' + changedClass + '"><i class="fas fa-link"></i><span>' + escapeHtml(url) + '</span></div>';
    }
    
    function renderTileField(value, status) {
        if (!value) return '<span class="empty">(not set)</span>';
        var changedClass = status === 'changed' ? ' changed' : '';
        return '<span class="tile-field' + changedClass + '"><i class="fas fa-tag"></i>' + escapeHtml(value) + '</span>';
    }
    
    function renderImageField(url, crop, status) {
        var changedClass = status === 'changed' ? ' changed' : '';
        var html = '<div class="image-field">';
        html += '<div class="image-preview' + changedClass + '">';
        if (url) {
            html += '<img src="' + escapeHtml(url) + '" alt="Preview" onerror="this.parentNode.innerHTML=\'<i class=\\\'fas fa-image placeholder\\\'></i>\'">';
        } else {
            html += '<i class="fas fa-image placeholder"></i>';
        }
        html += '</div>';
        
        if (crop) {
            html += '<div class="image-meta">';
            html += '<span class="image-meta-item' + changedClass + '"><i class="fas fa-crop-alt"></i> ' + crop.width + 'x' + crop.height + '</span>';
            html += '<span class="image-meta-item' + changedClass + '"><i class="fas fa-arrows-alt"></i> (' + crop.x + ', ' + crop.y + ')</span>';
            html += '</div>';
        }
        html += '</div>';
        return html;
    }
    
    function renderListField(oldList, newList, status) {
        var oldArr = Array.isArray(oldList) ? oldList : [];
        var newArr = Array.isArray(newList) ? newList : [];
        
        if (typeof oldList === 'object' && !Array.isArray(oldList)) {
            oldArr = Object.keys(oldList || {}).filter(function(k) { return oldList[k]; });
        }
        if (typeof newList === 'object' && !Array.isArray(newList)) {
            newArr = Object.keys(newList || {}).filter(function(k) { return newList[k]; });
        }
        
        var allItems = new Set([...oldArr, ...newArr]);
        var html = '<div class="list-field">';
        
        allItems.forEach(function(item) {
            var inOld = oldArr.indexOf(item) !== -1;
            var inNew = newArr.indexOf(item) !== -1;
            var itemClass = 'unchanged';
            var icon = '';
            
            if (!inOld && inNew) {
                itemClass = 'added';
                icon = '<i class="fas fa-plus"></i> ';
            } else if (inOld && !inNew) {
                itemClass = 'removed';
                icon = '<i class="fas fa-minus"></i> ';
            }
            
            html += '<span class="list-item ' + itemClass + '">' + icon + escapeHtml(item) + '</span>';
        });
        
        html += '</div>';
        return html;
    }
    
    function renderField(item, isNewPanel) {
        var value = isNewPanel ? item.newValue : item.oldValue;
        var otherValue = isNewPanel ? item.oldValue : item.newValue;
        var status = item.status;
        var fieldType = item.fieldType;
        var label = formatLabel(item.key);
        
        var typeIcon = '';
        switch(fieldType) {
            case 'url': typeIcon = '<i class="field-type-icon fas fa-link"></i>'; break;
            case 'tile': typeIcon = '<i class="field-type-icon fas fa-th-large"></i>'; break;
            case 'image': typeIcon = '<i class="field-type-icon fas fa-image"></i>'; break;
            case 'list': typeIcon = '<i class="field-type-icon fas fa-list"></i>'; break;
            case 'crop': typeIcon = '<i class="field-type-icon fas fa-crop-alt"></i>'; break;
            default: typeIcon = '<i class="field-type-icon fas fa-font"></i>';
        }
        
        var valueHtml = '';
        
        switch(fieldType) {
            case 'url':
                valueHtml = renderUrlField(value, status, isNewPanel);
                break;
            case 'tile':
                valueHtml = renderTileField(value, status);
                break;
            case 'image':
                var cropKey = item.key.replace('Url', 'Crop');
                var cropData = isNewPanel ? 
                    (versionData[document.getElementById('compareVersionNew').value] || {})[cropKey] :
                    (versionData[document.getElementById('compareVersionOld').value] || {})[cropKey];
                valueHtml = renderImageField(value, cropData, status);
                break;
            case 'list':
                valueHtml = renderListField(item.oldValue, item.newValue, status);
                break;
            case 'crop':
                if (typeof value === 'object' && value) {
                    valueHtml = '<div class="image-meta">';
                    valueHtml += '<span class="image-meta-item ' + (status === 'changed' ? 'changed' : '') + '">x: ' + value.x + '</span>';
                    valueHtml += '<span class="image-meta-item ' + (status === 'changed' ? 'changed' : '') + '">y: ' + value.y + '</span>';
                    valueHtml += '<span class="image-meta-item ' + (status === 'changed' ? 'changed' : '') + '">w: ' + value.width + '</span>';
                    valueHtml += '<span class="image-meta-item ' + (status === 'changed' ? 'changed' : '') + '">h: ' + value.height + '</span>';
                    valueHtml += '</div>';
                } else {
                    valueHtml = '<span class="empty">(no crop)</span>';
                }
                break;
            default:
                if (value === undefined || value === null || value === '') {
                    valueHtml = '<span class="empty">(empty)</span>';
                } else if (typeof value === 'object') {
                    valueHtml = escapeHtml(JSON.stringify(value));
                } else {
                    valueHtml = renderTextDiff(item.oldValue, item.newValue, status);
                }
        }
        
        return '<div class="compare-field ' + status + '" data-field="' + item.key + '">' +
            '<span class="compare-field-label">' + typeIcon + ' ' + escapeHtml(label) + '</span>' +
            '<div class="compare-field-value">' + valueHtml + '</div>' +
            '</div>';
    }
    
    function renderChangedFieldsList(results) {
        var changedFields = results.filter(function(r) { return r.status !== 'unchanged'; });
        var listEl = document.getElementById('changedFieldsList');
        var summaryBar = document.getElementById('changesSummaryBar');
        
        if (changedFields.length === 0) {
            summaryBar.style.display = 'none';
            return;
        }
        
        summaryBar.style.display = 'block';
        var html = '';
        
        changedFields.forEach(function(item) {
            var icon = '';
            switch(item.status) {
                case 'changed': icon = '<i class="fas fa-pen"></i>'; break;
                case 'added': icon = '<i class="fas fa-plus"></i>'; break;
                case 'removed': icon = '<i class="fas fa-minus"></i>'; break;
            }
            html += '<span class="changed-field-chip ' + item.status + '" onclick="scrollToField(\'' + item.key + '\')">' + 
                icon + formatLabel(item.key) + '</span>';
        });
        
        listEl.innerHTML = html;
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
        
        renderChangedFieldsList(comparison.results);
        
        var leftHtml = '';
        var rightHtml = '';
        
        comparison.results.forEach(function(item) {
            if (showOnlyChanges && item.status === 'unchanged') return;
            
            leftHtml += renderField(item, false);
            rightHtml += renderField(item, true);
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
    
    return { render: render, init: init, getVersionData: getVersionData, formatLabel: formatLabel };
})();

function scrollToField(fieldKey) {
    var fields = document.querySelectorAll('[data-field="' + fieldKey + '"]');
    if (fields.length > 0) {
        fields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        fields.forEach(function(f) {
            f.style.transition = 'box-shadow 0.3s';
            f.style.boxShadow = '0 0 0 3px rgba(245, 158, 11, 0.5)';
            setTimeout(function() {
                f.style.boxShadow = '';
            }, 2000);
        });
    }
}

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
