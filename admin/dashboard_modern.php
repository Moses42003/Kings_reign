<?php
// Modern Admin Dashboard
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');

// Get dashboard statistics
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$totalMessages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages"))['count'];

// Get recent orders
$recentOrders = mysqli_query($conn, "SELECT o.*, u.fname, u.lname, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");

// Get recent messages
$recentMessages = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");

// Set page variables for layout
$page_title = 'Dashboard';
$page_description = 'Kings Reign Admin Dashboard - Overview and Statistics';
$header_actions = '<button class="btn btn-secondary" onclick="refreshStats()"><i class="fas fa-sync-alt"></i> Refresh</button>';

// Start output buffering
ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Kings Reign Admin</title>
    <link rel="stylesheet" href="../styles/modern_admin.css">
    <link rel="shortcut icon" href="../images/logos/logo-black.jpg" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Include Header -->
            <?php include 'includes/header.php'; ?>

            <div class="admin-content">
                <!-- Statistics Cards -->
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <span class="card-title">Total Users</span>
                            <div class="card-icon primary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo number_format($totalUsers); ?></div>
                        <div class="card-change positive">
                            <i class="fas fa-arrow-up"></i> +12% from last month
                        </div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <span class="card-title">Total Products</span>
                            <div class="card-icon success">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo number_format($totalProducts); ?></div>
                        <div class="card-change positive">
                            <i class="fas fa-arrow-up"></i> +5% from last month
                        </div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <span class="card-title">Total Orders</span>
                            <div class="card-icon warning">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo number_format($totalOrders); ?></div>
                        <div class="card-change positive">
                            <i class="fas fa-arrow-up"></i> +8% from last month
                        </div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <span class="card-title">Messages</span>
                            <div class="card-icon danger">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo number_format($totalMessages); ?></div>
                        <div class="card-change negative">
                            <i class="fas fa-arrow-down"></i> -3% from last month
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="dashboard-sections">
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Recent Orders</h2>
                            <a href="orders.php" class="btn btn-outline btn-sm">View All</a>
                        </div>
                        
                        <div class="admin-table">
                            <div class="table-content">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($order = mysqli_fetch_assoc($recentOrders)) { ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['fname'] . ' ' . $order['lname']); ?></td>
                                                <td>GH ₵<?php echo number_format($order['total'], 2); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $order['status']; ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Recent Messages</h2>
                            <a href="messages.php" class="btn btn-outline btn-sm">View All</a>
                        </div>
                        
                        <div class="messages-list">
                            <?php while($message = mysqli_fetch_assoc($recentMessages)) { ?>
                                <div class="message-card">
                                    <div class="message-header">
                                        <h4><?php echo htmlspecialchars($message['name']); ?></h4>
                                        <span class="message-date"><?php echo date('M j, Y, h:i:s', strtotime($message['created_at'])); ?></span>
                                    </div>
                                    <p class="message-email"><?php echo htmlspecialchars($message['email']); ?></p>
                                    <p class="message-content"><?php echo htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : ''); ?></p>
                                    <?php if ($message['reply']) { ?>
                                        <div class="message-reply">
                                            <strong>Replied:</strong> <?php echo htmlspecialchars(substr($message['reply'], 0, 50)) . (strlen($message['reply']) > 50 ? '...' : ''); ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <div class="actions-grid">
                        <a href="add_product.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <h3>Add Product</h3>
                            <p>Add new products to inventory</p>
                        </a>
                        
                        <a href="users.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>Manage Users</h3>
                            <p>View and manage user accounts</p>
                        </a>
                        
                        <a href="orders.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <h3>View Orders</h3>
                            <p>Process and manage orders</p>
                        </a>
                        
                        <a href="messages.php" class="action-card">
                            <div class="action-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3>Customer Support</h3>
                            <p>Respond to customer messages</p>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dashboard functionality
        function refreshStats() {
            location.reload();
        }

        // Auto-refresh every 5 minutes
        setInterval(refreshStats, 300000);

        // Add active class to current nav item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navItems = document.querySelectorAll('.nav-item');
            
            navItems.forEach(item => {
                if (item.getAttribute('href') === currentPage) {
                    item.classList.add('active');
                }
            });
        });

        // Show notifications
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Check for new messages every 30 seconds
        setInterval(() => {
            fetch('check_admin_messages.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.count > 0) {
                        showNotification(`You have ${data.count} new message(s)!`, 'info');
                    }
                });
        }, 30000);
    </script>
</body>
</html> 