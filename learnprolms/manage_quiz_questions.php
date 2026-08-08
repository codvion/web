<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'instructor') {
    header('Location: dashboard.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$quiz_id = (int) ($_POST['quiz_id'] ?? ($_GET['quiz_id'] ?? 0));
$quiz = null;
$questions = [];
$error = '';
$success = '';
$max_quiz_questions = 10;
$seconds_per_question = 90;

if ($quiz_id <= 0) {
    header('Location: manage_quizzes.php');
    exit();
}

if ($role === 'admin') {
    $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, c.duration AS course_duration, c.status AS course_status, u.full_name AS instructor_name, u.email AS instructor_email, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id WHERE q.id = ?');
    $stmt->execute([$quiz_id]);
} else {
    $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, c.duration AS course_duration, c.status AS course_status, u.full_name AS instructor_name, u.email AS instructor_email, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id WHERE q.id = ? AND c.instructor_id = ?');
    $stmt->execute([$quiz_id, $user_id]);
}

if ($stmt->rowCount() === 0) {
    header('Location: manage_quizzes.php');
    exit();
}

$quiz = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_question_set') {
        $posted_question_texts = $_POST['batch_question_text'] ?? [];
        $posted_option_a = $_POST['batch_option_a'] ?? [];
        $posted_option_b = $_POST['batch_option_b'] ?? [];
        $posted_option_c = $_POST['batch_option_c'] ?? [];
        $posted_option_d = $_POST['batch_option_d'] ?? [];
        $posted_correct_option = $_POST['batch_correct_option'] ?? [];
        $posted_marks = $_POST['batch_marks'] ?? [];

        if (!is_array($posted_question_texts)) {
            $posted_question_texts = [];
        }
        if (!is_array($posted_option_a)) {
            $posted_option_a = [];
        }
        if (!is_array($posted_option_b)) {
            $posted_option_b = [];
        }
        if (!is_array($posted_option_c)) {
            $posted_option_c = [];
        }
        if (!is_array($posted_option_d)) {
            $posted_option_d = [];
        }
        if (!is_array($posted_correct_option)) {
            $posted_correct_option = [];
        }
        if (!is_array($posted_marks)) {
            $posted_marks = [];
        }

        $question_rows_to_save = [];
        $question_rows_total_marks = 0;

        for ($question_index = 0; $question_index < $max_quiz_questions; $question_index++) {
            $row_question_text = trim((string) ($posted_question_texts[$question_index] ?? ''));
            $row_option_a = trim((string) ($posted_option_a[$question_index] ?? ''));
            $row_option_b = trim((string) ($posted_option_b[$question_index] ?? ''));
            $row_option_c = trim((string) ($posted_option_c[$question_index] ?? ''));
            $row_option_d = trim((string) ($posted_option_d[$question_index] ?? ''));
            $row_correct_option = (string) ($posted_correct_option[$question_index] ?? 'A');
            $row_marks = trim((string) ($posted_marks[$question_index] ?? '1'));
            $row_has_content = $row_question_text !== '' || $row_option_a !== '' || $row_option_b !== '' || $row_option_c !== '' || $row_option_d !== '';

            if ($row_has_content) {
                if ($row_question_text === '' || $row_option_a === '' || $row_option_b === '' || $row_option_c === '' || $row_option_d === '') {
                    $error = 'Complete every field for question ' . ($question_index + 1) . ' or remove that question.';
                } elseif ($row_correct_option !== 'A' && $row_correct_option !== 'B' && $row_correct_option !== 'C' && $row_correct_option !== 'D') {
                    $error = 'Select a valid correct option for question ' . ($question_index + 1) . '.';
                } elseif (!ctype_digit($row_marks) || (int) $row_marks < 1) {
                    $error = 'Marks must be a positive number for question ' . ($question_index + 1) . '.';
                }

                if ($error === '') {
                    $question_rows_to_save[] = [
                        'question_text' => $row_question_text,
                        'option_a' => $row_option_a,
                        'option_b' => $row_option_b,
                        'option_c' => $row_option_c,
                        'option_d' => $row_option_d,
                        'correct_option' => $row_correct_option,
                        'marks' => (int) $row_marks
                    ];
                    $question_rows_total_marks += (int) $row_marks;
                }
            }

            if ($error !== '') {
                break;
            }
        }

        if ($error === '' && count($question_rows_to_save) === 0) {
            $error = 'Please add at least one complete question before saving.';
        }

        if ($error === '' && count($question_rows_to_save) > $max_quiz_questions) {
            $error = 'A quiz can have a maximum of 10 questions.';
        }

        if ($error === '') {
            try {
                $conn->beginTransaction();

                $stmt = $conn->prepare('DELETE FROM quiz_questions WHERE quiz_id = ?');
                $stmt->execute([$quiz_id]);

                $saved_question_order = 1;
                foreach ($question_rows_to_save as $question_row) {
                    $stmt = $conn->prepare('INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, marks, question_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$quiz_id, $question_row['question_text'], $question_row['option_a'], $question_row['option_b'], $question_row['option_c'], $question_row['option_d'], $question_row['correct_option'], (int) $question_row['marks'], $saved_question_order]);
                    $saved_question_order++;
                }

                $question_rows_total_duration = count($question_rows_to_save) * $seconds_per_question;
                $stmt = $conn->prepare('UPDATE quizzes SET total_marks = ?, duration_seconds = ? WHERE id = ?');
                $stmt->execute([$question_rows_total_marks, $question_rows_total_duration, $quiz_id]);

                $conn->commit();
                $success = 'Question set saved successfully. ' . count($question_rows_to_save) . ' questions are ready.';
            } catch (PDOException $e) {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                $error = 'Question set could not be saved. Please try again.';
            }
        }
    }
}

if ($role === 'admin') {
    $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, c.duration AS course_duration, c.status AS course_status, u.full_name AS instructor_name, u.email AS instructor_email, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id WHERE q.id = ?');
    $stmt->execute([$quiz_id]);
} else {
    $stmt = $conn->prepare('SELECT q.*, c.id AS course_id, c.title AS course_title, c.category, c.level, c.duration AS course_duration, c.status AS course_status, u.full_name AS instructor_name, u.email AS instructor_email, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q INNER JOIN courses c ON q.course_id = c.id INNER JOIN login_system u ON c.instructor_id = u.id WHERE q.id = ? AND c.instructor_id = ?');
    $stmt->execute([$quiz_id, $user_id]);
}
$quiz = $stmt->fetch();

$stmt = $conn->prepare('SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_order ASC, id ASC');
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
$quiz_total_duration_seconds = count($questions) * $seconds_per_question;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Quiz Questions | LearnPro LMS</title>
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
                <a class="active" href="manage_quizzes.php"><i data-lucide="file-question"></i> Manage Quizzes</a>
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
                    <span class="eyebrow">Quiz Questions</span>
                    <h1><?php echo htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p><?php echo htmlspecialchars($quiz['instructions'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="actions">
                    <a class="btn" href="manage_quizzes.php?course_id=<?php echo (int) $quiz['course_id']; ?>"><i data-lucide="arrow-left"></i> Manage Quizzes</a>
                    <a class="btn primary" href="quiz.php?id=<?php echo (int) $quiz_id; ?>"><i data-lucide="eye"></i> Preview Quiz</a>
                </div>
            </section>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <section class="panel quiz-detail-hero">
                <div class="quiz-detail-copy">
                    <span class="quiz-detail-kicker">Selected Quiz</span>
                    <h2><?php echo htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p><?php echo htmlspecialchars($quiz['instructions'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <div class="tag-row">
                        <span class="tag <?php echo htmlspecialchars($quiz['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($quiz['status'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="tag"><i data-lucide="timer"></i> <?php echo (int) $seconds_per_question; ?> sec / question</span>
                        <span class="tag"><?php echo (int) $quiz_total_duration_seconds; ?> sec total</span>
                        <span class="tag success"><?php echo (int) $quiz['question_count']; ?> Questions</span>
                    </div>
                </div>
                <div class="quiz-detail-grid">
                    <span>Course<strong><?php echo htmlspecialchars($quiz['course_title'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Instructor<strong><?php echo htmlspecialchars($quiz['instructor_name'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Instructor Email<strong><?php echo htmlspecialchars($quiz['instructor_email'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Category<strong><?php echo htmlspecialchars($quiz['category'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Level<strong><?php echo htmlspecialchars($quiz['level'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Course Duration<strong><?php echo htmlspecialchars($quiz['course_duration'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Start Time<strong><?php echo htmlspecialchars(date('M j, Y h:i A', strtotime($quiz['start_time'])), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>End Time<strong><?php echo htmlspecialchars(date('M j, Y h:i A', strtotime($quiz['end_time'])), ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    <span>Per Question<strong><?php echo (int) $seconds_per_question; ?> sec</strong></span>
                    <span>Total Duration<strong><?php echo (int) $quiz_total_duration_seconds; ?> sec</strong></span>
                    <span>Total Marks<strong><?php echo (int) $quiz['total_marks']; ?></strong></span>
                    <span>Course Status<strong><?php echo htmlspecialchars($quiz['course_status'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                </div>
            </section>

            <section class="panel question-set-panel" data-question-set-builder data-max-questions="<?php echo (int) $max_quiz_questions; ?>">
                <div class="section-head">
                    <div>
                        <h2>Question Set Builder</h2>
                        <p>Add up to 10 MCQs and save the complete quiz question set in one submit.</p>
                    </div>
                    <span class="tag success"><span data-question-count><?php echo count($questions); ?></span> / <?php echo (int) $max_quiz_questions; ?> Questions</span>
                </div>

                <form method="post" action="manage_quiz_questions.php?quiz_id=<?php echo (int) $quiz_id; ?>" class="question-set-form">
                    <input type="hidden" name="action" value="save_question_set">
                    <input type="hidden" name="quiz_id" value="<?php echo (int) $quiz_id; ?>">

                    <div class="question-set-toolbar">
                        <div>
                            <strong>Bulk Question Entry</strong>
                            <span>Empty hidden cards are ignored. Filled cards are saved in order.</span>
                        </div>
                        <button class="btn" type="button" data-add-question-row><i data-lucide="plus"></i> Add Question</button>
                    </div>

                    <div class="question-set-grid">
                        <?php for ($question_index = 0; $question_index < $max_quiz_questions; $question_index++): ?>
                            <?php
                            $question_row = $questions[$question_index] ?? null;
                            $question_is_visible = $question_row || ($question_index === 0 && count($questions) === 0);
                            $row_question_text = $question_row ? $question_row['question_text'] : '';
                            $row_option_a = $question_row ? $question_row['option_a'] : '';
                            $row_option_b = $question_row ? $question_row['option_b'] : '';
                            $row_option_c = $question_row ? $question_row['option_c'] : '';
                            $row_option_d = $question_row ? $question_row['option_d'] : '';
                            $row_correct_option = $question_row ? $question_row['correct_option'] : 'A';
                            $row_marks = $question_row ? (string) $question_row['marks'] : '1';
                            ?>
                            <article class="question-set-card <?php echo $question_is_visible ? '' : 'is-hidden'; ?>" data-question-card>
                                <div class="question-set-card-head">
                                    <span class="question-index">Q<?php echo (int) ($question_index + 1); ?></span>
                                    <button class="btn small danger" type="button" data-remove-question-row><i data-lucide="x"></i> Remove</button>
                                </div>

                                <div class="field full">
                                    <label>Question Text</label>
                                    <textarea name="batch_question_text[]" rows="3"><?php echo htmlspecialchars($row_question_text, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="question-options-grid">
                                    <div class="field">
                                        <label>Option A</label>
                                        <input type="text" name="batch_option_a[]" value="<?php echo htmlspecialchars($row_option_a, ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="field">
                                        <label>Option B</label>
                                        <input type="text" name="batch_option_b[]" value="<?php echo htmlspecialchars($row_option_b, ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="field">
                                        <label>Option C</label>
                                        <input type="text" name="batch_option_c[]" value="<?php echo htmlspecialchars($row_option_c, ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="field">
                                        <label>Option D</label>
                                        <input type="text" name="batch_option_d[]" value="<?php echo htmlspecialchars($row_option_d, ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                    <div class="field">
                                        <label>Correct Option</label>
                                        <select name="batch_correct_option[]">
                                            <option value="A" <?php echo $row_correct_option === 'A' ? 'selected' : ''; ?>>A</option>
                                            <option value="B" <?php echo $row_correct_option === 'B' ? 'selected' : ''; ?>>B</option>
                                            <option value="C" <?php echo $row_correct_option === 'C' ? 'selected' : ''; ?>>C</option>
                                            <option value="D" <?php echo $row_correct_option === 'D' ? 'selected' : ''; ?>>D</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Marks</label>
                                        <input type="number" min="1" name="batch_marks[]" value="<?php echo htmlspecialchars($row_marks, ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                            </article>
                        <?php endfor; ?>
                    </div>

                    <div class="form-actions">
                        <button class="btn primary" type="submit"><i data-lucide="save"></i> Save Question Set</button>
                        <a class="btn" href="manage_quizzes.php?course_id=<?php echo (int) $quiz['course_id']; ?>"><i data-lucide="arrow-left"></i> Back to Quizzes</a>
                    </div>
                </form>
            </section>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
