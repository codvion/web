<?php
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect_to('dashboard');
}

$pageTitle = 'Login';
$pageDescription = 'Secure admin login for the ERP System business control dashboard.';
$pageRobots = 'noindex, nofollow';
$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) post_value('username'));
    $password = (string) post_value('password');
    $user = query_one('SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1', [$username, $username]);

    if ($user && password_verify($password, $user['password_hash'])) {
        login_user($user);
        redirect_to('dashboard');
    }

    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php require __DIR__ . '/includes/seo.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-card">
            <a class="site-brand login-brand" href="/">
                <img class="brand-logo" src="assets/img/logo.svg" alt="" aria-hidden="true">
                <span>
                    <strong>ERP System</strong>
                    <small>Secure Admin Login</small>
                </span>
            </a>

            <div class="login-heading">
                <p class="eyebrow">Welcome back</p>
                <h1>Login to your ERP dashboard</h1>
                <p>Use your admin account to access clients, projects, finance, salaries, and reports.</p>
            </div>

            <?php if ($error): ?>
                <div class="login-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" class="login-form">
                <label>
                    Username
                    <input type="text" name="username" required value="<?= e($username) ?>" placeholder="admin" autocomplete="username">
                </label>
                <label>
                    Password
                    <input type="password" name="password" required placeholder="admin123" autocomplete="current-password">
                </label>
                <button type="submit" class="primary-button">Login</button>
            </form>

            <div class="demo-login">
                <span>Default login</span>
                <strong>admin / admin123</strong>
            </div>
        </section>
    </main>
</body>
</html>
