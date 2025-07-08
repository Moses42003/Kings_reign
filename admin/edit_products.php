<?php
// List all products for editing
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
include('../db.php');
$type = isset($_GET['type']) && $_GET['type'] === 'clothes' ? 'clothes' : 'phones';
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$products = mysqli_query($conn, $search ? "SELECT * FROM $type WHERE name LIKE '%$search%' OR description LIKE '%$search%'" : "SELECT * FROM $type");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Products</title>
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
        <h2 style="color:#1a237e;text-align:center;">Edit Products</h2>
        <form method="get" style="margin-bottom:24px;display:flex;justify-content:center;gap:12px;">
            <select name="type" onchange="this.form.submit()" style="padding:8px 12px;border-radius:8px;border:1px solid #c5cae9;">
                <option value="phones" <?php if($type==='phones') echo 'selected'; ?>>Phones</option>
                <option value="clothes" <?php if($type==='clothes') echo 'selected'; ?>>Clothing</option>
            </select>
            <input type="text" name="search" placeholder="Search Products by Name or Description" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="padding:8px 12px;border-radius:8px;border:1px solid #c5cae9;min-width:200px;">
            <button type="submit" style="padding:8px 18px;border-radius:8px;background:#1a237e;color:#fff;border:none;">Search</button>
        </form>
        <div class="admin-table-flex">
            <?php while($product = mysqli_fetch_assoc($products)) { ?>
                <div class="admin-table-card">
                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                    <p>ID: <?php echo $product['id']; ?></p>
                    <p>Price: GH <?php echo $product['price']; ?></p>
                    <p>Stock: <?php echo $product['stock']; ?></p>
                    <p>Description: <?php echo htmlspecialchars($product['description']); ?></p>
                    <a href="edit_product.php?id=<?php echo $product['id']; ?>&type=<?php echo $type; ?>" class="admin-edit-btn" style="color:#fff;background:#1a237e;padding:6px 16px;border-radius:6px;text-decoration:none;display:inline-block;margin-top:8px;">Edit</a>
                    <form method="post" action="delete_product.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <button type="submit" class="admin-delete-btn" data-id="<?php echo $product['id']; ?>" data-type="<?php echo $type; ?>" style="color:#fff;background:#e53935;padding:6px 16px;border-radius:6px;border:none;margin-left:8px;">Delete</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>
    <!-- Add admin_script.js -->
    <script src="admin_script.js"></script>
</body>
</html>
