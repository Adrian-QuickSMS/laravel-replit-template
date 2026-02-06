# Supplier Rate Card Management System

## Overview

Complete implementation of the Supplier Rate Card Management module for QuickSMS Admin Console.

This module provides centralized supplier cost management, multi-currency support, FX conversion, historical rate tracking, and full audit logging.

---

## What's Included

### Database Migrations (6 files)
- `2026_02_07_000001_create_suppliers_table.php`
- `2026_02_07_000002_create_gateways_table.php`
- `2026_02_07_000003_create_mcc_mnc_master_table.php`
- `2026_02_07_000004_create_rate_cards_table.php`
- `2026_02_07_000005_create_fx_rates_table.php`
- `2026_02_07_000006_create_rate_card_audit_log_table.php`

### Models (6 files)
- `app/Models/Supplier.php`
- `app/Models/Gateway.php`
- `app/Models/MccMnc.php`
- `app/Models/RateCard.php`
- `app/Models/FxRate.php`
- `app/Models/RateCardAuditLog.php`

### Controllers (4 files)
- `app/Http/Controllers/Admin/SupplierController.php`
- `app/Http/Controllers/Admin/GatewayController.php`
- `app/Http/Controllers/Admin/RateCardController.php`
- `app/Http/Controllers/Admin/MccMncController.php`

### Routes
- `routes/supplier-management.php` - All supplier management routes

---

## Installation Instructions

### Step 1: Upload Files to Replit

Upload all the files to your Replit project maintaining the directory structure:

```
database/migrations/
app/Models/
app/Http/Controllers/Admin/
routes/
```

### Step 2: Register Routes

Add this line to `routes/web.php` (at the end of the file):

```php
require __DIR__ . '/supplier-management.php';
```

### Step 3: Run Migrations

In Replit Shell, run:

```bash
php artisan migrate
```

This will create all 6 database tables.

### Step 4: Seed MCC/MNC Master Data (Optional)

You can import MCC/MNC data via CSV upload through the admin interface, or manually insert into the `mcc_mnc_master` table.

Example SQL:

```sql
INSERT INTO mcc_mnc_master (mcc, mnc, country_name, country_iso, network_name, network_type, active, created_at, updated_at) VALUES
('234', '10', 'United Kingdom', 'GB', 'O2', 'mobile', 1, NOW(), NOW()),
('234', '15', 'United Kingdom', 'GB', 'Vodafone', 'mobile', 1, NOW(), NOW()),
('234', '20', 'United Kingdom', 'GB', 'Three', 'mobile', 1, NOW(), NOW()),
('234', '30', 'United Kingdom', 'GB', 'EE', 'mobile', 1, NOW(), NOW());
```

### Step 5: Seed FX Rates (Optional)

Insert initial FX rates:

```sql
INSERT INTO fx_rates (from_currency, to_currency, rate, source, rate_date, created_at, updated_at) VALUES
('EUR', 'GBP', 0.85, 'ECB', CURDATE(), NOW(), NOW()),
('USD', 'GBP', 0.78, 'ECB', CURDATE(), NOW(), NOW());
```

---

## Module Access

Navigate to: **Admin Console → Supplier Management**

### Pages:

1. **Suppliers** (`/admin/supplier-management/suppliers`)
   - View all suppliers
   - Create/edit suppliers
   - Suspend suppliers
   - View gateway count

2. **Gateways** (`/admin/supplier-management/gateways`)
   - View all gateways by supplier
   - Create/edit gateways
   - Toggle active/inactive
   - Configure billing method

3. **Rate Cards** (`/admin/supplier-management/rate-cards`)
   - View active rate cards
   - Filter by supplier/gateway/country
   - Upload rate cards (CSV/XLSX)
   - Edit individual rates
   - View rate history

4. **MCC/MNC Reference** (`/admin/supplier-management/mcc-mnc`)
   - Master network reference database
   - Add/edit networks
   - Import bulk networks via CSV
   - Toggle active/inactive

---

## Usage Workflows

### Creating a New Supplier

1. Go to **Suppliers** page
2. Click **Add Supplier**
3. Fill in:
   - Supplier Name
   - Default Currency (GBP/EUR/USD)
   - Default Billing Method (Submitted/Delivered)
   - Contact Details
4. Submit → System generates unique `supplier_code`

### Creating a Gateway

1. Go to **Gateways** page
2. Click **Add Gateway**
3. Select Supplier
4. Fill in gateway details:
   - Gateway Name
   - Currency
   - Billing Method
   - FX Source
5. Submit → System generates unique `gateway_code`

### Uploading Rate Cards

1. Go to **Rate Cards** → **Upload Rates**
2. Select Gateway
3. Upload CSV/XLSX file with columns:
   - `mcc`
   - `mnc`
   - `rate`
   - `currency` (optional, uses gateway default)
   - `product_type` (SMS/RCS_BASIC/RCS_SINGLE)
4. System validates:
   - MCC/MNC exists in master reference
   - No negative rates
   - No duplicates
5. Preview changes
6. Confirm import
7. System:
   - Deactivates old rates (`valid_to` = yesterday)
   - Creates new versioned rates
   - Applies FX conversion to GBP
   - Logs all changes in audit log

### Editing a Rate

1. Find rate in Rate Cards table
2. Click **Edit**
3. Enter new rate and reason
4. Submit → System:
   - Creates new version (increments `version` number)
   - Deactivates old version
   - Links `previous_version_id`
   - Logs change with reason

### Viewing Rate History

1. Click **History** on any rate
2. See all previous versions:
   - What changed
   - When it changed
   - Who changed it
   - Why it changed

---

## Data Models

### Suppliers
- Stores supplier information
- Has many gateways
- Has many rate cards
- Status: active/suspended

### Gateways
- Belongs to one supplier
- Has many rate cards
- Each gateway has its own currency and billing method
- Can be active/inactive

### MCC/MNC Master
- Global network reference
- Single source of truth
- MCC+MNC must be unique
- Prevents invalid rate imports

### Rate Cards
- Versioned pricing
- Links to supplier + gateway + network
- Stores both native currency and GBP converted
- Valid from/to dates for scheduling
- Never overwritten - only versioned

### FX Rates
- Historical FX rates
- Stored permanently
- Used for conversions
- Source: ECB or configurable

### Audit Log
- Every action logged
- Stores old/new values
- Admin user + IP tracked
- Reason field for changes

---

## Billing Methods

### Submitted Billing
**Billable:**
- Delivered
- Failed
- Expired
- Rejected
- Any message submitted to supplier

**Not Billable:**
- Messages not submitted

### Delivered Billing
**Billable:**
- Delivered only

**Not Billable:**
- Failed, Expired, Rejected, Undelivered

---

## Rate Versioning

**How it works:**

1. Current active rate: `version: 1, active: true, valid_to: null`
2. Admin updates rate
3. System:
   - Sets old rate: `active: false, valid_to: yesterday`
   - Creates new rate: `version: 2, active: true, previous_version_id: <old_rate_id>`
4. Message rating always uses rate active at `sent_at` date

**Benefits:**
- Historical accuracy guaranteed
- Full audit trail
- Can trace every price change
- Support invoice reconciliation

---

## Message Rating Integration

### How Messages Get Rated

When a message is sent, the rating engine:

1. Looks up MCC/MNC from destination number
2. Finds active rate for that network + gateway
3. Checks `valid_from` and `valid_to` dates
4. Returns GBP cost for message
5. Stores:
   - Supplier cost
   - Billing method used
   - Billable flag
   - FX rate applied

### Rate Lookup Query

```php
$rate = RateCard::findRateForMessage(
    $mcc,           // e.g., '234'
    $mnc,           // e.g., '10'
    $gatewayId,     // e.g., 1
    $productType,   // 'SMS' or 'RCS_BASIC' or 'RCS_SINGLE'
    $sentAt         // Carbon timestamp
);

$cost = $rate->gbp_rate;
```

---

## CSV Upload Format

### Rate Card Upload

```csv
mcc,mnc,rate,currency,product_type
234,10,0.0350,GBP,SMS
234,15,0.0340,GBP,SMS
234,20,0.0360,EUR,SMS
234,30,0.0355,USD,RCS_BASIC
```

### MCC/MNC Import

```csv
mcc,mnc,country_name,country_iso,network_name,network_type
234,10,United Kingdom,GB,O2,mobile
234,15,United Kingdom,GB,Vodafone,mobile
234,20,United Kingdom,GB,Three,mobile
234,30,United Kingdom,GB,EE,mobile
```

---

## API Endpoints

All routes require admin authentication.

### Suppliers
- `GET /admin/supplier-management/suppliers` - List suppliers
- `POST /admin/supplier-management/suppliers` - Create supplier
- `PUT /admin/supplier-management/suppliers/{id}` - Update supplier
- `POST /admin/supplier-management/suppliers/{id}/suspend` - Toggle status
- `DELETE /admin/supplier-management/suppliers/{id}` - Delete supplier

### Gateways
- `GET /admin/supplier-management/gateways` - List gateways
- `POST /admin/supplier-management/gateways` - Create gateway
- `PUT /admin/supplier-management/gateways/{id}` - Update gateway
- `POST /admin/supplier-management/gateways/{id}/toggle-status` - Toggle status
- `DELETE /admin/supplier-management/gateways/{id}` - Delete gateway

### Rate Cards
- `GET /admin/supplier-management/rate-cards` - List rate cards
- `GET /admin/supplier-management/rate-cards/upload` - Upload form
- `POST /admin/supplier-management/rate-cards/validate-upload` - Validate CSV
- `POST /admin/supplier-management/rate-cards/process-upload` - Import rates
- `PUT /admin/supplier-management/rate-cards/{id}` - Update rate
- `GET /admin/supplier-management/rate-cards/{id}/history` - View history

### MCC/MNC
- `GET /admin/supplier-management/mcc-mnc` - List networks
- `POST /admin/supplier-management/mcc-mnc` - Add network
- `PUT /admin/supplier-management/mcc-mnc/{id}` - Update network
- `POST /admin/supplier-management/mcc-mnc/{id}/toggle-status` - Toggle status
- `DELETE /admin/supplier-management/mcc-mnc/{id}` - Delete network
- `POST /admin/supplier-management/mcc-mnc/import` - Bulk import

---

## Security

- **Admin Only**: All routes protected by AdminAuthenticate middleware
- **IP Allowlist**: AdminIpAllowlist middleware enforced
- **Audit Logging**: Every action logged with user + IP
- **Soft Deletes**: Suppliers, gateways, rate cards use soft deletes
- **Version Control**: Rates never deleted, only archived

---

## Performance

### Optimizations

1. **Indexed Lookups**:
   - `[mcc, mnc, gateway_id, product_type, active]` - Message rating
   - `[gateway_id, active, valid_from, valid_to]` - Date filtering
   - `supplier_id`, `valid_from`, `valid_to` - Fast filtering

2. **Query Optimization**:
   - Rate lookup uses single indexed query
   - No joins needed for message rating
   - Denormalized network data for speed

3. **Caching Strategy** (Future):
   - Cache active rates per gateway
   - Invalidate on rate upload
   - TTL: 1 hour

---

## Next Steps

### Required Views

You'll need to create Blade templates for:

1. `resources/views/admin/supplier-management/suppliers.blade.php`
2. `resources/views/admin/supplier-management/gateways.blade.php`
3. `resources/views/admin/supplier-management/rate-cards.blade.php`
4. `resources/views/admin/supplier-management/upload-rates.blade.php`
5. `resources/views/admin/supplier-management/mcc-mnc.blade.php`

These should follow the existing Fillow admin template patterns with:
- Admin blue color scheme (#1e3a5f)
- Data tables with sorting/filtering
- Modal forms for create/edit
- Audit trail display
- Pagination

### Navigation Menu

Add to `resources/views/elements/admin-sidebar.blade.php`:

```html
<li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
    <i class="fas fa-dollar-sign"></i>
    <span class="nav-text">Supplier Management</span>
</a>
    <ul aria-expanded="false">
        <li><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
        <li><a href="{{ route('admin.gateways.index') }}">Gateways</a></li>
        <li><a href="{{ route('admin.rate-cards.index') }}">Rate Cards</a></li>
        <li><a href="{{ route('admin.mcc-mnc.index') }}">MCC/MNC Reference</a></li>
    </ul>
</li>
```

---

## Support & Documentation

This module integrates with:
- Message rating engine
- Margin intelligence
- Finance reporting
- Supplier invoice validation

All supplier costs flow through this system - it's the single source of truth for supplier pricing.

---

## License

Internal QuickSMS Admin Module - Not for redistribution
