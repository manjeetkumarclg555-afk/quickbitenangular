<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || strlen($password) < 6) {
        flash('error', 'Please provide a name, valid email, and password with at least 6 characters.');
        redirect('register.php');
    }

    $checkStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $checkStmt->bind_param('s', $email);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if ($exists) {
        flash('error', 'That email is already registered.');
        redirect('register.php');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'customer';
    $stmt = $conn->prepare('INSERT INTO users(name, email, password, role) VALUES(?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $email, $hash, $role);
    $stmt->execute();
    $stmt->close();

    flash('success', 'Registration complete. Please log in.');
    redirect('login.php');
}

render_header('Create Account');
?>
<section class="auth-shell">
    <form class="auth-card" method="post">
        <p class="eyebrow">New customer</p>
        <h1>Create your account</h1>
        <label>
            <span>Full name</span>
            <input type="text" name="name" placeholder="Priya Sharma" required>
        </label>
        <label>
            <span>Email</span>
            <input type="email" name="email" placeholder="you@example.com" required>
        </label>
        <label>
            <span>Password</span>
            <input type="password" name="password" placeholder="At least 6 characters" minlength="6" required>
        </label>
        <button class="button primary" type="submit">Sign Up</button>
    </form>
</section>
<?php render_footer(); ?>
