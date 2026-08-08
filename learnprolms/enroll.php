<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'student') {
    header('Location: courses.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: courses.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$course_id = (int) ($_POST['course_id'] ?? 0);

$stmt = $conn->prepare('SELECT id, title FROM courses WHERE id = ? AND status = ?');
$stmt->execute([$course_id, 'published']);

if ($stmt->rowCount() === 0) {
    header('Location: courses.php');
    exit();
}

$course = $stmt->fetch();

$stmt = $conn->prepare('SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?');
$stmt->execute([$user_id, $course_id]);

if ($stmt->rowCount() === 0) {
    $stmt = $conn->prepare('INSERT INTO enrollments (user_id, course_id, status, progress_percent) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, $course_id, 'active', 0]);

    $stmt = $conn->prepare('INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user_id, 'Course Enrollment Confirmed', 'You are now enrolled in ' . $course['title'] . '.', 'success']);
}

header('Location: course.php?id=' . $course_id);
exit();

?>
