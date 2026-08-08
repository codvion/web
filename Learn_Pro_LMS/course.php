<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
$course_id = (int) ($_GET['id'] ?? ($_GET['course_id'] ?? 0));
$requested_lesson_id = (int) ($_GET['lesson_id'] ?? 0);
$course = null;
$lessons = [];
$lesson_access = [];
$is_enrolled = false;
$enrollment = null;
$can_open_course = false;
$current_index = -1;
$current_lesson = null;
$previous_lesson = null;
$next_lesson = null;
$current_unlocked = false;
$youtube_video_id = '';
$youtube_embed_origin = 'http://' . ($_SERVER['HTTP_HOST'] ?? '127.0.0.1:8000');
$course_quiz_count = 0;

$stmt = $conn->prepare('SELECT c.*, u.full_name AS instructor_name FROM courses c INNER JOIN login_system u ON c.instructor_id = u.id WHERE c.id = ?');
$stmt->execute([$course_id]);

if ($stmt->rowCount() === 0) {
    header('Location: courses.php');
    exit();
}

$course = $stmt->fetch();

if ($role === 'admin') {
    $can_open_course = true;
}

if ($role === 'instructor' && (int) $course['instructor_id'] === $user_id) {
    $can_open_course = true;
}

if ($role === 'student' && $course['status'] === 'published') {
    $stmt = $conn->prepare('SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?');
    $stmt->execute([$user_id, $course_id]);

    if ($stmt->rowCount() > 0) {
        $is_enrolled = true;
        $enrollment = $stmt->fetch();
    }

    $can_open_course = true;
}

if (!$can_open_course) {
    header('Location: courses.php');
    exit();
}

if ($role === 'student') {
    $stmt = $conn->prepare('SELECT l.*, p.can_continue, p.watch_seconds FROM lessons l LEFT JOIN lesson_progress p ON p.lesson_id = l.id AND p.user_id = ? WHERE l.course_id = ? AND l.status = ? ORDER BY l.lesson_order ASC, l.id ASC');
    $stmt->execute([$user_id, $course_id, 'published']);
} else {
    $stmt = $conn->prepare('SELECT l.*, NULL AS can_continue, 0 AS watch_seconds FROM lessons l WHERE l.course_id = ? ORDER BY l.lesson_order ASC, l.id ASC');
    $stmt->execute([$course_id]);
}

$lessons = $stmt->fetchAll();

if ($role === 'student') {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM quizzes WHERE course_id = ? AND status = ?');
    $stmt->execute([$course_id, 'published']);
} else {
    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM quizzes WHERE course_id = ?');
    $stmt->execute([$course_id]);
}
$course_quiz_count = (int) $stmt->fetch()['total'];

$preferred_index = -1;
$first_unlocked_index = -1;
$first_incomplete_unlocked_index = -1;

foreach ($lessons as $key => $lesson_row) {
    $lesson_locked = false;

    if ($role === 'student') {
        if (!$is_enrolled) {
            $lesson_locked = true;
        } elseif ($key > 0) {
            $previous_row = $lessons[$key - 1];
            if ((int) ($previous_row['can_continue'] ?? 0) !== 1) {
                $lesson_locked = true;
            }
        }
    }

    $lesson_access[$key] = $lesson_locked ? 'locked' : 'open';

    if (!$lesson_locked && $first_unlocked_index === -1) {
        $first_unlocked_index = $key;
    }

    if (!$lesson_locked && (int) ($lesson_row['can_continue'] ?? 0) !== 1 && $first_incomplete_unlocked_index === -1) {
        $first_incomplete_unlocked_index = $key;
    }

    if ((int) $lesson_row['id'] === $requested_lesson_id && !$lesson_locked) {
        $preferred_index = $key;
    }
}

if ($preferred_index !== -1) {
    $current_index = $preferred_index;
} elseif ($first_incomplete_unlocked_index !== -1) {
    $current_index = $first_incomplete_unlocked_index;
} elseif ($first_unlocked_index !== -1) {
    $current_index = $first_unlocked_index;
} elseif (count($lessons) > 0) {
    $current_index = 0;
}

if ($current_index >= 0 && isset($lessons[$current_index])) {
    $current_lesson = $lessons[$current_index];

    if ($current_index > 0 && isset($lessons[$current_index - 1])) {
        $previous_lesson = $lessons[$current_index - 1];
    }

    if (isset($lessons[$current_index + 1])) {
        $next_lesson = $lessons[$current_index + 1];
    }

    if ($role !== 'student') {
        $current_unlocked = true;
    } elseif ($is_enrolled && (int) ($current_lesson['can_continue'] ?? 0) === 1) {
        $current_unlocked = true;
    }

    $raw_video_url = trim($current_lesson['video_url'] ?? '');
    $url_parts = parse_url($raw_video_url);
    $youtube_host = strtolower($url_parts['host'] ?? '');
    $youtube_path = trim($url_parts['path'] ?? '', '/');

    if (strpos($youtube_host, 'youtu.be') !== false) {
        $path_parts = explode('/', $youtube_path);
        $youtube_video_id = $path_parts[0] ?? '';
    } elseif (strpos($youtube_host, 'youtube.com') !== false || strpos($youtube_host, 'youtube-nocookie.com') !== false) {
        $query_values = [];
        if (isset($url_parts['query'])) {
            parse_str($url_parts['query'], $query_values);
        }

        if (isset($query_values['v'])) {
            $youtube_video_id = $query_values['v'];
        } else {
            $path_parts = explode('/', $youtube_path);
            foreach ($path_parts as $part_key => $path_part) {
                if (($path_part === 'embed' || $path_part === 'shorts' || $path_part === 'live') && isset($path_parts[$part_key + 1])) {
                    $youtube_video_id = $path_parts[$part_key + 1];
                }
            }
        }
    } elseif (preg_match('/^[A-Za-z0-9_-]{11}$/', $raw_video_url)) {
        $youtube_video_id = $raw_video_url;
    }

    $youtube_video_id = preg_replace('/[^A-Za-z0-9_-]/', '', $youtube_video_id);
}

$previous_url = '';
$next_url = '';
$next_enabled = false;
$total_topics = count($lessons);
$completed_topics = 0;

foreach ($lessons as $lesson_row) {
    if ((int) ($lesson_row['can_continue'] ?? 0) === 1) {
        $completed_topics++;
    }
}

$completion_percent = 0;
if ($role === 'student' && $enrollment) {
    $completion_percent = (int) $enrollment['progress_percent'];
} elseif ($total_topics > 0) {
    $completion_percent = (int) floor(($completed_topics / $total_topics) * 100);
}

if ($previous_lesson && ($role !== 'student' || $is_enrolled)) {
    $previous_url = 'course.php?id=' . (int) $course_id . '&lesson_id=' . (int) $previous_lesson['id'];
}

if ($next_lesson) {
    $next_url = 'course.php?id=' . (int) $course_id . '&lesson_id=' . (int) $next_lesson['id'];
} else {
    $next_url = 'course.php?id=' . (int) $course_id;
}

if ($role !== 'student') {
    $next_enabled = $next_lesson ? true : false;
} elseif ($is_enrolled && $current_unlocked) {
    $next_enabled = true;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title><?php echo htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?> | LearnPro LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="course-studio-body">
    <?php include 'partials/learnpro-header.php'; ?>

    <div class="app-layout course-app-layout">
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

        <main class="course-studio-shell">
        <section class="course-studio-hero">
            <div class="course-studio-copy">
                <span class="studio-eyebrow">Course Studio</span>
                <h1><?php echo $current_lesson ? htmlspecialchars($current_lesson['title'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p><?php echo $current_lesson ? htmlspecialchars($current_lesson['content'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="studio-tags">
                    <span><?php echo htmlspecialchars($course['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo htmlspecialchars($course['level'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo htmlspecialchars($course['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo (int) $total_topics; ?> Topics</span>
                    <a class="studio-tag-link" href="quizzes.php?course_id=<?php echo (int) $course_id; ?>"><i data-lucide="clipboard-list"></i> <?php echo (int) $course_quiz_count; ?> Quizzes</a>
                </div>
            </div>

            <div class="studio-topic-actions">
                <a class="studio-nav-button ghost" href="quizzes.php?course_id=<?php echo (int) $course_id; ?>">
                    <i data-lucide="clipboard-list"></i>
                    Course Quizzes
                </a>
                <?php if ($previous_url !== ''): ?>
                    <a class="studio-nav-button ghost" href="<?php echo htmlspecialchars($previous_url, ENT_QUOTES, 'UTF-8'); ?>">
                        <i data-lucide="arrow-left"></i>
                        Previous
                    </a>
                <?php else: ?>
                    <span class="studio-nav-button ghost disabled">
                        <i data-lucide="arrow-left"></i>
                        Previous
                    </span>
                <?php endif; ?>

                <?php if ($next_lesson): ?>
                    <?php if ($next_enabled): ?>
                        <a class="studio-nav-button" href="<?php echo htmlspecialchars($next_url, ENT_QUOTES, 'UTF-8'); ?>" data-next-topic data-next-url="<?php echo htmlspecialchars($next_url, ENT_QUOTES, 'UTF-8'); ?>">
                            Next Topic
                            <i data-lucide="arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="studio-nav-button disabled" data-next-topic data-next-url="<?php echo htmlspecialchars($next_url, ENT_QUOTES, 'UTF-8'); ?>">
                            Next Topic
                            <i data-lucide="lock"></i>
                        </span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="studio-nav-button disabled">
                        Final Topic
                        <i data-lucide="flag"></i>
                    </span>
                <?php endif; ?>
            </div>
        </section>

        <section class="course-studio-grid" data-course-layout>
            <section class="video-studio-card">
                <div class="studio-card-toolbar">
                    <span><i data-lucide="youtube"></i> Video Lesson</span>
                    <?php if ($role === 'student' && $is_enrolled && $current_lesson): ?>
                        <div class="toolbar-watch-gate">
                            <span class="toolbar-watch-count" data-watch-timer><?php echo $current_unlocked ? 'Unlocked' : '5s'; ?></span>
                        </div>
                    <?php elseif ($role === 'student' && !$is_enrolled): ?>
                        <div class="toolbar-watch-gate preview">
                            <span class="toolbar-watch-count" data-watch-timer>5s</span>
                        </div>
                    <?php else: ?>
                        <div class="toolbar-watch-gate preview">
                            <span class="toolbar-watch-count" data-watch-timer>5s</span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="studio-video-frame">
                    <?php if (!$current_lesson): ?>
                        <div class="course-gate studio-gate">
                            <span class="feature-icon"><i data-lucide="list-video"></i></span>
                            <h2>No video topic is available yet.</h2>
                            <p>This course does not have published topics at the moment.</p>
                            <?php if ($role === 'admin' || ($role === 'instructor' && (int) $course['instructor_id'] === $user_id)): ?>
                                <a class="btn primary" href="manage_lessons.php?course_id=<?php echo (int) $course_id; ?>"><i data-lucide="plus"></i> Add Topics</a>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($role === 'student' && !$is_enrolled): ?>
                        <div class="course-gate studio-gate">
                            <span class="feature-icon"><i data-lucide="lock"></i></span>
                            <h2>Enroll to start this course.</h2>
                            <p>You need an active enrollment before watching topics and saving progress.</p>
                            <form method="post" action="enroll.php">
                                <input type="hidden" name="course_id" value="<?php echo (int) $course_id; ?>">
                                <button class="btn primary" type="submit"><i data-lucide="badge-plus"></i> Enroll Now</button>
                            </form>
                        </div>
                    <?php elseif ($youtube_video_id !== ''): ?>
                        <div
                            id="youtube-player"
                            class="youtube-player-mount"
                            data-youtube-player
                            data-youtube-id="<?php echo htmlspecialchars($youtube_video_id, ENT_QUOTES, 'UTF-8'); ?>"
                            data-student="<?php echo $role === 'student' ? '1' : '0'; ?>"
                            data-course-id="<?php echo (int) $course_id; ?>"
                            data-lesson-id="<?php echo (int) $current_lesson['id']; ?>"
                            data-required-seconds="5"
                            data-watched-seconds="<?php echo (int) ($current_lesson['watch_seconds'] ?? 0); ?>"
                            data-unlocked="<?php echo $current_unlocked ? '1' : '0'; ?>"
                            data-progress-endpoint="progress.php"
                            data-origin="<?php echo htmlspecialchars($youtube_embed_origin, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    <?php else: ?>
                        <div class="course-gate studio-gate">
                            <span class="feature-icon"><i data-lucide="youtube"></i></span>
                            <h2>This topic needs a YouTube video URL.</h2>
                            <p>Add a YouTube watch, shorts, live, or embed URL in the lesson manager.</p>
                            <?php if ($role === 'admin' || ($role === 'instructor' && (int) $course['instructor_id'] === $user_id)): ?>
                                <a class="btn primary" href="manage_lessons.php?course_id=<?php echo (int) $course_id; ?>&edit=<?php echo (int) $current_lesson['id']; ?>"><i data-lucide="pencil"></i> Edit Video URL</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="lesson-workbench">
                    <div>
                        <span class="studio-eyebrow">Now Learning</span>
                        <h2><?php echo $current_lesson ? htmlspecialchars($current_lesson['title'], ENT_QUOTES, 'UTF-8') : htmlspecialchars($course['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
            </section>

            <aside class="studio-playlist" id="course-playlist">
                <div class="playlist-head">
                    <div>
                        <span class="studio-eyebrow">Playlist</span>
                        <h2>Course Topics</h2>
                    </div>
                    <span><?php echo (int) $completion_percent; ?>%</span>
                </div>
                <div class="progress-track playlist-track">
                    <span class="progress-bar" style="width: <?php echo (int) $completion_percent; ?>%;"></span>
                </div>

                <?php if (count($lessons) > 0): ?>
                    <div class="playlist-list">
                        <?php foreach ($lessons as $key => $lesson_row): ?>
                            <?php
                                $topic_is_locked = $lesson_access[$key] === 'locked';
                                $topic_is_active = $current_lesson && (int) $current_lesson['id'] === (int) $lesson_row['id'];
                                $topic_is_done = (int) ($lesson_row['can_continue'] ?? 0) === 1;
                            ?>

                            <?php if ($topic_is_locked): ?>
                                <span class="playlist-topic locked">
                                    <span class="playlist-number"><?php echo str_pad((string) ($key + 1), 2, '0', STR_PAD_LEFT); ?></span>
                                    <span>
                                        <strong><?php echo htmlspecialchars($lesson_row['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <small><?php echo (int) $lesson_row['duration_minutes']; ?> min</small>
                                    </span>
                                    <i data-lucide="lock"></i>
                                </span>
                            <?php else: ?>
                                <a class="playlist-topic <?php echo $topic_is_active ? 'active' : ''; ?> <?php echo $topic_is_done ? 'completed' : ''; ?>" href="course.php?id=<?php echo (int) $course_id; ?>&lesson_id=<?php echo (int) $lesson_row['id']; ?>">
                                    <span class="playlist-number"><?php echo str_pad((string) ($key + 1), 2, '0', STR_PAD_LEFT); ?></span>
                                    <span>
                                        <strong><?php echo htmlspecialchars($lesson_row['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <small><?php echo (int) $lesson_row['duration_minutes']; ?> min</small>
                                    </span>
                                    <?php if ($topic_is_done): ?>
                                        <i data-lucide="check-circle-2"></i>
                                    <?php else: ?>
                                        <i data-lucide="play-circle"></i>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No topics have been added yet.</p>
                    </div>
                <?php endif; ?>
                <button class="playlist-toggle playlist-toggle-inline" type="button" data-playlist-toggle aria-controls="course-playlist" aria-expanded="true">
                    <i data-lucide="panel-left-close"></i>
                    <span>Hide Topics</span>
                </button>
            </aside>
            <button class="playlist-toggle playlist-toggle-collapsed" type="button" data-playlist-toggle aria-controls="course-playlist" aria-expanded="true">
                <i data-lucide="panel-left-close"></i>
                <span>Hide Topics</span>
            </button>
        </section>
        </main>
    </div>
    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="https://www.youtube.com/iframe_api"></script>
    <script src="assets/js/app.js"></script>
    <script src="assets/js/course-player.js"></script>
</body>
</html>
