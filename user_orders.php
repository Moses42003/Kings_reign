<?php
include('db.php');
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Get user info
$user = $conn->query("SELECT fname, lname, address, email, phone FROM users WHERE id='$user_id'")->fetch_assoc();

// Get user stats
$orders_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = '$user_id'")->fetch_assoc()['count'];
$messages_count = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE user_id = '$user_id'")->fetch_assoc()['count'];
$cart_count = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = '$user_id'")->fetch_assoc()['count'];

// Get orders with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$orders_query = "SELECT o.*, 
                 COUNT(oi.id) as item_count,
                 SUM(oi.quantity * oi.price) as total_amount
                 FROM orders o 
                 LEFT JOIN order_items oi ON o.id = oi.order_id 
                 WHERE o.user_id = '$user_id' 
                 GROUP BY o.id 
                 ORDER BY o.created_at DESC 
                 LIMIT $per_page OFFSET $offset";

$orders_result = $conn->query($orders_query);

// Get total orders count
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = '$user_id'")->fetch_assoc()['count'];
$total_pages = ceil($total_orders / $per_page);

function getOrderStatus($status) {
    $statuses = [
        'pending' => ['label' => 'Pending', 'color' => 'warning'],
        'processing' => ['label' => 'Processing', 'color' => 'info'],
        'shipped' => ['label' => 'Shipped', 'color' => 'primary'],
        'delivered' => ['label' => 'Delivered', 'color' => 'success'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger']
    ];
    
    return $statuses[$status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Kings Reign</title>
    <link rel="stylesheet" href="styles/modern_style.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .user-dashboard {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem 0;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .dashboard-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }

        .user-info h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            margin: 0 0 0.5rem 0;
        }

        .user-info p {
            color: #6b7280;
            font-size: 1.1rem;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        .sidebar {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
            height: fit-content;
        }

        .sidebar-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 3px solid #e5e7eb;
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .main-content {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
        }

        .content-header {
            margin-bottom: 2rem;
        }

        .content-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .content-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }

        .orders-grid {
            display: grid;
            gap: 1.5rem;
        }

        .order-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-color: #d1d5db;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .order-id {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
        }

        .order-date {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-processing {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-shipped {
            background: #e0e7ff;
            color: #3730a3;
        }

        .status-delivered {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
        }

        .order-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .no-orders {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .no-orders i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-orders h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .no-orders p {
            margin-bottom: 1.5rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            background: white;
            color: #374151;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .pagination-btn:hover {
            background: #f3f4f6;
        }

        .pagination-btn.active {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            border-color: #2563eb;
        }

        @media (max-width: 768px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="user-dashboard">
        <div class="dashboard-container">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div class="user-welcome">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['fname'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <h1>Welcome back, <?php echo htmlspecialchars($user['fname']); ?>!</h1>
                        <p>Manage your account, orders, and messages</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-number"><?php echo $orders_count; ?></div>
                        <div class="stat-label">Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-number"><?php echo $messages_count; ?></div>
                        <div class="stat-label">Messages</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-number"><?php echo $cart_count; ?></div>
                        <div class="stat-label">Cart Items</div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <!-- Sidebar Navigation -->
                <aside class="sidebar">
                    <h3 class="sidebar-title">Account Menu</h3>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <a href="update_account.php" class="nav-link">
                                <i class="fas fa-user-edit"></i>
                                <span>Update Account</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="user_orders.php" class="nav-link active">
                                <i class="fas fa-shopping-bag"></i>
                                <span>My Orders</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="user_messages.php" class="nav-link">
                                <i class="fas fa-envelope"></i>
                                <span>Messages</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php" class="nav-link">
                                <i class="fas fa-home"></i>
                                <span>Back to Home</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </aside>

                <!-- Main Content -->
                <main class="main-content">
                    <div class="content-header">
                        <h2 class="content-title">My Orders</h2>
                        <p class="content-subtitle">Track your order history and status</p>
                    </div>

                    <?php if($orders_result && $orders_result->num_rows > 0): ?>
                        <div class="orders-grid">
                            <?php while($order = $orders_result->fetch_assoc()): ?>
                                <?php $status = getOrderStatus($order['status']); ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div>
                                            <div class="order-id">Order #<?php echo $order['id']; ?></div>
                                            <div class="order-date"><?php echo formatDate($order['created_at']); ?></div>
                                        </div>
                                        <div class="order-status status-<?php echo $order['status']; ?>">
                                            <?php echo $status['label']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="order-details">
                                        <div class="detail-item">
                                            <div class="detail-label">Items</div>
                                            <div class="detail-value"><?php echo $order['item_count']; ?> items</div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Total Amount</div>
                                            <div class="detail-value">GH ₵<?php echo number_format($order['total_amount'], 2); ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Payment Method</div>
                                            <div class="detail-value"><?php echo ucfirst($order['payment_method'] ?? 'Cash on Delivery'); ?></div>
                                        </div>
                                        <div class="detail-item">
                                            <div class="detail-label">Shipping Address </div>
                                            <div class="detail-value"><?php echo htmlspecialchars($order['shipping_address']); ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="order-actions">
                                        <button class="btn btn-primary" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <?php if($order['status'] === 'pending'): ?>
                                            <button class="btn btn-secondary" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-times"></i> Cancel Order
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>" class="pagination-btn">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>" class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>" class="pagination-btn">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-orders">
                            <i class="fas fa-shopping-bag"></i>
                            <h3>No Orders Yet</h3>
                            <p>You haven't placed any orders yet. Start shopping to see your order history here.</p>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> Start Shopping
                            </a>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>

    <script>
        function viewOrderDetails(orderId) {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            fetch('get_order_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'order_id=' + orderId
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire('Error', data.message || 'Could not fetch order details.', 'error');
                    return;
                }
                const order = data.order;
                const items = data.items;
                let itemsHtml = '';
                let total = 0;
                items.forEach(item => {
                    const itemTotal = item.price * item.quantity;
                    total += itemTotal;
                    itemsHtml += `
                        <tr>
                            <td style="padding:8px 12px;">${item.product_name}</td>
                            <td style="padding:8px 12px; text-align:center;">${item.quantity}</td>
                            <td style="padding:8px 12px; text-align:right;">GH ₵${parseFloat(item.price).toFixed(2)}</td>
                            <td style="padding:8px 12px; text-align:right;">GH ₵${itemTotal.toFixed(2)}</td>
                        </tr>
                    `;
                });
                Swal.fire({
                    title: `Order #${order.id}`,
                    html: `
                        <div style="text-align:left;">
                            <p><strong>Status:</strong> ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}</p>
                            <p><strong>Placed on:</strong> ${order.created_at}</p>
                            <p><strong>Shipping Address( You can change this in your account settings):</strong> <br/> ${order.shipping_address}</p>
                            <p><strong>Payment Method:</strong> ${order.payment_method}</p>
                            <hr/>
                            <table style="width:100%;border-collapse:collapse;font-size:0.98em;">
                                <thead>
                                    <tr style="background:#f3f4f6;">
                                        <th style="padding:8px 12px;text-align:left;">Product</th>
                                        <th style="padding:8px 12px;text-align:center;">Qty</th>
                                        <th style="padding:8px 12px;text-align:right;">Price</th>
                                        <th style="padding:8px 12px;text-align:right;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                </tbody>
                                <tfoot>
                                    <tr style="font-weight:bold;">
                                        <td colspan="3" style="padding:8px 12px;text-align:right;">Grand Total:</td>
                                        <td style="padding:8px 12px;text-align:right;">GH ₵${total.toFixed(2)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    `,
                    width: 700,
                    showCloseButton: true,
                    confirmButtonText: 'Close',
                    customClass: { popup: 'swal2-order-details' }
                });
            })
            .catch(() => {
                Swal.fire('Error', 'Network error. Please try again.', 'error');
            });
        }

        function cancelOrder(orderId) {
            Swal.fire({
                title: 'Cancel Order?',
                text: 'Are you sure you want to cancel this order? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading while processing
                    Swal.fire({
                        title: 'Cancelling Order...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // Call cancel endpoint (adjust to your actual API/handler)
                    fetch('cancel_order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'order_id=' + orderId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Cancelled!', 'The order has been cancelled.', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message || 'Failed to cancel the order.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    });
                }
            });
        }

    </script>
</body>
</html>
