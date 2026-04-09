<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

require_login();

$user = current_user();
global $conn;

// Order count
$orderStmt = $conn->prepare('SELECT COUNT(*) AS count FROM orders WHERE user_id = ?');
$orderStmt->bind_param('i', $user['id']);
$orderStmt->execute();
$orderCount = (int) $orderStmt->get_result()->fetch_assoc()['count'];
$orderStmt->close();

// Cart count
$cartCount = cart_count($conn, (int) $user['id']);

// Password update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_password'])) {
    if (strlen($_POST['new_password']) >= 6) {
        $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $updateStmt->bind_param('si', $hash, $user['id']);
        if ($updateStmt->execute()) {
            flash('success', 'Password updated!');
        } else {
            flash('error', 'Update failed.');
        }
        $updateStmt->close();
        redirect('profile.php');
    } else {
        $message = flash('error', 'Password too short.');
    }
}

render_header('My Profile');
?>
<section class="page-section">
    <div class="container">
        <h1>My Profile</h1>
        <?php if ($message): ?>
            <div class="flash error"><?php echo h($message); ?></div>
        <?php endif; ?>
        <div class="profile-card">
            <h2>Account Info</h2>
            <p><strong>Name:</strong> <?php echo h($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo h($user['email']); ?></p>
            <p><strong>Total Orders:</strong> <?php echo $orderCount; ?></p>
            <p><strong>Cart Items:</strong> <?php echo $cartCount; ?></p>
        </div>
        <div class="profile-card">
            <h2>Change Password</h2>
            <form method="POST">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6" class="form-input">
                </div>
                <button type="submit" class="button primary">Update Password</button>
            </form>
        </div>
        <div class="profile-actions">
            <a href="<?php echo h(app_path('history.php')); ?>" class="button secondary">Order History</a>
            <a href="<?php echo h(app_path('cart.php')); ?>" class="button secondary">Cart</a>
        </div>
    </div>
</section>
<?php render_footer(); ?>

