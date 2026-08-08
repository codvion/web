<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$certificates = [];

if ($role === 'student') {
    $stmt = $conn->prepare('SELECT e.*, c.id AS course_id, c.title AS course_title, c.category, c.level, c.duration, c.cover_image, u.full_name AS instructor_name, ls.full_name AS student_name, (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id AND l.status = ?) AS total_lessons FROM enrollments e INNER JOIN courses c ON e.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id INNER JOIN login_system ls ON e.user_id = ls.id WHERE e.user_id = ? AND (e.status = ? OR e.progress_percent >= ?) ORDER BY e.completed_at DESC, e.enrolled_at DESC');
    $stmt->execute(['published', $user_id, 'completed', 100]);
} elseif ($role === 'instructor') {
    $stmt = $conn->prepare('SELECT e.*, c.id AS course_id, c.title AS course_title, c.category, c.level, c.duration, c.cover_image, u.full_name AS instructor_name, ls.full_name AS student_name, (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id AND l.status = ?) AS total_lessons FROM enrollments e INNER JOIN courses c ON e.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id INNER JOIN login_system ls ON e.user_id = ls.id WHERE c.instructor_id = ? AND (e.status = ? OR e.progress_percent >= ?) ORDER BY e.completed_at DESC, e.enrolled_at DESC');
    $stmt->execute(['published', $user_id, 'completed', 100]);
} else {
    $stmt = $conn->prepare('SELECT e.*, c.id AS course_id, c.title AS course_title, c.category, c.level, c.duration, c.cover_image, u.full_name AS instructor_name, ls.full_name AS student_name, (SELECT COUNT(*) FROM lessons l WHERE l.course_id = c.id AND l.status = ?) AS total_lessons FROM enrollments e INNER JOIN courses c ON e.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id INNER JOIN login_system ls ON e.user_id = ls.id WHERE e.status = ? OR e.progress_percent >= ? ORDER BY e.completed_at DESC, e.enrolled_at DESC');
    $stmt->execute(['published', 'completed', 100]);
}

$certificates = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Certificates | LearnPro LMS</title>
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
                <a class="active" href="certificates.php"><i data-lucide="award"></i> Certificates</a>
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
                <a href="profile.php"><i data-lucide="user-round"></i> Profile</a>
            </nav>
        </aside>

        <main class="page-main">
            <section class="page-top">
                <div>
                    <span class="eyebrow">Achievements</span>
                    <h1>Certificates</h1>
                    <?php if ($role === 'student'): ?>
                        <p>View certificates for every course you have completed.</p>
                    <?php elseif ($role === 'instructor'): ?>
                        <p>Review certificates earned by students in your courses.</p>
                    <?php else: ?>
                        <p>Review every completed course certificate across the platform.</p>
                    <?php endif; ?>
                </div>
                <a class="btn" href="courses.php"><i data-lucide="book-open"></i> Courses</a>
            </section>

            <?php if (count($certificates) > 0): ?>
                <section class="course-grid">
                    <?php foreach ($certificates as $certificate): ?>
                        <?php
                        $completed_date = $certificate['completed_at'] ?? $certificate['enrolled_at'];
                        $certificate_id = 'LP-' . str_pad((string) $certificate['user_id'], 4, '0', STR_PAD_LEFT) . '-' . str_pad((string) $certificate['course_id'], 4, '0', STR_PAD_LEFT);
                        $certificate_url = 'certificate.php?course_id=' . (int) $certificate['course_id'];
                        if ($role === 'admin' || $role === 'instructor') {
                            $certificate_url .= '&user_id=' . (int) $certificate['user_id'];
                        }
                        ?>
                        <article class="course-card certificate-list-card">
                            <img src="<?php echo htmlspecialchars($certificate['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($certificate['course_title'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="course-card-body">
                                <div class="tag-row">
                                    <span class="tag success">Completed</span>
                                    <span class="tag"><?php echo htmlspecialchars($certificate['level'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($certificate['course_title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <?php if ($role !== 'student'): ?>
                                    <p><strong>Student:</strong> <?php echo htmlspecialchars($certificate['student_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                                <p><strong>Instructor:</strong> <?php echo htmlspecialchars($certificate['instructor_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <div class="certificate-mini-grid">
                                    <span>Completed<strong><?php echo htmlspecialchars(date('M j, Y', strtotime($completed_date)), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                    <span>Certificate ID<strong><?php echo htmlspecialchars($certificate_id, ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                    <span>Lessons<strong><?php echo (int) $certificate['total_lessons']; ?></strong></span>
                                </div>
                                <a class="btn primary" href="<?php echo htmlspecialchars($certificate_url, ENT_QUOTES, 'UTF-8'); ?>"><i data-lucide="award"></i> View Certificate</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <div class="empty-state">
                    <?php if ($role === 'student'): ?>
                        <p>No certificates yet. Complete a course to unlock your certificate.</p>
                        <a class="btn primary" href="courses.php"><i data-lucide="book-open"></i> Continue Learning</a>
                    <?php else: ?>
                        <p>No completed course certificates have been generated yet.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
