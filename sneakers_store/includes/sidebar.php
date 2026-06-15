<?php
// Ensure session is started to check the user's role
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current filename (e.g., 'user_dashboard.php') to highlight the active link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">
    <div class="sidebar-logo">
        <a href="index.php">Sneakers Store</a>
    </div>

    <div class="sidebar-menu">
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'system_admin')): ?>
            <div class="menu-label">Admin Controls</div>
            <a href="admin_dashboard.php" class="<?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">Inventory</a>
            <a href="admin_product.php" class="<?= ($current_page == 'admin_product.php') ? 'active' : '' ?>">Products</a>
            <a href="admin_deliveries.php" class="<?= ($current_page == 'admin_deliveries.php') ? 'active' : '' ?>">Orders</a>
            <a href="admin_customers.php" class="<?= ($current_page == 'admin_customers.php') ? 'active' : '' ?>">Customers</a>
            <a href="admin_notifications.php" class="<?= ($current_page == 'admin_notifications.php') ? 'active' : '' ?>">Marketing</a>
            
        <?php else: ?>
            <div class="menu-label">My Account</div>
            <a href="user_dashboard.php" class="<?= ($current_page == 'user_dashboard.php') ? 'active' : '' ?>">Dashboard</a>
            <a href="cart.php" class="<?= ($current_page == 'cart.php') ? 'active' : '' ?>">Shopping Cart</a>
            <a href="wishlist.php" class="<?= ($current_page == 'wishlist.php') ? 'active' : '' ?>">My Wishlist</a>
            <a href="checkout.php" class="<?= ($current_page == 'checkout.php') ? 'active' : '' ?>">Checkout</a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">Log Out</a>
    </div>
</div>