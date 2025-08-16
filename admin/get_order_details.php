<?php
session_start();
include('db.php');

header('Content-Type: application/json');

// TEMP: show errors while debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

$order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid order ID']);
    exit;
}

try {
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection not available.');
    }

    // Fetch order
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Fetch items
    $stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    $items = [];
    while ($row = $items_result->fetch_assoc()) {
        $row['price'] = (float) $row['price'];
        $row['quantity'] = (int) $row['quantity'];
        $items[] = $row;
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
