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
        <a href="index.php">HYPE Sneakers Store</a>
    </div>

    <div class="sidebar-menu">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'system_admin'): ?>
            <!-- System Admin: full access -->
            <div class="menu-label">Admin Controls</div>
            <a href="admin_dashboard.php" class="<?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">Inventory</a>
            <a href="admin_product.php" class="<?= ($current_page == 'admin_product.php') ? 'active' : '' ?>">Products</a>
            <a href="admin_tracking.php" class="<?= ($current_page == 'admin_tracking.php') ? 'active' : '' ?>">Manage Tracking</a>
            <a href="admin_refund.php" class="<?= ($current_page == 'admin_refund.php') ? 'active' : '' ?>">Manage Refunds</a>
            <a href="admin_feedback.php" class="<?= ($current_page == 'admin_feedback.php') ? 'active' : '' ?>">View Feedbacks</a>
            <a href="admin_customers.php" class="<?= ($current_page == 'admin_customers.php') ? 'active' : '' ?>">Customers</a>
            <a href="admin_notifications.php" class="<?= ($current_page == 'admin_notifications.php') ? 'active' : '' ?>">Marketing</a>

        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <!-- Admin: no Customers or Marketing -->
            <div class="menu-label">Admin Controls</div>
            <a href="admin_dashboard.php" class="<?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">Inventory</a>
            <a href="admin_product.php" class="<?= ($current_page == 'admin_product.php') ? 'active' : '' ?>">Products</a>
            <a href="admin_tracking.php" class="<?= ($current_page == 'admin_tracking.php') ? 'active' : '' ?>">Manage Tracking</a>
            <a href="admin_refund.php" class="<?= ($current_page == 'admin_refund.php') ? 'active' : '' ?>">Manage Refunds</a>
            <a href="admin_feedback.php" class="<?= ($current_page == 'admin_feedback.php') ? 'active' : '' ?>">View Feedbacks</a>
            
        <?php else: ?>
            <div class="menu-label">My Account</div>
            <a href="user_dashboard.php" class="<?= ($current_page == 'user_dashboard.php') ? 'active' : '' ?>">Dashboard</a>
            <a href="tracking.php" class="<?= ($current_page == 'tracking.php') ? 'active' : '' ?>">Order Tracking</a>
            <a href="cart.php" class="<?= ($current_page == 'cart.php') ? 'active' : '' ?>">Shopping Cart</a>
            <a href="wishlist.php" class="<?= ($current_page == 'wishlist.php') ? 'active' : '' ?>">My Wishlist</a>
            <a href="refund.php" class="<?= ($current_page == 'refund.php') ? 'active' : '' ?>">Request Refund</a>
            <a href="feedback.php" class="<?= ($current_page == 'feedback.php') ? 'active' : '' ?>">Submit Feedback</a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <a href="logout.php" class="logout-link">Log Out</a>
    </div>
</div>