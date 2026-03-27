{{-- Bug Report Widget — Floating button + Modal --}}
{{-- Included in both quicksms.blade.php and admin.blade.php layouts --}}

<div id="bugReportWidget"
     data-user-name="{{ trim((auth()->user()->first_name ?? '') . ' ' . (auth()->user()->last_name ?? '')) }}"
     data-user-email="{{ auth()->user()->email ?? '' }}"
     data-account-id="{{ auth()->user()->tenant_id ?? session('customer_tenant_id', '') }}"
     data-account-name="{{ auth()->user()->account->company_name ?? auth()->user()->account->trading_name ?? '' }}"
     data-environment="{{ app()->environment() }}">

    {{-- Floating Bug Report Button --}}
    <button type="button" id="bugReportBtn" class="bug-report-fab" title="Report a Bug"
            data-bs-toggle="modal" data-bs-target="#bugReportModal">
        <i class="fas fa-bug"></i>
    </button>

    {{-- Bug Report Modal --}}
    <div class="modal fade" id="bugReportModal" tabindex="-1" aria-labelledby="bugReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                {{-- Header --}}
                <div class="modal-header bug-report-header">
                    <h5 class="modal-title" id="bugReportModalLabel">
                        <i class="fas fa-bug me-2"></i>Report a Bug
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- Body --}}
                <div class="modal-body">
                    <form id="bugReportForm" novalidate>
                        {{-- Section: Report Details --}}
                        <div class="bug-report-section">
                            <h6 class="bug-report-section-title">Report Details</h6>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="bugCategory" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" id="bugCategory" name="category" required>
                                        <option value="">Select category...</option>
                                        <option value="portal_bug">Portal Bug</option>
                                        <option value="ui_layout">UI / Layout Issue</option>
                                        <option value="performance">Performance Issue</option>
                                        <option value="sms_issue">SMS Issue</option>
                                        <option value="rcs_issue">RCS Issue</option>
                                        <option value="whatsapp_issue">WhatsApp Issue</option>
                                        <option value="api_webhook">API / Webhook Issue</option>
                                        <option value="reporting_billing">Reporting / Billing Issue</option>
                                        <option value="login_permissions">Login / Permissions Issue</option>
                                        <option value="feature_request">Feature Request</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a category.</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="bugSeverity" class="form-label">Severity <span class="text-danger">*</span></label>
                                    <select class="form-select" id="bugSeverity" name="severity" required>
                                        <option value="">Select severity...</option>
                                        <option value="critical">Critical — System down / data loss</option>
                                        <option value="high">High — Major feature broken</option>
                                        <option value="medium" selected>Medium — Feature impaired</option>
                                        <option value="low">Low — Minor / cosmetic</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a severity.</div>
                                </div>

                                <div class="col-12">
                                    <label for="bugTitle" class="form-label">Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="bugTitle" name="title"
                                           placeholder="Brief summary of the issue" maxlength="200" required>
                                    <div class="invalid-feedback">Please provide a title (5-200 characters).</div>
                                </div>

                                <div class="col-12">
                                    <label for="bugDescription" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="bugDescription" name="description" rows="4"
                                              placeholder="Describe the issue in detail. What happened? What did you expect?" maxlength="5000" required></textarea>
                                    <div class="invalid-feedback">Please provide a description (20-5000 characters).</div>
                                    <div class="form-text"><span id="bugDescCharCount">0</span> / 5000</div>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Screenshot --}}
                        <div class="bug-report-section">
                            <h6 class="bug-report-section-title">Screenshot (Optional)</h6>

                            <div class="bug-screenshot-zone" id="bugScreenshotZone">
                                {{-- Empty state --}}
                                <div id="bugScreenshotEmpty">
                                    <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                    <p class="mb-1">Drag & drop, paste (Ctrl+V), or click to upload</p>
                                    <p class="text-muted small mb-0">PNG, JPG, GIF, WebP — Max 5MB</p>
                                    <input type="file" id="bugScreenshotInput" accept="image/png,image/jpeg,image/gif,image/webp"
                                           class="d-none">
                                </div>

                                {{-- Preview state --}}
                                <div id="bugScreenshotPreview" class="d-none">
                                    <div class="bug-screenshot-preview-wrap">
                                        <img id="bugScreenshotImg" src="" alt="Screenshot preview">
                                        <div class="bug-screenshot-actions">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="bugAnnotateBtn"
                                                    title="Annotate screenshot">
                                                <i class="fas fa-pencil-alt me-1"></i>Annotate
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" id="bugRemoveScreenshotBtn"
                                                    title="Remove screenshot">
                                                <i class="fas fa-times me-1"></i>Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Annotation Canvas (hidden until user clicks Annotate) --}}
                            <div id="bugAnnotationContainer" class="d-none">
                                <div class="bug-annotation-toolbar">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary active" data-tool="freehand" title="Freehand draw">
                                            <i class="fas fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" data-tool="arrow" title="Arrow">
                                            <i class="fas fa-long-arrow-alt-right"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" data-tool="rectangle" title="Rectangle">
                                            <i class="far fa-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" data-tool="text" title="Text label">
                                            <i class="fas fa-font"></i>
                                        </button>
                                    </div>

                                    <input type="color" id="bugAnnotationColor" value="#ff0000" class="bug-annotation-color" title="Annotation colour">

                                    <div class="btn-group btn-group-sm ms-2" role="group">
                                        <button type="button" class="btn btn-outline-secondary" id="bugAnnotationUndo" title="Undo">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="bugAnnotationClear" title="Clear all">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-primary ms-auto" id="bugAnnotationDone">
                                        <i class="fas fa-check me-1"></i>Done
                                    </button>
                                </div>

                                <div class="bug-annotation-canvas-wrap">
                                    <canvas id="bugAnnotationCanvas"></canvas>
                                </div>
                            </div>
                        </div>

                        {{-- Section: Technical Context --}}
                        <div class="bug-report-section">
                            <h6 class="bug-report-section-title">
                                Technical Context
                                <small class="text-muted">(auto-captured)</small>
                            </h6>

                            <div class="bug-metadata-grid">
                                <div class="bug-metadata-item">
                                    <span class="bug-metadata-label">Page</span>
                                    <span class="bug-metadata-value" id="bugMetaPage">-</span>
                                </div>
                                <div class="bug-metadata-item">
                                    <span class="bug-metadata-label">Browser</span>
                                    <span class="bug-metadata-value" id="bugMetaBrowser">-</span>
                                </div>
                                <div class="bug-metadata-item">
                                    <span class="bug-metadata-label">Environment</span>
                                    <span class="bug-metadata-value" id="bugMetaEnv">-</span>
                                </div>
                                <div class="bug-metadata-item">
                                    <span class="bug-metadata-label">Account</span>
                                    <span class="bug-metadata-value" id="bugMetaAccount">-</span>
                                </div>
                                <div class="bug-metadata-item">
                                    <span class="bug-metadata-label">Viewport</span>
                                    <span class="bug-metadata-value" id="bugMetaViewport">-</span>
                                </div>
                                <div class="bug-metadata-item">
                                    <span class="bug-metadata-label">Timestamp</span>
                                    <span class="bug-metadata-value" id="bugMetaTimestamp">-</span>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Success state --}}
                    <div id="bugReportSuccess" class="d-none text-center py-4">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>Bug Report Submitted</h5>
                        <p class="text-muted mb-1">Reference: <strong id="bugReportRef">-</strong></p>
                        <p class="text-muted small" id="bugReportAutoFixMsg"></p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer" id="bugReportFooter">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn bug-report-submit-btn" id="bugReportSubmitBtn">
                        <span id="bugSubmitText"><i class="fas fa-paper-plane me-1"></i>Submit Report</span>
                        <span id="bugSubmitLoading" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>Submitting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
