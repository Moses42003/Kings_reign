<?php
session_start();
include('db.php');
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action !== 'place_order') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get user details
    $user_query = "SELECT fname, lname, email, address FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Get cart items with product details
    $cart_query = "SELECT c.*, p.name, p.price, p.stock 
                   FROM cart c 
                   LEFT JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = ?";
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();
    
    if ($cart_items->num_rows === 0) {
        throw new Exception('Cart is empty');
    }
    
    // Calculate total
    $total = 0;
    $order_items = [];
    
    while ($item = $cart_items->fetch_assoc()) {
        // Check stock availability
        if ($item['quantity'] > $item['stock']) {
            throw new Exception("Insufficient stock for {$item['name']}. Only {$item['stock']} available.");
        }
        
        $item_total = $item['quantity'] * $item['price'];
        $total += $item_total;
        
        $order_items[] = [
            'product_id' => $item['product_id'],
            'product_name' => $item['name'],
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'total' => $item_total
        ];
    }
    
    // Create order
    $order_query = "INSERT INTO orders (user_id, total, status, payment_method, shipping_address, created_at) 
                    VALUES (?, ?, 'pending', 'Cash on Delivery', ?, NOW())";
    $stmt = $conn->prepare($order_query);
    $shipping_address = $user['address'] ?? 'Address not provided';
    $stmt->bind_param('ids', $user_id, $total, $shipping_address);
    $stmt->execute();
    
    $order_id = $conn->insert_id;
    
    if (!$order_id) {
        throw new Exception('Failed to create order');
    }
    
    // Add order items
    foreach ($order_items as $item) {
        $item_query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                       VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($item_query);
        $stmt->bind_param('iisid', $order_id, $item['product_id'], $item['product_name'], $item['quantity'], $item['price']);
        $stmt->execute();
        
        // Update product stock
        $stock_query = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stmt = $conn->prepare($stock_query);
        $stmt->bind_param('ii', $item['quantity'], $item['product_id']);
        $stmt->execute();
    }
    
    // Clear cart
    $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($clear_cart_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Send success response
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'total' => $total,
        'items_count' => count($order_items)
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 