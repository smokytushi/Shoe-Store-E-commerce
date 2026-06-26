<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password is an empty string
$dbname = "sneakers_store";

// Enable error reporting for MySQLi (Helps with debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Set character set to utf8mb4 for security and emoji support
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // If connection fails, stop the script and display a generic error
    error_log($e->getMessage());
    die("Database connection failed. Please contact the system administrator.");
}
?>
