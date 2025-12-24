<?php
require_once '../config.php';

if (!hasRole('admin')) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$conn = getDBConnection();

// Sales report
$stmt = $conn->prepare("SELECT DATE(order_date) as date, SUM(total_amount) as total, COUNT(*) as count 
                        FROM orders 
                        WHERE DATE(order_date) BETWEEN ? AND ? AND payment_status = 'paid'
                        GROUP BY DATE(order_date) 
                        ORDER BY date DESC");
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$sales_report = $stmt->get_result();
$stmt->close();

// Payment method breakdown
$stmt = $conn->prepare("SELECT payment_method, SUM(total_amount) as total, COUNT(*) as count 
                        FROM orders 
                        WHERE DATE(order_date) BETWEEN ? AND ? AND payment_status = 'paid'
                        GROUP BY payment_method");
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$payment_breakdown = $stmt->get_result();
$stmt->close();

$conn->close();

$page_title = 'Reports';
include '../includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Sales Reports</h2>
        </div>
        
        <form method="GET" style="margin-bottom: 2rem;" class="grid grid-3">
            <div class="form-group">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Generate Report</button>
            </div>
        </form>
        
        <h3>Daily Sales Report</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Orders</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $sales_report->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                        <td><?php echo $row['count']; ?></td>
                        <td>৳<?php echo number_format($row['total'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h3 style="margin-top: 2rem;">Payment Method Breakdown</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Payment Method</th>
                    <th>Orders</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $payment_breakdown->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo ucfirst($row['payment_method']); ?></td>
                        <td><?php echo $row['count']; ?></td>
                        <td>৳<?php echo number_format($row['total'], 2); ?></td>
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

