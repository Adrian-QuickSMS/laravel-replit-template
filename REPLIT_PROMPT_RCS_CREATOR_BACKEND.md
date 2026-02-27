# Replit Prompt: RCS Rich Message Creator — Pull & Merge Backend

## Overview

The complete backend for the **RCS Rich Message Creator** (single rich cards + carousels) has been built on branch `claude/quicksms-security-performance-dr8sw`. This includes the media asset pipeline, content validation, campaign integration, and placeholder resolution — everything needed for creating and sending rich RCS messages with images, text, and buttons.

### Step 1: Pull and merge the backend branch

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git merge origin/claude/quicksms-security-performance-dr8sw
```

### Step 2: Run migrations

```bash
php artisan migrate
```

---

## What Was Deployed — RCS Content Creator Backend

### New Database Table

| Table | Purpose |
|---|---|
| `rcs_assets` | RCS media storage: upload/URL import, crop/zoom edit params, draft sessions, tenant-scoped via `account_id` |

### Campaign Table Updates

The `campaigns` table now supports RCS rich content:
- `rcs_content` (JSONB) — stores the rich card/carousel structure
- `rcs_agent_id` (FK) — links to the approved RCS Agent used as sender
- `type` check constraint updated: `sms`, `rcs_basic`, `rcs_single`, `rcs_carousel`

### New Service: `RcsContentValidator` (257 lines)

`app/Services/RcsContentValidator.php` — Full structural validation for RCS rich messages:

- **Card counts:** Single = exactly 1 card, Carousel = 2–10 cards
- **Button limits:** Max 4 per card, label max 25 chars
- **Button types:** `url`, `phone`, `calendar`, `postback` — each with required action fields
- **Text limits:** Body max 2,000 chars, callback_data max 64 chars
- **Orientation consistency:** All carousel cards must use the same orientation
- **Asset finalization:** Before send, validates all media asset UUIDs exist in `rcs_assets` with `is_draft = false`

Two validation levels:
- `validateStructure()` — called on campaign save (lenient, allows drafts)
- `validateForSend()` — called before send (strict, checks asset finalization)

### Updated Service: `RcsAssetService` (481 lines)

`app/Services/RcsAssetService.php` — Image processing pipeline:

- Import from URL or file upload (JPEG, PNG, GIF)
- Apply edit params: zoom (25–200%), crop position, orientation
- **Lossless re-editing:** Stores original at `original_storage_path`, always re-crops from original
- Draft session tracking for cleanup
- Finalization workflow (`is_draft` → `false`)

### New Controller: `RcsAssetController` (273 lines)

`app/Http/Controllers/Api/RcsAssetController.php` — Media API:

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `POST` | `/api/rcs/assets/process-url` | Import image from URL. Body: `{ url, edit_params?, draft_session? }` |
| `POST` | `/api/rcs/assets/process-upload` | Upload image file (max 1MB). Multipart: `file` + `edit_params?` |
| `POST` | `/api/rcs/assets/proxy-image` | SSRF-safe image proxy for previews. Returns base64 data URL |
| `PUT` | `/api/rcs/assets/{uuid}` | Update crop/zoom. Body: `{ edit_params: { zoom, crop_position, orientation } }` |
| `POST` | `/api/rcs/assets/{uuid}/finalize` | Mark asset as non-draft (required before campaign send) |

### Updated: `CampaignService` — RCS Validation Wired In

`app/Services/Campaign/CampaignService.php`:

- **`create()`** and **`update()`** — validates RCS structure via `RcsContentValidator::validateStructure()` for `rcs_single` and `rcs_carousel` types
- **`validateForSend()`** — full validation via `RcsContentValidator::validateForSend()`, including asset finalization checks
- **Placeholder resolution** — `{{merge_fields}}` resolved in RCS card `description` and `textBody` per-recipient, stored as `resolved_rcs_content` in recipient metadata

### Updated: Campaign & MessageTemplate Models

- `Campaign::TYPE_RCS_SINGLE = 'rcs_single'`
- `Campaign::TYPE_RCS_CAROUSEL = 'rcs_carousel'`
- Both models: `'rcs_content' => 'array'` cast, fillable

### New: Draft Cleanup Command

`app/Console/Commands/CleanupDraftRcsAssets.php`:
- `php artisan rcs:cleanup-drafts [--hours=24] [--dry-run]`
- Scheduled daily at 03:00, removes stale draft assets from disk + database

---

## Campaign API Endpoints (Already Registered)

All under `customer.auth` middleware:

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `POST` | `/api/campaigns` | Create draft campaign. Body includes `type`, `rcs_content`, `rcs_agent_id` |
| `PUT` | `/api/campaigns/{id}` | Update draft. Same fields |
| `POST` | `/api/campaigns/{id}/prepare` | Resolve recipients, dispatch content resolution |
| `GET` | `/api/campaigns/{id}/preparation-status` | Poll preparation progress + cost estimate |
| `GET` | `/api/campaigns/{id}/validate` | Dry-run validation before send |
| `POST` | `/api/campaigns/{id}/send` | Validate → reserve billing → queue for delivery |
| `POST` | `/api/campaigns/{id}/schedule` | Schedule for future send. Body: `{ scheduled_at, timezone }` |
| `POST` | `/api/campaigns/{id}/pause` | Pause sending campaign |
| `POST` | `/api/campaigns/{id}/resume` | Resume paused campaign |
| `POST` | `/api/campaigns/{id}/cancel` | Cancel campaign |

---

## RCS Content Data Structure

### Single Rich Card

```json
{
  "type": "single",
  "cards": [
    {
      "cardIndex": 1,
      "textBody": "Message text (max 2000 chars, supports {{placeholders}})",
      "media": {
        "assetUuid": "rcs-asset-uuid",
        "orientation": "vertical_short|vertical_medium|vertical_tall|horizontal"
      },
      "buttons": [
        {
          "label": "Visit Website (max 25 chars)",
          "type": "url",
          "action": { "url": "https://example.com" }
        },
        {
          "label": "Call Us",
          "type": "phone",
          "action": { "phoneNumber": "+44..." }
        },
        {
          "label": "Add to Calendar",
          "type": "calendar",
          "action": { "title": "Event", "startTime": "ISO8601", "endTime": "ISO8601" }
        },
        {
          "label": "Learn More",
          "type": "postback",
          "tracking": { "enabled": true, "callback_data": "max 64 chars" }
        }
      ]
    }
  ]
}
```

### Carousel (2–10 cards, same orientation)

```json
{
  "type": "carousel",
  "width": "small|medium",
  "cards": [
    { "cardIndex": 1, "textBody": "...", "media": { ... }, "buttons": [ ... ] },
    { "cardIndex": 2, "textBody": "...", "media": { ... }, "buttons": [ ... ] }
  ]
}
```

---

## Frontend Flow (Already Built)

The RCS content wizard (`public/js/rcs-wizard.js`, 3,460 lines) and preview renderer (`public/js/rcs-preview-renderer.js`) are already on the branch. The send-message page already includes the wizard.

### End-to-end flow:

1. **User uploads media** → `POST /api/rcs/assets/process-upload` or `process-url` → gets `assetUuid`
2. **User builds rich card(s)** — title, text, media, buttons in the wizard
3. **User finalizes assets** → `POST /api/rcs/assets/{uuid}/finalize`
4. **User submits campaign form** → `POST /messages/store-campaign-config` (stores in session)
5. **Confirm page** → `POST /api/campaigns` (creates draft with `rcs_content`)
6. **Prepare** → `POST /api/campaigns/{id}/prepare` (resolves recipients, placeholders)
7. **Send** → `POST /api/campaigns/{id}/send` (validates via `RcsContentValidator`, reserves billing, queues)

---

## Key Architecture Notes

1. **Validation is two-tier:** `validateStructure()` on save (allows incomplete drafts), `validateForSend()` before send (strict — all assets must be finalized)
2. **Assets are draft by default** — must call `/finalize` before campaign can send. Stale drafts cleaned daily.
3. **Placeholder resolution** — `{{first_name}}`, `{{company}}` etc. resolved per-recipient in RCS card text, stored as `resolved_rcs_content` in campaign_recipients metadata
4. **Lossless re-editing** — original image stored separately; crop/zoom always applied from original, no quality loss
5. **SSRF protection** — image proxy validates URL doesn't resolve to private IP ranges
6. **Tenant isolation** — `RcsAsset` model has global scope on `account_id`, matching Campaign/MessageTemplate pattern
