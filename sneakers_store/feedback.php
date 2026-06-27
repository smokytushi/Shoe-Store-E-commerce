<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}
require_once 'includes/db_connect.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $order_id = (int)$_POST['order_id'];
    $rating = (int)$_POST['rating'];
    $comments = $conn->real_escape_string($_POST['comments']);
    
    $sql = "INSERT INTO feedbacks (user_id, order_id, rating, comments) VALUES ($user_id, $order_id, $rating, '$comments')";
    $conn->query($sql);
    $message = "Thank you for your feedback!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback | Sneakers Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar { display: none !important; }
        body { font-family: Arial, sans-serif; background-color: #eef8f2; margin: 0; display: flex; min-height: 100vh; }
        .main-content { flex: 1; padding: 40px; }
        .panel { background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px; margin: auto;}
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #333; }
        .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-submit { background-color: #333; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .success-msg { background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="panel">
            <h2>Submit Feedback</h2>
            <?php if (isset($message)) echo "<div class='success-msg'>$message</div>"; ?>
            <form method="POST" action="feedback.php">
                <input type="hidden" name="submit_feedback" value="1">
                <div class="form-group">
                    <label>Select Order</label>
                    <select name="order_id" required>
                        <option value="">-- Choose an Order --</option>
                        <?php
                        $orders_result = $conn->query("SELECT order_id, order_date FROM orders WHERE user_id = $user_id ORDER BY order_id DESC");
                        while ($order = $orders_result->fetch_assoc()) {
                            echo "<option value='{$order['order_id']}'>Order #{$order['order_id']} (" . date('M d, Y', strtotime($order['order_date'])) . ")</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Rating (1-5)</label>
                    <select name="rating" required>
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Terrible</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Comments</label>
                    <textarea name="comments" rows="4" placeholder="Tell us about your experience..."></textarea>
                </div>
                <button type="submit" class="btn-submit">Submit Feedback</button>
            </form>
        </div>
    </div>
</body>
</html>
