<?php
// Start a session to handle error/success messages
session_start();

// Include your database connection
require_once 'includes/db_connect.php';

// Initialize variables to hold error or success messages
$error_msg = '';
$success_msg = '';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize and collect inputs
    // trim() removes extra spaces, htmlspecialchars() prevents XSS attacks
    $username = htmlspecialchars(trim($_POST['username']));
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone_number']));
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $password = $_POST['password']; // We don't sanitize the password before hashing

    // 2. Server-Side Validation (Rubric Requirement)
    if (empty($username) || empty($full_name) || empty($email) || empty($password)) {
        $error_msg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } else {
        // 3. Security: Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 4. Database Insertion using PREPARED STATEMENTS (SQL Injection Protection)
        // Notice the '?' placeholders. This tells MySQL to treat input as data, not executable code.
        $sql = "INSERT INTO users (username, full_name, email, phone_number, gender, dob, password_hash, role) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'customer')";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind the variables to the '?' placeholders
            // "sssssss" means we are binding 7 Strings
            $stmt->bind_param("sssssss", $username, $full_name, $email, $phone, $gender, $dob, $hashed_password);
            
            // Execute the statement
            if ($stmt->execute()) {
                $success_msg = "Registration successful! You can now log in.";
                // Optional: Redirect to login page after 2 seconds
                // header("refresh:2;url=login.php"); 
            } else {
                // Check for duplicate email/username (MySQL Error 1062)
                if ($conn->errno == 1062) {
                    $error_msg = "That email or username is already registered.";
                } else {
                    $error_msg = "Something went wrong. Please try again later.";
                }
            }
            $stmt->close();
        } else {
            $error_msg = "Database error: Could not prepare statement.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Sneakers Store</title>
    <style>
        /* Vanilla CSS - No Frameworks Used */
        body {
            font-family: Arial, sans-serif;
            background-color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .register-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #333;
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
        input[type="email"],
        input[type="password"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; /* Ensures padding doesn't affect width */
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
        }
        .btn-submit:hover {
            background-color: #444;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .error { background-color: #ffcccc; color: #cc0000; }
        .success { background-color: #ccffcc; color: #006600; }
        .login-link {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .login-link a { color: #333; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="register-container">
    <h2>Registration</h2>

    <?php if(!empty($error_msg)): ?>
        <div class="message error"><?php echo $error_msg; ?></div>
    <?php endif; ?>
    
    <?php if(!empty($success_msg)): ?>
        <div class="message success"><?php echo $success_msg; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="phone_number">Phone Number</label>
            <input type="text" id="phone_number" name="phone_number" required>
        </div>
        
        <div class="form-group">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" required>
                <option value="" disabled selected>Select your gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn-submit">Sign Up</button>
    </form>

    <div class="login-link">
        Already have an account? <a href="login.php">Log in</a>
    </div>
</div>

</body>
</html>