<?php
// get_user_messages.php
include('db.php');
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'html' => '<p>Please log in to view messages.</p>']);
    exit();
}
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$messages = mysqli_query($conn, "SELECT * FROM contact_messages WHERE email='".mysqli_real_escape_string($conn, $user_email)."' ORDER BY created_at DESC");
$html = '';
while($msg = mysqli_fetch_assoc($messages)) {
    $html .= '<div class="user-msg-card">';
    $html .= '<p style="margin:0 0 8px 0;"><strong>Message:</strong><br>' . nl2br(htmlspecialchars($msg['message'])) . '</p>';
    $html .= '<p style="font-size:0.95em;color:#666;">Sent: ' . $msg['created_at'] . '</p>';
    if ($msg['reply']) {
        $html .= '<div class="reply"><strong>Admin Reply:</strong><br>' . nl2br(htmlspecialchars($msg['reply'])) . '<br>';
        $html .= '<span style="font-size:0.9em;color:#3949ab;">Replied: ' . $msg['replied_at'] . '</span></div>';
    }
    $html .= '</div>';
}
echo json_encode(['success' => true, 'html' => $html]);
