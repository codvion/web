<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$courses = [];
$categories = [];

$stmt = $conn->prepare('SELECT DISTINCT category FROM courses WHERE status = ? ORDER BY category ASC');
$stmt->execute(['published']);
$categories = $stmt->fetchAll();

if ($role === 'student') {
    if ($search !== '' && $category !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, e.id AS enrollment_id, e.progress_percent, e.status AS enrollment_status FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id LEFT JOIN enrollments e ON e.course_id = c.id AND e.user_id = ? WHERE c.status = ? AND (c.title LIKE ? OR c.description LIKE ?) AND c.category = ? ORDER BY c.created_at DESC');
        $stmt->execute([$user_id, 'published', '%' . $search . '%', '%' . $search . '%', $category]);
    } elseif ($search !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, e.id AS enrollment_id, e.progress_percent, e.status AS enrollment_status FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id LEFT JOIN enrollments e ON e.course_id = c.id AND e.user_id = ? WHERE c.status = ? AND (c.title LIKE ? OR c.description LIKE ?) ORDER BY c.created_at DESC');
        $stmt->execute([$user_id, 'published', '%' . $search . '%', '%' . $search . '%']);
    } elseif ($category !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, e.id AS enrollment_id, e.progress_percent, e.status AS enrollment_status FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id LEFT JOIN enrollments e ON e.course_id = c.id AND e.user_id = ? WHERE c.status = ? AND c.category = ? ORDER BY c.created_at DESC');
        $stmt->execute([$user_id, 'published', $category]);
    } else {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, e.id AS enrollment_id, e.progress_percent, e.status AS enrollment_status FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id LEFT JOIN enrollments e ON e.course_id = c.id AND e.user_id = ? WHERE c.status = ? ORDER BY c.created_at DESC');
        $stmt->execute([$user_id, 'published']);
    }
    $courses = $stmt->fetchAll();
} elseif ($role === 'instructor') {
    if ($search !== '' && $category !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, NULL AS enrollment_id, 0 AS progress_percent FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE (c.status = ? OR c.instructor_id = ?) AND (c.title LIKE ? OR c.description LIKE ?) AND c.category = ? ORDER BY c.created_at DESC');
        $stmt->execute(['published', $user_id, '%' . $search . '%', '%' . $search . '%', $category]);
    } elseif ($search !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, NULL AS enrollment_id, 0 AS progress_percent FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE (c.status = ? OR c.instructor_id = ?) AND (c.title LIKE ? OR c.description LIKE ?) ORDER BY c.created_at DESC');
        $stmt->execute(['published', $user_id, '%' . $search . '%', '%' . $search . '%']);
    } elseif ($category !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, NULL AS enrollment_id, 0 AS progress_percent FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE (c.status = ? OR c.instructor_id = ?) AND c.category = ? ORDER BY c.created_at DESC');
        $stmt->execute(['published', $user_id, $category]);
    } else {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, NULL AS enrollment_id, 0 AS progress_percent FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE c.status = ? OR c.instructor_id = ? ORDER BY c.created_at DESC');
        $stmt->execute(['published', $user_id]);
    }
    $courses = $stmt->fetchAll();
} else {
    if ($search !== '' && $category !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, NULL AS enrollment_id, 0 AS progress_percent FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE (c.title LIKE ? OR c.description LIKE ?) AND c.category = ? ORDER BY c.created_at DESC');
        $stmt->execute(['%' . $search . '%', '%' . $search . '%', $category]);
    } elseif ($search !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, NULL AS enrollment_id, 0 AS progress_percent FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE c.title LIKE ? OR c.description LIKE ? ORDER BY c.created_at DESC');
        $stmt->execute(['%' . $search . '%', '%' . $search . '%']);
    } elseif ($category !== '') {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, NULL AS enrollment_id, 0 AS progress_percent FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE c.category = ? ORDER BY c.created_at DESC');
        $stmt->execute([$category]);
    } else {
        $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name, NULL AS enrollment_id, 0 AS progress_percent FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id ORDER BY c.created_at DESC');
        $stmt->execute();
    }
    $courses = $stmt->fetchAll();
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Courses | LearnPro LMS</title>
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
                <a class="active" href="courses.php"><i data-lucide="book-open"></i> Courses</a>
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
                    <span class="eyebrow">Course Library</span>
                    <h1>Courses</h1>
                    <p>Browse available learning paths and continue your enrolled courses.</p>
                </div>
                <?php if ($role === 'admin' || $role === 'instructor'): ?>
                    <a class="btn primary" href="manage_courses.php"><i data-lucide="plus"></i> New Course</a>
                <?php endif; ?>
            </section>

            <form class="search-panel" method="get" action="courses.php">
                <div class="field">
                    <input type="search" name="search" placeholder="Search courses" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <select name="category">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $row): ?>
                            <option value="<?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?>" <?php echo $category === $row['category'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn primary" type="submit"><i data-lucide="search"></i> Search</button>
            </form>

            <?php if (count($courses) > 0): ?>
                <section class="course-grid">
                    <?php foreach ($courses as $course): ?>
                        <article class="course-card">
                            <img src="<?php echo htmlspecialchars($course['cover_image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="course-card-body">
                                <div class="tag-row">
                                    <span class="tag"><?php echo htmlspecialchars($course['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="tag <?php echo htmlspecialchars($course['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($course['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars(substr($course['description'], 0, 130), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                <div class="meta-row">
                                    <span class="tag"><?php echo htmlspecialchars($course['level'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="tag"><?php echo htmlspecialchars($course['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="tag success">Free</span>
                                </div>
                                <p><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php if ($role === 'student' && $course['enrollment_id']): ?>
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
                                <?php elseif ($role === 'student'): ?>
                                    <form method="post" action="enroll.php">
                                        <input type="hidden" name="course_id" value="<?php echo (int) $course['id']; ?>">
                                        <button class="btn primary" type="submit"><i data-lucide="badge-plus"></i> Enroll Now</button>
                                    </form>
                                <?php else: ?>
                                    <a class="btn primary" href="course.php?id=<?php echo (int) $course['id']; ?>"><i data-lucide="external-link"></i> Open Course</a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <div class="empty-state">
                    <p>No courses match your current filters.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
