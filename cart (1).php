<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $item_id => $quantity) {
            $quantity = intval($quantity);
            if ($quantity > 0) {
                $_SESSION['cart'][$item_id] = $quantity;
            } else {
                unset($_SESSION['cart'][$item_id]);
            }
        }
    } elseif (isset($_POST['remove_item'])) {
        $item_id = intval($_POST['item_id']);
        unset($_SESSION['cart'][$item_id]);
    } elseif (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
    }
    
    header('Location: ' . SITE_URL . '/cart.php');
    exit;
}

$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
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
}

$page_title = 'Shopping Cart';
include 'includes/header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Shopping Cart</h2>
        </div>
        
        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 3rem;">
                <p style="font-size: 1.2rem; margin-bottom: 2rem;">Your cart is empty</p>
                <a href="menu.php" class="btn btn-primary">Browse Menu</a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $cart_item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($cart_item['item']['name']); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($cart_item['item']['description']); ?></small>
                                </td>
                                <td>৳<?php echo number_format($cart_item['item']['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantity[<?php echo $cart_item['item']['id']; ?>]" 
                                           value="<?php echo $cart_item['quantity']; ?>" 
                                           min="1" 
                                           style="width: 60px; padding: 0.5rem;">
                                </td>
                                <td>৳<?php echo number_format($cart_item['subtotal'], 2); ?></td>
                                <td>
                                    <button type="submit" name="remove_item" value="1" class="btn btn-danger" 
                                            onclick="document.querySelector('input[name=\'item_id\']').value=<?php echo $cart_item['item']['id']; ?>">
                                        Remove
                                    </button>
                                    <input type="hidden" name="item_id" value="">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" name="update_cart" class="btn btn-secondary">Update Cart</button>
                    <button type="submit" name="clear_cart" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to clear your cart?')">Clear Cart</button>
                </div>
            </form>
            
            <div class="cart-summary">
                <div class="cart-total">
                    <span>Total:</span>
                    <span>৳<?php echo number_format($total, 2); ?></span>
                </div>
                <div style="margin-top: 1.5rem;">
                    <a href="checkout.php" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                        Proceed to Checkout
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

