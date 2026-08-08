<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: 'localhost';
    $database = getenv('DB_NAME') ?: 'erp_system';
    $username = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$database};charset={$charset}";

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        http_response_code(500);
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>Database setup required</title>';
        echo '<style>body{font-family:Arial,sans-serif;background:#f5f7fb;color:#17202a;margin:0;padding:40px}';
        echo '.box{max-width:760px;background:#fff;border:1px solid #d9e2ec;border-radius:10px;padding:28px;margin:auto;box-shadow:0 20px 50px rgba(15,23,42,.08)}';
        echo 'code{background:#eef2f7;padding:2px 6px;border-radius:5px}.error{color:#b42318}</style></head><body><div class="box">';
        echo '<h1>Database setup required</h1>';
        echo '<p class="error">Could not connect to MySQL: ' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p>Create a MySQL database named <code>erp_system</code>, then import <code>database/schema.sql</code>.</p>';
        echo '<p>Default config uses <code>root</code> with an empty password. You can also set <code>DB_HOST</code>, <code>DB_NAME</code>, <code>DB_USER</code>, and <code>DB_PASS</code>.</p>';
        echo '</div></body></html>';
        exit;
    }

    return $pdo;
}
