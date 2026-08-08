<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

http_response_code(404);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Page Not Found | LearnPro LMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <?php include 'partials/learnpro-header.php'; ?>

    <main class="auth-shell">
        <section class="auth-panel compact-panel" data-animate>
            <span class="eyebrow">404 Error</span>
            <h1>Page not found.</h1>
            <p>The page you are looking for may have moved, been renamed, or does not exist in LearnPro LMS.</p>
            <div class="form-actions">
                <a class="btn primary" href="index.php"><i data-lucide="home"></i> Go Home</a>
                <a class="btn" href="courses.php"><i data-lucide="book-open"></i> Browse Courses</a>
            </div>
        </section>
    </main>

    <?php include 'partials/learnpro-footer.php'; ?>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
