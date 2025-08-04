<?php
// get_cart_unified.php - Updated for unified products table
session_start();
include('db.php');
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$query = "SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.stock, pi.image_path
          FROM cart c
          JOIN products p ON c.product_id = p.id
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
          WHERE c.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while ($row = $result->fetch_assoc()) {
    $cart[] = [
        'id' => $row['id'],
        'product_id' => $row['product_id'],
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'image_path' => $row['image_path'],
        'stock' => $row['stock']
    ];
}

echo json_encode(['success' => true, 'cart' => $cart]);

$stmt->close();
?> 