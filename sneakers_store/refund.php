<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}
require_once 'includes/db_connect.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_refund'])) {
    $order_id = (int)$_POST['order_id'];
    $reason = $conn->real_escape_string($_POST['reason']);
    
    $sql = "INSERT INTO refunds (user_id, order_id, reason) VALUES ($user_id, $order_id, '$reason')";
    $conn->query($sql);
    $message = "Refund request submitted successfully. We will review it shortly.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Refund | Sneakers Store</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #eef8f2; margin: 0; display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 40px; }
        .panel { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px; margin: auto;}
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #333; }
        .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-submit { background-color: #e67e22; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .success-msg { background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="panel">
            <h2>Request a Refund</h2>
            <?php if (isset($message)) echo "<div class='success-msg'>$message</div>"; ?>
            <form method="POST" action="refund.php">
                <input type="hidden" name="submit_refund" value="1">
                <div class="form-group">
                    <label>Select Order to Refund</label>
                    <select name="order_id" required>
                        <option value="">-- Choose an Order --</option>
                        <?php
                        $orders_result = $conn->query("SELECT order_id, order_date, total_amount FROM orders WHERE user_id = $user_id ORDER BY order_id DESC");
                        while ($order = $orders_result->fetch_assoc()) {
                            echo "<option value='{$order['order_id']}'>Order #{$order['order_id']} - RM " . number_format($order['total_amount'], 2) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reason for Refund</label>
                    <textarea name="reason" rows="4" required placeholder="Please explain why you are requesting a refund..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Submit Request</button>
            </form>
        </div>
    </div>
</body>
</html>
