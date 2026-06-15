<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'system_admin')) {
    header("Location: login.php");
    exit();
}

require_once 'includes/db_connect.php';

$success_msg = '';
$error_msg = '';

// Handle Product Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $product_name = htmlspecialchars(trim($_POST['product_name']));
    $description = htmlspecialchars(trim($_POST['description']));
    $category = htmlspecialchars(trim($_POST['category']));
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    
    // Generate a random SKU for the product (e.g., SNK-12345)
    $sku = 'SNK-' . rand(10000, 99999);

    // Handle File Upload
    $image_path = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = $_FILES['product_image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/';
            // Create a unique filename to prevent overwriting
            $file_name = time() . '_' . basename($_FILES['product_image']['name']);
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $error_msg = "Failed to upload image. Ensure the 'uploads' folder exists and has write permissions.";
            }
        } else {
            $error_msg = "Invalid file type. Only JPG, PNG, and WEBP are allowed.";
        }
    }

    // Insert into Database if no errors
    if (empty($error_msg)) {
        $sql = "INSERT INTO products (sku, product_name, product_description, category, price, stock_quantity, image_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
                
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssdis", $sku, $product_name, $description, $category, $price, $stock, $image_path);
            
            if ($stmt->execute()) {
                $success_msg = "Product successfully added to the catalog!";
            } else {
                $error_msg = "Database error: Could not save the product.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products | Sneakers Store</title>
    <style>
        /* Vanilla CSS Architecture */
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

        /* Main Content & Grid Layout matching mockup */
        .main-content {
            flex: 1;
            padding: 40px;
        }
        
        .grid-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .panel {
            background-color: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .panel h2 {
            margin-top: 0;
            font-size: 18px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #555;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: #f9f9f9;
        }
        textarea {
            height: 80px;
            resize: vertical;
        }

        /* Buttons */
        .btn-submit {
            background-color: #222;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            width: 100%;
            margin-bottom: 10px;
        }
        .btn-submit:hover {
            background-color: #444;
        }
        .btn-back {
            background-color: #555;
        }
        .btn-back:hover {
            background-color: #777;
        }

        /* Alerts */
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

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-logo">Sneakers Store</div>
        <a href="admin_dashboard.php">Inventory Dashboard</a>
        <a href="admin_product.php" class="active">Manage Products</a>
        <a href="admin_deliveries.php">Order Management</a>
        <a href="admin_customers.php">Customers</a>
        <a href="admin_notifications.php">Marketing</a>
        <a href="logout.php" style="margin-top: auto; border-top: 1px solid #eee;">Log Out</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <?php if(!empty($success_msg)): ?>
            <div class="message success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error_msg)): ?>
            <div class="message error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Note the enctype attribute is REQUIRED for file uploads -->
        <form action="admin_product.php" method="POST" enctype="multipart/form-data">
            <div class="grid-layout">
                
                <!-- Left Column: General Info & Price/Stock -->
                <div>
                    <div class="panel" style="margin-bottom: 20px;">
                        <h2>General Information</h2>
                        
                        <div class="form-group">
                            <label for="product_name">Sneaker Name</label>
                            <input type="text" id="product_name" name="product_name" placeholder="e.g. Nike Air Force 1" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Item Description</label>
                            <textarea id="description" name="description" placeholder="Classic white sneakers with details..." required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="category">Type</label>
                            <select id="category" name="category" required>
                                <option value="Men's Sneakers">Men's Sneakers</option>
                                <option value="Women's Sneakers">Women's Sneakers</option>
                                <option value="Kids">Kids</option>
                                <option value="Unisex">Unisex</option>
                            </select>
                        </div>
                    </div>

                    <div class="panel">
                        <h2>Price and Stock</h2>
                        <div style="display: flex; gap: 20px;">
                            <div class="form-group" style="flex: 1;">
                                <label for="price">Price (RM)</label>
                                <input type="number" id="price" name="price" step="0.01" placeholder="350.00" required>
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label for="stock">Stock Quantity</label>
                                <input type="number" id="stock" name="stock" placeholder="100" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Image Upload & Actions -->
                <div class="panel">
                    <h2>Item Picture</h2>
                    <div class="form-group">
                        <input type="file" id="product_image" name="product_image" accept="image/png, image/jpeg, image/webp" required>
                    </div>

                    <div style="margin-top: 40px;">
                        <button type="submit" class="btn-submit">Save Changes</button>
                        <button type="button" class="btn-submit btn-back" onclick="window.location.href='admin_dashboard.php'">Back</button>
                    </div>
                </div>

            </div>
        </form>
    </div>

</body>
</html>