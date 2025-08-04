<?php
// Modern Add Product Page
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');
include('get_categories.php'); // Include categories helper

$message = '';
$messageType = '';

// Get categories for the dropdown
$categories = getCategories($conn, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $price = floatval($_POST['price']);
    $original_price = isset($_POST['original_price']) ? floatval($_POST['original_price']) : $price;
    $description = $conn->real_escape_string($_POST['description']);
    $stock = intval($_POST['stock']);
    $category_id = intval($_POST['category_id']);
    $subcategory = isset($_POST['subcategory']) ? $conn->real_escape_string($_POST['subcategory']) : '';
    $brand = isset($_POST['brand']) ? $conn->real_escape_string($_POST['brand']) : '';
    $discount_percentage = isset($_POST['discount_percentage']) ? intval($_POST['discount_percentage']) : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_flash_sale = isset($_POST['is_flash_sale']) ? 1 : 0;
    $flash_sale_end = isset($_POST['flash_sale_end']) && !empty($_POST['flash_sale_end']) ? $_POST['flash_sale_end'] : null;

    // Handle multiple image uploads
    $file_paths = [];
    $upload_success = true;
    
    if (isset($_FILES['images']) && is_array($_FILES['images']['name']) && !empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $file_name = $_FILES['images']['name'][$key];
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($ext, $allowed_extensions)) {
                    $img_name = uniqid('prod_', true) . '_' . $key . '.' . $ext;
                    $target_dir = '../images/products/';
                    
                    // Create directory if it doesn't exist
                    if (!is_dir($target_dir)) {
                        if (!mkdir($target_dir, 0777, true)) {
                            $message = 'Failed to create upload directory!';
                            $messageType = 'error';
                            $upload_success = false;
                            break;
                        }
                    }
                    
                    // Check if directory is writable
                    if (!is_writable($target_dir)) {
                        // Try to make it writable
                        if (!chmod($target_dir, 0777)) {
                            $message = 'Upload directory is not writable! Please check permissions.';
                            $messageType = 'error';
                            $upload_success = false;
                            break;
                        }
                    }
                    
                    $target = $target_dir . $img_name;
                    if (move_uploaded_file($tmp_name, $target)) {
                        $file_paths[] = 'images/products/' . $img_name;
                    } else {
                        $error = error_get_last();
                        $message = 'Image upload failed! Error: ' . ($error ? $error['message'] : 'Unknown error');
                        $messageType = 'error';
                        $upload_success = false;
                        break;
                    }
                } else {
                    $message = 'Invalid image format. Please use JPG, PNG, GIF, or WebP.';
                    $messageType = 'error';
                    $upload_success = false;
                    break;
                }
            } else {
                switch ($_FILES['images']['error'][$key]) {
                    case UPLOAD_ERR_INI_SIZE:
                        $message = 'Image file is too large (exceeds PHP upload limit).';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        $message = 'Image file is too large (exceeds form limit).';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $message = 'Image upload was incomplete.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        // This is fine - no images selected
                        break;
                    default:
                        $message = 'Image upload error occurred.';
                }
                if ($message) {
                    $messageType = 'error';
                    $upload_success = false;
                    break;
                }
            }
        }
    }
    // If no images uploaded, that's fine - continue without images

    if (empty($message)) {
        // Insert product into database
        $query = "INSERT INTO products (name, price, original_price, description, stock, category_id, subcategory, brand, discount_percentage, is_featured, is_flash_sale, flash_sale_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sddssisssiiss', $name, $price, $original_price, $description, $stock, $category_id, $subcategory, $brand, $discount_percentage, $is_featured, $is_flash_sale, $flash_sale_end);
        
        if ($stmt->execute()) {
            $product_id = $conn->insert_id;
            
            // Insert images into product_images table
            if (!empty($file_paths)) {
                try {
                    foreach ($file_paths as $index => $image_path) {
                        $image_name = basename($image_path);
                        $is_main = ($index === 0) ? 1 : 0; // First image is main
                        $sort_order = $index;
                        
                        $insert_image_query = "INSERT INTO product_images (product_id, image_path, image_name, is_main, sort_order) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($insert_image_query);
                        if ($stmt) {
                            $stmt->bind_param('issii', $product_id, $image_path, $image_name, $is_main, $sort_order);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error inserting product images: " . $e->getMessage());
                    // Don't break the entire insert, just log the error
                }
            }
            
            $message = 'Product added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding product: ' . $stmt->error;
            $messageType = 'error';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Kings Reign Admin</title>
    <link rel="stylesheet" href="../styles/modern_admin.css">
    <link rel="shortcut icon" href="../images/logos/logo-black.jpg" type="image/x-icon">
    <style>
        .add-product-container {
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-section {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        .form-section h3 {
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--bg-secondary);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            background: var(--bg-secondary);
            cursor: pointer;
        }

        .form-select:focus {
            outline: none;
            border-color: var(--primary-color);
            background: white;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary-color);
        }

        .checkbox-group label {
            font-weight: 500;
            color: var(--text-primary);
            cursor: pointer;
        }

        .image-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1rem;
            background: #f8fafc;
            overflow: hidden;
            position: relative;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
            border-radius: 12px;
            display: block;
        }

        .image-preview.empty {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .images-preview {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            max-height: 400px;
            overflow-y: auto;
            padding: 1rem;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
        }

        .image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background: white;
        }

        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }

        .image-item .image-info {
            padding: 0.5rem;
            background: white;
            border-top: 1px solid #e2e8f0;
        }

        .image-item .image-name {
            font-size: 0.8rem;
            color: var(--text-primary);
            font-weight: 500;
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .image-item .image-size {
            font-size: 0.7rem;
            color: var(--text-secondary);
        }

        .image-item .main-badge {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background: var(--primary-color);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .image-item .remove-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: var(--danger-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .no-images-message {
            grid-column: 1 / -1;
            text-align: center;
            color: #94a3b8;
            font-size: 0.9rem;
            padding: 2rem;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
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

        .price-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .discount-input {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .discount-input input {
            width: 80px;
        }

        .discount-input span {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .add-product-container {
                padding: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .price-inputs {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php
// Set page variables for layout
$page_title = 'Add New Product';
$page_description = 'Add new products to the Kings Reign inventory';
$show_back_button = true;
$back_url = 'dashboard_modern.php';
$header_actions = '<a href="dashboard_modern.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>';
?>

    <div class="admin-layout">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Include Header -->
            <?php include 'includes/header.php'; ?>

            <div class="admin-content">
                <div class="add-product-container">
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" id="addProductForm">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="name">Product Name *</label>
                                    <input type="text" id="name" name="name" class="form-input" 
                                           placeholder="Enter product name" required 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="category_id">Category *</label>
                                    <select id="category_id" name="category_id" class="form-select" required>
                                        <?php echo generateCategoryOptions($categories, isset($_POST['category_id']) ? $_POST['category_id'] : ''); ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="subcategory">Subcategory</label>
                                    <input type="text" id="subcategory" name="subcategory" class="form-input" 
                                           placeholder="Enter subcategory (optional)"
                                           value="<?php echo isset($_POST['subcategory']) ? htmlspecialchars($_POST['subcategory']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="brand">Brand</label>
                                    <input type="text" id="brand" name="brand" class="form-input" 
                                           placeholder="Enter brand name (optional)"
                                           value="<?php echo isset($_POST['brand']) ? htmlspecialchars($_POST['brand']) : ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-tag"></i> Pricing Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="price">Current Price *</label>
                                    <input type="number" id="price" name="price" class="form-input" 
                                           placeholder="0.00" step="0.01" min="0" required
                                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="original_price">Original Price</label>
                                    <input type="number" id="original_price" name="original_price" class="form-input" 
                                           placeholder="0.00" step="0.01" min="0"
                                           value="<?php echo isset($_POST['original_price']) ? htmlspecialchars($_POST['original_price']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="discount_percentage">Discount Percentage</label>
                                    <div class="discount-input">
                                        <input type="number" id="discount_percentage" name="discount_percentage" 
                                               class="form-input" placeholder="0" min="0" max="100"
                                               value="<?php echo isset($_POST['discount_percentage']) ? htmlspecialchars($_POST['discount_percentage']) : '0'; ?>">
                                        <span>%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="form-section">
                            <h3><i class="fas fa-box"></i> Product Details</h3>
                            <div class="form-group">
                                <label class="form-label" for="description">Description</label>
                                <textarea id="description" name="description" class="form-input form-textarea" 
                                          placeholder="Enter product description..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="stock">Stock Quantity *</label>
                                <input type="number" id="stock" name="stock" class="form-input" 
                                       placeholder="0" min="0" required
                                       value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : ''; ?>">
                            </div>
                        </div>

                        <!-- Product Image -->
                        <div class="form-section">
                            <h3><i class="fas fa-images"></i> Product Images</h3>
                            <div class="form-group">
                                <label class="form-label" for="images">Product Images</label>
                                <input type="file" id="images" name="images[]" class="form-input" 
                                       accept="image/*" multiple onchange="previewMultipleImages(this)">
                                <small style="color: var(--text-secondary); font-size: 0.8rem;">
                                    You can select multiple images. First image will be the main product image. (Optional)
                                </small>
                                <div class="images-preview" id="imagesPreview">
                                    <div class="no-images-message">No images selected</div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Options -->
                        <div class="form-section">
                            <h3><i class="fas fa-cog"></i> Product Options</h3>
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                       <?php echo (isset($_POST['is_featured']) && $_POST['is_featured']) ? 'checked' : ''; ?>>
                                <label for="is_featured">Featured Product</label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_flash_sale" name="is_flash_sale" value="1" 
                                       <?php echo (isset($_POST['is_flash_sale']) && $_POST['is_flash_sale']) ? 'checked' : ''; ?>>
                                <label for="is_flash_sale">Flash Sale Product</label>
                            </div>
                            
                            <div class="form-group" id="flashSaleEndGroup" style="display: none;">
                                <label class="form-label" for="flash_sale_end">Flash Sale End Date</label>
                                <input type="datetime-local" id="flash_sale_end" name="flash_sale_end" class="form-input">
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-plus"></i> Add Product
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Make functions globally available
        window.previewMultipleImages = function(input) {
            const preview = document.getElementById('imagesPreview');
            
            if (!preview) {
                return;
            }
            
            if (input.files && input.files.length > 0) {
                preview.innerHTML = '';
                
                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const imageItem = document.createElement('div');
                            imageItem.className = 'image-item';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = file.name;
                            
                            const imageInfo = document.createElement('div');
                            imageInfo.className = 'image-info';
                            
                            const imageName = document.createElement('div');
                            imageName.className = 'image-name';
                            imageName.textContent = file.name;
                            
                            const imageSize = document.createElement('div');
                            imageSize.className = 'image-size';
                            imageSize.textContent = formatFileSize(file.size);
                            
                            // Main badge for first image
                            if (index === 0) {
                                const mainBadge = document.createElement('div');
                                mainBadge.className = 'main-badge';
                                mainBadge.textContent = 'MAIN';
                                imageItem.appendChild(mainBadge);
                            }
                            
                            // Remove button
                            const removeBtn = document.createElement('button');
                            removeBtn.className = 'remove-btn';
                            removeBtn.innerHTML = '×';
                            removeBtn.onclick = function() {
                                removeImageFromPreview(imageItem, file, input);
                            };
                            
                            imageInfo.appendChild(imageName);
                            imageInfo.appendChild(imageSize);
                            imageItem.appendChild(img);
                            imageItem.appendChild(imageInfo);
                            imageItem.appendChild(removeBtn);
                            preview.appendChild(imageItem);
                        }
                        
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                preview.innerHTML = '<div class="no-images-message">No images selected</div>';
            }
        };
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        function removeImageFromPreview(imageItem, file, input) {
            // Create a new FileList without the removed file
            const dt = new DataTransfer();
            Array.from(input.files).forEach(f => {
                if (f !== file) {
                    dt.items.add(f);
                }
            });
            input.files = dt.files;
            
            // Remove the image item from preview
            imageItem.remove();
            
            // If no images left, show message
            if (input.files.length === 0) {
                const preview = document.getElementById('imagesPreview');
                preview.innerHTML = '<div class="no-images-message">No images selected</div>';
            }
        }

        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            
            // Flash sale end date toggle
            const flashSaleCheckbox = document.getElementById('is_flash_sale');
            if (flashSaleCheckbox) {
                flashSaleCheckbox.addEventListener('change', function() {
                    const endDateGroup = document.getElementById('flashSaleEndGroup');
                    if (this.checked) {
                        endDateGroup.style.display = 'block';
                    } else {
                        endDateGroup.style.display = 'none';
                    }
                });
            }

            // Auto-calculate discount percentage
            document.getElementById('original_price').addEventListener('input', function() {
                const originalPrice = parseFloat(this.value) || 0;
                const currentPrice = parseFloat(document.getElementById('price').value) || 0;
                
                if (originalPrice > 0 && currentPrice > 0 && originalPrice > currentPrice) {
                    const discount = Math.round(((originalPrice - currentPrice) / originalPrice) * 100);
                    document.getElementById('discount_percentage').value = discount;
                }
            });

            // Form validation
            document.getElementById('addProductForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form elements
                const name = document.getElementById('name').value.trim();
                const category = document.getElementById('category_id').value;
                const price = parseFloat(document.getElementById('price').value);
                const originalPrice = parseFloat(document.getElementById('original_price').value) || 0;
                const discount = parseInt(document.getElementById('discount_percentage').value) || 0;
                const stock = parseInt(document.getElementById('stock').value);
                const description = document.getElementById('description').value.trim();
                const images = document.getElementById('images').files;
                const isFlashSale = document.getElementById('is_flash_sale').checked;
                const flashSaleEnd = document.getElementById('flash_sale_end').value;
                
                let isValid = true;
                let errorMessage = '';
                
                // Validate product name
                if (!name) {
                    errorMessage += '• Product name is required\n';
                    isValid = false;
                } else if (name.length < 3) {
                    errorMessage += '• Product name must be at least 3 characters long\n';
                    isValid = false;
                } else if (name.length > 255) {
                    errorMessage += '• Product name must be less than 255 characters\n';
                    isValid = false;
                }
                
                // Validate category
                if (!category) {
                    errorMessage += '• Please select a category\n';
                    isValid = false;
                }
                
                // Validate price
                if (!price || price <= 0) {
                    errorMessage += '• Current price must be greater than 0\n';
                    isValid = false;
                } else if (price > 999999.99) {
                    errorMessage += '• Current price cannot exceed 999,999.99\n';
                    isValid = false;
                }
                
                // Validate original price
                if (originalPrice > 0) {
                    if (originalPrice < price) {
                        errorMessage += '• Original price cannot be less than current price\n';
                        isValid = false;
                    } else if (originalPrice > 999999.99) {
                        errorMessage += '• Original price cannot exceed 999,999.99\n';
                        isValid = false;
                    }
                }
                
                // Validate discount percentage
                if (discount < 0 || discount > 100) {
                    errorMessage += '• Discount percentage must be between 0 and 100\n';
                    isValid = false;
                }
                
                // Validate stock
                if (!stock || stock < 0) {
                    errorMessage += '• Stock quantity must be 0 or greater\n';
                    isValid = false;
                } else if (stock > 999999) {
                    errorMessage += '• Stock quantity cannot exceed 999,999\n';
                    isValid = false;
                }
                
                // Validate description
                if (description && description.length > 1000) {
                    errorMessage += '• Description must be less than 1000 characters\n';
                    isValid = false;
                }
                
                // Validate image
                if (!images || images.length === 0) {
                    // Images are optional, so this is valid
                } else {
                    // Validate image size (max 5MB)
                    const maxSize = 5 * 1024 * 1024; // 5MB
                    for (let i = 0; i < images.length; i++) {
                        if (images[i].size > maxSize) {
                            errorMessage += `• Image ${i+1} size must be less than 5MB\n`;
                            isValid = false;
                            break;
                        }
                    }
                    
                    // Validate image type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    for (let i = 0; i < images.length; i++) {
                        if (!allowedTypes.includes(images[i].type)) {
                            errorMessage += `• Image ${i+1} must be a valid image file (JPG, PNG, GIF, WebP)\n`;
                            isValid = false;
                            break;
                        }
                    }
                }
                
                // Validate flash sale end date
                if (isFlashSale && !flashSaleEnd) {
                    errorMessage += '• Please set flash sale end date\n';
                    isValid = false;
                } else if (isFlashSale && flashSaleEnd) {
                    const endDate = new Date(flashSaleEnd);
                    const now = new Date();
                    if (endDate <= now) {
                        errorMessage += '• Flash sale end date must be in the future\n';
                        isValid = false;
                    }
                }
                
                // Show error message if validation fails
                if (!isValid) {
                    showNotification(errorMessage, 'error');
                    return false;
                }
                
                // Show loading state
                const submitBtn = document.querySelector('.submit-btn');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding Product...';
                submitBtn.disabled = true;
                
                // Submit form
                this.submit();
            });
            
            // Real-time validation
            function validateField(field, validationRules) {
                const value = field.value.trim();
                const errorElement = field.parentNode.querySelector('.field-error');
                
                // Remove existing error
                if (errorElement) {
                    errorElement.remove();
                }
                
                // Apply validation rules
                for (const rule of validationRules) {
                    if (!rule.test(value)) {
                        showFieldError(field, rule.message);
                        return false;
                    }
                }
                
                return true;
            }
            
            function showFieldError(field, message) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.style.color = '#ef4444';
                errorDiv.style.fontSize = '0.8rem';
                errorDiv.style.marginTop = '0.25rem';
                errorDiv.textContent = message;
                field.parentNode.appendChild(errorDiv);
            }
            
            // Add real-time validation to key fields
            document.getElementById('name').addEventListener('blur', function() {
                validateField(this, [
                    { test: (value) => value.length >= 3, message: 'Name must be at least 3 characters' },
                    { test: (value) => value.length <= 255, message: 'Name must be less than 255 characters' }
                ]);
            });
            
            document.getElementById('price').addEventListener('blur', function() {
                const price = parseFloat(this.value);
                validateField(this, [
                    { test: (value) => !isNaN(price) && price > 0, message: 'Price must be greater than 0' },
                    { test: (value) => price <= 999999.99, message: 'Price cannot exceed 999,999.99' }
                ]);
            });
            
            document.getElementById('stock').addEventListener('blur', function() {
                const stock = parseInt(this.value);
                validateField(this, [
                    { test: (value) => !isNaN(stock) && stock >= 0, message: 'Stock must be 0 or greater' },
                    { test: (value) => stock <= 999999, message: 'Stock cannot exceed 999,999' }
                ]);
            });
            
            // Show notification function
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `alert alert-${type}`;
                notification.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i>
                    <div style="white-space: pre-line;">${message}</div>
                `;
                
                notification.style.position = 'fixed';
                notification.style.top = '20px';
                notification.style.left = '50%';
                notification.style.transform = 'translateX(-50%)';
                notification.style.zIndex = '9999';
                notification.style.minWidth = '400px';
                notification.style.maxWidth = '600px';
                
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.remove();
                }, 5000);
            }
        });
    </script>
</body>
</html>