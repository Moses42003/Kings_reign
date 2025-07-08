<?php
// user_orders.php
include('db.php');
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id='$user_id' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
    .order-card {background:#f5f7fa;border-radius:12px;box-shadow:0 2px 8px rgba(26,35,126,0.07);padding:22px 24px;margin-bottom:18px;max-width:700px;margin-left:auto;margin-right:auto;}
    .order-items-list {margin:10px 0 0 0;padding-left:18px;}
    .order-items-list li {margin-bottom:6px;}
    </style>
</head>
<body>
    <div style="max-width:800px;margin:40px auto 0 auto;padding:0 10px;">
        <button onclick="window.history.back()" style="margin-bottom:18px;background:#1a237e;color:#fff;padding:8px 18px;border:none;border-radius:6px;cursor:pointer;">&larr; Back</button>
        <h2 style="color:#1a237e;text-align:center;">My Orders</h2>
        <?php while($order = mysqli_fetch_assoc($orders)) { 
            $order_id = $order['id'];
            $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id='$order_id'");
        ?>
            <div class="order-card">
                <p style="margin:0 0 8px 0;"><strong>Order #<?php echo $order['id']; ?></strong></p>
                <p style="font-size:0.95em;color:#666;">Date: <?php echo $order['created_at']; ?></p>
                <ul class="order-items-list">
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
