<?php
// Example Admin Page using Reusable Components
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');

// Set page variables for layout
$page_title = 'Example Page';
$page_description = 'This is an example page showing how to use the reusable components';
$show_back_button = true;
$back_url = 'dashboard.php';
$header_actions = '<button class="btn btn-primary" onclick="exampleAction()"><i class="fas fa-plus"></i> Example Action</button>';

// Custom CSS for this page
$custom_css = '
.example-content {
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.example-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.example-card h3 {
    color: #1e293b;
    margin-bottom: 0.5rem;
}

.example-card p {
    color: #64748b;
    line-height: 1.6;
}
';

// Custom JavaScript for this page
$custom_js = '
function exampleAction() {
    showNotification("Example action performed!", "success");
}

function showNotification(message, type = "info") {
    const notification = document.createElement("div");
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-triangle" : "info"}"></i>
        ${message}
    `;
    
    notification.style.position = "fixed";
    notification.style.top = "20px";
    notification.style.left = "50%";
    notification.style.transform = "translateX(-50%)";
    notification.style.zIndex = "9999";
    notification.style.minWidth = "300px";
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Kings Reign Admin</title>
    <link rel="stylesheet" href="../styles/modern_admin.css">
    <link rel="shortcut icon" href="../images/logos/logo-black.jpg" type="image/x-icon">
    <style><?php echo $custom_css; ?></style>
</head>
<body>
    <div class="admin-layout">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Include Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Page Content -->
            <div class="admin-content">
                <div class="example-content">
                    <h2>Welcome to the Example Page</h2>
                    <p>This page demonstrates how to use the reusable sidebar and header components.</p>
                    
                    <div class="example-card">
                        <h3>How to Use Components</h3>
                        <p>1. Set page variables before including components</p>
                        <p>2. Include the sidebar and header components</p>
                        <p>3. Add your page-specific content</p>
                        <p>4. Add custom CSS and JavaScript as needed</p>
                    </div>
                    
                    <div class="example-card">
                        <h3>Available Variables</h3>
                        <p><strong>$page_title</strong> - Sets the page title</p>
                        <p><strong>$page_description</strong> - Sets the page description</p>
                        <p><strong>$show_back_button</strong> - Shows/hides back button</p>
                        <p><strong>$back_url</strong> - Sets the back button URL</p>
                        <p><strong>$header_actions</strong> - Custom header actions</p>
                        <p><strong>$custom_css</strong> - Page-specific CSS</p>
                        <p><strong>$custom_js</strong> - Page-specific JavaScript</p>
                    </div>
                    
                    <div class="example-card">
                        <h3>Benefits</h3>
                        <p>• Consistent navigation across all admin pages</p>
                        <p>• Easy maintenance - update one file to update all pages</p>
                        <p>• Automatic active state detection</p>
                        <p>• Built-in authentication checks</p>
                        <p>• Responsive design out of the box</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script><?php echo $custom_js; ?></script>
</body>
</html> 