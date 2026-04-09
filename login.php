<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

if (is_logged_in()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    $seededAdminMatch = $user
        && $user['role'] === 'admin'
        && $user['password'] === 'ADMIN123_SEEDED'
        && $password === 'admin123';

    $seededDemoUserMatch = $user
        && $user['role'] === 'customer'
        && $user['password'] === 'SEEDED_USER'
        && $password === 'demo123';

    if ($user && ($seededAdminMatch || $seededDemoUserMatch || password_verify($password, $user['password']))) {
        unset($user['password']);
        $_SESSION['user'] = $user;
        flash('success', 'Welcome back, ' . $user['name'] . '!');
        redirect('index.php');
    }

    flash('error', 'Invalid email or password.');
    redirect('login.php');
}

render_header('Login');
?>
<section class="auth-shell">
    <form class="auth-card" method="post">
        <p class="eyebrow">Welcome to My online testy food System</p>
        <h1>Log in to continue</h1>
        <label>
            <span>Email</span>
            <input type="email" name="email" placeholder="you@example.com" required>
        </label>
        <label>
            <span>Password</span>
            <input type="password" name="password" placeholder="Enter your password" required>
        </label>
        <button class="button primary" type="submit">Login</button>
        <!-- <p class="muted">Admin demo: <strong>admin@quickbite.test</strong> / <strong>admin123</strong></p> -->
        <!-- <p class="muted">Customer demo: <strong>aarav@quickbite.test</strong> / <strong>demo123</strong></p> -->
    </form>
</section>
<?php render_footer(); ?>
