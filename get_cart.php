<?php
// get_cart.php
session_start();
include('db.php');
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}
$user_id = $_SESSION['user_id'];
$cart = [];
// Phones
$result = mysqli_query($conn, "SELECT c.product_id, c.quantity, p.name, p.price, p.file_path FROM cart c JOIN phones p ON c.product_id = p.name WHERE c.user_id='$user_id'");
while ($row = mysqli_fetch_assoc($result)) {
    $cart[] = $row;
}
// Clothes
$result2 = mysqli_query($conn, "SELECT c.product_id, c.quantity, p.name, p.price, p.file_path FROM cart c JOIN clothes p ON c.product_id = p.name WHERE c.user_id='$user_id'");
while ($row = mysqli_fetch_assoc($result2)) {
    $cart[] = $row;
}
echo json_encode(['success' => true, 'cart' => $cart]);
