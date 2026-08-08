<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$course_id = (int) ($_GET['course_id'] ?? 0);
$target_user_id = (int) ($_GET['user_id'] ?? $user_id);

if ($role === 'student') {
    $target_user_id = $user_id;
    $stmt = $conn->prepare('SELECT e.*, c.title AS course_title, c.category, c.duration, c.level, u.full_name AS instructor_name, s.full_name AS student_name FROM enrollments e INNER JOIN courses c ON e.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id INNER JOIN login_system s ON e.user_id = s.id WHERE e.user_id = ? AND e.course_id = ?');
    $stmt->execute([$user_id, $course_id]);
} elseif ($role === 'instructor') {
    $stmt = $conn->prepare('SELECT e.*, c.title AS course_title, c.category, c.duration, c.level, u.full_name AS instructor_name, s.full_name AS student_name FROM enrollments e INNER JOIN courses c ON e.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id INNER JOIN login_system s ON e.user_id = s.id WHERE e.user_id = ? AND e.course_id = ? AND c.instructor_id = ?');
    $stmt->execute([$target_user_id, $course_id, $user_id]);
} else {
    $stmt = $conn->prepare('SELECT e.*, c.title AS course_title, c.category, c.duration, c.level, u.full_name AS instructor_name, s.full_name AS student_name FROM enrollments e INNER JOIN courses c ON e.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id INNER JOIN login_system s ON e.user_id = s.id WHERE e.user_id = ? AND e.course_id = ?');
    $stmt->execute([$target_user_id, $course_id]);
}

if ($stmt->rowCount() === 0) {
    header('Location: courses.php');
    exit();
}

$certificate = $stmt->fetch();

if ($certificate['status'] !== 'completed' && (int) $certificate['progress_percent'] < 100) {
    header('Location: course.php?id=' . $course_id);
    exit();
}

$completed_date = $certificate['completed_at'] ?? date('Y-m-d H:i:s');
$certificate_id = 'LP-' . str_pad((string) $certificate['user_id'], 4, '0', STR_PAD_LEFT) . '-' . str_pad((string) $course_id, 4, '0', STR_PAD_LEFT);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Certificate | LearnPro LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'partials/learnpro-header.php'; ?>

    <main class="certificate-shell">
        <section class="certificate-card">
            <div class="certificate-logo">
                <span class="brand-mark">LP</span>
                <strong>LearnPro LMS</strong>
            </div>
            <span class="studio-eyebrow">Certificate of Completion</span>
            <h1><?php echo htmlspecialchars($certificate['student_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>has successfully completed</p>
            <h2><?php echo htmlspecialchars($certificate['course_title'], ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="certificate-grid">
                <span>Instructor<br><strong><?php echo htmlspecialchars($certificate['instructor_name'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <span>Completed<br><strong><?php echo htmlspecialchars(date('F j, Y', strtotime($completed_date)), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                <span>Certificate ID<br><strong><?php echo htmlspecialchars($certificate_id, ENT_QUOTES, 'UTF-8'); ?></strong></span>
            </div>
            <div class="certificate-line"></div>
            <p class="certificate-note">Awarded for completing all required course topics and video progress milestones.</p>
        </section>

        <div class="certificate-actions">
            <a class="btn" href="certificates.php"><i data-lucide="award"></i> Certificates</a>
            <a class="btn" href="courses.php"><i data-lucide="book-open"></i> Courses</a>
            <button class="btn primary" type="button" onclick="window.print();"><i data-lucide="printer"></i> Print Certificate</button>
        </div>
    </main>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
