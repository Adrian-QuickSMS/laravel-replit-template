# HR Module Phase 2 - Implementation Plan

## Core Constraint
**Maximum 5 additional days (20 quarter-day units) per year** across ALL additional pools combined:
- Carry-over from previous year
- Purchased holiday
- Gifted holiday / TOIL

This is enforced at the `leave_entitlements` level. The sum `carried_over_units + purchased_units + gifted_units` must never exceed 20 units.

---

## Feature 1: Purchased Holiday / TOIL (Time Off In Lieu)

**What:** Employees can request to purchase extra holiday days. Managers/HR admins can grant TOIL for overtime worked. Both consume from the shared 5-day additional pool.

### Schema Changes (migration)
- `leave_entitlements`: Add `gifted_units` integer column (default 0) — for TOIL/gifted days
- `company_hr_settings`: Add `allow_holiday_purchase` boolean (default false), `max_additional_days_per_year` integer (default 5), `allow_toil` boolean (default false)

> Note: `purchased_units` column already exists in `leave_entitlements`

### New Model
- `App\Models\Hr\HolidayAdjustmentRequest` — tracks purchase/TOIL requests
  - Columns: `id`, `tenant_id`, `employee_id`, `type` (purchase|toil|gift), `units_requested`, `status` (pending|approved|rejected|cancelled), `reason`, `approver_id`, `approved_at`, `rejected_at`, `year`, `timestamps`

### Service Changes
- `LeaveCalculationService`:
  - New method `getAdditionalDaysRemaining($employee, $year)` — returns how many of the 5-day cap is still available
  - Update `getBalanceSummary()` to include `purchased_days`, `gifted_days`, `carried_over_days` breakdown, and `additional_pool` showing used/remaining of 5-day cap
  - New method `validateAdditionalDaysCap($employee, $year, $newUnits)` — shared validation
- `LeaveRequestService`:
  - New method `submitPurchaseRequest($employee, $days, $reason)` — validates against 5-day cap
  - New method `grantToil($employee, $days, $reason, $grantor)` — manager action, validates cap
  - New method `approvePurchaseRequest($request, $approver)` — adds to `purchased_units`

### Controller Changes
- `MyLeaveController`: Add `purchaseHoliday()` endpoint for employees to request
- `HrAdminController`: Add `grantToil()`, `approvePurchase()`, `rejectPurchase()` endpoints

### View Changes
- `my-leave.blade.php`: Add "Purchase Holiday" section below request form showing remaining pool
- `admin.blade.php`: Add "TOIL/Adjustments" tab with grant form and pending purchase approvals
- Balance cards: Show breakdown (base + carry-over + purchased + gifted)

### Routes
```
POST /hr/my-leave/purchase          — Request to purchase holiday
POST /hr/admin/toil/grant           — Grant TOIL to employee
POST /hr/admin/purchase/{id}/approve — Approve purchase request
POST /hr/admin/purchase/{id}/reject  — Reject purchase request
```

---

## Feature 2: Year-End Carry-Over (Artisan Command)

**What:** Artisan command that runs at year-end to carry unused annual leave into the next year, respecting the 5-day cap.

### New Artisan Command
- `App\Console\Commands\Hr\ProcessYearEndCarryOver`
- Command signature: `hr:carry-over {--year=} {--tenant=} {--dry-run}`

### Logic
1. For each active employee in each tenant:
   - Calculate unused annual leave for the closing year (entitlement - used approved days)
   - Cap carry-over at min(unused, remaining_additional_pool_for_next_year) — respecting the 5-day max
   - The additional pool for next year = 20 units minus any already-purchased or gifted units for next year
   - If `--dry-run`, log what would happen without writing
2. Create/update next year's `LeaveEntitlement` with `carried_over_units`
3. Record audit log entries for each carry-over
4. Output summary table to console

### Settings Addition
- `company_hr_settings`: Add `carry_over_enabled` boolean (default true), `carry_over_max_days` integer (default 5 — but always capped at the 5-day global max)
- The `carry_over_max_days` is per-company configurable but cannot exceed the 5-day additional pool cap

### Admin UI
- Settings tab: Add carry-over toggle and max days field (capped at 5)
- Add "Run Carry-Over" button in admin for manual trigger (calls same logic)

---

## Feature 3: Manager-Scoped Approvals

**What:** Managers can only approve/reject leave for their direct reports. HR Admins retain full access.

### Changes to `HrAdminController`
- `index()`: Filter `pendingRequests` — managers see only direct reports, HR admins see all
- `approveRequest()` / `rejectRequest()`: Validate that the actor is either:
  - An HR admin (full access), OR
  - The employee's direct manager (`employee.manager_id === actor.id`)
- `employees()`: Managers see only direct reports, HR admins see all

### Changes to `HrDashboardController`
- `$teamPendingRequests`: Already shows team pending — scope to direct reports for managers

### Changes to `LeaveRequest` model
- Add scope `scopeForManager($query, $managerId)` — filters by employee's manager_id

### View Changes
- Approval queue: No UI changes needed (just filtered data)
- Employee tab: Managers see only their reports

---

## Feature 4: Team Calendar View (FullCalendar.js)

**What:** Visual calendar showing team absences with colour coding by leave type.

### New Controller
- `App\Http\Controllers\Hr\TeamCalendarController`
  - `index()` — renders calendar view
  - `events(Request $request)` — JSON API returning events for date range

### Events API Response Format
```json
[
  {
    "id": "uuid",
    "title": "John Smith - Annual Leave",
    "start": "2026-03-15",
    "end": "2026-03-18",
    "color": "#886CC0",
    "extendedProps": { "employee": "...", "type": "...", "status": "..." }
  }
]
```

### Colour Scheme
- Annual Leave: `#886CC0` (purple — matches existing theme)
- Sickness: `#FF6746` (red/orange)
- Medical: `#3F9AE0` (blue)
- Bank Holiday: `#6c757d` (grey)

### Privacy
- Respect `show_leave_type_in_notifications` setting
- If disabled: all events show as "On Leave" with neutral colour
- Managers see their reports; HR admins see everyone

### Frontend
- Include FullCalendar.js via CDN (`<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js">`)
- New view: `resources/views/quicksms/hr/calendar.blade.php`
- Month/week/list views
- Click event to show detail popover
- Filter by department, leave type

### Routes
```
GET /hr/calendar            — Calendar page
GET /hr/calendar/events     — JSON events API (?start=&end=)
```

### Sidebar
- Add "Team Calendar" link between "My Leave" and "Admin"

---

## Feature 5: Slack/Teams Webhook Notifications

**What:** Send leave notifications to Slack or Microsoft Teams channels via incoming webhooks.

### Schema Changes
- `company_hr_settings`: Add `webhook_url` string (nullable), `webhook_platform` enum (null|slack|teams), `webhook_events` JSON array (default: ["approved", "submitted"])

### New Service
- `App\Services\Hr\WebhookNotificationService`
  - `sendNotification($event, $leaveRequest)` — formats and sends payload
  - `buildSlackPayload($event, $leaveRequest)` — Slack Block Kit format
  - `buildTeamsPayload($event, $leaveRequest)` — Teams Adaptive Card format
  - Uses `Http::timeout(5)->post()` — fire-and-forget, log failures, don't block

### Integration Points
- `LeaveRequestService::submitRequest()` — fire webhook after successful create
- `LeaveRequestService::approveRequest()` — fire webhook after approval
- `LeaveRequestService::rejectRequest()` — fire webhook after rejection
- `LeaveRequestService::cancelRequest()` — fire webhook after cancellation

### Privacy
- Respect `show_leave_type_in_notifications` — webhook messages say "On Leave" if disabled
- Never include employee notes in webhook messages

### Admin UI
- Settings tab: Add webhook URL field, platform dropdown (Slack/Teams), event checkboxes
- "Test Webhook" button to send a test message

### Routes
```
POST /hr/admin/settings/test-webhook — Send test webhook
```

---

## Feature 6: Self-Service Bank Holiday Management

**What:** HR admins can add, edit, and remove bank holidays. Option to auto-import UK bank holidays from GOV.UK API.

### New Controller
- `App\Http\Controllers\Hr\BankHolidayController`
  - `index()` — list bank holidays for current/next year
  - `store(Request $request)` — add a bank holiday
  - `update(Request $request, $id)` — edit a bank holiday
  - `destroy($id)` — remove a bank holiday
  - `importUk(Request $request)` — fetch from GOV.UK API for given year

### GOV.UK Import
- Endpoint: `https://www.gov.uk/bank-holidays.json`
- Parse `england-and-wales.events` array
- Create `bank_holidays` records with `tenant_id` set (not system-wide null)
- Skip duplicates by date

### Admin UI
- New tab in admin.blade.php: "Bank Holidays"
- Table showing holidays with name, date, region
- Add/edit modal with date picker and name field
- "Import UK Bank Holidays" button with year selector
- Delete with confirmation

### Routes
```
GET    /hr/admin/bank-holidays            — List
POST   /hr/admin/bank-holidays            — Create
PUT    /hr/admin/bank-holidays/{id}       — Update
DELETE /hr/admin/bank-holidays/{id}       — Delete
POST   /hr/admin/bank-holidays/import-uk  — Import from GOV.UK
```

---

## Feature 7: Bradford Factor (Sickness Pattern Analysis)

**What:** Calculate and display the Bradford Factor score for each employee to identify sickness absence patterns. Score = S x S x D where S = number of sickness spells, D = total sickness days.

### New Service
- `App\Services\Hr\BradfordFactorService`
  - `calculateForEmployee($employee, $fromDate, $toDate)` — returns `['score' => int, 'spells' => int, 'total_days' => float, 'rating' => string]`
  - `calculateForTeam($tenantId, $fromDate, $toDate)` — batch calculation
  - `getRating($score)` — returns threshold label:
    - 0-49: "Low" (green)
    - 50-124: "Moderate" (amber)
    - 125-399: "Concerning" (orange)
    - 400-649: "High" (red)
    - 650+: "Critical" (dark red)

### Spell Detection Logic
- A "spell" = one continuous period of approved sickness absence
- Adjacent working days of sickness = same spell
- Gap of 1+ working day = new spell
- Only counts `leave_type = 'sickness'` with `status = 'approved'`

### Controller Changes
- `HrAdminController`: Add `bradfordReport()` method
  - Returns employee list with Bradford scores for rolling 12 months
  - Filterable by department, date range

### Admin UI
- New tab in admin.blade.php: "Bradford Factor"
- Table: Employee, Department, Spells, Total Days, Score, Rating (colour-coded badge)
- Date range filter (default: rolling 12 months)
- Click employee row to see spell breakdown
- Tooltip explaining the formula

### Routes
```
GET /hr/admin/bradford          — Bradford Factor report
GET /hr/admin/bradford/{id}     — Individual employee detail (JSON)
```

---

## Feature 8: Gifted Holiday / TOIL Tracking

**What:** Managers can gift additional holiday days to employees (e.g., for going above and beyond). These draw from the same 5-day additional pool as carry-over and purchased days.

> Note: This is closely related to Feature 1 (TOIL). The `gifted_units` column and `HolidayAdjustmentRequest` model from Feature 1 handle this. This feature focuses on the manager-facing gift workflow and tracking UI.

### Service Changes (extends Feature 1)
- `LeaveRequestService::giftHoliday($employee, $days, $reason, $grantor)`:
  - Validates against 5-day additional pool cap
  - Adds to `gifted_units` on the entitlement
  - Records audit log with `ACTION_HOLIDAY_GIFTED`
  - Creates `HolidayAdjustmentRequest` with type=`gift` for tracking

### Audit Log
- New constant: `LeaveAuditLog::ACTION_HOLIDAY_GIFTED`
- New constant: `LeaveAuditLog::ACTION_HOLIDAY_PURCHASED`
- New constant: `LeaveAuditLog::ACTION_CARRY_OVER_PROCESSED`

### Controller Changes
- `HrAdminController::giftHoliday()` — endpoint for managers to gift days

### Admin UI
- "TOIL/Adjustments" tab (from Feature 1): Add "Gift Holiday" section
  - Employee selector, number of days (0.25 step, max shows remaining pool), reason text
- Employee table: Add "Additional" column showing carry-over + purchased + gifted / 5 max

### Routes
```
POST /hr/admin/gift-holiday — Gift holiday to employee
```

---

## Database Migration Summary

Single new migration: `2026_03_12_000002_add_hr_phase2_columns.php`

### Alter `leave_entitlements`
- Add `gifted_units` integer default 0

### Alter `company_hr_settings`
- Add `allow_holiday_purchase` boolean default false
- Add `allow_toil` boolean default false
- Add `max_additional_days_per_year` integer default 5
- Add `carry_over_enabled` boolean default true
- Add `carry_over_max_days` integer default 5
- Add `webhook_url` string(500) nullable
- Add `webhook_platform` string(20) nullable
- Add `webhook_events` JSON nullable

### New table: `holiday_adjustment_requests`
- `id` UUID primary
- `tenant_id` UUID (FK accounts)
- `employee_id` UUID (FK employee_hr_profiles)
- `type` string(20) — purchase, toil, gift
- `units_requested` integer
- `year` integer
- `status` string(20) default 'pending'
- `reason` text nullable
- `approver_id` UUID nullable (FK employee_hr_profiles)
- `approved_at` timestamp nullable
- `rejected_at` timestamp nullable
- `timestamps`
- Index: employee_id + year

---

## Files to Create

| # | File | Purpose |
|---|------|---------|
| 1 | `database/migrations/2026_03_12_000002_add_hr_phase2_columns.php` | Schema changes |
| 2 | `app/Models/Hr/HolidayAdjustmentRequest.php` | Purchase/TOIL/Gift tracking model |
| 3 | `app/Services/Hr/BradfordFactorService.php` | Bradford Factor calculations |
| 4 | `app/Services/Hr/WebhookNotificationService.php` | Slack/Teams webhooks |
| 5 | `app/Http/Controllers/Hr/TeamCalendarController.php` | Calendar view + events API |
| 6 | `app/Http/Controllers/Hr/BankHolidayController.php` | Bank holiday CRUD + UK import |
| 7 | `app/Console/Commands/Hr/ProcessYearEndCarryOver.php` | Artisan carry-over command |
| 8 | `resources/views/quicksms/hr/calendar.blade.php` | FullCalendar team view |

## Files to Modify

| # | File | Changes |
|---|------|---------|
| 1 | `app/Models/Hr/LeaveEntitlement.php` | Add `gifted_units` to fillable/casts, update `total_available_units` |
| 2 | `app/Models/Hr/CompanyHrSettings.php` | Add new setting fields |
| 3 | `app/Models/Hr/LeaveAuditLog.php` | Add new action constants |
| 4 | `app/Services/Hr/LeaveCalculationService.php` | Add pool validation, update balance summary |
| 5 | `app/Services/Hr/LeaveRequestService.php` | Add purchase/TOIL/gift methods, webhook integration |
| 6 | `app/Http/Controllers/Hr/HrAdminController.php` | Manager scoping, TOIL/gift/purchase endpoints, Bradford tab, bank holidays tab |
| 7 | `app/Http/Controllers/Hr/HrDashboardController.php` | Manager-scoped team requests |
| 8 | `app/Http/Controllers/Hr/MyLeaveController.php` | Purchase holiday endpoint |
| 9 | `routes/web.php` | Add all new routes |
| 10 | `resources/views/quicksms/hr/admin.blade.php` | New tabs (Bank Holidays, Bradford, TOIL/Adjustments), settings fields |
| 11 | `resources/views/quicksms/hr/my-leave.blade.php` | Purchase section, enhanced balance breakdown |
| 12 | `resources/views/quicksms/hr/dashboard.blade.php` | Additional pool info on balance cards |
| 13 | `resources/views/elements/quicksms-sidebar.blade.php` | Add Team Calendar link |

---

## Implementation Order

1. **Migration + Models first** — schema changes and HolidayAdjustmentRequest model
2. **5-Day Cap Logic** — `LeaveCalculationService` pool validation (foundation for everything)
3. **Purchased Holiday / TOIL / Gifted** (Features 1 + 8) — closely related, build together
4. **Year-End Carry-Over** (Feature 2) — depends on cap logic
5. **Manager-Scoped Approvals** (Feature 3) — independent, quick win
6. **Bank Holiday Management** (Feature 6) — independent, used by calendar
7. **Team Calendar** (Feature 4) — depends on bank holidays for display
8. **Bradford Factor** (Feature 7) — independent analysis feature
9. **Webhook Notifications** (Feature 5) — integration layer, last to avoid blocking other work
