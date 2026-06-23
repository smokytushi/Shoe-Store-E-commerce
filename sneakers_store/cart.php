<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2>My Shopping Cart</h2>
    
    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <tr style="border-bottom: 2px solid #eee; text-align: left;">
            <th style="padding: 15px;">Product</th>
            <th style="padding: 15px;">Price</th>
            <th style="padding: 15px;">Quantity</th>
            <th style="padding: 15px;">Total</th>
            <th style="padding: 15px;">Action</th>
        </tr>
        <tr>
            <td>
                
            </td>
        </tr>
    </table>
</div>

</body>
</html>
