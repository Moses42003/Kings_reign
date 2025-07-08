<?php
// Add Product Page
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
include('../db.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $stock = $_POST['stock'];
    $product_type = $_POST['product_type'];
    $category = $_POST['category'];
    $table = ($product_type === 'clothes') ? 'clothes' : 'phones';
    // Handle image upload
    $img_folder = $product_type === 'clothes' ? '../images/cloth imgs/' : '../images/phone imgs/';
    $img_folder_db = $product_type === 'clothes' ? 'images/cloth imgs/' : 'images/phone imgs/';
    $file_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $img_name = uniqid('prod_', true) . '.' . $ext;
        $target = $img_folder . $img_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $file_path = $img_folder_db . $img_name;
        } else {
            $msg = 'Image upload failed!';
        }
    }
    if ($file_path) {
        $query = "INSERT INTO $table (name, price, description, file_path, stock, category) VALUES ('$name', '$price', '$description', '$file_path', '$stock', '$category')";
        if (mysqli_query($conn, $query)) {
            $msg = 'Product added!';
        } else {
            $msg = 'Error adding product.';
        }
    }
    header('Content-Type: application/json');
    if (isset($msg) && $msg === 'Product added!') {
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        echo json_encode(['success' => false, 'message' => isset($msg) ? $msg : 'Error adding product.']);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="../styles/style.css">
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
        <form class="admin-form" id="adminAddProductForm" method="post" enctype="multipart/form-data">
            <h2 style="color:#1a237e;text-align:center;">Add Product</h2>
            <div id="add-product-status" style="text-align:center;margin-bottom:10px;"></div>
            <select name="product_type" id="product_type_select" required style="padding:12px 10px;border-radius:8px;border:1px solid #c5cae9;font-size:1rem;" onchange="updateCategoryOptions()">
                <option value="phones">Phone</option>
                <option value="clothes">Clothing</option>
            </select>
            <select name="category" id="category_select" required style="padding:12px 10px;border-radius:8px;border:1px solid #c5cae9;font-size:1rem;margin-top:10px;">
                <!-- Options will be set by JS -->
            </select>
            <input type="text" name="name" placeholder="Product Name" required>
            <input type="number" name="price" placeholder="Price" required>
            <input type="text" name="description" placeholder="Description">
            <input type="file" name="image" accept="image/*" required>
            <input type="number" name="stock" placeholder="Stock" required>
            <button type="submit">Add Product</button>
        </form>
    </div>
</body>
</html>
<script>
function updateCategoryOptions() {
    var type = document.getElementById('product_type_select').value;
    var catSel = document.getElementById('category_select');
    catSel.innerHTML = '';
    if (type === 'clothes') {
        ['Shoes', 'Hoodies', 'T-Shirts', 'Jeans', 'Jackets'].forEach(function(cat) {
            var opt = document.createElement('option');
            opt.value = cat; opt.textContent = cat;
            catSel.appendChild(opt);
        });
    } else {
        ['Infinix', 'Samsung', 'Tecno', 'iPhone', 'Itel', 'Xiaomi'].forEach(function(cat) {
            var opt = document.createElement('option');
            opt.value = cat; opt.textContent = cat;
            catSel.appendChild(opt);
        });
    }
}
document.addEventListener('DOMContentLoaded', updateCategoryOptions);

// AJAX add product
const addProductForm = document.getElementById('adminAddProductForm');
if (addProductForm) {
    addProductForm.onsubmit = function(e) {
        e.preventDefault();
        var status = document.getElementById('add-product-status');
        status.textContent = 'Adding...';
        var formData = new FormData(addProductForm);
        fetch('add_product.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                status.textContent = data.message;
                addProductForm.reset();
                updateCategoryOptions();
                showAdminNotification('Product added!');
                // If edit_products page is open, reload products
                if (window.parent && window.parent.reloadAdminProducts) window.parent.reloadAdminProducts();
            } else {
                status.textContent = data.message || 'Error adding product.';
            }
        })
        .catch(() => { status.textContent = 'Network error.'; });
    };
}
// Simple toast notification
function showAdminNotification(msg) {
    var toast = document.createElement('div');
    toast.textContent = msg;
    toast.style.position = 'fixed';
    toast.style.bottom = '30px';
    toast.style.right = '30px';
    toast.style.background = '#1a237e';
    toast.style.color = '#fff';
    toast.style.padding = '14px 28px';
    toast.style.borderRadius = '8px';
    toast.style.fontSize = '1.1rem';
    toast.style.zIndex = 9999;
    toast.style.boxShadow = '0 2px 8px rgba(26,35,126,0.18)';
    document.body.appendChild(toast);
    setTimeout(function() { toast.remove(); }, 2500);
}
</script>
