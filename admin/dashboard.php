<?php
require_once '../config.php';

if (!hasRole('admin')) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total sales
$stmt = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
$stats['total_sales'] = $stmt->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Total orders
$stmt = $conn->query("SELECT COUNT(*) as count FROM orders");
$stats['total_orders'] = $stmt->fetch_assoc()['count'];
$stmt->close();

// Total users
$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role IN ('student', 'faculty', 'staff')");
$stats['total_users'] = $stmt->fetch_assoc()['count'];
$stmt->close();

// Pending orders
$stmt = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'preparing')");
$stats['pending_orders'] = $stmt->fetch_assoc()['count'];
$stmt->close();

// Popular items
$popular_items = $conn->query("SELECT mi.name, SUM(oi.quantity) as total_quantity 
                              FROM order_items oi 
                              JOIN menu_items mi ON oi.menu_item_id = mi.id 
                              GROUP BY mi.id 
                              ORDER BY total_quantity DESC 
                              LIMIT 5");

// Recent orders
$recent_orders = $conn->query("SELECT o.*, u.name as user_name 
                              FROM orders o 
                              JOIN users u ON o.user_id = u.id 
                              ORDER BY o.created_at DESC 
                              LIMIT 10");

// Sales by hour (peak hours)
$peak_hours = $conn->query("SELECT HOUR(order_date) as hour, COUNT(*) as count 
                          FROM orders 
                          GROUP BY HOUR(order_date) 
                          ORDER BY count DESC 
                          LIMIT 5");

$conn->close();

$page_title = 'Admin Dashboard';
include '../includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Admin Dashboard</h2>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Sales</div>
                <div class="stat-value">৳<?php echo number_format($stats['total_sales'], 2); ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Users</div>
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending Orders</div>
                <div class="stat-value"><?php echo $stats['pending_orders']; ?></div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Popular Items</h3>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity Sold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $popular_items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo $item['total_quantity']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Peak Hours</h3>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Hour</th>
                        <th>Orders</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($hour = $peak_hours->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $hour['hour'] . ':00'; ?></td>
                            <td><?php echo $hour['count']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recent Orders</h3>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $recent_orders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                        <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                            <span class="badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="../order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Quick Actions</h3>
        </div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="menu_manage.php" class="btn btn-primary">Manage Menu</a>
            <a href="users.php" class="btn btn-primary">Manage Users</a>
            <a href="reports.php" class="btn btn-primary">View Reports</a>
            <a href="feedback_manage.php" class="btn btn-primary">View Feedback</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

