/**
 * Bug Report Widget — Form handling, metadata capture, console log buffer, submission
 *
 * Follows the QuickSMS service singleton pattern.
 * Included in both Customer Portal and Admin Console layouts.
 */

// ─── Console Log Buffer ───
// Captures last 50 console messages for bug reports.
// Must be initialised before any other script logs.
(function() {
    if (window.__bugReportConsoleBuf) return;
    window.__bugReportConsoleBuf = [];
    var MAX_ENTRIES = 50;
    var methods = ['log', 'warn', 'error', 'info'];
    var sensitivePattern = /Bearer |api[_-]?key|token|password|cookie|authorization/i;

    methods.forEach(function(method) {
        var orig = console[method];
        console[method] = function() {
            var args = [].slice.call(arguments);
            var message;
            try {
                message = args.map(function(a) {
                    if (typeof a === 'object') {
                        try { return JSON.stringify(a); } catch(e) { return String(a); }
                    }
                    return String(a);
                }).join(' ');
            } catch(e) {
                message = '[unserializable]';
            }

            // Skip entries that contain sensitive data
            if (!sensitivePattern.test(message)) {
                window.__bugReportConsoleBuf.push({
                    level: method,
                    message: message.substring(0, 1000),
                    ts: Date.now()
                });
                if (window.__bugReportConsoleBuf.length > MAX_ENTRIES) {
                    window.__bugReportConsoleBuf.shift();
                }
            }

            orig.apply(console, arguments);
        };
    });
})();


// ─── Bug Report Service ───
var BugReportService = {
    config: {
        baseUrl: '/api/bug-report',
        maxFileSize: 5 * 1024 * 1024 // 5MB
    },

    _headers: function() {
        return {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        };
        // No Content-Type — FormData sets its own multipart boundary
    },

    _handleResponse: function(response) {
        if (response.status === 422) {
            return response.json().then(function(data) {
                var msg = data.message || 'Validation failed';
                if (data.errors) {
                    var firstField = Object.keys(data.errors)[0];
                    msg = data.errors[firstField][0] || msg;
                }
                throw new Error(msg);
            });
        }
        if (response.status === 429) {
            throw new Error('Rate limit exceeded. Please wait before submitting another report.');
        }
        if (!response.ok) {
            return response.json().then(function(data) {
                throw new Error(data.message || 'Request failed: ' + response.status);
            }).catch(function(e) {
                if (e.message) throw e;
                throw new Error('Request failed: ' + response.status);
            });
        }
        return response.json();
    },

    // Product area mapping from URL path
    productAreaMap: {
        '/messages/send':       'Send Message',
        '/messages/inbox':      'Inbox',
        '/messages/campaign':   'Send Message',
        '/messages':            'Messages',
        '/contacts':            'Contact Book',
        '/reporting':           'Reporting',
        '/purchase':            'Billing',
        '/management/template': 'Templates',
        '/management/api':      'API Connections',
        '/management/rcs':      'Send Message',
        '/management/numbers':  'Send Message',
        '/management':          'Management',
        '/account':             'Account',
        '/support':             'Support',
        '/admin':               'Admin Console',
        '/flows':               'Flow Builder'
    },

    deriveProductArea: function() {
        var path = window.location.pathname;
        var bestMatch = 'Dashboard';
        var bestLen = 0;
        for (var key in this.productAreaMap) {
            if (path.indexOf(key) === 0 && key.length > bestLen) {
                bestLen = key.length;
                bestMatch = this.productAreaMap[key];
            }
        }
        return bestMatch;
    },

    parseBrowserInfo: function() {
        var ua = navigator.userAgent;
        // Simple browser detection
        if (ua.indexOf('Chrome') > -1 && ua.indexOf('Edg') === -1) {
            var m = ua.match(/Chrome\/(\d+)/);
            return 'Chrome ' + (m ? m[1] : '');
        }
        if (ua.indexOf('Edg') > -1) {
            var m = ua.match(/Edg\/(\d+)/);
            return 'Edge ' + (m ? m[1] : '');
        }
        if (ua.indexOf('Firefox') > -1) {
            var m = ua.match(/Firefox\/(\d+)/);
            return 'Firefox ' + (m ? m[1] : '');
        }
        if (ua.indexOf('Safari') > -1) {
            var m = ua.match(/Version\/(\d+)/);
            return 'Safari ' + (m ? m[1] : '');
        }
        return ua.substring(0, 100);
    },

    parseOS: function() {
        var ua = navigator.userAgent;
        if (ua.indexOf('Windows NT 10') > -1) return 'Windows 10/11';
        if (ua.indexOf('Windows') > -1) return 'Windows';
        if (ua.indexOf('Mac OS X') > -1) {
            var m = ua.match(/Mac OS X (\d+[._]\d+)/);
            return 'macOS ' + (m ? m[1].replace(/_/g, '.') : '');
        }
        if (ua.indexOf('Linux') > -1) return 'Linux';
        if (ua.indexOf('Android') > -1) return 'Android';
        if (ua.indexOf('iOS') > -1 || ua.indexOf('iPhone') > -1) return 'iOS';
        return navigator.platform || 'Unknown';
    },

    gatherMetadata: function() {
        var widget = document.getElementById('bugReportWidget');
        return {
            page_url: window.location.href,
            timestamp: new Date().toISOString(),
            reporter_name: widget ? widget.dataset.userName : '',
            reporter_email: widget ? widget.dataset.userEmail : '',
            account_id: widget ? widget.dataset.accountId : '',
            account_name: widget ? widget.dataset.accountName : '',
            browser: this.parseBrowserInfo(),
            os: this.parseOS(),
            viewport: window.innerWidth + 'x' + window.innerHeight,
            environment: widget ? widget.dataset.environment : 'unknown',
            product_area: this.deriveProductArea()
        };
    },

    submit: function(formFields, screenshotFile, annotatedScreenshotFile) {
        var metadata = this.gatherMetadata();
        var formData = new FormData();

        formData.append('category', formFields.category);
        formData.append('severity', formFields.severity);
        formData.append('title', formFields.title);
        formData.append('description', formFields.description);
        formData.append('metadata', JSON.stringify(metadata));

        // Console logs
        var consoleLogs = window.__bugReportConsoleBuf || [];
        if (consoleLogs.length > 0) {
            formData.append('console_logs', JSON.stringify(consoleLogs));
        }

        // Screenshot files
        if (screenshotFile) {
            formData.append('screenshot', screenshotFile);
        }
        if (annotatedScreenshotFile) {
            formData.append('annotated_screenshot', annotatedScreenshotFile);
        }

        return fetch(this.config.baseUrl, {
            method: 'POST',
            headers: this._headers(),
            body: formData
        }).then(this._handleResponse);
    }
};


// ─── Widget UI Controller ───
(function() {
    'use strict';

    var screenshotFile = null;
    var annotatedScreenshotBlob = null;
    var isSubmitting = false;

    function init() {
        var modal = document.getElementById('bugReportModal');
        if (!modal) return;

        bindEvents();
        // Populate metadata when modal opens
        modal.addEventListener('show.bs.modal', function() {
            populateMetadata();
        });
    }

    function bindEvents() {
        // Description character counter
        var desc = document.getElementById('bugDescription');
        var counter = document.getElementById('bugDescCharCount');
        if (desc && counter) {
            desc.addEventListener('input', function() {
                counter.textContent = desc.value.length;
            });
        }

        // Screenshot zone click → trigger file input
        var zone = document.getElementById('bugScreenshotZone');
        var input = document.getElementById('bugScreenshotInput');
        if (zone && input) {
            zone.addEventListener('click', function(e) {
                if (e.target.closest('#bugAnnotateBtn') || e.target.closest('#bugRemoveScreenshotBtn')) return;
                if (!screenshotFile) input.click();
            });

            input.addEventListener('change', function() {
                if (input.files && input.files[0]) {
                    handleScreenshotFile(input.files[0]);
                }
            });

            // Drag and drop
            zone.addEventListener('dragover', function(e) {
                e.preventDefault();
                zone.classList.add('dragover');
            });
            zone.addEventListener('dragleave', function() {
                zone.classList.remove('dragover');
            });
            zone.addEventListener('drop', function(e) {
                e.preventDefault();
                zone.classList.remove('dragover');
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    handleScreenshotFile(e.dataTransfer.files[0]);
                }
            });
        }

        // Clipboard paste
        document.getElementById('bugReportModal')?.addEventListener('paste', function(e) {
            var items = e.clipboardData?.items || [];
            for (var i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image/') === 0) {
                    e.preventDefault();
                    var file = items[i].getAsFile();
                    if (file) handleScreenshotFile(file);
                    break;
                }
            }
        });

        // Remove screenshot
        document.getElementById('bugRemoveScreenshotBtn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            removeScreenshot();
        });

        // Annotate button
        document.getElementById('bugAnnotateBtn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            openAnnotation();
        });

        // Annotation done
        document.getElementById('bugAnnotationDone')?.addEventListener('click', function() {
            closeAnnotation();
        });

        // Submit
        document.getElementById('bugReportSubmitBtn')?.addEventListener('click', function() {
            submitReport();
        });
    }

    function handleScreenshotFile(file) {
        if (!file.type.match(/^image\//)) {
            showToast('Please upload an image file.', 'warning');
            return;
        }
        if (file.size > BugReportService.config.maxFileSize) {
            showToast('Screenshot must be under 5MB.', 'warning');
            return;
        }

        screenshotFile = file;
        annotatedScreenshotBlob = null;

        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById('bugScreenshotImg');
            if (img) img.src = e.target.result;
            document.getElementById('bugScreenshotEmpty')?.classList.add('d-none');
            document.getElementById('bugScreenshotPreview')?.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }

    function removeScreenshot() {
        screenshotFile = null;
        annotatedScreenshotBlob = null;
        var input = document.getElementById('bugScreenshotInput');
        if (input) input.value = '';
        document.getElementById('bugScreenshotEmpty')?.classList.remove('d-none');
        document.getElementById('bugScreenshotPreview')?.classList.add('d-none');
        document.getElementById('bugAnnotationContainer')?.classList.add('d-none');
        document.getElementById('bugScreenshotImg').src = '';
    }

    function openAnnotation() {
        if (!screenshotFile) return;

        var container = document.getElementById('bugAnnotationContainer');
        container?.classList.remove('d-none');
        document.getElementById('bugScreenshotPreview')?.classList.add('d-none');

        // Load image onto canvas
        var img = new Image();
        img.onload = function() {
            if (typeof BugReportAnnotation !== 'undefined') {
                BugReportAnnotation.init('bugAnnotationCanvas', img);
            }
        };
        img.src = document.getElementById('bugScreenshotImg').src;
    }

    function closeAnnotation() {
        if (typeof BugReportAnnotation !== 'undefined') {
            // Export annotated image
            var canvas = document.getElementById('bugAnnotationCanvas');
            if (canvas) {
                canvas.toBlob(function(blob) {
                    annotatedScreenshotBlob = blob;

                    // Update preview with annotated version
                    var url = URL.createObjectURL(blob);
                    document.getElementById('bugScreenshotImg').src = url;
                    document.getElementById('bugScreenshotPreview')?.classList.remove('d-none');
                    document.getElementById('bugAnnotationContainer')?.classList.add('d-none');
                }, 'image/png');
            }
        } else {
            document.getElementById('bugScreenshotPreview')?.classList.remove('d-none');
            document.getElementById('bugAnnotationContainer')?.classList.add('d-none');
        }
    }

    function populateMetadata() {
        var meta = BugReportService.gatherMetadata();

        setText('bugMetaPage', truncate(meta.page_url, 60));
        setText('bugMetaBrowser', meta.browser + ' on ' + meta.os);
        setText('bugMetaEnv', meta.environment);
        setText('bugMetaAccount', meta.account_name || meta.account_id || '-');
        setText('bugMetaViewport', meta.viewport);
        setText('bugMetaTimestamp', new Date().toLocaleString());
    }

    function submitReport() {
        if (isSubmitting) return;

        var form = document.getElementById('bugReportForm');
        var category = document.getElementById('bugCategory')?.value;
        var severity = document.getElementById('bugSeverity')?.value;
        var title = document.getElementById('bugTitle')?.value?.trim();
        var description = document.getElementById('bugDescription')?.value?.trim();

        // Validate
        var valid = true;
        clearValidation();

        if (!category) { markInvalid('bugCategory'); valid = false; }
        if (!severity) { markInvalid('bugSeverity'); valid = false; }
        if (!title || title.length < 5) { markInvalid('bugTitle'); valid = false; }
        if (!description || description.length < 20) { markInvalid('bugDescription'); valid = false; }

        if (!valid) return;

        isSubmitting = true;
        setSubmitLoading(true);

        // Prepare annotated file if exists
        var annotatedFile = null;
        if (annotatedScreenshotBlob) {
            annotatedFile = new File([annotatedScreenshotBlob], 'annotated_screenshot.png', { type: 'image/png' });
        }

        BugReportService.submit(
            { category: category, severity: severity, title: title, description: description },
            screenshotFile,
            annotatedFile
        ).then(function(result) {
            // Show success
            document.getElementById('bugReportForm')?.classList.add('d-none');
            document.getElementById('bugReportFooter')?.classList.add('d-none');
            document.getElementById('bugReportSuccess')?.classList.remove('d-none');
            setText('bugReportRef', result.reference || '-');

            if (result.auto_fix) {
                setText('bugReportAutoFixMsg', 'An automated fix will be attempted by Claude Code.');
            }

            showToast('Bug report submitted! Reference: ' + (result.reference || ''), 'success');

            // Auto-close after 3 seconds
            setTimeout(function() {
                var modalEl = document.getElementById('bugReportModal');
                var bsModal = bootstrap.Modal.getInstance(modalEl);
                if (bsModal) bsModal.hide();
                resetForm();
            }, 3000);

        }).catch(function(err) {
            showToast(err.message || 'Failed to submit bug report. Please try again.', 'error');
        }).finally(function() {
            isSubmitting = false;
            setSubmitLoading(false);
        });
    }

    function resetForm() {
        document.getElementById('bugReportForm')?.classList.remove('d-none');
        document.getElementById('bugReportFooter')?.classList.remove('d-none');
        document.getElementById('bugReportSuccess')?.classList.add('d-none');

        var form = document.getElementById('bugReportForm');
        if (form) form.reset();

        removeScreenshot();
        clearValidation();

        var counter = document.getElementById('bugDescCharCount');
        if (counter) counter.textContent = '0';

        // Reset severity default
        var sev = document.getElementById('bugSeverity');
        if (sev) sev.value = 'medium';
    }

    // ─── Helpers ───
    function setText(id, text) {
        var el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    function truncate(str, len) {
        if (!str) return '-';
        return str.length > len ? str.substring(0, len) + '...' : str;
    }

    function markInvalid(id) {
        var el = document.getElementById(id);
        if (el) el.classList.add('is-invalid');
    }

    function clearValidation() {
        var els = document.querySelectorAll('#bugReportForm .is-invalid');
        for (var i = 0; i < els.length; i++) {
            els[i].classList.remove('is-invalid');
        }
    }

    function setSubmitLoading(loading) {
        var btn = document.getElementById('bugReportSubmitBtn');
        var text = document.getElementById('bugSubmitText');
        var spinner = document.getElementById('bugSubmitLoading');
        if (btn) btn.disabled = loading;
        if (text) text.classList.toggle('d-none', loading);
        if (spinner) spinner.classList.toggle('d-none', !loading);
    }

    function showToast(message, type) {
        if (typeof toastr !== 'undefined') {
            toastr[type === 'error' ? 'error' : type === 'warning' ? 'warning' : 'success'](message);
        }
    }

    // Initialise when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
