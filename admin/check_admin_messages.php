<?php
// Returns count of new user messages for admin
include('../db.php');
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit();
}
$last_checked = isset($_SESSION['last_checked_admin_msg']) ? $_SESSION['last_checked_admin_msg'] : '1970-01-01 00:00:00';
$q = "SELECT COUNT(*) as cnt FROM contact_messages WHERE created_at > '".mysqli_real_escape_string($conn, $last_checked)."'";
$res = mysqli_query($conn, $q);
$row = mysqli_fetch_assoc($res);
echo json_encode(['success' => true, 'count' => (int)$row['cnt']]);
$_SESSION['last_checked_admin_msg'] = date('Y-m-d H:i:s');
