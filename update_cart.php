<?php
// update_cart.php
session_start();
include('db.php');
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}
$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
if (!$product_id || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}
if ($quantity === 0) {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id='$user_id' AND product_id='$product_id'");
    echo json_encode(['success' => true]);
    exit();
}
// Try phones first
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM phones WHERE name='$product_id'"));
$type = 'phones';
if (!$product) {
    // Try clothes
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM clothes WHERE name='$product_id'"));
    $type = 'clothes';
}
if (!$product || $quantity > $product['stock']) {
    echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    exit();
}
mysqli_query($conn, "UPDATE cart SET quantity='$quantity' WHERE user_id='$user_id' AND product_id='$product_id'");
echo json_encode(['success' => true]);
