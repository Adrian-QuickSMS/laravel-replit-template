# Supplier Monitoring — Backend Integration TODO

This document describes the metrics, data sources, evaluation windows, and scope dimensions that must be implemented to connect the Supplier Monitoring alert rules to the reporting databases.

Each trigger key below corresponds to a pre-configured admin alert rule in `config/alerting.php` under `admin_defaults`. The UI and config are already wired — the developer needs to build the evaluation services that query the reporting data and fire these triggers.

---

## Tier 1 — Critical Supplier Health

### 1a. `supplier_delivery_rate`
- **Metric**: Delivery success percentage (DLR success / total submitted)
- **Source table/view**: `reporting.message_delivery_summary` or equivalent DLR aggregation view
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per network (MCC/MNC), per supplier route, per country
- **Rule**: Fire when `delivery_rate < threshold` (default 92%)

### 1b. `supplier_delivery_rate_deviation`
- **Metric**: Delivery rate deviation from 24-hour baseline
- **Source table/view**: `reporting.message_delivery_summary` compared to `reporting.delivery_baseline_24h`
- **Evaluation window**: 5-minute rolling vs last 24h baseline
- **Scope dimensions**: per network, per supplier
- **Rule**: Fire when delivery rate drops by more than `threshold`% (default 5%) compared to the 24h rolling baseline
- **Baseline logic**: Calculate a 24-hour rolling average delivery rate per scope dimension. Compare the current 5-minute window against this baseline. Deviation = `(baseline_rate - current_rate)`.

### 2a. `supplier_dlr_latency_median`
- **Metric**: Median delivery receipt latency (time between submission and DLR)
- **Source table/view**: `reporting.dlr_latency_stats` or computed from `reporting.message_events`
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per network, per supplier
- **Rule**: Fire when `median_dlr_latency > threshold` seconds (default 20s)

### 2b. `supplier_dlr_latency_p95`
- **Metric**: 95th percentile DLR latency compared to 24h baseline
- **Source table/view**: `reporting.dlr_latency_stats`
- **Evaluation window**: 5-minute rolling vs 24h baseline
- **Scope dimensions**: per supplier
- **Rule**: Fire when `p95_latency > threshold × baseline_p95` (default multiplier 2×)
- **Baseline logic**: Maintain a rolling 24h p95 latency per supplier. Current p95 divided by baseline p95 gives the multiplier.

### 3a. `supplier_pending_messages`
- **Metric**: Count of messages in pending/queued state
- **Source table/view**: `reporting.supplier_queue_stats` or live queue depth query
- **Evaluation window**: Instant / 1-minute rolling
- **Scope dimensions**: per supplier route
- **Rule**: Fire when `pending_count > threshold` (default 5,000)

### 3b. `supplier_pending_growth_rate`
- **Metric**: Rate of pending message count increase
- **Source table/view**: `reporting.supplier_queue_stats` (compare consecutive snapshots)
- **Evaluation window**: 3-minute rolling
- **Scope dimensions**: per supplier route
- **Rule**: Fire when `pending_growth_rate > threshold`% per minute (default 20%)
- **Calculation**: `((current_pending - pending_3min_ago) / pending_3min_ago) × 100 / 3`

### 4. `supplier_submit_success_rate`
- **Metric**: Percentage of messages accepted by the supplier API (HTTP 2xx responses)
- **Source table/view**: `reporting.supplier_submit_log` or gateway response log
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per supplier
- **Rule**: Fire when `submit_success_rate < threshold`% (default 99.5%)

### 5a. `supplier_api_availability`
- **Metric**: Supplier API uptime percentage (successful health checks / total checks)
- **Source table/view**: `reporting.supplier_health_checks` or synthetic monitoring
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per supplier
- **Rule**: Fire when `api_availability < threshold`% (default 99.9%)

### 5b. `supplier_api_latency`
- **Metric**: Average API response time for message submission
- **Source table/view**: `reporting.supplier_api_response_times`
- **Evaluation window**: 1-minute rolling
- **Scope dimensions**: per supplier
- **Rule**: Fire when `api_response_time > threshold` ms (default 800ms)

### 5c. `supplier_api_timeout_rate`
- **Metric**: Percentage of API calls that timed out
- **Source table/view**: `reporting.supplier_api_response_times` (filter timeout flag)
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per supplier
- **Rule**: Fire when `timeout_rate > threshold`% (default 1%)

---

## Tier 2 — Carrier Behaviour

### 6. `supplier_network_delivery_delta`
- **Metric**: Delivery rate delta between a specific UK network and the average of all other UK networks
- **Source table/view**: `reporting.network_delivery_rates` (per MCC/MNC)
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per network
- **Rule**: Fire when one network's delivery rate is more than `threshold`% (default 6%) below the average of all other networks
- **Example**: Vodafone at 90%, others at 97% → delta = 7% → triggers alert

### 7. `supplier_senderid_rejection_rate`
- **Metric**: Percentage of messages rejected due to sender ID filtering
- **Source table/view**: `reporting.sender_rejection_log` or DLR error codes
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per supplier
- **Rule**: Fire when `senderid_rejection_rate > threshold`% (default 2%)

### 8. `supplier_country_delivery_delta`
- **Metric**: Country-level delivery rate deviation from 24h baseline
- **Source table/view**: `reporting.country_delivery_rates`
- **Evaluation window**: 5-minute rolling vs 24h baseline
- **Scope dimensions**: per country
- **Rule**: Fire when country delivery rate drops by more than `threshold`% (default 7%) from 24h baseline
- **Baseline logic**: Same approach as 1b but scoped per destination country.

### 9. `supplier_missing_dlr_rate`
- **Metric**: Percentage of submitted messages that never received a DLR
- **Source table/view**: `reporting.missing_dlr_tracker` (messages older than expected DLR window with no receipt)
- **Evaluation window**: 10-minute rolling
- **Scope dimensions**: per supplier
- **Rule**: Fire when `missing_dlr_rate > threshold`% (default 3%)

---

## Implementation Notes

### Evaluation Service Architecture
- Create `App\Services\Alerting\SupplierMonitoringEvaluator` (or similar) that runs on a scheduled basis (e.g. every 1 minute via Laravel Scheduler)
- For each trigger key, query the relevant reporting view/table, compute the metric, and compare against the configured threshold from the alert rule
- Use `App\Services\Alerting\AlertEvaluationService` (if it exists) or create a new one that dispatches `AlertTriggeredEvent` when a threshold is breached

### Reporting Database Connection
- The reporting data likely lives in a separate read-replica or analytics database
- Configure a `reporting` database connection in `config/database.php`
- All queries in the evaluator should use `DB::connection('reporting')`

### Baseline Deviation Calculations
- Triggers 1b, 2b, 8 require 24-hour rolling baselines
- Options: (a) Maintain a `supplier_baselines` table updated hourly, (b) compute on-the-fly with a 24h window query
- Recommended: Pre-compute baselines via a scheduled job every 15 minutes and store in a `reporting.supplier_baselines` table

### Scope Dimensions
- Many alerts support multiple scope dimensions (per supplier, per network, per country)
- The evaluator should iterate over each unique combination and evaluate independently
- Store the scope context in the alert history `metadata` field so the admin can see which specific supplier/network/country triggered the alert

### Cooldown Enforcement
- The alerting system already supports cooldown via `cooldown_minutes` on each rule
- The evaluator must check the last alert fire time before dispatching a new one
