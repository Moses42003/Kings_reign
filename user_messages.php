<?php
// user_messages.php
include('db.php');
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$messages = mysqli_query($conn, "SELECT * FROM contact_messages WHERE email='".mysqli_real_escape_string($conn, $user_email)."' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Messages</title>
    <link rel="stylesheet" href="styles/style.css">
    <style>
    .user-msg-card {background:#f5f7fa;border-radius:12px;box-shadow:0 2px 8px rgba(26,35,126,0.07);padding:22px 24px;margin-bottom:18px;max-width:600px;margin-left:auto;margin-right:auto;}
    .user-msg-card .reply {background:#e3e8fd;padding:10px 12px;border-radius:8px;margin:10px 0 0 0;}
    </style>
</head>
<body>
    <div style="max-width:700px;margin:40px auto 0 auto;padding:0 10px;">
        <button onclick="window.history.back()" style="margin-bottom:18px;background:#1a237e;color:#fff;padding:8px 18px;border:none;border-radius:6px;cursor:pointer;">&larr; Back</button>
        <h2 style="color:#1a237e;text-align:center;">My Contact Messages</h2>
        <div id="user-messages-list">
            <?php while($msg = mysqli_fetch_assoc($messages)) { ?>
                <div class="user-msg-card">
                    <p style="margin:0 0 8px 0;"><strong>Message:</strong><br><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    <p style="font-size:0.95em;color:#666;">Sent: <?php echo $msg['created_at']; ?></p>
                    <?php if ($msg['reply']) { ?>
                        <div class="reply">
                            <strong>Admin Reply:</strong><br><?php echo nl2br(htmlspecialchars($msg['reply'])); ?><br>
                            <span style="font-size:0.9em;color:#3949ab;">Replied: <?php echo $msg['replied_at']; ?></span>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <script>
        // Function to reload messages via AJAX
        function reloadUserMessages() {
            fetch('get_user_messages.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('user-messages-list').innerHTML = data.html;
                    }
                });
        }
        // Toast notification function (reusable)
        function showToast(msg) {
            var toast = document.createElement('div');
            toast.textContent = msg;
            toast.style.position = 'fixed';
            toast.style.bottom = '30px';
            toast.style.right = '30px';
            toast.style.background = '#1a237e';
            toast.style.color = '#fff';
            toast.style.padding = '14px 28px';
            toast.style.borderRadius = '8px';
            toast.style.fontSize = '1.1rem';
            toast.style.zIndex = 9999;
            toast.style.boxShadow = '0 2px 8px rgba(26,35,126,0.18)';
            document.body.appendChild(toast);
            setTimeout(function() { toast.remove(); }, 2500);
        }
        // Poll for new admin replies every 10s
        let lastUserMsgCount = 0;
        function pollUserMessages() {
            fetch('check_user_messages.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.count > lastUserMsgCount) {
                        showToast('You have a new reply from admin!');
                    }
                    lastUserMsgCount = data.count;
                });
        }
        setInterval(pollUserMessages, 10000);
        // Optional: Expose for other scripts
        window.reloadUserMessages = reloadUserMessages;
        </script>
    </div>
</body>
</html>
