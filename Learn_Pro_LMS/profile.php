<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$error = '';
$success = '';

$stmt = $conn->prepare('SELECT * FROM login_system WHERE id = ?');
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_full_name = trim($_POST['full_name'] ?? '');
    $new_user_name = trim($_POST['user_name'] ?? '');
    $new_email = trim($_POST['email'] ?? '');
    $new_phone = trim($_POST['phone'] ?? '');
    $new_date_of_birth = trim($_POST['date_of_birth'] ?? '');
    $new_gender = trim($_POST['gender'] ?? '');
    $new_address_line = trim($_POST['address_line'] ?? '');
    $new_city = trim($_POST['city'] ?? '');
    $new_country = trim($_POST['country'] ?? '');
    $new_education_level = trim($_POST['education_level'] ?? '');
    $new_profession = trim($_POST['profession'] ?? '');
    $new_learning_goal = trim($_POST['learning_goal'] ?? '');
    $new_bio = trim($_POST['bio'] ?? '');
    $new_emergency_contact_name = trim($_POST['emergency_contact_name'] ?? '');
    $new_emergency_contact_phone = trim($_POST['emergency_contact_phone'] ?? '');
    $new_linkedin_url = trim($_POST['linkedin_url'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_image_path = $profile['profile_image'] ?? '';

    if ($new_full_name === '' || $new_user_name === '' || $new_email === '' || $new_phone === '') {
        $error = 'Please complete your basic account details.';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($new_linkedin_url !== '' && !filter_var($new_linkedin_url, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid LinkedIn or portfolio URL.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM login_system WHERE (user_name = ? OR email = ? OR phone = ?) AND id != ?');
        $stmt->execute([$new_user_name, $new_email, $new_phone, $user_id]);

        if ($stmt->rowCount() > 0) {
            $error = 'Another account already uses that username, email, or phone.';
        }
    }

    if ($error === '' && $new_date_of_birth !== '') {
        $birth_time = strtotime($new_date_of_birth);
        if ($birth_time === false || $birth_time > time()) {
            $error = 'Please enter a valid date of birth.';
        }
    }

    if ($error === '' && isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Profile image could not be uploaded. Please try again.';
        } elseif ((int) $_FILES['profile_image']['size'] > 2097152) {
            $error = 'Profile image must be 2 MB or smaller.';
        } else {
            $image_info = getimagesize($_FILES['profile_image']['tmp_name']);
            $allowed_mimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];

            if (!$image_info || !isset($allowed_mimes[$image_info['mime'] ?? ''])) {
                $error = 'Please upload a JPG, PNG, or WEBP profile image.';
            } else {
                $upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0775, true);
                }

                $image_extension = $allowed_mimes[$image_info['mime']];
                $profile_image_path = 'uploads/profiles/user_' . $user_id . '_' . time() . '.' . $image_extension;
                $target_path = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $profile_image_path);

                if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                    $error = 'Profile image could not be saved. Please try again.';
                }
            }
        }
    }

    if ($error === '' && $new_password !== '') {
        if ($current_password === '') {
            $error = 'Please enter your current password to change password.';
        } elseif (!password_verify($current_password, $profile['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New password confirmation does not match.';
        }
    }

    if ($error === '') {
        $new_date_of_birth_db = $new_date_of_birth !== '' ? $new_date_of_birth : null;
        $new_gender_db = $new_gender !== '' ? $new_gender : null;

        if ($new_password !== '') {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE login_system SET full_name = ?, user_name = ?, email = ?, phone = ?, profile_image = ?, date_of_birth = ?, gender = ?, address_line = ?, city = ?, country = ?, education_level = ?, profession = ?, learning_goal = ?, bio = ?, emergency_contact_name = ?, emergency_contact_phone = ?, linkedin_url = ?, password_hash = ? WHERE id = ?');
            $stmt->execute([$new_full_name, $new_user_name, $new_email, $new_phone, $profile_image_path, $new_date_of_birth_db, $new_gender_db, $new_address_line, $new_city, $new_country, $new_education_level, $new_profession, $new_learning_goal, $new_bio, $new_emergency_contact_name, $new_emergency_contact_phone, $new_linkedin_url, $new_password_hash, $user_id]);
        } else {
            $stmt = $conn->prepare('UPDATE login_system SET full_name = ?, user_name = ?, email = ?, phone = ?, profile_image = ?, date_of_birth = ?, gender = ?, address_line = ?, city = ?, country = ?, education_level = ?, profession = ?, learning_goal = ?, bio = ?, emergency_contact_name = ?, emergency_contact_phone = ?, linkedin_url = ? WHERE id = ?');
            $stmt->execute([$new_full_name, $new_user_name, $new_email, $new_phone, $profile_image_path, $new_date_of_birth_db, $new_gender_db, $new_address_line, $new_city, $new_country, $new_education_level, $new_profession, $new_learning_goal, $new_bio, $new_emergency_contact_name, $new_emergency_contact_phone, $new_linkedin_url, $user_id]);
        }

        $_SESSION['full_name'] = $new_full_name;
        $_SESSION['user_name'] = $new_user_name;
        $_SESSION['email'] = $new_email;
        $_SESSION['profile_image'] = $profile_image_path;
        $full_name = $new_full_name;

        $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user_id, 'Profile Updated', 'Your detailed profile information has been updated successfully.', 'success']);

        $success = 'Profile updated successfully.';

        $stmt = $conn->prepare('SELECT * FROM login_system WHERE id = ?');
        $stmt->execute([$user_id]);
        $profile = $stmt->fetch();
    }
}

$profile_initial = strtoupper(substr(trim($profile['full_name'] ?? 'U'), 0, 1));
$profile_completion_total = 16;
$profile_completion_done = 0;
$profile_completion_fields = ['full_name', 'user_name', 'email', 'phone', 'profile_image', 'date_of_birth', 'gender', 'address_line', 'city', 'country', 'education_level', 'profession', 'learning_goal', 'bio', 'emergency_contact_name', 'emergency_contact_phone'];

foreach ($profile_completion_fields as $profile_completion_field) {
    if (trim((string) ($profile[$profile_completion_field] ?? '')) !== '') {
        $profile_completion_done++;
    }
}

$profile_completion = (int) floor(($profile_completion_done / $profile_completion_total) * 100);
$profile_image_src = trim((string) ($profile['profile_image'] ?? ''));

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Profile | LearnPro LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/learnpro-header.php'; ?>

    <div class="app-layout">
        <aside class="sidebar">
            <?php include 'partials/sidebar-profile.php'; ?>
            <nav class="side-links">
                <a href="dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>
                <a href="courses.php"><i data-lucide="book-open"></i> Courses</a>
                <a href="certificates.php"><i data-lucide="award"></i> Certificates</a>
                <a href="quizzes.php"><i data-lucide="clipboard-list"></i> Quizzes</a>
                <?php if ($role === 'admin' || $role === 'instructor'): ?>
                    <a href="manage_courses.php"><i data-lucide="folder-kanban"></i> Manage Courses</a>
                    <a href="manage_lessons.php"><i data-lucide="list-video"></i> Manage Lessons</a>
                    <a href="manage_quizzes.php"><i data-lucide="file-question"></i> Manage Quizzes</a>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <a href="users.php"><i data-lucide="users"></i> Users</a>
                <?php endif; ?>
                <a href="notifications.php"><i data-lucide="bell"></i> Notifications</a>
                <a class="active" href="profile.php"><i data-lucide="user-round"></i> Profile</a>
            </nav>
        </aside>

        <main class="page-main">
            <section class="page-top">
                <div>
                    <span class="eyebrow">Account</span>
                    <h1>Profile</h1>
                    <p>Update the details you want to change. Empty optional fields can be completed later.</p>
                </div>
            </section>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <section class="profile-grid advanced-profile-grid">
                <article class="panel profile-summary-card">
                    <div class="profile-avatar-wrap">
                        <?php if ($profile_image_src !== ''): ?>
                            <img data-profile-preview src="<?php echo htmlspecialchars($profile_image_src, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($profile['full_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span data-profile-fallback class="profile-initial hidden"><?php echo htmlspecialchars($profile_initial, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php else: ?>
                            <img data-profile-preview class="hidden" src="" alt="<?php echo htmlspecialchars($profile['full_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span data-profile-fallback class="profile-initial"><?php echo htmlspecialchars($profile_initial, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endif; ?>
                    </div>
                    <h2><?php echo htmlspecialchars($profile['full_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <div class="profile-badges">
                        <span><?php echo htmlspecialchars($profile['role'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span><?php echo htmlspecialchars($profile['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="profile-completion">
                        <div>
                            <strong><?php echo (int) $profile_completion; ?>%</strong>
                            <span>Profile Complete</span>
                        </div>
                        <div class="progress-track">
                            <span class="progress-bar" style="width: <?php echo (int) $profile_completion; ?>%;"></span>
                        </div>
                    </div>
                    <div class="profile-facts">
                        <p><strong>Phone</strong><span><?php echo htmlspecialchars($profile['phone'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p><strong>Location</strong><span><?php echo htmlspecialchars(trim(($profile['city'] ?? '') . ', ' . ($profile['country'] ?? ''), ', '), ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p><strong>Joined</strong><span><?php echo htmlspecialchars($profile['created_at'], ENT_QUOTES, 'UTF-8'); ?></span></p>
                        <p><strong>Last Login</strong><span><?php echo htmlspecialchars($profile['last_login'] ?? 'Never', ENT_QUOTES, 'UTF-8'); ?></span></p>
                    </div>
                </article>

                <article class="panel profile-form-panel">
                    <h2>Update Detailed Profile</h2>
                    <form method="post" action="profile.php" class="form-grid advanced-profile-form" enctype="multipart/form-data">
                        <div class="field full profile-upload-field">
                            <label for="profile_image">Profile Image</label>
                            <input id="profile_image" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp" data-profile-image-input>
                            <small>Upload JPG, PNG, or WEBP. Maximum size is 2 MB.</small>
                        </div>

                        <div class="field full form-section-title">
                            <span>Basic Identity</span>
                        </div>
                        <div class="field">
                            <label for="full_name">Full Name</label>
                            <input id="full_name" type="text" name="full_name" value="<?php echo htmlspecialchars($profile['full_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="user_name">Username</label>
                            <input id="user_name" type="text" name="user_name" value="<?php echo htmlspecialchars($profile['user_name'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="date_of_birth">Date of Birth</label>
                            <input id="date_of_birth" type="date" name="date_of_birth" value="<?php echo htmlspecialchars($profile['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="field">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($profile['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                                <option value="Female" <?php echo ($profile['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                                <option value="Other" <?php echo ($profile['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                                <option value="Prefer not to say" <?php echo ($profile['gender'] ?? '') === 'Prefer not to say' ? 'selected' : ''; ?>>Prefer not to say</option>
                            </select>
                        </div>

                        <div class="field full form-section-title">
                            <span>Contact and Address</span>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="phone">Phone</label>
                            <input id="phone" type="text" name="phone" value="<?php echo htmlspecialchars($profile['phone'], ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field full">
                            <label for="address_line">Address</label>
                            <input id="address_line" type="text" name="address_line" value="<?php echo htmlspecialchars($profile['address_line'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="field">
                            <label for="city">City</label>
                            <input id="city" type="text" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="field">
                            <label for="country">Country</label>
                            <input id="country" type="text" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="field full form-section-title">
                            <span>Learning Profile</span>
                        </div>
                        <div class="field">
                            <label for="education_level">Education Level</label>
                            <input id="education_level" type="text" name="education_level" value="<?php echo htmlspecialchars($profile['education_level'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Intermediate, Bachelor, Master...">
                        </div>
                        <div class="field">
                            <label for="profession">Profession</label>
                            <input id="profession" type="text" name="profession" value="<?php echo htmlspecialchars($profile['profession'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Student, Developer, Designer...">
                        </div>
                        <div class="field full">
                            <label for="learning_goal">Learning Goal</label>
                            <input id="learning_goal" type="text" name="learning_goal" value="<?php echo htmlspecialchars($profile['learning_goal'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="What do you want to achieve?">
                        </div>
                        <div class="field full">
                            <label for="bio">About You</label>
                            <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($profile['bio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="field">
                            <label for="emergency_contact_name">Emergency Contact Name</label>
                            <input id="emergency_contact_name" type="text" name="emergency_contact_name" value="<?php echo htmlspecialchars($profile['emergency_contact_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="field">
                            <label for="emergency_contact_phone">Emergency Contact Phone</label>
                            <input id="emergency_contact_phone" type="text" name="emergency_contact_phone" value="<?php echo htmlspecialchars($profile['emergency_contact_phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="field full">
                            <label for="linkedin_url">LinkedIn / Portfolio URL</label>
                            <input id="linkedin_url" type="url" name="linkedin_url" value="<?php echo htmlspecialchars($profile['linkedin_url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.linkedin.com/in/username">
                        </div>

                        <div class="field full form-section-title">
                            <span>Password Security</span>
                        </div>
                        <div class="field full">
                            <label for="current_password">Current Password</label>
                            <input id="current_password" type="password" name="current_password">
                        </div>
                        <div class="field">
                            <label for="new_password">New Password</label>
                            <input id="new_password" type="password" name="new_password" minlength="6">
                        </div>
                        <div class="field">
                            <label for="confirm_password">Confirm New Password</label>
                            <input id="confirm_password" type="password" name="confirm_password" minlength="6">
                        </div>
                        <div class="field full">
                            <button class="btn primary" type="submit"><i data-lucide="save"></i> Save Profile Changes</button>
                        </div>
                    </form>
                </article>
            </section>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/profile.js"></script>
</body>
</html>
