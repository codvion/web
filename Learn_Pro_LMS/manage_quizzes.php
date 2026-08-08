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
$error = '';
$success = '';
$courses = [];
$quizzes = [];
$selected_course = null;
$edit_quiz = null;
$selected_course_id = (int) ($_GET['course_id'] ?? 0);
$selected_quiz_id = (int) ($_GET['quiz_id'] ?? 0);
$seconds_per_question = 90;
$quiz_duration_seconds = 0;

$quiz_title = '';
$quiz_instructions = '';
$quiz_start_time = date('Y-m-d\TH:i');
$quiz_end_time = date('Y-m-d\TH:i', strtotime('+7 days'));
$quiz_total_marks = '10';
$quiz_status = 'published';

if ($role === 'admin') {
    $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id ORDER BY c.title ASC');
    $stmt->execute();
} else {
    $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE c.instructor_id = ? ORDER BY c.title ASC');
    $stmt->execute([$user_id]);
}
$courses = $stmt->fetchAll();

if ($selected_course_id === 0 && count($courses) > 0) {
    $selected_course_id = (int) $courses[0]['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $selected_course_id = (int) ($_POST['course_id'] ?? 0);
    $selected_quiz_id = (int) ($_POST['quiz_id'] ?? 0);

    $stmt = $conn->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->execute([$selected_course_id]);

    if ($stmt->rowCount() === 0) {
        $error = 'Selected course was not found.';
    } else {
        $selected_course = $stmt->fetch();
        if ($role === 'instructor' && (int) $selected_course['instructor_id'] !== $user_id) {
            $error = 'You can only manage quizzes for your own courses.';
        }
    }

    if ($action === 'create_quiz' || $action === 'update_quiz') {
        $quiz_title = trim($_POST['title'] ?? '');
        $quiz_instructions = trim($_POST['instructions'] ?? '');
        $quiz_start_time = trim($_POST['start_time'] ?? '');
        $quiz_end_time = trim($_POST['end_time'] ?? '');
        $quiz_total_marks = trim($_POST['total_marks'] ?? '10');
        $quiz_status = $_POST['status'] ?? 'published';

        if ($quiz_status !== 'draft' && $quiz_status !== 'published') {
            $quiz_status = 'published';
        }

        $start_timestamp = strtotime($quiz_start_time);
        $end_timestamp = strtotime($quiz_end_time);

        if ($error === '') {
            if ($quiz_title === '' || $quiz_instructions === '' || $quiz_start_time === '' || $quiz_end_time === '') {
                $error = 'Please complete all required quiz fields.';
            } elseif ($start_timestamp === false || $end_timestamp === false) {
                $error = 'Please select valid quiz start and end times.';
            } elseif ($end_timestamp <= $start_timestamp) {
                $error = 'Quiz end time must be after the start time.';
            } elseif (!ctype_digit($quiz_total_marks) || (int) $quiz_total_marks < 1) {
                $error = 'Total marks must be a positive number.';
            }
        }

        if ($error === '' && $action === 'create_quiz') {
            $stmt = $conn->prepare('INSERT INTO quizzes (course_id, title, instructions, start_time, end_time, duration_seconds, total_marks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$selected_course_id, $quiz_title, $quiz_instructions, date('Y-m-d H:i:s', $start_timestamp), date('Y-m-d H:i:s', $end_timestamp), $quiz_duration_seconds, (int) $quiz_total_marks, $quiz_status]);
            $selected_quiz_id = (int) $conn->lastInsertId();

            $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
            $stmt->execute([(int) $selected_course['instructor_id'], 'Quiz Created', 'A course quiz was created for "' . $selected_course['title'] . '".', 'success']);

            $success = 'Quiz created successfully. Click Questions to build the question set.';
            $quiz_title = '';
            $quiz_instructions = '';
            $quiz_start_time = date('Y-m-d\TH:i');
            $quiz_end_time = date('Y-m-d\TH:i', strtotime('+7 days'));
            $quiz_total_marks = '10';
            $quiz_status = 'published';
        }

        if ($error === '' && $action === 'update_quiz') {
            $stmt = $conn->prepare('SELECT q.* FROM quizzes q INNER JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND q.course_id = ?');
            $stmt->execute([$selected_quiz_id, $selected_course_id]);

            if ($stmt->rowCount() === 0) {
                $error = 'Quiz not found for this course.';
            } else {
                $existing_quiz = $stmt->fetch();
                if ($role === 'instructor') {
                    $stmt = $conn->prepare('SELECT id FROM courses WHERE id = ? AND instructor_id = ?');
                    $stmt->execute([(int) $existing_quiz['course_id'], $user_id]);
                    if ($stmt->rowCount() === 0) {
                        $error = 'You can only update quizzes for your own courses.';
                    }
                }
            }

            if ($error === '') {
                $stmt = $conn->prepare('UPDATE quizzes SET title = ?, instructions = ?, start_time = ?, end_time = ?, total_marks = ?, status = ? WHERE id = ? AND course_id = ?');
                $stmt->execute([$quiz_title, $quiz_instructions, date('Y-m-d H:i:s', $start_timestamp), date('Y-m-d H:i:s', $end_timestamp), (int) $quiz_total_marks, $quiz_status, $selected_quiz_id, $selected_course_id]);

                $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
                $stmt->execute([(int) $selected_course['instructor_id'], 'Quiz Updated', 'The quiz "' . $quiz_title . '" has been updated.', 'info']);

                $success = 'Quiz updated successfully.';
            }
        }
    }

    if ($action === 'delete_quiz') {
        $stmt = $conn->prepare('SELECT q.* FROM quizzes q INNER JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND q.course_id = ?');
        $stmt->execute([$selected_quiz_id, $selected_course_id]);

        if ($stmt->rowCount() === 0) {
            $error = 'Quiz not found for this course.';
        }

        if ($error === '') {
            $stmt = $conn->prepare('DELETE FROM quizzes WHERE id = ? AND course_id = ?');
            $stmt->execute([$selected_quiz_id, $selected_course_id]);
            $selected_quiz_id = 0;
            $success = 'Quiz deleted successfully.';
        }
    }

}

if ($selected_course_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->execute([$selected_course_id]);
    if ($stmt->rowCount() > 0) {
        $selected_course = $stmt->fetch();
        if ($role === 'instructor' && (int) $selected_course['instructor_id'] !== $user_id) {
            $selected_course = null;
            $selected_course_id = 0;
        }
    }
}

if ($selected_course_id > 0) {
    $stmt = $conn->prepare('SELECT q.*, (SELECT COUNT(*) FROM quiz_questions qq WHERE qq.quiz_id = q.id) AS question_count FROM quizzes q WHERE q.course_id = ? ORDER BY q.created_at DESC, q.id DESC');
    $stmt->execute([$selected_course_id]);
    $quizzes = $stmt->fetchAll();
}

$edit_quiz_id = (int) ($_GET['edit'] ?? 0);
if ($edit_quiz_id > 0 && $selected_course_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM quizzes WHERE id = ? AND course_id = ?');
    $stmt->execute([$edit_quiz_id, $selected_course_id]);
    if ($stmt->rowCount() > 0) {
        $edit_quiz = $stmt->fetch();
        $selected_quiz_id = (int) $edit_quiz['id'];
        $quiz_title = $edit_quiz['title'];
        $quiz_instructions = $edit_quiz['instructions'];
        $quiz_start_time = date('Y-m-d\TH:i', strtotime($edit_quiz['start_time']));
        $quiz_end_time = date('Y-m-d\TH:i', strtotime($edit_quiz['end_time']));
        $quiz_total_marks = (string) $edit_quiz['total_marks'];
        $quiz_status = $edit_quiz['status'];
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Manage Quizzes | LearnPro LMS</title>
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
                    <span class="eyebrow">Quiz Builder</span>
                    <h1>Manage Quizzes</h1>
                    <p>Create course-wise quizzes, schedule availability, and build MCQ question banks.</p>
                </div>
                <div class="actions">
                    <a class="btn" href="quizzes.php"><i data-lucide="clipboard-list"></i> View Quizzes</a>
                    <a class="btn" href="manage_courses.php"><i data-lucide="folder-kanban"></i> Courses</a>
                </div>
            </section>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if (count($courses) === 0): ?>
                <div class="empty-state">
                    <p>Create a course before adding quizzes.</p>
                    <a class="btn primary" href="manage_courses.php"><i data-lucide="plus"></i> Create Course</a>
                </div>
            <?php else: ?>
                <section class="panel slim">
                    <form method="get" action="manage_quizzes.php" class="search-panel">
                        <div class="field">
                            <select name="course_id">
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo (int) $course['id']; ?>" <?php echo $selected_course_id === (int) $course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?> - <?php echo htmlspecialchars($course['instructor_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn primary" type="submit"><i data-lucide="arrow-right"></i> Open Course</button>
                        <a class="btn" href="course.php?id=<?php echo (int) $selected_course_id; ?>"><i data-lucide="external-link"></i> Preview Course</a>
                    </form>
                </section>

                <section class="panel quiz-main-form-panel">
                    <div class="section-head">
                        <div>
                            <h2><?php echo $edit_quiz ? 'Update Quiz' : 'Create Quiz'; ?></h2>
                            <p>Timer is calculated after questions are saved: 90 seconds per question.</p>
                        </div>
                        <span class="quiz-duration-badge"><i data-lucide="timer"></i> 90 sec / question</span>
                    </div>

                    <form method="post" action="manage_quizzes.php?course_id=<?php echo (int) $selected_course_id; ?><?php echo $edit_quiz ? '&edit=' . (int) $edit_quiz['id'] : ''; ?>" class="form-grid">
                        <input type="hidden" name="action" value="<?php echo $edit_quiz ? 'update_quiz' : 'create_quiz'; ?>">
                        <input type="hidden" name="course_id" value="<?php echo (int) $selected_course_id; ?>">
                        <?php if ($edit_quiz): ?>
                            <input type="hidden" name="quiz_id" value="<?php echo (int) $edit_quiz['id']; ?>">
                        <?php endif; ?>

                        <div class="field full">
                            <label for="title">Quiz Title</label>
                            <input id="title" type="text" name="title" value="<?php echo htmlspecialchars($quiz_title, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="start_time">Start Time</label>
                            <input id="start_time" type="datetime-local" name="start_time" value="<?php echo htmlspecialchars($quiz_start_time, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="end_time">End Time</label>
                            <input id="end_time" type="datetime-local" name="end_time" value="<?php echo htmlspecialchars($quiz_end_time, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="total_marks">Total Marks</label>
                            <input id="total_marks" type="number" min="1" name="total_marks" value="<?php echo htmlspecialchars($quiz_total_marks, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="published" <?php echo $quiz_status === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $quiz_status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <div class="field full">
                            <label for="instructions">Instructions</label>
                            <textarea id="instructions" name="instructions" required><?php echo htmlspecialchars($quiz_instructions, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="field full">
                            <div class="form-actions">
                                <button class="btn primary" type="submit"><i data-lucide="<?php echo $edit_quiz ? 'save' : 'plus'; ?>"></i> <?php echo $edit_quiz ? 'Update Quiz' : 'Create Quiz'; ?></button>
                                <?php if ($edit_quiz): ?>
                                    <a class="btn" href="manage_quizzes.php?course_id=<?php echo (int) $selected_course_id; ?>&quiz_id=<?php echo (int) $selected_quiz_id; ?>"><i data-lucide="x"></i> Cancel Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="panel">
                    <div class="section-head">
                        <div>
                            <h2>Quizzes for <?php echo htmlspecialchars($selected_course['title'] ?? 'Selected Course', ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p>Admin can manage all courses. Instructors can manage only their own course quizzes.</p>
                        </div>
                    </div>

                    <?php if (count($quizzes) > 0): ?>
                        <div class="table-shell">
                            <div class="table-scroll">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Quiz</th>
                                            <th>Window</th>
                                            <th>Duration</th>
                                            <th>Marks</th>
                                            <th>Status</th>
                                            <th>Questions</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quizzes as $quiz): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br><span><?php echo htmlspecialchars(substr($quiz['instructions'], 0, 80), ENT_QUOTES, 'UTF-8'); ?>...</span></td>
                                                <td><?php echo htmlspecialchars(date('M j, Y h:i A', strtotime($quiz['start_time'])), ENT_QUOTES, 'UTF-8'); ?><br><span><?php echo htmlspecialchars(date('M j, Y h:i A', strtotime($quiz['end_time'])), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><span class="tag"><i data-lucide="timer"></i> <?php echo (int) ((int) $quiz['question_count'] * $seconds_per_question); ?> sec</span></td>
                                                <td><?php echo (int) $quiz['total_marks']; ?></td>
                                                <td><span class="tag <?php echo htmlspecialchars($quiz['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($quiz['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><?php echo (int) $quiz['question_count']; ?></td>
                                                <td>
                                                    <div class="table-actions">
                                                        <a class="btn small" href="quiz.php?id=<?php echo (int) $quiz['id']; ?>"><i data-lucide="eye"></i> Preview</a>
                                                        <a class="btn small primary" href="manage_quiz_questions.php?quiz_id=<?php echo (int) $quiz['id']; ?>"><i data-lucide="list-checks"></i> Questions</a>
                                                        <a class="btn small warning" href="manage_quizzes.php?course_id=<?php echo (int) $selected_course_id; ?>&edit=<?php echo (int) $quiz['id']; ?>"><i data-lucide="pencil"></i> Edit</a>
                                                        <form class="inline-form" method="post" action="manage_quizzes.php?course_id=<?php echo (int) $selected_course_id; ?>" onsubmit="return confirm('Delete this quiz, questions, and attempts?');">
                                                            <input type="hidden" name="action" value="delete_quiz">
                                                            <input type="hidden" name="course_id" value="<?php echo (int) $selected_course_id; ?>">
                                                            <input type="hidden" name="quiz_id" value="<?php echo (int) $quiz['id']; ?>">
                                                            <button class="btn small danger" type="submit"><i data-lucide="trash-2"></i> Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No quizzes have been created for this course.</p>
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
