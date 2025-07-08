<?php
// Returns count of new admin replies for the logged-in user
include('db.php');
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit();
}
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$last_checked = isset($_SESSION['last_checked_user_msg']) ? $_SESSION['last_checked_user_msg'] : '1970-01-01 00:00:00';
$q = "SELECT COUNT(*) as cnt FROM contact_messages WHERE email='".mysqli_real_escape_string($conn, $user_email)."' AND reply IS NOT NULL AND replied_at > '".mysqli_real_escape_string($conn, $last_checked)."'";
$res = mysqli_query($conn, $q);
$row = mysqli_fetch_assoc($res);
echo json_encode(['success' => true, 'count' => (int)$row['cnt']]);
$_SESSION['last_checked_user_msg'] = date('Y-m-d H:i:s');
