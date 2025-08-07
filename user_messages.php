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

// Get messages with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$messages_query = "SELECT * FROM contact_messages WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$messages_result = $conn->query($messages_query);

// Get total messages count
$total_messages = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE user_id = '$user_id'")->fetch_assoc()['count'];
$total_pages = ceil($total_messages / $per_page);

function formatDate($date) {
    return date('M d, Y H:i', strtotime($date));
}

function getMessageStatus($status) {
    $statuses = [
        'unread' => ['label' => 'Unread', 'color' => 'warning'],
        'read' => ['label' => 'Read', 'color' => 'success'],
        'replied' => ['label' => 'Replied', 'color' => 'primary']
    ];
    
    return $statuses[$status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Messages - Kings Reign</title>
    <link rel="stylesheet" href="styles/modern_style.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
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

        .messages-grid {
            display: grid;
            gap: 1.5rem;
        }

        .message-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            position: relative;
        }

        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-color: #d1d5db;
        }

        .message-card.unread {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .message-subject {
            font-size: 1.1rem;
            font-weight: 700;
            color: #111827;
        }

        .message-date {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .message-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-unread {
            background: #fef3c7;
            color: #92400e;
        }

        .status-read {
            background: #d1fae5;
            color: #065f46;
        }

        .status-replied {
            background: #dbeafe;
            color: #1e40af;
        }

        .message-content {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .message-preview {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
        }

        .message-actions {
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

        .no-messages {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .no-messages i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .no-messages h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .no-messages p {
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

        .unread-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 12px;
            height: 12px;
            background: #ef4444;
            border-radius: 50%;
        }

        @media (max-width: 768px) {
            .dashboard-content {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
            
            .message-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .message-actions {
                flex-direction: column;
            }
        }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
                            <a href="user_orders.php" class="nav-link">
                                <i class="fas fa-shopping-bag"></i>
                                <span>My Orders</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="user_messages.php" class="nav-link active">
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
                        <h2 class="content-title">My Messages</h2>
                        <p class="content-subtitle">View and manage your customer support messages</p>
                    </div>

                    <?php if($messages_result && $messages_result->num_rows > 0): ?>
                        <div class="messages-grid">
                            <?php while($message = $messages_result->fetch_assoc()): ?>
                                <?php $status = getMessageStatus($message['status'] ?? 'unread'); ?>
                                <div class="message-card <?php echo ($message['status'] ?? 'unread') === 'unread' ? 'unread' : ''; ?>">
                                    <?php if(($message['status'] ?? 'unread') === 'unread'): ?>
                                        <div class="unread-indicator"></div>
                                    <?php endif; ?>
                                    
                                    <div class="message-header">
                                        <div>
                                            <div class="message-subject"><?php echo htmlspecialchars($message['subject'] ?? 'No Subject'); ?></div>
                                            <div class="message-date"><?php echo formatDate($message['created_at']); ?></div>
                                        </div>
                                        <div class="message-status status-<?php echo $message['status'] ?? 'unread'; ?>">
                                            <?php echo $status['label']; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="message-content">
                                        <strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?> (<?php echo htmlspecialchars($message['email']); ?>)
                                    </div>
                                    
                                    <div class="message-preview">
                                        <?php 
                                        $preview = htmlspecialchars($message['message']);
                                        echo strlen($preview) > 150 ? substr($preview, 0, 150) . '...' : $preview;
                                        ?>
                                    </div>
                                    
                                    <div class="message-actions">
                                        <button class="btn btn-primary" onclick="viewMessage(<?php echo $message['id']; ?>)">
                                            <i class="fas fa-eye"></i> View Full Message
                                        </button>
                                        <?php if(($message['status'] ?? 'unread') === 'unread'): ?>
                                            <button class="btn btn-secondary" onclick="markAsRead(<?php echo $message['id']; ?>)">
                                                <i class="fas fa-check"></i> Mark as Read
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            <div class="text-center mt-3">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal">
                                    <i class="fas fa-envelope"></i> Send Another Message
                                </button>
                            </div>

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

                        <div class="no-messages">
                            <i class="fas fa-envelope"></i>
                            <h3>No Messages Yet</h3>
                            <p>You haven't sent any messages to customer support yet. Contact us if you need help!</p>
                        </div>
                        <div class="text-center mt-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal">
                            <i class="fas fa-envelope"></i> Send Message
                        </button>
                        </div>

                    <?php endif; ?>

                    <!-- Contact Modal -->
                    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                            <form id="contactForm">
                                <div class="modal-header">
                                <h5 class="modal-title" id="contactModalLabel">Contact Customer Support</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                
                                <div class="modal-body">
                                <div id="status" class="mb-3"></div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name</label>
                                    <input type="text" class="form-control" name="name" id="name" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Your Email</label>
                                    <input type="email" class="form-control" name="email" id="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" class="form-control" name="subject" id="subject" required>
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" name="message" id="message" rows="5" required></textarea>
                                </div>
                                </div>
                                
                                <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Send Message</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </form>
                            </div>
                        </div>
                    </div>



                    <!-- View Message Modal -->
                    <div class="modal fade" id="viewMessageModal" tabindex="-1" aria-labelledby="viewMessageModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="viewMessageModalLabel">Message Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            
                            <div class="modal-body">
                                <div id="viewMessageContent">
                                <div><strong>From:</strong> <span id="viewMessageName"></span> &lt;<span id="viewMessageEmail"></span>&gt;</div>
                                <div><strong>Subject:</strong> <span id="viewMessageSubject"></span></div>
                                <div><strong>Date:</strong> <span id="viewMessageDate"></span></div>
                                <hr>
                                <div id="viewMessageBody" style="white-space: pre-line;"></div>
                                </div>
                                <div id="viewMessageLoading" class="text-center my-4 d-none">
                                <div class="spinner-border text-primary"></div>
                                </div>
                                <div id="viewMessageError" class="alert alert-danger d-none">
                                Failed to load message.
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                            </div>
                        </div>
                    </div>




                </main>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function markAsRead(messageId) {
            if(confirm('Mark this message as read?')) {
                // Implement mark as read functionality
                alert('Mark as read functionality coming soon!');
            }
        }
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('contactForm');
            const status = document.getElementById('status');

            form.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            const data = new URLSearchParams(formData);

            const response = await fetch('contact_message.php', {
                method: 'POST',
                headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: data,
            });

            const result = await response.json();

            if (result.success) {
                status.innerHTML = `<div class="alert alert-success">Message sent successfully.</div>`;
                form.reset();
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('contactModal'));
                    modal.hide();
                    location.reload();
                }, 1500);
            } else {
                status.innerHTML = `<div class="alert alert-danger">${result.message}</div>`;
            }
            });
        });
    </script>




        <script>
            function viewMessage(messageId) {
            const modal = new bootstrap.Modal(document.getElementById('viewMessageModal'));
            const loading = document.getElementById('viewMessageLoading');
            const error = document.getElementById('viewMessageError');
            const content = document.getElementById('viewMessageContent');

            // Clear previous content
            document.getElementById('viewMessageName').textContent = '';
            document.getElementById('viewMessageEmail').textContent = '';
            document.getElementById('viewMessageSubject').textContent = '';
            document.getElementById('viewMessageDate').textContent = '';
            document.getElementById('viewMessageBody').textContent = '';

            // Show modal
            modal.show();
            loading.classList.remove('d-none');
            error.classList.add('d-none');
            content.classList.add('d-none');

            fetch(`get_message.php?id=${messageId}`)
                .then(response => response.json())
                .then(data => {
                if (!data.success) throw new Error();

                document.getElementById('viewMessageName').textContent = data.message.name;
                document.getElementById('viewMessageEmail').textContent = data.message.email;
                document.getElementById('viewMessageSubject').textContent = data.message.subject;
                document.getElementById('viewMessageDate').textContent = data.message.created_at;
                document.getElementById('viewMessageBody').textContent = data.message.message;

                loading.classList.add('d-none');
                content.classList.remove('d-none');
                })
                .catch(() => {
                loading.classList.add('d-none');
                error.classList.remove('d-none');
                });
            }
        </script>





</body>
</html>
