<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin')) {
    header("Location: index.php");
    exit();
}
require_once 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedbacks | Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar { display: none !important; }
        body { font-family: Arial, sans-serif; background-color: #fcfbee; margin: 0; display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 40px; }
        .panel { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .stars { color: #f39c12; font-weight: bold; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="panel">
            <h2>Customer Feedbacks</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Order ID</th>
                    <th>Rating</th>
                    <th>Comments</th>
                </tr>
                <?php
                $sql = "SELECT f.submitted_at, u.full_name, f.order_id, f.rating, f.comments 
                        FROM feedbacks f 
                        JOIN users u ON f.user_id = u.user_id 
                        ORDER BY f.feedback_id DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . date('M d, Y', strtotime($row['submitted_at'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td>#" . $row['order_id'] . "</td>";
                        echo "<td class='stars'>" . str_repeat("★", $row['rating']) . str_repeat("☆", 5 - $row['rating']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['comments']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No feedbacks submitted yet.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
