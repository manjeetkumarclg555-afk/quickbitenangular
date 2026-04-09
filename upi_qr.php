<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';
require_login();

if (!isset($_SESSION['current_order_id'])) {
    flash('error', 'No pending order.');
    redirect('history.php');
}
$orderId = (int) $_SESSION['current_order_id'];
unset($_SESSION['current_order_id']);

$stmt = $conn->prepare('SELECT total FROM orders WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $orderId, current_user()['id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
$order['total'] = (float) $order['total'];


if (!$order) {
    flash('error', 'Invalid order.');
    redirect('history.php');
}
?>
<section class="upi-page">
    <div class="container">
        <h1>UPI Payment for Order #<?php echo $orderId; ?></h1>
        <p>Amount: Rs. <?php echo number_format($order['total'], 2); ?></p>
        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAADICAYAAACtWK6eAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsMAAA7DAcdvqGQAAAgAElEQVR4nO3df3hT1/8H8F9k0syT6orQsiJ2poQQRyYqKRoIlgYQGmXq3mWyg2fgCQk0VSXCCCFV1SqkT6UaEqSQQuQkQhQsVBTSOwBAi4C77t47d53Z0a2tndm0Za2bmdnZGTmnJmZm5mXm5mblc1ESCRgPP0QURQhQhRFRFAVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVIUIVmerchant_id=123456789
upi://pay?pa=merchant@payu&amp;pn=QuickBite&amp;am=<?php echo $order['total']; ?>&amp;cu=INR">
        <div class="qr-code">
            <img src="https://quickchart.io/qr?text=upi://pay?pa=testmerchant@payu&pn=QuickBite&am=<?php echo $order['total']; ?>&cu=INR&tn=Order%20<?php echo $orderId; ?>" alt="UPI QR Code">
        </div>
        <p>Scan with PhonePe/GPay/Paytm</p>
        <p>UPI ID: testmerchant@payu</p>
        <form method="post" action="confirm_upi.php">
            <input type="hidden" name="order_id" value="<?php echo $orderId; ?>">
            <input type="text" name="payment_id" placeholder="Enter UPI Txn ID after payment" required>
            <button type="submit" class="button primary">Confirm Payment</button>
        </form>
        <a href="history.php" class="button secondary">Back to History</a>
    </div>
</section>
<?php render_footer(); ?>

