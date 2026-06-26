<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin')) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';

$success_msg = '';
$error_msg = '';

// Handle User Deletion (The "Delete" Action)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Cast to integer for security
    $delete_id = intval($_POST['delete_id']);
    
    // We strictly ensure we only delete 'customers', not other admins
    $delete_sql = "DELETE FROM users WHERE user_id = ? AND role = 'customer'";
    
    if ($stmt = $conn->prepare($delete_sql)) {
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $success_msg = "Customer account deleted successfully.";
        } else {
            $error_msg = "Error deleting customer account.";
        }
        $stmt->close();
    }
}

// Fetch all customers to populate the table
$customers = [];
$fetch_sql = "SELECT user_id, full_name, email, phone_number, gender, dob FROM users WHERE role = 'customer' ORDER BY user_id DESC";

if ($result = $conn->query($fetch_sql)) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management | Sneakers Store</title>
    <style>
        /* Vanilla CSS - Inheriting the clean dashboard architecture */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eef8f2;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
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
        .panel {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            overflow-x: auto; /* Ensures table doesn't break layout on small screens */
        }
        .panel h2 {
            margin-top: 0;
            font-size: 20px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        /* Table Styling matching proposal */
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
        th { 
            color: #666; 
            background-color: #f9f9f9;
        }
        
        /* Delete Button inside the table */
        .btn-delete {
            background-color: #cc0000;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.2s;
        }
        .btn-delete:hover {
            background-color: #990000;
        }

        /* Alert Messages */
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
            <h2>User Details</h2>

            <?php if(!empty($success_msg)): ?>
                <div class="message success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <?php if(!empty($error_msg)): ?>
                <div class="message error"><?php echo $error_msg; ?></div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>DOB</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($customer['gender']); ?></td>
                        <td><?php echo htmlspecialchars($customer['dob']); ?></td>
                        <td>
                            <form action="admin_customers.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this customer? This action cannot be undone.');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="delete_id" value="<?php echo $customer['user_id']; ?>">
                                <button type="submit" class="btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if(empty($customers)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No registered customers found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>