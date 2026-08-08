<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function request_origin(): string
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host;
}

function app_url(string $path = ''): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/'));
    $basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $basePath = $basePath === '/' || $basePath === '.' ? '' : $basePath;

    return request_origin() . $basePath . '/' . ltrim($path, '/');
}

function current_canonical_url(): string
{
    $requestPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ($_SERVER['SCRIPT_NAME'] ?? '/')), PHP_URL_PATH);

    return request_origin() . ($requestPath ?: '/');
}

function money(mixed $amount): string
{
    return 'Rs ' . number_format((float) $amount, 0);
}

function query_all(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function query_one(string $sql, array $params = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch();
    return $row ?: null;
}

function query_value(string $sql, array $params = []): mixed
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchColumn() ?: 0;
}

function execute_query(string $sql, array $params = []): bool
{
    $statement = db()->prepare($sql);
    return $statement->execute($params);
}

function redirect_to(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'username' => $_SESSION['username'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'staff',
    ];
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect_to('login');
    }
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function post_value(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function selected(string $current, ?string $expected): string
{
    return $current === $expected ? 'selected' : '';
}

function date_to_month(?string $date): string
{
    if (!$date) {
        return date('Y-m');
    }

    return date('Y-m', strtotime($date));
}

function status_class(?string $status): string
{
    $status = strtolower((string) $status);
    return match ($status) {
        'active', 'paid', 'completed' => 'status-success',
        'pending', 'planning', 'hold' => 'status-warning',
        'overdue', 'cancelled', 'inactive' => 'status-danger',
        default => 'status-muted',
    };
}

function month_start(): string
{
    return date('Y-m-01');
}

function next_month_start(): string
{
    return date('Y-m-01', strtotime('first day of next month'));
}

function month_input_to_date(string $value): string
{
    if ($value === '') {
        return month_start();
    }

    return $value . '-01';
}

function flash_message(): ?string
{
    $messages = [
        'saved' => 'Saved successfully.',
        'updated' => 'Updated successfully.',
        'deleted' => 'Deleted successfully.',
        'status' => 'Status updated successfully.',
    ];

    foreach ($messages as $key => $message) {
        if (isset($_GET[$key])) {
            return $message;
        }
    }

    return null;
}
