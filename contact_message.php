<?php
// contact_message.php
session_start();
include('db.php');
header('Content-Type: application/json');
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
if (!$name || !$email || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields required.']);
    exit();
}
// Insert message into contact_messages table
$stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $name, $email, $message);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
}
$stmt->close();
