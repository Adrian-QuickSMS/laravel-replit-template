<div class="modal fade" id="rcsWizardModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content" style="height: 100vh; display: flex; flex-direction: column;">
            <div class="modal-header py-3 flex-shrink-0" style="background: var(--primary); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-magic me-2"></i>RCS Content Wizard</h5>
                <button type="button" class="btn-close btn-close-white" id="rcsWizardCloseBtn"></button>
            </div>
            <div class="modal-body p-0 flex-grow-1" style="overflow: hidden;">
                <div class="row g-0 h-100">
                    <div class="col-lg-5 p-4 d-flex flex-column align-items-center justify-content-start border-end h-100" id="rcsPreviewColumn" style="background: rgba(136, 108, 192, 0.1); overflow-y: auto;">
                        <p class="text-muted small mb-3">Live Preview</p>
                        <div id="rcsWizardPreviewContainer"></div>
                    </div>
                    <div class="col-lg-7 p-4 h-100" id="rcsConfigColumn" style="overflow-y: auto;">
                        <div class="rcs-config-panel">
                            <div id="rcsValidationErrors" class="d-none"></div>
                            
                            <div class="mb-4">
                                <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-layer-group me-2"></i>Message Type</h6>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="rcsMessageType" id="rcsTypeSingle" value="single" checked>
                                    <label class="btn btn-outline-primary" for="rcsTypeSingle">
                                        <i class="fas fa-square me-1"></i>Single Rich Card
                                    </label>
                                    <input type="radio" class="btn-check" name="rcsMessageType" id="rcsTypeCarousel" value="carousel">
                                    <label class="btn btn-outline-primary" for="rcsTypeCarousel">
                                        <i class="fas fa-images me-1"></i>Carousel
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-none mb-4" id="rcsCarouselNav">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="text-muted text-uppercase small mb-0"><i class="fas fa-th-list me-2"></i>Cards</h6>
                                    <span class="badge bg-secondary" id="rcsCardCount">1 / 10</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center" id="rcsCardTabs">
                                    <button type="button" class="btn btn-primary btn-sm rcs-card-tab active" data-card="1" onclick="selectRcsCard(1)">Card 1</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="rcsAddCardBtn" onclick="addRcsCard()">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-2">Cards display left to right in sent message order.</small>
                            </div>
                            
                            <div id="rcsCardConfig">
                                <div class="d-none mb-2" id="rcsCurrentCardLabel">
                                    <span class="badge bg-primary"><i class="fas fa-square me-1"></i>Editing: <span id="rcsCurrentCardName">Card 1</span></span>
                                </div>
                                
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-image me-2"></i>Media</h6>
                                    <div id="rcsMediaInputContainer">
                                        @include('quicksms.partials.shared-image-editor', [
                                            'editorId' => 'rcsMedia',
                                            'preset' => 'rich-card-short',
                                            'showLabel' => false,
                                            'showUrlTab' => true,
                                            'inputOnly' => true,
                                            'maxSize' => 100 * 1024 * 1024,
                                            'accept' => 'image/jpeg,image/png,image/gif'
                                        ])
                                    </div>
                                    
                                    <div id="rcsMediaPreview" class="d-none mt-3">
                                            <div class="mt-3 p-3 border rounded" id="rcsImageEditor" style="background: rgba(136, 108, 192, 0.1);">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="small text-muted text-uppercase mb-0"><i class="fas fa-crop-alt me-1"></i>Image Crop & Position</h6>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRcsMedia()">
                                                        <i class="fas fa-trash-alt me-1"></i>Remove
                                                    </button>
                                                </div>
                                                
                                                <div class="mb-3" id="rcsCardWidthSection">
                                                    <label class="form-label small mb-2">Card Width</label>
                                                    <div class="btn-group w-100" role="group">
                                                        <input type="radio" class="btn-check" name="rcsCardWidth" id="rcsCardWidthSmall" value="small">
                                                        <label class="btn btn-outline-secondary btn-sm" for="rcsCardWidthSmall">Small (180 DP)</label>
                                                        <input type="radio" class="btn-check" name="rcsCardWidth" id="rcsCardWidthMedium" value="medium" checked>
                                                        <label class="btn btn-outline-secondary btn-sm" for="rcsCardWidthMedium">Medium (296 DP)</label>
                                                    </div>
                                                    <div id="rcsCarouselWidthNotice" class="alert alert-success py-1 px-2 mt-2 d-none small">
                                                        <i class="fas fa-check-circle me-1"></i>Card width applied to all cards in this carousel.
                                                    </div>
                                                    <small class="text-muted d-block mt-1" id="rcsCarouselWidthHint">
                                                        <i class="fas fa-layer-group me-1"></i>In carousels, all cards share the same width.
                                                    </small>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small mb-2">Media Height</label>
                                                    <div class="btn-group w-100" role="group">
                                                        <input type="radio" class="btn-check" name="rcsOrientation" id="rcsOrientVertShort" value="vertical_short" checked>
                                                        <label class="btn btn-outline-secondary btn-sm" for="rcsOrientVertShort">Short (112 DP)</label>
                                                        <input type="radio" class="btn-check" name="rcsOrientation" id="rcsOrientVertMed" value="vertical_medium">
                                                        <label class="btn btn-outline-secondary btn-sm" for="rcsOrientVertMed">Medium (168 DP)</label>
                                                        <input type="radio" class="btn-check" name="rcsOrientation" id="rcsOrientVertTall" value="vertical_tall">
                                                        <label class="btn btn-outline-secondary btn-sm" for="rcsOrientVertTall" id="rcsOrientVertTallLabel">Tall (264 DP)</label>
                                                    </div>
                                                    <div id="rcsCardWidthHeightWarning" class="alert alert-info py-1 px-2 mt-2 d-none small">
                                                        <i class="fas fa-info-circle me-1"></i><span id="rcsCardWidthHeightWarningText">Tall media height is not available with Small card width.</span>
                                                    </div>
                                                    <div id="rcsCarouselHeightNotice" class="alert alert-success py-1 px-2 mt-2 d-none small">
                                                        <i class="fas fa-check-circle me-1"></i>Media height applied to all cards in this carousel.
                                                    </div>
                                                    <small class="text-muted d-block mt-1" id="rcsCarouselHeightHint">
                                                        <i class="fas fa-layer-group me-1"></i>In carousels, all cards share the same media height.
                                                    </small>
                                                    <div id="rcsSingleCardResolutionHint" class="mt-2 p-2 rounded" style="background-color: rgba(136, 108, 192, 0.08); border: 1px solid rgba(136, 108, 192, 0.2);">
                                                        <small class="d-block mb-1" style="color: #886cc0;">
                                                            <i class="fas fa-image me-1"></i><strong>Recommended resolutions (2:1 aspect ratio):</strong>
                                                        </small>
                                                        <ul class="mb-0 ps-3 small text-muted" style="font-size: 0.75rem; line-height: 1.4;">
                                                            <li><strong>Short:</strong> 1440 × 720 px <span class="text-success">(optimal)</span></li>
                                                            <li><strong>Medium:</strong> ~1080 × 720 px <span class="text-muted">(acceptable)</span></li>
                                                            <li><strong>Tall:</strong> DP-based scaling <span class="text-muted">(no fixed size)</span></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                
                                                <div class="rcs-crop-container mb-3" id="rcsCropContainer">
                                                    <div class="rcs-crop-workspace" id="rcsCropWorkspace">
                                                        <img id="rcsMediaPreviewImg" src="" alt="Media preview" draggable="false">
                                                        <div class="rcs-crop-overlay" id="rcsCropOverlay">
                                                            <div class="rcs-crop-frame" id="rcsCropFrame">
                                                                <div class="rcs-crop-crosshair rcs-crop-crosshair-h" id="rcsCrosshairH"></div>
                                                                <div class="rcs-crop-crosshair rcs-crop-crosshair-v" id="rcsCrosshairV"></div>
                                                                <div class="rcs-crop-crosshair rcs-crop-crosshair-center" id="rcsCrosshairCenter"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="rcs-crop-hint text-center mt-2">
                                                        <small class="text-muted"><i class="fas fa-hand-pointer me-1"></i>Drag image to position within frame</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label small mb-1">Zoom <span class="text-muted" id="rcsZoomValue">100%</span></label>
                                                    <input type="range" class="form-range" id="rcsZoomSlider" min="25" max="200" value="100" oninput="updateRcsCropZoom(this.value)">
                                                    <div class="d-flex justify-content-between small text-muted mt-1">
                                                        <span>25%</span>
                                                        <span>100%</span>
                                                        <span>200%</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex gap-2 mb-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="resetRcsCropPosition()">
                                                        <i class="fas fa-crosshairs me-1"></i>Center
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="resetRcsCropToFit()">
                                                        <i class="fas fa-expand me-1"></i>Fit
                                                    </button>
                                                </div>
                                                
                                                <div id="rcsImageSaveBtn" class="mb-3 d-none">
                                                    <button type="button" class="btn btn-primary btn-sm w-100" onclick="saveRcsImageEdits()">
                                                        <i class="fas fa-save me-1"></i>Save Image Changes
                                                    </button>
                                                    <small class="text-muted d-block mt-1">Changes will be saved to QuickSMS hosted URL</small>
                                                </div>
                                                
                                                <div class="pt-3 border-top">
                                                    <div class="d-flex justify-content-between align-items-center small">
                                                        <span class="text-muted">Original:</span>
                                                        <span id="rcsImageDimensions" class="badge bg-secondary">--</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center small mt-1">
                                                        <span class="text-muted">File size:</span>
                                                        <span id="rcsImageFileSize" class="badge bg-secondary">--</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-heading me-2"></i>Description</h6>
                                    <div class="position-relative border rounded">
                                        <input type="text" class="form-control form-control-sm fw-bold border-0" id="rcsDescription" 
                                            placeholder="Enter card description (bold text)" maxlength="150"
                                            oninput="updateRcsDescriptionCount()" style="padding-right: 70px;">
                                        <div class="position-absolute d-flex gap-1" style="top: 50%; right: 8px; transform: translateY(-50%); z-index: 10;">
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsPlaceholderPicker('description')" title="Insert personalisation">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsEmojiPicker('description')" title="Insert emoji">
                                                <i class="fas fa-smile"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-1">
                                        <small class="text-muted">
                                            <span id="rcsDescriptionCount">0</span> / <span class="text-warning">120</span> chars
                                        </small>
                                    </div>
                                    <div id="rcsDescriptionWarning" class="alert alert-warning py-1 px-2 mt-2 d-none small">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Description exceeds recommended 120 characters.
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-align-left me-2"></i>Text Body</h6>
                                    <div class="position-relative border rounded">
                                        <textarea class="form-control form-control-sm border-0" id="rcsTextBody" rows="4" 
                                            placeholder="Enter message body content..." maxlength="2100"
                                            oninput="updateRcsTextBodyCount()" style="padding-bottom: 40px;"></textarea>
                                        <div class="position-absolute d-flex gap-1" style="bottom: 8px; right: 8px; z-index: 10;">
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsPlaceholderPicker('textBody')" title="Insert personalisation">
                                                <i class="fas fa-user-tag"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border" onclick="openRcsEmojiPicker('textBody')" title="Insert emoji">
                                                <i class="fas fa-smile"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-1">
                                        <small class="text-muted">
                                            <span id="rcsTextBodyCount">0</span> / <span class="text-warning">2000</span> chars
                                        </small>
                                    </div>
                                    <div id="rcsTextBodyWarning" class="alert alert-warning py-1 px-2 mt-2 d-none small">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Text body exceeds recommended 2000 characters.
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <h6 class="text-muted text-uppercase small mb-3"><i class="fas fa-mouse-pointer me-2"></i>Action Buttons</h6>
                                    <div class="border rounded p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <small class="text-muted">Add up to 4 interactive buttons</small>
                                            <span class="badge bg-secondary" id="rcsButtonCount">0 / 4</span>
                                        </div>
                                        
                                        <div id="rcsButtonsList"></div>
                                        
                                        <div id="rcsAddButtonSection">
                                            <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="addRcsButton()" id="rcsAddButtonBtn">
                                                <i class="fas fa-plus me-1"></i>Add Button
                                            </button>
                                        </div>
                                        
                                        @include('quicksms.partials.rcs-button-config-modal')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2 flex-shrink-0 border-top">
                <button type="button" class="btn btn-secondary" onclick="handleRcsWizardClose()">Cancel</button>
                <button type="button" class="btn btn-primary" id="rcsApplyContentBtn" onclick="handleRcsApplyContent()" disabled>
                    <i class="fas fa-check me-1"></i>Apply RCS Content
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rcsUnsavedChangesModal" tabindex="-1" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Save image changes?</h5>
            </div>
            <div class="modal-body">
                <p>You have made changes to how the image is presented. Do you want to save?</p>
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>If you save:</strong> QuickSMS will create a unique URL on a quicksms.com domain to replace the URL you provided.
                </div>
                <div class="alert alert-secondary small mb-0">
                    <i class="fas fa-undo me-1"></i>
                    <strong>If you don't save:</strong> The image will render using the default (original URL and default presentation).
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="cancelRcsUnsavedChanges()">Cancel</button>
                <button type="button" class="btn btn-secondary" onclick="discardRcsImageEdits()">Don't Save</button>
                <button type="button" class="btn btn-primary" onclick="saveRcsImageEditsAndContinue()">
                    <i class="fas fa-save me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
