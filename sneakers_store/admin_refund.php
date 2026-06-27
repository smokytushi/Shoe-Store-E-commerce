<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin')) {
    header("Location: index.php");
    exit();
}
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_refund'])) {
    $refund_id = (int)$_POST['refund_id'];
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "UPDATE refunds SET refund_status = '$status', processed_at = CURRENT_TIMESTAMP WHERE refund_id = $refund_id";
    $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Refunds | Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 40px; }
        .panel { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        .btn-approve { background-color: #4CAF50; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-reject { background-color: #f44336; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="panel">
            <h2>Manage Refund Requests</h2>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Order ID</th>
                    <th>Amount</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php
                $sql = "SELECT r.refund_id, r.requested_at, u.full_name, r.order_id, o.total_amount, r.reason, r.refund_status 
                        FROM refunds r 
                        JOIN users u ON r.user_id = u.user_id 
                        JOIN orders o ON r.order_id = o.order_id
                        ORDER BY r.refund_id DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . date('M d, Y', strtotime($row['requested_at'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                        echo "<td>#" . $row['order_id'] . "</td>";
                        echo "<td>RM " . number_format($row['total_amount'], 2) . "</td>";
                        echo "<td>" . htmlspecialchars($row['reason']) . "</td>";
                        echo "<td><strong>" . $row['refund_status'] . "</strong></td>";
                        echo "<td>";
                        if ($row['refund_status'] === 'Pending') {
                            echo "<form method='POST' action='admin_refund.php' style='display:inline;'>
                                    <input type='hidden' name='process_refund' value='1'>
                                    <input type='hidden' name='refund_id' value='{$row['refund_id']}'>
                                    <input type='hidden' name='status' value='Approved'>
                                    <button type='submit' class='btn-approve'>Approve</button>
                                  </form> ";
                            echo "<form method='POST' action='admin_refund.php' style='display:inline;'>
                                    <input type='hidden' name='process_refund' value='1'>
                                    <input type='hidden' name='refund_id' value='{$row['refund_id']}'>
                                    <input type='hidden' name='status' value='Rejected'>
                                    <button type='submit' class='btn-reject'>Reject</button>
                                  </form>";
                        } else {
                            echo "Processed";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No refund requests found.</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
