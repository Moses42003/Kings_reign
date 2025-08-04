<?php
session_start();
include('db.php');

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$product_id) {
    header('Location: index.php');
    exit();
}

// Get product details with category name
$product_query = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header('Location: index.php');
    exit();
}

// Get all product images
$images_query = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC";
$stmt = $conn->prepare($images_query);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$images_result = $stmt->get_result();
$product_images = [];
while ($row = $images_result->fetch_assoc()) {
    $product_images[] = $row;
}

// Get related products (same category, excluding current product)
$related_query = "SELECT p.*, pi.image_path as main_image 
                 FROM products p 
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
                 WHERE p.category_id = ? AND p.id != ? 
                 ORDER BY p.created_at DESC LIMIT 4";
$stmt = $conn->prepare($related_query);
$stmt->bind_param('ii', $product['category_id'], $product_id);
$stmt->execute();
$related_products = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Kings Reign</title>
    <link rel="stylesheet" href="styles/modern_style.css">
    <link rel="shortcut icon" href="images/logos/logo-black.jpg" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .product-details-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .product-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .back-btn {
            background: none;
            border: none;
            color: #2563eb;
            font-size: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #f3f4f6;
        }

        .breadcrumb {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .product-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .product-gallery {
            position: relative;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 1rem;
        }

        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 0.5rem;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .thumbnail.active {
            border-color: #2563eb;
        }

        .product-info {
            padding: 1rem 0;
        }

        .product-title {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .product-category {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .product-price-section {
            margin-bottom: 1.5rem;
        }

        .current-price {
            font-size: 2.5rem;
            font-weight: 900;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }

        .original-price {
            font-size: 1.2rem;
            color: #6b7280;
            text-decoration: line-through;
            margin-bottom: 0.5rem;
        }

        .discount-badge {
            display: inline-block;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .product-description {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .product-specs {
            margin-bottom: 1.5rem;
        }

        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .spec-label {
            font-weight: 600;
            color: #374151;
        }

        .spec-value {
            color: #6b7280;
        }

        .stock-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .stock-info.available {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .stock-info.low {
            background: #fef3c7;
            border-color: #fde68a;
        }

        .stock-info.out {
            background: #fef2f2;
            border-color: #fecaca;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
            flex: 1;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            flex: 1;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }

        .quantity-section {
            margin-bottom: 1.5rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: #f3f4f6;
        }

        .quantity-input {
            width: 80px;
            height: 40px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            text-align: center;
            font-size: 1rem;
        }

        .related-products {
            margin-top: 3rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 1.5rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
        }

        .related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .related-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .related-info {
            padding: 1rem;
        }

        .related-name {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .related-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2563eb;
        }

        @media (max-width: 768px) {
            .product-main {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .product-actions {
                flex-direction: column;
            }
            
            .related-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <div class="product-details-container">
        <!-- Product Header -->
        <div class="product-header">
            <button class="back-btn" onclick="history.back()">
                <i class="fas fa-arrow-left"></i> Back
            </button>
            <div class="breadcrumb">
                <a href="index.php">Home</a> > 
                <a href="index.php"><?php echo htmlspecialchars($product['category_name']); ?></a> > 
                <?php echo htmlspecialchars($product['name']); ?>
            </div>
        </div>

        <!-- Main Product Section -->
        <div class="product-main">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <img id="mainImage" src="<?php echo $product_images[0]['image_path'] ?? 'images/placeholder.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" class="main-image">
                
                <?php if (count($product_images) > 1): ?>
                    <div class="thumbnail-grid">
                        <?php foreach ($product_images as $index => $image): ?>
                            <img src="<?php echo $image['image_path']; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                                 onclick="changeMainImage(this, '<?php echo $image['image_path']; ?>')">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Information -->
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="product-category">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
                
                <div class="product-price-section">
                    <div class="current-price">GH ₵<?php echo number_format($product['price'], 2); ?></div>
                    <?php if ($product['original_price'] > $product['price']): ?>
                        <div class="original-price">GH ₵<?php echo number_format($product['original_price'], 2); ?></div>
                        <div class="discount-badge">-<?php echo $product['discount_percentage']; ?>% OFF</div>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>

                <div class="product-specs">
                    <div class="spec-item">
                        <span class="spec-label">Brand:</span>
                        <span class="spec-value"><?php echo htmlspecialchars($product['brand'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">SKU:</span>
                        <span class="spec-value"><?php echo htmlspecialchars($product['id']); ?></span>
                    </div>
                    <?php if ($product['subcategory']): ?>
                        <div class="spec-item">
                            <span class="spec-label">Subcategory:</span>
                            <span class="spec-value"><?php echo htmlspecialchars($product['subcategory']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="stock-info <?php echo $product['stock'] > 10 ? 'available' : ($product['stock'] > 0 ? 'low' : 'out'); ?>">
                    <i class="fas fa-<?php echo $product['stock'] > 10 ? 'check-circle' : ($product['stock'] > 0 ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
                    <strong>
                        <?php if ($product['stock'] > 10): ?>
                            In Stock (<?php echo $product['stock']; ?> available)
                        <?php elseif ($product['stock'] > 0): ?>
                            Low Stock (<?php echo $product['stock']; ?> left)
                        <?php else: ?>
                            Out of Stock
                        <?php endif; ?>
                    </strong>
                </div>

                <?php if ($product['stock'] > 0): ?>
                    <div class="quantity-section">
                        <label for="quantity">Quantity:</label>
                        <div class="quantity-controls">
                            <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="product-actions">
                    <?php if ($product['stock'] > 0): ?>
                        <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary" disabled>
                            <i class="fas fa-times"></i> Out of Stock
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if ($related_products->num_rows > 0): ?>
            <div class="related-products">
                <h2 class="section-title">Related Products</h2>
                <div class="related-grid">
                    <?php while ($related = $related_products->fetch_assoc()): ?>
                        <div class="related-card" onclick="viewProduct(<?php echo $related['id']; ?>)">
                            <img src="<?php echo $related['main_image'] ?? 'images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>" class="related-image">
                            <div class="related-info">
                                <h3 class="related-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                                <div class="related-price">GH ₵<?php echo number_format($related['price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function changeMainImage(thumbnail, imagePath) {
            document.getElementById('mainImage').src = imagePath;
            document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
            thumbnail.classList.add('active');
        }

        function changeQuantity(delta) {
            const input = document.getElementById('quantity');
            const newValue = parseInt(input.value) + delta;
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            if (newValue >= min && newValue <= max) {
                input.value = newValue;
            }
        }

        function addToCart(productId) {
            const quantity = parseInt(document.getElementById('quantity').value);
            
            <?php if(isset($_SESSION['user_id'])) { ?>
                Swal.fire({
                    title: 'Adding to Cart...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                fetch('add_to_cart_unified.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'product_id=' + productId + '&quantity=' + quantity
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Cart!',
                            text: 'Product has been added to your cart successfully.',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Failed to add product to cart.'
                        });
                    }
                })
                .catch(() => {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Network Error',
                        text: 'Please check your connection and try again.'
                    });
                });
            <?php } else { ?>
                window.location.href = 'login.php';
            <?php } ?>
        }

        function viewProduct(productId) {
            window.location.href = 'product.php?id=' + productId;
        }
    </script>
</body>
</html> 