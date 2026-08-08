<?php

require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$full_name = '';
$user_name = '';
$email = '';
$phone = '';
$role = 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $user_name = trim($_POST['user_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = 'student';

    if ($full_name === '' || $user_name === '' || $email === '' || $phone === '' || $password === '' || $confirm_password === '') {
        $error = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password confirmation does not match.';
    } else {
        $stmt = $conn->prepare('SELECT user_name, email, phone FROM login_system WHERE user_name=? OR email=? OR phone=? ');
        $stmt->execute([$user_name, $email, $phone]);
        if ($stmt->rowCount() > 0) {
            $error = 'An account already exists with this username, email, or phone number.';
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare('INSERT INTO login_system (full_name, user_name, email, phone, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$full_name, $user_name, $email, $phone, $password_hash, $role, 'active']);
            $new_user_id = $conn->lastInsertId();

            $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
            $stmt->execute([$new_user_id, 'Welcome to LearnPro LMS', 'Your account has been created successfully. Your dashboard is ready.', 'success']);

            $success = 'Account created successfully. You can now login.';
            $full_name = '';
            $user_name = '';
            $email = '';
            $phone = '';
            $role = 'student';
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
    <title>Register | LearnPro LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <?php include 'partials/learnpro-header.php'; ?>

    <main class="auth-shell professional-auth-shell">
        <section class="auth-visual signup-showcase" data-animate>
            <div class="signup-board">
                <div class="signup-student-card">
                    <div class="signup-avatar"><i data-lucide="user-round"></i></div>
                    <div>
                        <span>New Student</span>
                        <strong>Account Ready</strong>
                    </div>
                    <em>Student</em>
                </div>

                <div class="signup-course-stack">
                    <article class="signup-course-card active">
                        <div>
                            <span>Course 01</span>
                            <strong>Digital Skills</strong>
                        </div>
                        <i data-lucide="book-open-check"></i>
                    </article>
                    <article class="signup-course-card">
                        <div>
                            <span>Course 02</span>
                            <strong>Web Basics</strong>
                        </div>
                        <i data-lucide="monitor-play"></i>
                    </article>
                </div>

                <div class="signup-path">
                    <span><i data-lucide="user-check"></i> Profile</span>
                    <span><i data-lucide="play-circle"></i> Lessons</span>
                    <span><i data-lucide="file-question"></i> Quiz</span>
                    <span><i data-lucide="award"></i> Certificate</span>
                </div>

                <div class="signup-certificate-preview">
                    <i data-lucide="badge-check"></i>
                    <div>
                        <span>Certificate</span>
                        <strong>Unlocked after completion</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="auth-panel" data-animate>
            <div class="auth-panel-head">
                <span class="eyebrow">Student Access</span>
                <h2>Create Account</h2>
                <p>Student registration is open. Team access is managed by the platform admin.</p>
            </div>
            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="post" action="register.php" class="form-grid">
                <div class="field full">
                    <label for="full_name">Full Name</label>
                    <input id="full_name" type="text" name="full_name" value="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="field">
                    <label for="user_name">Username</label>
                    <input id="user_name" type="text" name="user_name" value="<?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" type="text" name="phone" value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="field full">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" minlength="6" required>
                </div>
                <div class="field">
                    <label for="confirm_password">Confirm Password</label>
                    <input id="confirm_password" type="password" name="confirm_password" minlength="6" required>
                </div>
                <input type="hidden" name="role" value="student">
                <div class="field full">
                    <div class="account-type-lock">
                        <i data-lucide="graduation-cap"></i>
                        <div>
                            <strong>Student Account</strong>
                            <span>Your dashboard will be created with student learning access.</span>
                        </div>
                    </div>
                </div>
                <div class="field full">
                    <button class="btn primary" type="submit"><i data-lucide="user-plus"></i> Create Account</button>
                </div>
            </form>
            <div class="auth-footnote">
                <p>Already have an account? <a href="login.php"><strong>Login here</strong></a>.</p>
            </div>
        </section>
    </main>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
