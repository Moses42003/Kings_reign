<?php
// get_category_products.php
include('db.php');

// Get all categories
$catRes = mysqli_query($conn, "SELECT c.id, c.name FROM categories c WHERE c.is_active = 1 ORDER BY c.sort_order ASC, c.name ASC");
$categories = [];
while ($row = mysqli_fetch_assoc($catRes)) {
    $categories[] = $row;
}

if (count($categories) > 0) {
    foreach ($categories as $cat) {
        echo '<h3 style="margin:24px 0 12px 0;color:#1a237e;">' . htmlspecialchars($cat['name']) . '</h3>';
        
        $query = "SELECT p.name, p.price, p.description, p.stock, pi.image_path
                  FROM products p
                  LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                  WHERE p.category_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $cat['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo '<div class="category-products-grid">';
            while ($row = $result->fetch_assoc()) {
                $outOfStock = ($row['stock'] <= 0);
                echo '<div class="product-card" data-name="'.htmlspecialchars($row['name']).'" data-img="'.htmlspecialchars($row['image_path']).'" data-desc="'.htmlspecialchars($row['description']).'" data-id="'.htmlspecialchars($row['name']).'">';
                if ($outOfStock) {
                    echo '<span class="out-of-stock">Out of Stock</span>';
                }
                echo '<img src="'. $row['image_path'] . '" alt="'. $row['name'] .'">';
                echo '<div class="product-info">';
                echo '<p class="product-name">'. $row['name'] .'</p>';
                echo '<p class="product-price">GH '. $row['price'] .'</p>';
                if ($outOfStock) {
                    echo '<button class="view-btn" disabled>View</button>';
                } else {
                    echo '<button class="view-btn" onclick="openProductView(this)">View</button>';
                }
                echo '</div></div>';
            }
            echo '</div>';
        } else {
            echo '<div style="text-align:center;">No products found in this category.</div>';
        }
    }
} else {
    echo '<div style="text-align:center;">No categories found.</div>';
}
?>
