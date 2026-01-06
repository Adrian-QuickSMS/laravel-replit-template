<div class="modal fade" id="rcsButtonConfigModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="fas fa-mouse-pointer me-2"></i>Configure Button</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small">Button Label <span class="text-danger">*</span></label>
                    <div class="position-relative border rounded">
                        <input type="text" class="form-control form-control-sm border-0" id="rcsButtonLabel" maxlength="25" placeholder="e.g., Learn More" oninput="updateRcsButtonLabelCount()" style="padding-right: 70px;">
                        <div class="position-absolute d-flex gap-1" style="top: 50%; right: 8px; transform: translateY(-50%); z-index: 10;">
                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldPlaceholder('rcsButtonLabel')" title="Insert personalisation">
                                <i class="fas fa-user-tag"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldEmoji('rcsButtonLabel')" title="Insert emoji">
                                <i class="fas fa-smile"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small id="rcsButtonLabelError" class="text-danger d-none">Label is required</small>
                        <small class="text-muted ms-auto"><span id="rcsButtonLabelCount">0</span>/25</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small">Button Type <span class="text-danger">*</span></label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="rcsButtonType" id="rcsButtonTypeUrl" value="url" checked>
                        <label class="btn btn-outline-secondary btn-sm" for="rcsButtonTypeUrl">
                            <i class="fas fa-link me-1"></i>URL
                        </label>
                        <input type="radio" class="btn-check" name="rcsButtonType" id="rcsButtonTypePhone" value="phone">
                        <label class="btn btn-outline-secondary btn-sm" for="rcsButtonTypePhone">
                            <i class="fas fa-phone me-1"></i>Call
                        </label>
                        <input type="radio" class="btn-check" name="rcsButtonType" id="rcsButtonTypeCalendar" value="calendar">
                        <label class="btn btn-outline-secondary btn-sm" for="rcsButtonTypeCalendar">
                            <i class="fas fa-calendar-plus me-1"></i>Calendar
                        </label>
                    </div>
                </div>
                
                <div id="rcsButtonUrlConfig">
                    <div class="mb-3">
                        <label class="form-label small">URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control form-control-sm" id="rcsButtonUrl" placeholder="https://example.com">
                        <small id="rcsButtonUrlError" class="text-danger d-none">Valid URL is required</small>
                    </div>
                </div>
                
                <div id="rcsButtonPhoneConfig" class="d-none">
                    <div class="mb-3">
                        <label class="form-label small">Phone Number <span class="text-danger">*</span></label>
                        <div class="position-relative border rounded">
                            <input type="tel" class="form-control form-control-sm border-0" id="rcsButtonPhone" placeholder="+44 1234 567890" oninput="validateRcsPhoneNoEmoji()" style="padding-right: 40px;">
                            <div class="position-absolute d-flex gap-1" style="top: 50%; right: 8px; transform: translateY(-50%); z-index: 10;">
                                <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldPlaceholder('rcsButtonPhone')" title="Insert personalisation">
                                    <i class="fas fa-user-tag"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border disabled" title="Emoji not allowed in phone numbers" style="opacity: 0.5; cursor: not-allowed;">
                                    <i class="fas fa-smile"></i>
                                </button>
                            </div>
                        </div>
                        <small id="rcsButtonPhoneError" class="text-danger d-none">Valid phone number required (e.g., +44...)</small>
                        <small id="rcsButtonPhoneEmojiError" class="text-danger d-none"><i class="fas fa-ban me-1"></i>Emoji not allowed in phone numbers</small>
                    </div>
                </div>
                
                <div id="rcsButtonCalendarConfig" class="d-none">
                    <div class="mb-3">
                        <label class="form-label small">Event Title <span class="text-danger">*</span></label>
                        <div class="position-relative border rounded">
                            <input type="text" class="form-control form-control-sm border-0" id="rcsButtonEventTitle" maxlength="100" placeholder="Meeting with QuickSMS" style="padding-right: 70px;">
                            <div class="position-absolute d-flex gap-1" style="top: 50%; right: 8px; transform: translateY(-50%); z-index: 10;">
                                <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldPlaceholder('rcsButtonEventTitle')" title="Insert personalisation">
                                    <i class="fas fa-user-tag"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldEmoji('rcsButtonEventTitle')" title="Insert emoji">
                                    <i class="fas fa-smile"></i>
                                </button>
                            </div>
                        </div>
                        <small id="rcsButtonEventTitleError" class="text-danger d-none">Event title is required</small>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small">Start Date/Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control form-control-sm" id="rcsButtonEventStart">
                            <small id="rcsButtonEventStartError" class="text-danger d-none">Start time required</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label small">End Date/Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control form-control-sm" id="rcsButtonEventEnd">
                            <small id="rcsButtonEventEndError" class="text-danger d-none">End time required</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Event Description</label>
                        <div class="position-relative border rounded">
                            <textarea class="form-control form-control-sm border-0" id="rcsButtonEventDesc" rows="2" maxlength="500" placeholder="Optional description..." style="padding-bottom: 35px;"></textarea>
                            <div class="position-absolute d-flex gap-1" style="bottom: 6px; right: 8px; z-index: 10;">
                                <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldPlaceholder('rcsButtonEventDesc')" title="Insert personalisation">
                                    <i class="fas fa-user-tag"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-light border" onclick="openRcsButtonFieldEmoji('rcsButtonEventDesc')" title="Insert emoji">
                                    <i class="fas fa-smile"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="saveRcsButton()">
                    <i class="fas fa-check me-1"></i>Save Button
                </button>
            </div>
        </div>
    </div>
</div>
