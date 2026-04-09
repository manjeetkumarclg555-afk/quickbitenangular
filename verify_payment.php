<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$input = json_decode(file_get_contents('php://input'), true);
$razorpaySignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (!$razorpaySignature || !hash_equals(hash_hmac('sha256', file_get_contents('php://input'), 'your_webhook_secret'), $razorpaySignature)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

$paymentId = $input['razorpay_payment_id'] ?? '';
if ($paymentId) {
    // Complete order logic here (call from session or param)
    // Assume order pre-created in pending state
    flash('success', 'Payment successful. Order confirmed.');
}

redirect('history.php');
?>

