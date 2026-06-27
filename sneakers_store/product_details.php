<?php
include 'includes/header.php';
require_once 'includes/db_connect.php';

if (!isset($_GET['id'])) {
    die("Product not found.");
}

$product_id = intval($_GET['id']);

$sql = "SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.product_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

$main_img = !empty($product['image_url'])
          ? 'assets/images/' . htmlspecialchars($product['image_url'])
          : 'assets/images/placeholder.jpg';
?>

<style>
/* ── Page wrapper ── */
.page-wrap {
    width: 90%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 0 80px;
}

/* ── Product detail section ── */
.product-container {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 60px;
    align-items: start;
    margin-bottom: 80px;
}

.product-image img {
    width: 100%;
    max-height: 640px;
    object-fit: cover;
    border-radius: 15px;
    background: #f5f5f5;
    display: block;
}

.product-info {
    /* sticky only while shorter than viewport; removed to prevent overlap */
}

.category-label {
    color: #777;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.product-info h1 {
    font-size: 36px;
    margin: 10px 0 6px;
    line-height: 1.2;
}

.product-info .price {
    font-size: 32px;
    font-weight: bold;
    margin: 14px 0 20px;
}

.description {
    color: #555;
    line-height: 1.8;
    font-size: 15px;
    margin-bottom: 28px;
    border-top: 1px solid #eee;
    padding-top: 20px;
}

.qty {
    margin-bottom: 16px;
}

.qty label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}

.qty input {
    width: 90px;
    height: 42px;
    font-size: 17px;
    padding: 5px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
}

.cart-btn,
.wish-btn {
    display: block;
    width: 100%;
    padding: 15px;
    font-size: 18px;
    border-radius: 40px;
    cursor: pointer;
    text-align: center;
}

.cart-btn {
    background: #000;
    color: #fff;
    border: none;
    margin-top: 16px;
}

.cart-btn:hover { background: #333; }

.wish-btn {
    background: #fff;
    border: 1px solid #ccc;
    margin-top: 12px;
}

.wish-btn:hover { background: #f5f5f5; }

.stock-info {
    margin-top: 20px;
    color: green;
    font-size: 16px;
}

/* ── Divider ── */
.section-divider {
    border: none;
    border-top: 1px solid #eee;
    margin: 0 0 50px;
}

/* ── Related products ── */
.related-section h2 {
    font-size: 26px;
    margin-bottom: 28px;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 28px;
}

.product-card {
    background: #fff;
    border: 1px solid #eee;
    border-radius: 12px;
    overflow: hidden;
    transition: transform .2s, box-shadow .2s;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
}

.product-card a {
    text-decoration: none;
    color: #222;
    display: block;
}

.product-card img {
    width: 100%;
    height: 240px;
    object-fit: cover;
    display: block;
}

.product-card h3 {
    margin: 14px 15px 6px;
    font-size: 17px;
}

.product-card p {
    margin: 0 15px 18px;
    font-size: 16px;
    font-weight: bold;
    color: #333;
}

/* ── Responsive ── */
@media (max-width: 900px) {
    .product-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}
</style>

<div class="page-wrap">

    <!-- ── Product detail ── -->
    <div class="product-container">

        <div class="product-image">
            <img src="<?= $main_img ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">
        </div>

        <div class="product-info">

            <span class="category-label"><?= htmlspecialchars($product['category_name']) ?></span>

            <h1><?= htmlspecialchars($product['product_name']) ?></h1>

            <div class="price">RM <?= number_format($product['price'], 2) ?></div>

            <p class="description">
                <?= nl2br(htmlspecialchars($product['product_description'])) ?>
            </p>

            <form action="cart.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                <div class="qty">
                    <label for="qty-input">Quantity</label>
                    <input id="qty-input" type="number" name="quantity"
                           value="1" min="1" max="<?= intval($product['stock_quantity']) ?>">
                </div>

                <button class="cart-btn" type="submit" name="add_cart">Add to Cart</button>
            </form>

            <form action="wishlist.php" method="POST">
                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                <button class="wish-btn" type="submit" name="wishlist">♡ Add to Wishlist</button>
            </form>

            <p class="stock-info">
                <strong>In Stock:</strong> <?= intval($product['stock_quantity']) ?> pairs
            </p>

        </div>
    </div>

    <!-- ── Divider ── -->
    <hr class="section-divider">

    <!-- ── Related products ── -->
    <div class="related-section">
        <h2>You May Also Like</h2>

        <div class="product-grid">
            <?php
            $related = $conn->prepare("
                SELECT product_id, product_name, price, image_url
                FROM products
                WHERE category_id = ? AND product_id != ?
                LIMIT 4
            ");
            $related->bind_param("ii", $product['category_id'], $product['product_id']);
            $related->execute();
            $rel_result = $related->get_result();

            while ($row = $rel_result->fetch_assoc()):
                $rel_img = !empty($row['image_url'])
                         ? 'assets/images/' . htmlspecialchars($row['image_url'])
                         : 'assets/images/placeholder.jpg';
            ?>
            <div class="product-card">
                <a href="product_details.php?id=<?= $row['product_id'] ?>">
                    <img src="<?= $rel_img ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                    <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                    <p>RM <?= number_format($row['price'], 2) ?></p>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

</div>

</body>
</html>