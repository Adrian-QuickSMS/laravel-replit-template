<!-- Account Structure Modal -->
<div class="modal fade" id="accountStructureModal" tabindex="-1" aria-labelledby="accountStructureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountStructureModalLabel">Account Hierarchy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <div class="col-md-5 border-end" style="max-height: 500px; overflow-y: auto;">
                        <div class="p-3">
                            <h6 class="text-muted mb-3">Hierarchy Tree</h6>
                            <div id="hierarchyTree" class="hierarchy-tree"></div>
                        </div>
                    </div>
                    <div class="col-md-7" style="max-height: 500px; overflow-y: auto;">
                        <div class="p-3" id="nodeDetailsPanel">
                            <div class="text-center text-muted py-5">
                                <p>Select a node to view details</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <span class="text-muted small me-auto">Read-only view</span>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSubAccount()">
                    <i class="fa fa-plus me-1"></i>Add Sub-account
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="inviteUser()">
                    <i class="fa fa-user-plus me-1"></i>Invite User
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.hierarchy-tree { padding-left: 0; }
.tree-node { margin-left: 1.5rem; border-left: 1px solid #e9ecef; padding-left: 0.75rem; }
.tree-item { 
    display: flex; 
    align-items: center; 
    padding: 0.5rem 0.75rem; 
    margin-bottom: 0.25rem; 
    border-radius: 0.375rem; 
    cursor: pointer; 
    transition: all 0.15s ease;
}
.tree-item:hover { background: rgba(30, 58, 95, 0.08); }
.tree-item.selected { background: rgba(30, 58, 95, 0.15); border-left: 3px solid #1e3a5f; }
.tree-item.main-account { background: rgba(30, 58, 95, 0.08); font-weight: 600; margin-bottom: 0.5rem; }
.tree-node-name { flex: 1; font-size: 0.875rem; }
.tree-node-badges { display: flex; gap: 0.25rem; }
.tree-toggle { margin-right: 0.5rem; cursor: pointer; color: #6c757d; font-size: 0.75rem; }
.tree-children { margin-top: 0.25rem; }
.tree-children.collapsed { display: none; }
</style>
