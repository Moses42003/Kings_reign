<?php
session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Redirect to index.php for logged-in users
header('Location: index.php');
exit();
?>
