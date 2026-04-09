<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

require_login();

$userId = (int) current_user()['id'];
$summary = cart_summary($conn, $userId);
$itemCount = 0;
foreach ($summary['items'] as $item) {
    $itemCount += (int) $item['quantity'];
}

if (empty($summary['items'])) {
    flash('error', 'Your cart is empty.');
    redirect('menu.php');
}

$selectedZone = trim($_POST['delivery_zone'] ?? 'Central');
$paymentMethod = trim($_POST['payment_method'] ?? 'Cash on Delivery');
$deliveryProfile = estimate_delivery_profile($selectedZone, $itemCount);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'Cash on Delivery');
    $specialInstructions = trim($_POST['special_instructions'] ?? '');

    if ($address === '') {
        flash('error', 'Delivery address is required.');
        redirect('order.php');
    }

    $status = 'Placed';
    $subtotal = $summary['subtotal'];
    $deliveryFee = $summary['delivery_fee'];
    $taxAmount = $summary['tax'];
    $total = $summary['grand_total'];
    $distanceKm = $deliveryProfile['distance_km'];
    $estimatedMinutes = $deliveryProfile['estimated_delivery_minutes'];
    $prepTimeMinutes = $deliveryProfile['prep_time_minutes'];
    $trafficLevel = $deliveryProfile['traffic_level'];
    $weatherCondition = $deliveryProfile['weather_condition'];

    $stmt = $conn->prepare(
        'INSERT INTO orders(
            user_id, subtotal, delivery_fee, tax_amount, total, status, delivery_address,
            payment_method, delivery_zone, distance_km, estimated_delivery_minutes,
            actual_delivery_minutes, prep_time_minutes, traffic_level, weather_condition,
            customer_rating, special_instructions, delivered_at
         ) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, NULL, ?, NULL)'
    );
    $stmt->bind_param(
        'iddddssssdiisss',
        $userId,
        $subtotal,
        $deliveryFee,
        $taxAmount,
        $total,
        $status,
        $address,
        $paymentMethod,
        $selectedZone,
        $distanceKm,
        $estimatedMinutes,
        $prepTimeMinutes,
        $trafficLevel,
        $weatherCondition,
        $specialInstructions
    );
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    $itemStmt = $conn->prepare(
        'INSERT INTO order_items(order_id, food_id, quantity, price)
         VALUES(?, ?, ?, ?)'
    );

    foreach ($summary['items'] as $item) {
        $foodId = (int) $item['food_id'];
        $qty = (int) $item['quantity'];
        $price = (float) $item['price'];
        $itemStmt->bind_param('iiid', $orderId, $foodId, $qty, $price);
        $itemStmt->execute();
    }

    $itemStmt->close();

    $clearStmt = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
    $clearStmt->bind_param('i', $userId);
    $clearStmt->execute();
    $clearStmt->close();

    create_notification(
        $conn,
        $userId,
        $orderId,
        'order_placed',
        'Order placed successfully',
        'Order #' . $orderId . ' is confirmed with an ETA of ' . $estimatedMinutes . ' minutes.',
        'success'
    );

    flash('success', 'Order #' . $orderId . ' placed successfully. Delivery ETA: ' . $estimatedMinutes . ' minutes.');
    if ($paymentMethod === 'UPI QR') {
        $_SESSION['current_order_id'] = $orderId;
        redirect('upi_qr.php');
    } else {
        redirect('history.php');
    }
}


render_header('Checkout');
?>
<section class="checkout-layout">
    <form class="checkout-card" method="post">
        <p class="eyebrow">Checkout</p>
        <h1>Complete your delivery details</h1>
        <label>
            <span>Delivery address</span>
            <textarea name="address" rows="4" placeholder="House no, street, area, city" required></textarea>
        </label>
        <label>
            <span>Delivery zone</span>
            <select name="delivery_zone">
                <?php foreach (delivery_zone_options() as $zone): ?>
                    <option value="<?php echo h($zone); ?>" <?php echo $selectedZone === $zone ? 'selected' : ''; ?>>
                        <?php echo h($zone); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <span>Payment method</span>
            <select name="payment_method">
                <?php foreach (payment_method_options() as $method): ?>
                    <option value="<?php echo h($method); ?>"><?php echo h($method); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            <span>Special instructions</span>
            <textarea name="special_instructions" rows="3" placeholder="Gate code, landmark, cutlery preference, or rider note"></textarea>
        </label>
        <button class="button primary" type="submit">Place Order</button>

    </form>





    <aside class="summary-card">
        <h2>Operational Summary</h2>
        <?php foreach ($summary['items'] as $item): ?>
            <div class="summary-row">
                <span><?php echo h($item['name'] . ' x' . $item['quantity']); ?></span>
                <strong><?php echo h(format_price((float) $item['line_total'])); ?></strong>
            </div>
        <?php endforeach; ?>
        <div class="summary-row"><span>Subtotal</span><strong><?php echo h(format_price((float) $summary['subtotal'])); ?></strong></div>
        <div class="summary-row"><span>Delivery Fee</span><strong><?php echo h(format_price((float) $summary['delivery_fee'])); ?></strong></div>
        <div class="summary-row"><span>Tax</span><strong><?php echo h(format_price((float) $summary['tax'])); ?></strong></div>
        <div class="summary-row"><span>Delivery zone</span><strong><?php echo h($selectedZone); ?></strong></div>
        <div class="summary-row"><span>Estimated ETA</span><strong><?php echo h(format_minutes((int) $deliveryProfile['estimated_delivery_minutes'])); ?></strong></div>
        <div class="summary-row"><span>Distance</span><strong><?php echo h(number_format((float) $deliveryProfile['distance_km'], 1)); ?> km</strong></div>
        <div class="summary-row"><span>Traffic</span><strong><?php echo h($deliveryProfile['traffic_level']); ?></strong></div>
        <div class="summary-row total"><span>Total Payable</span><strong><?php echo h(format_price((float) $summary['grand_total'])); ?></strong></div>
    </aside>
</section>
<?php render_footer(); ?>
