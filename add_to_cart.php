<?php
// add_to_cart.php
session_start();
include('db.php');
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}
$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'No product specified']);
    exit();
}
// Check if already in cart
$check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id='$user_id' AND product_id='$product_id'");
if (mysqli_num_rows($check) > 0) {
    mysqli_query($conn, "UPDATE cart SET quantity = quantity + 1 WHERE user_id='$user_id' AND product_id='$product_id'");
} else {
    mysqli_query($conn, "INSERT INTO cart (user_id, product_id, quantity) VALUES ('$user_id', '$product_id', 1)");
}
echo json_encode(['success' => true, 'message' => 'Added to cart']);
