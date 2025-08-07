<?php
session_start();
include('db.php'); // Make sure this sets up $conn (MySQLi)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');


if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];


// Collect inputs and sanitize
$name    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
$email   = isset($_POST['email'])   ? trim($_POST['email'])   : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Log for debugging
error_log("NAME: $name");
error_log("EMAIL: $email");
error_log("SUBJECT: $subject");
error_log("MESSAGE: $message");

// Basic validation
if (!$name || !$email || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields required.']);
    exit();
}

// Optional: Check valid email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit();
}

// Insert into contact_messages table
$stmt = $conn->prepare("INSERT INTO contact_messages (user_id, name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'unread', NOW())");
$stmt->bind_param('sssss', $user_id, $name, $email, $subject, $message);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
}

$stmt->close();
