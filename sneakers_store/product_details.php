<?php
include 'includes/header.php';
require_once 'includes/db_connect.php';

if(!isset($_GET['id'])){
    die("Product not found.");
}

$product_id = intval($_GET['id']);

$sql = "SELECT p.*, c.category_name
        FROM products p
        LEFT JOIN categories c
        ON p.category_id = c.category_id
        WHERE p.product_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$product_id);
$stmt->execute();

$product = $stmt->get_result()->fetch_assoc();

if(!$product){
    die("Product not found.");
}
?>

<style>
.product-container{
    width:90%;
    margin:auto;
    display:flex;
    gap:60px;
    padding:50px 0;
}

.product-image{
    flex:1;
}

.product-image img{
    width:100%;
    border-radius:15px;
    background:#f7f7f7;
}

.product-info{
    width:430px;
}

.category{
    color:#888;
    font-size:18px;
}

.product-info h1{
    font-size:40px;
    margin:10px 0;
}

.product-info h2{
    font-size:34px;
    margin:20px 0;
}

.description{
    color:#555;
    line-height:1.8;
    margin-bottom:25px;
}

.sizes{
    margin:25px 0;
}

.sizes button{
    width:55px;
    height:55px;
    margin-right:8px;
    border:1px solid #ccc;
    background:white;
    cursor:pointer;
    border-radius:8px;
}

.sizes button:hover{
    background:black;
    color:white;
}

.qty{
    margin:20px 0;
}

.qty input{
    width:90px;
    padding:10px;
    font-size:18px;
}

.cart-btn{

    width:100%;
    padding:18px;
    background:black;
    color:white;
    border:none;
    border-radius:50px;
    font-size:18px;
    cursor:pointer;
    margin-top:20px;
}

.cart-btn:hover{
    background:#222;
}

.wish-btn{

    width:100%;
    margin-top:15px;
    padding:18px;
    background:white;
    border:1px solid #bbb;
    border-radius:50px;
    font-size:18px;
    cursor:pointer;
}

.wish-btn:hover{
    background:#f2f2f2;
}

.stock{
    margin-top:20px;
    color:green;
    font-size:18px;
}
</style>

<body>
<div class="product-container">

    <div class="product-image">

        <img src="assets/images/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['product_name']) ?>">

    </div>

    <div class="product-info">

        <span class="category">
            <?= htmlspecialchars($product['category_name']) ?>
        </span>

        <h1><?= htmlspecialchars($product['product_name']) ?></h1>

        <h2>RM <?= number_format($product['price'],2) ?></h2>

        <p class="description">
            <?= nl2br(htmlspecialchars($product['product_description'])) ?>
        </p>

        <form action="cart.php" method="POST">

            <input type="hidden"
                   name="product_id"
                   value="<?= $product['product_id'] ?>">

            <div class="qty">

                <label>Quantity</label>

                <input type="number"
                       name="quantity"
                       value="1"
                       min="1"
                       max="<?= $product['stock_quantity'] ?>">

            </div>

            <button class="cart-btn"
                    type="submit"
                    name="add_cart">
                Add to Cart
            </button>

        </form>

        <form action="wishlist.php" method="POST">

            <input type="hidden"
                   name="product_id"
                   value="<?= $product['product_id'] ?>">

            <button class="wish-btn"
                    type="submit"
                    name="wishlist">
                ♡ Add to Wishlist
            </button>

        </form>

        <p class="stock">

            <strong>Stock:</strong>

            <?= $product['stock_quantity'] ?>

        </p>

    </div>

</div>

</body>
</html>