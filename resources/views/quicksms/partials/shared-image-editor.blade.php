{{--
    Shared Image Editor Component
    
    Usage:
    @include('quicksms.partials.shared-image-editor', [
        'editorId' => 'logoEditor',
        'preset' => 'agent-logo',           // or custom config
        'label' => 'Upload Logo',
        'accept' => 'image/png,image/jpeg',
        'maxSize' => 2 * 1024 * 1024,       // 2MB
        'required' => true
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
@endphp

@once
@push('styles')
<style>
.sie-upload-zone {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    background: #fafbfc;
    transition: all 0.2s ease;
}
.sie-upload-zone.sie-dragover {
    border-color: #886CC0;
    background: rgba(136, 108, 192, 0.05);
}
.sie-upload-prompt {
    min-height: 150px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
.sie-editor-wrapper {
    padding: 1rem;
}
</style>
@endpush
@endonce

<div class="sie-component" data-editor-id="{{ $editorId }}" data-preset="{{ $preset }}" data-max-size="{{ $maxSize }}">
    <label class="form-label">{{ $label }}@if($required)<span class="text-danger">*</span>@endif</label>
    
    @if($helpText)
    <p class="text-muted small mb-2">{{ $helpText }}</p>
    @endif
    
    <div class="sie-upload-zone mb-3" id="{{ $editorId }}UploadZone">
        <input type="file" 
               class="d-none" 
               id="{{ $editorId }}FileInput" 
               accept="{{ $accept }}"
               data-max-size="{{ $maxSize }}">
        
        <div class="sie-upload-prompt text-center p-4" id="{{ $editorId }}UploadPrompt">
            <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
            <p class="mb-1">Drag & drop an image here</p>
            <p class="small text-muted mb-2">or</p>
            <button type="button" class="btn btn-sm btn-outline-primary" id="{{ $editorId }}BrowseBtn">
                <i class="fas fa-folder-open me-1"></i>Browse Files
            </button>
            <p class="small text-muted mt-2 mb-0">
                PNG or JPEG, max {{ number_format($maxSize / 1024 / 1024, 0) }}MB
            </p>
        </div>
        
        <div class="sie-editor-wrapper d-none" id="{{ $editorId }}EditorWrapper">
            <div id="{{ $editorId }}Container"></div>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <button type="button" class="btn btn-sm btn-outline-danger" id="{{ $editorId }}RemoveBtn">
                    <i class="fas fa-trash me-1"></i>Remove
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
        
        if (!file.type.match(/^image\/(png|jpeg)$/)) {
            showError('Please upload a PNG or JPEG image.');
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
    
    browseBtn.addEventListener('click', function() {
        fileInput.click();
    });
    
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
    
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('sie-dragover');
    });
    
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('sie-dragover');
    });
    
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('sie-dragover');
        
        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFile(e.dataTransfer.files[0]);
        }
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
