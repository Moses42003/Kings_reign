<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// One-time script to add a custom admin with hashed password
include('../db.php');

// Set your custom admin credentials here

$name = 'Moses Otu ADMIN';
$email = 'admin@example.com';
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO admin (name, email, passwd) VALUES ('$name', '$email', '$hashedPassword')";
if (mysqli_query($conn, $query)) {
    $msg = "Admin created!<br>Email: $email<br>Password: $password";
} else {
    $error = "Error: " . mysqli_error($conn);
}
?>

<body>
    <div class="forms">
        <form class="admin-form" style="max-width: 500px;">
            <h2 style="color:#1a237e;text-align:center;">Create Admin (One-time Use)</h2>
            <p style="text-align:center;">This page is for creating a custom admin. Delete after use.</p>
            <?php
            if (isset($msg)) echo '<p style="color:green;text-align:center;">'.$msg.'</p>';
            if (isset($error)) echo '<p style="color:red;text-align:center;">'.$error.'</p>';
            ?>
            <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Admin Name" readonly><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Admin Email" readonly><br>
            <input type="text" name="password" value="<?php echo htmlspecialchars($password); ?>" placeholder="Password" readonly><br>
        </form>
    </div>
</body>
