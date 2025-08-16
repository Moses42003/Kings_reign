<?php
// Reusable Admin Header Component
// Include this file in all admin pages for consistent header

// Get page title from parameter or use default
$page_title = isset($page_title) ? $page_title : 'Admin Dashboard';
$show_back_button = isset($show_back_button) ? $show_back_button : false;
$back_url = isset($back_url) ? $back_url : 'dashboard.php';
$header_actions = isset($header_actions) ? $header_actions : '';
?>

<header class="admin-header">
    <div class="header-left">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <?php if ($show_back_button): ?>
            <a href="<?php echo $back_url; ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        <?php endif; ?>
    </div>
    
    <div class="admin-header-actions">
        <?php if ($header_actions): ?>
            <?php echo $header_actions; ?>
        <?php else: ?>
            <div class="header-actions-default">
                <button class="btn btn-secondary" onclick="refreshPage()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <div class="admin-notifications">
                    <button class="btn btn-outline" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" id="notificationCount">0</span>
                    </button>
                </div>
                <div class="admin-user-menu">
                    <button class="btn btn-outline" onclick="toggleUserMenu()">
                        <i class="fas fa-user"></i>
                        <span><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown" id="userDropdown">
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user-edit"></i> Profile
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>

<script>
// Header functionality
function refreshPage() {
    location.reload();
}

function toggleNotifications() {
    // Toggle notifications panel
    const panel = document.getElementById('notificationsPanel');
    if (panel) {
        panel.classList.toggle('show');
    }
}

function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const userDropdown = document.getElementById('userDropdown');
    const notificationsPanel = document.getElementById('notificationsPanel');
    
    if (userDropdown && !event.target.closest('.admin-user-menu')) {
        userDropdown.classList.remove('show');
    }
    
    if (notificationsPanel && !event.target.closest('.admin-notifications')) {
        notificationsPanel.classList.remove('show');
    }
});

// Check for new notifications every 30 seconds
setInterval(() => {
    fetch('check_admin_messages.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                const badge = document.getElementById('notificationCount');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = 'block';
                }
            }
        })
        .catch(error => console.log('Notification check failed:', error));
}, 30000);
</script> 