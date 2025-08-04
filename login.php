<?php
include('db.php');
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home_modern.php');
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard_modern.php');
    exit();
}

$message = '';
$messageType = '';

if (isset($_POST['submit'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } else {
        // Check for user login
        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['passwd'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['fname'] . ' ' . $user['lname'];
                header("Location: index.php");
                exit();
            } else {
                $message = 'Invalid password!';
                $messageType = 'error';
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
                    header("Location: admin/dashboard_modern.php");
                    exit();
                } else {
                    $message = 'Invalid password!';
                    $messageType = 'error';
                }
            } else {
                $message = 'User not found!';
                $messageType = 'error';
            }
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
    <link rel="stylesheet" href="styles/modern_style.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        .login-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .login-logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .login-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .login-subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 400;
        }

        .login-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 45px;
            color: #94a3b8;
            font-size: 18px;
        }

        .password-toggle {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 18px;
            position: absolute;
            right: 15px;
            top: 45px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #2563eb;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .login-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .login-divider span {
            background: white;
            padding: 0 15px;
            color: #64748b;
            font-size: 14px;
        }

        .social-login {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            background: white;
            color: #1e293b;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .social-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
        }

        .signup-link {
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        .signup-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }

        .forgot-password a {
            color: #64748b;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password a:hover {
            color: #2563eb;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: #1e293b;
            padding: 10px 15px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .back-home:hover {
            background: white;
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .login-card {
                margin: 20px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-form {
                padding: 30px 20px;
            }
            
            .social-login {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="index.php" class="back-home">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <img src="images/logos/logo-black.jpg" alt="Kings Reign">
                </div>
                <h1 class="login-title">Welcome Back</h1>
                <p class="login-subtitle">Sign in to your Kings Reign account</p>
            </div>
            
            <form class="login-form" method="post" action="login.php">
                <?php if(isset($_GET['signup']) && $_GET['signup']==='success'): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Signup successful! Please login with your credentials.
                    </div>
                <?php endif; ?>
                
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo ($messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-triangle' : 'info')); ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="Enter your email address" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                
                <button type="submit" name="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
                
                <div class="login-divider">
                    <span>or continue with</span>
                </div>
                
                <div class="social-login">
                    <button type="button" class="social-btn" onclick="socialLogin('google')">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn" onclick="socialLogin('facebook')">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </button>
                </div>
                
                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up here</a>
                </div>
                
                <div class="forgot-password">
                    <a href="forgot_password.php">Forgot your password?</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleBtn.className = 'fas fa-eye';
            }
        }

        // Social login functionality
        function socialLogin(provider) {
            // Implement social login functionality
            showNotification(`${provider} login coming soon!`, 'info');
        }

        // Show notifications
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i>
                ${message}
            `;
            
            // Position notification at top of page
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.left = '50%';
            notification.style.transform = 'translateX(-50%)';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                showNotification('Please fill in all fields.', 'error');
                return false;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                showNotification('Please enter a valid email address.', 'error');
                return false;
            }
        });

        // Email validation
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Auto-focus on email input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });

        // Enter key to submit
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>

