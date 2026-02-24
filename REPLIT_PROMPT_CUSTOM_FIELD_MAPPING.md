# Replit Prompt: Custom CSV Column Mapping & Placeholder Integration

## Overview

This prompt extends the multi-file upload work (see `REPLIT_PROMPT_MULTI_FILE_UPLOAD.md`) to allow users to map arbitrary CSV columns as **custom fields** and use them as **personalisation placeholders** in their message content.

**The problem today:** The column mapping modal only offers 4 fixed fields (Mobile, First Name, Last Name, Email). Any extra CSV columns (e.g., `Appointment Date`, `company`, `amount_due`) can only be skipped — there's no way to keep them and use them as `{{Appointment Date}}` in the message.

**The backend already supports this.** `RecipientResolverService::expandCsvData()` already puts unknown columns into `custom_data`, and `CampaignRecipient::resolveContent()` has been updated (on this branch) to resolve custom fields with flat access — `{{Appointment Date}}` resolves directly without any prefix. **No backend changes are needed from this prompt — only frontend changes.**

## What This Prompt Changes

All changes are in a single file: `resources/views/quicksms/messages/send-message.blade.php`

| Change | Description |
|--------|-------------|
| Column mapping dropdown | Add "Custom Field (keep as-is)" option |
| Auto-detect default | Unrecognized columns default to "custom" instead of "skip" |
| Custom field tracking | Each file object gains a `customFields[]` array |
| CSV field buttons | Dynamically populate `#csvFieldButtons` in personalisation modal |
| Built-in field buttons | Fix camelCase → snake_case to match backend |
| Hardcoded custom fields | Remove fake static examples (appointmentDate, clinicName, etc.) |
| Intersection logic | Only show custom fields present in ALL uploaded files |

---

## Step 1: Add "Custom Field" Option to Column Mapping Dropdown

In the `populateColumnMappingModal()` function (from the multi-file upload prompt, Step 8), update the `fieldOptions` array:

```javascript
// BEFORE:
var fieldOptions = [
    { value: '',        label: '-- Skip --' },
    { value: 'mobile',  label: 'Mobile Number *' },
    { value: 'firstname', label: 'First Name' },
    { value: 'lastname',  label: 'Last Name' },
    { value: 'email',    label: 'Email' }
];

// AFTER:
var fieldOptions = [
    { value: '',        label: '-- Skip --' },
    { value: 'mobile',  label: 'Mobile Number *' },
    { value: 'firstname', label: 'First Name' },
    { value: 'lastname',  label: 'Last Name' },
    { value: 'email',    label: 'Email' },
    { value: 'custom',   label: 'Custom Field (keep as-is)' }
];
```

## Step 2: Default Unrecognized Columns to "Custom" Instead of "Skip"

In the same `populateColumnMappingModal()` function, change the auto-detect fallback:

```javascript
// BEFORE:
var detected = autoMap[normalised] || '';

// AFTER — unrecognized columns become custom fields by default:
var detected = header.trim() === '' ? '' : (autoMap[normalised] || 'custom');
```

This means when a user uploads a CSV with columns like `phone, first_name, Appointment Date, Amount Due`, the mapping modal will auto-detect:
- `phone` → Mobile Number (via autoMap)
- `first_name` → First Name (via autoMap)
- `Appointment Date` → Custom Field (unrecognized, defaults to custom)
- `Amount Due` → Custom Field (unrecognized, defaults to custom)

The user can still manually change any column to "-- Skip --" if they want to ignore it.

## Step 3: Track Custom Fields in `confirmColumnMapping()`

In `confirmColumnMapping()` (from the multi-file upload prompt, Step 9), add custom field tracking. This modifies the function in two places:

### 3a: Collect custom field names from the mapping selections

After reading column mappings from the modal, build a `customFieldNames` array:

```javascript
function confirmColumnMapping() {
    var mappings = {};
    var customFieldNames = [];  // NEW: track custom field header names

    document.querySelectorAll('.column-mapping-select').forEach(function(sel) {
        var colIndex = parseInt(sel.dataset.colIndex);
        var field = sel.value;
        if (field) {
            mappings[colIndex] = field;
        }
        // Track custom field names using the original CSV header
        if (field === 'custom') {
            var headerText = currentMappingFile._parsedHeaders[colIndex];
            if (headerText && headerText.trim() !== '') {
                customFieldNames.push(headerText.trim());
            }
        }
    });

    // ... existing mobile validation check ...
```

### 3b: Handle "custom" mapping when building row data

In the loop that builds `rowData` from each parsed row, handle the `'custom'` mapping value:

```javascript
    // The fieldMap for known fields (same as multi-file prompt)
    var fieldMap = { mobile: 'mobile_number', firstname: 'first_name', lastname: 'last_name', email: 'email' };

    currentMappingFile._parsedRows.forEach(function(row, rowIndex) {
        // ... existing mobile number extraction ...

        // Build the row data object from mapping
        var rowData = {};
        Object.keys(mappings).forEach(function(colIdx) {
            var mappingValue = mappings[parseInt(colIdx)];
            if (mappingValue === 'custom') {
                // Use the ORIGINAL CSV header text as the key (as-is)
                var headerName = currentMappingFile._parsedHeaders[parseInt(colIdx)];
                if (headerName && headerName.trim() !== '') {
                    rowData[headerName.trim()] = row[parseInt(colIdx)] || '';
                }
            } else {
                var field = fieldMap[mappingValue] || mappingValue;
                rowData[field] = row[parseInt(colIdx)] || '';
            }
        });

        // ... existing valid/invalid/dedup logic ...
    });
```

### 3c: Add `customFields` to the file entry

```javascript
    var fileEntry = {
        id: 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5),
        name: currentMappingFile.name,
        size: currentMappingFile.size,
        valid: valid,
        invalid: invalid,
        data: data,
        columnMapping: mappings,
        customFields: customFieldNames  // NEW: array of custom field header names
    };

    recipientState.files.push(fileEntry);
    currentMappingFile = null;

    renderUploadedFiles();
    updateRecipientSummary();
    refreshCsvFieldButtons();  // NEW: refresh personalisation modal buttons
    processNextPendingFile();
}
```

## Step 4: Create `refreshCsvFieldButtons()` Function

This new function computes the intersection of custom field names across all uploaded files and populates the `#csvFieldButtons` container in the personalisation modal.

```javascript
function refreshCsvFieldButtons() {
    var csvSection = document.getElementById('csvFieldsSection');
    var csvButtons = document.getElementById('csvFieldButtons');
    var hint = document.getElementById('noCustomFieldsHint');

    // Get files that have custom fields
    var filesWithCustomFields = recipientState.files.filter(function(f) {
        return f.customFields && f.customFields.length > 0;
    });

    if (filesWithCustomFields.length === 0) {
        csvSection.style.display = 'none';
        csvButtons.innerHTML = '';
        if (hint) hint.style.display = 'block';
        return;
    }

    // Compute intersection: only fields present in ALL files that have custom fields
    var intersection = filesWithCustomFields[0].customFields.slice();
    for (var i = 1; i < filesWithCustomFields.length; i++) {
        var fileFields = filesWithCustomFields[i].customFields;
        intersection = intersection.filter(function(field) {
            return fileFields.indexOf(field) !== -1;
        });
    }

    if (intersection.length === 0) {
        csvSection.style.display = 'none';
        csvButtons.innerHTML = '';
        if (hint) hint.style.display = 'block';
        return;
    }

    // Build buttons using DOM API (safe from injection)
    csvButtons.innerHTML = '';
    intersection.forEach(function(fieldName) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-outline-info btn-sm';
        btn.textContent = '{{' + fieldName + '}}';
        btn.addEventListener('click', function() {
            insertPlaceholder(fieldName);
        });
        csvButtons.appendChild(btn);
        csvButtons.appendChild(document.createTextNode(' '));
    });

    csvSection.style.display = 'block';
    if (hint) hint.style.display = 'none';
}
```

## Step 5: Call `refreshCsvFieldButtons()` When Files Change

Add the call to `removeUploadedFile()` (from multi-file upload prompt, Step 10):

```javascript
function removeUploadedFile(fileId) {
    recipientState.files = recipientState.files.filter(function(f) { return f.id !== fileId; });
    renderUploadedFiles();
    updateRecipientSummary();
    refreshCsvFieldButtons();  // ADD THIS LINE
}
```

## Step 6: Fix Personalisation Modal Built-in Field Buttons

**Current HTML (lines ~728-733) — camelCase names that DON'T match the backend:**

```html
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('firstName')">{{firstName}}</button>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('lastName')">{{lastName}}</button>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('fullName')">{{fullName}}</button>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('mobile')">{{mobile}}</button>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('email')">{{email}}</button>
```

**Replace with snake_case names that match the backend:**

```html
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('first_name')">{{first_name}}</button>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('last_name')">{{last_name}}</button>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('full_name')">{{full_name}}</button>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('mobile_number')">{{mobile_number}}</button>
<button type="button" class="btn btn-outline-primary btn-sm" onclick="insertPlaceholder('email')">{{email}}</button>
```

> **Note:** The backend `resolveContent()` on this branch supports BOTH formats as aliases (`firstName` and `first_name` both work). But going forward, the canonical format is snake_case.

## Step 7: Replace Hardcoded Custom Fields with Dynamic Hint

**Current HTML (lines ~735-743) — fake static examples:**

```html
<div class="mb-3">
    <h6 class="text-muted mb-2">Custom Fields</h6>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('appointmentDate')">{{appointmentDate}}</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('appointmentTime')">{{appointmentTime}}</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('clinicName')">{{clinicName}}</button>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="insertPlaceholder('customField_1')">{{customField_1}}</button>
    </div>
</div>
```

**Replace with a hint that explains where custom fields come from:**

```html
<div class="mb-3" id="noCustomFieldsHint">
    <h6 class="text-muted mb-2">Custom Fields</h6>
    <p class="text-muted mb-0" style="font-size: 12px;">
        <i class="fas fa-info-circle me-1"></i>Upload a CSV file with extra columns and map them as
        <strong>Custom Field</strong> to use them as merge fields here.
    </p>
</div>
```

When `refreshCsvFieldButtons()` runs and finds custom fields, it hides this hint and shows the `#csvFieldsSection` with real buttons instead.

## Step 8: Update `revalidateNumbers()` to Preserve Custom Fields

In the `revalidateNumbers()` function (from multi-file upload prompt, Step 18), when re-validating file numbers after UK mode change, ensure custom field data is preserved. The `file.data` array already contains the full row objects — just re-validate the `mobile_number` field within each row without discarding other fields.

No additional change is needed beyond what's in the multi-file upload prompt, since that implementation re-validates from `file.data[].mobile_number` and only updates `file.valid` and `file.invalid` arrays. The `file.data` (which contains custom field columns) and `file.customFields` remain untouched.

---

## How It All Works End-to-End

### User Flow

1. **User uploads a CSV** with columns: `Phone, First Name, Last Name, Appointment Date, Amount Due`
2. **Column mapping modal** appears with auto-detected mappings:
   - `Phone` → Mobile Number * (auto-detected)
   - `First Name` → First Name (auto-detected)
   - `Last Name` → Last Name (auto-detected)
   - `Appointment Date` → **Custom Field (keep as-is)** (unrecognized, defaults to custom)
   - `Amount Due` → **Custom Field (keep as-is)** (unrecognized, defaults to custom)
3. User clicks **Confirm & Import** → file is processed, card appears
4. **Personalisation modal** now shows:
   - Contact Book Fields: `{{first_name}}`, `{{last_name}}`, `{{full_name}}`, `{{mobile_number}}`, `{{email}}`
   - CSV/Excel Columns: `{{Appointment Date}}`, `{{Amount Due}}`
5. User clicks `{{Appointment Date}}` → inserts into message at cursor
6. Message now reads: `Hi {{first_name}}, your appointment is on {{Appointment Date}}.`
7. **At send time**, the backend resolves per-recipient: `Hi John, your appointment is on 15 March 2024.`

### Data Flow

```
CSV File
  ↓ parseCSV() — client-side parsing
Column Mapping Modal
  ↓ confirmColumnMapping() — applies mapping
file.data = [
  { mobile_number: "+447700900111", first_name: "John", "Appointment Date": "15 March" },
  ...
]
file.customFields = ["Appointment Date", "Amount Due"]
  ↓ collectCampaignConfig() → POST /api/campaigns
recipient_sources: [
  { type: "csv", data: file.data, filename: "contacts.csv" }
]
  ↓ RecipientResolverService::expandCsvData()
campaign_recipient.custom_data = { "Appointment Date": "15 March", "Amount Due": "£50" }
  ↓ CampaignRecipient::resolveContent()
{{Appointment Date}} → "15 March"  (flat lookup in custom_data)
{{first_name}} → "John"            (built-in field)
```

---

## Multi-File Intersection Example

**File 1** (`uk_contacts.csv`): columns `Phone, Name, Appointment Date, Clinic`
→ customFields: `["Appointment Date", "Clinic"]`

**File 2** (`scotland_contacts.csv`): columns `Mobile, Name, Appointment Date, Region`
→ customFields: `["Appointment Date", "Region"]`

**Intersection** = `["Appointment Date"]` — only this appears in the personalisation modal.

`Clinic` and `Region` do NOT appear as buttons because they're not in both files. Recipients from File 1 would have `{{Region}}` resolve to empty, which could cause confusing messages. The intersection rule prevents this.

---

## Summary of All Changes

| What | Where | Change |
|------|-------|--------|
| `fieldOptions` array | `populateColumnMappingModal()` | Add `{ value: 'custom', label: 'Custom Field (keep as-is)' }` |
| Auto-detect fallback | `populateColumnMappingModal()` | Change default from `''` to `'custom'` for unrecognized columns |
| Custom field collection | `confirmColumnMapping()` | Build `customFieldNames[]` from `'custom'` mapped columns |
| Row data building | `confirmColumnMapping()` | Handle `'custom'` mapping: use original CSV header as key |
| File entry | `confirmColumnMapping()` | Add `customFields: customFieldNames` to file object |
| New function | Global | `refreshCsvFieldButtons()` — intersection + dynamic button generation |
| File removal | `removeUploadedFile()` | Call `refreshCsvFieldButtons()` |
| Built-in buttons | Personalisation modal HTML (lines ~728-733) | Fix camelCase → snake_case |
| Fake custom fields | Personalisation modal HTML (lines ~735-743) | Replace with hint text + `id="noCustomFieldsHint"` |

## What NOT to Change

- **Backend PHP** — `resolveContent()` already updated on this branch to support flat custom fields and expanded regex
- **`RecipientResolverService::expandCsvData()`** — already collects unknown columns into `custom_data`
- **`insertPlaceholder()` function** — already works with any field name string, including spaces
- **`handleContentChange()` function** — character counting works fine with placeholder text
- **Manual entry textarea** — no changes
- **Contact book modal** — no changes

## Testing Checklist

1. Upload CSV with extra columns → column mapping shows them defaulting to "Custom Field (keep as-is)"
2. Change one custom column to "-- Skip --" → that column is excluded from customFields
3. Confirm mapping → personalisation modal shows CSV/Excel Columns section with buttons for custom fields
4. Click a custom field button → `{{Appointment Date}}` inserts at cursor in message textarea
5. Upload a second file with different columns → only intersection fields show in personalisation modal
6. Remove a file → custom field buttons update (may add back fields that were excluded by the removed file's intersection)
7. Remove all files → CSV/Excel Columns section hides, hint text reappears
8. Upload CSV where a column header matches a built-in name (`first_name`) → auto-detects as "First Name" not "Custom Field"
9. Column header with spaces (`Appointment Date`) → button displays correctly, inserts correctly, resolves correctly at send time
10. Built-in field buttons → confirm they show `{{first_name}}` not `{{firstName}}`
11. Old templates using `{{firstName}}` → still work (backend has camelCase aliases)
12. Column header with special characters (`Amount (£)`) → button renders safely, no XSS
