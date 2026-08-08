<?php

require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$identifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $error = 'Please enter your username, email, or phone and password.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM login_system WHERE user_name=? OR email=? OR phone=? LIMIT 1');
        $stmt->execute([$identifier, $identifier, $identifier]);

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();

            if ($user['status'] !== 'active') {
                $error = 'Your account is not active. Please contact the administrator.';
            } elseif (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_image'] = $user['profile_image'] ?? '';

                $stmt = $conn->prepare('UPDATE login_system SET last_login = NOW() WHERE id = ?');
                $stmt->execute([$user['id']]);

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid login credentials.';
            }
        } else {
            $error = 'Invalid login credentials.';
        }
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Login | LearnPro LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <?php include 'partials/learnpro-header.php'; ?>

    <main class="auth-shell professional-auth-shell">
        <section class="auth-visual" data-animate>
            <div class="auth-device">
                <div class="auth-device-top">
                    <span></span>
                    <span></span>
                    <span></span>
                    <strong>LearnPro Workspace</strong>
                </div>
                <div class="auth-device-grid">
                    <div class="auth-mini-nav">
                        <i data-lucide="layout-dashboard"></i>
                        <i data-lucide="book-open"></i>
                        <i data-lucide="clipboard-check"></i>
                        <i data-lucide="award"></i>
                    </div>
                    <div class="auth-mini-main">
                        <div class="auth-visual-head">
                            <span>Live Dashboard</span>
                            <strong>Today</strong>
                        </div>
                        <div class="auth-progress-card">
                            <span>Course Progress</span>
                            <strong>78%</strong>
                            <div class="auth-progress-line"><b></b></div>
                        </div>
                        <div class="auth-tiles">
                            <div>
                                <i data-lucide="shield-check"></i>
                                <strong>Secure</strong>
                                <span>Session</span>
                            </div>
                            <div>
                                <i data-lucide="timer"></i>
                                <strong>90s</strong>
                                <span>Quizzes</span>
                            </div>
                        </div>
                        <div class="auth-activity">
                            <span><b></b> New lesson unlocked</span>
                            <span><b></b> Certificate ready</span>
                            <span><b></b> Quiz attempt saved</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-panel" data-animate>
            <div class="auth-panel-head">
                <span class="eyebrow">Account Login</span>
                <h2>Sign in</h2>
                <p>Use your username, email, or phone number to access LearnPro.</p>
            </div>
            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="post" action="login.php" class="form-grid">
                <div class="field full">
                    <label for="identifier">Username, Email, or Phone</label>
                    <input id="identifier" type="text" name="identifier" value="<?php echo htmlspecialchars($identifier, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="field full">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required>
                </div>
                <div class="field full">
                    <button class="btn primary" type="submit"><i data-lucide="log-in"></i> Login</button>
                </div>
            </form>
            <div class="auth-footnote">
                <p>New to LearnPro? <a href="register.php"><strong>Create an account</strong></a>.</p>
                <button class="seed-login" type="button" data-seed-login data-identifier="instructor" data-password="ins123">
                    <strong>Seed login:</strong> <span>instructor / ins123</span>
                </button>
                <button class="seed-login" type="button" data-seed-login data-identifier="student" data-password="stu123">
                    <strong>Seed login:</strong> <span>student / stu123</span>
                </button>
            </div>
        </section>
    </main>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
