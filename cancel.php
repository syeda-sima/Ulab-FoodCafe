<?php
require_once '../config.php';

$page_title = 'Payment Cancelled';
include '../includes/header.php';
?>

<div class="container">
    <div class="card" style="max-width: 600px; margin: 2rem auto; text-align: center;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">⚠️</div>
        <h2 style="color: var(--ulab-accent); margin-bottom: 1rem;">Payment Cancelled</h2>
        <p style="font-size: 1.1rem; margin-bottom: 2rem;">
            You cancelled the payment process.
        </p>
        <div style="margin-top: 2rem;">
            <a href="../orders.php" class="btn btn-primary">View Orders</a>
            <a href="../menu.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

