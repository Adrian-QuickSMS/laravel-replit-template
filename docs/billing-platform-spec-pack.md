# QuickSMS Billing Platform Spec Pack

**Document Version:** 1.0
**Date:** 20 February 2026
**Classification:** Internal — Architecture & Engineering
**Scope:** Billing, invoicing, pricing, balance tracking, and integration architecture

---

## Table of Contents

- [A. Current-State Architecture Map](#a-current-state-architecture-map)
- [B. Database Schema — Billing & Financial Tables](#b-database-schema--billing--financial-tables)
- [C. Service Layer — Billing Services](#c-service-layer--billing-services)
- [D. Controller & API Route Map](#d-controller--api-route-map)
- [E. UI Surface Map — Portal & Admin](#e-ui-surface-map--portal--admin)
- [F. Integration Architecture — HubSpot](#f-integration-architecture--hubspot)
- [G. Integration Architecture — Stripe](#g-integration-architecture--stripe)
- [H. Integration Architecture — Xero](#h-integration-architecture--xero)
- [I. Production-Readiness Gap Analysis](#i-production-readiness-gap-analysis)
- [J. Recommended Implementation Roadmap](#j-recommended-implementation-roadmap)

---

## A. Current-State Architecture Map

### A.1 System Overview

The billing subsystem spans three external services and multiple internal layers:

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        CUSTOMER PORTAL (Blade/jQuery)                   │
│                                                                         │
│  Dashboard ─── Purchase ─── Reporting ─── Account                      │
│  (Balance     (Messages,    (Finance      (Billing/VAT                 │
│   tile)        Numbers)      Data,         settings,                   │
│                              Invoices)     activation)                 │
└────────────────────────┬────────────────────────────────────────────────┘
                         │ AJAX / fetch()
┌────────────────────────▼────────────────────────────────────────────────┐
│                       API CONTROLLERS (Laravel)                         │
│                                                                         │
│  PurchaseApiController ── InvoiceApiController ── TopUpApiController    │
│  BillingApiController  ── WebhookController                            │
└────────────────────────┬────────────────────────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────────────────────────┐
│                       SERVICE LAYER                                     │
│                                                                         │
│  HubSpotProductService  ── HubSpotInvoiceService  ── StripeService     │
│  VatService             ── (NO XeroService)                            │
└──────┬────────────────────────────┬──────────────────────┬──────────────┘
       │                            │                      │
  ┌────▼────┐              ┌────────▼────────┐      ┌──────▼──────┐
  │ HubSpot │              │   PostgreSQL    │      │   Stripe    │
  │ CRM API │              │                 │      │ Checkout +  │
  │ (Products,             │ accounts        │      │ Webhooks    │
  │  Invoices,             │ account_credits │      └─────────────┘
  │  Companies)            │ account_settings│
  └─────────┘              └─────────────────┘
```

### A.2 Account Types & Billing Modes

The platform supports four account types defined as a PostgreSQL ENUM (`account_type`):

| Type | Description | Balance Model | Implemented? |
|------|-------------|---------------|--------------|
| `trial` | New signups, promotional credits only | Credit-based (free) | Partial — credits tracked, not consumed |
| `prepay` | Buy credits upfront, deduct per-message | Prepaid balance | NOT IMPLEMENTED — balance stored in Cache |
| `postpay` | Monthly invoice for usage | Credit limit + invoicing | NOT IMPLEMENTED — no invoice generation |
| `system` | Internal/platform account | N/A | Schema only |

### A.3 Currency Support

Multi-currency is supported at the pricing layer only:

- **Supported currencies:** GBP (default), EUR, USD
- **Account-level currency:** Stored in `account_settings.currency` (default `'GBP'`)
- **HubSpot pricing fields:** `hs_price_gbp`, `hs_price_eur`, `hs_price_usd`
- **Stripe sessions:** Currency passed through from account settings
- **Gap:** No exchange rate management; no multi-currency ledger

---

## B. Database Schema — Billing & Financial Tables

### B.1 Tables That Exist

#### `accounts` (Migration: `2026_02_10_000001`)

The tenant root table. Contains billing-related fields:

| Column | Type | Purpose |
|--------|------|---------|
| `id` | UUID (PK) | Tenant identifier |
| `account_type` | ENUM (`trial`, `prepay`, `postpay`, `system`) | Billing mode |
| `billing_email` | VARCHAR | Invoice recipient |
| `vat_number` | VARCHAR | VAT registration |
| `hubspot_company_id` | VARCHAR (unique) | HubSpot CRM link |
| `signup_credits_awarded` | INTEGER | Total promo credits given |
| `signup_promotion_code` | VARCHAR | Promo code used at signup |
| `billing_contact_name` | VARCHAR | Billing contact |
| `billing_contact_phone` | VARCHAR | Billing contact phone |
| `billing_address_*` | VARCHAR (6 fields) | Billing postal address |
| `vat_registered` | BOOLEAN | Whether VAT-registered |
| `vat_reverse_charges` | BOOLEAN | Reverse charge applicable |
| `tax_id` | VARCHAR | Tax identification number |
| `tax_country` | VARCHAR | Tax jurisdiction |
| `purchase_order_required` | BOOLEAN | PO number mandatory |
| `purchase_order_number` | VARCHAR | Active PO number |
| `payment_terms` | VARCHAR | Net 14 / Net 30 / etc. |

**RLS:** Enabled and forced. Tenant isolation via `app.current_tenant_id` session variable.

#### `account_credits` (Migration: `2026_02_10_000008`)

Tracks promotional and purchased credit awards per account.

| Column | Type | Purpose |
|--------|------|---------|
| `id` | BIGINT (PK, auto) | Record identifier |
| `account_id` | UUID (FK → accounts) | Tenant reference |
| `type` | ENUM (`signup_promo`, `mobile_verification`, `referral`, `purchased`, `bonus`, `compensation`) | Credit category |
| `credits_awarded` | INTEGER | Initial amount |
| `credits_used` | INTEGER | Amount consumed |
| `credits_remaining` | INTEGER | Available balance |
| `reason` | VARCHAR | Description |
| `reference_id` | VARCHAR | External reference (order ID, promo code) |
| `expires_at` | TIMESTAMP (nullable) | NULL = valid during trial |
| `expired_at` | TIMESTAMP (nullable) | When credits were forcibly expired |
| `awarded_by` | VARCHAR (nullable) | Admin who awarded (manual bonuses) |

**Indexes:** `(account_id, type)`, `(account_id, expires_at, credits_remaining)`
**RLS:** Enabled and forced.

#### `account_settings` (Migration: `2026_02_10_000007`)

One row per account. Contains billing notification preferences.

| Column | Type | Purpose |
|--------|------|---------|
| `account_id` | UUID (PK, FK → accounts) | One-to-one with accounts |
| `notify_low_balance` | BOOLEAN (default `true`) | Low balance alerts enabled |
| `low_balance_threshold` | DECIMAL(10,2) (default `10.00`) | Alert threshold (GBP) |
| `currency` | VARCHAR(3) (default `'GBP'`) | Account currency |
| `timezone` | VARCHAR(50) (default `'Europe/London'`) | Display timezone |

### B.2 Tables That Do NOT Exist (Gaps)

| Missing Table | Purpose | Impact |
|---------------|---------|--------|
| `account_balances` / `ledger_entries` | Persistent prepaid balance tracking | **CRITICAL** — Balance is stored in `Cache::put()` with 30-day TTL |
| `invoices` / `invoice_line_items` | Local invoice records | All invoices fetched live from HubSpot API; no local persistence |
| `payments` / `payment_transactions` | Payment history | Only exists as audit log entries |
| `pricing_overrides` | Per-account custom pricing | All pricing from HubSpot; no local override capability |
| `credit_limits` | Postpay credit limit tracking | No column on `accounts`; HubSpot `hs_credit_limit` property referenced but not local |
| `billing_runs` / `usage_summary` | Monthly billing cycle tracking | No billing run infrastructure |

---

## C. Service Layer — Billing Services

### C.1 HubSpotProductService

**File:** `app/Services/HubSpotProductService.php`
**Purpose:** Fetch live product pricing from HubSpot CRM Products API.
**Status:** Functional with mock fallback.

**Capabilities:**
- Fetches message products (SMS, RCS Basic, RCS Single, VMN, Shortcode, AI) via SKU mapping
- Fetches number products (VMN UK/International/Tollfree setup+monthly, Keyword setup+monthly)
- Multi-currency support via `hs_price_gbp`, `hs_price_eur`, `hs_price_usd` fields
- No caching (intentional — prices always fetched live)

**SKU Mappings:**

| Product Key | SKU | Mock Price (GBP) |
|-------------|-----|-----------------|
| `sms` | `QSMS-SMS` | £0.0395 (starter) / £0.034 (enterprise) |
| `rcs_basic` | `QSMS-RCS-BASIC` | £0.037 / £0.031 |
| `rcs_single` | `QSMS-RCS-SINGLE` | £0.05 / £0.045 |
| `vmn` | `QSMS-VMN` | £2.00 / £1.00 |
| `shortcode_keyword` | `QSMS-SHORTCODE` | £2.00 / £1.00 |
| `ai` | `QSMS-AI` | £0.25 / £0.20 |

**Mock Fallback:** When `HUBSPOT_ACCESS_TOKEN` is not set, `getMockProducts()` returns static pricing. The response includes `is_mock: true` flag.

**Invoice Creation:** `createInvoice()` method exists but is **NOT IMPLEMENTED**. Returns `{ success: false, error: "Invoice creation requires HubSpot Payments integration" }`. Contains TODO comments outlining the required flow (find/create contact → create deal → generate invoice → return Stripe URL).

### C.2 HubSpotInvoiceService

**File:** `app/Services/HubSpotInvoiceService.php`
**Purpose:** Fetch invoices and account billing summary from HubSpot CRM Invoices API.
**Status:** Functional for reads with mock fallback. No write operations.

**Capabilities:**
- `fetchInvoices(filters)` — Returns list of invoices with summary (paid/pending/overdue totals)
- `fetchInvoice(invoiceId)` — Returns single invoice with line items (via associations API)
- `fetchAccountSummary()` — Returns billing mode, balance, credit limit, available credit
- Status mapping: HubSpot → Portal (`open`→`pending`, `paid`→`paid`, `overdue`→`overdue`, `draft`→`draft`, `voided`→`cancelled`)

**Architecture Issue:** Uses `env('HUBSPOT_ACCESS_TOKEN')` directly instead of `config('services.hubspot.access_token')` like HubSpotProductService. Inconsistent config access pattern.

**Mock Data:** Returns 12 static invoices spanning Aug 2024 – Jan 2025 when API not configured. Mock account summary returns `{ billingMode: 'prepaid', currentBalance: 2450.00, creditLimit: 5000.00 }`.

### C.3 StripeService

**File:** `app/Services/StripeService.php`
**Purpose:** Create Stripe Checkout Sessions for invoice payments and balance top-ups.
**Status:** Functional for both use cases. PCI-compliant (portal never handles card data).

**Capabilities:**
- `createInvoicePaymentSession(invoiceData)` — Creates checkout session for paying a specific invoice
- `createTopUpSession(topUpData)` — Creates checkout session for balance top-up with VAT as separate line item
- `verifyWebhookSignature(payload, signature)` — Verifies Stripe webhook signatures
- `parseWebhookEvent(payload)` — Parses webhook JSON payload

**Session Metadata:**

For invoice payments:
```php
'metadata' => [
    'type' => 'invoice_payment',
    'invoice_id' => $invoiceId,
    'invoice_number' => $invoiceNumber,
]
```

For balance top-ups:
```php
'metadata' => [
    'type' => 'balance_topup',
    'tier' => $tier,          // starter | enterprise | bespoke
    'credit_amount' => $creditAmount,
    'vat_amount' => $vatAmount,
    'account_id' => $accountId,
]
```

**Mock Fallback:** When `STRIPE_SECRET_KEY` is not set, returns success URL directly with `isMock: true`.

**Security:**
- Webhook signature verification skipped (returns `true`) when `STRIPE_WEBHOOK_SECRET` not configured
- Config source inconsistency: `config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY')` — double fallback

### C.4 VatService

**File:** `app/Services/VatService.php`
**Purpose:** Calculate VAT on pricing.
**Status:** Implemented but simplistic.

- Fixed 20% VAT rate (UK standard)
- No EU/international VAT handling
- No reverse charge logic (field exists on `accounts.vat_reverse_charges` but unused by VatService)
- No VAT validation (e.g., VIES lookup)

### C.5 AccountObserver

**File:** `app/Observers/AccountObserver.php`
**Purpose:** Handle account lifecycle events.
**Status:** Implemented.

**Key billing-related behaviour:**
- When `account_type` changes from `trial` to `prepay`/`postpay`, all promotional credits (`signup_promo`, `mobile_verification`, `referral`) are expired via `markAsExpired()`
- Uses a transient property `_shouldExpireCredits` to defer work from `updating` to `updated` event
- Logs all status changes and credit expiry events

---

## D. Controller & API Route Map

### D.1 API Routes (routes/api.php)

| Method | URI | Controller | Status |
|--------|-----|------------|--------|
| GET | `/api/purchase/products` | `PurchaseApiController@getProducts` | Live (HubSpot) or mock |
| POST | `/api/purchase/calculate-order` | `PurchaseApiController@calculateOrder` | Functional |
| POST | `/api/purchase/create-invoice` | `PurchaseApiController@createInvoice` | **BROKEN** — HubSpot createInvoice not implemented |
| POST | `/api/topup/create-checkout-session` | `TopUpApiController@createCheckoutSession` | Functional (Stripe) |
| GET | `/api/invoices` | `InvoiceApiController@index` | Live (HubSpot) or mock |
| GET | `/api/invoices/account-summary` | `InvoiceApiController@accountSummary` | Mock data only |
| GET | `/api/invoices/{id}` | `InvoiceApiController@show` | Live (HubSpot) or mock |
| GET | `/api/invoices/{id}/pdf` | `InvoiceApiController@downloadPdf` | Proxy to HubSpot PDF URL |
| POST | `/api/invoices/{id}/create-checkout-session` | `InvoiceApiController@createCheckoutSession` | Functional (Stripe) |
| GET | `/api/billing/data` | `BillingApiController@getData` | **100% MOCK** — `generateMockBillingData()` |
| GET | `/api/billing/export` | `BillingApiController@export` | **MOCK** — no real export |
| GET | `/api/billing/saved-reports` | `BillingApiController@getSavedReports` | **MOCK** — hardcoded array |
| POST | `/api/billing/saved-reports` | `BillingApiController@saveReport` | **MOCK** — no persistence |
| POST | `/api/billing/schedule` | `BillingApiController@schedule` | **MOCK** — no job scheduling |
| POST | `/api/webhooks/stripe` | `WebhookController@stripeWebhook` | Functional but balance update uses Cache |
| POST | `/api/webhooks/hubspot/payment` | `WebhookController@hubspotPayment` | Partially functional — no signature verification |
| GET | `/api/balance` | `WebhookController@getAccountBalance` | **BROKEN** — reads from Cache |
| GET | `/api/payment-status` | `WebhookController@checkPaymentStatus` | Reads from Cache |

### D.2 Web Routes (routes/web.php) — Customer Portal

| Method | URI | Purpose |
|--------|-----|---------|
| GET | `/purchase` | Purchase landing page |
| GET | `/purchase/messages` | Buy SMS/RCS credits |
| GET | `/purchase/numbers` | Buy VMN/Keywords |
| GET | `/reporting/finance-data` | Finance Data reporting (mock) |
| GET | `/reporting/invoices` | Invoice listing & payment |

### D.3 Web Routes (routes/web.php) — Admin Panel

| Method | URI | Purpose |
|--------|-----|---------|
| GET | `/admin/accounts/balances` | Account balance overview |
| GET | `/admin/accounts/{id}/billing` | Per-account billing detail |
| GET | `/admin/billing/invoices` | All invoices |
| GET | `/admin/billing/payments` | Payment history |
| GET | `/admin/billing/credits` | Credit management |
| GET | `/admin/system/pricing` | Supplier pricing config |

### D.4 Web Routes — Numbers Purchase (QuickSMSController)

| Method | URI | Purpose |
|--------|-----|---------|
| GET | `/api/purchase/numbers/pricing` | Fetch number pricing (HubSpot) |
| POST | `/api/purchase/numbers/lock` | Lock numbers for purchase |
| POST | `/api/purchase/numbers/purchase` | Process number purchase |
| POST | `/api/purchase/numbers/release` | Release number locks |

### D.5 Controller Authentication Gaps

**Critical:** Several controllers use hardcoded demo values instead of authenticated user context:

| Controller | Method | Hardcoded Value |
|------------|--------|-----------------|
| `TopUpApiController` | `getCurrentAccountId()` | Returns `'ACC-001'` |
| `TopUpApiController` | `getCurrentUserId()` | Returns `'user_demo_001'` |
| `InvoiceApiController` | `getCurrentUserId()` | Returns `'user_demo_001'` |
| `PurchaseApiController` | `getAccountId()` | Returns `'ACC-001'` |
| `PurchaseApiController` | `isVatApplicable()` | Returns `true` (hardcoded) |
| `PurchaseApiController` | `getAccountCurrency()` | Returns `'GBP'` (hardcoded) |
| `WebhookController` | `getAccountBalance()` | Defaults to `'ACC-001'` |

These must be replaced with `auth()->user()->tenant_id` and account lookups before production.

---

## E. UI Surface Map — Portal & Admin

### E.1 Customer Portal Sidebar Navigation

```
Dashboard ................... Balance tile (£0.00 placeholder, links to /purchase)
Messages .................... Send, Inbox, Campaign History, Approvals
Contact Book ................ All Contacts, Lists, Tags, Opt-Out
Reporting
  ├── Dashboard
  ├── Message Log
  ├── Finance Data .......... BillingApiController (100% mock data)
  ├── Invoices .............. HubSpot invoice viewer + Stripe payment
  └── Download Area
Purchase
  ├── Messages .............. HubSpot pricing → Stripe checkout
  └── Numbers ............... HubSpot number pricing → purchase flow
Management .................. RCS, SenderID, Templates, API, Email-to-SMS, Numbers
Account
  ├── Details ............... Billing/VAT section (5-section activation wizard)
  ├── Users
  ├── Audit Logs
  └── Security
Support ..................... Dashboard, Create Ticket, Knowledge Base
```

### E.2 Admin Panel Sidebar Navigation

```
Dashboard
Accounts .................... Overview (includes billing controls)
Reporting
  ├── Message Log (Global)
  ├── Client Reporting
  └── Supplier Reporting
Management .................. SenderID Approvals, RCS, Campaigns, Templates, Numbers, Email-to-SMS
API & Integrations .......... Connections, Callbacks, Health
Invoices .................... admin.billing.invoices (single link)
Supplier Management ......... Suppliers, Gateways, Rate Cards, MCC/MNC
Security .................... Audit Logs, Country Controls, Spam Filter, IP Allow Lists, Admin Users
System Settings
  ├── Supplier Pricing
  ├── Routing Rules
  └── Platform Flags
```

### E.3 Dashboard Balance Tile

The customer dashboard shows a Balance tile that:
- Displays `£0.00` as default
- Links to `/purchase` on click
- Has loading skeleton and error states built in
- The actual balance value comes from `getAccountBalance()` which reads from `Cache` — NOT the database

---

## F. Integration Architecture — HubSpot

### F.1 Integration Points

| HubSpot Object | API Endpoint | QuickSMS Service | Direction |
|----------------|-------------|-------------------|-----------|
| Products | `/crm/v3/objects/products` | `HubSpotProductService` | Read only |
| Invoices | `/crm/v3/objects/invoices` | `HubSpotInvoiceService` | Read only |
| Line Items | `/crm/v3/objects/line_items/{id}` | `HubSpotInvoiceService` | Read only |
| Companies | `/crm/v3/objects/companies` | `HubSpotInvoiceService` | Read only |
| Webhooks | Inbound POST to `/api/webhooks/hubspot/payment` | `WebhookController` | Receive |

### F.2 Authentication

- **Method:** Bearer token via `HUBSPOT_ACCESS_TOKEN` secret
- **Config access inconsistency:**
  - `HubSpotProductService` uses `config('services.hubspot.access_token')` (correct)
  - `HubSpotInvoiceService` uses `env('HUBSPOT_ACCESS_TOKEN')` (incorrect — will fail with config cache)

### F.3 Data Flow — Product Pricing

```
User visits /purchase/messages
  → JS calls GET /api/purchase/products?currency=GBP
    → PurchaseApiController::getProducts()
      → HubSpotProductService::fetchProducts('GBP')
        → GET https://api.hubapi.com/crm/v3/objects/products?properties=name,price,hs_sku,...
        → Maps by SKU to internal product keys
        → Returns pricing array
      → VatService::calculateVat() applied to each product
    → JSON response with products + VAT breakdown
```

### F.4 Data Flow — Invoice Viewing

```
User visits /reporting/invoices
  → JS calls GET /api/invoices
    → InvoiceApiController::index()
      → HubSpotInvoiceService::fetchInvoices()
        → GET https://api.hubapi.com/crm/v3/objects/invoices?properties=...
        → Maps HS status to portal status
        → Calculates summary (paid/pending/overdue totals)
      → JSON response with invoices + summary
```

### F.5 HubSpot Webhook Handler

**Endpoint:** `POST /api/webhooks/hubspot/payment`
**Handled events:** `invoice.paid`, `deal.propertyChange`

**Critical issues:**
1. **No signature verification** — TODO comment exists but not implemented. Any POST to this endpoint is processed.
2. **Balance update goes to Cache** — `updateAccountBalance()` uses `Cache::put()` with 30-day TTL.
3. **No idempotency** — Same webhook delivered twice will double-count the balance.

### F.6 Account Linking

- `accounts.hubspot_company_id` stores the HubSpot Company ID (unique, indexed)
- `accounts.last_hubspot_sync` tracks sync timestamp
- `Account::scopeSyncedWithHubspot()` and `Account::isSyncedWithHubspot()` exist
- **Gap:** No bidirectional sync mechanism implemented. The fields exist but no sync job runs.

---

## G. Integration Architecture — Stripe

### G.1 Integration Points

| Feature | Method | Flow |
|---------|--------|------|
| Invoice Payment | `StripeService::createInvoicePaymentSession()` | Portal → Stripe Checkout → Webhook |
| Balance Top-Up | `StripeService::createTopUpSession()` | Portal → Stripe Checkout → Webhook |
| Webhook Handling | `WebhookController::stripeWebhook()` | Stripe → Portal webhook endpoint |

### G.2 Authentication & Configuration

| Config Key | Source | Purpose |
|------------|--------|---------|
| `STRIPE_SECRET_KEY` | Secret | API authentication |
| `STRIPE_WEBHOOK_SECRET` | Secret | Webhook signature verification |

### G.3 Checkout Session Flow

```
1. User clicks "Pay" or "Top Up" in portal
2. JS calls POST /api/invoices/{id}/create-checkout-session
   or POST /api/topup/create-checkout-session
3. Controller validates request, creates Stripe Checkout Session
4. Returns { checkoutUrl, sessionId }
5. JS redirects user to Stripe hosted payment page
6. User completes payment on Stripe
7. Stripe redirects to success_url (/reporting/invoices?payment=success)
8. Stripe sends webhook POST to /api/webhooks/stripe
9. WebhookController processes event
```

### G.4 Webhook Event Handling

| Event | Handler | Action |
|-------|---------|--------|
| `checkout.session.completed` (invoice) | `handleCheckoutCompleted()` | Logs audit, sets `invoice_paid_{id}` in Cache (24h TTL) |
| `checkout.session.completed` (topup) | `handleCheckoutCompleted()` | Logs audit, adds amount to Cache balance, notifies via Cache |
| `payment_intent.succeeded` | `handlePaymentSucceeded()` | Logs only |
| `payment_intent.payment_failed` | `handlePaymentFailed()` | Logs audit with error details |

### G.5 PCI Compliance

The implementation follows PCI DSS best practices:
- Portal **NEVER** handles, transmits, or stores card data
- All payment UI is Stripe's hosted Checkout page
- Payment confirmation is **webhook-only** (not client-side)
- No card numbers, CVVs, or sensitive payment data exist anywhere in the codebase

### G.6 Security Gaps

1. **Webhook secret optional** — When `STRIPE_WEBHOOK_SECRET` is not set, `verifyWebhookSignature()` returns `true`, accepting any POST
2. **No replay protection** — No idempotency key checking on webhook events
3. **No account ID verification** — Webhook handler trusts `metadata.account_id` from the session without verifying against the authenticated account

---

## H. Integration Architecture — Xero

### H.1 Status: NOT IMPLEMENTED

There is **zero Xero integration code** in the codebase:

- No `XeroService` class
- No Xero API calls
- No Xero OAuth configuration
- No Xero-related environment variables
- No Xero routes, controllers, or webhook handlers
- No reference to Xero in `composer.json` dependencies
- No Xero mentioned in `config/services.php`

Despite being listed in the project scope, Xero integration is entirely absent. If required, it would need to be built from scratch, covering:
- OAuth 2.0 authentication with Xero
- Contact/Organisation sync
- Invoice push (create/update invoices in Xero)
- Payment reconciliation
- Xero webhook subscription for payment notifications

---

## I. Production-Readiness Gap Analysis

### I.1 Critical Gaps (Must Fix Before Production)

| # | Gap | Current State | Required State | Affected Files |
|---|-----|---------------|----------------|----------------|
| C1 | **Balance stored in Cache, not database** | `Cache::put("account_balance_{id}", ...)` with 30-day TTL | Persistent ledger table with double-entry or running balance | `WebhookController.php` lines 243-249 |
| C2 | **No financial ledger table** | No `ledger_entries`, `transactions`, or `account_balances` table exists | PostgreSQL table with RLS, foreign keys, audit trail | Missing migration |
| C3 | **Hardcoded account/user IDs in controllers** | `getCurrentAccountId()` returns `'ACC-001'`, `getCurrentUserId()` returns `'user_demo_001'` | Must use `auth()->user()->tenant_id` | `TopUpApiController.php`, `InvoiceApiController.php`, `PurchaseApiController.php` |
| C4 | **BillingApiController is 100% mock** | `generateMockBillingData()` returns random billing data | Must query actual message usage from database | `BillingApiController.php` |
| C5 | **Credit consumption never called** | `AccountCredit::useCredits()` exists but is never invoked from message sending | Must integrate with message dispatch pipeline | `AccountCredit.php` line 159 |
| C6 | **Invoice creation not implemented** | `HubSpotProductService::createInvoice()` returns error stub | Must implement full HubSpot invoice creation or Stripe invoicing | `HubSpotProductService.php` lines 192-234 |
| C7 | **HubSpot webhook has no signature verification** | TODO comment at line 176 | Must implement `X-HubSpot-Signature` verification | `WebhookController.php` |
| C8 | **No idempotency on webhooks** | Duplicate webhooks double-count balance | Must track processed event IDs | `WebhookController.php` |

### I.2 High-Priority Gaps

| # | Gap | Current State | Required State |
|---|-----|---------------|----------------|
| H1 | **No local invoice storage** | Invoices fetched live from HubSpot every time | Local `invoices` + `invoice_line_items` tables for performance, offline access, and audit |
| H2 | **No payment transaction table** | Payments only exist as log entries | `payment_transactions` table linking Stripe sessions to accounts/invoices |
| H3 | **VAT reverse charge not enforced** | `accounts.vat_reverse_charges` field exists, `VatService` ignores it | VatService must check account's reverse charge status |
| H4 | **No credit limit on accounts** | `hs_credit_limit` referenced in HubSpot but no local field | Add `credit_limit` column to `accounts` or `account_settings` |
| H5 | **No low-balance notification system** | `account_settings.notify_low_balance` and `low_balance_threshold` exist but no job/trigger uses them | Scheduled job or balance-change trigger to send alerts |
| H6 | **No billing basis configuration** | No setting for "bill on submitted" vs "bill on delivered" | Per-account `billing_basis` configuration |
| H7 | **Config access inconsistency** | `HubSpotInvoiceService` uses `env()` directly | Must use `config()` to work with config caching |

### I.3 Medium-Priority Gaps

| # | Gap | Current State | Required State |
|---|-----|---------------|----------------|
| M1 | **No Xero integration** | Zero code | OAuth flow, invoice sync, payment reconciliation |
| M2 | **No pricing override tables** | All pricing from HubSpot live | Local per-account/per-tier pricing overrides |
| M3 | **No billing run infrastructure** | No scheduled jobs for postpay invoicing | Monthly billing run: aggregate usage → generate invoice → send |
| M4 | **No export implementation** | `BillingApiController::export()` returns mock download URL | Real CSV/Excel generation with async job for large exports |
| M5 | **No saved reports persistence** | `getSavedReports()` returns hardcoded array | Database-backed saved report configurations |
| M6 | **Dashboard balance tile disconnected** | Shows `£0.00` default, reads from Cache | Must read from persistent balance/credits |

### I.4 Architectural Observations

1. **Mock data fallback pattern:** All services (`HubSpotProductService`, `HubSpotInvoiceService`, `StripeService`) have `getMock*()` fallback methods. While useful for development, these must be either removed or gated behind an explicit `APP_ENV=local` check for production.

2. **Audit logging pattern:** Consistent use of `Log::channel('single')->info('[AUDIT]...')` across all financial controllers. However, this writes to the general log file rather than a dedicated financial audit log channel. For compliance, financial events should have a separate, tamper-evident audit trail.

3. **Account activation flow:** The 5-section activation wizard in `Account::updateActivationStatus()` includes billing/VAT as section 5. When all sections complete, `activated_at` is set, but the TODO comments note that trial credit expiry, welcome email, and live sending enablement are not yet triggered here. The `AccountObserver` handles credit expiry separately when `account_type` changes, which is the correct pattern.

4. **Tenant isolation verified:** All financial tables (`account_credits`, `account_settings`) have proper RLS policies with fail-closed design. The `accounts` table itself uses `id` matching against `app.current_tenant_id`. This is architecturally sound.

---

## J. Recommended Implementation Roadmap

### Phase 1 — Financial Foundation (Must-Have for MVP)

**Goal:** Persistent balance tracking and real account context.

1. **Create `account_balances` table** with columns: `account_id` (UUID FK), `balance` (DECIMAL), `currency`, `last_transaction_at`, `updated_by`. Enable RLS.
2. **Create `ledger_entries` table** with columns: `id` (UUID), `account_id`, `entry_type` (ENUM: credit_purchase, message_debit, refund, adjustment, promo_credit), `amount` (DECIMAL, signed), `running_balance`, `reference_type`, `reference_id`, `description`, `created_by`, `created_at`. Enable RLS.
3. **Replace all `Cache::put/get("account_balance_*")` calls** in `WebhookController` with database writes to `account_balances` + `ledger_entries`.
4. **Replace hardcoded account/user IDs** in `TopUpApiController`, `InvoiceApiController`, `PurchaseApiController` with `auth()->user()->tenant_id` and real account lookups.
5. **Wire `AccountCredit::useCredits()` into message dispatch** so trial credits are actually consumed.

### Phase 2 — Payment Infrastructure

**Goal:** End-to-end payment tracking with idempotency.

1. **Create `payment_transactions` table** linking Stripe session IDs to account IDs, amounts, statuses, and invoice references.
2. **Implement webhook idempotency** — store processed Stripe event IDs in `payment_transactions`, reject duplicates.
3. **Implement HubSpot webhook signature verification** using `X-HubSpot-Signature` header.
4. **Make Stripe webhook secret mandatory** in production (reject unsigned webhooks).

### Phase 3 — Invoice & Billing Engine

**Goal:** Local invoice management and postpay billing.

1. **Create `invoices` + `invoice_line_items` tables** with local storage, synced from HubSpot.
2. **Implement `BillingApiController`** with real usage data from message logs.
3. **Build billing run system** for postpay accounts (aggregate monthly usage → generate invoice).
4. **Implement VAT reverse charge logic** in `VatService` using account's `vat_reverse_charges` flag.

### Phase 4 — Integrations & Operations

**Goal:** Full integration suite and operational tooling.

1. **Fix HubSpot config inconsistency** — standardise on `config('services.hubspot.access_token')`.
2. **Implement Xero integration** (if required) — OAuth, invoice push, payment reconciliation.
3. **Build low-balance notification system** — scheduled job checking `account_balances` against `account_settings.low_balance_threshold`.
4. **Implement real CSV/Excel export** for Finance Data page.
5. **Remove or gate mock data fallbacks** behind `APP_ENV` check.
6. **Create dedicated financial audit log channel** separate from general application logs.

---

## Appendix: File Reference Index

| File | Purpose | Status |
|------|---------|--------|
| `app/Services/HubSpotProductService.php` | Product pricing from HubSpot | Functional (read) / Broken (write) |
| `app/Services/HubSpotInvoiceService.php` | Invoice fetching from HubSpot | Functional with mock fallback |
| `app/Services/StripeService.php` | Stripe Checkout Sessions | Functional |
| `app/Services/VatService.php` | VAT calculation | Simplistic (UK 20% only) |
| `app/Models/Account.php` | Tenant root model | Functional |
| `app/Models/AccountCredit.php` | Credit tracking model | Functional but `useCredits()` never called |
| `app/Models/AccountSettings.php` | Account settings model | Functional |
| `app/Observers/AccountObserver.php` | Account lifecycle events | Functional |
| `app/Http/Controllers/Api/WebhookController.php` | Stripe + HubSpot webhooks | **Critical gaps** — Cache-based balance |
| `app/Http/Controllers/Api/InvoiceApiController.php` | Invoice API | Functional (read) / demo user IDs |
| `app/Http/Controllers/Api/TopUpApiController.php` | Top-up checkout | Functional / demo user IDs |
| `app/Http/Controllers/Api/PurchaseApiController.php` | Product purchase API | Functional / demo user IDs |
| `app/Http/Controllers/Api/BillingApiController.php` | Finance Data API | **100% mock data** |
| `database/migrations/2026_02_10_000001_*` | Accounts table | Migrated |
| `database/migrations/2026_02_10_000007_*` | Account settings table | Migrated |
| `database/migrations/2026_02_10_000008_*` | Account credits table | Migrated |
| `routes/api.php` | API route definitions | Defined |
| `routes/web.php` | Web route definitions | Defined |
| `resources/views/elements/quicksms-sidebar.blade.php` | Customer nav | Rendered |
| `resources/views/elements/admin-sidebar.blade.php` | Admin nav | Rendered |
