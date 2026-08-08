<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$error = '';
$success = '';
$users = [];
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = (int) ($_POST['user_id'] ?? 0);
    $new_role = $_POST['role'] ?? 'student';
    $new_status = $_POST['status'] ?? 'active';

    if ($new_role !== 'admin' && $new_role !== 'instructor' && $new_role !== 'student') {
        $new_role = 'student';
    }

    if ($new_status !== 'active' && $new_status !== 'pending' && $new_status !== 'blocked') {
        $new_status = 'active';
    }

    if ($target_user_id === $user_id && $new_status !== 'active') {
        $error = 'You cannot block your own admin account.';
    } else {
        $stmt = $conn->prepare('SELECT id, full_name, role FROM login_system WHERE id = ?');
        $stmt->execute([$target_user_id]);

        if ($stmt->rowCount() === 0) {
            $error = 'User not found.';
        } else {
            $target_user = $stmt->fetch();

            if ($target_user['role'] === 'admin') {
                $new_role = 'admin';
            } elseif ($new_role === 'admin') {
                $error = 'Admin role is protected and cannot be assigned from Users page.';
            }

            if ($error === '') {
                $stmt = $conn->prepare('UPDATE login_system SET role = ?, status = ? WHERE id = ?');
                $stmt->execute([$new_role, $new_status, $target_user_id]);

                if ($target_user['role'] === 'admin') {
                    $notification_message = 'Your account status has been updated by the administrator. Admin roles are protected.';
                } else {
                    $notification_message = 'Your account role or status has been updated by the administrator.';
                }

                $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
                $stmt->execute([$target_user_id, 'Account Updated', $notification_message, 'info']);

                $success = 'User updated successfully: ' . $target_user['full_name'];
            }
        }
    }
}

if ($search !== '') {
    $stmt = $conn->prepare('SELECT * FROM login_system WHERE full_name LIKE ? OR user_name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY created_at DESC');
    $stmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
} else {
    $stmt = $conn->prepare('SELECT * FROM login_system ORDER BY created_at DESC');
    $stmt->execute();
}
$users = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Users | LearnPro LMS</title>
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
                <a href="manage_courses.php"><i data-lucide="folder-kanban"></i> Manage Courses</a>
                <a href="manage_lessons.php"><i data-lucide="list-video"></i> Manage Lessons</a>
                <a href="manage_quizzes.php"><i data-lucide="file-question"></i> Manage Quizzes</a>
                <a class="active" href="users.php"><i data-lucide="users"></i> Users</a>
                <a href="notifications.php"><i data-lucide="bell"></i> Notifications</a>
                <a href="profile.php"><i data-lucide="user-round"></i> Profile</a>
            </nav>
        </aside>

        <main class="page-main">
            <section class="page-top">
                <div>
                    <span class="eyebrow">Admin Control</span>
                    <h1>Users</h1>
                    <p>Update user roles and account status across the platform.</p>
                </div>
            </section>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form class="search-panel" method="get" action="users.php">
                <div class="field">
                    <input type="search" name="search" placeholder="Search users" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <button class="btn primary" type="submit"><i data-lucide="search"></i> Search</button>
                <a class="btn" href="users.php"><i data-lucide="rotate-ccw"></i> Reset</a>
            </form>

            <section class="panel">
                <div class="table-shell">
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $row): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                            <?php echo htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?><br>
                                            <?php echo htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td><span class="tag <?php echo htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><span class="tag <?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><?php echo htmlspecialchars($row['last_login'] ?? 'Never', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <form method="post" action="users.php<?php echo $search !== '' ? '?search=' . urlencode($search) : ''; ?>" class="table-actions">
                                                <input type="hidden" name="user_id" value="<?php echo (int) $row['id']; ?>">
                                                <?php if ($row['role'] === 'admin'): ?>
                                                    <input type="hidden" name="role" value="admin">
                                                    <span class="role-lock"><i data-lucide="shield-check"></i> Admin Locked</span>
                                                <?php else: ?>
                                                    <select name="role" aria-label="Role">
                                                        <option value="student" <?php echo $row['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                                        <option value="instructor" <?php echo $row['role'] === 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                                                    </select>
                                                <?php endif; ?>
                                                <select name="status" aria-label="Status">
                                                    <option value="active" <?php echo $row['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="pending" <?php echo $row['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="blocked" <?php echo $row['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                                </select>
                                                <button class="btn small primary" type="submit"><i data-lucide="save"></i> Save</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
