<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$order_id = intval($_GET['order_id'] ?? 0);
$current_user = getCurrentUser();
$error = '';
$success = '';

// Verify order belongs to user
if ($order_id > 0) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $current_user['id']);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    if (!$order) {
        header('Location: ' . SITE_URL . '/orders.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');
    $item_ratings = $_POST['item_rating'] ?? [];
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a valid overall rating';
    } else {
        $conn = getDBConnection();
        
        // Insert overall order feedback
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, order_id, menu_item_id, rating, comment) VALUES (?, ?, NULL, ?, ?)");
        $stmt->bind_param("iiis", $current_user['id'], $order_id, $rating, $comment);
        $stmt->execute();
        $stmt->close();
        
        // Insert individual item ratings
        foreach ($item_ratings as $item_id => $item_rating) {
            if (!empty($item_rating) && intval($item_rating) >= 1 && intval($item_rating) <= 5) {
                $item_id = intval($item_id);
                $item_rating = intval($item_rating);
                $stmt = $conn->prepare("INSERT INTO feedback (user_id, order_id, menu_item_id, rating, comment) VALUES (?, ?, ?, ?, '')");
                $stmt->bind_param("iiii", $current_user['id'], $order_id, $item_id, $item_rating);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        $success = 'Thank you for your feedback!';
        $order_id = 0; // Reset to show success message
        $conn->close();
    }
}

// Get order items for feedback
$order_items = [];
if ($order_id > 0 && isset($order)) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT oi.*, mi.name FROM order_items oi 
                            JOIN menu_items mi ON oi.menu_item_id = mi.id 
                            WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_items = $stmt->get_result();
    $stmt->close();
    $conn->close();
}

$page_title = 'Feedback';
include 'includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Give Feedback</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="orders.php" class="btn btn-primary">Back to Orders</a>
            </div>
        <?php elseif ($order_id > 0): ?>
            <form method="POST" action="">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                
                <div class="form-group">
                    <label class="form-label">Overall Order Rating</label>
                    <select name="rating" class="form-control" required>
                        <option value="">Select Rating</option>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Comment</label>
                    <textarea name="comment" class="form-control" rows="5" placeholder="Share your experience..."></textarea>
                </div>
                
                <h3 style="margin-top: 2rem; margin-bottom: 1rem;">Rate Individual Items (Optional)</h3>
                
                <?php while ($item = $order_items->fetch_assoc()): ?>
                    <div class="card" style="margin-bottom: 1rem;">
                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                        <div class="form-group" style="margin-top: 1rem;">
                            <label class="form-label">Rating</label>
                            <select name="item_rating[<?php echo $item['menu_item_id']; ?>]" class="form-control">
                                <option value="">Not rated</option>
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Very Good</option>
                                <option value="3">3 - Good</option>
                                <option value="2">2 - Fair</option>
                                <option value="1">1 - Poor</option>
                            </select>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    Submit Feedback
                </button>
            </form>
        <?php else: ?>
            <p>Please select an order to provide feedback.</p>
            <a href="orders.php" class="btn btn-primary">View Orders</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

