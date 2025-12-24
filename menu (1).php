<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Handle add to cart
if (isset($_GET['add_to_cart'])) {
    $item_id = intval($_GET['add_to_cart']);
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$item_id])) {
        $_SESSION['cart'][$item_id]++;
    } else {
        $_SESSION['cart'][$item_id] = 1;
    }
    
    header('Location: ' . SITE_URL . '/menu.php?added=1');
    exit;
}

$category = $_GET['category'] ?? 'all';
$conn = getDBConnection();

if ($category === 'all') {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE availability = 1 ORDER BY category, name");
} else {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE category = ? AND availability = 1 ORDER BY name");
    $stmt->bind_param("s", $category);
}

$stmt->execute();
$menu_items = $stmt->get_result();
$stmt->close();
$conn->close();

// Default images by category (using online image links)
$defaultImages = [
    'breakfast' => 'https://images.pexels.com/photos/376464/pexels-photo-376464.jpeg', // breakfast table
    'lunch'     => 'https://images.pexels.com/photos/1437267/pexels-photo-1437267.jpeg', // rice & curry
    'snacks'    => 'https://images.pexels.com/photos/4109137/pexels-photo-4109137.jpeg', // snacks
    'drinks'    => 'https://images.pexels.com/photos/110472/pexels-photo-110472.jpeg',   // drinks
];

$page_title = 'Menu';
include 'includes/header.php';
?>

<div class="container">
    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Item added to cart successfully!</div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Daily Menu</h2>
        </div>
        
        <div style="margin-bottom: 2rem;">
            <a href="menu.php?category=all" class="btn <?php echo $category === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
            <a href="menu.php?category=breakfast" class="btn <?php echo $category === 'breakfast' ? 'btn-primary' : 'btn-secondary'; ?>">Breakfast</a>
            <a href="menu.php?category=lunch" class="btn <?php echo $category === 'lunch' ? 'btn-primary' : 'btn-secondary'; ?>">Lunch</a>
            <a href="menu.php?category=snacks" class="btn <?php echo $category === 'snacks' ? 'btn-primary' : 'btn-secondary'; ?>">Snacks</a>
            <a href="menu.php?category=drinks" class="btn <?php echo $category === 'drinks' ? 'btn-primary' : 'btn-secondary'; ?>">Drinks</a>
        </div>
        
        <div class="menu-grid">
            <?php while ($item = $menu_items->fetch_assoc()): 
                // Determine image URL: use item image if set, otherwise category-based default
                $image_url = !empty($item['image'])
                    ? $item['image']
                    : ($defaultImages[$item['category']] ?? 'https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg');
            ?>
                <div class="menu-item">
                    <div class="menu-item-image">
                        <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="menu-item-content">
                        <div class="menu-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="menu-item-description"><?php echo htmlspecialchars($item['description']); ?></div>
                        <div style="margin: 0.5rem 0;">
                            <span class="badge badge-info"><?php echo ucfirst($item['category']); ?></span>
                            <?php if ($item['stock'] > 0): ?>
                                <span class="badge badge-success">In Stock</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="menu-item-footer">
                            <div class="menu-item-price">৳<?php echo number_format($item['price'], 2); ?></div>
                            <?php if ($item['stock'] > 0): ?>
                                <a href="menu.php?add_to_cart=<?php echo $item['id']; ?>" class="btn btn-primary">Add to Cart</a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

