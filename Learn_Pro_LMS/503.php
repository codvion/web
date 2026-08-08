<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <title>Service Unavailable | LearnPro LMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <?php include 'partials/learnpro-header.php'; ?>

    <main class="auth-shell">
        <section class="auth-panel compact-panel">
            <span class="eyebrow">Service Status</span>
            <h1>We cannot connect to the database right now.</h1>
            <p>Please confirm that MySQL is running and that the database configuration in <strong>config.php</strong> matches your local server.</p>
            <a class="btn primary" href="index.php">Try Again</a>
        </section>
    </main>
    <?php include 'partials/learnpro-footer.php'; ?>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>
