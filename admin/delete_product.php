<?php
// delete_product.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        echo json_encode(['success'=>false,'message'=>'Not logged in']);
        exit();
    }
    header('Location: login.php');
    exit();
}
include('../db.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $type = $_POST['type'] === 'clothes' ? 'clothes' : 'phones';
    $query = "DELETE FROM $type WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['success'=>true]);
            exit();
        }
        header('Location: edit_products.php?deleted=1');
        exit();
    } else {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            echo json_encode(['success'=>false,'message'=>'Delete failed']);
            exit();
        }
        header('Location: edit_products.php?deleted=0');
        exit();
    }
}
?>
