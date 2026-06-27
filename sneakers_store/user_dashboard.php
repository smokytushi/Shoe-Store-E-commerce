<?php
// Dashboard is a full-page app layout — it does NOT use header.php
// (header.php injects a <body> + navbar which breaks the sidebar flex layout)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ── Fetch user full name ──────────────────────────────────────────────────────
$full_name = "Customer";
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_full_name);
if ($stmt->fetch()) {
    $full_name = htmlspecialchars($db_full_name);
}
$stmt->close();

$first_name = explode(' ', $full_name)[0];

// ── Items in cart (from $_SESSION['cart'] — no cart table in DB) ──────────────
// Your DB uses $_SESSION['cart'] as an array keyed by product_id => qty
$items_in_cart = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        // Session stores cart as [product_id => qty] (an integer, not an array)
        $items_in_cart += is_array($qty) ? ($qty['quantity'] ?? 1) : (int)$qty;
    }
}
<<<<<<< HEAD
=======
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | Sneakers Store</title>
    <style>
        /* Vanilla CSS Architecture */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eef8f2; /* Light green tint from proposal */
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px;
            background-color: #ffffff;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            padding: 20px 0;
        }
        .sidebar-logo {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            color: #555;
            display: block;
            border-left: 4px solid transparent;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #f0f0f0;
            border-left: 4px solid #333;
            font-weight: bold;
            color: #333;
        }
        .logout-btn {
            margin-top: auto;
            border-top: 1px solid #eee;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            padding: 40px;
        }
        .welcome-header {
            margin-bottom: 30px;
        }
        .welcome-header h1 {
            margin: 0;
            font-size: 28px;
            color: #333;
        }
        .welcome-header span {
            color: #e67e22; /* Orange accent */
        }

        /* Dashboard Overview Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }
        .stat-card h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        .stat-card p {
            margin: 5px 0 0 0;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        /* Content Grid (Recent Orders & Wishlist) */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .panel {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .panel h2 {
            margin-top: 0;
            font-size: 18px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        /* Basic Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th { color: #666; font-size: 14px; }
        .status-shipped { color: #e67e22; font-weight: bold; }
        .status-delivered { color: #27ae60; font-weight: bold; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="welcome-header">
            <h2>My Account (<?php echo $full_name; ?>)</h2>
            <h1>Welcome back, <span><?php echo explode(' ', $full_name)[0]; ?>!</span></h1>
        </div>

        <h3>Dashboard Overview</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <div>
                    <h3>Active Orders</h3>
                    <p>[<?php echo $active_orders; ?>]</p>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <h3>Reward Points</h3>
                    <p>[<?php echo $reward_points; ?> PTS]</p>
                </div>
            </div>
            <div class="stat-card">
                <div>
                    <h3>Items in Cart</h3>
                    <p>[<?php echo $items_in_cart; ?>]</p>
                </div>
            </div>
        </div>

        <div class="content-grid">
            <div class="panel">
                <h2>Live Tracking & Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Tracking #</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $orders_sql = "SELECT o.order_id, o.order_date, ds.tracking_number, ds.current_status 
                                       FROM orders o 
                                       LEFT JOIN delivery_status ds ON o.order_id = ds.order_id 
                                       WHERE o.user_id = $user_id ORDER BY o.order_id DESC LIMIT 5";
                        $orders_res = $conn->query($orders_sql);
                        if ($orders_res && $orders_res->num_rows > 0) {
                            while($o_row = $orders_res->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>#" . $o_row['order_id'] . "</td>";
                                echo "<td>" . date('M d, Y', strtotime($o_row['order_date'])) . "</td>";
                                echo "<td>" . ($o_row['tracking_number'] ? htmlspecialchars($o_row['tracking_number']) : 'N/A') . "</td>";
                                echo "<td style='font-weight:bold; color:#e67e22;'>" . ($o_row['current_status'] ? htmlspecialchars($o_row['current_status']) : 'Pending') . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>You have no recent orders.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <h2>My Wishlist</h2>
                <div style="font-size: 14px; color: #666;">
                    <?php
                    $w_sql = "SELECT p.product_name, p.price FROM wishlist w JOIN products p ON w.product_id = p.product_id WHERE w.user_id = $user_id ORDER BY w.wishlist_id DESC LIMIT 3";
                    $w_res = $conn->query($w_sql);
                    if ($w_res && $w_res->num_rows > 0) {
                        while($w_row = $w_res->fetch_assoc()) {
                            echo '<p><strong>' . htmlspecialchars($w_row['product_name']) . '</strong><br>RM ' . number_format($w_row['price'], 2) . '</p>';
                            echo '<hr style="border: 0; border-top: 1px solid #eee;">';
                        }
                    } else {
                        echo '<p>Your wishlist is empty.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

</body>

<?php

// ===================================
// Session & Role-Based Access Control
// ===================================
session_start();

// Check if the user is logged in AND is specifically a 'customer'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    // Kick unauthorized users back to the login page
    header("Location: login.php");
    exit();
}

// ===================================
// Database connection
// ===================================
require_once 'includes/db_connect.php';

// ===================================
// Fetch customer information
// ===================================
$user_id = $_SESSION['user_id'];
$full_name = "Customer"; // Default fallback

$sql = "SELECT full_name FROM users WHERE user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_full_name);
    if ($stmt->fetch()) {
        $full_name = htmlspecialchars($db_full_name); // Sanitize output!
    }
    $stmt->close();
}

// ===================================
// Dashboard Statistic
// ===================================

/* NOTE FOR THE GROUP: 
  Right now, the stats (Active Orders, Reward Points) are hardcoded placeholders. 
  Once Solihin and Afiq build the 'orders' and 'cart' database tables, 
  Nabilah will need to write SQL COUNT() queries here to fetch the real numbers.
*/

// ===================================
// Active Order
// ===================================
$sql = "SELECT SUM(total_amount)
        FROM orders
        WHERE user_id=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i",$user_id);
>>>>>>> 19202a7530004290ac4060ee2349c7d5b200cfaf

// ── Active orders count (from orders table, status not 'Delivered'/'Cancelled') ─
$active_orders = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) FROM orders
    WHERE user_id = ?
    AND order_status NOT IN ('Delivered', 'Cancelled')
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($active_orders);
$stmt->fetch();
$stmt->close();

// ── Recent orders (last 5) ────────────────────────────────────────────────────
// Column is order_status (not 'status') per database.sql
$stmt = $conn->prepare("
    SELECT order_id, order_date, order_status, total_amount
    FROM orders
    WHERE user_id = ?
    ORDER BY order_date DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_orders = $stmt->get_result();

// ── Wishlist preview (last 3) — needs JOIN to products ───────────────────────
$stmt_w = $conn->prepare("
    SELECT p.product_name, p.price
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
    LIMIT 3
");
$stmt_w->bind_param("i", $user_id);
$stmt_w->execute();
$wishlist_items = $stmt_w->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | Sneakers Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<style>
/* Reset navbar from style.css — dashboard is full-page, no top nav */
.navbar { display: none !important; }

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #eef8f2;
    display: flex;
    min-height: 100vh;
}

/* ── Sidebar ── */
.sidebar {
    width: 240px;
    min-width: 240px;
    background: #fff;
    box-shadow: 2px 0 5px rgba(0,0,0,.05);
    display: flex;
    flex-direction: column;
    padding: 20px 0;
}
.sidebar-logo {
    font-size: 22px;
    font-weight: bold;
    text-align: center;
    padding: 0 20px 24px;
    color: #333;
    border-bottom: 1px solid #eee;
    margin-bottom: 10px;
}
.sidebar a {
    padding: 14px 24px;
    text-decoration: none;
    color: #555;
    display: block;
    border-left: 4px solid transparent;
    font-size: 15px;
}
.sidebar a:hover,
.sidebar a.active {
    background: #f5f5f5;
    border-left-color: #333;
    font-weight: bold;
    color: #333;
}
.sidebar .logout-link {
    margin-top: auto;
    border-top: 1px solid #eee;
    color: #c0392b;
}
.sidebar .logout-link:hover {
    background: #fff5f5;
    border-left-color: #c0392b;
    color: #c0392b;
}

/* ── Main content ── */
.main-content {
    flex: 1;
    padding: 40px;
    overflow-y: auto;
}
.welcome-header {
    margin-bottom: 30px;
}
.welcome-header h2 {
    margin: 0 0 4px;
    font-size: 15px;
    color: #999;
    font-weight: normal;
}
.welcome-header h1 {
    margin: 0;
    font-size: 30px;
    color: #333;
}
.welcome-header span { color: #e67e22; }

/* ── Stat cards ── */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 36px;
}
.stat-card {
    background: #fff;
    padding: 22px 20px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,.06);
}
.stat-card h3 {
    margin: 0 0 8px;
    font-size: 13px;
    color: #888;
    font-weight: normal;
    text-transform: uppercase;
    letter-spacing: .5px;
}
.stat-card p {
    margin: 0;
    font-size: 28px;
    font-weight: bold;
    color: #333;
}

/* ── Panels ── */
.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}
.panel {
    background: #fff;
    padding: 22px;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,.06);
}
.panel h2 {
    margin: 0 0 16px;
    font-size: 17px;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

/* ── Orders table ── */
table { width: 100%; border-collapse: collapse; }
th, td { padding: 11px 10px; text-align: left; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
th { color: #888; font-weight: 600; font-size: 13px; }
.badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}
.badge-pending    { background: #fff3cd; color: #856404; }
.badge-processing { background: #cce5ff; color: #004085; }
.badge-shipped    { background: #ffe0b2; color: #e65100; }
.badge-delivered  { background: #d4edda; color: #155724; }
.badge-cancelled  { background: #f8d7da; color: #721c24; }

/* ── Wishlist panel ── */
.wishlist-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}
.wishlist-item:last-child { border-bottom: none; }
.wishlist-item strong { display: block; color: #333; margin-bottom: 2px; }
.wishlist-item span { color: #555; }
.panel-link {
    display: block;
    margin-top: 14px;
    font-size: 13px;
    color: #e67e22;
    text-decoration: none;
    text-align: right;
}
.panel-link:hover { text-decoration: underline; }

/* ── Empty states ── */
.empty-state {
    color: #aaa;
    font-size: 14px;
    text-align: center;
    padding: 20px 0;
}
</style>

<div class="sidebar">
    <div class="sidebar-logo">HYPE Sneakers Store</div>
    <a href="index.php">Home</a>
    <a href="user_dashboard.php" class="active">Dashboard</a>
    <a href="browse.php">Browse</a>
    <a href="cart.php">My Cart
        <?php if ($items_in_cart > 0): ?>
            <span style="background:#e67e22;color:#fff;border-radius:10px;padding:1px 7px;font-size:12px;margin-left:4px;"><?= $items_in_cart ?></span>
        <?php endif; ?>
    </a>
    <a href="wishlist.php">♡ Wishlist</a>
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<div class="main-content">

    <div class="welcome-header">
        <h2>My Account — <?= $full_name ?></h2>
        <h1>Welcome back, <span><?= $first_name ?>!</span></h1>
    </div>

    <!-- ── Stat cards ── -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Active Orders</h3>
            <p><?= $active_orders ?></p>
        </div>
        <div class="stat-card">
            <h3>Items in Cart</h3>
            <p><?= $items_in_cart ?></p>
        </div>
        <div class="stat-card">
            <h3>Wishlist Items</h3>
            <p><?= $wishlist_items->num_rows ?></p>
        </div>
    </div>

    <!-- ── Main panels ── -->
    <div class="content-grid">

        <!-- Recent Orders -->
        <div class="panel">
            <h2>Recent Orders</h2>
            <?php if ($recent_orders->num_rows === 0): ?>
                <p class="empty-state">You haven't placed any orders yet.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recent_orders->fetch_assoc()):
                        $status = $order['order_status'];
                        $badge_class = 'badge-' . strtolower($status);
                    ?>
                    <tr>
                        <td>#<?= $order['order_id'] ?></td>
                        <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                        <td><span class="badge <?= $badge_class ?>"><?= htmlspecialchars($status) ?></span></td>
                        <td>RM <?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <!-- Wishlist preview -->
        <div class="panel">
            <h2>My Wishlist</h2>
            <?php
            // Reset pointer — num_rows already read it above so we re-fetch
            $stmt_w->execute();
            $wishlist_items = $stmt_w->get_result();
            if ($wishlist_items->num_rows === 0): ?>
                <p class="empty-state">Your wishlist is empty.</p>
            <?php else:
                while ($w = $wishlist_items->fetch_assoc()): ?>
                <div class="wishlist-item">
                    <strong><?= htmlspecialchars($w['product_name']) ?></strong>
                    <span>RM <?= number_format($w['price'], 2) ?></span>
                </div>
            <?php endwhile; endif; ?>
            <a href="wishlist.php" class="panel-link">View full wishlist →</a>
        </div>

    </div>
</div>

</body>
</html>