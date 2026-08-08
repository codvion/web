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
$lessons = [];
$selected_course = null;
$selected_course_id = (int) ($_GET['course_id'] ?? 0);
$edit_lesson = null;

$title = '';
$lesson_order = '1';
$video_url = '';
$duration_minutes = '10';
$content = '';
$status = 'published';

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

    $stmt = $conn->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->execute([$selected_course_id]);

    if ($stmt->rowCount() === 0) {
        $error = 'Selected course was not found.';
    } else {
        $selected_course = $stmt->fetch();
        if ($role === 'instructor' && (int) $selected_course['instructor_id'] !== $user_id) {
            $error = 'You can only manage lessons for your own courses.';
        }
    }

    if ($action === 'create' || $action === 'update') {
        $title = trim($_POST['title'] ?? '');
        $lesson_order = trim($_POST['lesson_order'] ?? '1');
        $video_url = trim($_POST['video_url'] ?? '');
        $duration_minutes = trim($_POST['duration_minutes'] ?? '10');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'published';

        if ($status !== 'draft' && $status !== 'published') {
            $status = 'published';
        }

        if ($error === '') {
            if ($title === '' || $video_url === '' || $content === '') {
                $error = 'Please complete all required lesson fields.';
            } elseif (!ctype_digit($lesson_order) || (int) $lesson_order < 1) {
                $error = 'Lesson order must be a positive number.';
            } elseif (!ctype_digit($duration_minutes) || (int) $duration_minutes < 1) {
                $error = 'Duration must be a positive number of minutes.';
            }
        }

        if ($error === '' && $action === 'create') {
            $stmt = $conn->prepare('INSERT INTO lessons (course_id, title, lesson_order, video_url, duration_minutes, content, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$selected_course_id, $title, (int) $lesson_order, $video_url, (int) $duration_minutes, $content, $status]);

            $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
            $stmt->execute([(int) $selected_course['instructor_id'], 'Lesson Added', 'A lesson was added to "' . $selected_course['title'] . '".', 'success']);

            $success = 'Lesson created successfully.';
            $title = '';
            $lesson_order = '1';
            $video_url = '';
            $duration_minutes = '10';
            $content = '';
            $status = 'published';
        }

        if ($error === '' && $action === 'update') {
            $lesson_id = (int) ($_POST['lesson_id'] ?? 0);
            $stmt = $conn->prepare('SELECT * FROM lessons WHERE id = ? AND course_id = ?');
            $stmt->execute([$lesson_id, $selected_course_id]);

            if ($stmt->rowCount() === 0) {
                $error = 'Lesson not found for this course.';
            }

            if ($error === '') {
                $stmt = $conn->prepare('UPDATE lessons SET title = ?, lesson_order = ?, video_url = ?, duration_minutes = ?, content = ?, status = ? WHERE id = ? AND course_id = ?');
                $stmt->execute([$title, (int) $lesson_order, $video_url, (int) $duration_minutes, $content, $status, $lesson_id, $selected_course_id]);

                $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
                $stmt->execute([(int) $selected_course['instructor_id'], 'Lesson Updated', 'The lesson "' . $title . '" has been updated.', 'info']);

                $success = 'Lesson updated successfully.';
            }
        }
    }

    if ($action === 'delete') {
        $lesson_id = (int) ($_POST['lesson_id'] ?? 0);
        if ($error === '') {
            $stmt = $conn->prepare('SELECT * FROM lessons WHERE id = ? AND course_id = ?');
            $stmt->execute([$lesson_id, $selected_course_id]);

            if ($stmt->rowCount() === 0) {
                $error = 'Lesson not found for this course.';
            }
        }

        if ($error === '') {
            $stmt = $conn->prepare('DELETE FROM lessons WHERE id = ? AND course_id = ?');
            $stmt->execute([$lesson_id, $selected_course_id]);
            $success = 'Lesson deleted successfully.';
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

$edit_id = (int) ($_GET['edit'] ?? 0);

if ($edit_id > 0 && $selected_course_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM lessons WHERE id = ? AND course_id = ?');
    $stmt->execute([$edit_id, $selected_course_id]);
    if ($stmt->rowCount() > 0) {
        $edit_lesson = $stmt->fetch();
        $title = $edit_lesson['title'];
        $lesson_order = $edit_lesson['lesson_order'];
        $video_url = $edit_lesson['video_url'];
        $duration_minutes = $edit_lesson['duration_minutes'];
        $content = $edit_lesson['content'];
        $status = $edit_lesson['status'];
    }
}

if ($selected_course_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM lessons WHERE course_id = ? ORDER BY lesson_order ASC, id ASC');
    $stmt->execute([$selected_course_id]);
    $lessons = $stmt->fetchAll();
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Manage Lessons | LearnPro LMS</title>
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
                <a class="active" href="manage_lessons.php"><i data-lucide="list-video"></i> Manage Lessons</a>
                <a href="manage_quizzes.php"><i data-lucide="file-question"></i> Manage Quizzes</a>
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
                    <span class="eyebrow">Lesson Builder</span>
                    <h1>Manage Lessons</h1>
                    <p>Add ordered video lessons and control what students can watch inside each course.</p>
                </div>
                <a class="btn" href="manage_courses.php"><i data-lucide="folder-kanban"></i> Courses</a>
                <a class="btn" href="manage_quizzes.php?course_id=<?php echo (int) $selected_course_id; ?>"><i data-lucide="file-question"></i> Manage Quizzes</a>
            </section>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <?php if (count($courses) === 0): ?>
                <div class="empty-state">
                    <p>Create a course before adding lessons.</p>
                    <a class="btn primary" href="manage_courses.php"><i data-lucide="plus"></i> Create Course</a>
                </div>
            <?php else: ?>
                <section class="panel slim">
                    <form method="get" action="manage_lessons.php" class="search-panel">
                        <div class="field">
                            <select name="course_id">
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo (int) $course['id']; ?>" <?php echo $selected_course_id === (int) $course['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars($course['instructor_name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn primary" type="submit"><i data-lucide="arrow-right"></i> Open Course</button>
                        <a class="btn" href="course.php?id=<?php echo (int) $selected_course_id; ?>"><i data-lucide="external-link"></i> Preview</a>
                    </form>
                </section>

                <section class="panel">
                    <h2><?php echo $edit_lesson ? 'Update Lesson' : 'Create Lesson'; ?></h2>
                    <form method="post" action="manage_lessons.php?course_id=<?php echo (int) $selected_course_id; ?><?php echo $edit_lesson ? '&edit=' . (int) $edit_lesson['id'] : ''; ?>" class="form-grid">
                        <input type="hidden" name="action" value="<?php echo $edit_lesson ? 'update' : 'create'; ?>">
                        <input type="hidden" name="course_id" value="<?php echo (int) $selected_course_id; ?>">
                        <?php if ($edit_lesson): ?>
                            <input type="hidden" name="lesson_id" value="<?php echo (int) $edit_lesson['id']; ?>">
                        <?php endif; ?>

                        <div class="field full">
                            <label for="title">Lesson Title</label>
                            <input id="title" type="text" name="title" value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="lesson_order">Lesson Order</label>
                            <input id="lesson_order" type="number" min="1" name="lesson_order" value="<?php echo htmlspecialchars((string) $lesson_order, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="duration_minutes">Duration Minutes</label>
                            <input id="duration_minutes" type="number" min="1" name="duration_minutes" value="<?php echo htmlspecialchars((string) $duration_minutes, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="video_url">YouTube Video URL</label>
                            <input id="video_url" type="url" name="video_url" value="<?php echo htmlspecialchars($video_url, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://www.youtube.com/watch?v=VIDEO_ID" required>
                        </div>
                        <div class="field full">
                            <label for="content">Lesson Content</label>
                            <textarea id="content" name="content" required><?php echo htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="field full">
                            <div class="form-actions">
                                <button class="btn primary" type="submit"><i data-lucide="<?php echo $edit_lesson ? 'save' : 'plus'; ?>"></i> <?php echo $edit_lesson ? 'Update Lesson' : 'Create Lesson'; ?></button>
                                <?php if ($edit_lesson): ?>
                                    <a class="btn" href="manage_lessons.php?course_id=<?php echo (int) $selected_course_id; ?>"><i data-lucide="x"></i> Cancel Edit</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </section>

                <section class="panel">
                    <div class="section-head">
                        <div>
                            <h2>Lessons for <?php echo htmlspecialchars($selected_course['title'] ?? 'Selected Course', ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p>Students progress through lessons by order and unlock the next lesson after watching the required video time.</p>
                        </div>
                    </div>

                    <?php if (count($lessons) > 0): ?>
                        <div class="table-shell">
                            <div class="table-scroll">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Lesson</th>
                                            <th>Video</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lessons as $lesson): ?>
                                            <tr>
                                                <td><?php echo (int) $lesson['lesson_order']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($lesson['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br><?php echo htmlspecialchars(substr($lesson['content'], 0, 90), ENT_QUOTES, 'UTF-8'); ?>...</td>
                                                <td><?php echo htmlspecialchars(substr($lesson['video_url'], 0, 60), ENT_QUOTES, 'UTF-8'); ?>...</td>
                                                <td><?php echo (int) $lesson['duration_minutes']; ?> min</td>
                                                <td><span class="tag <?php echo htmlspecialchars($lesson['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lesson['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td>
                                                    <div class="table-actions">
                                                        <a class="btn small" href="course.php?id=<?php echo (int) $selected_course_id; ?>&lesson_id=<?php echo (int) $lesson['id']; ?>"><i data-lucide="play"></i> Open</a>
                                                        <a class="btn small warning" href="manage_lessons.php?course_id=<?php echo (int) $selected_course_id; ?>&edit=<?php echo (int) $lesson['id']; ?>"><i data-lucide="pencil"></i> Edit</a>
                                                        <form class="inline-form" method="post" action="manage_lessons.php?course_id=<?php echo (int) $selected_course_id; ?>" onsubmit="return confirm('Delete this lesson and its saved student progress?');">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="course_id" value="<?php echo (int) $selected_course_id; ?>">
                                                            <input type="hidden" name="lesson_id" value="<?php echo (int) $lesson['id']; ?>">
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
                            <p>No lessons have been created for this course.</p>
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
