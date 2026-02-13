# QuickSMS Routing Rules Module

## Overview

The Routing Rules module provides **manual, deterministic routing control** for all outbound messaging (SMS, RCS Basic, RCS Single). This is the single control plane for routing decisions across the QuickSMS platform.

**Key Principles:**
- Manual routing only (no automation, no AI, no cost optimization)
- All routing decisions are human-controlled and auditable
- References but never duplicates supplier/gateway/rate card data
- Historical integrity - routing changes never rewrite history

## Architecture

### Database Tables

1. **`routing_rules`** - One rule per product + destination combination
   - Defines routing for UK networks or international countries
   - Stores primary gateway reference
   - Active/blocked status

2. **`routing_gateway_weights`** - Load balancing configuration
   - Multiple gateways per routing rule
   - Weight percentages (must total 100%)
   - Allowed/blocked status per gateway

3. **`routing_customer_overrides`** - Customer-specific routing
   - Forces specific gateway for a customer account
   - Time-bound with start/end dates
   - Scope: global, product-specific, or destination-specific

4. **`routing_audit_log`** - Complete audit trail
   - Every routing change logged
   - Stores admin user, IP, before/after values
   - Immutable history

### Data Model

```
RoutingRule (1) ──→ (N) RoutingGatewayWeight ──→ (1) Gateway ──→ (1) Supplier
     │
     └──→ (1) Primary Gateway

RoutingCustomerOverride ──→ (1) Forced Gateway
                       └──→ (1) Secondary Gateway (optional)
```

## User Interface

### UK Routes Tab (`/admin/routing-rules/uk-routes`)

- View all UK mobile network routes (Vodafone, O2, EE, Three, MVNOs)
- Product selector: SMS / RCS Basic / RCS Single
- Expandable row panels showing gateway details:
  - Gateway name, supplier
  - Weight percentage
  - Route status (allowed/blocked)
  - Gateway online/offline status
  - Telemetry: delivery rate, response time, median DLR time
  - Rate snapshot (display only from rate cards)

**Actions:**
- Add gateway to route
- Set primary gateway
- Change weight
- Block/unblock gateway
- Remove gateway
- Block/unblock destination

### International Routes Tab (`/admin/routing-rules/international-routes`)

- Same structure as UK Routes but organized by country
- Alphabetically sorted A–Z
- MCC/MNC network rates viewable per country

### Customer Overrides Tab (`/admin/routing-rules/customer-overrides`)

- Create customer-specific routing overrides
- Override types:
  - **Global** - applies to all destinations
  - **Product-specific** - SMS, RCS Basic, or RCS Single
  - **UK Network** - specific UK network
  - **Country** - specific international country

**Override Fields:**
- Customer account search
- Product scope
- Destination scope
- Forced gateway (required)
- Secondary gateway (optional, for failover)
- Start datetime
- End datetime (optional for indefinite)
- Reason (required)
- Notify customer toggle

**Precedence:** Customer overrides ALWAYS take priority over standard routing rules.

## Routing Model

### Step 1: Product Selection
Routing begins by product type:
- SMS
- RCS Basic
- RCS Single

### Step 2: Destination Type

**UK Numbers:**
Route by mobile network prefix (Vodafone, O2, EE, Three, MVNO)

**International:**
Route by destination country ISO code

### Step 3: Route Selection

Each destination has:
- **Primary gateway** (required if route active)
- **Secondary gateways** (optional)
- **Load balancing weights** (must total 100%)

### Gateway Eligibility Rules

Gateway is eligible only if:
- Route status = `allowed`
- Gateway status = `online` (active)
- Gateway supports the product type
- Not blocked by admin

If no eligible gateway exists, message submission fails with routing error.

## Load Balancing

**Single Gateway:**
- Weight must be 100%

**Multiple Gateways:**
- Weights split traffic (e.g., 70% Gateway A, 30% Gateway B)
- Weights must always total 100%

**Primary Gateway Offline:**
- Runtime routing selects next highest weight eligible gateway
- Weights are NOT automatically rewritten
- No auto-optimization logic

## Customer Overrides

Overrides force routing for specific customers.

**Precedence (most specific wins):**
1. Destination + Product specific
2. Product specific + Global
3. Global override (all products, all destinations)

**Active Period:**
- Start datetime marks when override begins
- End datetime marks when it expires (optional for indefinite)
- Status automatically changes to `expired` when end datetime passes

**Failover:**
- If forced gateway is offline, route fails
- Unless secondary gateway is defined (optional future feature)

## Audit Logging

Every routing change is logged immutably:

**Events logged:**
- `route_created`
- `route_edited`
- `primary_changed`
- `weight_changed`
- `gateway_blocked`
- `gateway_unblocked`
- `gateway_added`
- `gateway_removed`
- `destination_blocked`
- `destination_unblocked`
- `override_created`
- `override_edited`
- `override_cancelled`
- `override_expired`

**Log fields:**
- Admin user name
- Admin IP address
- Timestamp
- Product type
- Destination
- Old value (JSON)
- New value (JSON)
- Reason (if provided)

## API Endpoints

### Routing Rules

- `GET /admin/routing-rules/uk-routes` - UK routes view
- `GET /admin/routing-rules/international-routes` - International routes view
- `POST /admin/routing-rules/{id}/add-gateway` - Add gateway to route
- `POST /admin/routing-rules/{id}/set-primary` - Set primary gateway
- `POST /admin/routing-rules/weight/{id}/change` - Change weight
- `POST /admin/routing-rules/weight/{id}/toggle` - Block/unblock gateway
- `DELETE /admin/routing-rules/weight/{id}/remove` - Remove gateway
- `POST /admin/routing-rules/{id}/toggle-destination` - Block/unblock destination

### Customer Overrides

- `GET /admin/routing-rules/overrides` - List overrides
- `POST /admin/routing-rules/overrides` - Create override
- `PUT /admin/routing-rules/overrides/{id}` - Update override
- `POST /admin/routing-rules/overrides/{id}/cancel` - Cancel override

## Integration Points

### Dependencies (Read-Only)

Routing module references but NEVER duplicates:

1. **Supplier Management Module**
   - Supplier list
   - Gateway list
   - Gateway status (online/offline)
   - Supported products per gateway

2. **Rate Card Module**
   - Supplier rates (for display only)
   - Billing method (submitted/delivered)
   - Currency and GBP conversion

3. **MCC/MNC Master Database**
   - Country codes
   - UK network prefixes
   - Network names
   - ISO country mapping

4. **Health Telemetry Pipeline** (future)
   - Delivery rate per gateway
   - Response time
   - Median DLR time
   - Error rate

### Message Routing Engine Integration

When a message is submitted for routing:

```php
use App\Models\RoutingCustomerOverride;
use App\Models\RoutingRule;

// Step 1: Check for customer override
$override = RoutingCustomerOverride::findOverride(
    $customerId,
    $productType,      // 'SMS', 'RCS_BASIC', 'RCS_SINGLE'
    $destinationType,  // 'UK_NETWORK' or 'INTERNATIONAL'
    $destinationCode   // Network prefix or country ISO
);

if ($override) {
    $gatewayId = $override->forced_gateway_id;
} else {
    // Step 2: Use standard routing rule
    $rule = RoutingRule::where('product_type', $productType)
        ->where('destination_type', $destinationType)
        ->where('destination_code', $destinationCode)
        ->where('status', 'active')
        ->first();

    if (!$rule) {
        throw new RoutingException('No routing rule found');
    }

    // Step 3: Get eligible gateways
    $eligibleGateways = $rule->getEligibleGateways();

    if ($eligibleGateways->isEmpty()) {
        throw new RoutingException('No eligible gateways');
    }

    // Step 4: Select gateway by weight (weighted random selection)
    $gatewayId = selectByWeight($eligibleGateways);
}

// Step 5: Route message to selected gateway
$message->gateway_id = $gatewayId;
$message->save();
```

## Installation

### 1. Run Migrations

```bash
php artisan migrate
```

Migrations will create:
- `routing_rules`
- `routing_gateway_weights`
- `routing_customer_overrides`
- `routing_audit_log`

### 2. Register Routes

Routes are automatically registered via `/package/routes/routing-rules.php` which is included in `web.php`.

### 3. Seed Initial Data

Create routing rules for each product + destination combination:

```php
// UK Networks for SMS
RoutingRule::create([
    'product_type' => 'SMS',
    'destination_type' => 'UK_NETWORK',
    'destination_code' => 'VODAFONE',
    'destination_name' => 'Vodafone',
    'status' => 'active',
]);

// Add gateway weights
RoutingGatewayWeight::create([
    'routing_rule_id' => 1,
    'gateway_id' => 1,  // Supplier Gateway ID
    'weight' => 100,
    'route_status' => 'allowed',
    'is_primary' => true,
]);
```

### 4. Access Module

Navigate to:
- Admin Console → Routing Rules → UK Routes
- Admin Console → Routing Rules → International Routes
- Admin Console → Routing Rules → Customer Overrides

## Validation Rules

### Weight Validation
- All active gateway weights for a route must total 100%
- Single gateway must have weight = 100

### Primary Gateway
- Every active route MUST have a primary gateway
- Primary gateway must be in the route's gateway list

### Customer Overrides
- Start datetime cannot be in the past
- End datetime must be after start datetime
- Forced gateway must exist and be active
- Scope value required for non-global scopes

## Security & Permissions

- Module is **admin-only** (never accessible to customers)
- Requires `AdminIpAllowlist` and `AdminAuthenticate` middleware
- All actions logged with admin user and IP
- Route to view changes requires full audit trail

## Non-Functional Requirements

### Performance
- Fast load times with large datasets (1000+ routes)
- Lazy-load expanded panels
- Indexed database queries for message routing

### Safety
- Confirmation required for:
  - Blocking destination
  - Removing last gateway
  - Disabling primary gateway
- Cannot remove last eligible gateway from active route

### Auditability
- Complete history of all routing changes
- Searchable audit log by admin, date, action
- Old/new values stored as JSON for comparison

## Future Enhancements

**Explicitly NOT included in V1:**
- ❌ Cheapest route logic
- ❌ Margin optimization
- ❌ Delivery optimization
- ❌ Automatic failover logic
- ❌ AI routing
- ❌ Dynamic rebalancing
- ❌ Performance-based auto-switching

**Potential V2 features:**
- Read-only support role permissions
- Scheduled routing changes
- Bulk route updates
- Route templates/copying
- Advanced telemetry graphs
- Route simulation/testing

## Support

For issues or questions about the Routing Rules module, contact the QuickSMS platform team.

---

**Module Version:** 1.0
**Last Updated:** February 2026
**Documentation:** This file
