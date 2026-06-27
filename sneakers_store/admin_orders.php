<?php

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin')) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_order_status'])) {
        $order_id      = (int)$_POST['order_id'];
        $order_status  = $conn->real_escape_string($_POST['order_status']);
        $conn->query("UPDATE orders SET order_status = '$order_status' WHERE order_id = $order_id");
    }

    if (isset($_POST['update_delivery_status'])) {
        $order_id         = (int)$_POST['order_id'];
        $delivery_status  = $conn->real_escape_string($_POST['delivery_status']);
        $expected_date    = $conn->real_escape_string($_POST['expected_delivery']);

        // Upsert: update if exists, insert if not
        $check = $conn->query("SELECT delivery_id FROM delivery_status WHERE order_id = $order_id");
        if ($check && $check->num_rows > 0) {
            $conn->query("UPDATE delivery_status 
                          SET current_status = '$delivery_status', expected_delivery = '$expected_date'
                          WHERE order_id = $order_id");
        } else {
            $conn->query("INSERT INTO delivery_status (order_id, expected_delivery, current_status)
                          VALUES ($order_id, '$expected_date', '$delivery_status')");
        }
    }
}

$filter_status  = isset($_GET['status'])  ? $conn->real_escape_string($_GET['status'])  : '';
$filter_payment = isset($_GET['payment']) ? $conn->real_escape_string($_GET['payment']) : '';
$search         = isset($_GET['search'])  ? $conn->real_escape_string($_GET['search'])  : '';

$where_clauses = [];
if ($filter_status)  $where_clauses[] = "o.order_status = '$filter_status'";
if ($filter_payment) $where_clauses[] = "p.payment_status = '$filter_payment'";
if ($search)         $where_clauses[] = "(o.order_id LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';


$sql = "
    SELECT
        o.order_id,
        o.total_amount,
        o.shipping_address,
        o.order_status,
        o.order_date,
        u.full_name,
        u.email,
        u.phone_number,
        p.payment_method,
        p.payment_status,
        p.payment_date,
        d.current_status   AS delivery_status,
        d.expected_delivery,
        d.updated_at       AS delivery_updated_at,
        (
            SELECT GROUP_CONCAT(pr.product_name SEPARATOR ', ')
            FROM order_details od
            JOIN products pr ON od.product_id = pr.product_id
            WHERE od.order_id = o.order_id
        ) AS items
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN payments p ON p.order_id = o.order_id
    LEFT JOIN delivery_status d ON d.order_id = o.order_id
    $where_sql
    ORDER BY o.order_date DESC
";

$orders = [];
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}


$counts = ['Pending' => 0, 'Processing' => 0, 'Shipped' => 0, 'Delivered' => 0, 'Cancelled' => 0];
$count_result = $conn->query("SELECT order_status, COUNT(*) AS cnt FROM orders GROUP BY order_status");
if ($count_result) {
    while ($r = $count_result->fetch_assoc()) {
        $counts[$r['order_status']] = $r['cnt'];
    }
}
$total_orders = array_sum($counts);


$revenue = 0;
$rev_result = $conn->query("SELECT SUM(o.total_amount) AS rev FROM orders o JOIN payments p ON p.order_id = o.order_id WHERE p.payment_status = 'Completed'");
if ($row = $rev_result->fetch_assoc()) $revenue = $row['rev'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Orders | Sneakers Store</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f8ee;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Top Header ── */
        .top-header {
            width: 100%;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .top-header .logo { font-size: 20px; font-weight: bold; color: #333; text-decoration: none; }
        .top-header nav a { margin-left: 20px; text-decoration: none; color: #555; font-size: 14px; }
        .top-header nav a:hover { color: #111; }
        .top-header .user-info { font-size: 14px; color: #555; }

        /* ── Page Wrapper ── */
        .page-wrapper { display: flex; flex: 1; }

        /* ── Sidebar ── */
        .sidebar {
            width: 220px;
            min-width: 220px;
            background: #fff;
            box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            padding: 20px 0;
            min-height: calc(100vh - 60px);
        }
        .sidebar-label {
            font-size: 11px;
            font-weight: bold;
            color: #aaa;
            letter-spacing: 1px;
            padding: 10px 25px 6px;
            text-transform: uppercase;
        }
        .sidebar a {
            padding: 12px 25px;
            text-decoration: none;
            color: #555;
            display: block;
            border-left: 4px solid transparent;
            font-size: 14px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: #f0f0f0;
            border-left: 4px solid #333;
            font-weight: bold;
            color: #333;
        }
        .sidebar .logout {
            margin-top: auto;
            padding: 12px 25px;
            color: #cc0000;
            font-size: 14px;
            text-decoration: none;
            display: block;
            border-left: 4px solid transparent;
        }
        .sidebar .logout:hover { background: #fff0f0; border-left-color: #cc0000; }

        /* ── Main Content ── */
        .main-content { flex: 1; padding: 36px 40px; }
        .page-title { font-size: 24px; font-weight: bold; margin: 0 0 24px; color: #222; }

        /* ── Stat Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: #fff;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card h3 { margin: 0 0 8px; font-size: 11px; color: #999; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .val { font-size: 24px; font-weight: bold; margin: 0; }

        /* ── Filters Bar ── */
        .filter-bar {
            background: #fff;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
        }
        .filter-bar input,
        .filter-bar select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            color: #333;
        }
        .filter-bar input { min-width: 220px; }
        .btn-filter {
            padding: 8px 18px;
            background: #333;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
        }
        .btn-filter:hover { background: #555; }
        .btn-reset {
            padding: 8px 14px;
            background: #eee;
            color: #555;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-reset:hover { background: #ddd; }

        /* ── Orders Table Panel ── */
        .panel {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .panel-header {
            padding: 16px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .panel-header h2 { margin: 0; font-size: 15px; color: #333; }
        .result-count { font-size: 13px; color: #888; }

        /* ── Table ── */
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        thead tr { background: #fafafa; }
        th {
            padding: 11px 14px;
            text-align: left;
            font-size: 11px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #eee;
            white-space: nowrap;
        }
        td { padding: 13px 14px; font-size: 13px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tbody tr:hover { background: #fafffe; }
        tbody tr:last-child td { border-bottom: none; }

        /* ── Badges ── */
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }
        .badge-pending    { background: #fff3cd; color: #856404; }
        .badge-processing { background: #cce5ff; color: #004085; }
        .badge-shipped    { background: #d4edda; color: #155724; }
        .badge-delivered  { background: #d1e7dd; color: #0f5132; }
        .badge-cancelled  { background: #f8d7da; color: #721c24; }
        .badge-completed  { background: #d1e7dd; color: #0f5132; }
        .badge-failed     { background: #f8d7da; color: #721c24; }
        .badge-out        { background: #cce5ff; color: #004085; }
        .badge-none       { background: #eee; color: #888; }

        /* ── Inline Edit Forms ── */
        .edit-form { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
        .edit-form select {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .edit-form input[type="date"] {
            padding: 4px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn-save {
            padding: 4px 10px;
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-save:hover { background: #3d9140; }

        /* ── Customer Cell ── */
        .customer-name { font-weight: bold; color: #222; }
        .customer-meta { font-size: 11px; color: #888; margin-top: 2px; }

        /* ── Items cell ── */
        .items-list { font-size: 12px; color: #555; max-width: 160px; }

        /* ── Empty state ── */
        .empty-state { text-align: center; padding: 40px; color: #aaa; font-size: 14px; }
    </style>
</head>
<body>

<!-- ── Top Header ── -->
<header class="top-header">
    <a href="index.php" class="logo">SNEAKERS STORE</a>
    <nav>
        <a href="index.php">All</a>
        <a href="index.php?cat=men">Men</a>
        <a href="index.php?cat=women">Women</a>
        <a href="index.php?cat=kids">Kids</a>
    </nav>
    <div class="user-info">
        Hello, <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></strong>
    </div>
</header>

<div class="page-wrapper">

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
        <div class="sidebar-label">Admin Controls</div>
        <a href="admin_dashboard.php">Inventory</a>
        <a href="admin_product.php">Products</a>
        <a href="admin_orders.php" class="active">Orders</a>
        <a href="admin_customers.php">Customers</a>
        <a href="admin_marketing.php">Marketing</a>
        <a href="logout.php" class="logout">Log Out</a>
    </aside>

    <!-- ── Main Content ── -->
    <div class="main-content">
        <h1 class="page-title">Order Management</h1>

        <!-- Stat Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p class="val"><?php echo $total_orders; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <p class="val" style="color:#856404;"><?php echo $counts['Pending']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Processing</h3>
                <p class="val" style="color:#004085;"><?php echo $counts['Processing']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Shipped</h3>
                <p class="val" style="color:#155724;"><?php echo $counts['Shipped']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Delivered</h3>
                <p class="val" style="color:#0f5132;"><?php echo $counts['Delivered']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Cancelled</h3>
                <p class="val" style="color:#cc0000;"><?php echo $counts['Cancelled']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Revenue</h3>
                <p class="val" style="color:#27ae60;">RM <?php echo number_format($revenue, 2); ?></p>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="admin_orders.php" class="filter-bar">
            <input type="text" name="search"
                   placeholder="Search by Order ID, name or email..."
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">

            <select name="status">
                <option value="">All Order Statuses</option>
                <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $filter_status === $s ? 'selected' : ''; ?>>
                        <?php echo $s; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="payment">
                <option value="">All Payment Statuses</option>
                <?php foreach (['Pending','Completed','Failed'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $filter_payment === $s ? 'selected' : ''; ?>>
                        <?php echo $s; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-filter">Filter</button>
            <a href="admin_orders.php" class="btn-reset">Reset</a>
        </form>

        <!-- Orders Table -->
        <div class="panel">
            <div class="panel-header">
                <h2>Orders</h2>
                <span class="result-count"><?php echo count($orders); ?> result(s)</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total (RM)</th>
                            <th>Payment</th>
                            <th>Order Status</th>
                            <th>Delivery Status</th>
                            <th>Expected Delivery</th>
                            <th>Order Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" class="empty-state">No orders found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                        <?php
                            // Badge helpers
                            $os_class = match($o['order_status']) {
                                'Pending'    => 'badge-pending',
                                'Processing' => 'badge-processing',
                                'Shipped'    => 'badge-shipped',
                                'Delivered'  => 'badge-delivered',
                                'Cancelled'  => 'badge-cancelled',
                                default      => 'badge-none'
                            };
                            $ps_class = match($o['payment_status'] ?? '') {
                                'Completed' => 'badge-completed',
                                'Failed'    => 'badge-failed',
                                'Pending'   => 'badge-pending',
                                default     => 'badge-none'
                            };
                            $ds_class = match($o['delivery_status'] ?? '') {
                                'Out for Delivery' => 'badge-out',
                                'Delivered'        => 'badge-delivered',
                                'Pending'          => 'badge-pending',
                                default            => 'badge-none'
                            };
                        ?>
                        <tr>
                            <!-- Order ID -->
                            <td><strong>#<?php echo $o['order_id']; ?></strong></td>

                            <!-- Customer -->
                            <td>
                                <div class="customer-name"><?php echo htmlspecialchars($o['full_name']); ?></div>
                                <div class="customer-meta"><?php echo htmlspecialchars($o['email']); ?></div>
                                <div class="customer-meta"><?php echo htmlspecialchars($o['phone_number']); ?></div>
                            </td>

                            <!-- Items -->
                            <td>
                                <div class="items-list">
                                    <?php echo $o['items'] ? htmlspecialchars($o['items']) : '<em style="color:#bbb;">—</em>'; ?>
                                </div>
                            </td>

                            <!-- Total -->
                            <td><strong>RM <?php echo number_format($o['total_amount'], 2); ?></strong></td>

                            <!-- Payment -->
                            <td>
                                <span class="badge <?php echo $ps_class; ?>">
                                    <?php echo $o['payment_status'] ?? '—'; ?>
                                </span>
                                <div class="customer-meta"><?php echo $o['payment_method'] ?? ''; ?></div>
                            </td>

                            <!-- Order Status (editable) -->
                            <td>
                                <form method="POST" action="admin_orders.php" class="edit-form">
                                    <input type="hidden" name="update_order_status" value="1">
                                    <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                    <select name="order_status">
                                        <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo $o['order_status'] === $s ? 'selected' : ''; ?>>
                                                <?php echo $s; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn-save">Save</button>
                                </form>
                            </td>

                            <!-- Delivery Status (editable) -->
                            <td>
                                <form method="POST" action="admin_orders.php" class="edit-form">
                                    <input type="hidden" name="update_delivery_status" value="1">
                                    <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                    <select name="delivery_status">
                                        <?php foreach (['Pending','Out for Delivery','Delivered'] as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo ($o['delivery_status'] ?? '') === $s ? 'selected' : ''; ?>>
                                                <?php echo $s; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="date" name="expected_delivery"
                                           value="<?php echo $o['expected_delivery'] ?? ''; ?>">
                                    <button type="submit" class="btn-save">Save</button>
                                </form>
                            </td>

                            <!-- Expected Delivery -->
                            <td>
                                <?php echo $o['expected_delivery']
                                    ? date('d M Y', strtotime($o['expected_delivery']))
                                    : '<span style="color:#bbb;">—</span>'; ?>
                            </td>

                            <!-- Order Date -->
                            <td style="white-space:nowrap; color:#666;">
                                <?php echo date('d M Y, g:ia', strtotime($o['order_date'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /main-content -->
</div><!-- /page-wrapper -->

<?php $conn->close(); ?>
</body>
</html>