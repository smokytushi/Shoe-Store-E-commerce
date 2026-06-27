
<?php
// Maintain State & Security Gatekeeper
session_start();

// Check if the user is logged in AND is specifically a 'customer'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    // Kick unauthorized users back to the login page
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'includes/db_connect.php';

// Fetch specific user data to personalize the dashboard
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

/* NOTE FOR THE GROUP: 
  Orders and Reward Points are still placeholders.
  Cart items are now fetched from the session dynamically.
*/
$active_orders = 2; // Placeholder
$reward_points = 850; // Placeholder
$items_in_cart = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $items_in_cart += $item['quantity'];
    }
}
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

$stmt->execute();

$stmt->bind_result($active_orders);

$stmt->fetch();

$stmt->close();

//$active_orders = 2; // Placeholder

// ===================================
// Active Order
// ===================================
$sql = "SELECT SUM(total_amount)
        FROM orders
        WHERE user_id=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i",$user_id);

$stmt->execute();

$stmt->bind_result($total_spent);

$stmt->fetch();

$stmt->close();

$reward_points = floor($total_spent ?? 0);

//$reward_points = 850; // Placeholder

// =====================================
// ITEMS IN CART
// =====================================

$sql = "SELECT COUNT(*)
        FROM cart
        WHERE user_id=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i",$user_id);

$stmt->execute();

$stmt->bind_result($items_in_cart);

$stmt->fetch();

$stmt->close();

//$items_in_cart = 3; // Placeholder

// ========================================
// RECENT ORDERS
// ========================================

$sql = "SELECT
order_id,
order_date,
status,
total_amount
FROM orders
WHERE user_id=?
ORDER BY order_date DESC
LIMIT 5";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i",$user_id);

$stmt->execute();

$recent_orders = $stmt->get_result();

// ========================================
// WISHLIST
// ========================================

$sql = "SELECT
product_name,
price
FROM wishlist
WHERE user_id=?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i",$user_id);

$stmt->execute();

$wishlist = $stmt->get_result();
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

    <?php include 'sidebar.php'; ?>

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
                <h2>Recent Orders</h2>
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
                        <?php while($order = $recent_orders->fetch_assoc()) { ?>

                        <tr>

                            <td>#<?php echo $order['order_id']; ?></td>

                            <td><?php echo $order['order_date']; ?></td>

                            <td><?php echo htmlspecialchars($order['status']); ?></td>

                            <td>RM <?php echo number_format($order['total_amount'], 2); ?></td>

                            </tr>

                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <h2>My Wishlist</h2>
                <div style="font-size: 14px; color: #666;">
                    <p><strong>Nike Dunk Low</strong><br>RM 450.00</p>
                    <hr style="border: 0; border-top: 1px solid #eee;">
                    <p><strong>Air Jordan 1 Retro</strong><br>RM 800.00</p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>