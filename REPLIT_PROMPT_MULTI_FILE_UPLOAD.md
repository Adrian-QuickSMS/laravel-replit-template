# Replit Prompt: Multi-File Upload & Recipient Source Improvements

## Overview

The Send Message page (`resources/views/quicksms/messages/send-message.blade.php`) currently supports three recipient sources that can be combined in a single campaign:

1. **Manual Entry** — textarea for pasting numbers
2. **CSV/Excel File Upload** — currently limited to a **single file**
3. **Contact Book** — modal for selecting lists, tags, dynamic lists, individual contacts

The backend (`RecipientResolverService`) already handles multiple CSV source entries — it iterates `recipient_sources[]` and expands each `{type:"csv", data:[...]}` independently. **No backend changes are needed.** This is a **frontend-only** change.

## What Needs to Change

Transform the single-file upload into a multi-file upload system where:
- Users can upload **up to 5 files** (CSV, XLSX, XLS)
- Each file gets its own **column-mapping step** (different CSVs may have different structures)
- Each uploaded file appears as a **separate card** showing filename, valid/invalid counts, and a remove button
- Files can be individually removed without affecting others
- All file data merges into the campaign alongside manual numbers and contact book selections

## Current Code Structure (Key Reference Points)

All changes are in a single file: `resources/views/quicksms/messages/send-message.blade.php`

### HTML Elements (lines ~152-204)

```html
<!-- Line 165-166: Manual entry textarea — NO CHANGES NEEDED -->
<textarea class="form-control mb-3" id="manualNumbers" rows="4" ...></textarea>

<!-- Line 174-181: Upload button + hidden file input — NEEDS CHANGES -->
<button type="button" class="btn btn-outline-primary" onclick="triggerFileUpload()">
    <i class="fas fa-upload me-1"></i>Upload CSV
</button>
<input type="file" class="d-none" id="recipientFile" accept=".csv,.xlsx,.xls" onchange="handleFileSelect()">

<!-- Lines 184-193: Single upload progress + result — NEEDS REPLACEMENT -->
<div class="d-none mb-3" id="uploadProgress">
    <div class="progress mb-2" style="height: 6px;"><div class="progress-bar" id="uploadProgressBar" style="width: 0%;"></div></div>
    <span id="uploadStatus" class="text-muted">Processing...</span>
</div>
<div class="d-none mb-3" id="uploadResult">
    <span class="badge bg-light text-dark me-2"><i class="fas fa-file-csv me-1"></i>File uploaded</span>
    <span class="text-success"><i class="fas fa-check-circle me-1"></i><span id="uploadValid">0</span> valid</span>
    <span class="text-danger ms-2"><i class="fas fa-times-circle me-1"></i><span id="uploadInvalid">0</span> invalid</span>
    <a href="#" class="ms-2 d-none" id="uploadInvalidLink" onclick="showInvalidNumbers('upload')">View</a>
</div>
```

### JavaScript State (line ~2903)

```javascript
// CURRENT — flat single-file structure
var recipientState = {
    manual: { valid: [], invalid: [] },
    upload: { valid: [], invalid: [] },  // <-- This needs to change
    contactBook: { contacts: [], lists: [], dynamicLists: [], tags: [] },
    ukMode: true,
    convert07: true
};
```

### JavaScript Functions That Need Changes

| Function | Line | What it does now | What needs to change |
|----------|------|-----------------|---------------------|
| `triggerFileUpload()` | ~3044 | Clicks hidden file input | Add file count check (max 5) |
| `handleFileSelect()` | ~3048 | Grabs `files[0]` | Process each selected file |
| `processFileUpload()` | ~3055 | Shows progress, opens column modal | Queue per-file processing |
| `confirmColumnMapping()` | ~3081 | Replaces `recipientState.upload` with mock data | Appends to `files[]` array for current file |
| `updateRecipientSummary()` | ~3189 | Uses `recipientState.upload.valid.length` | Sum across all `files[].valid.length` |
| `showInvalidNumbers()` | ~3205 | Uses `recipientState.upload.invalid` | Concat invalid from all files |
| `showAllInvalidNumbers()` | ~3210 | Concats `manual.invalid` + `upload.invalid` | Concat across all files |
| `collectCampaignConfig()` | ~2596 | Single `uploadCount` | Sum across all files; build multiple CSV source entries |

### Validation function (line ~2782)

```javascript
// CURRENT — checks recipientState.upload.valid
var hasRecipients = manualNumbers.length > 0 ||
    (recipientState && recipientState.manual && recipientState.manual.valid && recipientState.manual.valid.length > 0) ||
    (recipientState && recipientState.upload && recipientState.upload.valid && recipientState.upload.valid.length > 0) ||
    // ... contactBook checks
```

### Column Mapping Modal (lines ~617-657)

The modal at `#columnMappingModal` is currently static/hardcoded. It needs to:
- Show the **filename** being mapped in the modal title
- Dynamically populate the column mapping table from the actual file headers
- Track which file is currently being mapped

---

## Implementation Steps

### Step 1: Update `recipientState` Structure

Replace the flat `upload` bucket with a `files` array:

```javascript
var recipientState = {
    manual: { valid: [], invalid: [] },
    files: [],  // Array of file objects (was: upload: { valid: [], invalid: [] })
    contactBook: { contacts: [], lists: [], dynamicLists: [], tags: [] },
    ukMode: true,
    convert07: true
};
```

Each file object in `files[]`:

```javascript
{
    id: 'file_' + Date.now() + '_' + index,  // Unique ID for removal
    name: 'contacts_uk.csv',                   // Original filename
    size: 45231,                                // File size in bytes
    valid: ['+447700900111', ...],              // Validated numbers
    invalid: [{ row: 6, original: '...', reason: '...' }, ...],  // Invalid entries
    data: [                                     // Full CSV data with column mapping applied
        { mobile_number: '+447700900111', first_name: 'John', last_name: 'Smith' },
        ...
    ],
    columnMapping: { 0: 'mobile', 1: 'firstname', 2: 'lastname' }  // Column mapping used
}
```

### Step 2: Update the File Input HTML

Replace the current file input (line ~181):

```html
<!-- Add multiple attribute, keep max 5 enforced in JS -->
<input type="file" class="d-none" id="recipientFile" accept=".csv,.xlsx,.xls" multiple onchange="handleFileSelect()">
```

Update the upload button to show remaining file count:

```html
<button type="button" class="btn btn-outline-primary" id="uploadCsvBtn" onclick="triggerFileUpload()">
    <i class="fas fa-upload me-1"></i>Upload CSV <span class="d-none" id="fileCountBadge" class="badge bg-secondary ms-1"></span>
</button>
```

### Step 3: Replace Single Upload Result with File Cards Container

Remove lines 184-193 (the single `uploadProgress` and `uploadResult` divs) and replace with:

```html
<!-- Container for individual file cards -->
<div id="uploadedFilesContainer" class="mb-3"></div>

<!-- Processing indicator (shown while any file is being processed) -->
<div class="d-none mb-3" id="fileProcessingIndicator">
    <div class="d-flex align-items-center gap-2">
        <div class="spinner-border spinner-border-sm" style="color: #886CC0;" role="status"></div>
        <span class="text-muted" id="fileProcessingStatus">Processing file...</span>
    </div>
</div>
```

### Step 4: Create the File Card Rendering Function

```javascript
function renderUploadedFiles() {
    var container = document.getElementById('uploadedFilesContainer');
    if (recipientState.files.length === 0) {
        container.innerHTML = '';
        return;
    }

    var html = '';
    recipientState.files.forEach(function(file) {
        var sizeStr = file.size < 1024 ? file.size + ' B' :
                      file.size < 1048576 ? (file.size / 1024).toFixed(1) + ' KB' :
                      (file.size / 1048576).toFixed(1) + ' MB';

        html += '<div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded border" id="file-card-' + file.id + '">' +
            '<div class="d-flex align-items-center gap-2">' +
                '<i class="fas fa-file-csv" style="color: #886CC0;"></i>' +
                '<div>' +
                    '<div class="fw-medium" style="font-size: 13px;">' + escapeHtml(file.name) + ' <span class="text-muted fw-normal">(' + sizeStr + ')</span></div>' +
                    '<div style="font-size: 12px;">' +
                        '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + file.valid.length + ' valid</span>' +
                        (file.invalid.length > 0 ?
                            '<span class="text-danger ms-2"><i class="fas fa-times-circle me-1"></i>' + file.invalid.length + ' invalid</span>' +
                            '<a href="#" class="ms-2" onclick="showFileInvalidNumbers(\'' + file.id + '\')" style="font-size: 12px;">View</a>'
                            : '') +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<button class="btn btn-sm btn-outline-danger border-0" onclick="removeUploadedFile(\'' + file.id + '\')" title="Remove file">' +
                '<i class="fas fa-times"></i>' +
            '</button>' +
        '</div>';
    });

    container.innerHTML = html;

    // Update upload button state
    updateUploadButtonState();
}

function escapeHtml(text) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
```

### Step 5: Update `triggerFileUpload()`

```javascript
function triggerFileUpload() {
    if (recipientState.files.length >= 5) {
        // Show a toast or alert
        showToast('Maximum 5 files allowed. Remove a file to upload another.', 'warning');
        return;
    }
    // Reset the input so the same file can be re-selected if needed
    var fileInput = document.getElementById('recipientFile');
    fileInput.value = '';
    fileInput.click();
}

function updateUploadButtonState() {
    var btn = document.getElementById('uploadCsvBtn');
    var count = recipientState.files.length;
    if (count >= 5) {
        btn.disabled = true;
        btn.title = 'Maximum 5 files reached';
    } else {
        btn.disabled = false;
        btn.title = '';
    }
    // Optional: show count on button
    btn.innerHTML = '<i class="fas fa-upload me-1"></i>Upload CSV' +
        (count > 0 ? ' <span class="badge rounded-pill" style="background-color: #886CC0;">' + count + '/5</span>' : '');
}
```

### Step 6: Update `handleFileSelect()` and `processFileUpload()`

The key change: handle multiple files, enforce the 5-file cap, and process files **sequentially** (each opens its own column mapping modal).

```javascript
// Queue of files waiting to be column-mapped
var pendingFiles = [];
var currentMappingFile = null;  // The file currently in the column mapping modal

function handleFileSelect() {
    var fileInput = document.getElementById('recipientFile');
    if (!fileInput.files.length) return;

    var selectedFiles = Array.from(fileInput.files);
    var remaining = 5 - recipientState.files.length;

    if (selectedFiles.length > remaining) {
        showToast('Only ' + remaining + ' more file(s) can be added. First ' + remaining + ' will be used.', 'warning');
        selectedFiles = selectedFiles.slice(0, remaining);
    }

    // Add all selected files to the pending queue
    selectedFiles.forEach(function(file) {
        pendingFiles.push(file);
    });

    // Start processing the queue (opens column mapping for first file)
    processNextPendingFile();
}

function processNextPendingFile() {
    if (pendingFiles.length === 0) {
        document.getElementById('fileProcessingIndicator').classList.add('d-none');
        return;
    }

    var file = pendingFiles.shift();
    currentMappingFile = file;

    document.getElementById('fileProcessingIndicator').classList.remove('d-none');
    document.getElementById('fileProcessingStatus').textContent = 'Reading ' + file.name + '...';

    // Parse the file to extract headers and sample data
    parseFileForMapping(file, function(headers, sampleRow, allRows) {
        document.getElementById('fileProcessingIndicator').classList.add('d-none');

        // Store parsed data on the file object for use after mapping is confirmed
        currentMappingFile._parsedHeaders = headers;
        currentMappingFile._parsedRows = allRows;

        // Populate and show the column mapping modal
        populateColumnMappingModal(file.name, headers, sampleRow);
        var modal = new bootstrap.Modal(document.getElementById('columnMappingModal'));
        modal.show();
    });
}
```

### Step 7: File Parsing (CSV)

```javascript
function parseFileForMapping(file, callback) {
    var reader = new FileReader();
    reader.onload = function(e) {
        var text = e.target.result;
        var rows = parseCSV(text);  // See helper below

        if (rows.length === 0) {
            showToast('File "' + file.name + '" appears to be empty.', 'error');
            processNextPendingFile();  // Skip to next file
            return;
        }

        var hasHeaders = document.getElementById('hasHeaders').checked;
        var headers = hasHeaders ? rows[0] : rows[0].map(function(_, i) { return 'Column ' + String.fromCharCode(65 + i); });
        var sampleRow = hasHeaders ? (rows[1] || []) : rows[0];
        var dataRows = hasHeaders ? rows.slice(1) : rows;

        callback(headers, sampleRow, dataRows);
    };
    reader.onerror = function() {
        showToast('Failed to read file "' + file.name + '".', 'error');
        processNextPendingFile();
    };
    reader.readAsText(file);
}

function parseCSV(text) {
    // Simple CSV parser — handles quoted fields with commas
    var rows = [];
    var current = [];
    var field = '';
    var inQuotes = false;

    for (var i = 0; i < text.length; i++) {
        var ch = text[i];
        var next = text[i + 1];

        if (inQuotes) {
            if (ch === '"' && next === '"') {
                field += '"';
                i++;  // skip escaped quote
            } else if (ch === '"') {
                inQuotes = false;
            } else {
                field += ch;
            }
        } else {
            if (ch === '"') {
                inQuotes = true;
            } else if (ch === ',') {
                current.push(field.trim());
                field = '';
            } else if (ch === '\n' || (ch === '\r' && next === '\n')) {
                current.push(field.trim());
                if (current.some(function(f) { return f !== ''; })) {
                    rows.push(current);
                }
                current = [];
                field = '';
                if (ch === '\r') i++;  // skip \n after \r
            } else {
                field += ch;
            }
        }
    }
    // Last field
    current.push(field.trim());
    if (current.some(function(f) { return f !== ''; })) {
        rows.push(current);
    }

    return rows;
}
```

**Note on XLSX/XLS:** The current codebase does not include an XLSX parser library. For now, CSV parsing is sufficient. For Excel files, either:
- Add SheetJS (xlsx.js) via CDN: `<script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>`
- Or show a toast: "Please save your Excel file as CSV before uploading."

If adding SheetJS, wrap `parseFileForMapping` to detect `.xlsx`/`.xls` extensions and use `XLSX.read()` instead of `FileReader.readAsText()`.

### Step 8: Dynamic Column Mapping Modal

Update the column mapping modal title to show the filename, and dynamically populate the table:

```javascript
function populateColumnMappingModal(filename, headers, sampleRow) {
    // Update title to show filename
    document.querySelector('#columnMappingModal .modal-title').innerHTML =
        '<i class="fas fa-columns me-2"></i>Map Columns — <span class="text-muted">' + escapeHtml(filename) + '</span>';

    // Auto-detect common column name patterns
    var autoMap = {
        'mobile': 'mobile', 'phone': 'mobile', 'mobile_number': 'mobile', 'phone_number': 'mobile',
        'tel': 'mobile', 'telephone': 'mobile', 'cell': 'mobile', 'number': 'mobile', 'msisdn': 'mobile',
        'first_name': 'firstname', 'firstname': 'firstname', 'first': 'firstname', 'given_name': 'firstname',
        'last_name': 'lastname', 'lastname': 'lastname', 'last': 'lastname', 'surname': 'lastname', 'family_name': 'lastname',
        'email': 'email', 'email_address': 'email'
    };

    var fieldOptions = [
        { value: '', label: '-- Skip --' },
        { value: 'mobile', label: 'Mobile Number *' },
        { value: 'firstname', label: 'First Name' },
        { value: 'lastname', label: 'Last Name' },
        { value: 'email', label: 'Email' }
    ];

    var tableBody = document.getElementById('columnMappingTable');
    var html = '';

    headers.forEach(function(header, idx) {
        var normalised = header.toLowerCase().replace(/[\s\-]+/g, '_').trim();
        var detected = autoMap[normalised] || '';
        var sample = sampleRow[idx] || '';

        html += '<tr>' +
            '<td>' + escapeHtml(header) + '</td>' +
            '<td><select class="form-select form-select-sm column-mapping-select" data-col-index="' + idx + '">';
        fieldOptions.forEach(function(opt) {
            var selected = (opt.value === detected) ? ' selected' : '';
            html += '<option value="' + opt.value + '"' + selected + '>' + opt.label + '</option>';
        });
        html += '</select></td>' +
            '<td class="text-muted">' + escapeHtml(sample) + '</td>' +
            '</tr>';
    });

    tableBody.innerHTML = html;

    // Check for Excel zero-stripping pattern
    var isExcel = currentMappingFile && (currentMappingFile.name.endsWith('.xlsx') || currentMappingFile.name.endsWith('.xls'));
    var hasNumbersStartingWith7 = sampleRow.some(function(v) { return /^7\d{9}$/.test(String(v)); });
    document.getElementById('excelZeroWarning').classList.toggle('d-none', !(isExcel && hasNumbersStartingWith7));
}
```

### Step 9: Update `confirmColumnMapping()`

```javascript
function confirmColumnMapping() {
    // Read column mappings from the modal
    var mappings = {};
    document.querySelectorAll('.column-mapping-select').forEach(function(sel) {
        var colIndex = parseInt(sel.dataset.colIndex);
        var field = sel.value;
        if (field) {
            mappings[colIndex] = field;
        }
    });

    // Validate: mobile column must be mapped
    var hasMobile = Object.values(mappings).indexOf('mobile') !== -1;
    if (!hasMobile) {
        showToast('You must map at least one column to "Mobile Number".', 'error');
        return;
    }

    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('columnMappingModal')).hide();

    // Apply mapping to parsed rows
    var fieldMap = { mobile: 'mobile_number', firstname: 'first_name', lastname: 'last_name', email: 'email' };
    var mobileColIndex = null;
    Object.keys(mappings).forEach(function(idx) {
        if (mappings[idx] === 'mobile') mobileColIndex = parseInt(idx);
    });

    var valid = [];
    var invalid = [];
    var data = [];  // Full row data for backend
    var seen = new Set();
    var fixExcelZeros = document.getElementById('fixExcelZeros') && document.getElementById('fixExcelZeros').checked;

    currentMappingFile._parsedRows.forEach(function(row, rowIndex) {
        var rawNumber = row[mobileColIndex] || '';
        if (!rawNumber.trim()) return;

        // Apply Excel zero-fix if enabled
        if (fixExcelZeros && /^7\d{9}$/.test(rawNumber.trim())) {
            rawNumber = '0' + rawNumber.trim();
        }

        var result = normalizeNumber(rawNumber.trim().replace(/[^\d+]/g, ''));

        // Build the row data object from mapping
        var rowData = {};
        Object.keys(mappings).forEach(function(colIdx) {
            var field = fieldMap[mappings[colIdx]] || mappings[colIdx];
            rowData[field] = row[parseInt(colIdx)] || '';
        });

        if (result.valid && !seen.has(result.number)) {
            seen.add(result.number);
            valid.push(result.number);
            rowData.mobile_number = result.number;
            data.push(rowData);
        } else if (!result.valid) {
            invalid.push({
                row: rowIndex + 1,  // 1-indexed for display
                original: rawNumber,
                reason: result.reason || 'Invalid number'
            });
        }
        // Duplicates within the same file are silently skipped (dedup)
    });

    // Create the file entry
    var fileEntry = {
        id: 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
        name: currentMappingFile.name,
        size: currentMappingFile.size,
        valid: valid,
        invalid: invalid,
        data: data,
        columnMapping: mappings
    };

    recipientState.files.push(fileEntry);
    currentMappingFile = null;

    // Render the updated file list
    renderUploadedFiles();
    updateRecipientSummary();

    // Process next pending file (if user selected multiple)
    processNextPendingFile();
}
```

### Step 10: File Removal Function

```javascript
function removeUploadedFile(fileId) {
    recipientState.files = recipientState.files.filter(function(f) { return f.id !== fileId; });
    renderUploadedFiles();
    updateRecipientSummary();
}
```

### Step 11: Show Invalid Numbers Per File

```javascript
function showFileInvalidNumbers(fileId) {
    var file = recipientState.files.find(function(f) { return f.id === fileId; });
    if (file) {
        showInvalidNumbersTable(file.invalid, file.name);
    }
}
```

Update `showInvalidNumbers()` and `showAllInvalidNumbers()`:

```javascript
function showInvalidNumbers(source) {
    if (source === 'manual') {
        showInvalidNumbersTable(recipientState.manual.invalid, 'Manual Entry');
    } else {
        // Show all invalid from all files combined
        var allFileInvalid = [];
        recipientState.files.forEach(function(f) {
            f.invalid.forEach(function(inv) {
                allFileInvalid.push({ row: inv.row, original: inv.original, reason: inv.reason + ' (' + f.name + ')' });
            });
        });
        showInvalidNumbersTable(allFileInvalid, 'All Files');
    }
}

function showAllInvalidNumbers() {
    var allInvalid = recipientState.manual.invalid.slice();
    recipientState.files.forEach(function(f) {
        f.invalid.forEach(function(inv) {
            allInvalid.push({ row: inv.row, original: inv.original, reason: inv.reason + ' (' + f.name + ')' });
        });
    });
    showInvalidNumbersTable(allInvalid, 'All Sources');
}
```

### Step 12: Update `updateRecipientSummary()`

```javascript
function updateRecipientSummary() {
    var manualValid = recipientState.manual.valid.length;

    // Sum valid numbers across all uploaded files
    var uploadValid = 0;
    recipientState.files.forEach(function(f) { uploadValid += f.valid.length; });

    var contactBookCount = (recipientState.contactBook.contacts.length * 1) +
                          (recipientState.contactBook.lists.length * 1234) +
                          (recipientState.contactBook.dynamicLists.length * 2000) +
                          (recipientState.contactBook.tags.length * 500);

    var totalValid = manualValid + uploadValid + contactBookCount;

    document.getElementById('recipientCount').textContent = totalValid;
    document.getElementById('previewRecipients').textContent = totalValid;

    updatePreviewCost();
}
```

### Step 13: Update Validation (`hasRecipients` Check)

Around line ~2782, update the upload check:

```javascript
// OLD: recipientState.upload.valid.length > 0
// NEW: check files array
var hasUploadedFiles = recipientState.files.some(function(f) { return f.valid.length > 0; });

var hasRecipients = manualNumbers.length > 0 ||
    (recipientState && recipientState.manual && recipientState.manual.valid && recipientState.manual.valid.length > 0) ||
    hasUploadedFiles ||
    (recipientState && recipientState.contactBook && (
        recipientState.contactBook.contacts.length > 0 ||
        recipientState.contactBook.lists.length > 0 ||
        recipientState.contactBook.dynamicLists.length > 0 ||
        recipientState.contactBook.tags.length > 0
    ));
```

### Step 14: Update `collectCampaignConfig()` (line ~2596)

The `recipients` list and `sources` object need to aggregate across all files:

```javascript
// Replace the upload section in collectCampaignConfig()

// Sum across all uploaded files
var uploadCount = 0;
var uploadInvalidCount = 0;
recipientState.files.forEach(function(f) {
    recipientsList = recipientsList.concat(f.valid);
    uploadCount += f.valid.length;
    uploadInvalidCount += f.invalid.length;
});

// ... later in the return object:
return {
    // ... other fields ...
    recipients: recipientsList,
    recipient_count: totalRecipientCount,
    valid_count: totalRecipientCount,
    invalid_count: recipientState.manual.invalid.length + uploadInvalidCount,
    sources: {
        manual_input: manualCount,
        file_upload: uploadCount,
        file_count: recipientState.files.length,  // How many files were uploaded
        contacts: contactsCount,
        lists: listsCount,
        dynamic_lists: dynamicListsCount,
        tags: tagsCount
    }
};
```

### Step 15: Update Campaign Submission to Backend

When building `recipient_sources` for the backend API, each uploaded file becomes a **separate CSV source entry**. This is critical — the backend `RecipientResolverService.expandSources()` already iterates sources and handles multiple `csv` entries.

In the function that submits to `POST /api/campaigns` (or wherever `recipient_sources` is built):

```javascript
// Build recipient_sources array for the backend
var recipientSources = [];

// Manual numbers
if (recipientState.manual.valid.length > 0) {
    recipientSources.push({
        type: 'manual',
        numbers: recipientState.manual.valid
    });
}

// Each file becomes its own CSV source
recipientState.files.forEach(function(file) {
    if (file.data.length > 0) {
        recipientSources.push({
            type: 'csv',
            data: file.data,
            filename: file.name  // For audit/display purposes
        });
    }
});

// Contact book sources
recipientState.contactBook.contacts.forEach(function(c) {
    recipientSources.push({ type: 'individual', contact_ids: [c.id || c] });
});
recipientState.contactBook.lists.forEach(function(l) {
    recipientSources.push({ type: 'list', id: l.id || l });
});
recipientState.contactBook.dynamicLists.forEach(function(dl) {
    recipientSources.push({ type: 'list', id: dl.id || dl });
});
recipientState.contactBook.tags.forEach(function(t) {
    recipientSources.push({ type: 'tag', id: t.id || t });
});
```

### Step 16: Update Summary Display in Confirm Flow (line ~2825)

```javascript
// Replace the line that uses recipientState.upload.valid.length
// OLD:
// var uploadCount = recipientState.upload.valid.length;
// NEW:
var uploadCount = 0;
recipientState.files.forEach(function(f) { uploadCount += f.valid.length; });

// OLD:
// var invalidCount = recipientState.manual.invalid.length + recipientState.upload.invalid.length;
// NEW:
var uploadInvalidCount = 0;
recipientState.files.forEach(function(f) { uploadInvalidCount += f.invalid.length; });
var invalidCount = recipientState.manual.invalid.length + uploadInvalidCount;
```

### Step 17: Handle Column Mapping Modal Cancel

When the user cancels the column mapping modal, skip the current file and process the next one:

```javascript
// Add an event listener for the modal being hidden (cancelled)
document.getElementById('columnMappingModal').addEventListener('hidden.bs.modal', function() {
    // If currentMappingFile is still set, user cancelled (didn't confirm)
    if (currentMappingFile) {
        currentMappingFile = null;
        processNextPendingFile();  // Move to next file in queue
    }
});
```

### Step 18: Update `revalidateNumbers()` (line ~3038)

```javascript
function revalidateNumbers() {
    validateManualNumbers();
    if (recipientState.files.length > 0) {
        // Re-validate all uploaded file numbers with new UK mode
        recipientState.files.forEach(function(file) {
            var valid = [];
            var invalid = [];
            var seen = new Set();

            file.data.forEach(function(row, idx) {
                var rawNumber = row.mobile_number || '';
                if (!rawNumber.trim()) return;
                var result = normalizeNumber(rawNumber.trim().replace(/[^\d+]/g, ''));
                if (result.valid && !seen.has(result.number)) {
                    seen.add(result.number);
                    valid.push(result.number);
                } else if (!result.valid) {
                    invalid.push({ row: idx + 1, original: rawNumber, reason: result.reason });
                }
            });

            file.valid = valid;
            file.invalid = invalid;
        });

        renderUploadedFiles();
        updateRecipientSummary();
    }
}
```

---

## Visual Design Guidelines

### File Card Style

Each uploaded file card should match the existing UI style:
- Use the project's purple accent: `#886CC0` for icons, `#f0ebf8` background for highlights
- Border: `1px solid #dee2e6` (Bootstrap default)
- Rounded corners: `border-radius: 0.375rem`
- Font sizes: filename at 13px, counts at 12px
- Remove button: outline-danger, no border, just an `fa-times` icon

### Upload Button with Counter

When files are uploaded, the button shows a pill badge:
```
[Upload CSV (3/5)]
```
When 5 files are uploaded, button becomes disabled with a tooltip.

### File Card Layout

```
┌─────────────────────────────────────────────────────────┐
│ 📄 contacts_uk.csv (45.2 KB)                        [×] │
│    ✓ 1,234 valid  ✗ 12 invalid  View                    │
├─────────────────────────────────────────────────────────┤
│ 📄 vip_customers.csv (12.1 KB)                       [×] │
│    ✓ 567 valid                                           │
├─────────────────────────────────────────────────────────┤
│ 📄 promo_list_march.xlsx (89.3 KB)                   [×] │
│    ✓ 2,340 valid  ✗ 3 invalid  View                     │
└─────────────────────────────────────────────────────────┘
```

---

## Summary of All Changes

| What | Where | Change |
|------|-------|--------|
| `recipientState` | Line ~2903 | Replace `upload: {valid, invalid}` with `files: []` |
| File input | Line ~181 | Add `multiple` attribute |
| Upload button | Line ~175 | Add `id="uploadCsvBtn"`, show file count badge |
| Upload progress/result divs | Lines ~184-193 | Replace with `#uploadedFilesContainer` + `#fileProcessingIndicator` |
| `triggerFileUpload()` | Line ~3044 | Add max-5 check |
| `handleFileSelect()` | Line ~3048 | Handle `files[]` array, enforce limit, queue processing |
| `processFileUpload()` | Line ~3055 | Replace with `processNextPendingFile()` |
| `confirmColumnMapping()` | Line ~3081 | Build file entry from mapping, append to `files[]`, process next |
| `updateRecipientSummary()` | Line ~3189 | Sum `.valid.length` across all `files[]` |
| `showInvalidNumbers()` | Line ~3205 | Concat invalid from all files |
| `showAllInvalidNumbers()` | Line ~3210 | Include all files' invalid entries |
| `collectCampaignConfig()` | Line ~2596 | Sum uploads across files, build multiple CSV sources |
| Validation check | Line ~2782 | Check `files.some(f => f.valid.length > 0)` |
| Confirm flow counts | Line ~2825 | Sum across files |
| `revalidateNumbers()` | Line ~3038 | Re-validate all file numbers |
| Column mapping modal | Lines ~617-657 | Dynamic population from file headers |
| New functions | — | `renderUploadedFiles()`, `removeUploadedFile()`, `showFileInvalidNumbers()`, `parseFileForMapping()`, `parseCSV()`, `populateColumnMappingModal()`, `updateUploadButtonState()`, `processNextPendingFile()`, `escapeHtml()` |

## What NOT to Change

- **Manual entry textarea** — keep as-is (the user confirmed textarea is fine)
- **Contact book modal** — already supports multi-select, no changes needed
- **Backend PHP code** — `RecipientResolverService.expandSources()` already handles multiple CSV source entries
- **Column mapping modal HTML structure** — keep the same modal, just dynamically populate its content
- **The `normalizeNumber()` function** — reuse it for file number validation
- **The `showInvalidNumbersTable()` function** — reuse it, just pass different data

## Testing Checklist

1. Upload a single CSV file → card appears with correct counts
2. Upload 3 files at once (select multiple in file picker) → column mapping modal appears for each sequentially
3. Upload 5 files → upload button shows "5/5" and becomes disabled
4. Try to upload a 6th file → shows warning toast, button stays disabled
5. Remove a file card → count updates, button re-enables
6. Cancel column mapping on file 2 of 3 → skips to file 3
7. Mix all three sources: paste manual numbers + upload 2 files + select from contact book → total count sums correctly
8. Change UK mode toggle → all file numbers re-validate
9. Click "View" on invalid numbers for a specific file → shows only that file's invalid entries
10. Click "View All Invalid" → shows combined invalid from manual + all files
11. Continue to confirm page → all sources appear in campaign config
12. Upload empty CSV → shows toast error, skips to next file
13. Upload CSV with no mobile column mapped → shows error, doesn't close modal
14. Upload Excel file → either parses with SheetJS or shows "save as CSV" toast
