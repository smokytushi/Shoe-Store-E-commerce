<?php
// Start the session to maintain state across pages
session_start();

// Include database connection
require_once 'db_connect.php';

$error_msg = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input to prevent XSS
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    // Basic server-side validation
    if (empty($username) || empty($password)) {
        $error_msg = "Please enter both username and password.";
    } else {
        // 2. Query the database securely using Prepared Statements
        $sql = "SELECT user_id, username, password_hash, role FROM users WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            
            // Store the result so we can check if the user exists
            $stmt->store_result();
            
            if ($stmt->num_rows == 1) {
                // Bind the result variables
                $stmt->bind_result($id, $db_username, $db_password_hash, $role);
                $stmt->fetch();
                
                // 3. Verify the password against the hashed password in the database
                if (password_verify($password, $db_password_hash)) {
                    // Password is correct! Initialize the session variables
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $db_username;
                    $_SESSION['role'] = $role;
                    
                    // 4. Role-Based Routing
                    // Redirect the user based on their specific role
                    if ($_SESSION['role'] === 'admin') {
                        header("Location: admin_dashboard.php");
                    } elseif ($_SESSION['role'] === 'system_admin') {
                        header("Location: system_admin.php"); // Or wherever you manage users
                    } else {
                        header("Location: user_dashboard.php"); // Default customer page
                    }
                    exit(); // Ensure no further code is executed after redirection
                } else {
                    $error_msg = "Invalid username or password.";
                }
            } else {
                $error_msg = "Invalid username or password.";
            }
            $stmt->close();
        } else {
            $error_msg = "Database error. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In | Sneakers Store</title>
    <style>
        /* Vanilla CSS - Matches your proposal wireframes perfectly */
        body {
            font-family: Arial, sans-serif;
            background-color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            flex-direction: column;
        }
        .header-text {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-text h1 {
            margin: 0;
            color: #333;
            font-size: 32px;
        }
        .header-text h2 {
            margin: 5px 0 0 0;
            color: #555;
            font-size: 20px;
            font-weight: normal;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 350px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #666;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #222;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            font-weight: bold;
        }
        .btn-submit:hover {
            background-color: #444;
        }
        .btn-back {
            background-color: #555;
            margin-top: 10px;
        }
        .btn-back:hover {
            background-color: #777;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .error { background-color: #ffcccc; color: #cc0000; }
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .register-link a { color: #333; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="header-text">
    <h1>Log In</h1>
    <h2>Sneakers Store</h2>
</div>

<div class="login-container">
    
    <?php if(!empty($error_msg)): ?>
        <div class="message error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn-submit">Log In</button>
        <button type="button" class="btn-submit btn-back" onclick="window.location.href='index.php'">Back</button>
    </form>

    <div class="register-link">
        No account? <a href="register.php">Sign up</a>
    </div>
</div>

</body>
</html>