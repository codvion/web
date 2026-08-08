<?php

$sidebar_profile_name = $full_name ?? ($_SESSION['full_name'] ?? 'User');
$sidebar_profile_role = $role ?? ($_SESSION['role'] ?? 'student');
$sidebar_profile_image = trim((string) ($_SESSION['profile_image'] ?? ''));

if ($sidebar_profile_image === '' && isset($profile_image_src)) {
    $sidebar_profile_image = trim((string) $profile_image_src);
}

$sidebar_profile_initial_source = trim((string) $sidebar_profile_name);
$sidebar_profile_initial = $sidebar_profile_initial_source !== '' ? strtoupper(substr($sidebar_profile_initial_source, 0, 1)) : 'U';

?>
<div class="sidebar-profile">
    <span class="sidebar-avatar" aria-hidden="true">
        <?php if ($sidebar_profile_image !== ''): ?>
            <img src="<?php echo htmlspecialchars($sidebar_profile_image, ENT_QUOTES, 'UTF-8'); ?>" alt="">
        <?php else: ?>
            <span><?php echo htmlspecialchars($sidebar_profile_initial, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endif; ?>
    </span>
    <div class="sidebar-profile-info">
        <strong><?php echo htmlspecialchars($sidebar_profile_name, ENT_QUOTES, 'UTF-8'); ?></strong>
        <span class="sidebar-role"><?php echo htmlspecialchars($sidebar_profile_role, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
</div>
