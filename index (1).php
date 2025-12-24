<?php
require_once 'config.php';

$page_title = 'Home';
include 'includes/header.php';

// Get featured menu items
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM menu_items WHERE availability = 1 ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$featured_items = $stmt->get_result();
$stmt->close();

// Get statistics for homepage
$stats = [];
$stmt = $conn->query("SELECT COUNT(*) as count FROM menu_items WHERE availability = 1");
$stats['menu_items'] = $stmt->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->query("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
$stats['today_orders'] = $stmt->fetch_assoc()['count'];
$stmt->close();

// Default images by category (for featured items)
$defaultImages = [
    'breakfast' => 'https://images.pexels.com/photos/376464/pexels-photo-376464.jpeg',
    'lunch'     => 'https://images.pexels.com/photos/1437267/pexels-photo-1437267.jpeg',
    'snacks'    => 'https://images.pexels.com/photos/4109137/pexels-photo-4109137.jpeg',
    'drinks'    => 'https://images.pexels.com/photos/110472/pexels-photo-110472.jpeg',
];

$conn->close();
?>

<!-- Hero Section -->
<div style="background: linear-gradient(135deg, var(--ulab-primary) 0%, var(--ulab-secondary) 100%); color: white; padding: 5rem 2rem; text-align: center;">
    <div class="container">
        <h1 style="font-size: 3.5rem; margin-bottom: 1.5rem; font-weight: bold;">Welcome to ULAB FoodCafe</h1>
        <p style="font-size: 1.5rem; margin-bottom: 2rem; opacity: 0.95;">
            Order your favorite meals online and enjoy hassle-free dining at ULAB
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <?php if (!isLoggedIn()): ?>
                <a href="register.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 2.5rem; background: var(--ulab-accent);">
                    Get Started
                </a>
                <a href="login.php" class="btn btn-outline" style="font-size: 1.2rem; padding: 1rem 2.5rem;">
                    Login
                </a>
            <?php else: ?>
                <a href="menu.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 2.5rem; background: var(--ulab-accent);">
                    Order Now
                </a>
                <a href="orders.php" class="btn btn-outline" style="font-size: 1.2rem; padding: 1rem 2.5rem;">
                    My Orders
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <!-- Statistics Section -->
    <div class="stats-grid" style="margin-top: -3rem; position: relative; z-index: 10;">
        <div class="stat-card" style="background: linear-gradient(135deg, #10B981, #059669);">
            <div class="stat-label">Menu Items</div>
            <div class="stat-value"><?php echo $stats['menu_items']; ?>+</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #3B82F6, #2563EB);">
            <div class="stat-label">Orders Today</div>
            <div class="stat-value"><?php echo $stats['today_orders']; ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #F59E0B, #D97706);">
            <div class="stat-label">Fast Service</div>
            <div class="stat-value">24/7</div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #8B5CF6, #7C3AED);">
            <div class="stat-label">Happy Customers</div>
            <div class="stat-value">100%</div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="card" style="margin-top: 4rem;">
        <div class="card-header">
            <h2 class="card-title" style="text-align: center;">How It Works</h2>
        </div>
        <div class="grid grid-4" style="margin-top: 2rem;">
            <div style="text-align: center; padding: 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">1️⃣</div>
                <h3 style="color: var(--ulab-primary); margin-bottom: 0.5rem;">Browse Menu</h3>
                <p>Explore our delicious menu items categorized by meals</p>
            </div>
            <div style="text-align: center; padding: 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">2️⃣</div>
                <h3 style="color: var(--ulab-primary); margin-bottom: 0.5rem;">Add to Cart</h3>
                <p>Select your favorite items and add them to your cart</p>
            </div>
            <div style="text-align: center; padding: 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">3️⃣</div>
                <h3 style="color: var(--ulab-primary); margin-bottom: 0.5rem;">Place Order</h3>
                <p>Choose pickup time and complete your order</p>
            </div>
            <div style="text-align: center; padding: 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">4️⃣</div>
                <h3 style="color: var(--ulab-primary); margin-bottom: 0.5rem;">Pick Up</h3>
                <p>Collect your order at the scheduled time</p>
            </div>
        </div>
    </div>

    <!-- Featured Menu Items -->
    <div class="card" style="margin-top: 3rem;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="card-title">Featured Items</h2>
            <a href="menu.php" class="btn btn-secondary">View All</a>
        </div>
        <div class="menu-grid">
            <?php 
            $item_count = 0;
            while ($item = $featured_items->fetch_assoc()): 
                $item_count++;
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
                        </div>
                        <div class="menu-item-footer">
                            <div class="menu-item-price">৳<?php echo number_format($item['price'], 2); ?></div>
                            <?php if (isLoggedIn()): ?>
                                <a href="menu.php?add_to_cart=<?php echo $item['id']; ?>" class="btn btn-primary">Add to Cart</a>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">Login to Order</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
            <?php if ($item_count == 0): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <p style="font-size: 1.2rem; color: var(--ulab-text);">No menu items available at the moment. Check back soon!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Features Section -->
    <div class="grid grid-3" style="margin-top: 3rem; margin-bottom: 3rem;">
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📱</div>
            <h3 style="color: var(--ulab-primary); margin-bottom: 1rem;">Easy Ordering</h3>
            <p>Browse our menu, add items to cart, and place your order in minutes. No waiting in long queues!</p>
        </div>
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⏰</div>
            <h3 style="color: var(--ulab-primary); margin-bottom: 1rem;">Pre-Order</h3>
            <p>Pre-order your meals and pick them up at your convenience. Perfect for busy schedules!</p>
        </div>
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💳</div>
            <h3 style="color: var(--ulab-primary); margin-bottom: 1rem;">Cashless Payment</h3>
            <p>Pay securely using cash, card, or digital wallets like bKash and Nagad. Multiple payment options!</p>
        </div>
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
            <h3 style="color: var(--ulab-primary); margin-bottom: 1rem;">Track Orders</h3>
            <p>Real-time order tracking from preparation to ready for pickup. Stay updated every step!</p>
        </div>
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
            <h3 style="color: var(--ulab-primary); margin-bottom: 1rem;">Rate & Review</h3>
            <p>Share your feedback and rate your meals. Help us improve our service!</p>
        </div>
        <div class="card" style="text-align: center; padding: 2rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔔</div>
            <h3 style="color: var(--ulab-primary); margin-bottom: 1rem;">Notifications</h3>
            <p>Get instant notifications when your order is ready. Never miss an update!</p>
        </div>
    </div>

    <!-- Call to Action Section -->
    <?php if (!isLoggedIn()): ?>
    <div class="card" style="background: linear-gradient(135deg, var(--ulab-primary), var(--ulab-secondary)); color: white; text-align: center; padding: 3rem; margin-bottom: 3rem;">
        <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Order?</h2>
        <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.95;">
            Join hundreds of students, faculty, and staff who enjoy convenient online ordering
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="register.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 2.5rem; background: var(--ulab-accent);">
                Create Account
            </a>
            <a href="menu.php" class="btn btn-outline" style="font-size: 1.2rem; padding: 1rem 2.5rem;">
                Browse Menu
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

