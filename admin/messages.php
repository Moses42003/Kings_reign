<?php
// admin/messages.php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
include('../db.php');
$messages = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Messages</title>
    <link rel="stylesheet" href="../styles/admin_styles.css">
</head>
<body>
    <div class="admin-header">Admin Panel</div>
    <div class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_product.php">Add Product</a>
        <a href="users.php">View Users</a>
        <a href="admins.php">Manage Admins</a>
        <a href="messages.php">Contact Messages</a>
        <a href="logout.php" style="color:#e53935;">Logout</a>
    </div>
    <div class="admin-container">
        <h2 style="color:#1a237e;text-align:center;">Contact Messages</h2>
        <div class="admin-table-flex">
            <?php while($msg = mysqli_fetch_assoc($messages)) { ?>
                <div class="admin-table-card" style="min-width:260px;max-width:400px;position:relative;">
                    <h4><?php echo htmlspecialchars($msg['name']); ?> <span style="font-size:0.9em;color:#888;">(<?php echo htmlspecialchars($msg['email']); ?>)</span></h4>
                    <p style="margin:10px 0 6px 0;"><strong>Message:</strong><br><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    <p style="font-size:0.9em;color:#666;">Sent: <?php echo $msg['created_at']; ?></p>
                    <?php if ($msg['reply']) { ?>
                        <div style="background:#e3e8fd;padding:10px 12px;border-radius:8px;margin:10px 0 0 0;">
                            <strong>Reply:</strong><br><?php echo nl2br(htmlspecialchars($msg['reply'])); ?><br>
                            <span style="font-size:0.9em;color:#3949ab;">Replied: <?php echo $msg['replied_at']; ?></span>
                        </div>
                    <?php } else { ?>
                        <form method="post" style="margin-top:10px;">
                            <input type="hidden" name="reply_id" value="<?php echo $msg['id']; ?>">
                            <textarea name="reply_text" rows="2" placeholder="Type reply..." style="width:100%;border-radius:6px;padding:6px 8px;margin-bottom:6px;"></textarea>
                            <button type="submit" style="background:#1a237e;color:#fff;border:none;padding:4px 14px;border-radius:5px;cursor:pointer;">Send Reply</button>
                        </form>
                    <?php } ?>
                    <form method="post" style="position:absolute;top:10px;right:10px;">
                        <input type="hidden" name="delete_id" value="<?php echo $msg['id']; ?>">
                        <button type="submit" style="background:#e53935;color:#fff;border:none;padding:4px 10px;border-radius:5px;cursor:pointer;">Delete</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
    // Handle delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $del_id = intval($_POST['delete_id']);
        mysqli_query($conn, "DELETE FROM contact_messages WHERE id=$del_id");
        header('Location: messages.php');
        exit();
    }
    // Handle reply
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id']) && isset($_POST['reply_text'])) {
        $reply_id = intval($_POST['reply_id']);
        $reply_text = mysqli_real_escape_string($conn, $_POST['reply_text']);
        mysqli_query($conn, "UPDATE contact_messages SET reply='$reply_text', replied_at=NOW() WHERE id=$reply_id");
        header('Location: messages.php');
        exit();
    }
    ?>
    <script>
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
    // Poll for new user messages every 10s
    let lastAdminMsgCount = 0;
    function pollAdminMessages() {
        fetch('check_admin_messages.php')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.count > lastAdminMsgCount) {
                    showToast('New user message received!');
                }
                lastAdminMsgCount = data.count;
            });
    }
    setInterval(pollAdminMessages, 10000);
    </script>
</body>
</html>
