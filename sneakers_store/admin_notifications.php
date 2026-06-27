<?php
// Strict Admin Gatekeeper
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin')) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';

$success_msg = '';
$error_msg = '';

// Handle New Notification Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $message = htmlspecialchars(trim($_POST['message']));
    $admin_id = $_SESSION['user_id'];

    if (empty($message)) {
        $error_msg = "Message cannot be empty.";
    } else {
        $sql_insert = "INSERT INTO notifications (admin_id, message) VALUES (?, ?)";
        if ($stmt = $conn->prepare($sql_insert)) {
            $stmt->bind_param("is", $admin_id, $message);
            if ($stmt->execute()) {
                $success_msg = "Notification sent to customers successfully!";
            } else {
                $error_msg = "Failed to send notification.";
            }
            $stmt->close();
        }
    }
}

// Fetch Notification History
$notifications = [];
// We JOIN with the users table so we can show the name of the Admin who sent it
$sql_fetch = "SELECT n.message, n.created_at, u.full_name as messenger 
              FROM notifications n 
              JOIN users u ON n.admin_id = u.user_id 
              ORDER BY n.created_at DESC";

if ($result = $conn->query($sql_fetch)) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing & Notifications | Sneakers Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar { display: none !important; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eef8f2;
            display: flex;
            min-height: 100vh;
        }

        
        .main-content {
            flex: 1;
            padding: 40px;
        }
        .panel {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        .panel h2 {
            margin-top: 0;
            font-size: 20px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 15px;
        }
        textarea {
            width: 100%;
            height: 120px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
            background-color: #f9f9f9;
        }
        .btn-submit {
            background-color: #222;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-submit:hover {
            background-color: #444;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        th { color: #666; background-color: #f9f9f9; }

        /* Alerts */
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .success { background-color: #ccffcc; color: #006600; }
        .error { background-color: #ffcccc; color: #cc0000; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        
        <div class="panel">
            <h2>Send Message to Customer</h2>
            
            <?php if(!empty($success_msg)): ?>
                <div class="message success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($error_msg)): ?>
                <div class="message error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <form action="admin_notifications.php" method="POST">
                <div class="form-group">
                    <textarea name="message" placeholder="Type your marketing or delivery update message here..." required></textarea>
                </div>
                <button type="submit" class="btn-submit">Submit Notification</button>
            </form>
        </div>

        <div class="panel">
            <h2>Notifications History</h2>
            <table>
                <thead>
                    <tr>
                        <th width="20%">Messenger</th>
                        <th width="20%">Date</th>
                        <th width="60%">Context</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($notifications as $note): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($note['messenger']); ?></strong></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($note['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($note['message']); ?></td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if(empty($notifications)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">No notifications sent yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>