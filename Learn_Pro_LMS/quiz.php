<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$quiz_id = (int) ($_GET['id'] ?? 0);
$quiz = null;
$questions = [];
$attempt = null;
$error = '';
$success = '';
$now_time = time();
$seconds_per_question = 90;
$quiz_duration_seconds = 0;
$quiz_remaining_seconds = 0;
$quiz_elapsed_seconds = 0;
$quiz_timer_active = false;
$quiz_session_key = '';

if ($role === 'student') {
    $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level FROM quizzes q INNER JOIN courses c ON q.course_id = c.id INNER JOIN enrollments e ON e.course_id = c.id AND e.user_id = ? WHERE q.id = ? AND q.status = ? AND c.status = ?');
    $stmt->execute([$user_id, $quiz_id, 'published', 'published']);
} elseif ($role === 'instructor') {
    $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level FROM quizzes q INNER JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND c.instructor_id = ?');
    $stmt->execute([$quiz_id, $user_id]);
} else {
    $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level FROM quizzes q INNER JOIN courses c ON q.course_id = c.id WHERE q.id = ?');
    $stmt->execute([$quiz_id]);
}

if ($stmt->rowCount() === 0) {
    header('Location: quizzes.php');
    exit();
}

$quiz = $stmt->fetch();
$quiz_start_time = strtotime($quiz['start_time']);
$quiz_end_time = strtotime($quiz['end_time']);
$quiz_session_key = 'quiz_started_' . $quiz_id . '_' . $user_id;
$quiz_state = 'closed';
$quiz_state_text = 'Closed';

if ($now_time < $quiz_start_time) {
    $quiz_state = 'upcoming';
    $quiz_state_text = 'Upcoming';
} elseif ($now_time >= $quiz_start_time && $now_time <= $quiz_end_time) {
    $quiz_state = 'open';
    $quiz_state_text = 'Open Now';
}

$stmt = $conn->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_order ASC, id ASC');
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
$quiz_duration_seconds = count($questions) * $seconds_per_question;

if ($role === 'student') {
    $stmt = $conn->prepare('SELECT * FROM quiz_attempts WHERE quiz_id = ? AND user_id = ?');
    $stmt->execute([$quiz_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        $attempt = $stmt->fetch();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'submit_quiz') {
        if ($role !== 'student') {
            $error = 'Only students can submit quiz attempts.';
        } elseif ($quiz_state !== 'open') {
            $error = 'This quiz is not open right now.';
        } elseif ($attempt) {
            $error = 'You have already submitted this quiz.';
        } elseif (count($questions) === 0) {
            $error = 'This quiz has no questions yet.';
        } else {
            $submit_time = time();
            $quiz_started_at = $submit_time;
            if (isset($_SESSION[$quiz_session_key])) {
                $quiz_started_at = (int) $_SESSION[$quiz_session_key];
            }
            if ($quiz_started_at < $quiz_start_time || $quiz_started_at > $submit_time) {
                $quiz_started_at = $submit_time;
            }
            $quiz_elapsed_on_submit = $submit_time - $quiz_started_at;
            if ($quiz_elapsed_on_submit < 0) {
                $quiz_elapsed_on_submit = 0;
            }

            $score = 0;
            $total_marks = 0;

            if ($quiz_elapsed_on_submit <= ($quiz_duration_seconds + 10)) {
                foreach ($questions as $question) {
                    $total_marks += (int) $question['marks'];
                    $answer_key = 'answer_' . (int) $question['id'];
                    $selected_answer = $_POST[$answer_key] ?? '';

                    if (($selected_answer === 'A' || $selected_answer === 'B' || $selected_answer === 'C' || $selected_answer === 'D') && $selected_answer === $question['correct_option']) {
                        $score += (int) $question['marks'];
                    }
                }
            } else {
                foreach ($questions as $question) {
                    $total_marks += (int) $question['marks'];
                }
            }

            $time_spent_seconds = $quiz_elapsed_on_submit;
            if ($time_spent_seconds > $quiz_duration_seconds) {
                $time_spent_seconds = $quiz_duration_seconds;
            }

            $stmt = $conn->prepare('INSERT INTO quiz_attempts (quiz_id, user_id, started_at, score, total_marks, time_spent_seconds) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$quiz_id, $user_id, date('Y-m-d H:i:s', $quiz_started_at), $score, $total_marks, $time_spent_seconds]);

            $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
            $stmt->execute([$user_id, 'Quiz Submitted', 'Your quiz "' . $quiz['title'] . '" has been submitted successfully.', 'success']);

            unset($_SESSION[$quiz_session_key]);

            if ($quiz_elapsed_on_submit > ($quiz_duration_seconds + 10)) {
                $success = 'Quiz time expired. Your attempt has been submitted automatically.';
            } else {
                $success = 'Quiz submitted successfully.';
            }

            $stmt = $conn->prepare('SELECT * FROM quiz_attempts WHERE quiz_id = ? AND user_id = ?');
            $stmt->execute([$quiz_id, $user_id]);
            $attempt = $stmt->fetch();
        }
    }
}

if ($role === 'student' && !$attempt && $quiz_state === 'open' && count($questions) > 0) {
    if (!isset($_SESSION[$quiz_session_key]) || (int) $_SESSION[$quiz_session_key] < $quiz_start_time || (int) $_SESSION[$quiz_session_key] > time()) {
        $_SESSION[$quiz_session_key] = time();
    }

    $quiz_elapsed_seconds = time() - (int) $_SESSION[$quiz_session_key];
    if ($quiz_elapsed_seconds < 0) {
        $quiz_elapsed_seconds = 0;
    }
    $quiz_remaining_seconds = $quiz_duration_seconds - $quiz_elapsed_seconds;
    if ($quiz_remaining_seconds < 0) {
        $quiz_remaining_seconds = 0;
    }
    $quiz_timer_active = true;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title><?php echo htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8'); ?> | LearnPro LMS</title>
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
                    <span class="eyebrow">Quiz</span>
                    <h1><?php echo htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p><?php echo htmlspecialchars($quiz['instructions'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="actions">
                    <a class="btn" href="quizzes.php"><i data-lucide="clipboard-list"></i> Quizzes</a>
                    <a class="btn" href="course.php?id=<?php echo (int) $quiz['course_id']; ?>"><i data-lucide="book-open"></i> Course</a>
                    <?php if ($role === 'admin' || $role === 'instructor'): ?>
                        <a class="btn primary" href="manage_quizzes.php?course_id=<?php echo (int) $quiz['course_id']; ?>&edit=<?php echo (int) $quiz_id; ?>"><i data-lucide="file-question"></i> Manage Quiz</a>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <section class="panel quiz-status-panel">
                <span class="tag <?php echo htmlspecialchars($quiz_state, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($quiz_state_text, ENT_QUOTES, 'UTF-8'); ?></span>
                <div class="quiz-meta">
                    <span>Course<strong><?php echo htmlspecialchars($quiz['course_title'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Level<strong><?php echo htmlspecialchars($quiz['level'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Start Time<strong><?php echo htmlspecialchars(date('M j, Y h:i A', $quiz_start_time), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>End Time<strong><?php echo htmlspecialchars(date('M j, Y h:i A', $quiz_end_time), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Per Question<strong><?php echo (int) $seconds_per_question; ?> sec</strong></span>
                    <span>Total Duration<strong><?php echo (int) $quiz_duration_seconds; ?> sec</strong></span>
                    <span>Total Marks<strong><?php echo (int) $quiz['total_marks']; ?></strong></span>
                    <span>Questions<strong><?php echo count($questions); ?></strong></span>
                </div>
            </section>

            <?php if ($quiz_timer_active): ?>
                <section class="panel quiz-timer-panel" data-quiz-countdown data-duration="<?php echo (int) $quiz_duration_seconds; ?>" data-remaining="<?php echo (int) $quiz_remaining_seconds; ?>">
                    <div>
                        <span class="eyebrow">Time Remaining</span>
                        <strong data-quiz-time-text><?php echo (int) $quiz_remaining_seconds; ?> sec</strong>
                    </div>
                    <div class="quiz-timer-track" aria-hidden="true">
                        <span data-quiz-time-bar style="width: <?php echo (int) floor(($quiz_remaining_seconds / $quiz_duration_seconds) * 100); ?>%;"></span>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($attempt): ?>
                <section class="panel quiz-result-card">
                    <span class="feature-icon"><i data-lucide="badge-check"></i></span>
                    <h2>Quiz Result</h2>
                    <p>You scored <strong><?php echo (int) $attempt['score']; ?></strong> out of <strong><?php echo (int) $attempt['total_marks']; ?></strong>.</p>
                    <p>Time spent: <strong><?php echo (int) ($attempt['time_spent_seconds'] ?? 0); ?> sec</strong> of <strong><?php echo (int) $quiz_duration_seconds; ?> sec</strong>.</p>
                    <span class="tag success">Submitted <?php echo htmlspecialchars(date('M j, Y h:i A', strtotime($attempt['submitted_at'])), ENT_QUOTES, 'UTF-8'); ?></span>
                </section>
            <?php elseif ($role === 'student' && $quiz_state === 'upcoming'): ?>
                <div class="empty-state">
                    <p>This quiz has not started yet.</p>
                </div>
            <?php elseif ($role === 'student' && $quiz_state === 'closed'): ?>
                <div class="empty-state">
                    <p>This quiz window is closed.</p>
                </div>
            <?php elseif (count($questions) === 0): ?>
                <div class="empty-state">
                    <p>No quiz questions have been added yet.</p>
                </div>
            <?php elseif ($role === 'student'): ?>
                <form method="post" action="quiz.php?id=<?php echo (int) $quiz_id; ?>" class="quiz-form" data-quiz-form>
                    <input type="hidden" name="action" value="submit_quiz">
                    <input type="hidden" name="auto_submit" value="0" data-quiz-auto-submit>
                    <?php foreach ($questions as $question): ?>
                        <article class="panel quiz-question-card">
                            <span class="tag">Question <?php echo (int) $question['question_order']; ?></span>
                            <h3><?php echo htmlspecialchars($question['question_text'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <div class="option-grid">
                                <label><input type="radio" name="answer_<?php echo (int) $question['id']; ?>" value="A" required> <span>A. <?php echo htmlspecialchars($question['option_a'], ENT_QUOTES, 'UTF-8'); ?></span></label>
                                <label><input type="radio" name="answer_<?php echo (int) $question['id']; ?>" value="B" required> <span>B. <?php echo htmlspecialchars($question['option_b'], ENT_QUOTES, 'UTF-8'); ?></span></label>
                                <label><input type="radio" name="answer_<?php echo (int) $question['id']; ?>" value="C" required> <span>C. <?php echo htmlspecialchars($question['option_c'], ENT_QUOTES, 'UTF-8'); ?></span></label>
                                <label><input type="radio" name="answer_<?php echo (int) $question['id']; ?>" value="D" required> <span>D. <?php echo htmlspecialchars($question['option_d'], ENT_QUOTES, 'UTF-8'); ?></span></label>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <button class="btn primary" type="submit"><i data-lucide="send"></i> Submit Quiz</button>
                </form>
            <?php else: ?>
                <?php foreach ($questions as $question): ?>
                    <article class="panel quiz-question-card">
                        <div class="quiz-card-head">
                            <span class="tag">Question <?php echo (int) $question['question_order']; ?></span>
                            <span class="tag success">Answer <?php echo htmlspecialchars($question['correct_option'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <h3><?php echo htmlspecialchars($question['question_text'], ENT_QUOTES, 'UTF-8'); ?></h3>
                        <div class="option-grid preview">
                            <span>A. <?php echo htmlspecialchars($question['option_a'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span>B. <?php echo htmlspecialchars($question['option_b'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span>C. <?php echo htmlspecialchars($question['option_c'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span>D. <?php echo htmlspecialchars($question['option_d'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
