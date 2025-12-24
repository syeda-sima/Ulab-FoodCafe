<?php
$config_path = __DIR__ . '/../config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    require_once __DIR__ . '/config.php';
}
$current_user = getCurrentUser();
$unread_count = 0;

if ($current_user) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND read_status = 0");
    $stmt->bind_param("i", $current_user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $unread = $result->fetch_assoc();
    $unread_count = $unread['count'];
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js" defer></script>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>/index.php" style="color: white; text-decoration: none;">ULAB FoodCafe</a>
            </div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/menu.php">Menu</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="<?php echo SITE_URL; ?>/cart.php">Cart</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/orders.php">My Orders</a></li>
                        <?php if (hasRole('admin')): ?>
                            <li><a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Admin</a></li>
                        <?php elseif (hasRole('cafeteria_staff')): ?>
                            <li><a href="<?php echo SITE_URL; ?>/staff/dashboard.php">Staff</a></li>
                        <?php endif; ?>
                        <li class="notification-badge">
                            <a href="<?php echo SITE_URL; ?>/notifications.php">
                                🔔
                                <?php if ($unread_count > 0): ?>
                                    <span class="notification-count"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li>
                            <span><?php echo htmlspecialchars($current_user['name']); ?></span>
                            <a href="<?php echo SITE_URL; ?>/logout.php" class="btn btn-outline" style="margin-left: 1rem;">Logout</a>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-outline">Login</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

