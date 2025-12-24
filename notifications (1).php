<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$current_user = getCurrentUser();

// Mark as read if requested
if (isset($_GET['mark_read'])) {
    $notif_id = intval($_GET['mark_read']);
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE notifications SET read_status = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notif_id, $current_user['id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: ' . SITE_URL . '/notifications.php');
    exit;
}

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE notifications SET read_status = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $current_user['id']);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: ' . SITE_URL . '/notifications.php');
    exit;
}

// Get notifications
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $current_user['id']);
$stmt->execute();
$notifications = $stmt->get_result();
$stmt->close();
$conn->close();

$page_title = 'Notifications';
include 'includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Notifications</h2>
            <a href="?mark_all_read=1" class="btn btn-secondary">Mark All as Read</a>
        </div>
        
        <?php if ($notifications->num_rows === 0): ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem;">No notifications</p>
            </div>
        <?php else: ?>
            <div style="list-style: none; padding: 0;">
                <?php while ($notif = $notifications->fetch_assoc()): ?>
                    <div class="card" style="margin-bottom: 1rem; <?php echo $notif['read_status'] == 0 ? 'border-left: 4px solid var(--ulab-primary);' : ''; ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <p style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <small style="color: #6B7280;">
                                    <?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?>
                                    <span class="badge badge-info"><?php echo ucfirst($notif['type']); ?></span>
                                </small>
                            </div>
                            <?php if ($notif['read_status'] == 0): ?>
                                <a href="?mark_read=<?php echo $notif['id']; ?>" class="btn btn-secondary" style="margin-left: 1rem;">
                                    Mark as Read
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

