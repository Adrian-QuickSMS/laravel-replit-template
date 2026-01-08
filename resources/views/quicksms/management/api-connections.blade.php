@extends('layouts.quicksms')

@section('title', 'API Connections')

@push('styles')
<style>
.api-connections-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.api-connections-header h2 {
    margin: 0;
    font-weight: 600;
}
.api-connections-header p {
    margin: 0;
    color: #6c757d;
}
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
}
.empty-state-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(136, 108, 192, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}
.empty-state-icon i {
    font-size: 2rem;
    color: var(--primary);
}
.empty-state h4 {
    margin-bottom: 0.5rem;
    color: #343a40;
}
.empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
}
.api-table-container {
    background: #fff;
    border-radius: 0.75rem;
    border: 1px solid #e9ecef;
    overflow-x: auto;
}
.api-table {
    width: 100%;
    margin: 0;
    min-width: 1400px;
    table-layout: fixed;
}
.api-table thead th {
    background: #f8f9fa;
    padding: 0.75rem 0.5rem;
    font-weight: 600;
    font-size: 0.8rem;
    color: #495057;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    white-space: nowrap;
    user-select: none;
}
.api-table thead th:first-child { width: 14%; }
.api-table thead th:nth-child(2) { width: 10%; }
.api-table thead th:nth-child(3) { width: 9%; }
.api-table thead th:nth-child(4) { width: 7%; }
.api-table thead th:nth-child(5) { width: 8%; }
.api-table thead th:nth-child(6) { width: 7%; }
.api-table thead th:nth-child(7) { width: 14%; }
.api-table thead th:nth-child(8) { width: 7%; }
.api-table thead th:nth-child(9) { width: 9%; }
.api-table thead th:nth-child(10) { width: 10%; }
.api-table thead th:last-child { 
    width: 5%; 
    position: sticky;
    right: 0;
    background: #f8f9fa;
    z-index: 2;
    cursor: default;
}
.api-table thead th:hover {
    background: #e9ecef;
}
.api-table thead th:last-child:hover {
    background: #f8f9fa;
}
.api-table thead th .sort-icon {
    margin-left: 0.25rem;
    opacity: 0.4;
}
.api-table thead th.sorted .sort-icon {
    opacity: 1;
    color: var(--primary);
}
.api-table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f5;
    font-size: 0.85rem;
}
.api-table tbody td:last-child {
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 1;
    box-shadow: -2px 0 4px rgba(0,0,0,0.05);
}
.api-table tbody tr:last-child td {
    border-bottom: none;
}
.api-table tbody tr:hover td {
    background: #f8f9fa;
}
.api-table tbody tr:hover td:last-child {
    background: #f8f9fa;
}
.api-name {
    font-weight: 500;
    color: #343a40;
}
.api-description {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.15rem;
}
.badge-bulk {
    background: rgba(48, 101, 208, 0.15);
    color: #3065D0;
}
.badge-campaign {
    background: rgba(136, 108, 192, 0.15);
    color: #886CC0;
}
.badge-integration {
    background: rgba(255, 191, 0, 0.15);
    color: #cc9900;
}
.badge-test {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.badge-live-env {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-live-status {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-suspended {
    background: rgba(220, 53, 69, 0.15);
    color: #dc3545;
}
.badge-on {
    background: rgba(28, 187, 140, 0.15);
    color: #1cbb8c;
}
.badge-off {
    background: rgba(108, 117, 125, 0.15);
    color: #6c757d;
}
.search-filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    gap: 1rem;
    flex-wrap: wrap;
}
.search-box {
    flex: 1;
    max-width: 300px;
    min-width: 200px;
}
.filters-group {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}
.action-menu-btn {
    background: transparent;
    border: none;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #6c757d;
}
.action-menu-btn:hover {
    color: var(--primary);
}
.archived-row {
    opacity: 0.6;
    background-color: #f8f9fa;
}
.archived-row:hover {
    opacity: 0.8;
}
.copy-btn {
    background: transparent;
    border: none;
    padding: 0.15rem 0.35rem;
    cursor: pointer;
    color: #6c757d;
    font-size: 0.75rem;
}
.copy-btn:hover {
    color: var(--primary);
}
.base-url-cell {
    font-family: monospace;
    font-size: 0.75rem;
    color: #495057;
}
.table-footer {
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.pagination-info {
    font-size: 0.85rem;
    color: #6c757d;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="api-connections-header">
        <div>
            <h2>API Connections</h2>
            <p>Manage your API keys and integrations for accessing QuickSMS services.</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="createApiConnection()">
                <i class="fas fa-plus me-2"></i>Create API Connection
            </button>
        </div>
    </div>
    
    <div class="api-table-container">
        <div class="search-filter-bar">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Search API connections...">
                </div>
            </div>
            <div class="filters-group">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="showArchivedToggle">
                    <label class="form-check-label" for="showArchivedToggle">Show Archived</label>
                </div>
            </div>
        </div>
        
        <table class="api-table" id="apiConnectionsTable">
            <thead>
                <tr>
                    <th data-sort="name">API Name <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="subAccount">Sub-Account <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="type">Type <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="environment">Environment <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="authType">Auth Type <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="status">Status <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="baseUrl">Dedicated Base URL <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="ipAllowList">IP Allow List <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="createdDate">Created Date <i class="fas fa-sort sort-icon"></i></th>
                    <th data-sort="lastUsed">Last Used <i class="fas fa-sort sort-icon"></i></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="apiConnectionsBody">
            </tbody>
        </table>
        
        <div class="table-footer">
            <div class="pagination-info">
                Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> connections
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var apiConnections = [
        {
            id: 1,
            name: 'Production Bulk Sender',
            description: 'Main production API for bulk messaging',
            subAccount: 'Main Account',
            type: 'bulk',
            integrationName: null,
            environment: 'live',
            authType: 'API Key',
            status: 'live',
            baseUrl: 'https://api.quicksms.io/v1/bulk/prod-001',
            ipAllowList: true,
            createdDate: '2024-08-15',
            lastUsed: '2025-01-08 14:32:45',
            archived: false
        },
        {
            id: 2,
            name: 'Test Bulk API',
            description: 'Testing environment for bulk operations',
            subAccount: 'Development',
            type: 'bulk',
            integrationName: null,
            environment: 'test',
            authType: 'API Key',
            status: 'live',
            baseUrl: 'https://sandbox.quicksms.io/v1/bulk/test-001',
            ipAllowList: false,
            createdDate: '2024-09-20',
            lastUsed: '2025-01-07 09:15:22',
            archived: false
        },
        {
            id: 3,
            name: 'Campaign Manager API',
            description: 'API for campaign scheduling and management',
            subAccount: 'Marketing',
            type: 'campaign',
            integrationName: null,
            environment: 'live',
            authType: 'Basic Auth',
            status: 'live',
            baseUrl: 'https://api.quicksms.io/v1/campaigns/mkt-001',
            ipAllowList: true,
            createdDate: '2024-06-10',
            lastUsed: '2025-01-08 11:45:18',
            archived: false
        },
        {
            id: 4,
            name: 'Campaign Testing',
            description: null,
            subAccount: 'Development',
            type: 'campaign',
            integrationName: null,
            environment: 'test',
            authType: 'API Key',
            status: 'suspended',
            baseUrl: 'https://sandbox.quicksms.io/v1/campaigns/test-002',
            ipAllowList: false,
            createdDate: '2024-10-05',
            lastUsed: '2024-12-15 16:30:00',
            archived: false
        },
        {
            id: 5,
            name: 'Salesforce Integration',
            description: 'CRM sync for customer messaging',
            subAccount: 'Main Account',
            type: 'integration',
            integrationName: 'Salesforce CRM',
            environment: 'live',
            authType: 'OAuth',
            status: 'live',
            baseUrl: 'https://api.quicksms.io/v1/integrations/sf-001',
            ipAllowList: true,
            createdDate: '2024-03-22',
            lastUsed: '2025-01-08 15:01:33',
            archived: false
        },
        {
            id: 6,
            name: 'HubSpot Connector',
            description: 'Marketing automation integration',
            subAccount: 'Marketing',
            type: 'integration',
            integrationName: 'HubSpot',
            environment: 'test',
            authType: 'API Key',
            status: 'live',
            baseUrl: 'https://sandbox.quicksms.io/v1/integrations/hs-test',
            ipAllowList: false,
            createdDate: '2024-11-18',
            lastUsed: '2025-01-06 10:22:14',
            archived: false
        },
        {
            id: 7,
            name: 'Legacy Bulk API',
            description: 'Deprecated - migrated to new API',
            subAccount: 'Main Account',
            type: 'bulk',
            integrationName: null,
            environment: 'live',
            authType: 'API Key',
            status: 'suspended',
            baseUrl: 'https://api.quicksms.io/v1/bulk/legacy-001',
            ipAllowList: true,
            createdDate: '2023-05-10',
            lastUsed: '2024-06-30 08:00:00',
            archived: true
        },
        {
            id: 8,
            name: 'Old Campaign API',
            description: 'Archived campaign connection',
            subAccount: 'Development',
            type: 'campaign',
            integrationName: null,
            environment: 'test',
            authType: 'Basic Auth',
            status: 'suspended',
            baseUrl: 'https://sandbox.quicksms.io/v1/campaigns/old-001',
            ipAllowList: false,
            createdDate: '2023-08-15',
            lastUsed: '2024-01-20 12:00:00',
            archived: true
        }
    ];
    
    var currentSort = { column: 'name', direction: 'asc' };
    var showArchived = false;
    var searchTerm = '';
    
    function getTypeBadgeClass(type) {
        switch(type) {
            case 'bulk': return 'badge-bulk';
            case 'campaign': return 'badge-campaign';
            case 'integration': return 'badge-integration';
            default: return 'badge-bulk';
        }
    }
    
    function getTypeLabel(type) {
        switch(type) {
            case 'bulk': return 'Bulk API';
            case 'campaign': return 'Campaign API';
            case 'integration': return 'Integration';
            default: return type;
        }
    }
    
    function getEnvironmentBadgeClass(env) {
        return env === 'live' ? 'badge-live-env' : 'badge-test';
    }
    
    function getStatusBadgeClass(status) {
        return status === 'live' ? 'badge-live-status' : 'badge-suspended';
    }
    
    function getIpAllowBadgeClass(enabled) {
        return enabled ? 'badge-on' : 'badge-off';
    }
    
    function formatDate(dateStr) {
        var date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    
    function formatDateTime(dateTimeStr) {
        var date = new Date(dateTimeStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + 
               ' ' + date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(function() {
            var originalHtml = $(btn).html();
            $(btn).html('<i class="fas fa-check"></i>');
            setTimeout(function() {
                $(btn).html(originalHtml);
            }, 1500);
        });
    }
    
    function renderTable() {
        var filtered = apiConnections.filter(function(conn) {
            if (!showArchived && conn.archived) return false;
            if (searchTerm) {
                var search = searchTerm.toLowerCase();
                return conn.name.toLowerCase().includes(search) ||
                       (conn.description && conn.description.toLowerCase().includes(search)) ||
                       conn.subAccount.toLowerCase().includes(search) ||
                       conn.baseUrl.toLowerCase().includes(search);
            }
            return true;
        });
        
        filtered.sort(function(a, b) {
            var aVal = a[currentSort.column] || '';
            var bVal = b[currentSort.column] || '';
            
            if (typeof aVal === 'string') aVal = aVal.toLowerCase();
            if (typeof bVal === 'string') bVal = bVal.toLowerCase();
            
            if (aVal < bVal) return currentSort.direction === 'asc' ? -1 : 1;
            if (aVal > bVal) return currentSort.direction === 'asc' ? 1 : -1;
            return 0;
        });
        
        var html = '';
        filtered.forEach(function(conn) {
            var rowClass = conn.archived ? 'archived-row' : '';
            html += '<tr class="' + rowClass + '" data-id="' + conn.id + '">';
            
            html += '<td>';
            html += '<div class="api-name">' + conn.name + '</div>';
            if (conn.description) {
                html += '<div class="api-description">' + conn.description + '</div>';
            }
            html += '</td>';
            
            html += '<td>' + conn.subAccount + '</td>';
            
            html += '<td>';
            var typeHtml = '<span class="badge rounded-pill ' + getTypeBadgeClass(conn.type) + '"';
            if (conn.type === 'integration' && conn.integrationName) {
                typeHtml += ' title="' + conn.integrationName + '" data-bs-toggle="tooltip"';
            }
            typeHtml += '>' + getTypeLabel(conn.type) + '</span>';
            html += typeHtml;
            html += '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getEnvironmentBadgeClass(conn.environment) + '">' + 
                    (conn.environment === 'live' ? 'Live' : 'Test') + '</span></td>';
            
            html += '<td>' + conn.authType + '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getStatusBadgeClass(conn.status) + '">' + 
                    (conn.status === 'live' ? 'Live' : 'Suspended') + '</span></td>';
            
            html += '<td>';
            html += '<span class="base-url-cell">' + conn.baseUrl + '</span>';
            html += '<button class="copy-btn ms-1" onclick="copyToClipboard(\'' + conn.baseUrl + '\', this)" title="Copy URL">';
            html += '<i class="fas fa-copy"></i>';
            html += '</button>';
            html += '</td>';
            
            html += '<td><span class="badge rounded-pill ' + getIpAllowBadgeClass(conn.ipAllowList) + '">' + 
                    (conn.ipAllowList ? 'On' : 'Off') + '</span></td>';
            
            html += '<td>' + formatDate(conn.createdDate) + '</td>';
            
            html += '<td>' + formatDateTime(conn.lastUsed) + '</td>';
            
            html += '<td>';
            html += '<div class="dropdown">';
            html += '<button class="action-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
            html += '<i class="fas fa-ellipsis-v"></i>';
            html += '</button>';
            html += '<ul class="dropdown-menu dropdown-menu-end">';
            html += '<li><a class="dropdown-item" href="#" onclick="viewConnection(' + conn.id + '); return false;"><i class="fas fa-eye me-2"></i>View</a></li>';
            html += '<li><a class="dropdown-item" href="#" onclick="editConnection(' + conn.id + '); return false;"><i class="fas fa-edit me-2"></i>Edit</a></li>';
            html += '<li><a class="dropdown-item" href="#" onclick="regenerateKey(' + conn.id + '); return false;"><i class="fas fa-sync-alt me-2"></i>Regenerate Key</a></li>';
            html += '<li><hr class="dropdown-divider"></li>';
            if (conn.status === 'live') {
                html += '<li><a class="dropdown-item text-warning" href="#" onclick="suspendConnection(' + conn.id + '); return false;"><i class="fas fa-pause me-2"></i>Suspend</a></li>';
            } else {
                html += '<li><a class="dropdown-item text-success" href="#" onclick="activateConnection(' + conn.id + '); return false;"><i class="fas fa-play me-2"></i>Activate</a></li>';
            }
            if (!conn.archived) {
                html += '<li><a class="dropdown-item text-danger" href="#" onclick="archiveConnection(' + conn.id + '); return false;"><i class="fas fa-archive me-2"></i>Archive</a></li>';
            } else {
                html += '<li><a class="dropdown-item text-primary" href="#" onclick="restoreConnection(' + conn.id + '); return false;"><i class="fas fa-undo me-2"></i>Restore</a></li>';
            }
            html += '</ul>';
            html += '</div>';
            html += '</td>';
            
            html += '</tr>';
        });
        
        $('#apiConnectionsBody').html(html);
        $('#showingCount').text(filtered.length);
        $('#totalCount').text(showArchived ? apiConnections.length : apiConnections.filter(c => !c.archived).length);
        
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        $('.api-table thead th').removeClass('sorted');
        $('.api-table thead th[data-sort="' + currentSort.column + '"]').addClass('sorted');
    }
    
    window.copyToClipboard = copyToClipboard;
    
    $('.api-table thead th[data-sort]').on('click', function() {
        var column = $(this).data('sort');
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }
        renderTable();
    });
    
    $('#showArchivedToggle').on('change', function() {
        showArchived = $(this).is(':checked');
        renderTable();
    });
    
    $('#searchInput').on('input', function() {
        searchTerm = $(this).val();
        renderTable();
    });
    
    window.createApiConnection = function() {
        alert('Create API Connection - TODO: Implement modal/wizard');
    };
    
    window.viewConnection = function(id) {
        alert('View Connection ID: ' + id + ' - TODO: Implement view modal');
    };
    
    window.editConnection = function(id) {
        alert('Edit Connection ID: ' + id + ' - TODO: Implement edit modal');
    };
    
    window.regenerateKey = function(id) {
        if (confirm('Are you sure you want to regenerate the API key? The current key will be invalidated immediately.')) {
            alert('Regenerate Key for ID: ' + id + ' - TODO: Implement API call');
        }
    };
    
    window.suspendConnection = function(id) {
        if (confirm('Are you sure you want to suspend this API connection?')) {
            alert('Suspend Connection ID: ' + id + ' - TODO: Implement API call');
        }
    };
    
    window.activateConnection = function(id) {
        alert('Activate Connection ID: ' + id + ' - TODO: Implement API call');
    };
    
    window.archiveConnection = function(id) {
        if (confirm('Are you sure you want to archive this API connection?')) {
            alert('Archive Connection ID: ' + id + ' - TODO: Implement API call');
        }
    };
    
    window.restoreConnection = function(id) {
        alert('Restore Connection ID: ' + id + ' - TODO: Implement API call');
    };
    
    renderTable();
});
</script>
@endpush
