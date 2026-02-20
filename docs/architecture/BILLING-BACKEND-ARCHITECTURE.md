# QuickSMS Billing Backend Architecture
## Tier-1 CPaaS Financial Engine — Complete System Design

**Version:** 1.0
**Date:** 2026-02-20
**Status:** Architecture Specification
**Classification:** Internal — Commercial in Confidence

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Account Hierarchy & Tenant Model](#2-account-hierarchy--tenant-model)
3. [Double-Entry Ledger Engine](#3-double-entry-ledger-engine)
4. [Balance & Credit Engine](#4-balance--credit-engine)
5. [Test Credit System](#5-test-credit-system)
6. [Pricing Engine](#6-pricing-engine)
7. [Billing Engine — Message Lifecycle](#7-billing-engine--message-lifecycle)
8. [Invoice Engine & Xero Integration](#8-invoice-engine--xero-integration)
9. [Stripe Integration](#9-stripe-integration)
10. [HubSpot Pricing Sync](#10-hubspot-pricing-sync)
11. [Credit Notes & Refunds](#11-credit-notes--refunds)
12. [DLR Reconciliation Engine](#12-dlr-reconciliation-engine)
13. [Recurring Charges Engine](#13-recurring-charges-engine)
14. [Balance Alerts & Notifications](#14-balance-alerts--notifications)
15. [Admin Console vs Customer Portal](#15-admin-console-vs-customer-portal)
16. [Audit & Compliance Layer](#16-audit--compliance-layer)
17. [Database Schema](#17-database-schema)
18. [API Structure](#18-api-structure)
19. [Event Flows](#19-event-flows)
20. [Scale & Performance Design](#20-scale--performance-design)
21. [Reseller / White-Label Readiness](#21-reseller--white-label-readiness)
22. [FX Rate Management](#22-fx-rate-management)

---

## 1. System Overview

### 1.1 Architecture Principles

```
┌─────────────────────────────────────────────────────────────────┐
│                     QUICKSMS BILLING ENGINE                      │
│                                                                   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────┐ │
│  │ LEDGER   │  │ PRICING  │  │ BILLING  │  │ INVOICE          │ │
│  │ ENGINE   │  │ ENGINE   │  │ ENGINE   │  │ ENGINE           │ │
│  │          │  │          │  │          │  │                  │ │
│  │ Double   │  │ Waterfall│  │ Prepay / │  │ Generation +     │ │
│  │ Entry    │  │ Lookup   │  │ Postpay  │  │ Xero Push        │ │
│  │ Immutable│  │ Per-Ctry │  │ Deduction│  │ Webhook Receive  │ │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────────────┘ │
│       │              │              │              │               │
│  ┌────┴──────────────┴──────────────┴──────────────┴─────────┐   │
│  │              PostgreSQL — Source of Truth                    │   │
│  └─────────────────────────────────────────────────────────────┘   │
│                                                                   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────────────┐ │
│  │ STRIPE   │  │ HUBSPOT  │  │ XERO     │  │ NOTIFICATION     │ │
│  │ SERVICE  │  │ SYNC     │  │ SERVICE  │  │ ENGINE           │ │
│  └──────────┘  └──────────┘  └──────────┘  └──────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

**Core Principles:**
- **Immutable ledger**: No UPDATE or DELETE on financial records. Ever.
- **Double-entry**: Every financial event produces balanced debit/credit pairs.
- **Synchronous balance checks**: API/E2S messages check balance per-transaction. Portal campaigns reserve full estimated cost upfront.
- **PostgreSQL**: All financial data in PostgreSQL with `SELECT FOR UPDATE` for atomic operations.
- **Idempotency**: Every financial operation carries an idempotency key to prevent duplicate processing.
- **Auditability**: Every mutation is traceable to an actor (admin, system, webhook) with timestamp and IP.

### 1.2 Module Responsibilities

| Module | Responsibility |
|--------|---------------|
| **Ledger Engine** | Append-only double-entry journal. Source of truth for all balances. |
| **Pricing Engine** | Waterfall price lookup. Tier → Bespoke → Override. Per-country/product. |
| **Billing Engine** | Deduction at submission. Campaign reservation. Sub-account cap enforcement. |
| **Invoice Engine** | Line item aggregation. Xero push. Status lifecycle. |
| **Stripe Service** | Checkout sessions. Auto top-up. Bacs DD collection. |
| **HubSpot Sync** | Bidirectional pricing sync. Conflict detection. Deal lifecycle. |
| **Xero Service** | Invoice creation. Credit notes. Payment webhook processing. |
| **Notification Engine** | Balance alerts. Dunning. Invoice reminders. |
| **Reconciliation Engine** | Daily DLR reconciliation. RCS→SMS fallback credits. |
| **Recurring Charges** | Monthly virtual numbers, shortcodes, platform fees, support fees. |

---

## 2. Account Hierarchy & Tenant Model

### 2.1 Three-Tier Account Structure

```
QuickSMS Platform
├── Direct Customer Account (billing_type: prepay|postpay)
│   ├── Sub-Account A (spending_cap: £500)
│   ├── Sub-Account B (spending_cap: £300)
│   └── Sub-Account C (spending_cap: unlimited)
│
├── Reseller Account (billing_type: prepay|postpay)
│   ├── Reseller Customer 1 (own pricing, own invoicing by reseller)
│   │   ├── Sub-Account X
│   │   └── Sub-Account Y
│   └── Reseller Customer 2
│       └── Sub-Account Z
```

### 2.2 Account Model Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `parent_account_id` | UUID nullable | Reseller parent (NULL for direct/reseller accounts) |
| `account_type` | enum | `direct`, `reseller`, `reseller_customer` |
| `billing_type` | enum | `prepay`, `postpay` |
| `billing_method` | enum | `submitted`, `delivered` |
| `product_tier` | enum | `starter`, `enterprise`, `bespoke` |
| `currency` | char(3) | Account currency. Default `GBP`. One per account, immutable after first transaction. |
| `credit_limit` | decimal(12,4) | Postpay credit limit. Default 0.00. |
| `payment_terms_days` | int | 15, 30, or 60. Default 30. |
| `platform_fee_monthly` | decimal(10,4) | Monthly platform/support fee. |
| `status` | enum | `trial`, `active`, `suspended`, `closed` |
| `xero_contact_id` | string | Xero contact reference |
| `hubspot_company_id` | string | HubSpot company reference |
| `hubspot_deal_id` | string | HubSpot deal reference |
| `vat_number` | string | UK VAT registration |
| `company_name` | string | Legal entity name |
| `trial_expires_at` | timestamp | 30 days from signup |

### 2.3 Sub-Account Model

Sub-accounts are **spending caps against the parent balance**, not isolated wallets.

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | Primary key |
| `account_id` | UUID FK | Parent account |
| `name` | string | Display name |
| `spending_limit` | decimal(12,4) nullable | NULL = unlimited |
| `spending_used_current_period` | decimal(12,4) | Resets each billing period |
| `period_reset_at` | timestamp | When the current period started |
| `status` | enum | `active`, `suspended` |

**Sub-account spending check (pseudocode):**
```
function canSubAccountSpend(subAccount, amount):
    if subAccount.spending_limit IS NULL:
        return true  // unlimited
    return (subAccount.spending_used_current_period + amount) <= subAccount.spending_limit
```

---

## 3. Double-Entry Ledger Engine

### 3.1 Chart of Accounts

Every QuickSMS account maps to a set of internal ledger accounts. These are **system-level GL accounts**, not customer-facing.

| Code | Name | Type | Description |
|------|------|------|-------------|
| `CASH` | Cash / Stripe | Asset | Money received via Stripe or bank transfer |
| `AR` | Accounts Receivable | Asset | Postpay invoices outstanding |
| `DEFERRED_REV` | Deferred Revenue | Liability | Prepay top-ups not yet consumed |
| `REVENUE_SMS` | SMS Revenue | Revenue | Recognised SMS revenue |
| `REVENUE_RCS` | RCS Revenue | Revenue | Recognised RCS revenue |
| `REVENUE_AI` | AI Query Revenue | Revenue | Recognised AI usage revenue |
| `REVENUE_RECURRING` | Recurring Revenue | Revenue | Platform fees, virtual numbers, support |
| `COGS` | Cost of Goods Sold | Expense | Supplier costs (gateway charges) |
| `SUPPLIER_PAY` | Supplier Payable | Liability | Amounts owed to gateway suppliers |
| `REFUND` | Refunds & Adjustments | Contra-Revenue | Credit notes, RCS fallback adjustments |

### 3.2 Ledger Entry Structure

Every financial event creates one **ledger_entry** (header) with two or more **ledger_lines** (debit/credit pairs).

**Invariant: For every ledger_entry, SUM(debit) = SUM(credit). This is enforced by a PostgreSQL CHECK constraint via a trigger.**

```
ledger_entries (IMMUTABLE — no UPDATE, no DELETE)
├── id: UUID
├── entry_type: enum (see below)
├── reference_type: string (polymorphic: 'message_log', 'invoice', 'stripe_payment', etc.)
├── reference_id: string
├── account_id: UUID FK
├── sub_account_id: UUID FK nullable
├── currency: char(3)
├── amount: decimal(12,4)  ← absolute value of the transaction
├── description: text
├── metadata: JSONB
├── idempotency_key: string UNIQUE  ← prevents duplicate entries
├── created_by: UUID nullable
├── created_at: timestamp (immutable)

ledger_lines (IMMUTABLE — no UPDATE, no DELETE)
├── id: UUID
├── ledger_entry_id: UUID FK
├── ledger_account_code: string FK
├── debit: decimal(12,4) default 0
├── credit: decimal(12,4) default 0
├── created_at: timestamp
```

### 3.3 Journal Entry Types

| Entry Type | Trigger | Debit | Credit |
|------------|---------|-------|--------|
| `top_up` | Stripe checkout success | CASH | DEFERRED_REV |
| `message_charge_prepay` | Message submitted (prepay) | DEFERRED_REV | REVENUE_SMS/RCS |
| `message_charge_postpay` | Message submitted (postpay) | AR | REVENUE_SMS/RCS |
| `supplier_cost` | Message dispatched to gateway | COGS | SUPPLIER_PAY |
| `rcs_fallback_adjustment` | Daily DLR reconciliation | REVENUE_RCS | DEFERRED_REV (prepay) or AR reduction (postpay) |
| `delivered_billing_refund` | DLR failed (delivered billing) | REVENUE_SMS/RCS | DEFERRED_REV or AR |
| `credit_note` | Admin issues credit note | REFUND | DEFERRED_REV (prepay wallet) or AR (postpay) |
| `invoice_payment` | Xero webhook: paid | CASH | AR |
| `postpay_advance` | Postpay mid-month top-up | CASH | AR |
| `platform_fee_prepay` | Monthly recurring (prepay) | DEFERRED_REV | REVENUE_RECURRING |
| `platform_fee_postpay` | Monthly recurring (postpay) | AR | REVENUE_RECURRING |
| `manual_adjustment` | Admin manual | Configurable | Configurable |
| `dd_collection` | Stripe Bacs DD success | CASH | AR |

### 3.4 Balance Derivation

The customer's **available balance** is derived from the ledger, but cached for performance.

```sql
-- Prepay available balance (from ledger)
SELECT
    COALESCE(SUM(ll.credit), 0) - COALESCE(SUM(ll.debit), 0) AS available_balance
FROM ledger_lines ll
JOIN ledger_entries le ON le.id = ll.ledger_entry_id
WHERE le.account_id = :account_id
AND ll.ledger_account_code = 'DEFERRED_REV';

-- Postpay available credit
SELECT
    a.credit_limit - COALESCE(SUM(
        CASE WHEN ll.ledger_account_code = 'AR' THEN ll.debit - ll.credit ELSE 0 END
    ), 0) AS available_credit
FROM accounts a
LEFT JOIN ledger_entries le ON le.account_id = a.id
LEFT JOIN ledger_lines ll ON ll.ledger_entry_id = le.id
WHERE a.id = :account_id;
```

**Cached in `account_balances` table (updated on every transaction):**

| Field | Type | Description |
|-------|------|-------------|
| `account_id` | UUID PK | |
| `currency` | char(3) | |
| `balance` | decimal(12,4) | Prepay: available funds. Postpay: credit remaining. |
| `reserved` | decimal(12,4) | Active campaign reservations |
| `credit_limit` | decimal(12,4) | Mirror of accounts.credit_limit |
| `effective_available` | decimal(12,4) | `balance - reserved` (prepay) or `credit_limit - outstanding + balance` (postpay) |
| `total_outstanding` | decimal(12,4) | Postpay: unpaid invoice total |
| `updated_at` | timestamp | |
| `last_reconciled_at` | timestamp | Last time balance was verified against ledger |

### 3.5 Ledger Immutability Enforcement

```sql
-- PostgreSQL: Prevent UPDATE and DELETE on ledger tables
CREATE RULE no_update_ledger_entries AS ON UPDATE TO ledger_entries DO INSTEAD NOTHING;
CREATE RULE no_delete_ledger_entries AS ON DELETE TO ledger_entries DO INSTEAD NOTHING;
CREATE RULE no_update_ledger_lines AS ON UPDATE TO ledger_lines DO INSTEAD NOTHING;
CREATE RULE no_delete_ledger_lines AS ON DELETE TO ledger_lines DO INSTEAD NOTHING;

-- Trigger to enforce balanced entries
CREATE OR REPLACE FUNCTION check_ledger_balance()
RETURNS TRIGGER AS $$
BEGIN
    IF (
        SELECT ABS(SUM(debit) - SUM(credit)) > 0.0001
        FROM ledger_lines
        WHERE ledger_entry_id = NEW.ledger_entry_id
    ) THEN
        RAISE EXCEPTION 'Ledger entry % is unbalanced', NEW.ledger_entry_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;
```

---

## 4. Balance & Credit Engine

### 4.1 Prepay Balance Flow

```
Customer tops up £500 via Stripe
        │
        ▼
┌─────────────────────────┐
│ Stripe checkout.session  │
│ .completed webhook       │
└──────────┬──────────────┘
           │
           ▼
┌─────────────────────────┐     ┌─────────────────────────┐
│ Ledger Entry:            │     │ account_balances:        │
│ DR: CASH £500            │────▶│ balance += £500          │
│ CR: DEFERRED_REV £500    │     │ effective_available += £500│
└─────────────────────────┘     └─────────────────────────┘
           │
           ▼
┌─────────────────────────┐
│ Invoice created:         │
│ Type: top_up             │
│ Status: paid             │
│ → Push to Xero           │
└─────────────────────────┘
```

### 4.2 Postpay Credit Flow

```
Postpay customer sends messages (credit_limit = £5,000)
        │
        ▼
┌─────────────────────────┐     ┌─────────────────────────┐
│ Ledger Entry:            │     │ account_balances:        │
│ DR: AR £0.035            │────▶│ total_outstanding += £0.035│
│ CR: REVENUE_SMS £0.035   │     │ effective_available -= £0.035│
└─────────────────────────┘     └─────────────────────────┘
           │
           ▼
┌─────────────────────────┐
│ Check: effective_available│
│ = credit_limit            │
│   - total_outstanding     │
│   + any advance payments  │
│                           │
│ If <= 0: BLOCK SENDING   │
└─────────────────────────┘
```

### 4.3 Postpay Mid-Month Top-Up

When a postpay customer tops up mid-cycle, it's an **advance payment against AR** (not prepay credit):

```
Ledger Entry:
  DR: CASH £1,000
  CR: AR £1,000

Effect: total_outstanding reduces by £1,000
        effective_available increases by £1,000
        Customer can keep sending
```

### 4.4 Balance Check Logic (Per-Transaction)

For **API and Email-to-SMS** (per message):

```php
class BalanceService
{
    public function deductForMessage(Account $account, SubAccount $subAccount, Money $cost): LedgerEntry
    {
        return DB::transaction(function () use ($account, $subAccount, $cost) {
            // Lock the balance row for atomic update
            $balance = AccountBalance::where('account_id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Check effective available
            if ($balance->effective_available->lessThan($cost)) {
                throw new InsufficientBalanceException($account, $cost, $balance->effective_available);
            }

            // Check sub-account spending cap
            if ($subAccount && $subAccount->spending_limit !== null) {
                $newSpend = $subAccount->spending_used_current_period->add($cost);
                if ($newSpend->greaterThan($subAccount->spending_limit)) {
                    throw new SubAccountSpendingLimitException($subAccount, $cost);
                }
                $subAccount->increment('spending_used_current_period', $cost->getAmount());
            }

            // Create immutable ledger entry
            $entry = $this->ledger->createEntry(
                type: $account->isPostpay() ? 'message_charge_postpay' : 'message_charge_prepay',
                account: $account,
                subAccount: $subAccount,
                amount: $cost,
                // ... reference, idempotency key
            );

            // Update cached balance
            $balance->decrement('effective_available', $cost->getAmount());
            if ($account->isPrepay()) {
                $balance->decrement('balance', $cost->getAmount());
            } else {
                $balance->increment('total_outstanding', $cost->getAmount());
            }

            return $entry;
        });
    }
}
```

### 4.5 Balance Check Logic (Campaign Reservation)

For **portal UI campaigns**, the entire estimated cost is reserved upfront:

```php
class CampaignReservationService
{
    public function reserveForCampaign(Account $account, Campaign $campaign, Money $estimatedTotal): CampaignReservation
    {
        return DB::transaction(function () use ($account, $campaign, $estimatedTotal) {
            $balance = AccountBalance::where('account_id', $account->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($balance->effective_available->lessThan($estimatedTotal)) {
                throw new InsufficientBalanceException($account, $estimatedTotal, $balance->effective_available);
            }

            $reservation = CampaignReservation::create([
                'account_id' => $account->id,
                'campaign_id' => $campaign->id,
                'reserved_amount' => $estimatedTotal,
                'used_amount' => Money::zero(),
                'released_amount' => Money::zero(),
                'status' => 'active',
                'expires_at' => now()->addHours(24),
            ]);

            $balance->increment('reserved', $estimatedTotal->getAmount());
            $balance->decrement('effective_available', $estimatedTotal->getAmount());

            return $reservation;
        });
    }

    // Called per-message as campaign sends
    public function consumeFromReservation(CampaignReservation $reservation, Money $actualCost): void
    {
        // Deduct from reservation, create ledger entry per message
        // If actual cost < estimated, remainder is released back at campaign end
    }

    // Called when campaign completes
    public function releaseRemainder(CampaignReservation $reservation): void
    {
        $unused = $reservation->reserved_amount - $reservation->used_amount;
        // Release unused funds back to effective_available
    }
}
```

---

## 5. Test Credit System

### 5.1 Design — Separate from Financial Ledger

Test credits are **integer message credits** stored in a completely separate system from the monetary ledger. They never mix.

```
┌─────────────────────────────┐     ┌─────────────────────────────┐
│     MONETARY LEDGER          │     │     TEST CREDIT SYSTEM       │
│ (Currency: GBP/EUR/USD)      │     │ (Integer credits)            │
│                               │     │                               │
│ Used when: account.status     │     │ Used when: account.status     │
│            = 'active'         │     │            = 'trial'          │
│                               │     │                               │
│ NEVER mixed with test credits │     │ NEVER mixed with real money   │
└─────────────────────────────┘     └─────────────────────────────┘
```

### 5.2 Test Credit Tables

**test_credit_wallets:**

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `account_id` | UUID FK | |
| `credits_total` | int | Total awarded (100 default + any admin bonuses) |
| `credits_used` | int | Total consumed |
| `credits_remaining` | int | `total - used` |
| `expires_at` | timestamp | 30 days from account creation |
| `expired` | boolean | Set true when trial ends or date passes |
| `created_at` | timestamp | |

**test_credit_transactions:**

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `wallet_id` | UUID FK | |
| `message_log_id` | UUID FK | |
| `credits_consumed` | int | 1 for UK SMS, 2 for RCS, 10 for international |
| `destination_type` | enum | `uk`, `international` |
| `product_type` | enum | `sms`, `rcs_basic`, `rcs_single` |
| `created_at` | timestamp | |

### 5.3 Test Credit Consumption Rates

| Product | Destination | Credits Consumed |
|---------|-------------|-----------------|
| SMS (1 segment) | UK | 1 |
| SMS (1 segment) | International | 10 |
| RCS Basic | UK | 2 |
| RCS Basic | International | 20 |
| RCS Single | UK | 2 |
| RCS Single | International | 20 |

**Multi-segment SMS:** Credits = segments × rate. E.g., 2-segment UK SMS = 2 credits.

### 5.4 Test Credit Lifecycle

```
Account Created (trial)
        │
        ▼
    Award 100 credits
    Set expires_at = now + 30 days
        │
        ├──── Customer sends test messages ────▶ Deduct credits
        │     (only to predefined test numbers)
        │
        ├──── Admin awards bonus credits ────▶ Increase credits_total
        │
        ├──── 30 days pass ────▶ Mark expired, credits_remaining = 0
        │
        └──── Account activated (trial → active) ────▶ Mark expired
              Credits are gone. Monetary ledger takes over.
```

### 5.5 Test Number Allowlist

Test mode messages can only be sent to numbers in the `test_number_allowlist` table:

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `account_id` | UUID FK | |
| `mobile_number` | string | E.164 format |
| `label` | string | "My mobile", "QA phone" |
| `added_by` | UUID | |
| `created_at` | timestamp | |

Maximum 10 test numbers per account.

---

## 6. Pricing Engine

### 6.1 Pricing Waterfall

The pricing engine resolves the **customer-facing price** for a message using a strict waterfall:

```
Input: account_id, product_type (sms|rcs_basic|rcs_single|ai_query), country_iso

Step 1: Determine product tier
        ├── IF account.product_tier = 'starter' OR 'enterprise'
        │   └── Lookup product_tier_prices WHERE tier AND product_type AND country_iso
        │       ├── Found → RETURN price
        │       └── Not found → Lookup default (country_iso IS NULL)
        │           ├── Found → RETURN price
        │           └── Not found → REJECT (destination not supported for this tier)
        │
        └── IF account.product_tier = 'bespoke'
            ├── Step 2a: Check admin override
            │   └── Lookup customer_prices WHERE account_id AND product_type AND country_iso AND source = 'admin_override'
            │       ├── Found → RETURN price
            │       └── Not found → continue
            │
            ├── Step 2b: Check HubSpot deal pricing
            │   └── Lookup customer_prices WHERE account_id AND product_type AND country_iso AND source = 'hubspot'
            │       ├── Found → RETURN price
            │       └── Not found → continue
            │
            └── Step 2c: Fall back to Enterprise tier pricing
                └── Lookup product_tier_prices WHERE tier = 'enterprise' AND product_type AND country_iso
                    ├── Found → RETURN price
                    └── Not found → REJECT
```

### 6.2 Product Tier Prices Table (Starter & Enterprise)

Fixed prices set by QuickSMS. Same for all customers on that tier.

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `product_tier` | enum | `starter`, `enterprise` |
| `product_type` | enum | `sms`, `rcs_basic`, `rcs_single`, `ai_query`, `virtual_number_monthly`, `shortcode_monthly`, `inbound_sms`, `support` |
| `country_iso` | char(2) nullable | NULL = default rate for unlisted countries |
| `unit_price` | decimal(10,6) | Price per unit (per segment, per query, per month) |
| `currency` | char(3) | Matches account currency |
| `valid_from` | date | |
| `valid_to` | date nullable | NULL = no expiry |
| `active` | boolean | |
| `created_by` | UUID | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 6.3 Customer Prices Table (Bespoke)

Per-customer pricing for bespoke accounts. Source tracks where the price came from.

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `account_id` | UUID FK | |
| `product_type` | enum | |
| `country_iso` | char(2) nullable | |
| `unit_price` | decimal(10,6) | |
| `currency` | char(3) | |
| `source` | enum | `hubspot`, `admin_override` |
| `hubspot_deal_line_item_id` | string nullable | |
| `set_by` | UUID nullable | Admin who set override |
| `set_at` | timestamp | When this price was set (for conflict detection) |
| `valid_from` | date | |
| `valid_to` | date nullable | |
| `active` | boolean | |
| `version` | int | Increment on each change for audit |
| `previous_version_id` | UUID nullable | Points to the previous record |
| `change_reason` | string nullable | |
| `created_at` | timestamp | |

### 6.4 Pricing Engine Service (Pseudocode)

```php
class PricingEngine
{
    public function resolvePrice(Account $account, string $productType, string $countryIso): PriceResult
    {
        // Step 1: Tier-based fixed pricing
        if (in_array($account->product_tier, ['starter', 'enterprise'])) {
            return $this->lookupTierPrice($account->product_tier, $productType, $countryIso, $account->currency);
        }

        // Step 2: Bespoke — waterfall
        // 2a: Admin override
        $override = CustomerPrice::where('account_id', $account->id)
            ->where('product_type', $productType)
            ->where('country_iso', $countryIso)
            ->where('source', 'admin_override')
            ->where('active', true)
            ->validAt(now())
            ->first();

        if ($override) {
            return new PriceResult($override->unit_price, $override->currency, 'admin_override');
        }

        // 2b: HubSpot deal pricing
        $hubspot = CustomerPrice::where('account_id', $account->id)
            ->where('product_type', $productType)
            ->where('country_iso', $countryIso)
            ->where('source', 'hubspot')
            ->where('active', true)
            ->validAt(now())
            ->first();

        if ($hubspot) {
            return new PriceResult($hubspot->unit_price, $hubspot->currency, 'hubspot');
        }

        // 2c: Fallback to Enterprise tier
        return $this->lookupTierPrice('enterprise', $productType, $countryIso, $account->currency);
    }

    public function calculateMessageCost(Account $account, string $productType, string $countryIso, int $segments): CostResult
    {
        $price = $this->resolvePrice($account, $productType, $countryIso);
        $totalCost = $price->unitPrice * $segments; // RCS segments always = 1

        // Also lookup supplier cost for margin tracking
        $supplierCost = $this->supplierCostEngine->lookupCost($countryIso, $productType, $gatewayId);

        return new CostResult(
            customerPrice: $totalCost,
            supplierCost: $supplierCost->total,
            margin: $totalCost - $supplierCost->total,
            priceSource: $price->source,
            rateCardId: $supplierCost->rateCardId,
        );
    }
}
```

### 6.5 Supplier Cost Engine

Internal-only. Determines what QuickSMS pays the gateway per message.

```
Input: mcc, mnc, gateway_id, product_type, sent_at

Lookup: rate_cards
  WHERE mcc = :mcc
  AND mnc = :mnc
  AND gateway_id = :gateway_id
  AND product_type = :product_type
  AND active = true
  AND valid_from <= :sent_at
  AND (valid_to IS NULL OR valid_to >= :sent_at)

Return: gbp_rate (converted using daily FX rate)
```

### 6.6 Margin Tracking

Every message generates a `supplier_cost_log` entry for real-time margin reporting:

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `message_log_id` | UUID FK | |
| `account_id` | UUID FK | |
| `rate_card_id` | UUID FK | |
| `country_iso` | char(2) | |
| `mcc` | char(3) | |
| `mnc` | char(3) | |
| `gateway_id` | UUID FK | |
| `product_type` | enum | |
| `segments` | int | |
| `customer_price` | decimal(10,6) | What customer paid |
| `supplier_cost_native` | decimal(10,6) | What gateway charges in native currency |
| `supplier_cost_gbp` | decimal(10,6) | Converted to GBP |
| `fx_rate` | decimal(10,6) | Rate used |
| `margin_amount` | decimal(10,6) | `customer_price - supplier_cost_gbp` |
| `margin_percentage` | decimal(5,2) | |
| `created_at` | timestamp | |

---

## 7. Billing Engine — Message Lifecycle

### 7.1 Complete Message Billing Flow

```
Customer submits message (API / Portal / Email-to-SMS)
        │
        ▼
┌───────────────────────────────────────┐
│ 1. VALIDATION                          │
│    ├── Account status = active?        │
│    ├── Country enabled?                │
│    ├── Sender ID approved?             │
│    ├── Content passes enforcement?     │
│    └── Destination on opt-out list?    │
└──────────────┬────────────────────────┘
               │ Pass
               ▼
┌───────────────────────────────────────┐
│ 2. PRICING                             │
│    ├── Determine country_iso from MCC  │
│    ├── Calculate segments              │
│    │   ├── GSM-7: ceil(len/160)        │
│    │   ├── Unicode: ceil(len/70)       │
│    │   └── RCS: always 1              │
│    ├── Resolve customer price          │
│    │   (waterfall: tier → override)    │
│    └── Resolve supplier cost           │
│        (rate_cards by MCC/MNC/gateway) │
└──────────────┬────────────────────────┘
               │
               ▼
┌───────────────────────────────────────┐
│ 3. BALANCE CHECK & DEDUCTION           │
│    ├── API/E2S: per-transaction lock   │
│    │   SELECT ... FOR UPDATE           │
│    │   Check effective_available >= cost│
│    │   Check sub-account cap           │
│    │   Deduct immediately              │
│    │                                   │
│    └── Portal campaign: reservation    │
│        Already reserved at campaign    │
│        confirmation. Consume from      │
│        reservation per message.        │
└──────────────┬────────────────────────┘
               │
               ▼
┌───────────────────────────────────────┐
│ 4. LEDGER ENTRY                        │
│    ├── Create ledger_entry             │
│    │   (idempotency_key = msg UUID)    │
│    ├── Create ledger_lines             │
│    │   DR: DEFERRED_REV or AR          │
│    │   CR: REVENUE_SMS or REVENUE_RCS  │
│    ├── Create supplier_cost_log        │
│    │   DR: COGS                        │
│    │   CR: SUPPLIER_PAY                │
│    └── Update account_balances cache   │
└──────────────┬────────────────────────┘
               │
               ▼
┌───────────────────────────────────────┐
│ 5. DISPATCH TO GATEWAY                 │
│    ├── Select gateway (routing rules)  │
│    ├── Submit to gateway API           │
│    └── Record message_log              │
│        (status: pending, cost, etc.)   │
└──────────────┬────────────────────────┘
               │
               ▼
┌───────────────────────────────────────┐
│ 6. DLR CALLBACK (async, later)         │
│    ├── Gateway sends delivery report   │
│    ├── Update message_log status       │
│    ├── IF RCS fallback to SMS:         │
│    │   Queue for daily reconciliation  │
│    ├── IF delivered billing + failed:  │
│    │   Queue for daily reconciliation  │
│    └── IF delivered billing + success: │
│        No action (already charged)     │
└───────────────────────────────────────┘
```

### 7.2 Enforcement-Blocked Messages

Messages blocked by the enforcement engine (spam filter, URL blacklist, content rules) are **still charged** because they were submitted by the customer. The message_log is created with:
- `status: 'rejected'`
- `billable_flag: true`
- `rejection_reason: 'enforcement_blocked'`

The ledger entry is created normally. This incentivises customers to maintain clean sending practices.

### 7.3 Billing Method Comparison

| Aspect | Submitted Billing | Delivered Billing |
|--------|------------------|-------------------|
| **Available to** | Starter, Enterprise, Bespoke | Bespoke only |
| **Charge point** | At submission | At submission (refund on failure) |
| **Undelivered** | Customer still pays | Customer refunded via daily reconciliation |
| **Gateway timeout** | Customer pays | Customer pays (no DLR = no refund) |
| **Enforcement blocked** | Customer pays | Customer pays |
| **RCS→SMS fallback** | Charge RCS, refund delta daily | Charge RCS, refund delta daily |

---

## 8. Invoice Engine & Xero Integration

### 8.1 Invoice Number Format

**Recommended format:** `QS-{YYYYMM}-{6-char alphanumeric}`

Example: `QS-202602-A3K9M2`

- `QS` — QuickSMS prefix (identifiable in bank statements)
- `YYYYMM` — Year/month of generation (aids filing)
- 6-char alphanumeric — Random, collision-resistant (36^6 = 2.1 billion combinations)
- Generated by the platform, sent to Xero as the invoice reference

### 8.2 Invoice Types

| Type | Trigger | Line Items | Payment |
|------|---------|------------|---------|
| **Usage** | Monthly (1st) or manual trigger | Per-country, per-product breakdown | Postpay terms |
| **Top-up** | Stripe checkout success | Single line "Account Top-Up £X" | Marked paid immediately |
| **Recurring** | Monthly (1st) | Virtual numbers, shortcodes, platform fees | Included in usage invoice OR separate |
| **Credit Note** | Admin manual | References original invoice | Applied to wallet or AR |

### 8.3 Invoice Lifecycle

```
                    ┌─────────┐
                    │  DRAFT  │  (Created in platform, line items assembled)
                    └────┬────┘
                         │ Push to Xero API
                         ▼
               ┌──────────────────┐
               │ SUBMITTED_TO_XERO│  (Xero received, invoice_id stored)
               └────────┬─────────┘
                        │ Xero processes, assigns number
                        ▼
                    ┌─────────┐
                    │ ISSUED  │  (Xero confirmed, PDF available)
                    └────┬────┘
                         │ Xero sends to customer
                         ▼
                    ┌─────────┐
                    │  SENT   │  (Customer notified)
                    └────┬────┘
                         │
              ┌──────────┼──────────┐
              ▼          ▼          ▼
        ┌──────────┐ ┌────────┐ ┌─────────┐
        │PARTIALLY │ │  PAID  │ │ OVERDUE │
        │  PAID    │ │        │ │         │
        └────┬─────┘ └────────┘ └────┬────┘
             │                       │
             └───────────┬───────────┘
                         ▼
              ┌──────────────────┐
              │ VOID / WRITTEN_OFF│  (Terminal states)
              └──────────────────┘
```

### 8.4 Invoice Generation Process (Monthly)

```php
class InvoiceGenerationService
{
    // Runs on 1st of each month (scheduled) or manual trigger
    public function generateMonthlyInvoice(Account $account, Carbon $periodStart, Carbon $periodEnd): Invoice
    {
        return DB::transaction(function () use ($account, $periodStart, $periodEnd) {

            // 1. Aggregate billable messages for the period
            $usageLines = MessageLog::where('account_id', $account->id)
                ->where('billable_flag', true)
                ->whereBetween('sent_time', [$periodStart, $periodEnd])
                ->whereNull('invoice_id') // Not yet invoiced
                ->selectRaw("
                    country,
                    type as product_type,
                    COUNT(*) as message_count,
                    SUM(fragments) as total_segments,
                    SUM(cost) as total_cost
                ")
                ->groupBy('country', 'type')
                ->get();

            // 2. Create invoice header
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'account_id' => $account->id,
                'invoice_type' => 'usage',
                'status' => 'draft',
                'currency' => $account->currency,
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'issued_date' => now(),
                'due_date' => now()->addDays($account->payment_terms_days),
                'payment_terms_days' => $account->payment_terms_days,
            ]);

            // 3. Create line items per country per product
            $subtotal = Money::zero($account->currency);
            foreach ($usageLines as $line) {
                $lineTotal = Money::of($line->total_cost, $account->currency);
                $taxAmount = $this->calculateVAT($account, $lineTotal);

                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'product_type' => $line->product_type,
                    'country_iso' => $line->country,
                    'description' => $this->formatLineDescription($line),
                    'quantity' => $line->total_segments,
                    'unit_price' => $lineTotal->dividedBy($line->total_segments),
                    'tax_rate' => $this->getVATRate($account),
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal->plus($taxAmount),
                ]);

                $subtotal = $subtotal->plus($lineTotal);
            }

            // 4. Add recurring charges
            $this->addRecurringChargeLines($invoice, $account, $periodStart, $periodEnd);

            // 5. Update invoice totals
            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $this->calculateTotalVAT($invoice),
                'total' => $subtotal->plus($this->calculateTotalVAT($invoice)),
                'amount_due' => $subtotal->plus($this->calculateTotalVAT($invoice)),
            ]);

            // 6. Mark messages as invoiced
            MessageLog::where('account_id', $account->id)
                ->where('billable_flag', true)
                ->whereBetween('sent_time', [$periodStart, $periodEnd])
                ->whereNull('invoice_id')
                ->update(['invoice_id' => $invoice->id]);

            // 7. Queue for Xero push
            dispatch(new PushInvoiceToXero($invoice));

            return $invoice;
        });
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'QS-' . now()->format('Ym') . '-';
        do {
            $number = $prefix . strtoupper(Str::random(6));
        } while (Invoice::where('invoice_number', $number)->exists());
        return $number;
    }
}
```

### 8.5 Xero Integration Service

```php
class XeroInvoiceService
{
    // Push invoice to Xero
    public function pushInvoice(Invoice $invoice): void
    {
        $account = $invoice->account;

        // Ensure Xero contact exists
        $xeroContactId = $this->ensureXeroContact($account);

        // Build Xero invoice payload
        $xeroPayload = [
            'Type' => 'ACCREC', // Accounts Receivable
            'Contact' => ['ContactID' => $xeroContactId],
            'InvoiceNumber' => $invoice->invoice_number, // Our ID is master
            'Reference' => $invoice->invoice_number,
            'Date' => $invoice->issued_date->format('Y-m-d'),
            'DueDate' => $invoice->due_date->format('Y-m-d'),
            'CurrencyCode' => $invoice->currency,
            'Status' => 'AUTHORISED',
            'LineItems' => $invoice->lineItems->map(fn ($li) => [
                'Description' => $li->description,
                'Quantity' => $li->quantity,
                'UnitAmount' => $li->unit_price,
                'TaxType' => $this->mapTaxType($li->tax_rate),
                'AccountCode' => $this->mapRevenueAccountCode($li->product_type),
                'LineAmount' => $li->line_total,
            ])->toArray(),
        ];

        // POST to Xero with rate limit handling
        $response = $this->xeroClient->createInvoice($xeroPayload);

        // Store Xero references
        $invoice->update([
            'xero_invoice_id' => $response['InvoiceID'],
            'xero_invoice_number' => $response['InvoiceNumber'],
            'status' => 'issued',
        ]);

        // For prepay top-ups: also create payment in Xero
        if ($invoice->invoice_type === 'top_up') {
            $this->createXeroPayment($invoice, $response['InvoiceID']);
            $invoice->update(['status' => 'paid', 'paid_date' => now()]);
        }
    }

    // Handle Xero webhook: invoice paid
    public function handlePaymentWebhook(array $payload): void
    {
        $xeroInvoiceId = $payload['InvoiceID'];
        $invoice = Invoice::where('xero_invoice_id', $xeroInvoiceId)->firstOrFail();

        $amountPaid = Money::of($payload['AmountPaid'], $invoice->currency);

        DB::transaction(function () use ($invoice, $amountPaid, $payload) {
            // Record payment
            Payment::create([
                'account_id' => $invoice->account_id,
                'invoice_id' => $invoice->id,
                'payment_method' => 'bank_transfer',
                'xero_payment_id' => $payload['PaymentID'],
                'currency' => $invoice->currency,
                'amount' => $amountPaid,
                'status' => 'succeeded',
                'paid_at' => Carbon::parse($payload['Date']),
            ]);

            // Create ledger entry
            $this->ledger->createEntry(
                type: 'invoice_payment',
                account: $invoice->account,
                amount: $amountPaid,
                reference: $invoice,
            );

            // Update invoice status
            $newAmountPaid = $invoice->amount_paid->plus($amountPaid);
            $newAmountDue = $invoice->total->minus($newAmountPaid);
            $newStatus = $newAmountDue->isZero() ? 'paid' : 'partially_paid';

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'amount_due' => $newAmountDue,
                'status' => $newStatus,
                'paid_date' => $newStatus === 'paid' ? now() : null,
            ]);

            // Reactivate account if it was suspended for overdue
            if ($invoice->account->status === 'suspended') {
                $this->checkAndReactivate($invoice->account);
            }
        });
    }

    // Xero contact sync (1:1 with parent account)
    private function ensureXeroContact(Account $account): string
    {
        if ($account->xero_contact_id) {
            return $account->xero_contact_id;
        }

        $response = $this->xeroClient->createContact([
            'Name' => $account->company_name,
            'EmailAddress' => $account->billing_email,
            'TaxNumber' => $account->vat_number,
            // ... address fields
        ]);

        $account->update(['xero_contact_id' => $response['ContactID']]);
        return $response['ContactID'];
    }
}
```

### 8.6 Xero Rate Limit Handling

Xero allows 60 API calls/minute. Invoice creation is queued:

```php
class PushInvoiceToXero implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public $tries = 5;

    public function backoff(): array
    {
        return [30, 60, 120, 300, 600]; // seconds
    }

    public function handle(XeroInvoiceService $xero): void
    {
        // Rate limiter: max 50 per minute (leave headroom)
        RateLimiter::attempt('xero-api', 50, function () use ($xero) {
            $xero->pushInvoice($this->invoice);
        }, 60);
    }

    public function retryUntil(): DateTime
    {
        return now()->addHours(24);
    }
}
```

---

## 9. Stripe Integration

### 9.1 Prepay Top-Up (Checkout Session)

```
Customer clicks "Top Up £100"
        │
        ▼
┌────────────────────────────┐
│ Platform creates Stripe     │
│ Checkout Session            │
│ mode: 'payment'             │
│ currency: account.currency  │
│ No card stored locally      │
└──────────┬─────────────────┘
           │ Customer completes payment
           ▼
┌────────────────────────────┐
│ Webhook: checkout.session   │
│ .completed                  │
└──────────┬─────────────────┘
           │
           ▼
┌────────────────────────────┐
│ 1. Verify webhook signature │
│ 2. Idempotency check       │
│    (session.id as key)      │
│ 3. Create ledger entry:     │
│    DR: CASH £100            │
│    CR: DEFERRED_REV £100    │
│ 4. Update account_balances  │
│    balance += £100          │
│ 5. Create invoice (top_up)  │
│    Status: paid             │
│ 6. Queue Xero push          │
│ 7. Check: was auto-top-up?  │
│    Log accordingly          │
└────────────────────────────┘
```

### 9.2 Auto Top-Up

```php
class AutoTopUpService
{
    // Called after every balance deduction
    public function checkAndTrigger(Account $account): void
    {
        $config = AutoTopUpConfig::where('account_id', $account->id)
            ->where('enabled', true)
            ->first();

        if (!$config) return;

        $balance = AccountBalance::where('account_id', $account->id)->first();

        if ($balance->effective_available->greaterThan($config->threshold_amount)) {
            return; // Balance is above threshold
        }

        // Safety: max N top-ups per day
        $todayCount = Payment::where('account_id', $account->id)
            ->where('payment_method', 'stripe_auto_topup')
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= $config->max_topups_per_day) {
            Log::warning('Auto top-up daily limit reached', ['account' => $account->id]);
            dispatch(new SendAutoTopUpLimitNotification($account));
            return;
        }

        // Create PaymentIntent using saved payment method (on Stripe, not local)
        $paymentIntent = Stripe::paymentIntents()->create([
            'amount' => $config->topup_amount->getMinorAmount(),
            'currency' => $account->currency,
            'customer' => $config->stripe_customer_id,
            'payment_method' => $config->stripe_payment_method_id,
            'off_session' => true,
            'confirm' => true,
        ]);

        // Handle result via webhook (payment_intent.succeeded)
    }
}
```

**Auto Top-Up Config:**

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `account_id` | UUID FK | |
| `enabled` | boolean | Default false |
| `threshold_amount` | decimal(10,4) | Trigger when balance drops below |
| `topup_amount` | decimal(10,4) | Amount to charge |
| `stripe_customer_id` | string | Stripe customer ID |
| `stripe_payment_method_id` | string | Stored on Stripe, NOT locally |
| `max_topups_per_day` | int | Safety limit, default 3 |
| `last_triggered_at` | timestamp nullable | |

### 9.3 Bacs Direct Debit (Postpay)

```
Monthly invoice generated
        │
        ▼
┌────────────────────────────┐
│ Invoice pushed to Xero      │
│ Xero sends to customer      │
└──────────┬─────────────────┘
           │ Due date approaches
           ▼
┌────────────────────────────┐
│ Platform initiates Stripe   │
│ PaymentIntent with:         │
│ payment_method_type: bacs   │
│ mandate: pre-authorised     │
└──────────┬─────────────────┘
           │ 3-5 business days (Bacs cycle)
           ▼
┌────────────────────────────┐
│ Webhook: payment_intent     │
│ .succeeded                  │
│                             │
│ 1. Create ledger entry:     │
│    DR: CASH                 │
│    CR: AR                   │
│ 2. Update invoice status    │
│ 3. Create Xero payment      │
│ 4. Check account suspension │
└────────────────────────────┘
```

### 9.4 Stripe Webhook Security

```php
class StripeWebhookController
{
    public function handle(Request $request): Response
    {
        // 1. Verify Stripe signature
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $event = Webhook::constructEvent($payload, $sigHeader, config('stripe.webhook_secret'));

        // 2. Idempotency: check if event already processed
        if (ProcessedStripeEvent::where('event_id', $event->id)->exists()) {
            return response('Already processed', 200);
        }

        // 3. Route to handler
        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutComplete($event),
            'payment_intent.succeeded' => $this->handlePaymentSuccess($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),
            'mandate.updated' => $this->handleMandateUpdate($event),
            default => Log::info('Unhandled Stripe event', ['type' => $event->type]),
        };

        // 4. Record as processed
        ProcessedStripeEvent::create(['event_id' => $event->id, 'type' => $event->type]);

        return response('OK', 200);
    }
}
```

---

## 10. HubSpot Pricing Sync

### 10.1 Bidirectional Sync with Conflict Detection

```
┌─────────────┐                    ┌─────────────┐
│   HUBSPOT   │◄──── Sync ────────▶│  PLATFORM   │
│             │    (bidirectional)  │             │
│ Deal Line   │                    │ customer_   │
│ Items       │                    │ prices      │
│             │                    │             │
│ Company     │                    │ accounts    │
│ Properties  │                    │             │
└──────┬──────┘                    └──────┬──────┘
       │                                  │
       └──────────┬───────────────────────┘
                  │
                  ▼
       ┌──────────────────┐
       │ pricing_sync_log  │
       │                   │
       │ Conflict detected?│
       │ ├── YES → Flag    │
       │ │   for human     │
       │ │   resolution    │
       │ └── NO → Apply    │
       │     change        │
       └──────────────────┘
```

### 10.2 Sync Direction: HubSpot → Platform

```php
class HubSpotPricingSyncService
{
    // Called by webhook or near-real-time polling (every 2 minutes)
    public function syncFromHubSpot(string $hubspotDealId): void
    {
        $deal = $this->hubspotClient->deals()->getById($hubspotDealId, ['associations', 'line_items']);
        $account = Account::where('hubspot_deal_id', $hubspotDealId)->firstOrFail();

        foreach ($deal->lineItems as $lineItem) {
            $productType = $this->mapHubSpotProductToType($lineItem->product_id);
            $countryIso = $this->extractCountryFromLineItem($lineItem);
            $newPrice = $lineItem->price;

            // Check for conflict
            $existing = CustomerPrice::where('account_id', $account->id)
                ->where('product_type', $productType)
                ->where('country_iso', $countryIso)
                ->where('active', true)
                ->first();

            if ($existing && $existing->source === 'admin_override') {
                // CONFLICT: Admin set a different price than HubSpot
                PricingSyncLog::create([
                    'account_id' => $account->id,
                    'field_path' => "{$productType}.{$countryIso}.unit_price",
                    'old_value' => (string) $existing->unit_price,
                    'new_value' => (string) $newPrice,
                    'source' => 'hubspot',
                    'hubspot_timestamp' => $deal->updatedAt,
                    'admin_timestamp' => $existing->set_at,
                    'conflict_detected' => true,
                ]);
                // Do NOT apply — human must resolve
                dispatch(new NotifyPricingConflict($account, $productType, $countryIso));
                continue;
            }

            // No conflict — apply
            $this->upsertCustomerPrice($account, $productType, $countryIso, $newPrice, 'hubspot', $lineItem->id);

            PricingSyncLog::create([
                'account_id' => $account->id,
                'field_path' => "{$productType}.{$countryIso}.unit_price",
                'old_value' => $existing ? (string) $existing->unit_price : null,
                'new_value' => (string) $newPrice,
                'source' => 'hubspot',
                'conflict_detected' => false,
            ]);
        }
    }
}
```

### 10.3 Sync Direction: Platform → HubSpot

```php
class HubSpotPricePushJob implements ShouldQueue
{
    // Queued when admin changes pricing in the platform
    public function handle(): void
    {
        $account = $this->account;
        $prices = CustomerPrice::where('account_id', $account->id)
            ->where('active', true)
            ->get();

        // Update HubSpot deal line items
        foreach ($prices as $price) {
            $this->hubspotClient->deals()->updateLineItem(
                $account->hubspot_deal_id,
                $this->mapToHubSpotLineItem($price)
            );
        }

        PricingSyncLog::create([
            'account_id' => $account->id,
            'source' => 'admin',
            'conflict_detected' => false,
        ]);
    }
}
```

### 10.4 Conflict Resolution UI

The admin console shows a **pricing conflicts** dashboard:

| Account | Field | HubSpot Value | Platform Value | HubSpot Changed | Platform Changed | Action |
|---------|-------|---------------|----------------|-----------------|------------------|--------|
| Acme Ltd | sms.GB | £0.035 | £0.029 | 2026-02-19 10:01 | 2026-02-19 10:02 | [Accept HS] [Accept Platform] [Custom] |

### 10.5 HubSpot Deal Lifecycle → Account Provisioning

```
HubSpot Deal Created
    │
    ▼
Deal Stage: "Qualified"
    → No platform action
    │
    ▼
Deal Stage: "Proposal Sent"
    → Pricing attached as line items
    │
    ▼
Deal Stage: "Closed Won"
    │
    ├── Webhook to platform
    ├── Create/activate account
    ├── Apply pricing from deal line items → customer_prices
    ├── Set product_tier based on deal properties
    ├── Set billing_type (prepay/postpay)
    └── Set payment_terms_days
```

### 10.6 Pricing Sync Log Table

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `account_id` | UUID FK | |
| `field_path` | string | e.g., `sms.GB.unit_price` |
| `old_value` | string nullable | |
| `new_value` | string | |
| `source` | enum | `hubspot`, `admin` |
| `hubspot_timestamp` | timestamp nullable | When HubSpot was modified |
| `admin_timestamp` | timestamp nullable | When admin made change |
| `conflict_detected` | boolean | |
| `conflict_resolved` | boolean | Default false |
| `resolved_by` | UUID nullable | |
| `resolved_at` | timestamp nullable | |
| `resolution` | enum nullable | `accept_hubspot`, `accept_admin`, `custom` |
| `created_at` | timestamp | |

---

## 11. Credit Notes & Refunds

### 11.1 Credit Note Lifecycle

```
Admin initiates credit note
        │
        ▼
┌────────────────────────────┐
│ Create credit_note record   │
│ Status: draft               │
│ Link to original invoice    │
│ (optional)                  │
└──────────┬─────────────────┘
           │ Admin confirms
           ▼
┌────────────────────────────┐
│ Create ledger entry:        │
│ DR: REFUND (contra-revenue) │
│ CR: DEFERRED_REV (prepay)   │
│     or AR (postpay)         │
│                             │
│ Update account_balances:    │
│ Prepay: balance increases   │
│ Postpay: outstanding reduces│
└──────────┬─────────────────┘
           │
           ▼
┌────────────────────────────┐
│ Push to Xero as Credit Note │
│ Apply against invoice if    │
│ specified                   │
│ Status: issued              │
└────────────────────────────┘
```

### 11.2 Credit Note Table

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `credit_note_number` | string unique | Format: `CN-{YYYYMM}-{RANDOM6}` |
| `account_id` | UUID FK | |
| `original_invoice_id` | UUID FK nullable | Invoice being credited |
| `xero_credit_note_id` | string nullable | Xero reference |
| `reason` | text | Admin must provide reason |
| `currency` | char(3) | |
| `subtotal` | decimal(12,4) | |
| `tax_amount` | decimal(12,4) | |
| `total` | decimal(12,4) | |
| `status` | enum | `draft`, `submitted_to_xero`, `issued`, `applied`, `void` |
| `applied_to_invoice_id` | UUID FK nullable | Which invoice it offsets (postpay) |
| `issued_by` | UUID | Admin who issued |
| `issued_date` | date | |
| `created_at` | timestamp | |

---

## 12. DLR Reconciliation Engine

### 12.1 Daily Reconciliation Process

Runs daily at 02:00 UTC. Processes all DLR events from the previous day that require financial adjustment.

```
┌────────────────────────────────────────────┐
│         DAILY DLR RECONCILIATION            │
│         Scheduled: 02:00 UTC daily          │
└──────────────────┬─────────────────────────┘
                   │
      ┌────────────┼────────────┐
      ▼            ▼            ▼
┌──────────┐ ┌──────────┐ ┌──────────┐
│ RCS→SMS  │ │ Delivered│ │ Delivered│
│ Fallback │ │ Billing  │ │ Billing  │
│ Refunds  │ │ Refunds  │ │ Confirms │
│          │ │ (failed) │ │ (no-op)  │
└────┬─────┘ └────┬─────┘ └──────────┘
     │             │
     ▼             ▼
┌──────────────────────────────┐
│ For each adjustment:          │
│ 1. Calculate delta            │
│ 2. Create ledger entry        │
│ 3. Update account_balances    │
│ 4. Mark reconciliation record │
│    as processed               │
└──────────────────────────────┘
```

### 12.2 DLR Reconciliation Queue Table

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `message_log_id` | UUID FK | |
| `account_id` | UUID FK | |
| `original_product_type` | enum | What customer was charged for |
| `actual_product_type` | enum | What DLR reports |
| `original_cost` | decimal(10,6) | Amount charged |
| `adjusted_cost` | decimal(10,6) | What should have been charged |
| `adjustment_amount` | decimal(10,6) | Refund amount |
| `adjustment_type` | enum | `rcs_to_sms_fallback`, `delivered_billing_refund` |
| `status` | enum | `pending`, `processed`, `failed` |
| `processed_at` | timestamp nullable | |
| `batch_id` | UUID nullable | Group reconciliations into batches |
| `created_at` | timestamp | |

### 12.3 RCS Fallback Reconciliation Logic

```php
class DlrReconciliationService
{
    public function processRcsFallback(DlrReconciliationRecord $record): void
    {
        $account = $record->account;
        $rcsCost = $record->original_cost;
        $smsCost = $record->adjusted_cost;
        $refundAmount = $rcsCost - $smsCost;

        if ($refundAmount <= 0) return; // SMS was more expensive (unlikely but safe)

        // Create refund ledger entry
        $this->ledger->createEntry(
            type: 'rcs_fallback_adjustment',
            account: $account,
            amount: Money::of($refundAmount, $account->currency),
            reference: $record->messageLog,
            description: "RCS→SMS fallback adjustment for message {$record->message_log_id}",
            idempotencyKey: "rcs-fallback-{$record->message_log_id}",
        );

        $record->update(['status' => 'processed', 'processed_at' => now()]);
    }
}
```

---

## 13. Recurring Charges Engine

### 13.1 Recurring Charge Types

| Type | Description | Billing |
|------|-------------|---------|
| `virtual_number` | Monthly rental per number | Prepay: deduct from wallet. Postpay: invoice line. |
| `shortcode` | Monthly rental per shortcode | Same as above |
| `platform_fee` | Monthly platform/support fee | Same as above |
| `support_fee` | Bespoke support tier fee | Same as above |

### 13.2 Recurring Charges Table

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `account_id` | UUID FK | |
| `charge_type` | enum | `virtual_number`, `shortcode`, `platform_fee`, `support_fee` |
| `description` | string | "Virtual number +447700900100" |
| `amount` | decimal(10,4) | Monthly amount |
| `currency` | char(3) | |
| `frequency` | enum | `monthly` |
| `next_charge_date` | date | |
| `active` | boolean | |
| `reference_type` | string nullable | Polymorphic: 'virtual_number', 'shortcode' |
| `reference_id` | UUID nullable | |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### 13.3 Monthly Charge Processing

```php
class RecurringChargeService
{
    // Runs 1st of each month
    public function processMonthlyCharges(): void
    {
        $charges = RecurringCharge::where('active', true)
            ->where('next_charge_date', '<=', today())
            ->with('account')
            ->cursor(); // Memory-efficient for large sets

        foreach ($charges as $charge) {
            $account = $charge->account;

            if ($account->isPrepay()) {
                $this->chargeFromWallet($account, $charge);
            } else {
                // Postpay: will be included in monthly invoice generation
                // Just mark as processed for this period
                $charge->update(['next_charge_date' => today()->addMonth()]);
            }
        }
    }

    private function chargeFromWallet(Account $account, RecurringCharge $charge): void
    {
        $balance = AccountBalance::where('account_id', $account->id)->lockForUpdate()->first();

        if ($balance->effective_available->lessThan(Money::of($charge->amount, $charge->currency))) {
            // Insufficient balance — suspend sending, notify customer and admin
            $account->update(['status' => 'suspended']);
            dispatch(new SendInsufficientBalanceNotification($account, $charge));
            return;
        }

        // Deduct and create ledger entry
        $this->ledger->createEntry(
            type: 'platform_fee_prepay',
            account: $account,
            amount: Money::of($charge->amount, $charge->currency),
            description: $charge->description,
        );

        $charge->update(['next_charge_date' => today()->addMonth()]);
    }
}
```

---

## 14. Balance Alerts & Notifications

### 14.1 Alert Configuration Table

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `account_id` | UUID FK | |
| `alert_type` | enum | `balance_low`, `credit_usage` |
| `threshold_percentage` | int | e.g., 75, 90, 95 |
| `notify_customer` | boolean | Default true |
| `notify_admin` | boolean | Default true |
| `last_triggered_at` | timestamp nullable | Prevent spam |
| `cooldown_hours` | int | Default 24 — don't re-alert within this window |

### 14.2 Alert Check Logic

```php
class BalanceAlertService
{
    // Called after every balance deduction
    public function checkAlerts(Account $account): void
    {
        $alerts = BalanceAlertConfig::where('account_id', $account->id)->get();
        $balance = AccountBalance::where('account_id', $account->id)->first();

        foreach ($alerts as $alert) {
            $usagePercentage = $this->calculateUsagePercentage($account, $balance);

            if ($usagePercentage >= $alert->threshold_percentage) {
                // Check cooldown
                if ($alert->last_triggered_at && $alert->last_triggered_at->diffInHours(now()) < $alert->cooldown_hours) {
                    continue;
                }

                $remainingBalance = $balance->effective_available;

                if ($alert->notify_customer) {
                    dispatch(new SendBalanceAlertToCustomer($account, $alert->threshold_percentage, $remainingBalance));
                }
                if ($alert->notify_admin) {
                    dispatch(new SendBalanceAlertToAdmin($account, $alert->threshold_percentage, $remainingBalance));
                }

                $alert->update(['last_triggered_at' => now()]);
            }
        }
    }

    private function calculateUsagePercentage(Account $account, AccountBalance $balance): float
    {
        if ($account->isPrepay()) {
            // % of balance consumed since last top-up
            $lastTopUp = LedgerEntry::where('account_id', $account->id)
                ->where('entry_type', 'top_up')
                ->latest()
                ->first();
            $startBalance = $lastTopUp ? $lastTopUp->amount : $balance->balance;
            if ($startBalance->isZero()) return 100;
            return (1 - ($balance->effective_available->getAmount() / $startBalance->getAmount())) * 100;
        } else {
            // % of credit limit used
            if ($account->credit_limit == 0) return 100;
            return ($balance->total_outstanding->getAmount() / $account->credit_limit) * 100;
        }
    }
}
```

### 14.3 Alert Message Template

**Customer email:**
> Subject: QuickSMS — Balance Alert: {threshold}% used
>
> You have used {threshold}% of your {prepay balance / credit limit}.
> Remaining: {currency} {remaining_amount}
>
> [Top Up Now] — button link to portal

**Admin email:**
> Subject: [Alert] {company_name} — {threshold}% balance consumed
>
> Account: {company_name} ({account_id})
> Type: {prepay/postpay}
> Threshold: {threshold}%
> Remaining: {currency} {remaining_amount}

### 14.4 Dunning Sequence (Postpay Overdue)

| Day | Action |
|-----|--------|
| Due date | Invoice becomes overdue. Status → `overdue` |
| Due + 7 | Email reminder #1 to customer + account manager |
| Due + 14 | Email reminder #2, escalate to admin |
| Due + 21 | Email reminder #3, warning: suspension imminent |
| Due + 30 | Account suspended. Admin notified. Manual intervention required. |

---

## 15. Admin Console vs Customer Portal

### 15.1 Capability Matrix

| Capability | Customer Portal | Admin Console |
|------------|----------------|---------------|
| View balance | Own account | All accounts |
| View transactions | Own ledger entries | All ledger entries |
| Top up (prepay) | Yes (Stripe) | Yes (manual adjustment) |
| View invoices | Own invoices | All invoices |
| Download invoice PDF | Own | All |
| View pricing | Own rates | All rates |
| Change pricing | No | Yes (creates customer_prices) |
| Change billing type | No | Yes |
| Change billing method | No | Yes |
| Set credit limit | No | Yes |
| Set payment terms | No | Yes |
| Issue credit note | No | Yes |
| Manual balance adjustment | No | Yes |
| View margin data | No | Yes |
| View supplier costs | No | Yes |
| Resolve pricing conflicts | No | Yes |
| Configure alerts | Yes (own) | Yes (any account) |
| Configure auto top-up | Yes | Yes (any account) |
| Generate invoice (manual) | No | Yes |
| Void invoice | No | Yes |
| Suspend/reactivate account | No | Yes |
| View DLR reconciliation | No | Yes |
| View audit log | No | Yes |
| Switch account prepay↔postpay | No | Yes |
| Manage recurring charges | No | Yes |
| Award test credits | No | Yes |

### 15.2 Admin Manual Adjustment

Admins can make direct balance adjustments with mandatory reason:

```php
class AdminBalanceAdjustmentService
{
    public function adjust(Account $account, Money $amount, string $direction, string $reason, AdminUser $admin): LedgerEntry
    {
        return DB::transaction(function () use ($account, $amount, $direction, $reason, $admin) {
            $entry = $this->ledger->createEntry(
                type: 'manual_adjustment',
                account: $account,
                amount: $amount,
                description: "Manual adjustment by {$admin->email}: {$reason}",
                createdBy: $admin->id,
                metadata: [
                    'direction' => $direction, // 'credit' or 'debit'
                    'reason' => $reason,
                    'admin_email' => $admin->email,
                    'admin_ip' => request()->ip(),
                ],
            );

            // Update balance
            if ($direction === 'credit') {
                AccountBalance::where('account_id', $account->id)
                    ->increment('balance', $amount->getAmount());
            } else {
                AccountBalance::where('account_id', $account->id)
                    ->decrement('balance', $amount->getAmount());
            }

            // Audit log
            FinancialAuditLog::create([
                'actor_id' => $admin->id,
                'actor_type' => 'admin',
                'action' => 'manual_balance_adjustment',
                'entity_type' => 'account',
                'entity_id' => $account->id,
                'old_values' => null,
                'new_values' => ['amount' => $amount, 'direction' => $direction, 'reason' => $reason],
                'ip_address' => request()->ip(),
            ]);

            return $entry;
        });
    }
}
```

---

## 16. Audit & Compliance Layer

### 16.1 Financial Audit Log

Every financial mutation is logged independently of the ledger.

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `actor_id` | UUID | Who performed the action |
| `actor_type` | enum | `admin`, `customer`, `system`, `webhook` |
| `action` | string | `top_up`, `price_change`, `credit_note_issued`, `invoice_voided`, etc. |
| `entity_type` | string | `account`, `invoice`, `customer_price`, `credit_note` |
| `entity_id` | UUID | |
| `old_values` | JSONB nullable | Previous state |
| `new_values` | JSONB nullable | New state |
| `ip_address` | inet | |
| `user_agent` | string nullable | |
| `created_at` | timestamp | Immutable |

### 16.2 Data Retention Policy

| Data Type | Retention | Basis |
|-----------|-----------|-------|
| Ledger entries | 6 years + current year | HMRC VAT requirement |
| Invoices | 6 years + current year | HMRC |
| Credit notes | 6 years + current year | HMRC |
| Payments | 6 years + current year | HMRC |
| Financial audit log | 6 years + current year | HMRC |
| Pricing history | 6 years + current year | Dispute resolution |
| Message logs | As required by Ofcom / business | Separate policy |

### 16.3 Immutability Guarantees

```sql
-- Database-level immutability: revoke UPDATE/DELETE on financial tables
-- Applied via migration
REVOKE UPDATE, DELETE ON ledger_entries FROM app_user;
REVOKE UPDATE, DELETE ON ledger_lines FROM app_user;
REVOKE UPDATE, DELETE ON financial_audit_log FROM app_user;

-- Application-level: Eloquent model protection
class LedgerEntry extends Model
{
    public $timestamps = false; // Only created_at, no updated_at

    // Block updates
    public static function boot()
    {
        parent::boot();
        static::updating(function () {
            throw new \RuntimeException('Ledger entries are immutable and cannot be updated.');
        });
        static::deleting(function () {
            throw new \RuntimeException('Ledger entries are immutable and cannot be deleted.');
        });
    }
}
```

### 16.4 Reconciliation Process

Daily automated reconciliation verifies cached balances match the ledger:

```php
class LedgerReconciliationService
{
    // Runs daily at 03:00 UTC
    public function reconcileAll(): void
    {
        Account::where('status', '!=', 'closed')->chunk(100, function ($accounts) {
            foreach ($accounts as $account) {
                $ledgerBalance = $this->calculateBalanceFromLedger($account);
                $cachedBalance = AccountBalance::where('account_id', $account->id)->first();

                if (abs($ledgerBalance - $cachedBalance->balance) > 0.001) {
                    // MISMATCH — alert finance team
                    Log::critical('Balance reconciliation mismatch', [
                        'account_id' => $account->id,
                        'ledger_balance' => $ledgerBalance,
                        'cached_balance' => $cachedBalance->balance,
                        'delta' => $ledgerBalance - $cachedBalance->balance,
                    ]);

                    dispatch(new NotifyReconciliationMismatch($account, $ledgerBalance, $cachedBalance->balance));

                    // Auto-correct cache to match ledger (ledger is source of truth)
                    $cachedBalance->update([
                        'balance' => $ledgerBalance,
                        'last_reconciled_at' => now(),
                    ]);
                } else {
                    $cachedBalance->update(['last_reconciled_at' => now()]);
                }
            }
        });
    }
}
```

---

## 17. Database Schema

### 17.1 Complete Table List

```
CORE ACCOUNTS
├── accounts                    — Customer/reseller accounts
├── sub_accounts                — Spending-capped sub-divisions
├── account_balances            — Cached balance (derived from ledger)

LEDGER
├── ledger_accounts             — Chart of accounts (GL categories)
├── ledger_entries              — Journal headers (IMMUTABLE)
├── ledger_lines                — Debit/credit lines (IMMUTABLE)

TEST CREDITS
├── test_credit_wallets         — Trial credit pools
├── test_credit_transactions    — Per-message credit usage
├── test_number_allowlist       — Approved test destinations

PRICING
├── product_tier_prices         — Fixed Starter/Enterprise rates
├── customer_prices             — Bespoke per-customer rates
├── rate_cards                  — Supplier costs (per MCC/MNC) [exists]
├── mcc_mnc_master              — Network reference data [exists]
├── fx_rates                    — Daily FX rates
├── pricing_sync_log            — HubSpot ↔ Platform conflict tracking

INVOICING
├── invoices                    — Invoice headers
├── invoice_line_items          — Per-country/product line items
├── credit_notes                — Formal credit note documents

PAYMENTS
├── payments                    — All payment records
├── auto_topup_configs          — Auto top-up settings
├── processed_stripe_events     — Idempotency for Stripe webhooks

BILLING
├── campaign_reservations       — UI campaign balance holds
├── dlr_reconciliation_queue    — RCS fallback + delivered billing adjustments
├── recurring_charges           — Monthly fees (virtual numbers, platform, support)
├── supplier_cost_log           — Per-message margin tracking

NOTIFICATIONS
├── balance_alert_configs       — Threshold alert settings
├── dunning_log                 — Overdue invoice reminder tracking

AUDIT
├── financial_audit_log         — All financial mutations (IMMUTABLE)
```

### 17.2 Entity Relationship Diagram (Text)

```
accounts ─────────────┬──── 1:N ────── sub_accounts
    │                 │
    │                 ├──── 1:1 ────── account_balances
    │                 │
    │                 ├──── 1:N ────── ledger_entries ──── 1:N ──── ledger_lines
    │                 │                                              │
    │                 │                                    ledger_accounts (FK)
    │                 │
    │                 ├──── 1:N ────── customer_prices
    │                 │
    │                 ├──── 1:N ────── invoices ──── 1:N ──── invoice_line_items
    │                 │                    │
    │                 │                    ├──── 1:N ──── payments
    │                 │                    │
    │                 │                    └──── 0:N ──── credit_notes
    │                 │
    │                 ├──── 1:N ────── recurring_charges
    │                 │
    │                 ├──── 1:1 ────── test_credit_wallets ── 1:N ── test_credit_transactions
    │                 │
    │                 ├──── 1:1 ────── auto_topup_configs
    │                 │
    │                 ├──── 1:N ────── balance_alert_configs
    │                 │
    │                 └──── 1:N ────── campaign_reservations
    │
    │ (self-referencing for reseller hierarchy)
    └──── parent_account_id → accounts.id
```

### 17.3 Critical Indexes

```sql
-- Ledger: fast balance calculation
CREATE INDEX idx_ledger_entries_account_type ON ledger_entries(account_id, entry_type);
CREATE INDEX idx_ledger_lines_entry ON ledger_lines(ledger_entry_id);
CREATE INDEX idx_ledger_lines_account ON ledger_lines(ledger_account_code);

-- Idempotency
CREATE UNIQUE INDEX idx_ledger_entries_idempotency ON ledger_entries(idempotency_key);

-- Pricing: waterfall lookup
CREATE INDEX idx_customer_prices_lookup ON customer_prices(account_id, product_type, country_iso, active);
CREATE INDEX idx_tier_prices_lookup ON product_tier_prices(product_tier, product_type, country_iso, active);

-- Invoice generation: find uninvoiced messages
CREATE INDEX idx_message_logs_billing ON message_logs(account_id, billable_flag, sent_time, invoice_id);

-- DLR reconciliation
CREATE INDEX idx_dlr_recon_pending ON dlr_reconciliation_queue(status, created_at) WHERE status = 'pending';

-- Balance: hot path lock
CREATE UNIQUE INDEX idx_account_balances_pk ON account_balances(account_id);

-- Supplier cost margin reporting
CREATE INDEX idx_supplier_cost_reporting ON supplier_cost_log(account_id, country_iso, product_type, created_at);
```

---

## 18. API Structure

### 18.1 Customer Portal APIs

```
BALANCE
  GET    /api/v1/balance                         → Current balance, credit limit, effective available
  GET    /api/v1/balance/transactions             → Paginated transaction history (from ledger)
  GET    /api/v1/balance/transactions/{id}        → Single transaction detail

TOP-UP
  POST   /api/v1/topup/checkout-session           → Create Stripe checkout session
  GET    /api/v1/topup/auto-topup                 → Get auto top-up config
  PUT    /api/v1/topup/auto-topup                 → Update auto top-up config

PRICING
  GET    /api/v1/pricing                          → My pricing (resolved via waterfall)
  GET    /api/v1/pricing/{country_iso}            → Price for specific country
  GET    /api/v1/pricing/estimate                 → Estimate campaign cost (recipients, segments)

INVOICES
  GET    /api/v1/invoices                         → Paginated invoice list
  GET    /api/v1/invoices/{id}                    → Invoice detail with line items
  GET    /api/v1/invoices/{id}/pdf                → Redirect to Xero PDF URL

ALERTS
  GET    /api/v1/alerts/balance                   → Get alert configurations
  POST   /api/v1/alerts/balance                   → Create alert threshold
  PUT    /api/v1/alerts/balance/{id}              → Update alert
  DELETE /api/v1/alerts/balance/{id}              → Remove alert

USAGE
  GET    /api/v1/usage/summary                    → Usage summary (current period)
  GET    /api/v1/usage/by-country                 → Breakdown by country
  GET    /api/v1/usage/by-product                 → Breakdown by product type
  GET    /api/v1/usage/by-sub-account             → Breakdown by sub-account
```

### 18.2 Admin Console APIs

```
ACCOUNTS
  GET    /api/admin/v1/accounts                   → List all accounts
  GET    /api/admin/v1/accounts/{id}              → Account detail (inc. balance, credit, tier)
  PUT    /api/admin/v1/accounts/{id}              → Update account settings
  PUT    /api/admin/v1/accounts/{id}/billing-type → Switch prepay↔postpay
  PUT    /api/admin/v1/accounts/{id}/billing-method → Switch submitted↔delivered
  PUT    /api/admin/v1/accounts/{id}/credit-limit → Set credit limit
  PUT    /api/admin/v1/accounts/{id}/payment-terms → Set payment terms
  POST   /api/admin/v1/accounts/{id}/suspend      → Suspend account
  POST   /api/admin/v1/accounts/{id}/reactivate   → Reactivate account

BALANCE (Admin)
  GET    /api/admin/v1/accounts/{id}/balance       → Balance detail
  GET    /api/admin/v1/accounts/{id}/transactions  → Full ledger history
  POST   /api/admin/v1/accounts/{id}/adjustment    → Manual balance adjustment (with reason)

PRICING (Admin)
  GET    /api/admin/v1/pricing/tiers               → View all tier pricing
  PUT    /api/admin/v1/pricing/tiers               → Update tier pricing
  GET    /api/admin/v1/accounts/{id}/pricing       → Customer-specific pricing
  PUT    /api/admin/v1/accounts/{id}/pricing       → Set/override customer pricing
  GET    /api/admin/v1/pricing/conflicts            → List unresolved pricing conflicts
  POST   /api/admin/v1/pricing/conflicts/{id}/resolve → Resolve a conflict

INVOICES (Admin)
  GET    /api/admin/v1/invoices                    → All invoices (filterable)
  POST   /api/admin/v1/accounts/{id}/invoices/generate → Manual invoice generation
  POST   /api/admin/v1/invoices/{id}/void          → Void an invoice

CREDIT NOTES (Admin)
  GET    /api/admin/v1/credit-notes                → All credit notes
  POST   /api/admin/v1/accounts/{id}/credit-notes  → Issue credit note
  POST   /api/admin/v1/credit-notes/{id}/void      → Void credit note

RECURRING CHARGES (Admin)
  GET    /api/admin/v1/accounts/{id}/recurring-charges → List recurring charges
  POST   /api/admin/v1/accounts/{id}/recurring-charges → Add recurring charge
  PUT    /api/admin/v1/recurring-charges/{id}       → Update charge
  DELETE /api/admin/v1/recurring-charges/{id}       → Deactivate charge

TEST CREDITS (Admin)
  GET    /api/admin/v1/accounts/{id}/test-credits   → View test credit wallet
  POST   /api/admin/v1/accounts/{id}/test-credits   → Award additional credits

MARGIN & REPORTING (Admin)
  GET    /api/admin/v1/reporting/margin              → Real-time margin dashboard
  GET    /api/admin/v1/reporting/margin/by-account   → Margin by customer
  GET    /api/admin/v1/reporting/margin/by-country   → Margin by destination
  GET    /api/admin/v1/reporting/margin/by-gateway   → Margin by supplier
  GET    /api/admin/v1/reporting/revenue             → Revenue recognition report

RECONCILIATION (Admin)
  GET    /api/admin/v1/reconciliation/dlr            → DLR reconciliation queue
  GET    /api/admin/v1/reconciliation/balance        → Balance reconciliation status
  POST   /api/admin/v1/reconciliation/balance/run    → Trigger manual reconciliation

AUDIT (Admin)
  GET    /api/admin/v1/audit/financial               → Financial audit log (paginated)

WEBHOOKS (Internal)
  POST   /api/webhooks/stripe                        → Stripe events
  POST   /api/webhooks/xero                          → Xero payment events
  POST   /api/webhooks/hubspot/deal                  → HubSpot deal changes
  POST   /api/webhooks/gateway/dlr                   → Gateway delivery reports
```

### 18.3 Middleware Stack

```php
// Customer Portal APIs
Route::prefix('api/v1')
    ->middleware(['auth:customer', 'tenant.scope', 'throttle:120,1'])
    ->group(/* customer routes */);

// Admin Console APIs
Route::prefix('api/admin/v1')
    ->middleware(['auth:admin', 'admin.rbac', 'audit.log', 'throttle:60,1'])
    ->group(/* admin routes */);

// Webhooks (no auth — signature verification in controller)
Route::prefix('api/webhooks')
    ->middleware(['throttle:300,1'])
    ->group(/* webhook routes */);
```

---

## 19. Event Flows

### 19.1 Complete Prepay Message Flow

```
1. Customer → POST /api/v1/messages/send
       ↓
2. MessageController validates payload
       ↓
3. EnforcementService checks content/sender/URL rules
       ↓  (blocked → still charged, message_log with status='rejected')
4. PricingEngine resolves price
       ├── Account tier = starter → product_tier_prices lookup
       ├── Account tier = enterprise → product_tier_prices lookup
       └── Account tier = bespoke → customer_prices waterfall
       ↓
5. SegmentCalculator determines fragment count
       ├── GSM-7: ceil(chars / 160)
       ├── Unicode: ceil(chars / 70)
       └── RCS: 1
       ↓
6. cost = unit_price × segments
       ↓
7. BalanceService.deductForMessage()
       ├── SELECT ... FOR UPDATE on account_balances
       ├── Check effective_available >= cost
       ├── Check sub_account spending cap
       ├── Create ledger_entry + ledger_lines
       ├── Update account_balances
       └── Return or throw InsufficientBalanceException
       ↓
8. SupplierCostEngine records margin
       ├── Lookup rate_card for MCC/MNC/gateway
       └── Create supplier_cost_log entry
       ↓
9. BalanceAlertService.checkAlerts()
       ├── Calculate usage percentage
       └── Send notifications if threshold crossed
       ↓
10. AutoTopUpService.checkAndTrigger()
       └── If balance < threshold → initiate Stripe charge
       ↓
11. GatewayDispatcher submits to selected gateway
       ├── Create message_log (status: pending)
       └── Return message_id to customer
       ↓
12. [ASYNC] Gateway DLR callback → /api/webhooks/gateway/dlr
       ├── Update message_log status
       ├── If RCS→SMS fallback: queue to dlr_reconciliation_queue
       └── If delivered billing + failed: queue to dlr_reconciliation_queue
       ↓
13. [DAILY 02:00] DlrReconciliationService processes queue
       ├── Calculate refund amounts
       ├── Create ledger entries for adjustments
       └── Update account_balances
```

### 19.2 Postpay Monthly Invoice Flow

```
1. [SCHEDULED: 1st of month, 04:00 UTC]
       ↓
2. InvoiceGenerationService.generateForAllPostpay()
       ↓
3. For each postpay account:
       ├── Query message_logs (billable, uninvoiced, in period)
       ├── Aggregate by country + product_type
       ├── Query recurring_charges for this period
       ├── Create invoice + line_items
       ├── Mark messages as invoiced
       └── Queue PushInvoiceToXero job
       ↓
4. [QUEUE] PushInvoiceToXero processes (rate limited)
       ├── Ensure Xero contact exists
       ├── POST invoice to Xero API
       ├── Store xero_invoice_id
       └── Update status → 'issued'
       ↓
5. Xero sends invoice to customer (email)
       ↓
6. [DAYS LATER] Customer pays (bank transfer or DD)
       ↓
7. Finance matches payment in Xero
       ↓
8. Xero webhook → /api/webhooks/xero
       ├── Verify Xero webhook signature
       ├── Find invoice by xero_invoice_id
       ├── Create payment record
       ├── Create ledger_entry (DR: CASH, CR: AR)
       ├── Update invoice status → 'paid'
       └── If account suspended → check reactivation
       ↓
9. [IF OVERDUE] DunningService sends reminders
       ├── Day 7: Reminder #1
       ├── Day 14: Reminder #2 + admin escalation
       ├── Day 21: Final warning
       └── Day 30: Suspend account
```

### 19.3 Stripe Auto Top-Up Flow

```
1. Balance deducted for message
       ↓
2. AutoTopUpService.checkAndTrigger()
       ├── Is auto top-up enabled? → No → exit
       ├── Is balance < threshold? → No → exit
       ├── Daily limit reached? → Yes → notify, exit
       └── Proceed
       ↓
3. Create Stripe PaymentIntent (off_session, confirm=true)
       ├── Uses stripe_customer_id + stripe_payment_method_id
       └── Amount = config.topup_amount
       ↓
4. [WEBHOOK] payment_intent.succeeded
       ├── Idempotency check
       ├── Create ledger_entry (DR: CASH, CR: DEFERRED_REV)
       ├── Update account_balances
       ├── Create invoice (top_up, paid immediately)
       └── Queue Xero push
       ↓
5. [WEBHOOK] payment_intent.payment_failed (if card declined)
       ├── Notify customer: "Auto top-up failed, please update payment method"
       ├── Notify admin
       └── Do NOT retry automatically (Stripe handles retries)
```

### 19.4 HubSpot Deal Closed Won → Account Activation

```
1. Sales rep closes deal in HubSpot
       ↓
2. HubSpot webhook → /api/webhooks/hubspot/deal
       ├── Payload: deal_id, stage='closedwon', line_items, company
       ↓
3. HubSpotDealService.handleClosedWon()
       ├── Find or create account from HubSpot company
       ├── Set product_tier from deal properties
       ├── Set billing_type (prepay/postpay)
       ├── Set payment_terms_days
       ├── Set credit_limit (if postpay)
       ↓
4. Extract pricing from deal line items
       ├── For each line item:
       │   ├── Map HubSpot product → product_type
       │   ├── Extract country_iso
       │   ├── Extract unit_price
       │   └── Create customer_prices (source: 'hubspot')
       ↓
5. Create account_balances row (zero balance)
       ↓
6. Create default balance_alert_configs
       ↓
7. Activate account (trial → active)
       ↓
8. Send welcome email with portal credentials
```

---

## 20. Scale & Performance Design

### 20.1 Hot Path: Per-Message Balance Deduction

At 1,000 messages/second, the balance deduction is the critical bottleneck.

**Design:**
- `account_balances` row locked with `SELECT FOR UPDATE` per transaction
- PostgreSQL handles row-level locking well up to ~5,000 TPS per row
- For a single account sending 1,000 msg/sec, this works because PostgreSQL advisory locks and connection pooling manage contention

**Optimization for extreme burst (single account, >1,000 msg/sec):**

```php
// Campaign reservation eliminates per-message lock contention
// Portal: reserve full amount → messages consume from reservation (no lock needed on main balance)
// API burst: use batch reservation (reserve £100, consume per-message, re-reserve when low)

class BatchReservationService
{
    // For API accounts with high throughput
    // Reserve a chunk (e.g., £50) and deduct from it locally
    // When chunk is 80% consumed, reserve another chunk
    // This reduces lock contention on account_balances from N to N/1000

    public function getOrCreateReservation(Account $account, Money $chunkSize): ActiveReservation
    {
        $reservation = Cache::get("api_reservation:{$account->id}");

        if (!$reservation || $reservation->remaining->lessThan($chunkSize->multipliedBy(0.2))) {
            // Reserve new chunk (this is the only point that locks account_balances)
            $reservation = $this->reserveChunk($account, $chunkSize);
            Cache::put("api_reservation:{$account->id}", $reservation, 300);
        }

        return $reservation;
    }
}
```

### 20.2 Database Connection Pooling

```
Application (Laravel) → PgBouncer (connection pooler) → PostgreSQL

PgBouncer config:
  pool_mode = transaction  (release connection after each transaction)
  max_client_conn = 1000
  default_pool_size = 50
```

### 20.3 Read Replicas

```
WRITES: account_balances, ledger_entries, ledger_lines → Primary
READS:  reporting, margin dashboards, invoice history → Read Replica

Laravel config:
  'pgsql' => [
      'read' => ['host' => 'replica.db'],
      'write' => ['host' => 'primary.db'],
  ]
```

### 20.4 Queue Architecture

```
HIGH PRIORITY QUEUE (Redis)
├── Balance deduction confirmations
├── Stripe webhook processing
├── Xero webhook processing
├── Auto top-up triggers

MEDIUM PRIORITY QUEUE (Redis)
├── Invoice Xero push
├── HubSpot pricing sync
├── Balance alert notifications
├── Dunning emails

LOW PRIORITY QUEUE (Redis)
├── Daily DLR reconciliation
├── Daily balance reconciliation
├── Margin report generation
├── Financial audit log archival
```

### 20.5 Caching Strategy

```
CACHED (Redis, 60s TTL, invalidated on write):
├── account_balances → Used for read-only balance display in portal
├── pricing lookups  → product_tier_prices (changes rarely)
├── country controls → Enabled/disabled countries

NOT CACHED (always hit PostgreSQL):
├── Balance deduction  → Must be real-time, uses SELECT FOR UPDATE
├── Ledger writes      → Immutable, always primary DB
├── Invoice generation → Must be transactional
```

---

## 21. Reseller / White-Label Readiness

### 21.1 Account Hierarchy

```sql
-- Direct customer
INSERT INTO accounts (id, parent_account_id, account_type) VALUES ('acme', NULL, 'direct');

-- Reseller
INSERT INTO accounts (id, parent_account_id, account_type) VALUES ('reseller-co', NULL, 'reseller');

-- Reseller's customer
INSERT INTO accounts (id, parent_account_id, account_type) VALUES ('reseller-cust-1', 'reseller-co', 'reseller_customer');
```

### 21.2 Two-Level Invoicing

```
QuickSMS invoices the Reseller (wholesale rate):
  Invoice QS-202602-X3K2M1
  └── UK SMS: 100,000 × £0.022 = £2,200.00  (wholesale)

Reseller invoices their Customer (retail rate):
  Managed within QuickSMS platform (future scope)
  └── UK SMS: 100,000 × £0.038 = £3,800.00  (retail)

Reseller margin: £1,600.00
```

### 21.3 Pricing: Reseller Gets Wholesale

```
Reseller account:
  customer_prices → wholesale rates (set by QuickSMS admin or HubSpot deal)

Reseller's customer account:
  customer_prices → retail rates (set by reseller using platform tools)

Message billing:
  1. Message sent by reseller_customer
  2. Charged at reseller_customer's retail rate (to reseller_customer's account)
  3. Reseller's account charged at wholesale rate
  4. QuickSMS margin = wholesale rate - supplier cost
  5. Reseller margin = retail rate - wholesale rate
```

### 21.4 Schema Readiness

The `parent_account_id` self-referencing FK and `account_type` enum already support this. No schema changes needed when reseller features are built — just new business logic and portal pages.

---

## 22. FX Rate Management

### 22.1 Daily FX Rate Table

| Field | Type | Description |
|-------|------|-------------|
| `id` | UUID | PK |
| `from_currency` | char(3) | e.g., `USD` |
| `to_currency` | char(3) | e.g., `GBP` |
| `rate` | decimal(12,6) | |
| `source` | string | `ecb`, `manual`, `stripe` |
| `effective_date` | date | Applies for this entire day |
| `created_at` | timestamp | |

### 22.2 FX Rate Application

- FX rates are fetched **daily** (from ECB or configurable source)
- Supplier rate cards store `native_rate` + `fx_rate` + `gbp_rate`
- When a message is sent, the **current day's FX rate** applies
- FX rate is recorded on the `supplier_cost_log` entry for audit
- If a campaign spans midnight, all messages use the rate effective at their individual send time

```php
class FxRateService
{
    // Scheduled daily at 00:30 UTC
    public function fetchDailyRates(): void
    {
        $rates = $this->ecbClient->getLatestRates();

        foreach ($rates as $currency => $rate) {
            FxRate::create([
                'from_currency' => $currency,
                'to_currency' => 'GBP',
                'rate' => $rate,
                'source' => 'ecb',
                'effective_date' => today(),
            ]);
        }
    }

    public function convert(Money $amount, string $toCurrency, ?Carbon $date = null): Money
    {
        $date = $date ?? today();
        $rate = FxRate::where('from_currency', $amount->getCurrency())
            ->where('to_currency', $toCurrency)
            ->where('effective_date', $date)
            ->firstOrFail();

        return $amount->multipliedBy($rate->rate);
    }
}
```

---

## Appendix A: Technology Stack

| Component | Technology |
|-----------|-----------|
| **Application** | Laravel 10+ (PHP 8.2+) |
| **Database** | PostgreSQL 15+ |
| **Connection Pool** | PgBouncer |
| **Cache / Queue** | Redis |
| **Queue Worker** | Laravel Horizon |
| **Payments** | Stripe API (Checkout, PaymentIntents, Bacs DD) |
| **Accounting** | Xero API v2 |
| **CRM** | HubSpot API v3 |
| **FX Rates** | ECB API (daily) |
| **Money Handling** | `brick/money` PHP library (avoids floating point) |

## Appendix B: Migration Execution Order

```
1.  create_ledger_accounts_table
2.  create_accounts_table (add billing fields to existing)
3.  create_sub_accounts_table
4.  create_account_balances_table
5.  create_ledger_entries_table
6.  create_ledger_lines_table
7.  create_test_credit_wallets_table
8.  create_test_credit_transactions_table
9.  create_test_number_allowlist_table
10. create_product_tier_prices_table
11. create_customer_prices_table
12. create_fx_rates_table
13. create_pricing_sync_log_table
14. create_invoices_table
15. create_invoice_line_items_table
16. create_credit_notes_table
17. create_payments_table
18. create_processed_stripe_events_table
19. create_auto_topup_configs_table
20. create_campaign_reservations_table
21. create_dlr_reconciliation_queue_table
22. create_recurring_charges_table
23. create_supplier_cost_log_table
24. create_balance_alert_configs_table
25. create_dunning_log_table
26. create_financial_audit_log_table
27. apply_ledger_immutability_rules
28. seed_ledger_accounts (chart of accounts)
29. seed_product_tier_prices (Starter + Enterprise defaults)
```

## Appendix C: Scheduled Jobs

| Schedule | Job | Description |
|----------|-----|-------------|
| `00:30 UTC daily` | `FetchDailyFxRates` | Pull ECB exchange rates |
| `02:00 UTC daily` | `ProcessDlrReconciliation` | RCS fallback + delivered billing adjustments |
| `03:00 UTC daily` | `ReconcileAccountBalances` | Verify cached vs ledger balances |
| `04:00 UTC 1st monthly` | `GeneratePostpayInvoices` | Monthly invoice generation for all postpay accounts |
| `04:00 UTC 1st monthly` | `ProcessRecurringCharges` | Monthly fees for prepay accounts |
| `06:00 UTC daily` | `ProcessDunningSequence` | Send overdue invoice reminders |
| `*/2 * * * *` | `PollHubSpotChanges` | Near-real-time HubSpot pricing sync |

---

*End of Architecture Specification*
