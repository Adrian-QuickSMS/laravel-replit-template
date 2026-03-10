{{-- Inbox: Contact details modal --}}
<div class="modal fade" id="contactPanelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title"><i class="far fa-address-card me-2"></i>Contact Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="contact-panel__avatar mx-auto" id="contactAvatar"></div>
                    <h6 class="contact-panel__name mt-2 mb-1" id="contactName"></h6>
                    <span class="text-muted" id="contactPhone"></span>
                </div>

                <div class="contact-panel__info">
                    <div class="contact-panel__row">
                        <span class="contact-panel__label">Channel</span>
                        <span class="contact-panel__value" id="contactChannel"></span>
                    </div>
                    <div class="contact-panel__row">
                        <span class="contact-panel__label">Source</span>
                        <span class="contact-panel__value" id="contactSource"></span>
                    </div>
                    <div class="contact-panel__row">
                        <span class="contact-panel__label">First Contact</span>
                        <span class="contact-panel__value" id="contactFirstDate"></span>
                    </div>
                </div>

                <div class="contact-panel__section mt-3">
                    <h6 class="contact-panel__section-title">
                        Tags
                        <span class="badge rounded-pill ms-1" id="contactTagCount" style="font-size: 0.625rem; background-color: rgba(136, 108, 192, 0.15); color: #886CC0;">0</span>
                    </h6>
                    <div id="contactTags" class="contact-panel__tags">
                        <span class="text-muted small">No tags</span>
                    </div>
                    <div class="mt-2">
                        <div class="d-none" id="addTagForm">
                            <select class="form-select form-select-sm mb-2" id="existingTagSelect">
                                <option value="">Select existing tag...</option>
                            </select>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="newTagInput" placeholder="Or type new tag..." maxlength="50">
                                <button class="btn btn-outline-primary" id="saveTagBtn" title="Add tag">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button class="btn btn-outline-secondary" id="cancelTagBtn" title="Cancel">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" id="showAddTagBtn">
                            <i class="fas fa-tag me-1"></i> Add tag
                        </button>
                    </div>
                </div>

                <div class="contact-panel__section mt-3">
                    <h6 class="contact-panel__section-title">
                        Notes
                        <span class="badge rounded-pill ms-1" id="contactNoteCount" style="font-size: 0.625rem; background-color: rgba(136, 108, 192, 0.15); color: #886CC0;">0</span>
                    </h6>
                    <div id="contactNotes" class="contact-panel__notes">
                        <span class="text-muted small">No notes yet</span>
                    </div>
                    <div class="mt-2">
                        <div class="d-none" id="addNoteForm">
                            <textarea class="form-control form-control-sm mb-2" id="newNoteInput" rows="3" placeholder="Write a note..." maxlength="500"></textarea>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm flex-fill" id="saveNoteBtn" style="background-color: #886CC0; color: #fff;">
                                    <i class="fas fa-check me-1"></i>Save
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" id="cancelNoteBtn">Cancel</button>
                            </div>
                        </div>
                        <button class="btn btn-sm" id="showAddNoteBtn" style="color: #886CC0; border-color: #886CC0;">
                            <i class="fas fa-sticky-note me-1"></i> Add note
                        </button>
                    </div>
                </div>

                <div class="contact-panel__section mt-3">
                    <h6 class="contact-panel__section-title">Lists</h6>
                    <div id="contactLists" class="contact-panel__lists mb-2">
                        <span class="text-muted small">No lists</span>
                    </div>
                    <div class="d-none" id="addToListForm">
                        <div class="input-group input-group-sm">
                            <select class="form-select form-select-sm" id="addToListSelect">
                                <option value="">Select a list...</option>
                            </select>
                            <button class="btn btn-outline-primary" id="confirmAddToListBtn" title="Add to list">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="cancelAddToListBtn" title="Cancel">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" id="showAddToListBtn">
                        <i class="fas fa-list me-1"></i> Add to list
                    </button>
                </div>
            </div>
            <div class="modal-footer py-2">
                <a class="btn btn-sm btn-outline-primary" id="viewInContactsBtn" href="/contacts/all">
                    <i class="far fa-address-book me-1"></i> View in Contacts
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
