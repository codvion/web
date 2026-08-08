<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'ERP System';
$activePage = $activePage ?? 'dashboard';
$pageDescription = $pageDescription ?? 'Manage your company from one simple ERP workspace.';
$pageRobots = $pageRobots ?? 'noindex, nofollow';
require_login();
$currentUser = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
<?php require __DIR__ . '/seo.php'; ?>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="dashboard">
            <img class="brand-logo" src="assets/img/logo.svg" alt="" aria-hidden="true">
            <span>
                <strong>ERP System</strong>
                <small>Business Control</small>
            </span>
        </a>

        <nav class="nav">
            <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="dashboard">Dashboard</a>
            <a class="<?= $activePage === 'clients' ? 'active' : '' ?>" href="clients">Clients</a>
            <a class="<?= $activePage === 'projects' ? 'active' : '' ?>" href="projects">Projects</a>
            <a class="<?= $activePage === 'finance' ? 'active' : '' ?>" href="finance">Revenue & Expenses</a>
            <a class="<?= $activePage === 'salaries' ? 'active' : '' ?>" href="salaries">Salaries</a>
            <a class="<?= $activePage === 'bills' ? 'active' : '' ?>" href="bills">Rent & Bills</a>
            <a class="<?= $activePage === 'reports' ? 'active' : '' ?>" href="reports">Reports</a>
        </nav>

        <div class="sidebar-note">
            <span>Core Modules</span>
            <strong>Dashboard, CRM, Projects, Finance, HR, Bills, Reports</strong>
        </div>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <button class="menu-button" type="button" data-menu-toggle aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <div>
                <p class="eyebrow">Company ERP</p>
                <h1><?= e($pageTitle) ?></h1>
                <p><?= e($pageDescription) ?></p>
            </div>
            <div class="topbar-actions">
                <div class="user-chip">
                    <span><?= e($currentUser['role'] ?? 'admin') ?></span>
                    <strong><?= e($currentUser['name'] ?? 'Admin User') ?></strong>
                </div>
                <a class="logout-button" href="logout">Logout</a>
            </div>
        </header>

        <?php if ($message = flash_message()): ?>
            <div class="flash" data-flash><?= e($message) ?></div>
        <?php endif; ?>
