<?php

require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$course_id = (int) ($_GET['course_id'] ?? ($_GET['id'] ?? 0));
$lesson_id = (int) ($_GET['lesson_id'] ?? 0);

if ($course_id <= 0) {
    header('Location: courses.php');
    exit();
}

$target_url = 'course.php?id=' . $course_id;

if ($lesson_id > 0) {
    $target_url .= '&lesson_id=' . $lesson_id;
}

header('Location: ' . $target_url);
exit();

?>
