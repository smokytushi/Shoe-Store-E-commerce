<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['action']) && $_POST['action'] === 'remove' && isset($_POST['product_id'])) {
    $pid = $_POST['product_id'];
    unset($_SESSION['cart'][$pid]);
}

if (isset($_POST['action']) && $_POST['action'] === 'update' && isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $pid = $_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    if ($qty > 0) {
        $_SESSION['cart'][$pid] = $qty;
    } else {
        unset($_SESSION['cart'][$pid]);
    }
}
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2>My Shopping Cart</h2>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <tr style="border-bottom: 2px solid #eee; text-align: left;">
                <th style="padding: 15px;">Image</th>
                <th style="padding: 15px;">Product</th>
                <th style="padding: 15px;">Price</th>
                <th style="padding: 15px;">Quantity</th>
                <th style="padding: 15px;">Total</th>
                <th style="padding: 15px;">Action</th>
            </tr>
            
            <?php
            $grand_total = 0;
            $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
            
            if (!empty($ids)) {
                $sql = "SELECT product_id, product_name, price, image_url FROM products WHERE product_id IN ($ids)";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $pid = $row['product_id'];
                        $qty = $_SESSION['cart'][$pid];
                        $price = $row['price'];
                        $subtotal = $price * $qty;
                        $grand_total += $subtotal;
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px;">
                                <img src="assets/images/<?php echo htmlspecialchars($row['image_url']); ?>" class="product-image" style="width: 80px; height: 80px; object-fit: cover;" alt="Product">
                            </td>
                            <td style="padding: 15px;"><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td style="padding: 15px;" class="item-price" data-price="<?php echo $price; ?>">RM <?php echo number_format($price, 2); ?></td>
                            <td style="padding: 15px;">
                                <form method="POST" action="cart.php" style="display:inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                    <input type="number" name="quantity" value="<?php echo $qty; ?>" min="1" class="qty-input" style="width: 60px; padding: 5px;">
                                    <button type="submit" style="padding: 5px 10px; cursor: pointer;">Update</button>
                                </form>
                            </td>
                            <td style="padding: 15px;" class="item-subtotal">RM <?php echo number_format($subtotal, 2); ?></td>
                            <td style="padding: 15px;">
                                <form method="POST" action="cart.php" style="display:inline;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                    <button type="submit" style="background-color: #ff4d4d; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }
            ?>
            
            <tr>
                <td colspan="4" style="padding: 15px; text-align: right; font-weight: bold;">Grand Total:</td>
                <td colspan="2" style="padding: 15px; font-weight: bold; font-size: 1.2em;" id="grand-total">RM <?php echo number_format($grand_total, 2); ?></td>
            </tr>
        </table>
        
        <div style="text-align: right; margin-top: 20px;">
            <a href="checkout.php" class="btn-primary" style="padding: 10px 20px; background-color: #333; color: white; text-decoration: none; border-radius: 4px;">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.qty-input').forEach(function(input) {
    input.addEventListener('input', function() {
        var row = this.closest('tr');
        var priceText = row.querySelector('.item-price').getAttribute('data-price');
        var price = parseFloat(priceText);
        var qty = parseInt(this.value);
        
        if (qty > 0) {
            var subtotal = price * qty;
            row.querySelector('.item-subtotal').innerHTML = 'RM ' + subtotal.toFixed(2);
            
            var newGrandTotal = 0;
            document.querySelectorAll('.qty-input').forEach(function(inp) {
                var p = parseFloat(inp.closest('tr').querySelector('.item-price').getAttribute('data-price'));
                var q = parseInt(inp.value);
                if(q > 0) {
                    newGrandTotal += (p * q);
                }
            });
            document.getElementById('grand-total').innerHTML = 'RM ' + newGrandTotal.toFixed(2);
        }
    });
});
</script>

</body>
</html>
