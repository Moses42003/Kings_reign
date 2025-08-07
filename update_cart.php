<?php
// update_cart.php
session_start();
include('db.php');
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if (!$product_id || $quantity < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

try {
    // Check if product exists and get stock
    $product_query = "SELECT id, name, stock, price FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }
    
    // Check if quantity is available
    if ($quantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock available. Only ' . $product['stock'] . ' items left.']);
        exit();
    }
    
    // If quantity is 0, remove from cart
    if ($quantity === 0) {
        $delete_query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('ii', $user_id, $product_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        exit();
    }
    
    // Check if item already exists in cart
    $cart_query = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param('ii', $user_id, $product_id);
    $stmt->execute();
    $cart_item = $stmt->get_result()->fetch_assoc();
    
    if ($cart_item) {
        // Update existing cart item
        $update_query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('iii', $quantity, $user_id, $product_id);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
    } else {
        // Add new item to cart
        $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param('iii', $user_id, $product_id, $quantity);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Item added to cart']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
