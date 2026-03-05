# Email-to-SMS Module — Backend Developer Spec

This document describes everything the frontend currently does so you know exactly what the backend needs to support. It covers the UI layout, data shapes, API contracts, validation, and business logic.

---

## 1. Module Overview

The Email-to-SMS module lets customers configure auto-generated email addresses that convert incoming emails into SMS messages. There are two setup types:

- **Standard** — A direct email-to-mobile mapping. An incoming email to the generated address gets its body extracted and sent as SMS to one or more mobile numbers.
- **Contact List** — An email-to-contact-book mapping. The SMS goes to contacts, lists, dynamic lists, or tags from the Contact Book, with opt-out screening.

Both types share common settings (SenderID, multiple SMS, delivery reports) and appear together in an Overview tab.

---

## 2. Pages & Navigation

| Page | URL | Purpose |
|---|---|---|
| Main Library | `/management/email-to-sms` | 4-tab dashboard (Overview, Standard, Contact List, Reporting Groups) |
| Standard Create Wizard | `/management/email-to-sms/standard/create` | 4-step wizard to create a Standard setup |
| Standard Edit | `/management/email-to-sms/standard/{id}/edit` | 3-step edit form for existing Standard setups |
| Contact List Create Wizard | `/management/email-to-sms/create-mapping` | 5-step wizard to create a Contact List setup |
| Contact List Edit | `/management/email-to-sms/contact-list/{id}/edit` | Edit form for existing Contact List setups |

---

## 3. Main Library Page — Tab by Tab

### 3.1 Overview Tab

Displays ALL Email-to-SMS addresses (Standard + Contact List) in one unified table.

**Table Columns:**

| Column | Description |
|---|---|
| Name | Setup name |
| Allowed Email Addresses | Whitelisted sender emails |
| Type | "Standard" or "Contact List" |
| Reporting Group | Which billing/reporting group it belongs to (or "None") |
| Status | Active / Suspended / Archived (shown as coloured pill badges) |
| Created | Date created (YYYY-MM-DD) |
| Actions | Dropdown: Edit, Suspend/Reactivate, View History, Delete |

**Filters:**
- Quick Search (by name or email address)
- Date Created range (From/To with presets: Last 7 Days, Last 30 Days, This Month, Last Month)
- Sub Account (multi-select dropdown)
- Status (multi-select: Active, Suspended)
- Reporting Group (multi-select)

**Actions:**
- "+ Create" button opens a modal to create a new address
- Clicking a row opens a **Details Drawer** (side panel) showing full configuration, messaging settings, security, and activity data
- Drawer actions: Copy Email, Edit Configuration, View Message Log, Suspend, Delete

**Data shape the frontend expects for each overview item:**
```javascript
{
    id: "uuid",                          // Unique overview-level ID
    sourceId: "uuid",                    // The actual setup UUID
    sourceType: "standard" | "contactList",
    name: "NHS Patient Notifications",
    originatingEmails: ["nhs-patient-1523@sms.quicksms.com"],
    description: "Automated notifications...",
    type: "Standard" | "Contact List",   // Display label
    senderId: "NHS",
    optOut: "Global Opt-Out" | null,     // Human-readable opt-out label
    subAccount: "Main Account",
    reportingGroup: "Appointments" | null,
    allowedSenders: ["admin@nhstrust.nhs.uk"],
    dailyLimit: 5000,
    status: "Active" | "Suspended" | "Archived",
    created: "2024-10-20",               // Date only
    lastUsed: "2025-01-09 10:15",        // DateTime string
    messagesSent: 1234                   // Total messages sent via this address
}
```

---

### 3.2 Standard Tab

Manages Standard-type setups only.

**Table Columns:**

| Column | Width | Description |
|---|---|---|
| Name | 18% | Setup name |
| Originating Emails | 26% | Auto-generated email addresses |
| Subaccount | 12% | Which sub-account owns it |
| Status | 10% | Active / Suspended / Archived |
| Created | 10% | Date created |
| Actions | 14% | Dropdown menu |

**Filters:**
- Quick Search (by name)
- Show Archived toggle
- Date Created range with presets
- Subaccount dropdown (All, Main, Marketing, Support)

**Actions:**
- "+ Create" button navigates to `/management/email-to-sms/standard/create`
- Row actions via dropdown: Edit, Archive/Unarchive, Suspend/Reactivate

**Data shape (Standard setup):**
```javascript
{
    id: "uuid",
    name: "General Notifications",
    description: "General purpose notification emails...",
    subaccountId: "uuid",
    subaccountName: "Main Account",
    originatingEmails: ["general.notify@sms.quicksms.io"],
    allowedEmails: ["admin@company.com", "system@company.com"],
    senderIdTemplateId: "uuid",
    senderId: "QuickSMS",
    multipleSmsEnabled: true,
    deliveryReportsEnabled: true,
    deliveryReportsEmail: "reports@company.com",
    status: "active",
    createdAt: "2024-10-20T10:00:00Z",
    updatedAt: "2025-01-09T10:15:00Z",
    created: "2024-10-20",              // Computed: date portion of createdAt
    lastUpdated: "2025-01-09",          // Computed: date portion of updatedAt
    archived: false                     // Computed: status === 'archived'
}
```

---

### 3.3 Contact List Tab

Manages Contact List-type setups only.

**Table Columns:**

| Column | Description |
|---|---|
| Name | Setup name |
| Originating Emails | Auto-generated email addresses |
| Target Lists | Names of contact lists/tags the SMS is sent to |
| Status | Active / Suspended / Archived |
| Created | Date created |
| Actions | Dropdown menu (centred) |

**Filters:**
- Quick Search
- Show Archived toggle
- Date Created range with presets
- Contact List dropdown (filter by which list is targeted)

**Actions:**
- "+ Create" button navigates to `/management/email-to-sms/create-mapping`
- Row actions: Edit, Archive/Unarchive

**Data shape (Contact List setup):**
```javascript
{
    id: "uuid",
    name: "NHS Patient Notifications",
    description: "Automated notifications to NHS patients",
    subaccountId: "uuid",
    subaccountName: "Main Account",
    originatingEmails: ["nhs-patient.12abc@sms.quicksms.io"],
    allowedSenderEmails: ["admin@nhstrust.nhs.uk", "appointments@nhstrust.nhs.uk"],
    contactBookListIds: ["uuid-1", "uuid-2"],
    contactBookListNames: ["NHS Patients", "Appointment List"],
    optOutMode: "SELECTED" | "NONE",
    optOutListIds: ["uuid"],
    optOutListNames: ["Global Opt-Out"],
    senderIdTemplateId: "uuid",
    senderId: "NHS",
    multipleSmsEnabled: true,
    deliveryReportsEnabled: true,
    deliveryReportsEmail: "nhs-reports@nhstrust.nhs.uk",
    status: "active",
    createdAt: "2024-10-15T09:00:00Z",
    updatedAt: "2025-01-09T10:30:00Z",
    created: "2024-10-15",             // Computed
    lastUpdated: "2025-01-09",         // Computed
    targetLists: ["NHS Patients", "Appointment List"],  // Alias for contactBookListNames
    optOutLists: ["Global Opt-Out"]    // Alias for optOutListNames
}
```

---

### 3.4 Reporting Groups Tab

Groups setups together for billing and analytics attribution. Each Email-to-SMS address can belong to only one reporting group.

**Table Columns:**

| Column | Description |
|---|---|
| Group Name | Name of the reporting group |
| Description | Short description |
| Linked Addresses | Names of setups linked to this group |
| Messages Sent | Total messages sent by all linked addresses |
| Last Activity | DateTime of most recent message |
| Created | Date group was created |
| Actions | Dropdown (right-aligned) |

**Filters:**
- Quick Search (by group name)
- Date Created range with presets
- Status dropdown (All, Active, Archived)

**Actions:**
- "+ Create" opens a modal with fields: Group Name (required), Description (optional), Assign Email-to-SMS Address (dropdown, optional)
- Row actions: Edit, Archive/Unarchive

**Data shape:**
```javascript
{
    id: "uuid",
    name: "Appointments",
    description: "All appointment-related SMS communications",
    linkedAddresses: ["Appointment Reminders"],  // Array of setup names
    messagesSent: 12847,
    lastActivity: "2025-01-09 08:45",
    created: "2024-11-10",
    status: "Active" | "Archived"
}
```

---

## 4. Standard Create Wizard (4 Steps)

URL: `/management/email-to-sms/standard/create`

### Step 1: General
| Field | ID | Type | Validation |
|---|---|---|---|
| Name | `stdName` | Text input | Required, max 50 chars, unique per tenant |
| Description | `stdDescription` | Textarea | Optional, max 200 chars |
| Sub-Account | `stdSubaccountCheck` | Multi-select checkboxes | Required, at least one |

### Step 2: Email Settings
| Field | ID | Type | Validation |
|---|---|---|---|
| Allowed Sender Emails | `stdEmailInput` | Textarea (bulk input) | At least one required; each must be a valid email or wildcard (`*@domain.com`) |

Emails are displayed as removable tags. Supports comma, space, or newline separation. Wildcard entries show a security warning.

### Step 3: Message Settings
| Field | ID | Type | Validation |
|---|---|---|---|
| SenderID | `stdSenderId` | Dropdown | Required, must be approved/live |
| Enable Multiple SMS | `stdMultipleSms` | Toggle switch | Default: ON |
| Send Delivery Reports | `stdDeliveryReports` | Toggle switch | Default: OFF |
| Delivery Report Email | `stdDeliveryEmail` | Email input | Required if reports enabled |

### Step 4: Review & Confirm
- Summary table showing all configuration from Steps 1-3
- Info box: "Email address will be generated and cannot be changed after creation"
- "Create Mapping" button submits

### Success Modal
After creation:
- Shows a check icon and "Email-to-SMS Address Created"
- Displays the auto-generated email address (e.g., `general-notifications-4829@sms.quicksms.com`) in a pastel purple box with monospace black text
- Copy-to-clipboard button
- Info line: "SMS Content: Extracted from email body"
- Buttons: "Close" and "Back to Contact List Library"

---

## 5. Contact List Create Wizard (5 Steps)

URL: `/management/email-to-sms/create-mapping`

### Step 1: General
| Field | ID | Type | Validation |
|---|---|---|---|
| Mapping Name | `mappingName` | Text input | Required, max 50 chars, unique per tenant |
| Description | `mappingDescription` | Textarea | Optional, max 200 chars |
| Sub-Account | Multi-select checkboxes | Checkboxes | Required, at least one |

### Step 2: Email
| Field | ID | Type | Validation |
|---|---|---|---|
| Allowed Sender Emails | Textarea/bulk input | Bulk email entry | At least one required; valid email or `*@domain.com` wildcard |

Same tag-based display as the Standard wizard.

### Step 3: Recipients
Two sections:

**Contact Book Selection:**
- Button opens a large modal with 4 tabs: Contacts, Lists, Dynamic Lists, Tags
- Each tab has a search bar (left) and sort dropdown (right)
- Contacts tab shows: checkbox, avatar (coloured initials circle), name, masked mobile, tags
- Lists/Dynamic Lists/Tags tabs show: checkbox, name, count
- Bottom bar shows live selection summary: "Selected: X contacts, Y lists, Z dynamic lists, W tags"
- "+ Add to Campaign" button applies selection and closes modal

**Opt-out Management:**
- Enable toggle switch
- When enabled, shows "Screening Lists" bordered section with description text
- Checkboxes for available opt-out lists (e.g., "Opt-Out List (24)")
- Selected lists are used to exclude recipients before sending

**Recipient Summary Bar (top of step):**
Shows: `Contacts: X | Lists: Y | Total: Z | Removed: R (red) | Unique: U (green)`

### Step 4: Message Settings
| Field | ID | Type | Validation |
|---|---|---|---|
| SenderID | `senderId` | Dropdown | Required, must be approved/live |
| Enable Multiple SMS | `multipleSms` | Toggle switch | Default: ON |
| Send Delivery Reports | `deliveryReports` | Toggle switch | Default: ON |
| Delivery Report Email | `deliveryReportEmail` | Email input | Required if reports enabled |

### Step 5: Review & Confirm
Three-section summary table:

**Configuration section:**
- Mapping Name
- Description
- Sub-Account (comma-separated names)
- Allowed Senders (count + "whitelisted")

**Recipients section:**
- Selected From (e.g., "8 contacts")
- Total Recipients (highlighted green)
- Opt-out Lists (list names or "None applied")

**Message Settings section:**
- SenderID
- Settings (e.g., "Multiple SMS, Delivery Reports")

Info box (pastel purple): "Email address will be generated and cannot be changed after creation."

### Success Modal
Same pattern as Standard:
- Generated email in pastel purple box with black monospace text
- Copy button
- "SMS Content: Extracted from email body"
- "Close" and "Back to Contact List Library" buttons

---

## 6. Standard Edit Form (3 Steps)

URL: `/management/email-to-sms/standard/{id}/edit`

### Step 1: Core Configuration
- Name (pre-populated)
- Description (pre-populated)
- Subaccount (single-select dropdown, pre-selected)

### Step 2: Sender Allowlist
- Same bulk email entry as create wizard, pre-populated with existing allowed senders

### Step 3: Message Settings
- SenderID (pre-selected)
- Enable Multiple SMS (pre-set)
- Delivery Reports (pre-set)
- Delivery Report Email (pre-populated)

Includes autosave indicator. The generated email address is shown (read-only) and cannot be changed.

---

## 7. JS Service API Contract

The frontend service (`public/js/services/email-to-sms-service.js`) defines all API calls. It currently runs on mock data (`config.useMockData = true`). Once the backend is built, setting this to `false` activates real API calls.

### 7.1 Endpoints & Methods

**Standard Setups:**

| Function | HTTP | Endpoint | Purpose |
|---|---|---|---|
| `listEmailToSmsSetups(options)` | GET | `/api/email-to-sms/setups` | List standard setups. Options: `{includeArchived, search}` |
| `createEmailToSmsSetup(payload)` | POST | `/api/email-to-sms/setups` | Create standard setup |
| `updateEmailToSmsSetup(id, payload)` | PUT | `/api/email-to-sms/setups/{id}` | Update standard setup |
| `getEmailToSmsSetup(id)` | GET | `/api/email-to-sms/setups/{id}` | Get single standard setup |
| `archiveEmailToSmsSetup(id)` | POST | `/api/email-to-sms/setups/{id}/archive` | Archive |
| `unarchiveEmailToSmsSetup(id)` | POST | `/api/email-to-sms/setups/{id}/unarchive` | Unarchive |
| `suspendEmailToSmsSetup(id)` | POST | `/api/email-to-sms/setups/{id}/suspend` | Suspend |
| `reactivateEmailToSmsSetup(id)` | POST | `/api/email-to-sms/setups/{id}/reactivate` | Reactivate |

**Contact List Setups:**

| Function | HTTP | Endpoint | Purpose |
|---|---|---|---|
| `listEmailToSmsContactListSetups(options)` | GET | `/api/email-to-sms/contact-lists/setups` | List contact list setups. Options: `{includeArchived, search, subaccountId}` |
| `createEmailToSmsContactListSetup(payload)` | POST | `/api/email-to-sms/contact-lists/setups` | Create contact list setup |
| `updateEmailToSmsContactListSetup(id, payload)` | PUT | `/api/email-to-sms/contact-lists/setups/{id}` | Update contact list setup |
| `getEmailToSmsContactListSetup(id)` | GET | `/api/email-to-sms/contact-lists/setups/{id}` | Get single contact list setup |
| `archiveEmailToSmsContactListSetup(id)` | POST | `/api/email-to-sms/contact-lists/setups/{id}/archive` | Archive |
| `unarchiveEmailToSmsContactListSetup(id)` | POST | `/api/email-to-sms/contact-lists/setups/{id}/unarchive` | Unarchive |

**Overview (Aggregated):**

| Function | HTTP | Endpoint | Purpose |
|---|---|---|---|
| `listOverviewAddresses(options)` | GET | `/api/email-to-sms/overview` | Combined list of all setups. Options: `{search, status[]}` |
| `getOverviewAddress(id)` | GET | `/api/email-to-sms/overview/{id}` | Get single overview item |
| `suspendOverviewAddress(id)` | POST | `/api/email-to-sms/overview/{id}/suspend` | Suspend from overview |
| `reactivateOverviewAddress(id)` | POST | `/api/email-to-sms/overview/{id}/reactivate` | Reactivate from overview |
| `deleteOverviewAddress(id)` | DELETE | `/api/email-to-sms/overview/{id}` | Delete from overview |

**Reporting Groups:**

| Function | HTTP | Endpoint | Purpose |
|---|---|---|---|
| `listReportingGroups(options)` | GET | `/api/email-to-sms/reporting-groups` | List groups. Options: `{search, status}` |
| `archiveReportingGroup(id)` | POST | `/api/email-to-sms/reporting-groups/{id}/archive` | Archive group |
| `unarchiveReportingGroup(id)` | POST | `/api/email-to-sms/reporting-groups/{id}/unarchive` | Unarchive group |

**Lookups (for wizard dropdowns):**

| Function | HTTP | Endpoint | Purpose |
|---|---|---|---|
| `getTemplatesForSenderIdDropdown()` | GET | `/api/email-to-sms/templates/senderids` | Approved SenderIDs for the tenant |
| `getSubaccounts()` | GET | `/api/email-to-sms/subaccounts` | Tenant's sub-accounts |
| `getContactBookData(subaccountId)` | GET | `/api/email-to-sms/contact-book` | Contacts, lists, dynamic lists, tags, opt-out lists |
| `getApprovedSmsTemplates(subaccountId)` | GET | `/api/email-to-sms/templates/approved` | Approved SMS templates by sub-account |
| `getAccountFlags()` | GET | `/api/email-to-sms/account/flags` | Feature flags for the account |
| `getOptOutLists(subaccountId)` | GET | `/api/email-to-sms/opt-out-lists` | Available opt-out lists |
| `getContacts(subaccountId)` | GET | `/api/email-to-sms/contacts` | Individual contacts |
| `getTags(subaccountId)` | GET | `/api/email-to-sms/tags` | Tags |

**Utilities (client-side only, no backend needed):**

| Function | Purpose |
|---|---|
| `validateContentFilterRegex(pattern)` | Validates regex string, returns `{valid, error}` |
| `validateEmail(email)` | Validates email or wildcard, returns `{valid, isWildcard}` |
| `checkNameExists(name, excludeId)` | Checks for duplicate setup names |

---

### 7.2 Response Envelope

Every API response must follow this shape:

```javascript
// Success
{ "success": true, "data": { ... }, "message": "Optional success message" }
{ "success": true, "data": [...], "total": 14 }

// Error
{ "success": false, "error": "Human-readable error message" }
{ "success": false, "error": "Validation failed", "errors": { "name": ["Name is required"] } }
```

---

### 7.3 Create Payloads

**Standard setup create payload (what the frontend sends to POST):**
```javascript
{
    name: "General Notifications",
    description: "General purpose notifications",
    subaccountId: "uuid",
    allowedEmails: ["admin@company.com", "*@company.com"],
    senderIdTemplateId: "uuid",
    multipleSmsEnabled: true,
    deliveryReportsEnabled: true,
    deliveryReportsEmail: "reports@company.com"
}
```

**Contact List setup create payload (what the wizard sends to POST):**
```javascript
{
    name: "NHS Patient Notifications",
    description: "Automated notifications to NHS patients",
    subaccountIds: ["uuid-1", "uuid-2"],           // Array of sub-account IDs
    emailAddress: "nhs-patient-1523@sms.quicksms.com", // Pre-generated slug (display only)
    allowedSenderEmails: ["admin@nhstrust.nhs.uk"],
    contactBookListIds: ["uuid-1", "uuid-2"],       // Combined IDs across contacts/lists/dynamic/tags
    contactBookListNames: ["NHS Patients", "Tag: VIP"], // Human-readable names (tags prefixed "Tag: ")
    optOutMode: "NONE" | "SELECTED",
    optOutListIds: ["uuid"],
    optOutListNames: ["Global Opt-Out"],
    senderIdTemplateId: "uuid",
    senderId: "NHS",                                // Display name of the SenderID
    multipleSmsEnabled: true,
    deliveryReportsEnabled: true,
    deliveryReportsEmail: "reports@nhstrust.nhs.uk",
    status: "active" | "draft"
}
```

---

### 7.4 Lookup Response Shapes

**SenderIDs (`getTemplatesForSenderIdDropdown` / `getApprovedSmsTemplates`):**
```javascript
{
    "success": true,
    "data": [
        { "id": "uuid", "senderId": "QuickSMS", "name": "Default Sender", "status": "live", "version": "v1.2" },
        { "id": "uuid", "senderId": "NHS", "name": "NHS Trust Sender", "status": "live", "version": "v2.0" }
    ]
}
```

**Sub-accounts (`getSubaccounts`):**
```javascript
{
    "success": true,
    "data": [
        { "id": "uuid", "name": "Main Account" },
        { "id": "uuid", "name": "Marketing" },
        { "id": "uuid", "name": "Support" }
    ]
}
```

**Contact Book Data (`getContactBookData`):**
```javascript
{
    "success": true,
    "data": {
        "contacts": [
            { "id": "uuid", "name": "John Smith", "mobile": "+447700900001", "email": "john@example.com", "status": "active" }
        ],
        "lists": [
            { "id": "uuid", "name": "NHS Patients", "type": "static", "recipientCount": 4521, "status": "active" }
        ],
        "dynamicLists": [
            { "id": "uuid", "name": "Active Subscribers", "type": "dynamic", "recipientCount": 2890, "status": "active", "criteria": "last_activity < 30 days" }
        ],
        "tags": [
            { "id": "uuid", "name": "VIP", "recipientCount": 234, "color": "#7c3aed" }
        ],
        "optOutLists": [
            { "id": "uuid", "name": "Global Opt-Out", "description": "Master opt-out list", "recipientCount": 1245 }
        ]
    }
}
```

**Account Flags (`getAccountFlags`):**
```javascript
{
    "success": true,
    "data": {
        "wildcard_email_allowed": true,
        "max_allowed_sender_emails": 20,
        "delivery_reports_enabled": true
    }
}
```

---

## 8. Email Address Generation

When a setup is created, the backend must auto-generate a unique inbound email address. The frontend generates a preview using this pattern:

```
{slugified-name}-{random-4-digit-number}@sms.quicksms.com
```

**Slugification rules:**
1. Take the setup name (e.g., "NHS Patient Notifications")
2. Lowercase it
3. Replace spaces with hyphens
4. Strip non-alphanumeric characters (except hyphens)
5. Truncate to max 30 characters
6. Append `-` and a random 4-digit number (e.g., `1523`)

**Example:** `"NHS Patient Notifications"` → `nhs-patient-notifications-1523@sms.quicksms.com`

The backend must ensure global uniqueness (no two tenants can share an email address). Retry with a different random suffix on collision. The email address is immutable after creation.

---

## 9. Status Lifecycle

Setups follow this status flow:

```
active ←→ suspended
  ↓
archived → (can be unarchived back to active)
```

- **Active**: Fully operational, incoming emails are processed
- **Suspended**: Email address stays allocated but incoming emails are rejected
- **Archived**: Hidden from default views, not processing emails

Status transitions the frontend supports:
- Active → Suspend (via "Suspend" action)
- Suspended → Reactivate (via "Reactivate" action)
- Active/Suspended → Archive (via "Archive" action)
- Archived → Unarchive (via "Unarchive" action, returns to Active)
- Any → Delete (permanent removal, via "Delete" action with confirmation modal)

---

## 10. Modals & Drawers on the Main Page

### Overview Details Drawer
Opened by clicking a row in the Overview tab. Shows sections:
- **Status** — pill badge + status text
- **Configuration** — Name, Description, Type, SenderID
- **Messaging Settings** — Multiple SMS, Delivery Reports
- **Organisation** — Sub-Account, Reporting Group
- **Security** — Allowed Senders list
- **Activity** — Messages Sent, Last Used, Created date

Actions: Copy Email, Edit Configuration, View Message Log, Suspend, Delete

### Standard Details Drawer
Similar to Overview but specific to Standard setups. Additional fields:
- SenderID
- Multiple SMS setting
- Delivery Reports setting

### Contact List Details Drawer
Additional fields:
- Target Lists (names of contact lists/dynamic lists)
- Opt-out Lists (names of screening lists)

### Create Reporting Group Modal
Fields:
- Group Name (required, max 255)
- Description (optional)
- Assign Email-to-SMS Address (dropdown of existing setups)

### Suspend/Delete Confirmation Modals
Standard confirmation dialogs with descriptive text explaining the impact.

---

## 11. Validation Summary

### Setup Create/Update
| Field | Rules |
|---|---|
| name | Required, max 50 chars, unique per tenant |
| description | Optional, max 200 chars |
| sub-account(s) | Required, at least one, must exist for tenant |
| allowed sender emails | At least one required, each must be valid email or `*@domain.com` wildcard, max 20 |
| SenderID | Required, must be an approved/live SenderID for the tenant |
| multiple SMS | Boolean, defaults to true |
| delivery reports | Boolean, defaults to false |
| delivery report email | Required if delivery reports enabled, valid email format |
| recipients (Contact List) | At least one contact, list, dynamic list, or tag required |
| opt-out list IDs (Contact List) | Optional, each must be a valid opt-out list for the tenant |

### Reporting Group
| Field | Rules |
|---|---|
| name | Required, max 255, unique per tenant |
| description | Optional, max 500 |

---

## 12. Authentication & Tenant Isolation

- All endpoints use session-based customer authentication (`customer.auth` middleware)
- Tenant ID comes from `session('customer_tenant_id')` — NEVER from request input
- The existing `SetTenantContext` middleware sets the PostgreSQL session variable for Row Level Security
- All queries must be scoped to the authenticated tenant
- All portal responses must use `toPortalArray()` — never expose raw model data or internal IDs
- Foreign key references (SenderID, sub-account, opt-out lists) must be validated as belonging to the same tenant

---

## 13. Design System Notes

- **Colour palette**: Fillow design system with pastel purples as primary
- **Status badges**: `badge-pastel-success` (Active/green), `badge-pastel-warning` (Suspended/amber), `badge-pastel-secondary` (Archived/grey)
- **Info boxes**: `alert-pastel-primary` class (pastel purple background, dark text)
- **Buttons**: Primary buttons use purple (`btn-primary`), secondary actions use outline styles
- **Tables**: Standard Fillow table styling with hover states, responsive
- **Drawers**: Bootstrap Offcanvas panels, slide in from right
- **Avatars**: Contact rows show coloured initials circles (36px, rounded, pastel background)
- **Toggle switches**: Bootstrap form-switch style
- **Filters**: Search bar on LEFT, filter button on RIGHT. "Clear All" resets but does NOT auto-apply
