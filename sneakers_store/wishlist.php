<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ── Add to wishlist (from product pages) ─────────────────────────────────────
if (isset($_POST['wishlist'], $_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    $check = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id=? AND product_id=?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        $ins = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $ins->bind_param("ii", $user_id, $product_id);
        $ins->execute();
    }
    header("Location: wishlist.php");
    exit();
}

// ── Remove from wishlist ──────────────────────────────────────────────────────
if (isset($_POST['remove_id'])) {
    $remove_id = (int)$_POST['remove_id'];
    $del = $conn->prepare("DELETE FROM wishlist WHERE user_id=? AND product_id=?");
    $del->bind_param("ii", $user_id, $remove_id);
    $del->execute();
    header("Location: wishlist.php");
    exit();
}

// ── Move to cart ──────────────────────────────────────────────────────────────
if (isset($_POST['add_to_cart_id'])) {
    $product_id = (int)$_POST['add_to_cart_id'];
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
    $del = $conn->prepare("DELETE FROM wishlist WHERE user_id=? AND product_id=?");
    $del->bind_param("ii", $user_id, $product_id);
    $del->execute();
    header("Location: cart.php");
    exit();
}

// ── Fetch wishlist items ──────────────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT p.product_id, p.product_name, p.price, p.image_url
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ── Cart item count for sidebar badge ────────────────────────────────────────
$items_in_cart = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $items_in_cart += is_array($qty) ? ($qty['quantity'] ?? 1) : (int)$qty;
    }
}

// ── Full name for sidebar ─────────────────────────────────────────────────────
$full_name = "Customer";
$s = $conn->prepare("SELECT full_name FROM users WHERE user_id=?");
$s->bind_param("i", $user_id);
$s->execute();
$s->bind_result($db_name);
if ($s->fetch()) $full_name = htmlspecialchars($db_name);
$s->close();

$wishlist_count = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | Sneakers Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .navbar { display: none !important; }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            margin: 0; padding: 0;
            background-color: #ffffff;
            display: flex;
            min-height: 100vh;
        }

    
        /* ── Main content ── */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 28px;
        }
        .page-header h1 { margin: 0; font-size: 26px; color: #222; }
        .page-header span { font-size: 14px; color: #888; }

        /* ── Empty state ── */
        .empty-state {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,.06);
            padding: 60px 20px;
            text-align: center;
            color: #aaa;
        }
        .empty-state p { font-size: 16px; margin: 0 0 16px; }
        .btn-browse {
            display: inline-block;
            padding: 10px 24px;
            background: #222;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .btn-browse:hover { background: #444; }

        /* ── Product grid ── */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 24px;
        }

        .product-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,.06);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s;
        }
        .product-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,.1); }

        .product-card img {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            display: block;
            background: #f5f5f5;
        }

        .card-body {
            padding: 14px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .card-body h3 {
            margin: 0 0 6px;
            font-size: 14px;
            color: #222;
            line-height: 1.4;
        }
        .card-body .price {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 14px;
        }

        .card-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }
        .btn-cart, .btn-remove {
            flex: 1;
            padding: 8px 0;
            border: none;
            border-radius: 5px;
            font-size: 13px;
            cursor: pointer;
            font-weight: bold;
            transition: background .15s;
        }
        .btn-cart   { background: #222; color: #fff; }
        .btn-cart:hover   { background: #444; }
        .btn-remove { background: #f8d7da; color: #721c24; }
        .btn-remove:hover { background: #f5c6cb; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>
<!-- ── Main content ── -->
<div class="main-content">

    <div class="page-header">
        <h1>My Wishlist</h1>
        <?php if ($wishlist_count > 0): ?>
            <span><?= $wishlist_count ?> item<?= $wishlist_count !== 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </div>

    <?php if ($wishlist_count === 0): ?>
        <div class="empty-state">
            <p>Your wishlist is empty.</p>
            <a href="browse.php" class="btn-browse">Browse Products</a>
        </div>

    <?php else: ?>
        <div class="product-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?= !empty($row['image_url'])
                    ? 'assets/images/' . htmlspecialchars($row['image_url'])
                    : 'assets/images/placeholder.jpg' ?>"
                     alt="<?= htmlspecialchars($row['product_name']) ?>">

                <div class="card-body">
                    <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                    <p class="price">RM <?= number_format($row['price'], 2) ?></p>

                    <div class="card-actions">
                        <form method="POST" action="wishlist.php">
                            <input type="hidden" name="add_to_cart_id" value="<?= $row['product_id'] ?>">
                            <button type="submit" class="btn-cart">Add to Cart</button>
                        </form>
                        <form method="POST" action="wishlist.php">
                            <input type="hidden" name="remove_id" value="<?= $row['product_id'] ?>">
                            <button type="submit" class="btn-remove">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

</div>

<?php $conn->close(); ?>
</body>
</html>