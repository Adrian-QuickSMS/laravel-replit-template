# Replit Prompt: Pull & Merge RCS Content Wizard Backend

## What to do

Pull and merge the RCS Content Wizard backend from the feature branch. This adds the complete backend for creating and sending rich RCS messages (single rich cards + carousels) — media asset pipeline, content validation, campaign creation, and the full send flow from the confirm page.

```bash
git fetch origin claude/quicksms-security-performance-dr8sw
git merge origin/claude/quicksms-security-performance-dr8sw
php artisan migrate
```

After merge, clear caches:
```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
```

---

## What Changed — Summary

### 1. RCS Content Wizard → Campaign Pipeline (Wiring)

The RCS Content Wizard modal on the Send Message page now fully submits its content to the backend:

- **`continueToConfirmation()`** in `send-message.blade.php` now calls `getRcsSendPayload()` to capture the rich card/carousel content and includes it as `rcs_content` in the session payload
- Also stores `sender_id_id`, `rcs_agent_id`, `campaign_type`, and `recipient_sources` (actual phone numbers, list UUIDs) — everything needed to create a real Campaign record
- **Channel type mapping:** `rich_rcs` → `rcs_single` or `rcs_carousel` (based on wizard's `messageType`)

### 2. Confirm & Send Page — Now Real

The confirm page (`confirm-campaign.blade.php`) previously faked the send with a `setTimeout`. It now:

- POSTs to `POST /messages/confirm-send` (new endpoint)
- Creates a Campaign record via `CampaignService::create()` using session data
- Calls `sendNow()` for immediate send or `schedule()` for scheduled campaigns
- Shows proper error messages on validation failure
- Clears session after successful send

### 3. Campaign API — Carousel Support

`CampaignApiController` `store()` and `update()` validation now accepts `rcs_carousel` type (was previously rejecting it).

### 4. Media Asset Pipeline (New)

| Endpoint | Purpose |
|----------|---------|
| `POST /api/rcs/assets/process-url` | Import image from URL, apply crop/zoom |
| `POST /api/rcs/assets/process-upload` | Upload image file (JPEG/PNG/GIF, max 1MB) |
| `POST /api/rcs/assets/proxy-image` | SSRF-safe image proxy for previews |
| `PUT /api/rcs/assets/{uuid}` | Update crop/zoom (lossless, re-crops from original) |
| `POST /api/rcs/assets/{uuid}/finalize` | Mark asset as non-draft (required before send) |

New table: `rcs_assets` — stores uploaded/imported images with edit params, draft sessions, tenant isolation.

### 5. RCS Content Validation (New)

`RcsContentValidator` service enforces:
- Single card = exactly 1 card; Carousel = 2–10 cards
- Max 4 buttons per card, label max 25 chars
- Button types: `url`, `phone`, `calendar`, `postback`
- Text body max 2,000 chars
- All carousel cards must use same orientation
- Before send: all media assets must be finalized (`is_draft = false`)

Wired into `CampaignService::create()`, `update()`, and `validateForSend()`.

### 6. Draft Cleanup

`php artisan rcs:cleanup-drafts` — scheduled daily at 03:00, removes stale draft media assets.

---

## Files Changed

| File | Change |
|------|--------|
| `app/Http/Controllers/Api/CampaignApiController.php` | Added `rcs_carousel` to type validation |
| `app/Http/Controllers/Api/RcsAssetController.php` | **New** — Media pipeline API |
| `app/Http/Controllers/QuickSMSController.php` | New `confirmAndSend()` method; expanded `storeCampaignConfig` allowed fields |
| `app/Services/RcsAssetService.php` | **New** — Image processing, crop, storage |
| `app/Services/RcsContentValidator.php` | **New** — Rich card/carousel validation |
| `app/Services/Campaign/CampaignService.php` | RCS validation wired into create/update/send |
| `app/Models/RcsAsset.php` | **New** — Media asset model with tenant scope |
| `app/Models/Campaign.php` | Added `TYPE_RCS_CAROUSEL`, `rcs_content` cast |
| `app/Models/MessageTemplate.php` | Added `TYPE_RCS_CAROUSEL`, `rcs_content` cast |
| `app/Console/Commands/CleanupDraftRcsAssets.php` | **New** — Draft cleanup command |
| `database/migrations/2026_02_27_000001_create_rcs_assets_table.php` | **New** — `rcs_assets` table |
| `resources/views/quicksms/messages/send-message.blade.php` | Wired `rcs_content`, IDs, recipient sources into session payload |
| `resources/views/quicksms/messages/confirm-campaign.blade.php` | Real `confirmSend()` hitting backend API |
| `routes/web.php` | Added `POST /messages/confirm-send` route |

---

## End-to-End Flow After Merge

1. User opens Send Message → selects Rich RCS channel → opens RCS Content Wizard modal
2. User uploads/imports media → `POST /api/rcs/assets/process-upload` or `process-url`
3. User builds rich card(s) with text, buttons in the wizard modal
4. User clicks "Continue" → `getRcsSendPayload()` serializes content → stored in session with recipient data
5. Confirm page displays campaign summary
6. User clicks "Confirm & Send" → `POST /messages/confirm-send` → creates Campaign record → sends via CampaignService
7. Success modal → redirect to campaign history

---

## Important Notes

- **Do not rebuild** the RCS wizard modal, `rcs-wizard.js`, or `rcs-preview-renderer.js` — they are already on the branch
- **Do not rebuild** the RCS Asset pipeline or RcsContentValidator — they are complete
- The `rcs_agents` table and RCS Agent Registration are **separate systems** — the only relationship is that `campaigns.rcs_agent_id` references an approved agent as the sender
- If merge conflicts occur in `send-message.blade.php`, keep the version that includes `rcs_content` and `recipient_sources` in the `campaignConfig` object
