<?php
include('db.php');
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard_modern.php');
    exit();
}

$message = '';
$messageType = '';

if (isset($_POST['submit'])) {
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $pnum = $_POST['pnum'];
    $address = $_POST['address'];
    
    // Validation
    if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($cpassword) || empty($pnum) || empty($address)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $messageType = 'error';
    } elseif ($password !== $cpassword) {
        $message = 'Passwords do not match!';
        $messageType = 'error';
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM users WHERE email='$email'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result && $check_result->num_rows > 0) {
            $message = 'Email address already registered!';
            $messageType = 'error';
        } else {
            $hashedPass = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (fname, lname, email, passwd, phone, address) VALUES ('$firstname', '$lastname', '$email', '$hashedPass', '$pnum', '$address')";
            
            if($conn->query($sql) === TRUE){
                header("Location: login.php?signup=success");
                exit();
            } else {
                $message = "Error: " . $conn->error;
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
    <title>Sign Up - Kings Reign</title>
    <link rel="stylesheet" href="styles/modern_style.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
    <style>
        .signup-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .signup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            position: relative;
        }

        .signup-header {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .signup-logo {
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

        .signup-logo img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .signup-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .signup-subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 400;
        }

        .signup-form {
            padding: 40px 30px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
            flex: 1;
        }

        .form-group.full-width {
            flex: none;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: var(--bg-secondary);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 45px;
            color: var(--text-light);
            font-size: 18px;
        }

        .password-toggle {
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            font-size: 18px;
            position: absolute;
            right: 15px;
            top: 45px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
        }

        .signup-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .signup-divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }

        .signup-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--border-color);
        }

        .signup-divider span {
            background: white;
            padding: 0 15px;
            color: var(--text-secondary);
            font-size: 14px;
        }

        .social-signup {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            background: white;
            color: var(--text-primary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .social-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .login-link {
            text-align: center;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
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

        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            background: var(--border-color);
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: #ef4444; width: 25%; }
        .strength-fair { background: #f59e0b; width: 50%; }
        .strength-good { background: #10b981; width: 75%; }
        .strength-strong { background: #059669; width: 100%; }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--text-primary);
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

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
        }

        .terms-checkbox input[type="checkbox"] {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .terms-checkbox label {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .terms-checkbox a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .terms-checkbox a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .signup-card {
                margin: 20px;
            }
            
            .signup-header {
                padding: 30px 20px;
            }
            
            .signup-form {
                padding: 30px 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .social-signup {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <a href="index.php" class="back-home">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
        
        <div class="signup-card">
            <div class="signup-header">
                <div class="signup-logo">
                    <img src="images/logos/logo-black.jpg" alt="Kings Reign">
                </div>
                <h1 class="signup-title">Join Kings Reign</h1>
                <p class="signup-subtitle">Create your account and start shopping</p>
            </div>
            
            <form class="signup-form" method="post" action="signup.php">
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo ($messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-triangle' : 'info')); ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="firstname">First Name</label>
                        <input type="text" id="firstname" name="firstname" class="form-input" 
                               placeholder="Enter your first name" required 
                               value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="lastname">Last Name</label>
                        <input type="text" id="lastname" name="lastname" class="form-input" 
                               placeholder="Enter your last name" required 
                               value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>
                
                
                <div class="form-group full-width">
                    <label class="form-label" for="cpassword">Phone Number</label>
                    <input type="tel" id="pnum" name="pnum" class="form-input" 
                    placeholder="Enter Phone Number" required>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label" for="cpassword">Address</label>
                    <input type="address" id="address" name="address" class="form-input" 
                    placeholder="Enter Address" required>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="Enter your email address" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Create a strong password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                    <div class="password-strength">
                        <span id="strength-text">Password strength</span>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label" for="cpassword">Confirm Password</label>
                    <input type="password" id="cpassword" name="cpassword" class="form-input" 
                           placeholder="Confirm your password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('cpassword')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#terms">Terms of Service</a> and <a href="#privacy">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" name="submit" class="signup-btn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
                
                <div class="signup-divider">
                    <span>or sign up with</span>
                </div>
                
                <div class="social-signup">
                    <button type="button" class="social-btn" onclick="socialSignup('google')">
                        <i class="fab fa-google"></i>
                        Google
                    </button>
                    <button type="button" class="social-btn" onclick="socialSignup('facebook')">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </button>
                </div>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleBtn = passwordInput.parentNode.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleBtn.className = 'fas fa-eye';
            }
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            let strength = 0;
            const strengthText = document.getElementById('strength-text');
            const strengthFill = document.getElementById('strength-fill');
            
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            strengthFill.className = 'strength-fill';
            
            if (strength <= 2) {
                strengthText.textContent = 'Weak';
                strengthFill.classList.add('strength-weak');
            } else if (strength <= 3) {
                strengthText.textContent = 'Fair';
                strengthFill.classList.add('strength-fair');
            } else if (strength <= 4) {
                strengthText.textContent = 'Good';
                strengthFill.classList.add('strength-good');
            } else {
                strengthText.textContent = 'Strong';
                strengthFill.classList.add('strength-strong');
            }
        }

        // Social signup functionality
        function socialSignup(provider) {
            showNotification(`${provider} signup coming soon!`, 'info');
        }

        // Show notifications
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i>
                ${message}
            `;
            
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
            const firstname = document.getElementById('firstname').value.trim();
            const lastname = document.getElementById('lastname').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            const cpassword = document.getElementById('cpassword').value.trim();
            const terms = document.getElementById('terms').checked;
            
            if (!firstname || !lastname || !email || !password || !cpassword) {
                e.preventDefault();
                showNotification('Please fill in all fields.', 'error');
                return false;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                showNotification('Please enter a valid email address.', 'error');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showNotification('Password must be at least 6 characters long.', 'error');
                return false;
            }
            
            if (password !== cpassword) {
                e.preventDefault();
                showNotification('Passwords do not match.', 'error');
                return false;
            }
            
            if (!terms) {
                e.preventDefault();
                showNotification('Please agree to the Terms of Service.', 'error');
                return false;
            }
        });

        // Email validation
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Password strength monitoring
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });

        // Auto-focus on first name input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('firstname').focus();
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

