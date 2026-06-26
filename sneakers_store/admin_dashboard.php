<?php

// ===================================
// Session & Role-Based Access Control
// ===================================
session_start();

// Check if user is logged in AND has admin privileges
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

// ===================================
// Database connection
// ===================================
require_once 'includes/db_connect.php';

// ===================================
// Fetch Inventory Data securely
// ===================================
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

// ==========================================
// ADMIN DASHBOARD ANALYTICS
// ==========================================

// Total Inventory Value
$total_inventory_value = 0;

// ==========================================
// CALCULATE TOTAL INVENTORY VALUE
// Formula:
// Product Price × Stock Quantity
// ==========================================

$sql = "
SELECT
SUM(price * stock_quantity)
AS inventory_value
FROM products
";

$result = $conn->query($sql);

if($row = $result->fetch_assoc()){

$total_inventory_value =
$row['inventory_value'];

}

// ===================================
// Admin dashboard metrics
// ===================================
$total_inventory = count($products);

$low_stock_alerts = 0; // You can build logic for this later!

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
    <style>
        /* Vanilla CSS Architecture */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eef8f2;
            display: flex;
            min-height: 100vh;
        }

        /* Reusable Sidebar Styling */
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

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 40px;
        }
        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .header-flex h1 { margin: 0; font-size: 28px; }
        
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
        }
        
        .dashboard-layout {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 20px;
        }
        .panel {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th { color: #666; font-size: 14px; }
        
        /* Action Buttons */
        .btn-action {
            padding: 6px 12px;
            background-color: #222;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-action:hover { background-color: #444; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="header-flex">
            <h1>INVENTORY DASHBOARD</h1>
            <div>
                Hello <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong>!
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                    <p style="font-size:24px;font-weight:bold;">
                        <?php echo $total_inventory; ?>
                    </p>
            </div>
            <div class="stat-card">
                <h3>Total Inventory Value</h3>
                    <p style="font-size:24px;font-weight:bold;color:#27ae60;">
                        RM <?php echo number_format($total_inventory_value,2); ?>
                    </p>
            </div>
            
            <div class="stat-card">
                <h3>Low Stock Alerts</h3>
                    <p style="font-size:24px;font-weight:bold;color:#cc0000;">
                        <?php echo $low_stock_alerts; ?> Products
                    </p>
            </div>
        </div>

        <div class="dashboard-layout">
            <div class="panel">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin: 0; font-size: 18px;">CURRENT INVENTORY</h2>
                    <a href="admin_product.php" class="btn-action">+ Add New Product</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Product Name</th>
                            <th>Product Description</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['sku']); ?></td>
                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['product_description']); ?></td>
                            <td><strong><?php echo htmlspecialchars($product['stock_quantity']); ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(empty($products)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">No products in inventory.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <h2 style="margin: 0 0 15px 0; font-size: 16px;">RECENT STOCK UPDATES</h2>
                <div style="font-size: 13px; color: #555; border-left: 2px solid #222; padding-left: 10px; margin-bottom: 15px;">
                    10 New sizes added to AJ1 High by Admin
                </div>
                <div style="font-size: 13px; color: #555; border-left: 2px solid #222; padding-left: 10px;">
                    15 Units of Nike Dunk Low Panda sold
                </div>
            </div>
        </div>
    </div>

    <?php
    $conn->close();
    ?>
</body>
</html>