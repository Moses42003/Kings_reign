<?php
// Edit Product Page
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
include('../db.php');

// Get product ID
if (!isset($_GET['id'])) {
    die('Product ID not specified.');
}
$id = intval($_GET['id']);

// Get product type (phones or clothes) from query or fallback
$type = isset($_GET['type']) && $_GET['type'] === 'clothes' ? 'clothes' : 'phones';
if (isset($_GET['type'])) {
    $type = $_GET['type'] === 'clothes' ? 'clothes' : 'phones';
} elseif (isset($_GET['id'])) {
    // Try to infer type from referring page or fallback
    // This block can be improved if you pass type in the edit link
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $file_path = $_POST['file_path'];
    $category = $_POST['category'] === '__custom__' ? $_POST['custom_category'] : $_POST['category'];
    $query = "UPDATE $type SET name='$name', price='$price', description='$description', file_path='$file_path', stock='$stock', category='$category' WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        $msg = 'Product updated!';
    } else {
        $msg = 'Error updating product.';
    }
}

// Fetch product info
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM $type WHERE id=$id"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../styles/admin_styles.css">
</head>
<body>
    <div class="admin-header">Admin Panel</div>
    <div class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_product.php">Add Product</a>
        <a href="users.php">View Users</a>
        <a href="admins.php">Manage Admins</a>
        <a href="logout.php" style="color:#e53935;">Logout</a>
    </div>
    <div class="admin-container">
        <form class="admin-form" method="post">
            <h2 style="color:#1a237e;text-align:center;">Edit Product</h2>
            <?php if (isset($msg)) echo '<p style="color:green;text-align:center;">'.$msg.'</p>'; ?>
            <select name="category" id="category_select" required style="padding:12px 10px;border-radius:8px;border:1px solid #c5cae9;font-size:1rem;margin-bottom:10px;">
                <?php
                // Use $type to determine correct categories
                if ($type === 'clothes') {
                    $cats = ['Shoes', 'Hoodies', 'T-Shirts', 'Jeans', 'Jackets'];
                } else {
                    $cats = ['Infinix', 'Samsung', 'Tecno', 'iPhone', 'Itel', 'Xiaomi'];
                }
                $isCustom = true;
                foreach ($cats as $cat) {
                    $sel = ($product['category'] === $cat) ? 'selected' : '';
                    if ($sel) $isCustom = false;
                    echo "<option value='$cat' $sel>$cat</option>";
                }
                // If the current category is not in the list, show it as a custom option
                if ($isCustom && !empty($product['category'])) {
                    echo "<option value='".htmlspecialchars($product['category'])."' selected>".htmlspecialchars($product['category'])." (custom)</option>";
                }
                ?>
                <option value="__custom__">Other (enter custom)</option>
            </select>
            <input type="text" name="custom_category" id="custom_category_input" placeholder="Enter custom category" style="display:none;margin-bottom:10px;padding:12px 10px;border-radius:8px;border:1px solid #c5cae9;font-size:1rem;" value="<?php echo (!in_array($product['category'], $cats) ? htmlspecialchars($product['category']) : ''); ?>">
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" placeholder="Product Name" required>
            <input type="number" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" placeholder="Price" required>
            <input type="text" name="description" value="<?php echo htmlspecialchars($product['description']); ?>" placeholder="Description">
            <input type="text" name="file_path" value="<?php echo htmlspecialchars($product['file_path']); ?>" placeholder="Image Path" required>
            <input type="number" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" placeholder="Stock" required>
            <button type="submit">Update Product</button>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var catSel = document.getElementById('category_select');
        var customInput = document.getElementById('custom_category_input');
        function toggleCustom() {
            if (catSel.value === '__custom__') {
                customInput.style.display = '';
                customInput.required = true;
            } else {
                customInput.style.display = 'none';
                customInput.required = false;
            }
        }
        catSel.addEventListener('change', toggleCustom);
        toggleCustom();
    });
    </script>
</body>
</html>
