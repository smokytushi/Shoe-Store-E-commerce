<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}
require_once 'includes/db_connect.php';
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Tracking | Sneakers Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar { display: none !important; }
        body { font-family: Arial, sans-serif; background-color: #eef8f2; margin: 0; display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 40px; }
        .panel { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .status { font-weight: bold; color: #e67e22; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="panel">
            <h2>Order Tracking</h2>
            <p>Track your active shipments and view past deliveries.</p>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Tracking Number</th>
                    <th>Delivery Status</th>
                </tr>
                <?php
                $sql = "SELECT o.order_id, o.order_date, o.total_amount, ds.tracking_number, ds.current_status 
                        FROM orders o 
                        LEFT JOIN delivery_status ds ON o.order_id = ds.order_id 
                        WHERE o.user_id = $user_id ORDER BY o.order_id DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>#" . $row['order_id'] . "</td>";
                        echo "<td>" . date('M d, Y', strtotime($row['order_date'])) . "</td>";
                        echo "<td>RM " . number_format($row['total_amount'], 2) . "</td>";
                        echo "<td>" . ($row['tracking_number'] ? htmlspecialchars($row['tracking_number']) : "Not Available") . "</td>";
                        echo "<td class='status'>" . ($row['current_status'] ? htmlspecialchars($row['current_status']) : "Pending") . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>You have no orders yet.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
