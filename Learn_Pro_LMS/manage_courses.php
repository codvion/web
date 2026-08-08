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
$instructors = [];
$edit_course = null;

$title = '';
$category = '';
$level = 'Beginner';
$duration = '';
$price = '0';
$cover_image = '';
$description = '';
$status = 'draft';
$instructor_id = $user_id;

if ($role === 'admin') {
    $stmt = $conn->prepare('SELECT id, full_name, email FROM login_system WHERE role = ? AND status = ? ORDER BY full_name ASC');
    $stmt->execute(['instructor', 'active']);
    $instructors = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $level = $_POST['level'] ?? 'Beginner';
        $duration = trim($_POST['duration'] ?? '');
        $price = '0';
        $cover_image = trim($_POST['cover_image'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        if ($level !== 'Beginner' && $level !== 'Intermediate' && $level !== 'Advanced') {
            $level = 'Beginner';
        }

        if ($status !== 'draft' && $status !== 'published' && $status !== 'archived') {
            $status = 'draft';
        }

        if ($cover_image === '') {
            $cover_image = 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1400&q=80';
        }

        if ($role === 'admin') {
            $instructor_id = (int) ($_POST['instructor_id'] ?? 0);
            $stmt = $conn->prepare('SELECT id FROM login_system WHERE id = ? AND role = ? AND status = ?');
            $stmt->execute([$instructor_id, 'instructor', 'active']);
            if ($stmt->rowCount() === 0) {
                $error = 'Please select a valid active instructor.';
            }
        } else {
            $instructor_id = $user_id;
        }

        if ($error === '') {
            if ($title === '' || $category === '' || $duration === '' || $description === '') {
                $error = 'Please complete all required course fields.';
            }
        }

        if ($error === '' && $action === 'create') {
            $stmt = $conn->prepare('INSERT INTO courses (title, category, level, duration, price, cover_image, description, status, instructor_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $category, $level, $duration, (float) $price, $cover_image, $description, $status, $instructor_id]);
            $new_course_id = $conn->lastInsertId();

            $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
            $stmt->execute([$instructor_id, 'Course Created', 'The course "' . $title . '" has been created successfully.', 'success']);

            $success = 'Course created successfully.';
            $title = '';
            $category = '';
            $level = 'Beginner';
            $duration = '';
            $price = '0';
            $cover_image = '';
            $description = '';
            $status = 'draft';
            $instructor_id = $user_id;
        }

        if ($error === '' && $action === 'update') {
            $course_id = (int) ($_POST['course_id'] ?? 0);
            $stmt = $conn->prepare('SELECT * FROM courses WHERE id = ?');
            $stmt->execute([$course_id]);

            if ($stmt->rowCount() === 0) {
                $error = 'Course not found.';
            } else {
                $existing_course = $stmt->fetch();

                if ($role === 'instructor' && (int) $existing_course['instructor_id'] !== $user_id) {
                    $error = 'You can only update your own courses.';
                }
            }

            if ($error === '') {
                $stmt = $conn->prepare('UPDATE courses SET title = ?, category = ?, level = ?, duration = ?, price = ?, cover_image = ?, description = ?, status = ?, instructor_id = ? WHERE id = ?');
                $stmt->execute([$title, $category, $level, $duration, (float) $price, $cover_image, $description, $status, $instructor_id, $course_id]);

                $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
                $stmt->execute([$instructor_id, 'Course Updated', 'The course "' . $title . '" has been updated.', 'info']);

                $success = 'Course updated successfully.';
            }
        }
    }

    if ($action === 'delete') {
        $course_id = (int) ($_POST['course_id'] ?? 0);

        $stmt = $conn->prepare('SELECT * FROM courses WHERE id = ?');
        $stmt->execute([$course_id]);

        if ($stmt->rowCount() === 0) {
            $error = 'Course not found.';
        } else {
            $existing_course = $stmt->fetch();

            if ($role === 'instructor' && (int) $existing_course['instructor_id'] !== $user_id) {
                $error = 'You can only delete your own courses.';
            }
        }

        if ($error === '') {
            $stmt = $conn->prepare('DELETE FROM courses WHERE id = ?');
            $stmt->execute([$course_id]);
            $success = 'Course deleted successfully.';
        }
    }
}

$edit_id = (int) ($_GET['edit'] ?? 0);

if ($edit_id > 0) {
    $stmt = $conn->prepare('SELECT * FROM courses WHERE id = ?');
    $stmt->execute([$edit_id]);
    if ($stmt->rowCount() > 0) {
        $edit_course = $stmt->fetch();
        if ($role === 'instructor' && (int) $edit_course['instructor_id'] !== $user_id) {
            $edit_course = null;
        }
    }

    if ($edit_course) {
        $title = $edit_course['title'];
        $category = $edit_course['category'];
        $level = $edit_course['level'];
        $duration = $edit_course['duration'];
        $price = '0';
        $cover_image = $edit_course['cover_image'];
        $description = $edit_course['description'];
        $status = $edit_course['status'];
        $instructor_id = (int) $edit_course['instructor_id'];
    }
}

if ($role === 'admin') {
    $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id ORDER BY c.created_at DESC');
    $stmt->execute();
} else {
    $stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE c.instructor_id = ? ORDER BY c.created_at DESC');
    $stmt->execute([$user_id]);
}
$courses = $stmt->fetchAll();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Manage Courses | LearnPro LMS</title>
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
                <a class="active" href="manage_courses.php"><i data-lucide="folder-kanban"></i> Manage Courses</a>
                <a href="manage_lessons.php"><i data-lucide="list-video"></i> Manage Lessons</a>
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
                    <span class="eyebrow">Instructor Tools</span>
                    <h1>Manage Courses</h1>
                    <p>Create polished courses, update course metadata, and control publishing status.</p>
                </div>
                <a class="btn" href="manage_lessons.php"><i data-lucide="list-video"></i> Manage Lessons</a>
                <a class="btn" href="manage_quizzes.php"><i data-lucide="file-question"></i> Manage Quizzes</a>
            </section>

            <?php if ($error !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <section class="panel">
                <h2><?php echo $edit_course ? 'Update Course' : 'Create Course'; ?></h2>
                <form method="post" action="manage_courses.php<?php echo $edit_course ? '?edit=' . (int) $edit_course['id'] : ''; ?>" class="form-grid">
                    <input type="hidden" name="action" value="<?php echo $edit_course ? 'update' : 'create'; ?>">
                    <?php if ($edit_course): ?>
                        <input type="hidden" name="course_id" value="<?php echo (int) $edit_course['id']; ?>">
                    <?php endif; ?>

                    <div class="field full">
                        <label for="title">Course Title</label>
                        <input id="title" type="text" name="title" value="<?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="field">
                        <label for="category">Category</label>
                        <input id="category" type="text" name="category" value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="field">
                        <label for="duration">Duration</label>
                        <input id="duration" type="text" name="duration" value="<?php echo htmlspecialchars($duration, ENT_QUOTES, 'UTF-8'); ?>" placeholder="8 weeks" required>
                    </div>
                    <div class="field">
                        <label for="level">Level</label>
                        <select id="level" name="level">
                            <option value="Beginner" <?php echo $level === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="Intermediate" <?php echo $level === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="Advanced" <?php echo $level === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo $status === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>
                    <?php if ($role === 'admin'): ?>
                        <div class="field">
                            <label for="instructor_id">Instructor</label>
                            <select id="instructor_id" name="instructor_id" required>
                                <option value="">Select instructor</option>
                                <?php foreach ($instructors as $instructor): ?>
                                    <option value="<?php echo (int) $instructor['id']; ?>" <?php echo (int) $instructor_id === (int) $instructor['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($instructor['full_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="field <?php echo $role === 'admin' ? '' : 'full'; ?>">
                        <label for="cover_image">Cover Image URL</label>
                        <input id="cover_image" type="url" name="cover_image" value="<?php echo htmlspecialchars($cover_image, ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://example.com/course.jpg">
                    </div>
                    <div class="field full">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>
                    <div class="field full">
                        <div class="form-actions">
                            <button class="btn primary" type="submit"><i data-lucide="<?php echo $edit_course ? 'save' : 'plus'; ?>"></i> <?php echo $edit_course ? 'Update Course' : 'Create Course'; ?></button>
                            <?php if ($edit_course): ?>
                                <a class="btn" href="manage_courses.php"><i data-lucide="x"></i> Cancel Edit</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </section>

            <section class="panel">
                <div class="section-head">
                    <div>
                        <h2>Course Records</h2>
                        <p>All course actions are protected by role and ownership checks.</p>
                    </div>
                </div>

                <?php if (count($courses) > 0): ?>
                    <div class="table-shell">
                        <div class="table-scroll">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Instructor</th>
                                        <th>Access</th>
                                        <th>Status</th>
                                        <th>Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($courses as $course): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                                <span><?php echo htmlspecialchars($course['category'], ENT_QUOTES, 'UTF-8'); ?> • <?php echo htmlspecialchars($course['level'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($course['instructor_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><span class="tag success">Free</span></td>
                                            <td><span class="tag <?php echo htmlspecialchars($course['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($course['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                            <td><?php echo htmlspecialchars($course['updated_at'] ?? $course['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <div class="table-actions">
                                                    <a class="btn small" href="course.php?id=<?php echo (int) $course['id']; ?>"><i data-lucide="external-link"></i> Open</a>
                                                    <a class="btn small warning" href="manage_courses.php?edit=<?php echo (int) $course['id']; ?>"><i data-lucide="pencil"></i> Edit</a>
                                                    <a class="btn small" href="manage_lessons.php?course_id=<?php echo (int) $course['id']; ?>"><i data-lucide="list-video"></i> Lessons</a>
                                                    <a class="btn small" href="manage_quizzes.php?course_id=<?php echo (int) $course['id']; ?>"><i data-lucide="file-question"></i> Quizzes</a>
                                                    <form class="inline-form" method="post" action="manage_courses.php" onsubmit="return confirm('Delete this course and all related lessons, enrollments, progress, and notifications?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="course_id" value="<?php echo (int) $course['id']; ?>">
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
                        <p>No courses have been created yet.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
