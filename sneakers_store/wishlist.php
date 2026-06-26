<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['remove_id'])) {
    $remove_id = $_POST['remove_id'];
    $delete_sql = "DELETE FROM wishlist WHERE user_id = '$user_id' AND product_id = '$remove_id'";
    $conn->query($delete_sql);
}

if (isset($_POST['add_to_cart_id'])) {
    $product_id = $_POST['add_to_cart_id'];
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += 1;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    
    $delete_sql = "DELETE FROM wishlist WHERE user_id = '$user_id' AND product_id = '$product_id'";
    $conn->query($delete_sql);
    
    header("Location: cart.php");
    exit();
}

$sql = "SELECT p.product_id, p.product_name, p.price, p.image_url FROM wishlist w JOIN products p ON w.product_id = p.product_id WHERE w.user_id = '$user_id'";
$result = $conn->query($sql);
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2>My Wishlist</h2>
    
    <div class="product-grid">
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <div class="product-card">
                    <img src="assets/images/<?php echo htmlspecialchars($row['image_url']); ?>" class="product-image" alt="Product Image">
                    <h3><?php echo htmlspecialchars($row['product_name']); ?></h3>
                    <p class="price">RM <?php echo number_format($row['price'], 2); ?></p>
                    
                    <div style="margin-top: 15px; display: flex; justify-content: center; gap: 10px;">
                        <form method="POST" action="wishlist.php">
                            <input type="hidden" name="add_to_cart_id" value="<?php echo $row['product_id']; ?>">
                            <button type="submit" style="background-color: #4CAF50; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Add to Cart</button>
                        </form>
                        
                        <form method="POST" action="wishlist.php">
                            <input type="hidden" name="remove_id" value="<?php echo $row['product_id']; ?>">
                            <button type="submit" style="background-color: #ff4d4d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Remove</button>
                        </form>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<p>Your wishlist is empty.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>
