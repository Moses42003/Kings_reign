<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Get cart items with product details
$cart_query = "SELECT c.*, p.name, p.price, p.stock, pi.image_path as main_image 
               FROM cart c 
               LEFT JOIN products p ON c.product_id = p.id 
               LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
               WHERE c.user_id = ?";
$stmt = $conn->prepare($cart_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

// Calculate total
$total_query = "SELECT SUM(c.quantity * p.price) as total 
                FROM cart c 
                LEFT JOIN products p ON c.product_id = p.id 
                WHERE c.user_id = ?";
$stmt = $conn->prepare($total_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();
$total = $total_result['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Kings Reign</title>
    <link rel="stylesheet" href="styles/modern_style.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .cart-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 2rem 0;
        }

        .cart-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .cart-header {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
        }

        .cart-title {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .cart-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }

        .cart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .cart-items {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 1rem;
            align-items: center;
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-color: #d1d5db;
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-details h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .item-stock {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: flex-end;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #f3f4f6;
        }

        .quantity-input {
            width: 50px;
            height: 32px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
        }

        .remove-btn {
            padding: 6px 12px;
            background: #fee2e2;
            color: #991b1b;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #fecaca;
        }

        .cart-summary {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #f1f5f9;
            height: fit-content;
        }

        .summary-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 2px solid #e5e7eb;
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
            margin-bottom: 1rem;
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

        .empty-cart {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .empty-cart i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .empty-cart h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }

        .empty-cart p {
            margin-bottom: 1.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
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

        @media (max-width: 768px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
                gap: 1rem;
            }
            
            .item-actions {
                grid-column: 1 / -1;
                flex-direction: row;
                justify-content: space-between;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <div class="cart-content">
            <!-- Cart Header -->
            <div class="cart-header">
                <h1 class="cart-title">
                    <i class="fas fa-shopping-cart"></i>
                    My Shopping Cart
                </h1>
                <p class="cart-subtitle">Review and manage your cart items</p>
            </div>

            <?php if($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if($cart_items && $cart_items->num_rows > 0): ?>
                <div class="cart-grid">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <h2 style="margin-bottom: 1.5rem; color: #111827;">Cart Items (<?php echo $cart_items->num_rows; ?>)</h2>
                        
                        <?php while($item = $cart_items->fetch_assoc()): ?>
                            <div class="cart-item" data-product-id="<?php echo $item['product_id']; ?>">
                                <img src="<?php echo $item['main_image'] ?? 'images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="item-image">
                                
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <div class="item-price">GH ₵<?php echo number_format($item['price'], 2); ?></div>
                                    <div class="item-stock">Stock: <?php echo $item['stock']; ?> available</div>
                                </div>
                                
                                <div class="item-actions">
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                        <input type="number" class="quantity-input" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock']; ?>"
                                               onchange="updateQuantity(<?php echo $item['product_id']; ?>, this.value, true)">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                                    </div>
                                    <button class="remove-btn" onclick="removeItem(<?php echo $item['product_id']; ?>)">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <h3 class="summary-title">Order Summary</h3>
                        
                        <div class="summary-item">
                            <span>Subtotal:</span>
                            <span>GH ₵<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="summary-item">
                            <span>Tax:</span>
                            <span>GH ₵0.00</span>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total:</span>
                            <span>GH ₵<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <button class="btn btn-primary" onclick="checkout()">
                            <i class="fas fa-credit-card"></i>
                            Proceed to Checkout
                        </button>
                        
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Continue Shopping
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your Cart is Empty</h3>
                    <p>Looks like you haven't added any items to your cart yet.</p>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i>
                        Start Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateQuantity(productId, change, isDirect = false) {
            let quantity;
            
            if (isDirect) {
                quantity = parseInt(change);
            } else {
                const input = document.querySelector(`[data-product-id="${productId}"] .quantity-input`);
                quantity = parseInt(input.value) + parseInt(change);
            }
            
            if (quantity < 1) {
                quantity = 1;
            }
            
            fetch('update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(() => {
                alert('Network error occurred.');
            });
        }

        function removeItem(productId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                fetch('update_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `product_id=${productId}&quantity=0`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(() => {
                    alert('Network error occurred.');
                });
            }
        }

        function checkout() {
            // Calculate total dynamically from cart items
            let totalAmount = 0;
            let totalItems = 0;
            
            document.querySelectorAll('.cart-item').forEach(item => {
                const quantity = parseInt(item.querySelector('.quantity-input').value);
                const priceText = item.querySelector('.item-price').textContent;
                const price = parseFloat(priceText.replace('GH ₵', '').replace(',', ''));
                
                if (!isNaN(quantity) && !isNaN(price)) {
                    totalAmount += quantity * price;
                    totalItems += quantity;
                }
            });
            
            const formattedTotal = new Intl.NumberFormat('en-GH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(totalAmount);
            
            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Order',
                html: `
                    <div style="text-align: left; margin-bottom: 1rem;">
                        <p style="margin-bottom: 0.5rem;"><strong>Order Summary:</strong></p>
                        <p style="margin-bottom: 0.5rem;">Total Items: ${totalItems}</p>
                        <p style="margin-bottom: 0.5rem;">Total Amount: GH ₵${formattedTotal}</p>
                        <p style="margin-bottom: 0.5rem;">Shipping: Free</p>
                        <p style="margin-bottom: 0.5rem;"><strong>Total: GH ₵${formattedTotal}</strong></p>
                    </div>
                    <p style="color: #6b7280; font-size: 0.9rem;">Are you sure you want to place this order?</p>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, Place Order!',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('process_order.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=place_order'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Order failed');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Request failed: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Order Placed Successfully!',
                        html: `
                            <div style="text-align: center;">
                                <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                                <p style="margin-bottom: 0.5rem;"><strong>Thank you for your order!</strong></p>
                                <p style="margin-bottom: 0.5rem;">Order ID: <strong>#${result.value.order_id}</strong></p>
                                <p style="margin-bottom: 0.5rem;">We'll send you an SMS confirmation shortly.</p>
                                <p style="color: #6b7280; font-size: 0.9rem;">You can track your order in "My Orders" section.</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'View My Orders',
                        showCancelButton: true,
                        cancelButtonText: 'Continue Shopping'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'user_orders.php';
                        } else {
                            window.location.href = 'index.php';
                        }
                    });
                }
            });
        }
    </script>
</body>
</html> 