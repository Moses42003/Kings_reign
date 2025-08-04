<?php
include('db.php');
session_start();
$message = '';
if (isset($_POST['submit'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['passwd'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['fname'] . ' ' . $user['lname'];
            header("Location: home.php");
            exit();
        } else {
            $message = 'Invalid password!';
        }
    } else {
        // Check if admin
        $admin_sql = "SELECT * FROM admin WHERE email='$email'";
        $admin_result = $conn->query($admin_sql);
        if ($admin_result && $admin_result->num_rows > 0) {
            $admin = $admin_result->fetch_assoc();
            if (password_verify($password, $admin['passwd'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = isset($admin['name']) ? $admin['name'] : $admin['email'];
                header("Location: admin/dashboard.php");
                exit();
            } else {
                $message = 'Invalid password!';
            }
        } else {
            $message = 'User not found!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kings Reign</title>
    <link rel="stylesheet" href="styles/home.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
</head>
<body>
    <div class="forms">
        <form class="login" action="login.php" method="post">
            <h2>Login</h2>
            <?php if(isset($_GET['signup']) && $_GET['signup']==='success') echo '<p style="color:green;text-align:center;">Signup successful! Please login.</p>'; ?>
            <?php if($message) echo '<p style="color:red;text-align:center;">'.$message.'</p>'; ?>
            <input type="email" name="email" id="email" placeholder="Enter Your Email" required>
            <input type="password" name="password" id="password" placeholder="Enter Password" required>
            <p>Not having an account? <a href="signup.php">SignUp</a></p>
            <button name="submit" type="submit">Login</button>
        </form>
    </div>
</body>
</html>

