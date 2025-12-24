<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
$current_user = getCurrentUser();

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $current_user['id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: ' . SITE_URL . '/orders.php');
    exit;
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, mi.name, mi.description FROM order_items oi 
                        JOIN menu_items mi ON oi.menu_item_id = mi.id 
                        WHERE oi.order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
$stmt->close();
$conn->close();

$page_title = 'Order Details';
include 'includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
        </div>
        
        <div class="grid grid-2" style="margin-bottom: 2rem;">
            <div>
                <h3>Order Information</h3>
                <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></p>
                <p><strong>Pickup Time:</strong> <?php echo date('M d, Y H:i', strtotime($order['pickup_time'])); ?></p>
                <p><strong>Status:</strong> 
                    <span class="badge status-<?php echo $order['status']; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>
            </div>
            <div>
                <h3>Payment Information</h3>
                <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                <p><strong>Payment Status:</strong> 
                    <span class="badge <?php echo $order['payment_status'] === 'paid' ? 'badge-success' : 'badge-warning'; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </p>
                <?php if ($order['transaction_id']): ?>
                    <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <h3>Order Items</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $order_items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                            <br>
                            <small><?php echo htmlspecialchars($item['description']); ?></small>
                        </td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>৳<?php echo number_format($item['price'], 2); ?></td>
                        <td>৳<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>৳<?php echo number_format($order['total_amount'], 2); ?></th>
                </tr>
            </tfoot>
        </table>
        
        <!-- Order Status Timeline -->
        <div style="margin-top: 2rem; padding: 2rem; background: var(--ulab-light); border-radius: 10px;">
            <h3 style="margin-bottom: 1.5rem; color: var(--ulab-primary);">Order Status Timeline</h3>
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1; text-align: center; padding: 1rem; border-radius: 5px; <?php echo in_array($order['status'], ['pending', 'preparing', 'ready', 'completed']) ? 'background: var(--ulab-success); color: white;' : 'background: white; border: 2px solid var(--ulab-border);'; ?>">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">📝</div>
                    <strong>Pending</strong>
                </div>
                <div style="flex: 1; text-align: center; padding: 1rem; border-radius: 5px; <?php echo in_array($order['status'], ['preparing', 'ready', 'completed']) ? 'background: var(--ulab-success); color: white;' : 'background: white; border: 2px solid var(--ulab-border);'; ?>">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">👨‍🍳</div>
                    <strong>Preparing</strong>
                </div>
                <div style="flex: 1; text-align: center; padding: 1rem; border-radius: 5px; <?php echo in_array($order['status'], ['ready', 'completed']) ? 'background: var(--ulab-success); color: white;' : 'background: white; border: 2px solid var(--ulab-border);'; ?>">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">✅</div>
                    <strong>Ready</strong>
                </div>
                <div style="flex: 1; text-align: center; padding: 1rem; border-radius: 5px; <?php echo $order['status'] === 'completed' ? 'background: var(--ulab-success); color: white;' : 'background: white; border: 2px solid var(--ulab-border);'; ?>">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🎉</div>
                    <strong>Completed</strong>
                </div>
            </div>
            <p style="text-align: center; margin-top: 1rem; color: var(--ulab-text);">
                <?php 
                $status_messages = [
                    'pending' => 'Your order is pending and will be processed soon.',
                    'preparing' => 'Your order is being prepared. Please wait.',
                    'ready' => 'Your order is ready for pickup!',
                    'completed' => 'Order completed. Thank you!',
                    'cancelled' => 'This order has been cancelled.'
                ];
                echo $status_messages[$order['status']] ?? 'Status unknown';
                ?>
            </p>
        </div>
        
        <div style="margin-top: 2rem;">
            <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
            <?php if ($order['status'] === 'ready' || $order['status'] === 'completed'): ?>
                <a href="feedback.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary">Give Feedback</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

