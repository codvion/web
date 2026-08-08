<?php

$learnpro_header_current = basename($_SERVER['SCRIPT_NAME'] ?? '');
$learnpro_header_logged_in = isset($_SESSION['user_id']);
$learnpro_header_role = $learnpro_header_logged_in ? ($_SESSION['role'] ?? '') : '';
$learnpro_header_show_menu = $learnpro_header_current === 'index.php';
$learnpro_header_home = $learnpro_header_logged_in ? 'dashboard.php' : 'index.php';
$learnpro_header_plain = $learnpro_header_logged_in ? 'dashboard.php' : 'login.php';
$learnpro_header_plain_text = $learnpro_header_logged_in ? 'Dashboard' : 'Log In';
$learnpro_header_cta = $learnpro_header_logged_in ? 'logout.php' : 'register.php';
$learnpro_header_cta_text = $learnpro_header_logged_in ? 'Logout' : 'Get Started';

?>
<div class="site-loader" data-site-loader aria-hidden="true">
    <div class="site-loader-card">
        <span class="site-loader-mark">
            <span></span>
        </span>
        <p>Loading LearnPro</p>
    </div>
</div>
<header class="learnpro-header <?php echo $learnpro_header_show_menu ? 'index-sticky' : ''; ?> <?php echo !$learnpro_header_show_menu ? 'compact' : ''; ?> <?php echo $learnpro_header_logged_in ? 'logged-in' : ''; ?>">
    <a class="learnpro-brand" href="<?php echo htmlspecialchars($learnpro_header_home, ENT_QUOTES, 'UTF-8'); ?>">
        <span class="learnpro-logo" aria-hidden="true">
            <span></span>
        </span>
        <strong>LearnPro</strong>
        <span>LMS</span>
    </a>

    <button class="learnpro-menu-toggle" type="button" data-learnpro-menu-toggle aria-label="Open menu" aria-expanded="false">
        <i data-lucide="menu"></i>
        <i data-lucide="x"></i>
    </button>

    <?php if ($learnpro_header_show_menu): ?>
        <nav class="learnpro-menu" data-learnpro-menu aria-label="Main navigation">
            <a class="<?php echo $learnpro_header_current === 'index.php' ? 'active' : ''; ?>" href="#platform">Features</a>
            <a href="#courses">Courses</a>
            <a href="#workflow">Resources</a>
            <a href="#roles">About</a>
        </nav>
    <?php endif; ?>

    <div class="learnpro-actions">
        <?php if ($learnpro_header_logged_in): ?>
            <span class="learnpro-role"><?php echo htmlspecialchars($learnpro_header_role, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
        <a class="learnpro-login" href="<?php echo htmlspecialchars($learnpro_header_plain, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($learnpro_header_plain_text, ENT_QUOTES, 'UTF-8'); ?></a>
        <a class="learnpro-cta" href="<?php echo htmlspecialchars($learnpro_header_cta, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($learnpro_header_cta_text, ENT_QUOTES, 'UTF-8'); ?></a>
    </div>
</header>
