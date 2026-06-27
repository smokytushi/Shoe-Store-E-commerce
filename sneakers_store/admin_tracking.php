<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin')) {
    header("Location: index.php");
    exit();
}
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_tracking'])) {
    $order_id = (int)$_POST['order_id'];
    $tracking_num = $conn->real_escape_string($_POST['tracking_number']);
    $status = $conn->real_escape_string($_POST['current_status']);
    
    // Check if a delivery status row exists for this order
    $check_sql = "SELECT delivery_id FROM delivery_status WHERE order_id = $order_id";
    $result = $conn->query($check_sql);
    
    if ($result && $result->num_rows > 0) {
        $sql = "UPDATE delivery_status SET tracking_number = '$tracking_num', current_status = '$status' WHERE order_id = $order_id";
        $conn->query($sql);
    } else {
        $sql = "INSERT INTO delivery_status (order_id, tracking_number, current_status, expected_delivery) VALUES ($order_id, '$tracking_num', '$status', DATE_ADD(CURDATE(), INTERVAL 7 DAY))";
        $conn->query($sql);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tracking | Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 40px; }
        .panel { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .btn-update { background-color: #4CAF50; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="panel">
            <h2>Manage Order Tracking</h2>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Tracking Number</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php
                $sql = "SELECT o.order_id, o.order_date, u.full_name, ds.tracking_number, ds.current_status 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id
                        LEFT JOIN delivery_status ds ON o.order_id = ds.order_id 
                        ORDER BY o.order_id DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#" . $row['order_id'] . "</td>";
                        echo "<td>" . date('M d, Y', strtotime($row['order_date'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<form method='POST' action='admin_tracking.php'>";
                        echo "<input type='hidden' name='update_tracking' value='1'>";
                        echo "<input type='hidden' name='order_id' value='{$row['order_id']}'>";
                        echo "<td><input type='text' name='tracking_number' value='" . htmlspecialchars($row['tracking_number'] ?? '') . "' placeholder='Enter Tracking #'></td>";
                        echo "<td>
                                <select name='current_status'>
                                    <option value='Pending' " . (($row['current_status'] == 'Pending') ? 'selected' : '') . ">Pending</option>
                                    <option value='Shipped' " . (($row['current_status'] == 'Shipped') ? 'selected' : '') . ">Shipped</option>
                                    <option value='Out for Delivery' " . (($row['current_status'] == 'Out for Delivery') ? 'selected' : '') . ">Out for Delivery</option>
                                    <option value='Delivered' " . (($row['current_status'] == 'Delivered') ? 'selected' : '') . ">Delivered</option>
                                </select>
                              </td>";
                        echo "<td><button type='submit' class='btn-update'>Update</button></td>";
                        echo "</form>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No orders found.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
