<?php
include('db.php');
$message = '';
if (isset($_POST['submit'])) {
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    if ($password !== $cpassword) {
        $message = 'Passwords do not match!';
    } else {
        $hashedPass = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fname, lname, email, passwd) VALUES ('$firstname', '$lastname', '$email', '$hashedPass')";
        if($conn->query($sql) === TRUE){
            header("Location: login.php?signup=success");
            exit();
        }else{
            $message = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Kings Reign</title>
    <link rel="stylesheet" href="styles/home.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
</head>
<body>
    <div class="forms">
        <form class="signup" action="signup.php" method="post">
            <h2>Signup</h2>
            <?php if($message) echo '<p style="color:red;text-align:center;">'.$message.'</p>'; ?>
            <input type="text" name="firstname" id="firstname" placeholder="Enter FirstName" required>
            <input type="text" name="lastname" id="lastname" placeholder="Enter LastName" required>
            <input type="email" name="email" id="email" placeholder="Enter Your Email" required>
            <input type="password" name="password" id="password" placeholder="Enter a Password" required>
            <input type="password" name="cpassword" id="cpassword" placeholder="Confirm Password" required>
            <p>Already having an account? <a href="login.php">Login</a></p>
            <button name="submit" type="submit">SignUp</button>
        </form>
    </div>
</body>
</html>

