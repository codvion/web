<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Karachi');

define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'codvion');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

try {

    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    $conn = $pdo;

    if (isset($_SESSION['user_id']) && !isset($_SESSION['profile_image'])) {
        $stmt = $conn->prepare('SELECT profile_image FROM login_system WHERE id = ?');
        $stmt->execute([(int) $_SESSION['user_id']]);

        if ($stmt->rowCount() > 0) {
            $session_profile = $stmt->fetch();
            $_SESSION['profile_image'] = $session_profile['profile_image'] ?? '';
        }
    }

} catch (PDOException $e) {
    header('Location: 503.php');
    exit();
}

?>
