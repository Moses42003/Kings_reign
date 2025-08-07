<?php
session_start();
include('db.php');
header('Content-Type: application/json');

// Check user authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$message_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$stmt = $conn->prepare("SELECT name, email, subject, message, created_at, reply, replied_at FROM contact_messages WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $message_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['success' => true, 'message' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Message not found']);
}

$stmt->close();
