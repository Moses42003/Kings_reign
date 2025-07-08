<?php
// get_category_products.php
include('db.php');
$type = isset($_GET['type']) ? $_GET['type'] : '';
$type = ($type === 'clothes' || $type === 'phones') ? $type : 'phones';

if ($type === 'phones' || $type === 'clothes') {
    // Get all categories for this type
    $catRes = mysqli_query($conn, "SELECT DISTINCT category FROM $type WHERE category != '' ORDER BY category ASC");
    $categories = [];
    while ($row = mysqli_fetch_assoc($catRes)) {
        $categories[] = $row['category'];
    }
    if (count($categories) > 0) {
        foreach ($categories as $cat) {
            echo '<h3 style="margin:24px 0 12px 0;color:#1a237e;">' . htmlspecialchars($cat) . '</h3>';
            $query = "SELECT name, price, description, file_path, stock FROM $type WHERE category='".mysqli_real_escape_string($conn, $cat)."'";
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                echo '<div class="category-products-grid">';
                while ($row = mysqli_fetch_assoc($result)) {
                    $outOfStock = ($row['stock'] <= 0);
                    echo '<div class="product-card" data-name="'.htmlspecialchars($row['name']).'" data-img="'.htmlspecialchars($row['file_path']).'" data-desc="'.htmlspecialchars($row['description']).'" data-id="'.htmlspecialchars($row['name']).'">';
                    if ($outOfStock) {
                        echo '<span class="out-of-stock">Out of Stock</span>';
                    }
                    echo '<img src="'. $row['file_path'] . '" alt="'. $row['name'] .'">';
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
} else {
    echo '<div style="text-align:center;">Category not found.</div>';
}
