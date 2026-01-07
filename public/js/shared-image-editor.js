/**
 * Shared Image Editor Component
 * Reusable drag, zoom, crop editor with fixed aspect ratio enforcement
 * 
 * Usage:
 *   var editor = new SharedImageEditor({
 *       containerId: 'myEditorContainer',
 *       aspectRatio: 1,           // 1:1 square, or 16/9, or 2/1
 *       outputWidth: 224,         // Exact output dimensions
 *       outputHeight: 224,
 *       minZoom: 25,
 *       maxZoom: 200,
 *       onChange: function(data) { ... }
 *   });
 *   editor.loadImage(imageUrl);
 *   var result = editor.getCropData();
 */

(function(global) {
    'use strict';

    var PRESET_CONFIGS = {
        'agent-logo': {
            aspectRatio: 1,
            outputWidth: 224,
            outputHeight: 224,
            frameWidth: 120,
            frameHeight: 120,
            label: 'Logo (1:1)'
        },
        'agent-hero': {
            aspectRatio: 16/9,
            outputWidth: 1440,
            outputHeight: 810,
            frameWidth: 280,
            frameHeight: 158,
            label: 'Hero (16:9)'
        },
        'rich-card-short': {
            aspectRatio: 2,
            outputWidth: 200,
            outputHeight: 100,
            frameWidth: 200,
            frameHeight: 100,
            label: 'Short (2:1)'
        },
        'rich-card-medium': {
            aspectRatio: 2,
            outputWidth: 240,
            outputHeight: 120,
            frameWidth: 240,
            frameHeight: 120,
            label: 'Medium (2:1)'
        },
        'rich-card-tall': {
            aspectRatio: 2,
            outputWidth: 280,
            outputHeight: 140,
            frameWidth: 280,
            frameHeight: 140,
            label: 'Tall (2:1)'
        },
        'carousel-small-short': {
            aspectRatio: 180/112,
            outputWidth: 180,
            outputHeight: 112,
            frameWidth: 180,
            frameHeight: 112,
            label: 'Small Short'
        },
        'carousel-small-medium': {
            aspectRatio: 180/168,
            outputWidth: 180,
            outputHeight: 168,
            frameWidth: 180,
            frameHeight: 168,
            label: 'Small Medium'
        },
        'carousel-medium-short': {
            aspectRatio: 296/112,
            outputWidth: 296,
            outputHeight: 112,
            frameWidth: 296,
            frameHeight: 112,
            label: 'Medium Short'
        },
        'carousel-medium-medium': {
            aspectRatio: 296/168,
            outputWidth: 296,
            outputHeight: 168,
            frameWidth: 296,
            frameHeight: 168,
            label: 'Medium Medium'
        },
        'carousel-medium-tall': {
            aspectRatio: 296/264,
            outputWidth: 296,
            outputHeight: 264,
            frameWidth: 296,
            frameHeight: 264,
            label: 'Medium Tall'
        }
    };

    function SharedImageEditor(options) {
        this.options = Object.assign({
            containerId: null,
            preset: null,
            aspectRatio: 1,
            outputWidth: 200,
            outputHeight: 200,
            frameWidth: 200,
            frameHeight: 200,
            minZoom: 25,
            maxZoom: 200,
            defaultZoom: 100,
            showCrosshair: true,
            showZoomSlider: true,
            showResetButton: true,
            onChange: null
        }, options);

        if (this.options.preset && PRESET_CONFIGS[this.options.preset]) {
            var preset = PRESET_CONFIGS[this.options.preset];
            this.options = Object.assign(this.options, preset);
        }

        this.state = {
            imageWidth: 0,
            imageHeight: 0,
            displayScale: 1,
            zoom: this.options.defaultZoom,
            offsetX: 0,
            offsetY: 0,
            isDragging: false,
            startX: 0,
            startY: 0,
            startOffsetX: 0,
            startOffsetY: 0,
            imageLoaded: false,
            imageSrc: null
        };

        this.elements = {};
        this.crosshairTimer = null;
        this.boundHandlers = {};

        if (this.options.containerId) {
            this.init();
        }
    }

    SharedImageEditor.prototype.init = function() {
        var container = document.getElementById(this.options.containerId);
        if (!container) {
            console.error('[SharedImageEditor] Container not found:', this.options.containerId);
            return;
        }

        this.elements.container = container;
        this.buildUI();
        this.bindEvents();
    };

    SharedImageEditor.prototype.buildUI = function() {
        var container = this.elements.container;
        container.innerHTML = '';
        container.className = 'sie-editor-container';

        var workspace = document.createElement('div');
        workspace.className = 'sie-workspace';
        this.elements.workspace = workspace;

        var img = document.createElement('img');
        img.className = 'sie-image';
        img.draggable = false;
        img.alt = 'Image preview';
        this.elements.image = img;
        workspace.appendChild(img);

        var overlay = document.createElement('div');
        overlay.className = 'sie-overlay';
        this.elements.overlay = overlay;

        var frame = document.createElement('div');
        frame.className = 'sie-crop-frame';
        frame.style.width = this.options.frameWidth + 'px';
        frame.style.height = this.options.frameHeight + 'px';
        this.elements.frame = frame;

        if (this.options.showCrosshair) {
            var crosshairH = document.createElement('div');
            crosshairH.className = 'sie-crosshair sie-crosshair-h';
            frame.appendChild(crosshairH);

            var crosshairV = document.createElement('div');
            crosshairV.className = 'sie-crosshair sie-crosshair-v';
            frame.appendChild(crosshairV);

            var crosshairCenter = document.createElement('div');
            crosshairCenter.className = 'sie-crosshair sie-crosshair-center';
            frame.appendChild(crosshairCenter);

            this.elements.crosshairs = [crosshairH, crosshairV, crosshairCenter];
        }

        overlay.appendChild(frame);
        workspace.appendChild(overlay);
        container.appendChild(workspace);

        var hint = document.createElement('div');
        hint.className = 'sie-hint text-center mt-2';
        hint.innerHTML = '<i class="fas fa-arrows-alt me-1"></i>Drag image to position • Use slider to zoom';
        container.appendChild(hint);

        if (this.options.showZoomSlider) {
            var controls = document.createElement('div');
            controls.className = 'sie-controls mt-3';

            var zoomRow = document.createElement('div');
            zoomRow.className = 'd-flex align-items-center gap-2';

            var zoomIcon = document.createElement('i');
            zoomIcon.className = 'fas fa-search-minus text-muted';
            zoomRow.appendChild(zoomIcon);

            var slider = document.createElement('input');
            slider.type = 'range';
            slider.className = 'form-range flex-grow-1 sie-zoom-slider';
            slider.min = this.options.minZoom;
            slider.max = this.options.maxZoom;
            slider.value = this.state.zoom;
            this.elements.zoomSlider = slider;
            zoomRow.appendChild(slider);

            var zoomIconPlus = document.createElement('i');
            zoomIconPlus.className = 'fas fa-search-plus text-muted';
            zoomRow.appendChild(zoomIconPlus);

            var zoomValue = document.createElement('span');
            zoomValue.className = 'sie-zoom-value badge bg-light text-dark ms-2';
            zoomValue.textContent = this.state.zoom + '%';
            this.elements.zoomValue = zoomValue;
            zoomRow.appendChild(zoomValue);

            controls.appendChild(zoomRow);

            if (this.options.showResetButton) {
                var resetRow = document.createElement('div');
                resetRow.className = 'd-flex justify-content-center mt-2 gap-2';

                var resetBtn = document.createElement('button');
                resetBtn.type = 'button';
                resetBtn.className = 'btn btn-sm btn-outline-secondary';
                resetBtn.innerHTML = '<i class="fas fa-undo me-1"></i>Reset Position';
                this.elements.resetBtn = resetBtn;
                resetRow.appendChild(resetBtn);

                var fitBtn = document.createElement('button');
                fitBtn.type = 'button';
                fitBtn.className = 'btn btn-sm btn-outline-secondary';
                fitBtn.innerHTML = '<i class="fas fa-compress-alt me-1"></i>Fit to Frame';
                this.elements.fitBtn = fitBtn;
                resetRow.appendChild(fitBtn);

                controls.appendChild(resetRow);
            }

            container.appendChild(controls);
        }

        var placeholder = document.createElement('div');
        placeholder.className = 'sie-placeholder';
        placeholder.innerHTML = '<i class="fas fa-image fa-3x text-muted mb-2"></i><br><span class="text-muted">No image loaded</span>';
        this.elements.placeholder = placeholder;
        workspace.appendChild(placeholder);
    };

    SharedImageEditor.prototype.bindEvents = function() {
        var self = this;

        this.boundHandlers.mousedown = function(e) { self.startDrag(e); };
        this.boundHandlers.touchstart = function(e) { self.startDrag(e); };
        this.boundHandlers.mousemove = function(e) { self.doDrag(e); };
        this.boundHandlers.touchmove = function(e) { self.doDrag(e); };
        this.boundHandlers.mouseup = function() { self.endDrag(); };
        this.boundHandlers.touchend = function() { self.endDrag(); };

        this.elements.workspace.addEventListener('mousedown', this.boundHandlers.mousedown);
        this.elements.workspace.addEventListener('touchstart', this.boundHandlers.touchstart, { passive: false });
        document.addEventListener('mousemove', this.boundHandlers.mousemove);
        document.addEventListener('touchmove', this.boundHandlers.touchmove, { passive: false });
        document.addEventListener('mouseup', this.boundHandlers.mouseup);
        document.addEventListener('touchend', this.boundHandlers.touchend);

        if (this.elements.zoomSlider) {
            this.elements.zoomSlider.addEventListener('input', function() {
                self.setZoom(parseInt(this.value));
            });
        }

        if (this.elements.resetBtn) {
            this.elements.resetBtn.addEventListener('click', function() {
                self.resetPosition();
            });
        }

        if (this.elements.fitBtn) {
            this.elements.fitBtn.addEventListener('click', function() {
                self.fitToFrame();
            });
        }
    };

    SharedImageEditor.prototype.loadImage = function(src, callback) {
        var self = this;
        
        if (!src) {
            this.clearImage();
            return;
        }

        this.state.imageSrc = src;
        this.elements.image.onload = function() {
            self.state.imageWidth = this.naturalWidth;
            self.state.imageHeight = this.naturalHeight;
            self.state.imageLoaded = true;

            self.elements.placeholder.style.display = 'none';
            self.elements.image.style.display = 'block';

            self.calculateDisplayScale();
            self.state.zoom = self.options.defaultZoom;
            self.state.offsetX = 0;
            self.state.offsetY = 0;

            if (self.elements.zoomSlider) self.elements.zoomSlider.value = self.state.zoom;
            if (self.elements.zoomValue) self.elements.zoomValue.textContent = self.state.zoom + '%';

            self.applyTransform();

            if (callback) callback(null, self.state);
            self.triggerChange();
        };

        this.elements.image.onerror = function() {
            self.state.imageLoaded = false;
            if (callback) callback(new Error('Failed to load image'));
        };

        this.elements.image.src = src;
    };

    SharedImageEditor.prototype.loadImageFromFile = function(file, callback) {
        var self = this;
        
        if (!file || !file.type.match(/^image\/(jpeg|png|gif)$/)) {
            if (callback) callback(new Error('Invalid file type'));
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            self.loadImage(e.target.result, callback);
        };
        reader.onerror = function() {
            if (callback) callback(new Error('Failed to read file'));
        };
        reader.readAsDataURL(file);
    };

    SharedImageEditor.prototype.clearImage = function() {
        this.state.imageLoaded = false;
        this.state.imageSrc = null;
        this.elements.image.src = '';
        this.elements.image.style.display = 'none';
        this.elements.placeholder.style.display = 'flex';
        this.triggerChange();
    };

    SharedImageEditor.prototype.calculateDisplayScale = function() {
        var workspaceWidth = this.elements.workspace.clientWidth || 300;
        var workspaceHeight = this.elements.workspace.clientHeight || 220;

        var scaleX = workspaceWidth / this.state.imageWidth;
        var scaleY = workspaceHeight / this.state.imageHeight;
        this.state.displayScale = Math.max(scaleX, scaleY) * 0.8;
    };

    SharedImageEditor.prototype.applyTransform = function() {
        if (!this.state.imageLoaded) return;

        var workspaceWidth = this.elements.workspace.clientWidth || 300;
        var workspaceHeight = this.elements.workspace.clientHeight || 220;

        var scale = this.state.displayScale * (this.state.zoom / 100);
        var displayWidth = this.state.imageWidth * scale;
        var displayHeight = this.state.imageHeight * scale;

        this.elements.image.style.width = displayWidth + 'px';
        this.elements.image.style.height = displayHeight + 'px';

        var centerX = (workspaceWidth - displayWidth) / 2;
        var centerY = (workspaceHeight - displayHeight) / 2;

        this.elements.image.style.left = (centerX + this.state.offsetX) + 'px';
        this.elements.image.style.top = (centerY + this.state.offsetY) + 'px';
    };

    SharedImageEditor.prototype.constrainPosition = function() {
        var scale = this.state.displayScale * (this.state.zoom / 100);
        var displayWidth = this.state.imageWidth * scale;
        var displayHeight = this.state.imageHeight * scale;

        var frameWidth = this.options.frameWidth;
        var frameHeight = this.options.frameHeight;

        if (displayWidth > frameWidth) {
            var maxOffsetX = (displayWidth - frameWidth) / 2;
            this.state.offsetX = Math.max(-maxOffsetX, Math.min(maxOffsetX, this.state.offsetX));
        } else {
            this.state.offsetX = 0;
        }

        if (displayHeight > frameHeight) {
            var maxOffsetY = (displayHeight - frameHeight) / 2;
            this.state.offsetY = Math.max(-maxOffsetY, Math.min(maxOffsetY, this.state.offsetY));
        } else {
            this.state.offsetY = 0;
        }
    };

    SharedImageEditor.prototype.startDrag = function(e) {
        if (!this.state.imageLoaded) return;
        e.preventDefault();

        this.state.isDragging = true;
        var point = e.touches ? e.touches[0] : e;
        this.state.startX = point.clientX;
        this.state.startY = point.clientY;
        this.state.startOffsetX = this.state.offsetX;
        this.state.startOffsetY = this.state.offsetY;

        this.elements.workspace.classList.add('sie-dragging');
        this.showCrosshair();
    };

    SharedImageEditor.prototype.doDrag = function(e) {
        if (!this.state.isDragging) return;
        e.preventDefault();

        var point = e.touches ? e.touches[0] : e;
        var deltaX = point.clientX - this.state.startX;
        var deltaY = point.clientY - this.state.startY;

        this.state.offsetX = this.state.startOffsetX + deltaX;
        this.state.offsetY = this.state.startOffsetY + deltaY;

        this.constrainPosition();
        this.applyTransform();
    };

    SharedImageEditor.prototype.endDrag = function() {
        if (this.state.isDragging) {
            this.state.isDragging = false;
            this.elements.workspace.classList.remove('sie-dragging');
            this.hideCrosshairDelayed();
            this.triggerChange();
        }
    };

    SharedImageEditor.prototype.setZoom = function(value) {
        this.state.zoom = Math.max(this.options.minZoom, Math.min(this.options.maxZoom, value));
        
        if (this.elements.zoomValue) {
            this.elements.zoomValue.textContent = this.state.zoom + '%';
        }

        this.constrainPosition();
        this.applyTransform();
        this.showCrosshair();
        this.hideCrosshairDelayed();
        this.triggerChange();
    };

    SharedImageEditor.prototype.resetPosition = function() {
        this.state.offsetX = 0;
        this.state.offsetY = 0;
        this.applyTransform();
        this.triggerChange();
    };

    SharedImageEditor.prototype.fitToFrame = function() {
        if (!this.state.imageLoaded) return;

        var scaleX = this.options.frameWidth / this.state.imageWidth;
        var scaleY = this.options.frameHeight / this.state.imageHeight;
        var fitScale = Math.max(scaleX, scaleY);

        var fitZoom = Math.round((fitScale / this.state.displayScale) * 100);
        fitZoom = Math.max(this.options.minZoom, Math.min(this.options.maxZoom, fitZoom));

        this.state.zoom = fitZoom;
        this.state.offsetX = 0;
        this.state.offsetY = 0;

        if (this.elements.zoomSlider) this.elements.zoomSlider.value = fitZoom;
        if (this.elements.zoomValue) this.elements.zoomValue.textContent = fitZoom + '%';

        this.applyTransform();
        this.triggerChange();
    };

    SharedImageEditor.prototype.showCrosshair = function() {
        if (this.crosshairTimer) {
            clearTimeout(this.crosshairTimer);
            this.crosshairTimer = null;
        }
        if (this.elements.crosshairs) {
            this.elements.crosshairs.forEach(function(el) {
                el.classList.add('sie-crosshair-active');
            });
        }
    };

    SharedImageEditor.prototype.hideCrosshairDelayed = function() {
        var self = this;
        if (this.crosshairTimer) clearTimeout(this.crosshairTimer);
        this.crosshairTimer = setTimeout(function() {
            if (self.elements.crosshairs) {
                self.elements.crosshairs.forEach(function(el) {
                    el.classList.remove('sie-crosshair-active');
                });
            }
            self.crosshairTimer = null;
        }, 400);
    };

    SharedImageEditor.prototype.triggerChange = function() {
        if (typeof this.options.onChange === 'function') {
            this.options.onChange(this.getCropData());
        }
    };

    SharedImageEditor.prototype.getCropData = function() {
        if (!this.state.imageLoaded) {
            return null;
        }

        var scale = this.state.displayScale * (this.state.zoom / 100);
        var displayWidth = this.state.imageWidth * scale;
        var displayHeight = this.state.imageHeight * scale;

        var workspaceWidth = this.elements.workspace.clientWidth || 300;
        var workspaceHeight = this.elements.workspace.clientHeight || 220;

        var imageCenterX = (workspaceWidth / 2) + this.state.offsetX;
        var imageCenterY = (workspaceHeight / 2) + this.state.offsetY;

        var frameCenterX = workspaceWidth / 2;
        var frameCenterY = workspaceHeight / 2;

        var frameLeft = frameCenterX - this.options.frameWidth / 2;
        var frameTop = frameCenterY - this.options.frameHeight / 2;

        var imageLeft = imageCenterX - displayWidth / 2;
        var imageTop = imageCenterY - displayHeight / 2;

        var cropX = (frameLeft - imageLeft) / scale;
        var cropY = (frameTop - imageTop) / scale;
        var cropWidth = this.options.frameWidth / scale;
        var cropHeight = this.options.frameHeight / scale;

        cropX = Math.max(0, Math.min(this.state.imageWidth - cropWidth, cropX));
        cropY = Math.max(0, Math.min(this.state.imageHeight - cropHeight, cropY));

        return {
            imageSrc: this.state.imageSrc,
            imageWidth: this.state.imageWidth,
            imageHeight: this.state.imageHeight,
            zoom: this.state.zoom,
            offsetX: this.state.offsetX,
            offsetY: this.state.offsetY,
            crop: {
                x: Math.round(cropX),
                y: Math.round(cropY),
                width: Math.round(cropWidth),
                height: Math.round(cropHeight)
            },
            output: {
                width: this.options.outputWidth,
                height: this.options.outputHeight,
                aspectRatio: this.options.aspectRatio
            }
        };
    };

    SharedImageEditor.prototype.setCropData = function(data) {
        if (!data) return;

        if (data.zoom !== undefined) {
            this.state.zoom = data.zoom;
            if (this.elements.zoomSlider) this.elements.zoomSlider.value = data.zoom;
            if (this.elements.zoomValue) this.elements.zoomValue.textContent = data.zoom + '%';
        }

        if (data.offsetX !== undefined) this.state.offsetX = data.offsetX;
        if (data.offsetY !== undefined) this.state.offsetY = data.offsetY;

        this.constrainPosition();
        this.applyTransform();
    };

    SharedImageEditor.prototype.setFrameSize = function(width, height) {
        this.options.frameWidth = width;
        this.options.frameHeight = height;
        this.options.aspectRatio = width / height;

        if (this.elements.frame) {
            this.elements.frame.style.width = width + 'px';
            this.elements.frame.style.height = height + 'px';
        }

        this.constrainPosition();
        this.applyTransform();
        this.triggerChange();
    };

    SharedImageEditor.prototype.setPreset = function(presetName) {
        var preset = PRESET_CONFIGS[presetName];
        if (!preset) {
            console.warn('[SharedImageEditor] Unknown preset:', presetName);
            return;
        }

        this.options.aspectRatio = preset.aspectRatio;
        this.options.outputWidth = preset.outputWidth;
        this.options.outputHeight = preset.outputHeight;
        this.setFrameSize(preset.frameWidth, preset.frameHeight);
    };

    SharedImageEditor.prototype.generateCroppedImage = function(callback) {
        if (!this.state.imageLoaded) {
            if (callback) callback(new Error('No image loaded'));
            return;
        }

        var cropData = this.getCropData();
        var canvas = document.createElement('canvas');
        canvas.width = this.options.outputWidth;
        canvas.height = this.options.outputHeight;

        var ctx = canvas.getContext('2d');
        ctx.drawImage(
            this.elements.image,
            cropData.crop.x,
            cropData.crop.y,
            cropData.crop.width,
            cropData.crop.height,
            0,
            0,
            this.options.outputWidth,
            this.options.outputHeight
        );

        try {
            var dataUrl = canvas.toDataURL('image/png');
            if (callback) callback(null, dataUrl);
            return dataUrl;
        } catch (e) {
            if (callback) callback(e);
            return null;
        }
    };

    SharedImageEditor.prototype.destroy = function() {
        if (this.boundHandlers.mousemove) {
            document.removeEventListener('mousemove', this.boundHandlers.mousemove);
        }
        if (this.boundHandlers.touchmove) {
            document.removeEventListener('touchmove', this.boundHandlers.touchmove);
        }
        if (this.boundHandlers.mouseup) {
            document.removeEventListener('mouseup', this.boundHandlers.mouseup);
        }
        if (this.boundHandlers.touchend) {
            document.removeEventListener('touchend', this.boundHandlers.touchend);
        }

        if (this.crosshairTimer) {
            clearTimeout(this.crosshairTimer);
        }

        if (this.elements.container) {
            this.elements.container.innerHTML = '';
        }

        this.elements = {};
        this.boundHandlers = {};
    };

    SharedImageEditor.PRESETS = PRESET_CONFIGS;

    global.SharedImageEditor = SharedImageEditor;

})(typeof window !== 'undefined' ? window : this);
