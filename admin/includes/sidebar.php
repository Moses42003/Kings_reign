<?php
// Reusable Admin Sidebar Component
// Include this file in all admin pages for consistent navigation

// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="admin-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="../images/logos/logo-black.jpg" alt="Kings Reign">
            <h1>Kings Reign</h1>
        </div>
        
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="sidebar-user-info">
                <h3><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></h3>
                <p>Administrator</p>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="dashboard.php" class="nav-item <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <!-- <a href="add_product.php" class="nav-item <?php echo $current_page === 'add_product.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus"></i> Add Product
            </a> -->
            <a href="products.php" class="nav-item <?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i> All Products
            </a>
            <a href="orders.php" class="nav-item <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-bag"></i> Orders
            </a>
            <a href="categories.php" class="nav-item <?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
                <i class="fas fa-tags"></i> Categories
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-section-title">Management</div>
            <a href="users.php" class="nav-item <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="admins.php" class="nav-item <?php echo $current_page === 'admins.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-shield"></i> Admins
            </a>

            <a href="messages.php" class="nav-item <?php echo $current_page === 'messages.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i> Messages
            </a>
        </div>
        
        <!-- <div class="nav-section">
            <div class="nav-section-title">Reports</div>
            <a href="reports.php" class="nav-item <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Sales Reports
            </a>
            <a href="analytics.php" class="nav-item <?php echo $current_page === 'analytics.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i> Analytics
            </a>
        </div> -->
        
        <!-- <div class="nav-section">
            <div class="nav-section-title">Settings</div>
            <a href="settings.php" class="nav-item <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i> General Settings
            </a>

        </div> -->
        
        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="profile.php" class="nav-item <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-edit"></i> Profile
            </a>
            <a href="logout.php" class="nav-item text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
</aside> 