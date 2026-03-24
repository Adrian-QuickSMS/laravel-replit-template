# Customer Monitoring — Backend Integration TODO

This document describes the metrics, data sources, evaluation windows, and scope dimensions that must be implemented to connect the customer-facing alert rules to the reporting databases.

Each trigger key below corresponds to a pre-configured customer default alert rule in `config/alerting.php` under `defaults`. The UI and config are already wired — the developer needs to build the evaluation services that query the reporting data and fire these triggers.

---

## Tier 1 — Delivery Health (Critical Core Rules)

### 1. `delivery_rate` (updated)
- **Metric**: Delivery success percentage (DLR success / total submitted)
- **Source table/view**: `reporting.message_delivery_summary` or equivalent DLR aggregation view
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer, per customer + network, per customer + country
- **Rule**: Fire when `delivery_rate < threshold` (default 90%)
- **Note**: Threshold updated from 85% to 90%

### 2. `delivery_rate_delta`
- **Metric**: Delivery rate deviation from 24-hour baseline
- **Source table/view**: `reporting.message_delivery_summary` compared to `reporting.delivery_baseline_24h`
- **Evaluation window**: 5-minute rolling vs last 24h baseline
- **Scope dimensions**: per customer
- **Rule**: Fire when delivery rate drops by more than `threshold`% (default 7%) compared to the 24h rolling baseline
- **Baseline logic**: Calculate a 24-hour rolling average delivery rate per customer. Compare the current 5-minute window against this baseline. Deviation = `(baseline_rate - current_rate)`.

### 3. `network_delivery_delta`
- **Metric**: Network-level delivery deviation
- **Source table/view**: `reporting.message_delivery_summary` with network (MCC/MNC) breakdown
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer + network
- **Rule**: Fire when delivery rate drops by more than `threshold`% (default 8%) versus baseline for a specific network
- **Baseline logic**: Rolling 24h baseline per customer + network combination. Detect single-network filtering or routing issues.

### 4. `country_delivery_delta`
- **Metric**: Country-level delivery deviation
- **Source table/view**: `reporting.message_delivery_summary` with country breakdown
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer + country
- **Rule**: Fire when delivery rate drops by more than `threshold`% (default 10%) versus baseline for a specific country
- **Baseline logic**: Rolling 24h baseline per customer + country combination. Detect international routing problems.

---

## Tier 2 — Platform Processing Performance

### 5. `platform_processing_time`
- **Metric**: Time from message submission to exit from QuickSMS platform (submit → handoff to supplier)
- **Source table/view**: `reporting.message_processing_times` or computed from `reporting.message_events` (submit timestamp vs gateway handoff timestamp)
- **Evaluation window**: 3-minute rolling (median or p95)
- **Scope dimensions**: per customer
- **Rule**: Fire when `median_processing_time > threshold` seconds (default 3s, warning)
- **Note**: Detects queue congestion or routing slowdown.

### 6. `platform_processing_time_critical`
- **Metric**: Same as above, critical threshold
- **Source table/view**: Same as `platform_processing_time`
- **Evaluation window**: 3-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `median_processing_time > threshold` seconds (default 8s, critical)
- **Note**: Detects serious platform bottlenecks affecting delivery speed.

---

## Tier 3 — Queue Behaviour Monitoring

### 7. `queued_messages_outbound`
- **Metric**: Count of messages in the customer's outbound queue
- **Source table/view**: `reporting.customer_queue_stats` or live queue depth query filtered by customer
- **Evaluation window**: Instant / snapshot
- **Scope dimensions**: per customer
- **Rule**: Fire when `outbound_queue_count > threshold` (default 2,000)
- **Note**: Detects customer-specific backlog buildup.

### 8. `queue_growth_rate`
- **Metric**: Rate of outbound queue growth
- **Source table/view**: `reporting.customer_queue_stats` (compare consecutive 1-minute snapshots)
- **Evaluation window**: 3-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `growth_rate > threshold`% per minute (default 15%)
- **Calculation**: `((current_count - count_3m_ago) / count_3m_ago) * 100 / 3`
- **Note**: Detects runaway backlog before delivery failure occurs.

### 9. `oldest_queued_message_age`
- **Metric**: Age of the oldest message still in the outbound queue
- **Source table/view**: `reporting.customer_queue_stats` or live query on outbound queue ordered by created_at
- **Evaluation window**: Instant / snapshot
- **Scope dimensions**: per customer
- **Rule**: Fire when `oldest_message_age > threshold` seconds (default 30s)
- **Note**: Better indicator of queue health than queue size alone.

---

## Tier 4 — Pending Status Monitoring

### 10. `pending_rate`
- **Metric**: Percentage of messages still in "pending" status after expected delivery window
- **Source table/view**: `reporting.message_status_summary` (pending count / total submitted in window)
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `pending_rate > threshold`% (default 5%, warning)
- **Note**: Detects supplier or routing delay.

### 11. `pending_rate_critical`
- **Metric**: Same as above, critical threshold
- **Source table/view**: Same as `pending_rate`
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `pending_rate > threshold`% (default 10%, critical)
- **Note**: Detects serious routing failure risk.

---

## Tier 5 — DLR Processing Monitoring

### 12. `queued_dlr_count`
- **Metric**: Count of DLR callbacks waiting to be processed
- **Source table/view**: `reporting.dlr_queue_stats` or live DLR processing queue filtered by customer
- **Evaluation window**: 3-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `queued_dlr_count > threshold` (default 1,000)
- **Note**: Detects callback or supplier reporting delays.

### 13. `missing_dlr_rate`
- **Metric**: Percentage of submitted messages that have not received a DLR within expected window
- **Source table/view**: `reporting.message_delivery_summary` (submitted without DLR after 10 minutes)
- **Evaluation window**: 10-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `missing_dlr_rate > threshold`% (default 3%)
- **Note**: Detects callback pipeline problems. Distinct from supplier-level `supplier_missing_dlr_rate` — this is scoped to the customer's own traffic.

---

## Tier 6 — Submission Behaviour Monitoring

### 14. `submission_rejection_rate`
- **Metric**: Percentage of API submissions rejected (validation failures, blocked countries, etc.)
- **Source table/view**: `reporting.submission_stats` or API request logs (rejected / total submitted)
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `rejection_rate > threshold`% (default 1%)
- **Note**: Detects invalid sender / blocked country / filtering issues.

### 15. `senderid_rejection_rate`
- **Metric**: Percentage of submissions rejected due to sender ID issues
- **Source table/view**: `reporting.submission_stats` with sender ID breakdown
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer + sender ID
- **Rule**: Fire when `senderid_rejection_rate > threshold`% (default 2%)
- **Note**: Detects sender registration or filtering problems. Distinct from supplier-level `supplier_senderid_rejection_rate`.

---

## Tier 7 — Customer Integration Monitoring

### 16. `customer_api_error_rate`
- **Metric**: Percentage of customer API requests returning errors (4xx/5xx responses)
- **Source table/view**: `reporting.api_request_logs` or API gateway metrics filtered by customer
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `error_rate > threshold`% (default 2%)
- **Note**: Tighter threshold than the existing generic `api_error_rate` (5%). Detects integration failures.

### 17. `customer_api_latency`
- **Metric**: Average API response latency for the customer's requests
- **Source table/view**: `reporting.api_request_logs` or API gateway metrics
- **Evaluation window**: 3-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `avg_latency > threshold` ms (default 1,200ms)
- **Note**: Detects degraded integration responsiveness.

---

## Tier 8 — Traffic Behaviour Monitoring

### 18. `traffic_volume_spike`
- **Metric**: Submission volume compared to rolling baseline
- **Source table/view**: `reporting.submission_volume` compared to `reporting.submission_baseline_24h`
- **Evaluation window**: 3-minute rolling vs 24h baseline
- **Scope dimensions**: per customer
- **Rule**: Fire when `current_volume > baseline × (threshold / 100)` (default 200% = 2× baseline)
- **Baseline logic**: Rolling 24h hourly average submission rate. Compare current 3-minute rate against same time-of-day baseline.
- **Note**: Detects runaway campaigns or compromised API keys.

### 19. `traffic_volume_drop`
- **Metric**: Submission volume drop compared to rolling baseline
- **Source table/view**: Same as `traffic_volume_spike`
- **Evaluation window**: 5-minute rolling vs 24h baseline
- **Scope dimensions**: per customer
- **Rule**: Fire when `current_volume < baseline × (threshold / 100)` (default drops below 50% of baseline)
- **Baseline logic**: Same 24h rolling baseline as spike detection.
- **Note**: Detects customer outage or integration break.

---

## Tier 9 — Webhook / Callback Monitoring

### 20. `webhook_failure_rate`
- **Metric**: Percentage of webhook/callback delivery attempts that fail
- **Source table/view**: `reporting.webhook_delivery_log` (failed / total attempts)
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `failure_rate > threshold`% (default 5%)
- **Note**: Threshold-based complement to the existing event-based `webhook_delivery_failed` rule. Detects downstream delivery reporting failures.

### 21. `dlr_callback_latency`
- **Metric**: Average time to deliver DLR callbacks to the customer's endpoint
- **Source table/view**: `reporting.webhook_delivery_log` (time between DLR receipt and successful callback delivery)
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `avg_callback_latency > threshold` seconds (default 15s)
- **Note**: Detects callback endpoint slowdown on the customer's side.

---

## Tier 10 — RCS Monitoring

### 22. `rcs_fallback_rate`
- **Metric**: Percentage of RCS messages that fell back to SMS
- **Source table/view**: `reporting.rcs_delivery_summary` (fallback count / total RCS attempted)
- **Evaluation window**: 5-minute rolling
- **Scope dimensions**: per customer
- **Rule**: Fire when `fallback_rate > threshold`% (default 20%)
- **Note**: Detects RCS availability degradation. Only applicable to customers with RCS enabled.

---

## Implementation Notes

- All evaluation services should run as scheduled jobs (e.g., every 1 minute) rather than being triggered per-message.
- Baseline calculations (24h rolling averages) should be pre-computed and cached to avoid expensive real-time queries.
- Customer-scoped rules use `tenant_id` for scope isolation — the evaluation service must query per-tenant.
- Warning/critical pairs (e.g., `pending_rate` / `pending_rate_critical`, `platform_processing_time` / `platform_processing_time_critical`) share the same underlying metric but fire at different thresholds and severities.
- Rules in the `messaging` category are customer-facing and visible in the customer portal. Rules in `system` are also customer-facing but relate to platform/integration health. `campaign` rules relate to traffic behaviour.
- The `missing_dlr_rate` and `senderid_rejection_rate` rules are distinct from their `supplier_*` counterparts in `admin_defaults` — they measure the same concept but scoped to the customer's traffic rather than the supplier's aggregate.
