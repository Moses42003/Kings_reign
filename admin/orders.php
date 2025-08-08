<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');

$message = '';
$messageType = '';

// Build search and filter query
$where_conditions = [];
$params = [];

// Search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_conditions[] = "(u.fname LIKE '%$search%' OR u.lname LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%')";
}

// Status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $_GET['status'];
    switch ($status) {
        case 'pending':
            $where_conditions[] = "o.status = 'pending'";
            break;
        case 'completed':
            $where_conditions[] = "o.status = 'completed'";
            break;
    }
}

// Build WHERE clause
$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;


// Fetch orders
$sql = "SELECT o.*, u.fname, u.lname, u.email, u.phone, u.address
        FROM orders o
        JOIN users u ON o.user_id = u.id
        $where_clause
        ORDER BY o.created_at DESC
        LIMIT $per_page OFFSET $offset";
$result = $conn->query($sql);




// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM orders o $where_clause";
$count_result = $conn->query($count_query);
$total_orders = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Kings Reign Admin</title>
    <link rel="stylesheet" href="../styles/modern_admin.css">
    <link rel="shortcut icon" href="../images/logos/logo-black.jpg" type="image/x-icon">
    <style>
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        .admin-main {
            flex: 1;
        }
        .admin-content {
            padding: 2rem;
        }
        .orders-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .table th {
            background: #f8fafc;
            font-weight: 600;
        }
        .table tbody tr:hover {
            background: #f1f5f9;
        }
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .badge-warning {
            background: #f59e0b;
            color: #fff;
        }
        .badge-success {
            background: #10b981;
            color: #fff;
        }
        .badge-secondary {
            background: #6b7280;
            color: #fff;
        }
        .filters-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .filter-input {
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .alert {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }
        .alert-info {
            background: #e2e8f0;
            color: #2563eb;
        }
        .alert-success {
            background: #d1fae5;
            color: #10b981;
        }
        .filters-section h3 {
            margin-bottom: 1rem;
            font-size: 1.25rem;
            font-weight: 600;
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        .alert {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }
        .alert-info {
            background: #e2e8f0;
            color: #2563eb;
        }
        .alert-success {
            background: #d1fae5;
            color: #10b981;
        }
        .filters-section h3 {
            margin-bottom: 1rem;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .orders-container {
            padding: 2rem;
        }

        .orders-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .orders-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            text-align: center;
            min-width: 120px;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
<div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <div class="orders-container">
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>


                    <!-- Filters Section -->
                    <div class="filters-section">
                        <h3><i class="fas fa-filter"></i> Filters & Search</h3>
                        <div class="filters-grid">
                            <div class="filter-group">
                                <label class="filter-label" for="search">Search Orders</label>
                                <input type="text" id="search" class="filter-input" 
                                       placeholder="Search by customer name, order ID, or status"
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label" for="status">Status</label>
                                <select id="status" class="filter-input">
                                    <option value="">All</option>
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label" for="payment_method">Payment Method</label>
                                <select id="payment_method" class="filter-input">
                                    <option value="">All</option>
                                    <option value="Cash on Delivery">Cash on Delivery</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                            <button class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <button class="btn btn-secondary" onclick="clearFilters()">
                                <i class="fas fa-times"></i> Clear Filters
                            </button>
                        </div>
                    </div>

                                        <!-- orders Header -->
                    <div class="orders-header">
                        <div class="orders-stats">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo number_format($total_orders); ?></div>
                                <div class="stat-label">Total orders</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?php echo number_format($total_pages); ?></div>
                                <div class="stat-label">Pages</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number"><?php echo number_format($per_page); ?></div>
                                <div class="stat-label">Per Page</div>
                            </div>
                        </div>
                    </div>



                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Placed On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($order['id']) ?></td>
                                        <td><?= htmlspecialchars($order['fname']) ?> <?= htmlspecialchars($order['lname']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $order['status'] === 'pending' ? 'warning' : ($order['status'] === 'completed' ? 'success' : 'secondary') ?>">
                                                <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($order['payment_method']) ?></td>
                                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary me-2" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-danger" onclick="cancelOrder(<?= $order['id'] ?>)">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>





<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
// Search and filter functionality
function applyFilters() {
    const search = document.getElementById("search").value;
    const status = document.getElementById("status").value;
    const payment_method = document.getElementById("payment_method").value;
    
    const params = new URLSearchParams();
    if (search) params.append("search", search);
    if (status) params.append("status", status);
    if (payment_method) params.append("payment_method", payment_method);
    
    window.location.href = "orders.php?" + params.toString();
}


function clearFilters() {
    window.location.href = "orders.php";
}

function viewOrderDetails(orderId) {
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch('get_order_details.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
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
                    <td>${item.product_name}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">GH₵${item.price.toFixed(2)}</td>
                    <td class="text-end">GH₵${itemTotal.toFixed(2)}</td>
                </tr>`;
        });

        Swal.fire({
            title: `Order #${order.id}`,
            html: `
                <div class="text-start">
                    <p><strong>Status:</strong> ${order.status}</p>
                    <p><strong>Placed on:</strong> ${order.created_at}</p>
                    <p><strong>Shipping Address:</strong> <br> ${order.shipping_address}</p>
                    <p><strong>Payment Method:</strong> ${order.payment_method}</p>
                    <hr>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end">Grand Total:</td>
                                <td class="text-end">GH₵${total.toFixed(2)}</td>
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
    .catch(() => Swal.fire('Error', 'Network error. Please try again.', 'error'));
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
    }).then(result => {
        if (result.isConfirmed) {
            Swal.fire({ title: 'Cancelling Order...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            fetch('cancel_order.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'order_id=' + orderId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Cancelled!', 'The order has been cancelled.', 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message || 'Failed to cancel the order.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Network error. Please try again.', 'error'));
        }
    });
}
</script>
</body>
</html>
