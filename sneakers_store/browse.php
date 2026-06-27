<?php

include 'includes/header.php';
require_once 'includes/db_connect.php';

$search   = $_GET['search']   ?? '';
$category = $_GET['category'] ?? '';
$sort     = $_GET['sort']     ?? 'newest';

// Fetch categories for sidebar
$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");

// Build product query
$sql = "
    SELECT p.*, c.category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    WHERE 1=1
";

$params = [];
$types  = "";

if ($search !== "") {
    $sql .= " AND (
        p.product_name        LIKE ? OR
        p.product_description LIKE ? OR
        c.category_name       LIKE ?
    )";
    $keyword  = "%" . $search . "%";
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $types   .= "sss";
}

if ($category !== "") {
    $sql    .= " AND p.category_id = ?";
    $params[] = $category;
    $types   .= "i";
}

switch ($sort) {
    case "price_low":  $sql .= " ORDER BY p.price ASC";         break;
    case "price_high": $sql .= " ORDER BY p.price DESC";        break;
    case "name":       $sql .= " ORDER BY p.product_name ASC";  break;
    default:           $sql .= " ORDER BY p.created_at DESC";
}

$stmt = $conn->prepare($sql);
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>


<style>
.catalogue{
display:grid;
grid-template-columns:250px 1fr;
gap:40px;
max-width:1400px;
margin:auto;
padding:30px;
}

.sidebar h3{
margin-bottom:20px;
}

.sidebar a{
display:block;
padding:10px 0;
text-decoration:none;
color:#222;
}

.sidebar a:hover{
font-weight:bold;
}

.toolbar{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:30px;
}

.search-box{

display:flex;
gap:10px;

}

.search-box input{

padding:10px;
width:280px;

}

.product-grid{

display:grid;
grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
gap:25px;

}

.product-card{

text-decoration:none;
color:#111;

}

.product-card img{

width:100%;
height:320px;
object-fit:cover;
background:#f5f5f5;

}

.category{

color:#777;
margin-top:10px;

}

.price{

font-weight:bold;
font-size:18px;

}
</style>

<div class="catalogue">

  <div class="sidebar">
    <h3>Categories</h3>
    <a href="browse.php">All Products</a>
    <?php while ($cat = $categories->fetch_assoc()): ?>
      <a href="browse.php?category=<?= $cat['category_id'] ?>">
        <?= htmlspecialchars($cat['category_name']) ?>
      </a>
    <?php endwhile; ?>
  </div>

  <div>
    <div class="toolbar">

      <form class="search-box" method="GET">
        <input type="text" name="search"
               placeholder="Search shoes..."
               value="<?= htmlspecialchars($search) ?>">
        <?php if ($category !== ""): ?>
          <input type="hidden" name="category" value="<?= $category ?>">
        <?php endif; ?>
        <button>Search</button>
      </form>

      <form method="GET">
        <input type="hidden" name="search"   value="<?= htmlspecialchars($search) ?>">
        <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
        <select name="sort" onchange="this.form.submit()">
          <option value="newest"     <?= $sort === "newest"     ? "selected" : "" ?>>Newest</option>
          <option value="price_low"  <?= $sort === "price_low"  ? "selected" : "" ?>>Price Low → High</option>
          <option value="price_high" <?= $sort === "price_high" ? "selected" : "" ?>>Price High → Low</option>
          <option value="name"       <?= $sort === "name"       ? "selected" : "" ?>>Name A-Z</option>
        </select>
      </form>

    </div>

    <div class="product-grid">
      <?php while ($row = $result->fetch_assoc()):
        $image = !empty($row['image_url'])
               ? 'assets/images/' . htmlspecialchars($row['image_url'])
               : 'assets/images/placeholder.jpg';
      ?>
        <a class="product-card" href="product_details.php?id=<?= $row['product_id'] ?>">
          <img src="<?= $image ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
          <div class="category"><?= htmlspecialchars($row['category_name']) ?></div>
          <h3><?= htmlspecialchars($row['product_name']) ?></h3>
          <div class="price">RM <?= number_format($row['price'], 2) ?></div>
        </a>
      <?php endwhile; ?>
    </div>

  </div>
</div>

</body>
</html>