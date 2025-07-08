<?php
// View all users
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}
include('../db.php');
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$users = mysqli_query($conn,
    $search ?
        "SELECT * FROM users WHERE fname LIKE '%$search%' OR lname LIKE '%$search%' OR CONCAT(fname, ' ', lname) LIKE '%$search%' OR email LIKE '%$search%'"
        : "SELECT * FROM users"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users</title>
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
        <h2 style="color:#1a237e;text-align:center;">All Users</h2>
        <form method="get" style="margin-bottom:24px;display:flex;justify-content:center;gap:12px;">
            <input type="text" name="search" placeholder="Search Users by Name or Email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="padding:8px 12px;border-radius:8px;border:1px solid #c5cae9;min-width:200px;">
            <button type="submit" style="padding:8px 18px;border-radius:8px;background:#1a237e;color:#fff;border:none;">Search</button>
        </form>
        <div class="admin-table-flex">
            <?php while($user = mysqli_fetch_assoc($users)) { ?>
                <div class="admin-table-card">
                    <h4>
                        <?php
                        if (isset($user['name'])) {
                            echo htmlspecialchars($user['name']);
                        } else {
                            echo htmlspecialchars(trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '')));
                        }
                        ?>
                    </h4>
                    <p>ID: <?php echo $user['id']; ?></p>
                    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
