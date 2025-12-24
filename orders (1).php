<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$current_user = getCurrentUser();
$conn = getDBConnection();

// Get user orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $current_user['id']);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
$conn->close();

$page_title = 'My Orders';
include 'includes/header.php';
?>

<div class="container">
    <?php if (isset($_GET['order_placed'])): ?>
        <div class="alert alert-success">Order placed successfully! You will receive a notification when it's ready.</div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">My Orders</h2>
        </div>
        
        <?php if ($orders->num_rows === 0): ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; margin-bottom: 2rem;">You haven't placed any orders yet</p>
                <a href="menu.php" class="btn btn-primary">Browse Menu</a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Date</th>
                        <th>Pickup Time</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($order['pickup_time'])); ?></td>
                            <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning'; ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

