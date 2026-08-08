<?php

require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'message' => 'Please login again.']);
    exit();
}

if ($_SESSION['role'] !== 'student') {
    echo json_encode(['ok' => false, 'message' => 'Only students can save lesson progress.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'message' => 'Invalid request method.']);
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$course_id = (int) ($_POST['course_id'] ?? 0);
$lesson_id = (int) ($_POST['lesson_id'] ?? 0);
$seconds = (int) ($_POST['seconds'] ?? 0);
$can_continue = (int) ($_POST['can_continue'] ?? 0);

if ($course_id <= 0 || $lesson_id <= 0 || $seconds < 5 || $can_continue !== 1) {
    echo json_encode(['ok' => false, 'message' => 'Watch at least 5 seconds before continuing.']);
    exit();
}

$stmt = $conn->prepare('SELECT l.id, l.title, c.id AS course_id, c.title AS course_title FROM lessons l INNER JOIN courses c ON l.course_id = c.id WHERE l.id = ? AND l.course_id = ? AND l.status = ? AND c.status = ?');
$stmt->execute([$lesson_id, $course_id, 'published', 'published']);

if ($stmt->rowCount() === 0) {
    echo json_encode(['ok' => false, 'message' => 'Lesson is not available.']);
    exit();
}

$lesson = $stmt->fetch();

$stmt = $conn->prepare('SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?');
$stmt->execute([$user_id, $course_id]);

if ($stmt->rowCount() === 0) {
    echo json_encode(['ok' => false, 'message' => 'You are not enrolled in this course.']);
    exit();
}

$was_unlocked = false;

$stmt = $conn->prepare('SELECT id, can_continue FROM lesson_progress WHERE user_id = ? AND lesson_id = ?');
$stmt->execute([$user_id, $lesson_id]);

if ($stmt->rowCount() > 0) {
    $existing_progress = $stmt->fetch();
    if ((int) $existing_progress['can_continue'] === 1) {
        $was_unlocked = true;
    }

    $stmt = $conn->prepare('UPDATE lesson_progress SET watch_seconds = GREATEST(watch_seconds, ?), can_continue = 1, completed_at = IF(completed_at IS NULL, NOW(), completed_at) WHERE user_id = ? AND lesson_id = ?');
    $stmt->execute([$seconds, $user_id, $lesson_id]);
} else {
    $stmt = $conn->prepare('INSERT INTO lesson_progress (user_id, course_id, lesson_id, watch_seconds, can_continue, completed_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$user_id, $course_id, $lesson_id, $seconds, 1]);
}

$stmt = $conn->prepare('SELECT COUNT(*) AS total FROM lessons WHERE course_id = ? AND status = ?');
$stmt->execute([$course_id, 'published']);
$total_lessons = (int) $stmt->fetch()['total'];

$stmt = $conn->prepare('SELECT COUNT(*) AS total FROM lesson_progress WHERE user_id = ? AND course_id = ? AND can_continue = 1');
$stmt->execute([$user_id, $course_id]);
$completed_lessons = (int) $stmt->fetch()['total'];

$progress_percent = 0;
if ($total_lessons > 0) {
    $progress_percent = (int) floor(($completed_lessons / $total_lessons) * 100);
}

if ($progress_percent >= 100) {
    $stmt = $conn->prepare('UPDATE enrollments SET progress_percent = ?, status = ?, completed_at = NOW() WHERE user_id = ? AND course_id = ?');
    $stmt->execute([100, 'completed', $user_id, $course_id]);

    if (!$was_unlocked) {
        $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user_id, 'Course Completed', 'You completed ' . $lesson['course_title'] . '. Great work.', 'success']);
    }
} else {
    $stmt = $conn->prepare('UPDATE enrollments SET progress_percent = ?, status = ? WHERE user_id = ? AND course_id = ?');
    $stmt->execute([$progress_percent, 'active', $user_id, $course_id]);

    if (!$was_unlocked) {
        $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
        $stmt->execute([$user_id, 'Lesson Unlocked', 'You can continue after completing ' . $lesson['title'] . '.', 'success']);
    }
}

echo json_encode(['ok' => true, 'progress_percent' => $progress_percent]);
exit();

?>
