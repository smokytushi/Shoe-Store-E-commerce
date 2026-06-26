<?php
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin')) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $sku = $conn->real_escape_string($_POST['sku']);
        $name = $conn->real_escape_string($_POST['product_name']);
        $desc = $conn->real_escape_string($_POST['product_description']);
        $cat_id = (int)$_POST['category_id'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock_quantity'];
        $img = $conn->real_escape_string($_POST['image_url']);
        
        $sql = "INSERT INTO products (sku, product_name, product_description, category_id, price, stock_quantity, image_url) 
                VALUES ('$sku', '$name', '$desc', $cat_id, $price, $stock, '$img')";
        $conn->query($sql);
    }
    
    if (isset($_POST['update_product'])) {
        $pid = (int)$_POST['product_id'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock_quantity'];
        
        $sql = "UPDATE products SET price = $price, stock_quantity = $stock WHERE product_id = $pid";
        $conn->query($sql);
    }
    
    if (isset($_POST['delete_product'])) {
        $pid = (int)$_POST['product_id'];
        $sql = "DELETE FROM products WHERE product_id = $pid";
        $conn->query($sql);
    }
}

$categories = [];
$cat_result = $conn->query("SELECT * FROM categories");
if ($cat_result) {
    while ($cat = $cat_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}
?>

<div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <h2>Admin Catalog Management</h2>
    
    <div style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <h3>Add New Product</h3>
        <form method="POST" action="admin_product.php" style="display: grid; gap: 15px; max-width: 600px;">
            <input type="hidden" name="add_product" value="1">
            
            <input type="text" name="sku" placeholder="SKU (e.g. SNK-001)" required style="padding: 10px;">
            <input type="text" name="product_name" placeholder="Product Name" required style="padding: 10px;">
            <textarea name="product_description" placeholder="Description" rows="3" style="padding: 10px;"></textarea>
            
            <select name="category_id" required style="padding: 10px;">
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <input type="number" step="0.01" name="price" placeholder="Price (RM)" required style="padding: 10px;">
            <input type="number" name="stock_quantity" placeholder="Stock Quantity" required style="padding: 10px;">
            <input type="text" name="image_url" placeholder="Image Filename (e.g. shoe1.jpg)" style="padding: 10px;">
            
            <button type="submit" class="btn-primary" style="padding: 10px; background-color: #333; color: white; border: none; cursor: pointer;">Add Product</button>
        </form>
    </div>
    
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <h3>Manage Existing Products</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
            <tr style="border-bottom: 2px solid #eee; text-align: left;">
                <th style="padding: 10px;">ID / SKU</th>
                <th style="padding: 10px;">Name</th>
                <th style="padding: 10px;">Image</th>
                <th style="padding: 10px;">Price / Stock</th>
                <th style="padding: 10px;">Actions</th>
            </tr>
            
            <?php
            $prod_result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
            if ($prod_result && $prod_result->num_rows > 0) {
                while ($row = $prod_result->fetch_assoc()) {
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;"><?php echo $row['product_id']; ?> <br> <small><?php echo htmlspecialchars($row['sku']); ?></small></td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td style="padding: 10px;">
                            <img src="assets/images/<?php echo htmlspecialchars($row['image_url']); ?>" style="width: 50px; height: 50px; object-fit: cover;" alt="img">
                        </td>
                        <td style="padding: 10px;">
                            <form method="POST" action="admin_product.php" style="display: flex; gap: 5px;">
                                <input type="hidden" name="update_product" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <input type="number" step="0.01" name="price" value="<?php echo $row['price']; ?>" style="width: 80px; padding: 5px;" title="Price">
                                <input type="number" name="stock_quantity" value="<?php echo $row['stock_quantity']; ?>" style="width: 60px; padding: 5px;" title="Stock">
                                <button type="submit" style="padding: 5px 10px; cursor: pointer; background: #4CAF50; color: white; border: none; border-radius: 3px;">Update</button>
                            </form>
                        </td>
                        <td style="padding: 10px;">
                            <form method="POST" action="admin_product.php" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" style="background-color: #ff4d4d; color: white; border: none; padding: 6px 12px; border-radius: 3px; cursor: pointer;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                echo '<tr><td colspan="5" style="padding: 15px;">No products found.</td></tr>';
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>