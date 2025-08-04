<?php
// add_to_cart_unified.php - Updated for unified products table
session_start();
include('db.php');
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'No product specified']);
    exit();
}

// Check if product exists and has stock
$product_query = "SELECT * FROM products WHERE id = ? AND stock > 0";
$stmt = $conn->prepare($product_query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not available']);
    exit();
}

// Check if already in cart
$check_query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param('ii', $user_id, $product_id);
$check_stmt->execute();
$existing_item = $check_stmt->get_result()->fetch_assoc();

if ($existing_item) {
    // Update quantity if already in cart
    $new_quantity = $existing_item['quantity'] + 1;
    if ($new_quantity <= $product['stock']) {
        $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param('iii', $new_quantity, $user_id, $product_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available']);
    }
} else {
    // Add new item to cart
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param('ii', $user_id, $product_id);
    
    if ($insert_stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to cart']);
    }
}

$stmt->close();
$check_stmt->close();
?> 