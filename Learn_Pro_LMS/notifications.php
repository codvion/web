<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$success = '';
$notifications = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_all') {
        $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->execute([$user_id]);
        $success = 'All notifications marked as read.';
    }

    if ($action === 'mark_one') {
        $notification_id = (int) ($_POST['notification_id'] ?? 0);
        $stmt = $conn->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
        $stmt->execute([$notification_id, $user_id]);
        $success = 'Notification marked as read.';
    }
}

$stmt = $conn->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Notifications | LearnPro LMS</title>
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
                <a class="active" href="notifications.php"><i data-lucide="bell"></i> Notifications</a>
                <a href="profile.php"><i data-lucide="user-round"></i> Profile</a>
            </nav>
        </aside>

        <main class="page-main">
            <section class="page-top">
                <div>
                    <span class="eyebrow">Updates</span>
                    <h1>Notifications</h1>
                    <p>Review account, course, enrollment, and lesson progress updates.</p>
                </div>
                <form method="post" action="notifications.php">
                    <input type="hidden" name="action" value="mark_all">
                    <button class="btn primary" type="submit"><i data-lucide="check-check"></i> Mark All Read</button>
                </form>
            </section>

            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <article class="notice <?php echo (int) $notification['is_read'] === 0 ? 'unread' : ''; ?> <?php echo htmlspecialchars($notification['type'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="section-head" style="margin-bottom: 8px;">
                            <div>
                                <span class="tag <?php echo htmlspecialchars($notification['type'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($notification['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <h3><?php echo htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <small><?php echo htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                            </div>
                            <?php if ((int) $notification['is_read'] === 0): ?>
                                <form method="post" action="notifications.php">
                                    <input type="hidden" name="action" value="mark_one">
                                    <input type="hidden" name="notification_id" value="<?php echo (int) $notification['id']; ?>">
                                    <button class="btn small" type="submit"><i data-lucide="check"></i> Read</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No notifications yet.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
