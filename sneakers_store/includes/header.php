<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="assets/css/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HYPE Sneakers Store</title>
    <style>
       
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            padding: 15px 50px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .logo a {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            text-decoration: none;
        }
        .nav-links a {
            margin: 0 15px;
            text-decoration: none;
            color: #555;
            font-weight: bold;
        }
        .nav-links a:hover { color: #000; }
        
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .search-bar {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 20px;
            outline: none;
        }
        .btn-icon {
            text-decoration: none;
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">
        <a href="index.php">HYPE Sneakers Store</a>
    </div>
    
    <div class="nav-links">
        <a href="browse.php">All</a>
        <a href="browse.php?category=Men's Sneakers">Men</a>
        <a href="browse.php?category=Women's Sneakers">Women</a>
        <a href="browse.php?category=Kids">Kids</a>
    </div>
    
    <div class="nav-actions">
        <form action="browse.php" method="GET" class="search-form">
            <input
                type="text"
                name="search"
                placeholder="Search shoes..."
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
            >

            <button type="submit">
                Search
            </button>
        </form>
        
        <a href="wishlist.php" class="btn-icon">♡</a>
        <a href="cart.php" class="btn-icon">🛒</a>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <?php if($_SESSION['role'] === 'customer'): ?>
                <a href="user_dashboard.php" class="btn-icon">Account</a>
            <?php else: ?>
                <a href="admin_dashboard.php" class="btn-icon">Admin</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="btn-icon">Log In</a>
        <?php endif; ?>
    </div>
</div>