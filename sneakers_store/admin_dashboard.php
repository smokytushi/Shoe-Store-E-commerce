<?php

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';

$products = [];
$sql = "SELECT sku, product_name, product_description, stock_quantity
        FROM products
        ORDER BY created_at DESC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $result->free();
}

$total_inventory_value = 0;
$sql = "SELECT SUM(price * stock_quantity) AS inventory_value FROM products";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $total_inventory_value = $row['inventory_value'];
}

$total_inventory = count($products);
$low_stock_alerts = 0;
foreach ($products as $product) {
    if ($product['stock_quantity'] < 10) {
        $low_stock_alerts++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Sneakers Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar { display: none !important; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fdfef3;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Top Header ── */
        .top-header {
            width: 100%;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            height: 60px;
            box-sizing: border-box;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .top-header .logo {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }
        .top-header nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #555;
            font-size: 14px;
        }
        .top-header nav a:hover { color: #111; }
        .top-header .user-info {
            font-size: 14px;
            color: #555;
        }

        /* ── Body Layout ── */
        .page-wrapper {
            display: flex;
            flex: 1;
        }

        .main-content {
            flex: 1;
            padding: 36px 40px;
        }
        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 24px;
            color: #222;
        }

        /* ── Stat Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .stat-card {
            background-color: #ffffff;
            padding: 20px 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            margin: 0 0 10px;
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 26px;
            font-weight: bold;
            margin: 0;
        }

        /* ── Dashboard Layout ── */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 20px;
        }
        .panel {
            background-color: #ffffff;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }
        .panel-header h2 {
            margin: 0;
            font-size: 15px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 11px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        th {
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #eee;
        }

        .btn-action {
            padding: 7px 14px;
            background-color: #222;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-action:hover { background-color: #444; }

        /* ── Activity Feed ── */
        .activity-item {
            font-size: 13px;
            color: #555;
            border-left: 3px solid #222;
            padding: 6px 10px;
            margin-bottom: 12px;
            background: #fafafa;
            border-radius: 0 4px 4px 0;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
        <!-- ── Main Content ── -->
        <div class="main-content">
            <h1 class="page-title">Inventory Dashboard</h1>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Products</h3>
                    <p class="stat-value"><?php echo $total_inventory; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Inventory Value</h3>
                    <p class="stat-value" style="color:#27ae60;">
                        RM <?php echo number_format($total_inventory_value, 2); ?>
                    </p>
                </div>
                <div class="stat-card">
                    <h3>Low Stock Alerts</h3>
                    <p class="stat-value" style="color:#cc0000;">
                        <?php echo $low_stock_alerts; ?> Products
                    </p>
                </div>
            </div>

            <!-- Inventory Table + Side Panels -->
            <div class="dashboard-layout">

                <!-- Left: Inventory Table -->
                <div class="panel">
                    <div class="panel-header">
                        <h2>Current Inventory</h2>
                        <a href="admin_product.php" class="btn-action">+ Add New Product</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Product Name</th>
                                <th>Description</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['product_description']); ?></td>
                                <td><strong><?php echo htmlspecialchars($product['stock_quantity']); ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center; color:#888;">No products in inventory.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Right: Active Shipments + Recent Stock Updates -->
                <div style="display: flex; flex-direction: column; gap: 20px;">

                    <div class="panel">
                        <h2 style="margin: 0 0 15px; font-size: 15px; color: #333;">Active Shipments</h2>
                        <?php
                        $track_sql = "SELECT order_id, tracking_number, current_status FROM delivery_status WHERE current_status != 'Delivered' ORDER BY updated_at DESC LIMIT 5";
                        $track_res = $conn->query($track_sql);
                        if ($track_res && $track_res->num_rows > 0) {
                            while ($t = $track_res->fetch_assoc()) {
                                echo '<div style="font-size: 13px; color: #555; border-left: 2px solid #e67e22; padding-left: 10px; margin-bottom: 15px;">';
                                echo '<strong>Order #' . $t['order_id'] . '</strong> — ' . ($t['tracking_number'] ? htmlspecialchars($t['tracking_number']) : 'No tracking set') . '<br>';
                                echo '<span style="color:#e67e22;">' . htmlspecialchars($t['current_status']) . '</span>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p style="font-size: 13px; color: #aaa;">No active shipments.</p>';
                        }
                        ?>
                    </div>

                    <div class="panel">
                        <h2 style="margin: 0 0 16px; font-size: 15px; color: #333;">Recent Stock Updates</h2>
                        <div class="activity-item">10 new sizes added to AJ1 High by Admin</div>
                        <div class="activity-item">15 units of Nike Dunk Low Panda sold</div>
                    </div>

                </div>

            </div><!-- /dashboard-layout -->

        </div><!-- /main-content -->
    </div><!-- /page-wrapper -->

<?php $conn->close(); ?>
</body>
</html>