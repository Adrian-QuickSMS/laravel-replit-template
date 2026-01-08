{{--
    Shared Image Editor Component
    
    Usage:
    @include('quicksms.partials.shared-image-editor', [
        'editorId' => 'logoEditor',
        'preset' => 'agent-logo',           // or custom config
        'label' => 'Upload Logo',
        'accept' => 'image/png,image/jpeg',
        'maxSize' => 2 * 1024 * 1024,       // 2MB
        'required' => true,
        'showUrlTab' => true,               // Show URL/Upload toggle
        'browseLabel' => 'Browse files'     // Customize browse button text
    ])
    
    Presets available:
    - agent-logo (1:1, 224x224)
    - agent-hero (16:9, 1440x810)
    - rich-card-short, rich-card-medium, rich-card-tall
    - carousel-small-short, carousel-small-medium
    - carousel-medium-short, carousel-medium-medium, carousel-medium-tall
    
    JavaScript API (exposed on window):
    - {editorId}Instance: The SharedImageEditor instance
    - {editorId}GetCropData(): Returns current crop data
    - {editorId}LoadImage(src, callback): Load image from URL
    - {editorId}SetCropData(data): Restore saved crop state
    - {editorId}GenerateCroppedImage(callback): Export cropped image as data URL
    - {editorId}SetPreset(presetName): Change aspect ratio preset
    
    Callbacks (set on window before including):
    - on{editorId}Change(data): Called on crop/zoom changes
    - on{editorId}Remove(): Called when image is removed
--}}

@php
    $editorId = $editorId ?? 'imageEditor';
    $preset = $preset ?? 'agent-logo';
    $label = $label ?? 'Upload Image';
    $accept = $accept ?? 'image/png,image/jpeg';
    $maxSize = $maxSize ?? 2 * 1024 * 1024;
    $required = $required ?? false;
    $helpText = $helpText ?? null;
    $showUrlTab = $showUrlTab ?? false;
    $browseLabel = $browseLabel ?? 'Browse files';
@endphp

@once
@push('styles')
<style>
.sie-editor-wrapper {
    padding: 1rem;
}
.sie-tab-content {
    display: none;
}
.sie-tab-content.active {
    display: block;
}
</style>
@endpush
@endonce

<div class="sie-component" data-editor-id="{{ $editorId }}" data-preset="{{ $preset }}" data-max-size="{{ $maxSize }}" data-show-url-tab="{{ $showUrlTab ? 'true' : 'false' }}">
    <label class="form-label">{{ $label }}@if($required)<span class="text-danger">*</span>@endif</label>
    
    @if($helpText)
    <p class="text-muted small mb-2">{{ $helpText }}</p>
    @endif
    
    <div class="border rounded p-3 mb-3" id="{{ $editorId }}UploadZone">
        <input type="file" 
               class="d-none" 
               id="{{ $editorId }}FileInput" 
               accept="{{ $accept }}"
               data-max-size="{{ $maxSize }}">
        
        <div id="{{ $editorId }}UploadPrompt">
            @if($showUrlTab)
            <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                <input type="radio" class="btn-check" name="{{ $editorId }}Source" id="{{ $editorId }}SourceUrl" value="url" checked>
                <label class="btn btn-outline-secondary" for="{{ $editorId }}SourceUrl">
                    <i class="fas fa-link me-1"></i>URL
                </label>
                <input type="radio" class="btn-check" name="{{ $editorId }}Source" id="{{ $editorId }}SourceUpload" value="upload">
                <label class="btn btn-outline-secondary" for="{{ $editorId }}SourceUpload">
                    <i class="fas fa-upload me-1"></i>Upload
                </label>
            </div>
            @endif
            
            @if($showUrlTab)
            <div class="sie-tab-content active" data-tab-content="url" id="{{ $editorId }}UrlContent">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                    <input type="url" 
                           class="form-control" 
                           id="{{ $editorId }}UrlInput" 
                           placeholder="https://example.com/image.jpg">
                    <button type="button" class="btn btn-outline-primary" id="{{ $editorId }}UrlConfirmBtn">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
                <small class="text-muted d-block mt-1">Enter a publicly accessible image URL (JPEG, PNG, GIF)</small>
            </div>
            @endif
            
            <div class="sie-tab-content{{ $showUrlTab ? '' : ' active' }}" data-tab-content="upload" id="{{ $editorId }}UploadContent">
                <div class="border border-dashed rounded p-3 text-center bg-light" id="{{ $editorId }}Dropzone">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="mb-1 small">Drag & drop or</p>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="{{ $editorId }}BrowseBtn">
                        <i class="fas fa-folder-open me-1"></i>{{ $browseLabel }}
                    </button>
                    <small class="text-muted d-block mt-2">JPEG, PNG, GIF only. Max {{ number_format($maxSize / 1024 / 1024, 0) }} MB</small>
                </div>
            </div>
            
            <div id="{{ $editorId }}MediaError" class="alert alert-danger py-2 px-3 mt-2 d-none small">
                <i class="fas fa-exclamation-circle me-1"></i><span id="{{ $editorId }}ErrorText"></span>
            </div>
        </div>
        
        <div class="sie-editor-wrapper d-none" id="{{ $editorId }}EditorWrapper">
            <div id="{{ $editorId }}Container"></div>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="button" class="btn btn-sm btn-outline-danger" id="{{ $editorId }}RemoveBtn">
                    <i class="fas fa-trash-alt me-1"></i>Remove
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" id="{{ $editorId }}ChangeBtn">
                    <i class="fas fa-exchange-alt me-1"></i>Change Image
                </button>
            </div>
        </div>
    </div>
    
    <div class="alert alert-danger d-none small py-2" id="{{ $editorId }}Error"></div>
</div>

<script>
(function() {
    var editorId = @json($editorId);
    var component = document.querySelector('[data-editor-id="' + editorId + '"]');
    var preset = component.dataset.preset;
    var maxSize = parseInt(component.dataset.maxSize);
    
    var editor = null;
    var cropData = null;
    var initialized = false;
    
    var uploadZone = document.getElementById(editorId + 'UploadZone');
    var uploadPrompt = document.getElementById(editorId + 'UploadPrompt');
    var editorWrapper = document.getElementById(editorId + 'EditorWrapper');
    var fileInput = document.getElementById(editorId + 'FileInput');
    var browseBtn = document.getElementById(editorId + 'BrowseBtn');
    var removeBtn = document.getElementById(editorId + 'RemoveBtn');
    var changeBtn = document.getElementById(editorId + 'ChangeBtn');
    var errorEl = document.getElementById(editorId + 'Error');
    
    if (window[editorId + 'Initialized']) return;
    window[editorId + 'Initialized'] = true;
    
    function showError(message) {
        errorEl.textContent = message;
        errorEl.classList.remove('d-none');
    }
    
    function hideError() {
        errorEl.classList.add('d-none');
    }
    
    function initEditor() {
        if (editor) {
            editor.destroy();
        }
        
        editor = new SharedImageEditor({
            containerId: editorId + 'Container',
            preset: preset,
            onChange: function(data) {
                cropData = data;
                if (typeof window['on' + editorId + 'Change'] === 'function') {
                    window['on' + editorId + 'Change'](data);
                }
            }
        });
        
        window[editorId + 'Instance'] = editor;
    }
    
    function handleFile(file) {
        hideError();
        
        if (!file) return;
        
        if (!file.type.match(/^image\/(png|jpeg|gif)$/)) {
            showError('Please upload a JPEG, PNG, or GIF image.');
            return;
        }
        
        if (file.size > maxSize) {
            showError('Image is too large. Maximum size is ' + (maxSize / 1024 / 1024) + 'MB.');
            return;
        }
        
        uploadPrompt.classList.add('d-none');
        editorWrapper.classList.remove('d-none');
        
        initEditor();
        
        editor.loadImageFromFile(file, function(err) {
            if (err) {
                showError('Failed to load image. Please try another file.');
                uploadPrompt.classList.remove('d-none');
                editorWrapper.classList.add('d-none');
            }
        });
    }
    
    changeBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleFile(this.files[0]);
        }
    });
    
    removeBtn.addEventListener('click', function() {
        if (editor) {
            editor.clearImage();
        }
        cropData = null;
        fileInput.value = '';
        uploadPrompt.classList.remove('d-none');
        editorWrapper.classList.add('d-none');
        hideError();
        
        if (typeof window['on' + editorId + 'Remove'] === 'function') {
            window['on' + editorId + 'Remove']();
        }
    });
    
    var dropzone = document.getElementById(editorId + 'Dropzone') || uploadZone;
    
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('border-primary');
    });
    
    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('border-primary');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('border-primary');
        
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFile(e.dataTransfer.files[0]);
        }
    });
    
    // Source toggle (Upload/URL) functionality
    var showUrlTab = component.dataset.showUrlTab === 'true';
    if (showUrlTab) {
        var sourceUploadRadio = document.getElementById(editorId + 'SourceUpload');
        var sourceUrlRadio = document.getElementById(editorId + 'SourceUrl');
        var uploadContent = document.getElementById(editorId + 'UploadContent');
        var urlContent = document.getElementById(editorId + 'UrlContent');
        var urlInput = document.getElementById(editorId + 'UrlInput');
        var urlConfirmBtn = document.getElementById(editorId + 'UrlConfirmBtn');
        
        function switchToTab(tabName) {
            if (tabName === 'upload') {
                uploadContent.classList.add('active');
                if (urlContent) urlContent.classList.remove('active');
            } else {
                if (uploadContent) uploadContent.classList.remove('active');
                if (urlContent) urlContent.classList.add('active');
            }
        }
        
        if (sourceUploadRadio) {
            sourceUploadRadio.addEventListener('change', function() {
                if (this.checked) switchToTab('upload');
            });
        }
        
        if (sourceUrlRadio) {
            sourceUrlRadio.addEventListener('change', function() {
                if (this.checked) switchToTab('url');
            });
        }
        
        if (urlConfirmBtn && urlInput) {
            function loadImageFromUrl() {
                var url = urlInput.value.trim();
                if (!url) {
                    showError('Please enter an image URL.');
                    return;
                }
                
                if (!url.match(/^https?:\/\/.+/)) {
                    showError('Please enter a valid URL starting with http:// or https://');
                    return;
                }
                
                hideError();
                uploadPrompt.classList.add('d-none');
                editorWrapper.classList.remove('d-none');
                
                initEditor();
                
                editor.loadImage(url, function(err) {
                    if (err) {
                        showError('Failed to load image from URL. Please check the URL and try again.');
                        uploadPrompt.classList.remove('d-none');
                        editorWrapper.classList.add('d-none');
                    }
                });
            }
            
            urlConfirmBtn.addEventListener('click', loadImageFromUrl);
            
            urlInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    loadImageFromUrl();
                }
            });
        }
    }
    
    // Handle browse button click
    browseBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
    window[editorId + 'GetCropData'] = function() {
        return cropData;
    };
    
    window[editorId + 'LoadImage'] = function(src, callback) {
        hideError();
        uploadPrompt.classList.add('d-none');
        editorWrapper.classList.remove('d-none');
        initEditor();
        editor.loadImage(src, callback);
    };
    
    window[editorId + 'SetCropData'] = function(data) {
        if (editor && data) {
            editor.setCropData(data);
        }
    };
    
    window[editorId + 'GenerateCroppedImage'] = function(callback) {
        if (editor) {
            return editor.generateCroppedImage(callback);
        }
        return null;
    };
    
    window[editorId + 'SetPreset'] = function(presetName) {
        if (editor) {
            editor.setPreset(presetName);
        }
        preset = presetName;
    };
    
    window[editorId + 'Reset'] = function() {
        if (editor) {
            editor.clearImage();
            editor.destroy();
            editor = null;
        }
        cropData = null;
        fileInput.value = '';
        uploadPrompt.classList.remove('d-none');
        editorWrapper.classList.add('d-none');
        hideError();
    };
})();
</script>
