<?php
require_once '../config.php';

if (!hasRole('cafeteria_staff') && !hasRole('admin')) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$message = '';
$message_type = '';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    
    $conn = getDBConnection();
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        // Get order details for notification
        $order_stmt = $conn->prepare("SELECT user_id, order_number FROM orders WHERE id = ?");
        $order_stmt->bind_param("i", $order_id);
        $order_stmt->execute();
        $order_result = $order_stmt->get_result()->fetch_assoc();
        $order_stmt->close();
        
        // Create notification
        $status_messages = [
            'preparing' => "Your order #{$order_result['order_number']} is being prepared.",
            'ready' => "Your order #{$order_result['order_number']} is ready for pickup!",
            'completed' => "Your order #{$order_result['order_number']} has been completed."
        ];
        
        if (isset($status_messages[$status])) {
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'order')");
            $notif_stmt->bind_param("is", $order_result['user_id'], $status_messages[$status]);
            $notif_stmt->execute();
            $notif_stmt->close();
        }
        
        $message = 'Order status updated successfully';
        $message_type = 'success';
    } else {
        $message = 'Failed to update order status';
        $message_type = 'error';
    }
    
    $stmt->close();
    $conn->close();
}

// Get orders
$status_filter = $_GET['status'] ?? 'all';
$conn = getDBConnection();

if ($status_filter === 'all') {
    $stmt = $conn->prepare("SELECT o.*, u.name as user_name FROM orders o 
                            JOIN users u ON o.user_id = u.id 
                            ORDER BY o.created_at DESC");
} else {
    $stmt = $conn->prepare("SELECT o.*, u.name as user_name FROM orders o 
                            JOIN users u ON o.user_id = u.id 
                            WHERE o.status = ? 
                            ORDER BY o.created_at DESC");
    $stmt->bind_param("s", $status_filter);
}

$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
$conn->close();

$page_title = 'Staff Dashboard';
include '../includes/header.php';
?>

<div class="container">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Order Management</h2>
        </div>
        
        <div style="margin-bottom: 2rem;">
            <a href="?status=all" class="btn <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
            <a href="?status=pending" class="btn <?php echo $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending</a>
            <a href="?status=preparing" class="btn <?php echo $status_filter === 'preparing' ? 'btn-primary' : 'btn-secondary'; ?>">Preparing</a>
            <a href="?status=ready" class="btn <?php echo $status_filter === 'ready' ? 'btn-primary' : 'btn-secondary'; ?>">Ready</a>
            <a href="?status=completed" class="btn <?php echo $status_filter === 'completed' ? 'btn-primary' : 'btn-secondary'; ?>">Completed</a>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Order Date</th>
                    <th>Pickup Time</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['pickup_time'])); ?></td>
                        <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="form-control" style="display: inline-block; width: auto; margin-right: 0.5rem;" 
                                        onchange="this.form.submit()">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="preparing" <?php echo $order['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                    <option value="ready" <?php echo $order['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                            <a href="../order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View Details</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (hasRole('admin')): ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div style="display: flex; gap: 1rem;">
            <a href="../admin/menu_manage.php" class="btn btn-primary">Manage Menu</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

