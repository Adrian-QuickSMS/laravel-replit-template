{{-- Inbox: Right sidebar — contact details (collapsible) --}}
<div class="contact-panel d-none" id="contactPanel">
    <div class="contact-panel__header">
        <h6 class="mb-0">Contact Details</h6>
        <button class="btn btn-sm" id="closeContactPanel" title="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="contact-panel__body">
        {{-- Avatar --}}
        <div class="contact-panel__avatar" id="contactAvatar"></div>
        <h6 class="contact-panel__name" id="contactName"></h6>
        <span class="text-muted" id="contactPhone"></span>

        {{-- Info list --}}
        <div class="contact-panel__info mt-3">
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

        {{-- Tags --}}
        <div class="contact-panel__section mt-3">
            <h6 class="contact-panel__section-title">
                Tags
                <span class="badge bg-secondary badge-sm ms-1" id="contactTagCount" style="font-size: 0.625rem;">0</span>
            </h6>
            <div id="contactTags" class="contact-panel__tags">
                <span class="text-muted small">No tags</span>
            </div>
            <div class="mt-2">
                <div class="input-group input-group-sm d-none" id="addTagForm">
                    <input type="text" class="form-control" id="newTagInput" placeholder="Tag name..." maxlength="50">
                    <button class="btn btn-outline-primary" id="saveTagBtn" title="Add tag">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="btn btn-outline-secondary" id="cancelTagBtn" title="Cancel">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="showAddTagBtn">
                    <i class="fas fa-tag me-1"></i> Add tag
                </button>
            </div>
        </div>

        {{-- Notes --}}
        <div class="contact-panel__section mt-3">
            <h6 class="contact-panel__section-title">
                Notes
                <span class="badge bg-secondary badge-sm ms-1" id="contactNoteCount" style="font-size: 0.625rem;">0</span>
            </h6>
            <div id="contactNotes" class="contact-panel__notes">
                <span class="text-muted small">No notes yet</span>
            </div>
            <div class="mt-2">
                <div class="d-none" id="addNoteForm">
                    <textarea class="form-control form-control-sm mb-2" id="newNoteInput" rows="3" placeholder="Write a note..." maxlength="500"></textarea>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-primary flex-fill" id="saveNoteBtn">
                            <i class="fas fa-check me-1"></i>Save
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="cancelNoteBtn">Cancel</button>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="showAddNoteBtn">
                    <i class="fas fa-sticky-note me-1"></i> Add note
                </button>
            </div>
        </div>

        {{-- Lists --}}
        <div class="contact-panel__section mt-3">
            <h6 class="contact-panel__section-title">Lists</h6>
            <div id="contactLists" class="contact-panel__lists">
                <span class="text-muted small">No lists</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="contact-panel__actions mt-4">
            <a class="btn btn-sm btn-outline-primary w-100 mb-2" id="viewInContactsBtn" href="/contacts/all-contacts">
                <i class="far fa-address-book me-1"></i> View in Contacts
            </a>
        </div>
    </div>
</div>
