<?php 
include 'includes/header.php';
require_once 'includes/db_connect.php';
?>

<style>
    
    .hero-section {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 500px;
    background: url('assets/images/header.jpg') center center / cover no-repeat;
    position: relative;
    }

    .hero-img {
        display: none;
    }
    .hero-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 40px;
    }
    .hero-text h1 { font-size: 40px; margin: 0 0 10px 0; }
    
    .section-title { text-align: center; margin-bottom: 30px; font-size: 28px; }
    
    .product-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        max-width: 1200px;
        margin: 0 auto 50px auto;
        padding: 0 20px;
    }
    .product-card {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        text-decoration: none;
        color: #333;
        transition: transform 0.2s;
    }
    .product-card:hover { transform: translateY(-5px); }
    .product-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        margin-bottom: 15px;
        border-radius: 4px;
        background-color: #f9f9f9;
    }
    .price { font-weight: bold; color: #555; }
</style>

<div class="hero-section">
    <div class="hero-text">
        <h1>Never Stop<br>Inspiring</h1>
        <p>Explore the latest collections built for comfort, style, and performance.</p>
        <a href="browse.php" class="btn-primary">Shop Now</a>
    </div>
    <div class="hero-img"></div>
</div>

<h2 class="section-title">New Arrivals</h2>

<div class="product-grid">
    <?php
    // Fetch 3 latest products
    $sql = "SELECT product_id, product_name, price, image_url
            FROM products
            ORDER BY created_at DESC
            LIMIT 3";

    if ($result = $conn->query($sql)) {

        while ($row = $result->fetch_assoc()) {

            $img = !empty($row['image_url'])
                ? 'assets/images/' . htmlspecialchars($row['image_url'])
                : 'assets/images/placeholder.jpg';

            echo '<a href="product_details.php?id=' . $row['product_id'] . '" class="product-card">';

                echo '<div class="product-image-container">';
                    echo '<img src="' . $img . '" alt="' . htmlspecialchars($row['product_name']) . '" class="product-image">';
                echo '</div>';

                echo '<div class="product-info">';
                    echo '<h3>' . htmlspecialchars($row['product_name']) . '</h3>';
                    echo '<p class="price">RM ' . number_format($row['price'], 2) . '</p>';
                echo '</div>';

            echo '</a>';
        }

        $result->free();
    }
    ?>
</div>

</body>
</html>