<?php
session_start();
require_once 'includes/db_connect.php';

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Products | Sneakers Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar { display: none !important; }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fefff1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }


        /* ── Body Layout ── */
        .page-wrapper {
            display: flex;
            flex: 1;
        }

        .main-content {
            flex: 1;
            padding: 36px 40px;
        }
        .page-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 24px;
            color: #222;
        }

        /* ── Panels ── */
        .panel {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-bottom: 28px;
        }
        .panel h3 {
            margin: 0 0 18px;
            font-size: 16px;
            color: #333;
        }

        /* ── Form ── */
        .add-form {
            display: grid;
            gap: 12px;
            max-width: 560px;
        }
        .add-form input,
        .add-form textarea,
        .add-form select {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }
        .add-form textarea { resize: vertical; }
        .btn-primary {
            padding: 11px;
            background-color: #222;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-primary:hover { background-color: #444; }

        /* ── Products Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 11px 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        th {
            color: #777;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #eee;
        }
        .inline-form {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .inline-form input[type="number"] {
            padding: 5px 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
        .btn-update {
            padding: 5px 12px;
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-update:hover { background: #3d9140; }
        .btn-delete {
            padding: 5px 12px;
            background: #e53935;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn-delete:hover { background: #b71c1c; }
        .product-img {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        small { color: #888; }
    </style>
</head>
<body>
    <div class="page-wrapper">
    <?php include 'includes/sidebar.php'; ?>
        <!-- ── Main Content ── -->
        <div class="main-content">
            <h1 class="page-title">Admin Catalog Management</h1>

            <!-- Add Product -->
            <div class="panel">
                <h3>Add New Product</h3>
                <form method="POST" action="admin_product.php" class="add-form">
                    <input type="hidden" name="add_product" value="1">
                    <input type="text" name="sku" placeholder="SKU (e.g. SNK-001)" required>
                    <input type="text" name="product_name" placeholder="Product Name" required>
                    <textarea name="product_description" placeholder="Description" rows="3"></textarea>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['category_id']; ?>">
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" step="0.01" name="price" placeholder="Price (RM)" required>
                    <input type="number" name="stock_quantity" placeholder="Stock Quantity" required>
                    <input type="text" name="image_url" placeholder="Image Filename (e.g. shoe1.jpg)">
                    <button type="submit" class="btn-primary">Add Product</button>
                </form>
            </div>

            <!-- Manage Products -->
            <div class="panel">
                <h3>Manage Existing Products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID / SKU</th>
                            <th>Name</th>
                            <th>Image</th>
                            <th>Price / Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $prod_result = $conn->query("SELECT * FROM products ORDER BY product_id DESC");
                    if ($prod_result && $prod_result->num_rows > 0):
                        while ($row = $prod_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php echo $row['product_id']; ?><br>
                                <small><?php echo htmlspecialchars($row['sku']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                            <td>
                                <img src="assets/images/<?php echo htmlspecialchars($row['image_url']); ?>"
                                     class="product-img" alt="product image">
                            </td>
                            <td>
                                <form method="POST" action="admin_product.php" class="inline-form">
                                    <input type="hidden" name="update_product" value="1">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <input type="number" step="0.01" name="price"
                                           value="<?php echo $row['price']; ?>" style="width:80px;" title="Price">
                                    <input type="number" name="stock_quantity"
                                           value="<?php echo $row['stock_quantity']; ?>" style="width:60px;" title="Stock">
                                    <button type="submit" class="btn-update">Update</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="admin_product.php"
                                      onsubmit="return confirm('Delete this product?');">
                                    <input type="hidden" name="delete_product" value="1">
                                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile;
                    else: ?>
                        <tr><td colspan="5" style="text-align:center; color:#888;">No products found.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /main-content -->
    </div><!-- /page-wrapper -->

<?php $conn->close(); ?>
</body>
</html>