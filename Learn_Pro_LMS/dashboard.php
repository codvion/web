<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$page_title = 'Dashboard';

$metric_one = 0;
$metric_two = 0;
$metric_three = 0;
$metric_four = 0;
$recent_courses = [];
$recent_users = [];
$student_courses = [];
$recent_enrollments = [];

if ($role === 'admin') {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM login_system');
    $stmt->execute();
    $metric_one = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM courses');
    $stmt->execute();
    $metric_two = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM enrollments');
    $stmt->execute();
    $metric_three = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM notifications WHERE is_read = 0');
    $stmt->execute();
    $metric_four = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id ORDER BY c.created_at DESC LIMIT 6');
    $stmt->execute();
    $recent_courses = $stmt->fetchAll();

    $stmt = $conn->prepare('SELECT id, full_name, user_name, email, role, status, created_at FROM login_system ORDER BY created_at DESC LIMIT 6');
    $stmt->execute();
    $recent_users = $stmt->fetchAll();
}

if ($role === 'instructor') {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM courses WHERE instructor_id = ?');
    $stmt->execute([$user_id]);
    $metric_one = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM courses WHERE instructor_id = ? AND status = ?');
    $stmt->execute([$user_id, 'published']);
    $metric_two = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM lessons l INNER JOIN courses c ON l.course_id = c.id WHERE c.instructor_id = ?');
    $stmt->execute([$user_id]);
    $metric_three = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(DISTINCT e.user_id) AS total FROM enrollments e INNER JOIN courses c ON e.course_id = c.id WHERE c.instructor_id = ?');
    $stmt->execute([$user_id]);
    $metric_four = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE c.instructor_id = ? ORDER BY c.created_at DESC LIMIT 6');
    $stmt->execute([$user_id]);
    $recent_courses = $stmt->fetchAll();

    $stmt = $conn->prepare('SELECT e.*, u.full_name AS student_name, c.title AS course_title FROM enrollments e INNER JOIN login_system u ON e.user_id = u.id INNER JOIN courses c ON e.course_id = c.id WHERE c.instructor_id = ? ORDER BY e.enrolled_at DESC LIMIT 6');
    $stmt->execute([$user_id]);
    $recent_enrollments = $stmt->fetchAll();
}

if ($role === 'student') {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM enrollments WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $metric_one = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM enrollments WHERE user_id = ? AND status = ?');
    $stmt->execute([$user_id, 'completed']);
    $metric_two = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM lesson_progress WHERE user_id = ? AND can_continue = 1');
    $stmt->execute([$user_id]);
    $metric_three = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$user_id]);
    $metric_four = $stmt->fetch()['total'];

    $stmt = $conn->prepare('SELECT c.*, e.progress_percent, e.status AS enrollment_status, u.full_name AS instructor_name FROM enrollments e INNER JOIN courses c ON e.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id WHERE e.user_id = ? ORDER BY e.enrolled_at DESC LIMIT 6');
    $stmt->execute([$user_id]);
    $student_courses = $stmt->fetchAll();
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Dashboard | LearnPro LMS</title>
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
                <a class="active" href="dashboard.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>
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
                <a href="profile.php"><i data-lucide="user-round"></i> Profile</a>
            </nav>
        </aside>

        <main class="page-main">
            <section class="page-top">
                <div>
                    <span class="eyebrow">Role Dashboard</span>
                    <h1><?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <?php if ($role === 'admin'): ?>
                        <p>Monitor the full LMS, manage users, and control every course and lesson.</p>
                    <?php elseif ($role === 'instructor'): ?>
                        <p>Create premium courses, organize lessons, and review student activity.</p>
                    <?php else: ?>
                        <p>Continue your enrolled courses, unlock lessons, and track your learning progress.</p>
                    <?php endif; ?>
                </div>
                <div class="actions">
                    <?php if ($role === 'admin' || $role === 'instructor'): ?>
                        <a class="btn primary" href="manage_courses.php"><i data-lucide="plus"></i> New Course</a>
                    <?php else: ?>
                        <a class="btn primary" href="courses.php"><i data-lucide="search"></i> Find Courses</a>
                    <?php endif; ?>
                </div>
            </section>

            <section class="metrics">
                <article class="metric">
                    <span class="feature-icon"><i data-lucide="<?php echo $role === 'admin' ? 'users' : ($role === 'instructor' ? 'book-copy' : 'graduation-cap'); ?>"></i></span>
                    <strong><?php echo (int) $metric_one; ?></strong>
                    <span><?php echo $role === 'admin' ? 'Total users' : ($role === 'instructor' ? 'Your courses' : 'Enrolled courses'); ?></span>
                </article>
                <article class="metric">
                    <span class="feature-icon"><i data-lucide="<?php echo $role === 'admin' ? 'book-open-check' : ($role === 'instructor' ? 'send' : 'badge-check'); ?>"></i></span>
                    <strong><?php echo (int) $metric_two; ?></strong>
                    <span><?php echo $role === 'admin' ? 'Total courses' : ($role === 'instructor' ? 'Published courses' : 'Completed courses'); ?></span>
                </article>
                <article class="metric">
                    <span class="feature-icon"><i data-lucide="<?php echo $role === 'admin' ? 'user-check' : ($role === 'instructor' ? 'list-video' : 'timer'); ?>"></i></span>
                    <strong><?php echo (int) $metric_three; ?></strong>
                    <span><?php echo $role === 'admin' ? 'Enrollments' : ($role === 'instructor' ? 'Lessons created' : 'Unlocked lessons'); ?></span>
                </article>
                <article class="metric">
                    <span class="feature-icon"><i data-lucide="<?php echo $role === 'admin' ? 'bell' : ($role === 'instructor' ? 'users-round' : 'bell'); ?>"></i></span>
                    <strong><?php echo (int) $metric_four; ?></strong>
                    <span><?php echo $role === 'admin' ? 'Unread alerts' : ($role === 'instructor' ? 'Students reached' : 'Unread notifications'); ?></span>
                </article>
            </section>

            <?php if ($role === 'student'): ?>
                <section class="panel">
                    <div class="section-head">
                        <div>
                            <h2>Your Learning</h2>
                            <p>Continue from your enrolled courses and unlock lessons through the required video watch timer.</p>
                        </div>
                    </div>
                    <?php if (count($student_courses) > 0): ?>
                        <div class="course-grid">
                            <?php foreach ($student_courses as $course): ?>
                                <article class="course-card">
                                    <img src="<?php echo htmlspecialchars($course['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="course-card-body">
                                        <div class="tag-row">
                                            <span class="tag"><?php echo htmlspecialchars($course['level'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            <span class="tag success"><?php echo (int) $course['progress_percent']; ?>% Complete</span>
                                        </div>
                                        <h3><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <p><?php echo htmlspecialchars(substr($course['description'], 0, 120), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                        <div class="progress-track">
                                            <span class="progress-bar" style="width: <?php echo (int) $course['progress_percent']; ?>%;"></span>
                                        </div>
                                        <?php if (($course['enrollment_status'] ?? '') === 'completed' || (int) $course['progress_percent'] >= 100): ?>
                                            <div class="form-actions">
                                                <a class="btn success" href="course.php?id=<?php echo (int) $course['id']; ?>"><i data-lucide="check-circle-2"></i> Completed</a>
                                                <a class="btn primary" href="certificate.php?course_id=<?php echo (int) $course['id']; ?>"><i data-lucide="award"></i> View Certificate</a>
                                            </div>
                                        <?php else: ?>
                                            <a class="btn primary" href="course.php?id=<?php echo (int) $course['id']; ?>"><i data-lucide="play-circle"></i> Continue</a>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>You are not enrolled in any course yet.</p>
                            <a class="btn primary" href="courses.php"><i data-lucide="book-open"></i> Browse Courses</a>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($role === 'admin' || $role === 'instructor'): ?>
                <section class="panel">
                    <div class="section-head">
                        <div>
                            <h2><?php echo $role === 'admin' ? 'Recent Courses' : 'Your Recent Courses'; ?></h2>
                            <p>Review course status, ownership, and quick management actions.</p>
                        </div>
                        <a class="btn" href="manage_courses.php"><i data-lucide="folder-kanban"></i> Manage</a>
                    </div>
                    <?php if (count($recent_courses) > 0): ?>
                        <div class="table-shell">
                            <div class="table-scroll">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Instructor</th>
                                            <th>Level</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_courses as $course): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                                    <span><?php echo htmlspecialchars($course['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars($course['instructor_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($course['level'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="tag <?php echo htmlspecialchars($course['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($course['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><a class="btn small" href="course.php?id=<?php echo (int) $course['id']; ?>"><i data-lucide="external-link"></i> Open</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No courses found yet.</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
                <section class="panel">
                    <div class="section-head">
                        <div>
                            <h2>Recent Users</h2>
                            <p>Newly registered users across all account types.</p>
                        </div>
                        <a class="btn" href="users.php"><i data-lucide="users"></i> Manage Users</a>
                    </div>
                    <div class="table-shell">
                        <div class="table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $row): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong><br><?php echo htmlspecialchars($row['user_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="tag <?php echo htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['role'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><span class="tag <?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><?php echo htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($role === 'instructor'): ?>
                <section class="panel">
                    <div class="section-head">
                        <div>
                            <h2>Recent Enrollments</h2>
                            <p>Students who recently joined your courses.</p>
                        </div>
                    </div>
                    <?php if (count($recent_enrollments) > 0): ?>
                        <div class="table-shell">
                            <div class="table-scroll">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Progress</th>
                                            <th>Status</th>
                                            <th>Enrolled</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_enrollments as $row): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($row['course_title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo (int) $row['progress_percent']; ?>%</td>
                                                <td><span class="tag <?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><?php echo htmlspecialchars($row['enrolled_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No student enrollments have been recorded yet.</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
