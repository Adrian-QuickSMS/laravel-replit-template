# QuickSMS — Audit Log Specification

**Version:** 1.0
**Date:** 10 March 2026
**Status:** Draft — Gap Analysis Complete

---

## 1. Design Philosophy

Every sensitive action across the platform must be recorded in an **immutable, append-only** database table — not just application log files. This is required for ISO 27001, Cyber Essentials Plus, and NHS DSP Toolkit compliance.

### Core Rules

1. **Once written, never modified or deleted** — enforced at **two levels**: Eloquent model hooks (application layer) **and** database-level controls (REVOKE UPDATE/DELETE on the table for `portal_rw` and `portal_ro` roles, plus a trigger guard for defence-in-depth). Application-only enforcement is bypassable via raw SQL or query builder — both layers are required for compliance.
2. **Tenant-isolated** — customer-facing audit tables use PostgreSQL Row Level Security (RLS) so customers only see their own events.
3. **RED/GREEN separation** — admin-only audit records (RED zone) are never exposed to customer portal queries (GREEN zone).
4. **Always capture context** — every event records:
   - **Who** — `user_id`, `user_name`, `user_role`
   - **What** — `action` (event type string)
   - **When** — `created_at` (immutable timestamp)
   - **Where** — `ip_address`, `user_agent`
   - **Why** — `metadata` (JSONB with before/after values, reason text, related IDs)

### Standard Table Schema

All audit tables follow this pattern:

```sql
CREATE TABLE <module>_audit_log (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    account_id      UUID NOT NULL,          -- tenant scope for RLS
    <entity>_id     UUID,                   -- FK to the entity being audited
    action          VARCHAR(50) NOT NULL,   -- event type constant
    user_id         UUID,                   -- actor (NULL for system events)
    user_name       VARCHAR(255),           -- denormalised for log readability
    details         TEXT,                   -- human-readable summary
    metadata        JSONB DEFAULT '{}',     -- structured before/after, related IDs
    ip_address      INET,
    user_agent      TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- DB-level immutability (required alongside Eloquent hooks)
REVOKE UPDATE, DELETE ON <module>_audit_log FROM portal_rw, portal_ro;
CREATE OR REPLACE FUNCTION prevent_audit_mutation() RETURNS TRIGGER AS $$
BEGIN
    RAISE EXCEPTION 'Audit log entries are immutable — updates and deletes are prohibited';
END;
$$ LANGUAGE plpgsql;
CREATE TRIGGER trg_<module>_audit_immutable
    BEFORE UPDATE OR DELETE ON <module>_audit_log
    FOR EACH ROW EXECUTE FUNCTION prevent_audit_mutation();
```

### Standard RLS Policy

All tenant-scoped audit tables use `app.current_tenant_id` (the project-wide standard set by `SetTenantContext` middleware):

```sql
ALTER TABLE <module>_audit_log ENABLE ROW LEVEL SECURITY;
CREATE POLICY <module>_audit_tenant_isolation ON <module>_audit_log
    FOR SELECT
    USING (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
CREATE POLICY <module>_audit_insert ON <module>_audit_log
    FOR INSERT
    WITH CHECK (account_id = NULLIF(current_setting('app.current_tenant_id', true), '')::UUID);
-- svc_red and ops_admin bypass RLS via role-level BYPASSRLS
```

### Standard Model Contract

```php
class ExampleAuditLog extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    public static function boot()
    {
        parent::boot();
        static::updating(function () {
            throw new \RuntimeException('Audit log entries cannot be modified');
        });
        static::deleting(function () {
            throw new \RuntimeException('Audit log entries cannot be deleted');
        });
    }
}
```

---

## 2. What Exists Today — 9 Audit Areas

| # | Table | Domain | Storage | Quality |
|---|-------|--------|---------|---------|
| 1 | `auth_audit_log` | Login, logout, password, MFA, lockouts | Database | **Excellent** |
| 2 | `api_connection_audit_events` | API key create, rotate, revoke, permissions | Database | **Good** |
| 3 | `routing_audit_log` | Message routing rule changes | Database | **Good** |
| 4 | `rate_card_audit_log` | Supplier rate card changes, FX updates | Database | **Good** |
| 5 | `message_template_audit_log` | Template create, edit, approve, suspend | Database | **Good** |
| 6 | `email_to_sms_audit_log` | Email-to-SMS config changes | Database | **Good** |
| 7 | `financial_audit_log` | Credits, invoices, payments, ledger events | Database | **Excellent** |
| 8 | `purchase_audit_logs` | VMN and shortcode keyword purchases | Database | **Good** |
| 9 | `AdminAuditService` | Admin impersonation, admin user mgmt | **Log file only** | **Poor** |

### Existing Table Event Types

**`auth_audit_log`**: `login_success`, `login_failed`, `logout`, `password_changed`, `password_reset_requested`, `password_reset_completed`, `mfa_enabled`, `mfa_disabled`, `mfa_verified`, `account_locked`, `account_unlocked`, `session_expired`.

**`api_connection_audit_events`**: `created`, `updated`, `rotated`, `revoked`, `permissions_changed`.

**`routing_audit_log`**: `rule_created`, `rule_updated`, `rule_deleted`, `weight_changed`, `override_added`, `override_removed`.

**`rate_card_audit_log`**: `rate_card_created`, `rate_card_updated`, `rate_imported`, `fx_rate_updated`, `rate_card_activated`, `rate_card_archived`.

**`message_template_audit_log`**: `created`, `updated`, `submitted_for_approval`, `approved`, `rejected`, `suspended`, `archived`, `version_created`.

**`email_to_sms_audit_log`**: `setup_created`, `setup_updated`, `address_added`, `address_removed`, `sender_updated`, `recipient_changed`.

**`financial_audit_log`**: `credit_added`, `credit_deducted`, `invoice_generated`, `payment_received`, `refund_issued`, `balance_adjustment`.

**`purchase_audit_logs`**: `vmn_purchased`, `keyword_purchased`.

**`AdminAuditService` (log file)**: `ADMIN_IMPERSONATION_STARTED`, `ADMIN_IMPERSONATION_ENDED`, `ADMIN_USER_INVITED`, `ADMIN_USER_SUSPENDED`, `ADMIN_LOGIN_BLOCKED_BY_IP`, `admin_config_change`.

---

## 3. Complete Gap Analysis — 8 Gaps

---

### GAP 1: Campaign Lifecycle — CRITICAL

**Current state:** No audit table exists. `CampaignService` writes to `Log::info` only. There is zero persistent, queryable record of who created, approved, sent, or cancelled a campaign.

**Affected files:**
- `app/Http/Controllers/Api/CampaignApiController.php`
- `app/Services/Campaign/CampaignService.php`
- `app/Services/Campaign/DeliveryService.php`

**Missing events:**

| Event | Controller/Service Method | What Must Be Captured |
|-------|--------------------------|----------------------|
| `campaign_created` | `CampaignApiController@store` | Channel, message type, recipient sources, sub-account |
| `campaign_edited` | `CampaignApiController@update` | Before/after content, recipient changes, schedule changes |
| `campaign_prepared` | `CampaignApiController@prepare` | Recipient count, dedup results, opt-out exclusions |
| `campaign_sent` | `CampaignApiController@sendNow` | Who approved, final recipient count, estimated cost, reservation ID |
| `campaign_scheduled` | `CampaignApiController@schedule` | Scheduled time, who scheduled |
| `campaign_paused` | `CampaignApiController@pause` | Who paused, messages sent so far, messages remaining |
| `campaign_resumed` | `CampaignApiController@resume` | Who resumed, time paused |
| `campaign_cancelled` | `CampaignApiController@cancel` | Who cancelled, at what stage, messages already sent |
| `campaign_completed` | `DeliveryService` (batch finish) | Final stats: delivered, failed, pending, total cost |
| `campaign_archived` | `CampaignApiController@archive` | Who archived, final status at time of archive |
| `campaign_cloned` | `CampaignApiController@clone` | Source campaign ID, new campaign ID |
| `campaign_deleted` | `CampaignApiController@destroy` | Who deleted, campaign state at deletion |

**Recommended table:** `campaign_audit_log`

```sql
CREATE TABLE campaign_audit_log (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    account_id      UUID NOT NULL,
    campaign_id     UUID NOT NULL,
    action          VARCHAR(50) NOT NULL,
    user_id         UUID,
    user_name       VARCHAR(255),
    details         TEXT,
    metadata        JSONB DEFAULT '{}',
    ip_address      INET,
    user_agent      TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_campaign_audit_campaign ON campaign_audit_log(campaign_id);
CREATE INDEX idx_campaign_audit_account ON campaign_audit_log(account_id);
CREATE INDEX idx_campaign_audit_action ON campaign_audit_log(action);
CREATE INDEX idx_campaign_audit_created ON campaign_audit_log(created_at);

ALTER TABLE campaign_audit_log ENABLE ROW LEVEL SECURITY;
-- RLS policies per Standard RLS Policy pattern (Section 1)
-- DB immutability per Standard Table Schema pattern (Section 1)
```

---

### GAP 2: User Management — CRITICAL

**Current state:** All actions write to `Log::info` only — not a database table. Role and permission changes log only the changed key names, not the before/after values. This is a compliance failure.

**Affected files:**
- `app/Http/Controllers/UserManagementController.php`

**Missing events:**

| Event | Controller Method | What Must Be Captured |
|-------|------------------|----------------------|
| `user_invited` | `invite` | Invitee email, assigned role, assigned sub-account, permissions granted |
| `invitation_accepted` | `acceptInvitation` | User who accepted, token hash, invitation age |
| `invitation_revoked` | `revokeInvitation` | Invitee email, who revoked, reason |
| `user_role_changed` | `update` | Before/after role slug |
| `user_permissions_changed` | `update` | Before/after permission map (all 28 toggles) |
| `user_sender_capability_changed` | `update` | Before/after sender capability level |
| `user_suspended` | `suspend` | Who suspended, reason |
| `user_reactivated` | `reactivate` | Who reactivated |
| `ownership_transferred` | `transferOwnership` | From user, to user, both names and IDs |

**Recommended table:** `user_audit_log`

```sql
CREATE TABLE user_audit_log (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    account_id      UUID NOT NULL,
    target_user_id  UUID,
    action          VARCHAR(50) NOT NULL,
    user_id         UUID,
    user_name       VARCHAR(255),
    details         TEXT,
    metadata        JSONB DEFAULT '{}',
    ip_address      INET,
    user_agent      TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

ALTER TABLE user_audit_log ENABLE ROW LEVEL SECURITY;
-- RLS policies per Standard RLS Policy pattern (Section 1)
-- DB immutability per Standard Table Schema pattern (Section 1)
```

---

### GAP 3: Sub-Account Management — HIGH

**Current state:** Some actions write to `Log::info`, sub-account edits (name/description changes) have no logging at all.

**Affected files:**
- `app/Http/Controllers/SubAccountController.php`
- `app/Models/SubAccount.php`

**Missing events:**

| Event | Controller/Model Method | Current Logging |
|-------|------------------------|----------------|
| `sub_account_created` | `SubAccountController@store` | Log::info only |
| `sub_account_edited` | `SubAccountController@update` | **None** |
| `sub_account_limits_updated` | `SubAccount@updateLimits` | Log::info only |
| `sub_account_suspended` | `SubAccountController@suspend` | Log::info only |
| `sub_account_reactivated` | `SubAccountController@reactivate` | Log::info only |
| `sub_account_archived` | `SubAccountController@archive` | Log::info only |

**Recommendation:** Add these events to `user_audit_log` with a `module` discriminator column, or create a separate `sub_account_audit_log` table following the standard schema.

---

### GAP 4: Contact Book — HIGH

**Current state:** A `contact_timeline_events` table exists in the database but is **never written to** by any controller or service. The table is read-only infrastructure waiting for data. Almost all contact actions have zero audit coverage.

**Affected files:**
- `app/Http/Controllers/Api/ContactBookApiController.php`
- `app/Services/OptOutService.php`

**Missing events:**

| Event | Controller Method | Current Logging |
|-------|------------------|----------------|
| `contact_created` | `contactsStore` | Records `created_by` field only |
| `contact_updated` | `contactsUpdate` | Records `updated_by` field only |
| `contact_deleted` | `contactsDestroy` | Soft delete only |
| `contacts_bulk_deleted` | `bulkDelete` | **None** |
| `contacts_imported` | CSV/Excel import flow | **None** |
| `contacts_exported` | `bulkExport` | **None** |
| `list_created` | `listsStore` | **None** |
| `list_updated` | `listsUpdate` | **None** |
| `list_deleted` | `listsDestroy` | **None** |
| `list_members_added` | `listsAddMembers` | **None** |
| `list_members_removed` | `listsRemoveMembers` | **None** |
| `tag_created` | `tagsStore` | **None** |
| `tag_updated` | `tagsUpdate` | **None** |
| `tag_deleted` | `tagsDestroy` | **None** |
| `tags_assigned` | `bulkAddTags` | **None** |
| `tags_removed` | `bulkRemoveTags` | **None** |
| `opt_out_manual_add` | `bulkAddToOptOut` | **None** |
| `opt_out_manual_remove` | `bulkRemoveFromOptOut` | **None** |
| `opt_out_automatic` | `OptOutService` | Log::info only |
| `msisdn_revealed` | `revealMsisdn` | Log::info only |

**Recommendation:** Wire the existing `contact_timeline_events` table — the infrastructure is already built, it just needs to be populated by the controllers. The `msisdn_revealed` event is especially important for NHS DSP Toolkit compliance (PII access audit trail).

**Important:** The `contact_timeline_events` table currently uses a PostgreSQL ENUM for `timeline_event_type` with only these values: `message_sent`, `message_received`, `tag_added`, `tag_removed`, `list_added`, `list_removed`, `optout`, `optin`. Before wiring new events (e.g. `contact_created`, `contact_updated`, `contact_deleted`, `contacts_imported`, `contacts_exported`, `msisdn_revealed`), **a migration must extend this ENUM** or convert the column to `VARCHAR(50)` to accommodate the full event set. The `source_module` column may also need expansion.

---

### GAP 5: Account Settings — HIGH

**Current state:** No audit logging for account configuration changes. Status transitions log to `Log::info` via `AccountObserver` but not to a database table.

**Affected files:**
- `app/Http/Controllers/AccountController.php`
- `app/Models/Account.php`
- `app/Observers/AccountObserver.php`

**Missing events:**

| Event | Controller/Model | Current Logging |
|-------|-----------------|----------------|
| `account_details_updated` | `AccountController@update` | **None** (logs errors only) |
| `account_settings_changed` | `AccountController@updateSettings` | **None** |
| `test_numbers_changed` | `AccountController@updateSettings` | **None** |
| `billing_config_changed` | Admin controllers | **None** |
| `account_status_transition` | `Account@transitionTo` | Log::info only (Observer) |

**Recommended table:** `account_audit_log`

```sql
CREATE TABLE account_audit_log (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    account_id      UUID NOT NULL,
    action          VARCHAR(50) NOT NULL,
    user_id         UUID,
    user_name       VARCHAR(255),
    details         TEXT,
    metadata        JSONB DEFAULT '{}',
    ip_address      INET,
    user_agent      TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

ALTER TABLE account_audit_log ENABLE ROW LEVEL SECURITY;
-- RLS policies per Standard RLS Policy pattern (Section 1)
-- DB immutability per Standard Table Schema pattern (Section 1)
```

---

### GAP 6: Number Management (Beyond Purchases) — MEDIUM

**Current state:** VMN and keyword purchases are well-audited via `purchase_audit_logs`. All other number management actions (assignments, releases, auto-reply rules) are either `Log::info` or completely unlogged.

**Affected files:**
- `app/Services/Numbers/NumberService.php`
- `app/Http/Controllers/Api/NumberApiController.php`
- `app/Http/Controllers/Admin/AdminNumbersApiController.php`

**Missing events:**

| Event | Service/Controller Method | Current Logging |
|-------|--------------------------|----------------|
| `vmn_assigned` | `NumberService@assignNumber` | **None** |
| `vmn_bulk_assigned` | `NumberService@bulkAssign` | **None** |
| `vmn_released` | `NumberService@releaseNumber` | Log::info only |
| `vmn_bulk_released` | `NumberService@bulkRelease` | Log::info only |
| `auto_reply_created` | `NumberService@addAutoReplyRule` | **None** |
| `auto_reply_updated` | `NumberService@updateAutoReplyRule` | **None** |
| `auto_reply_deleted` | `NumberService@deleteAutoReplyRule` | **None** |
| `number_returned_to_pool` | `AdminNumbersApiController@returnToPool` | Log::info only |

**Recommended table:** `number_audit_log` (extends the scope of `purchase_audit_logs`)

---

### GAP 7: Admin Operations — MEDIUM

**Current state:** `AdminAuditService` writes structured JSON to the application log file (`storage/logs/laravel.log`), not to a database table. This means admin actions are:
- Not queryable via SQL
- Not displayable in the admin console UI
- Lost during log rotation
- Not available for compliance reporting

**Events currently captured (log file only — partial list, `AdminAuditService` defines many more):**
- `ADMIN_IMPERSONATION_STARTED`, `ADMIN_IMPERSONATION_ENDED`
- `ADMIN_USER_INVITED`, `ADMIN_USER_CREATED`, `ADMIN_USER_UPDATED`, `ADMIN_USER_DELETED`
- `ADMIN_USER_SUSPENDED`
- `ADMIN_LOGIN_SUCCESS`, `ADMIN_LOGIN_FAILED`, `ADMIN_LOGIN_BLOCKED_BY_IP`
- `ADMIN_MFA_ENABLED`, `ADMIN_MFA_DISABLED`
- `admin_config_change`
- Various category-specific events (`CATEGORY_AUTH`, `CATEGORY_USER`, `CATEGORY_CONFIG`, `CATEGORY_SECURITY`)

**Affected files:**
- `app/Services/Admin/AdminAuditService.php`

**Recommended table:** `admin_audit_log`

```sql
CREATE TABLE admin_audit_log (
    id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    admin_user_id   UUID NOT NULL,
    admin_user_name VARCHAR(255),
    action          VARCHAR(50) NOT NULL,
    target_type     VARCHAR(50),
    target_id       UUID,
    details         TEXT,
    metadata        JSONB DEFAULT '{}',
    ip_address      INET,
    user_agent      TEXT,
    created_at      TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- No RLS — admin-only table, RED zone
CREATE INDEX idx_admin_audit_action ON admin_audit_log(action);
CREATE INDEX idx_admin_audit_created ON admin_audit_log(created_at);
```

---

### GAP 8: Inbox / Two-Way Messaging — MEDIUM

**Current state:** Messages are stored in `inbox_messages` and conversations in `inbox_conversations`, but these are operational data — not an audit trail. There is no record of who read or managed conversations.

**Affected files:**
- `app/Http/Controllers/InboxController.php`

**Missing events:**

| Event | Controller Method | What Must Be Captured |
|-------|------------------|----------------------|
| `inbox_reply_sent` | `apiSendReply` | Who sent, to which conversation, message content hash, from number |
| `conversation_marked_read` | `apiMarkRead` | Who read, conversation ID |
| `conversation_marked_unread` | `apiMarkUnread` | Who marked unread, conversation ID |

**Recommendation:** The `inbox_messages` table already stores `sent_by_user_id`, which partially covers reply auditing. Read/unread actions should be captured via `inbox_read_receipts` (which exists) or a lightweight audit event. This gap is lower priority because the operational tables provide some traceability.

---

## 4. Coverage Heat Map

| Module | DB Audit Events | Log File Only | No Logging | Rating |
|--------|:-:|:-:|:-:|--------|
| Authentication & Security | 12+ | — | — | **Excellent** |
| API Connections | 5+ | — | — | **Good** |
| Routing Rules | 6+ | — | — | **Good** |
| Supplier Rate Cards | 6+ | — | — | **Good** |
| Message Templates | 8+ | — | — | **Good** |
| Email-to-SMS | 6+ | — | — | **Good** |
| Financial / Billing | 6+ | — | — | **Excellent** |
| Number Purchases | 2 | — | — | **Good** |
| **Campaigns** | **0** | Some | Most | **No coverage** |
| **User Management** | **0** | 8 events | 1 event | **Poor** |
| **Sub-Accounts** | **0** | 5 events | 1 event | **Poor** |
| **Contact Book** | **0** | 2 events | 10+ events | **No coverage** |
| **Account Settings** | **0** | 1 event | 4 events | **No coverage** |
| **Number Mgmt (non-purchase)** | **0** | 3 events | 4 events | **Poor** |
| **Admin Operations** | **0** | 12+ events | — | **Poor** |
| **Inbox** | **0** | — | 2 events | **Poor** |

---

## 5. Recommended New Tables — Summary

| # | Table | Scope | RLS | Priority |
|---|-------|-------|-----|----------|
| 1 | `campaign_audit_log` | Tenant | Yes | **Critical** |
| 2 | `user_audit_log` | Tenant | Yes | **Critical** |
| 3 | `account_audit_log` | Tenant | Yes | **High** |
| 4 | `number_audit_log` | Tenant | Yes | **Medium** |
| 5 | `admin_audit_log` | Internal (RED) | No | **Medium** |
| 6 | `contact_timeline_events` (existing) | Tenant | Yes | **High** — wire controllers to write to it |

All new tables must:
- Follow the standard schema from Section 1
- Be immutable (block update/delete via Eloquent hooks)
- Include `account_id` for RLS on tenant-scoped tables
- Capture `ip_address`, `user_agent`, and `metadata` (JSONB)
- Have indexes on `account_id`, `action`, `created_at`, and the entity FK

---

## 6. Display Infrastructure (Already Built)

The UI infrastructure already exists and can consume new audit data once backend tables and API endpoints are created:

### Customer Portal
- **Route:** `/account/audit-logs`
- **Features:** Tabs for All / Security / Messaging / Financial, filters by date / module / event / user / severity, CSV and Excel export
- **View:** Shared audit log component

### Admin Console
- **Route:** `/admin/security/audit-logs`
- **Features:** Cross-tenant viewer, internal admin audit, integrity verification
- **View:** Shared audit log component

### Shared Components
- **Blade partial:** `shared/partials/audit-log-component.blade.php`
- **JS controllers:** `admin-audit-log.js` and `quicksms-audit-logger.js`

### Current Status
The frontend currently renders **mock data**. Once the backend tables and API endpoints exist, the JS controllers need to be updated to fetch real data from the new audit log endpoints.

---

## 7. Implementation Priority

### Phase 1 — Critical (Compliance Blockers)
1. Create `campaign_audit_log` table and model
2. Wire `CampaignApiController` and `CampaignService` to log all lifecycle events
3. Create `user_audit_log` table and model
4. Wire `UserManagementController` to log all user management events with before/after values

### Phase 2 — High (Compliance Gaps)
5. Create `account_audit_log` table and model
6. Wire `AccountController` and `AccountObserver` to log settings and status changes
7. Wire `SubAccountController` events to `user_audit_log` (or dedicated table)
8. Wire `ContactBookApiController` to write to existing `contact_timeline_events`
9. Wire `OptOutService` and `revealMsisdn` to `contact_timeline_events`

### Phase 3 — Medium (Hardening)
10. Create `number_audit_log` table and model
11. Wire `NumberService` assignment, release, and auto-reply actions
12. Create `admin_audit_log` table and model
13. Migrate `AdminAuditService` from log file output to database writes
14. Wire inbox read/unread actions

### Phase 4 — Frontend Integration
15. Update `quicksms-audit-logger.js` to fetch from real API endpoints
16. Update `admin-audit-log.js` to fetch from real API endpoints
17. Replace mock data with live queries across all audit log views
