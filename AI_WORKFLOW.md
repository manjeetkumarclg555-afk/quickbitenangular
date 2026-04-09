# QuickBite AI Workflow

## Data capture

- `order.php` stores basket totals, zone, ETA, distance, prep time, traffic, weather, and payment method.
- `admin/dashboard.php` updates delivery status and finalizes actual delivery minutes and ratings for delivered orders.
- `confirm_upi.php` marks successful UPI payments and emits a customer notification.

## Preprocessing pipeline

`includes/ai_pipeline.php` converts historical orders into model-ready rows with:

- temporal features
- customer repeat-behavior features
- basket composition features
- delivery timing and zone features
- traffic and weather features
- delay and on-time labels

The browser export endpoint is `admin/export-preprocessed-data.php`.

The CLI export path is:

```powershell
php scripts/preprocess_delivery_ai_dataset.php
```

## Recommendation model

`includes/intelligence.php` implements a hybrid recommendation ranker using:

- category affinity from prior orders
- reorder strength for dishes already liked by the customer
- similar-customer demand in overlapping categories
- delivered-order popularity
- price-fit against the customer’s historical basket profile

The ranked output is rendered in `menu.php`.

## Route optimization model

The route model predicts a likely completion ETA from:

- estimated delivery minutes
- distance in km
- prep time
- traffic level
- weather condition
- total items
- active same-zone order load
- current order status

`admin/dashboard.php` uses that prediction to rank live orders by dispatch priority and batching opportunity.

## Monitoring and validation

- `admin/ai-modeling.php` surfaces AI row counts, on-time rate, delay averages, route-model MAE, and recommendation coverage.
- `scripts/run_ai_validation.php` checks seeded-data accuracy, notification persistence, and basic latency.
