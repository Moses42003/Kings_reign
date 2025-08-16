<?php
// Professional Admin Dashboard
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get dashboard statistics
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$totalProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products"))['count'];
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as total FROM orders WHERE status = 'completed'"))['total'] ?? 0;
$pendingOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"))['count'];
$cancelledOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'"))['count'];
$lowStockProducts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM products WHERE stock <= 5"))['count'];
// Get recent orders
$recentOrders = mysqli_query($conn, "SELECT o.*, u.fname, u.lname, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
$totalMessages = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM contact_messages"))['count'];
// Get recent messages
$recentMessages = mysqli_query($conn, "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");

// Get top selling products
$topProducts = mysqli_query($conn, "SELECT p.name, p.price, p.stock, COUNT(oi.id) as sales_count 
                                   FROM products p 
                                   LEFT JOIN order_items oi ON p.id = oi.product_id 
                                   GROUP BY p.id 
                                   ORDER BY sales_count DESC 
                                   LIMIT 5");

// Get category statistics
$categoryStats = mysqli_query($conn, "SELECT category_id, COUNT(*) as count FROM products GROUP BY category_id ORDER BY count DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kings Reign</title>
    <link rel="stylesheet" href="../styles/modern_admin.css">
    <link rel="shortcut icon" href="../images/logos/logo-black.jpg" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="../images/logos/logo-black.jpg" alt="Kings Reign">
                    <h1>Kings Reign</h1>
                </div>
                
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar">
                        <i class="fas fa-user-shield"></i>
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
                    <a href="dashboard.php" class="nav-item active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="products.php" class="nav-item">
                        <i class="fas fa-box"></i>All Products
                    </a>
                    <a href="orders.php" class="nav-item">
                        <i class="fas fa-shopping-bag"></i> Orders
                    </a>

                    <a href="categories.php" class="nav-item">
                        <i class="fas fa-tags"></i> Categories
                    </a>

                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="users.php" class="nav-item">
                        <i class="fas fa-users"></i> Users
                    </a>

                    <a href="admins.php" class="nav-item">
                        <i class="fas fa-user-shield"></i> Admins
                    </a>
                    <!-- <a href="inventory.php" class="nav-item">
                        <i class="fas fa-warehouse"></i> Inventory
                    </a>
                    <a href="reports.php" class="nav-item">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a> -->
                    <a href="messages.php" class="nav-item">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <!-- <a href="settings.php" class="nav-item">
                        <i class="fas fa-cog"></i> Settings
                    </a> -->
                    <a href="#" class="nav-item <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-edit"></i> Profile
                    </a>
                    <a href="logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard Overview</h1>
                <div class="admin-header-actions">
                    <button class="btn btn-secondary" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="exportData()">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </header>

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
                            <span class="card-title">Total Revenue</span>
                            <div class="card-icon danger">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="card-value">GH ₵<?php echo number_format($totalRevenue, 2); ?></div>
                        <div class="card-change positive">
                            <i class="fas fa-arrow-up"></i> +15% from last month
                        </div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <span class="card-title">Cancelled Orders</span>
                            <div class="card-icon danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo number_format($cancelledOrders); ?></div>
                        <div class="card-change negative">
                            <i class="fas fa-arrow-down"></i> -3% from last month
                        </div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <span class="card-title">Pending Orders</span>
                            <div class="card-icon warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo number_format($pendingOrders); ?></div>
                        <div class="card-change negative">
                            <i class="fas fa-arrow-down"></i> -3% from last month
                        </div>
                    </div>

                    <div class="dashboard-card">
                        <div class="card-header">
                            <span class="card-title">Low Stock Items</span>
                            <div class="card-icon danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="card-value"><?php echo number_format($lowStockProducts); ?></div>
                        <div class="card-change negative">
                            <i class="fas fa-arrow-up"></i> +2% from last month
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
                                            <th>Actions</th>
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
                                                <td>
                                                    <button class="btn btn-sm btn-primary" onclick="viewOrder(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Top Selling Products</h2>
                            <a href="products.php" class="btn btn-outline btn-sm">View All</a>
                        </div>
                        
                        <div class="products-list">
                            <?php while($product = mysqli_fetch_assoc($topProducts)) { ?>
                                <div class="product-item">
                                    <div class="product-info">
                                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p>GH ₵<?php echo number_format($product['price'], 2); ?></p>
                                    </div>
                                    <div class="product-stats">
                                        <span class="sales-count"><?php echo $product['sales_count']; ?> sold</span>
                                        <span class="stock-count"><?php echo $product['stock']; ?> in stock</span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>





                <!-- Charts and Analytics -->
                <div class="dashboard-sections">
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Sales Analytics</h2>
                        </div>
                        <div class="chart-container">
                            <canvas id="salesChart" width="400" height="200"></canvas>
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
        function refreshDashboard() {
            location.reload();
        }

        function exportData() {
            // Implement export functionality
            showNotification('Export feature coming soon!', 'info');
        }

        function viewOrder(orderId) {
            window.location.href = 'order_details.php?id=' + orderId;
        }

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

        // Charts
        document.addEventListener('DOMContentLoaded', function() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Sales',
                        data: [12000, 19000, 15000, 25000, 22000, 30000],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Category Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Electronics', 'Fashion', 'Home & Office', 'Appliances'],
                    datasets: [{
                        data: [35, 25, 20, 20],
                        backgroundColor: [
                            '#2563eb',
                            '#10b981',
                            '#f59e0b',
                            '#ef4444'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });

        // Auto-refresh every 5 minutes
        setInterval(refreshDashboard, 300000);
    </script>
</body>
</html> 