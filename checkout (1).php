<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$current_user = getCurrentUser();
$error = '';

// Get cart items
$cart_items = [];
$total = 0;

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ' . SITE_URL . '/cart.php');
    exit;
}

$conn = getDBConnection();
$item_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($item_ids), '?'));
$stmt = $conn->prepare("SELECT * FROM menu_items WHERE id IN ($placeholders) AND availability = 1");
$stmt->bind_param(str_repeat('i', count($item_ids)), ...$item_ids);
$stmt->execute();
$result = $stmt->get_result();

while ($item = $result->fetch_assoc()) {
    $quantity = $_SESSION['cart'][$item['id']];
    $subtotal = $item['price'] * $quantity;
    $total += $subtotal;
    $cart_items[] = [
        'item' => $item,
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];
}

$stmt->close();
$conn->close();

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_time = $_POST['pickup_time'] ?? '';
    $payment_method = $_POST['payment_method'] ?? 'cash';
    
    if (empty($pickup_time)) {
        $error = 'Please select a pickup time';
    } else {
        $conn = getDBConnection();
        $order_number = generateOrderNumber();
        $user_id = $current_user['id'];
        $order_date = date('Y-m-d H:i:s');
        $payment_status = ($payment_method === 'cash') ? 'pending' : 'pending';
        
        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, order_date, pickup_time, payment_method, payment_status, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssd", $user_id, $order_number, $order_date, $pickup_time, $payment_method, $payment_status, $total);
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Add order items
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($cart_items as $cart_item) {
                $item_id = $cart_item['item']['id'];
                $quantity = $cart_item['quantity'];
                $price = $cart_item['item']['price'];
                $subtotal = $cart_item['subtotal'];
                
                $item_stmt->bind_param("iiidd", $order_id, $item_id, $quantity, $price, $subtotal);
                $item_stmt->execute();
            }
            
            $item_stmt->close();
            
            // Create notification
            $notification_msg = "Your order #{$order_number} has been placed successfully!";
            $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'order')");
            $notif_stmt->bind_param("is", $user_id, $notification_msg);
            $notif_stmt->execute();
            $notif_stmt->close();
            
            // Clear cart
            $_SESSION['cart'] = [];
            
            // Handle payment gateway redirect
            if (in_array($payment_method, ['ssl', 'bkash', 'nagad'])) {
                header('Location: ' . SITE_URL . '/payment/process.php?order_id=' . $order_id);
                exit;
            } else {
                header('Location: ' . SITE_URL . '/orders.php?order_placed=1');
                exit;
            }
        } else {
            $error = 'Failed to place order. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}

$page_title = 'Checkout';
include 'includes/header.php';
?>

<div class="container">
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Order Summary</h2>
            </div>
            
            <table class="table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $cart_item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cart_item['item']['name']); ?></td>
                            <td><?php echo $cart_item['quantity']; ?></td>
                            <td>৳<?php echo number_format($cart_item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="cart-total">
                <span>Total:</span>
                <span>৳<?php echo number_format($total, 2); ?></span>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Checkout</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Pickup Time</label>
                    <input type="datetime-local" name="pickup_time" class="form-control" required 
                           min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Cash on Pickup</option>
                        <option value="card">Card</option>
                        <option value="ssl">SSL Commerce (Card)</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($current_user['name']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($current_user['email']); ?>" disabled>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                    Place Order
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

