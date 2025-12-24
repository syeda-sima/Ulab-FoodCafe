<?php
require_once '../config.php';

if (!hasRole('admin')) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$conn = getDBConnection();
$feedbacks = $conn->query("SELECT f.*, u.name as user_name, mi.name as item_name, o.order_number 
                          FROM feedback f 
                          JOIN users u ON f.user_id = u.id 
                          LEFT JOIN menu_items mi ON f.menu_item_id = mi.id 
                          LEFT JOIN orders o ON f.order_id = o.id 
                          ORDER BY f.created_at DESC");
$conn->close();

$page_title = 'Feedback Management';
include '../includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">User Feedback</h2>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Order</th>
                    <th>Item</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($feedback['user_name']); ?></td>
                        <td><?php echo $feedback['order_number'] ? htmlspecialchars($feedback['order_number']) : 'N/A'; ?></td>
                        <td><?php echo $feedback['item_name'] ? htmlspecialchars($feedback['item_name']) : 'Overall'; ?></td>
                        <td>
                            <span class="rating">
                                <?php for ($i = 0; $i < 5; $i++): ?>
                                    <?php echo $i < $feedback['rating'] ? '★' : '☆'; ?>
                                <?php endfor; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($feedback['comment']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top: 2rem;">
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

