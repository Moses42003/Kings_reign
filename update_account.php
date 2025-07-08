<?php
// update_account.php
include('db.php');
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $conn->real_escape_string($_POST['fname']);
    $lname = $conn->real_escape_string($_POST['lname']);
    $address = $conn->real_escape_string($_POST['address']);
    $email = $conn->real_escape_string($_POST['email']);
    $query = "UPDATE users SET fname='$fname', lname='$lname', address='$address', email='$email' WHERE id='$user_id'";
    if ($conn->query($query)) {
        $_SESSION['user_name'] = $fname . ' ' . $lname;
        $_SESSION['user_email'] = $email;
        $message = 'Account updated!';
    } else {
        $message = 'Error updating account.';
    }
}
$user = $conn->query("SELECT fname, lname, address, email FROM users WHERE id='$user_id'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Account</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div style="max-width:400px;margin:40px auto;padding:0 10px;">
        <h2 style="color:#1a237e;text-align:center;">Update Account</h2>
        <?php if($message) echo '<p style="color:green;text-align:center;">'.$message.'</p>'; ?>
        <form method="post">
            <input type="text" name="fname" placeholder="First Name" value="<?php echo htmlspecialchars($user['fname']); ?>" required style="width:100%;padding:10px 12px;margin-bottom:10px;border-radius:8px;border:1px solid #c5cae9;">
            <input type="text" name="lname" placeholder="Last Name" value="<?php echo htmlspecialchars($user['lname']); ?>" required style="width:100%;padding:10px 12px;margin-bottom:10px;border-radius:8px;border:1px solid #c5cae9;">
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width:100%;padding:10px 12px;margin-bottom:10px;border-radius:8px;border:1px solid #c5cae9;">
            <input type="text" name="address" placeholder="Address" value="<?php echo htmlspecialchars($user['address']); ?>" required style="width:100%;padding:10px 12px;margin-bottom:14px;border-radius:8px;border:1px solid #c5cae9;">
            <button type="submit" style="background:#1a237e;color:#fff;padding:10px 24px;border:none;border-radius:6px;cursor:pointer;width:100%;">Update Account</button>
        </form>
        <div style="text-align:center;margin-top:18px;"><a href="home.php" style="color:#3949ab;">&larr; Back to Home</a></div>
    </div>
</body>
</html>
