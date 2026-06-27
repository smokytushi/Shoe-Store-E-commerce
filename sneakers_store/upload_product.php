<?php
include 'includes/header.php';
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success = "";
$error   = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim($_POST['product_name']  ?? '');
    $description = trim($_POST['description']   ?? '');
    $price       = floatval($_POST['price']      ?? 0);
    $stock       = intval($_POST['stock']        ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);

    // Image upload 
    $filename = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $tmp      = $_FILES['image']['tmp_name'];
        $original = basename($_FILES['image']['name']);
        $ext      = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, WEBP or GIF images are allowed.";
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $error = "Image must be under 2 MB.";
        } else {
            // Slug from product name + short unique suffix
            $slug     = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
            $uid      = substr(uniqid(), -6);
            $filename = $slug . '_' . $uid . '.' . $ext;
            $dest     = 'assets/images/' . $filename;

            if (!move_uploaded_file($tmp, $dest)) {
                $error    = "Failed to save image. Make sure assets/images/ folder exists and is writable.";
                $filename = "";
            }
        }

    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $error = "Upload error code: " . $_FILES['image']['error'];
    }

    //  Insert into DB  stores ONLY the filename
    if ($error === '' && $name !== '' && $price > 0) {

        $stmt = $conn->prepare(
            "INSERT INTO products
             (product_name, product_description, price, stock_quantity, category_id, image_url, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $category_id, $filename);

        if ($stmt->execute()) {
            $success = "Product \"" . htmlspecialchars($name) . "\" added! Image saved as: " . $filename;
        } else {
            $error = "Database error: " . $stmt->error;
            // Roll back uploaded file if DB insert failed
            if ($filename && file_exists('assets/images/' . $filename)) {
                unlink('assets/images/' . $filename);
            }
        }
        $stmt->close();

    } elseif ($error === '') {
        $error = "Product name and a valid price are required.";
    }
}

$categories = $conn->query("SELECT category_id, category_name FROM categories ORDER BY category_name");
?>

<style>
.upload-wrap { max-width: 620px; margin: 40px auto; padding: 0 20px; }
.upload-wrap h2 { margin-bottom: 24px; }
.form-group { margin-bottom: 18px; display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-weight: 600; font-size: 14px; }
.form-group input,
.form-group textarea,
.form-group select {
    padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px;
    font-size: 15px; width: 100%; box-sizing: border-box;
}
.form-group textarea { resize: vertical; min-height: 90px; }
#img-preview {
    max-width: 200px; max-height: 200px; object-fit: cover;
    border-radius: 6px; border: 1px solid #ddd;
    margin-top: 10px; display: none;
}
.msg-success { background:#e6f4ea; color:#2a7a2a; padding:12px 16px; border-radius:6px; margin-bottom:20px; }
.msg-error   { background:#fdecea; color:#c0392b; padding:12px 16px; border-radius:6px; margin-bottom:20px; }
.btn-submit  { background:#333; color:#fff; border:none; padding:12px 28px; border-radius:6px; font-size:15px; cursor:pointer; width:100%; }
.btn-submit:hover { background:#555; }
</style>

<div class="upload-wrap">
    <h2>Add New Product</h2>

    <?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="msg-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" action="upload_product.php" enctype="multipart/form-data">

        <div class="form-group">
            <label>Product Name *</label>
            <input type="text" name="product_name"
                   value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>"
                   placeholder="e.g. Nike Air Max 270" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" placeholder="Short product description..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Price (RM) *</label>
            <input type="number" name="price" step="0.01" min="0"
                   value="<?= htmlspecialchars($_POST['price'] ?? '') ?>"
                   placeholder="e.g. 299.90" required>
        </div>

        <div class="form-group">
            <label>Stock Quantity</label>
            <input type="number" name="stock" min="0"
                   value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>">
        </div>

        <div class="form-group">
            <label>Category</label>
            <select name="category_id">
                <option value="0">— Select category —</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['category_id'] ?>"
                        <?= (($_POST['category_id'] ?? '') == $cat['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['category_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Product Image (JPG / PNG / WEBP, max 2 MB)</label>
            <input type="file" name="image" accept="image/*">
            <img id="img-preview" src="" alt="Preview">
        </div>

        <button type="submit" class="btn-submit">Add Product</button>
    </form>
</div>

<script>
document.querySelector('input[name="image"]').addEventListener('change', function () {
    const preview = document.getElementById('img-preview');
    const file    = this.files[0];
    if (file) {
        preview.src          = URL.createObjectURL(file);
        preview.style.display = 'block';
        preview.onload       = () => URL.revokeObjectURL(preview.src);
    } else {
        preview.style.display = 'none';
    }
});
</script>

</body>
</html>