<?php
header('Content-Type: application/json');

// Include your database connection
require_once 'db.php'; // Update this to your actual connection file

// Start session if needed (to check user ownership, etc.)
session_start();

// Check if request is POST and order_id is present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = intval($_POST['order_id']);

    // Optional: Get user ID from session (if user-specific orders)
    // $userId = $_SESSION['user_id'] ?? null;

    // Basic security validation
    if ($orderId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
        exit;
    }

    // Optional: Add user validation
    // $query = "UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'";
    // $stmt = $conn->prepare($query);
    // $stmt->bind_param('ii', $orderId, $userId);

    $query = "UPDATE orders SET status = 'cancelled' WHERE id = ? AND status = 'pending'";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit;
    }

    $stmt->bind_param('i', $orderId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Order could not be cancelled. It may already be processed or does not exist.'
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or missing order ID.']);
}
