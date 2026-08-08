<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$quizzes = [];
$now_time = time();
$seconds_per_question = 90;
$selected_course_id = (int) ($_GET['course_id'] ?? 0);
$selected_course = null;

if ($selected_course_id > 0) {
    if ($role === 'student') {
        $stmt = $conn->prepare('SELECT c.id, c.title FROM courses c INNER JOIN enrollments e ON e.course_id = c.id AND e.user_id = ? WHERE c.id = ? AND c.status = ?');
        $stmt->execute([$user_id, $selected_course_id, 'published']);
    } elseif ($role === 'instructor') {
        $stmt = $conn->prepare('SELECT id, title FROM courses WHERE id = ? AND instructor_id = ?');
        $stmt->execute([$selected_course_id, $user_id]);
    } else {
        $stmt = $conn->prepare('SELECT id, title FROM courses WHERE id = ?');
        $stmt->execute([$selected_course_id]);
    }

    if ($stmt->rowCount() > 0) {
        $selected_course = $stmt->fetch();
    } else {
        $selected_course_id = 0;
    }
}

if ($role === 'student') {
    if ($selected_course_id > 0) {
        $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, e.progress_percent, qa.id AS attempt_id, qa.score AS attempt_score, qa.total_marks AS attempt_total, qa.submitted_at AS attempted_at, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id INNER JOIN enrollments e ON e.course_id = c.id AND e.user_id = ? LEFT JOIN quiz_attempts qa ON qa.quiz_id = q.id AND qa.user_id = ? WHERE q.status = ? AND c.status = ? AND c.id = ? ORDER BY q.start_time ASC, c.title ASC');
        $stmt->execute([$user_id, $user_id, 'published', 'published', $selected_course_id]);
    } else {
        $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, e.progress_percent, qa.id AS attempt_id, qa.score AS attempt_score, qa.total_marks AS attempt_total, qa.submitted_at AS attempted_at, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id INNER JOIN enrollments e ON e.course_id = c.id AND e.user_id = ? LEFT JOIN quiz_attempts qa ON qa.quiz_id = q.id AND qa.user_id = ? WHERE q.status = ? AND c.status = ? ORDER BY q.start_time ASC, c.title ASC');
        $stmt->execute([$user_id, $user_id, 'published', 'published']);
    }
} elseif ($role === 'instructor') {
    if ($selected_course_id > 0) {
        $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, 0 AS progress_percent, NULL AS attempt_id, NULL AS attempt_score, NULL AS attempt_total, NULL AS attempted_at, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id WHERE c.instructor_id = ? AND c.id = ? ORDER BY q.start_time ASC, c.title ASC');
        $stmt->execute([$user_id, $selected_course_id]);
    } else {
        $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, 0 AS progress_percent, NULL AS attempt_id, NULL AS attempt_score, NULL AS attempt_total, NULL AS attempted_at, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id WHERE c.instructor_id = ? ORDER BY q.start_time ASC, c.title ASC');
        $stmt->execute([$user_id]);
    }
} else {
    if ($selected_course_id > 0) {
        $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, 0 AS progress_percent, NULL AS attempt_id, NULL AS attempt_score, NULL AS attempt_total, NULL AS attempted_at, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id WHERE c.id = ? ORDER BY q.start_time ASC, c.title ASC');
        $stmt->execute([$selected_course_id]);
    } else {
        $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, 0 AS progress_percent, NULL AS attempt_id, NULL AS attempt_score, NULL AS attempt_total, NULL AS attempted_at, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id ORDER BY q.start_time ASC, c.title ASC');
        $stmt->execute();
    }
}

$quizzes = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Quizzes | LearnPro LMS</title>
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
                <a class="active" href="quizzes.php"><i data-lucide="clipboard-list"></i> Quizzes</a>
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
                    <span class="eyebrow">Assessments</span>
                    <h1><?php echo $selected_course ? 'Course Quizzes' : 'Quizzes'; ?></h1>
                    <?php if ($selected_course): ?>
                        <p>Showing quizzes only for <?php echo htmlspecialchars($selected_course['title'], ENT_QUOTES, 'UTF-8'); ?>.</p>
                    <?php elseif ($role === 'student'): ?>
                        <p>View course quizzes for your enrolled courses, including start and end time.</p>
                    <?php elseif ($role === 'instructor'): ?>
                        <p>Review quiz windows attached to your courses.</p>
                    <?php else: ?>
                        <p>Review every course quiz window across the LMS.</p>
                    <?php endif; ?>
                </div>
                <div class="actions">
                    <?php if ($selected_course): ?>
                        <a class="btn" href="course.php?id=<?php echo (int) $selected_course_id; ?>"><i data-lucide="book-open"></i> Open Course</a>
                        <a class="btn" href="quizzes.php"><i data-lucide="clipboard-list"></i> All Quizzes</a>
                    <?php endif; ?>
                    <?php if ($role === 'admin' || $role === 'instructor'): ?>
                        <a class="btn primary" href="manage_quizzes.php<?php echo $selected_course_id > 0 ? '?course_id=' . (int) $selected_course_id : ''; ?>"><i data-lucide="file-question"></i> Manage Quizzes</a>
                    <?php elseif (!$selected_course): ?>
                        <a class="btn" href="courses.php"><i data-lucide="book-open"></i> Courses</a>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (count($quizzes) > 0): ?>
                <section class="quiz-grid">
                    <?php foreach ($quizzes as $quiz): ?>
                        <?php
                        $quiz_start_time = strtotime($quiz['start_time']);
                        $quiz_end_time = strtotime($quiz['end_time']);
                        $quiz_total_duration_seconds = (int) $quiz['question_count'] * $seconds_per_question;
                        $quiz_state = 'closed';
                        $quiz_state_text = 'Closed';
                        if ($now_time < $quiz_start_time) {
                            $quiz_state = 'upcoming';
                            $quiz_state_text = 'Upcoming';
                        } elseif ($now_time >= $quiz_start_time && $now_time <= $quiz_end_time) {
                            $quiz_state = 'open';
                            $quiz_state_text = 'Open Now';
                        }
                        ?>
                        <article class="panel quiz-card">
                            <div class="quiz-card-head">
                                <span class="feature-icon"><i data-lucide="clipboard-list"></i></span>
                                <span class="tag <?php echo htmlspecialchars($quiz_state, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($quiz_state_text, ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <h3><?php echo htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars($quiz['instructions'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="quiz-meta">
                                <span>Course<strong><?php echo htmlspecialchars($quiz['course_title'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                <span>Level<strong><?php echo htmlspecialchars($quiz['level'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                <span>Start Time<strong><?php echo htmlspecialchars(date('M j, Y h:i A', $quiz_start_time), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                <span>End Time<strong><?php echo htmlspecialchars(date('M j, Y h:i A', $quiz_end_time), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                                <span>Per Question<strong><?php echo (int) $seconds_per_question; ?> sec</strong></span>
                                <span>Total Duration<strong><?php echo (int) $quiz_total_duration_seconds; ?> sec</strong></span>
                                <span>Total Marks<strong><?php echo (int) $quiz['total_marks']; ?></strong></span>
                                <span>Questions<strong><?php echo (int) $quiz['question_count']; ?></strong></span>
                            </div>
                            <div class="form-actions">
                                <?php if ($role === 'student'): ?>
                                    <?php if ($quiz['attempt_id']): ?>
                                        <a class="btn primary" href="quiz.php?id=<?php echo (int) $quiz['id']; ?>"><i data-lucide="badge-check"></i> View Result</a>
                                    <?php elseif ($quiz_state === 'open' && (int) $quiz['question_count'] > 0): ?>
                                        <a class="btn primary" href="quiz.php?id=<?php echo (int) $quiz['id']; ?>"><i data-lucide="play-circle"></i> Start Quiz</a>
                                    <?php elseif ($quiz_state === 'upcoming'): ?>
                                        <span class="btn disabled"><i data-lucide="clock"></i> Starts Soon</span>
                                    <?php else: ?>
                                        <span class="btn disabled"><i data-lucide="lock"></i> Closed</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a class="btn primary" href="quiz.php?id=<?php echo (int) $quiz['id']; ?>"><i data-lucide="eye"></i> Preview Quiz</a>
                                    <a class="btn" href="manage_quizzes.php?course_id=<?php echo (int) $quiz['course_id']; ?>&edit=<?php echo (int) $quiz['id']; ?>"><i data-lucide="pencil"></i> Edit Quiz</a>
                                    <span class="tag"><?php echo (int) $quiz['question_count']; ?> Questions</span>
                                <?php endif; ?>
                                <a class="btn" href="course.php?id=<?php echo (int) $quiz['course_id']; ?>"><i data-lucide="external-link"></i> Open Course</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <div class="empty-state">
                    <?php if ($role === 'student'): ?>
                        <p><?php echo $selected_course ? 'No quizzes are attached to this course yet.' : 'No quizzes are attached to your enrolled courses yet.'; ?></p>
                        <a class="btn primary" href="<?php echo $selected_course ? 'course.php?id=' . (int) $selected_course_id : 'courses.php'; ?>"><i data-lucide="book-open"></i> <?php echo $selected_course ? 'Open Course' : 'Browse Courses'; ?></a>
                    <?php else: ?>
                        <p>No course quizzes have been scheduled yet.</p>
                        <a class="btn primary" href="manage_quizzes.php"><i data-lucide="plus"></i> Create Quiz</a>
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
