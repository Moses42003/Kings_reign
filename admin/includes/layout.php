<?php
// Reusable Admin Layout Component
// This file provides the basic HTML structure for all admin pages

// Ensure session is started
if (!isset($_SESSION)) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

// Set default values for layout variables
$page_title = isset($page_title) ? $page_title : 'Admin Dashboard';
$page_description = isset($page_description) ? $page_description : '';
$show_back_button = isset($show_back_button) ? $show_back_button : false;
$back_url = isset($back_url) ? $back_url : 'dashboard.php';
$header_actions = isset($header_actions) ? $header_actions : '';
$custom_css = isset($custom_css) ? $custom_css : '';
$custom_js = isset($custom_js) ? $custom_js : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Kings Reign Admin</title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../styles/modern_admin.css">
    <link rel="shortcut icon" href="../images/logos/logo-black.jpg" type="image/x-icon">
    
    <!-- Custom CSS -->
    <?php if ($custom_css): ?>
        <style><?php echo $custom_css; ?></style>
    <?php endif; ?>
</head>
<body>
    <div class="admin-layout">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Include Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Page Content -->
            <div class="admin-content">
                <?php if (isset($content)): ?>
                    <?php echo $content; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <!-- Custom JavaScript -->
    <?php if ($custom_js): ?>
        <script><?php echo $custom_js; ?></script>
    <?php endif; ?>
</body>
</html> 