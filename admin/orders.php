<?php
// admin/orders.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
include('../db.php');
$orders = mysqli_query($conn, "SELECT o.*, u.fname, u.lname, u.email, u.address FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Orders</title>
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
        <a href="messages.php">Contact Messages</a>
        <a href="orders.php">Orders</a>
        <a href="logout.php" style="color:#e53935;">Logout</a>
    </div>
    <div class="admin-container">
        <h2 style="color:#1a237e;text-align:center;">All Orders</h2>
        <?php while($order = mysqli_fetch_assoc($orders)) { 
            $order_id = $order['id'];
            $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$order_id'");
        ?>
            <div class="admin-table-card" style="max-width:700px;margin:18px auto;">
                <p><strong>Order #<?php echo $order['id']; ?></strong> | User: <?php echo htmlspecialchars($order['fname'] . ' ' . $order['lname']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
                <p style="font-size:0.95em;color:#666;">Date: <?php echo $order['created_at']; ?></p>
                <?php if (!empty($order['address'])) { ?>
                    <p style="font-size:0.98em;color:#3949ab;"><strong>Address:</strong> <?php echo htmlspecialchars($order['address']); ?></p>
                <?php } ?>
                <ul style="margin:10px 0 0 0;padding-left:18px;">
                    <?php while($item = mysqli_fetch_assoc($items)) { ?>
                        <li><?php echo htmlspecialchars($item['product_name']); ?> x<?php echo $item['quantity']; ?> @ GH <?php echo $item['price']; ?></li>
                    <?php } ?>
                </ul>
                <p style="font-weight:bold;">Total: GH <?php echo $order['total']; ?></p>
            </div>
        <?php } ?>
    </div>
</body>
</html>
