<?php include 'header.php'; ?>

<style>
    .browse-container {
        display: flex;
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        gap: 30px;
    }
    .sidebar-filters {
        width: 250px;
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        height: fit-content;
    }
    .filter-group { margin-bottom: 20px; }
    .filter-group h3 { margin-top: 0; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
    .filter-group a { display: block; color: #555; text-decoration: none; margin-bottom: 8px; }
    .filter-group a:hover { color: #000; font-weight: bold; }
    
    .products-area { flex: 1; }
    .page-title { margin-top: 0; font-size: 24px; margin-bottom: 20px; }
    
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    .card {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .card img { width: 100%; height: 200px; object-fit: cover; border-radius: 4px; }
    .card h4 { margin: 10px 0 5px 0; font-size: 16px; }
    .card p { color: #555; font-weight: bold; margin: 0 0 15px 0; }
    
    .btn-buy {
        display: inline-block;
        background: #222;
        color: #fff;
        padding: 8px 15px;
        text-decoration: none;
        border-radius: 4px;
        font-size: 14px;
    }
    .btn-buy:hover { background: #444; }
</style>

<div class="browse-container">
    <div class="sidebar-filters">
        <div class="filter-group">
            <h3>Categories</h3>
            <a href="browse.php">All Sneakers</a>
            <a href="browse.php?category=Men's Sneakers">Men's Sneakers</a>
            <a href="browse.php?category=Women's Sneakers">Women's Sneakers</a>
            <a href="browse.php?category=Kids">Kids</a>
        </div>
    </div>

    <div class="products-area">
        <?php
        // Determine search and filter parameters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        
        echo '<h1 class="page-title">';
        if ($search) echo 'Search Results for "' . htmlspecialchars($search) . '"';
        elseif ($category) echo htmlspecialchars($category);
        else echo 'All Sneakers';
        echo '</h1>';
        
        // Secure query building with Prepared Statements
        $sql = "SELECT * FROM products WHERE product_name LIKE ? AND category LIKE ? ORDER BY created_at DESC";
        
        if ($stmt = $conn->prepare($sql)) {
            $search_param = "%" . $search . "%";
            $category_param = "%" . $category . "%";
            
            $stmt->bind_param("ss", $search_param, $category_param);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                echo '<div class="grid">';
                while ($row = $result->fetch_assoc()) {
                    $img = !empty($row['image_url']) ? htmlspecialchars($row['image_url']) : 'https://via.placeholder.com/300x200?text=No+Image';
                    echo '<div class="card">';
                    echo '<img src="' . $img . '" alt="Product">';
                    echo '<h4>' . htmlspecialchars($row['product_name']) . '</h4>';
                    echo '<p>RM ' . number_format($row['price'], 2) . '</p>';
                    // Link to details page
                    echo '<a href="product_details.php?id=' . $row['product_id'] . '" class="btn-buy">View Details</a>';
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<p>No products found matching your criteria.</p>';
            }
            $stmt->close();
        }
        ?>
    </div>
</div>

</body>
</html>