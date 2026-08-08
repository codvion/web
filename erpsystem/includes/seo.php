<?php
$siteName = 'ERP System';
$pageTitle = $pageTitle ?? $siteName;
$pageDescription = $pageDescription ?? 'Manage clients, projects, revenue, expenses, salaries, bills, and reports from one ERP workspace.';
$pageRobots = $pageRobots ?? 'index, follow';
$pageCanonical = $pageCanonical ?? current_canonical_url();
$pageImage = $pageImage ?? app_url('assets/img/logo.svg');
$seoTitle = $pageTitle === $siteName ? $siteName . ' | Business Control Platform' : $pageTitle . ' | ' . $siteName;
?>
    <title><?= e($seoTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="robots" content="<?= e($pageRobots) ?>">
    <meta name="theme-color" content="#0f766e">
    <meta name="application-name" content="<?= e($siteName) ?>">
    <link rel="canonical" href="<?= e($pageCanonical) ?>">
    <link rel="icon" type="image/svg+xml" href="assets/img/favicon.svg">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <meta property="og:title" content="<?= e($seoTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($pageCanonical) ?>">
    <meta property="og:image" content="<?= e($pageImage) ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?= e($seoTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($pageImage) ?>">
