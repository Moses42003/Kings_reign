<?php
// Manage Admins
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
include('../db.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO admin (name, email, passwd) VALUES ('$name', '$email', '$hashedPassword')";
    if (mysqli_query($conn, $query)) {
        $msg = 'Admin added!';
    } else {
        $msg = 'Error adding admin: ' . $conn->error;
    }
}
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$admins = mysqli_query($conn, $search ? "SELECT * FROM admin WHERE email LIKE '%$search%'" : "SELECT * FROM admin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admins</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/admin_styles.css">
</head>
<body>
    <div class="admin-header">Admin Panel</div>
    <div class="admin-nav">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_product.php">Add Product</a>
        <a href="users.php">View Users</a>
        <a href="admins.php">Manage Admins</a>
        <a href="logout.php" style="color:#e53935;">Logout</a>
    </div>
    <div class="admin-container">
        <form class="admin-form" method="post">
            <h2 style="color:#1a237e;text-align:center;">Manage Admins</h2>
            <?php if (isset($msg)) echo '<p style="color:green;text-align:center;">'.$msg.'</p>'; ?>
            <input type="text" id="name" name="name" placeholder="Enter Admin Name">
            <input type="email" name="email" placeholder="Admin Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Add Admin</button>
        </form>
        <form method="get" style="margin-bottom:24px;display:flex;justify-content:center;gap:12px;">
            <input type="text" name="search" placeholder="Search Admins by Email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="padding:8px 12px;border-radius:8px;border:1px solid #c5cae9;min-width:200px;">
            <button type="submit" style="padding:8px 18px;border-radius:8px;background:#1a237e;color:#fff;border:none;">Search</button>
        </form>
        <h3 style="color:#1a237e;text-align:center;">Current Admins</h3>
        <div class="admin-table-flex">
            <?php while($admin = mysqli_fetch_assoc($admins)) { ?>
                <div class="admin-table-card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-width:180px;max-width:220px;box-shadow:0 2px 8px rgba(26,35,126,0.10);background:#f5f7fa;margin-bottom:18px;">
                    <div style="width:60px;height:60px;background:#e3e8fd;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;color:#1a237e;margin-bottom:10px;">
                        <span><?php echo strtoupper(substr($admin['email'],0,1)); ?></span>
                    </div>
                    <h4 style="margin:0 0 6px 0;text-align:center;word-break:break-all;"><?php echo htmlspecialchars($admin['email']); ?></h4>
                    <p style="margin:0 0 4px 0;">ID: <?php echo $admin['id']; ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
