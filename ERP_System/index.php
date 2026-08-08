<?php
require_once __DIR__ . '/includes/functions.php';
$loggedIn = is_logged_in();
$pageTitle = 'ERP System';
$pageDescription = 'Run clients, projects, revenue, expenses, payroll, bills, and reports from one clean ERP business control platform.';
$pageRobots = 'index, follow';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php require __DIR__ . '/includes/seo.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="public-page">
    <header class="site-header">
        <a class="site-brand" href="/">
            <img class="brand-logo" src="assets/img/logo.svg" alt="" aria-hidden="true">
            <span>
                <strong>ERP System</strong>
                <small>Business Control Platform</small>
            </span>
        </a>
        <a class="site-login-button" href="<?= $loggedIn ? 'dashboard' : 'login' ?>">
            <?= $loggedIn ? 'Open Dashboard' : 'Login' ?>
        </a>
    </header>

    <main class="site-main">
        <section class="site-hero">
            <div class="hero-copy">
                <p class="eyebrow">Smart ERP for growing teams</p>
                <h1>Manage clients, projects, revenue, expenses, salaries, and bills from one control room.</h1>
                <p>Clean workflows for everyday business operations with dashboard insights, finance tracking, payroll records, and professional reporting.</p>
                <div class="hero-actions">
                    <a class="primary-button hero-button" href="<?= $loggedIn ? 'dashboard' : 'login' ?>">
                        <?= $loggedIn ? 'Go to Dashboard' : 'Login to ERP' ?>
                    </a>
                    <a class="secondary-button hero-button" href="#modules">View Modules</a>
                </div>
            </div>

            <div class="hero-preview" aria-label="ERP dashboard preview">
                <div class="preview-top">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="preview-grid">
                    <article><span>Total Revenue</span><strong>Rs 1.2M</strong></article>
                    <article><span>Active Projects</span><strong>24</strong></article>
                    <article><span>Pending Payments</span><strong>Rs 490K</strong></article>
                    <article><span>Net Profit</span><strong>Rs 692K</strong></article>
                </div>
                <div class="preview-bars">
                    <span style="height: 62%"></span>
                    <span style="height: 85%"></span>
                    <span style="height: 44%"></span>
                    <span style="height: 73%"></span>
                    <span style="height: 55%"></span>
                </div>
            </div>
        </section>

        <section id="modules" class="site-modules">
            <article>
                <span>01</span>
                <h2>Dashboard</h2>
                <p>Business KPIs, profit, pending payments, salaries, and bills in one place.</p>
            </article>
            <article>
                <span>02</span>
                <h2>CRM & Projects</h2>
                <p>Manage clients, active projects, status, budgets, and deadlines.</p>
            </article>
            <article>
                <span>03</span>
                <h2>Finance</h2>
                <p>Invoices, payments, expenses, payroll, and monthly cost control.</p>
            </article>
        </section>

        <footer class="app-footer">
            <span>This site was built by</span>
            <a href="https://codvion.site" target="_blank" rel="noopener noreferrer">CodVion</a>
        </footer>
    </main>
</body>
</html>
