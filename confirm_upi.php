<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/intelligence.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('history.php');
}

$orderId = (int) ($_POST['order_id'] ?? 0);
$paymentId = trim($_POST['payment_id'] ?? '');

if (!$orderId || !$paymentId) {
    flash('error', 'Invalid payment details.');
    redirect('history.php');
}

$userId = current_user()['id'];

// Update payment reference
$stmt = $conn->prepare('UPDATE orders SET payment_method = CONCAT("UPI: ", ?), status = "Paid" WHERE id = ? AND user_id = ?');
$stmt->bind_param('sii', $paymentId, $orderId, $userId);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    create_notification(
        $conn,
        (int) $userId,
        $orderId,
        'payment_confirmed',
        'Payment received',
        'UPI payment was confirmed for Order #' . $orderId . '. The kitchen can start processing it now.',
        'success'
    );
    flash('success', 'Payment confirmed for Order #' . $orderId . '.');
} else {
    flash('error', 'Order not found or already paid.');
}
$stmt->close();

redirect('history.php');
?>

