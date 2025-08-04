<?php
// Modern Edit Product Page
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');
include('get_categories.php'); // Include categories helper

$message = '';
$messageType = '';

// Check for success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = 'Product updated successfully!';
    $messageType = 'success';
}

// Get product ID
if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}
$product_id = intval($_GET['id']);

// Handle form submission
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

    // Handle multiple image uploads if new images are provided
    $additional_images = [];
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
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
                            break;
                        }
                    }
                    
                    // Check if directory is writable
                    if (!is_writable($target_dir)) {
                        if (!chmod($target_dir, 0777)) {
                            $message = 'Upload directory is not writable! Please check permissions.';
                            $messageType = 'error';
                            break;
                        }
                    }
                    
                    $target = $target_dir . $img_name;
                    if (move_uploaded_file($tmp_name, $target)) {
                        $additional_images[] = 'images/products/' . $img_name;
                    } else {
                        $error = error_get_last();
                        $message = 'Image upload failed! Error: ' . ($error ? $error['message'] : 'Unknown error');
                        $messageType = 'error';
                        break;
                    }
                } else {
                    $message = 'Invalid image format. Please use JPG, PNG, GIF, or WebP.';
                    $messageType = 'error';
                    break;
                }
            }
        }
    }

    if (empty($message)) {
        // Handle removed images
        $removed_images = isset($_POST['removed_images']) ? $_POST['removed_images'] : '';
        
        // Delete removed images from product_images table
        if (!empty($removed_images)) {
            $removed_ids = explode(',', $removed_images);
            foreach ($removed_ids as $image_id) {
                if (is_numeric($image_id)) {
                    try {
                        // Get image path before deleting
                        $get_image_query = "SELECT image_path FROM product_images WHERE id = ? AND product_id = ?";
                        $stmt = $conn->prepare($get_image_query);
                        if ($stmt) {
                            $stmt->bind_param('ii', $image_id, $product_id);
                            $stmt->execute();
                            $image_result = $stmt->get_result();
                            
                            if ($image_result && $image_result->num_rows > 0) {
                                $image_data = $image_result->fetch_assoc();
                                // Delete file from server
                                $file_path_to_delete = '../' . $image_data['image_path'];
                                if (file_exists($file_path_to_delete)) {
                                    unlink($file_path_to_delete);
                                }
                            }
                            $stmt->close();
                        }
                        
                        // Delete from database
                        $delete_image_query = "DELETE FROM product_images WHERE id = ? AND product_id = ?";
                        $stmt = $conn->prepare($delete_image_query);
                        if ($stmt) {
                            $stmt->bind_param('ii', $image_id, $product_id);
                            $stmt->execute();
                            $stmt->close();
                        }
                    } catch (Exception $e) {
                        error_log("Error removing product image: " . $e->getMessage());
                        // Continue with other operations
                    }
                }
            }
        }
        
        // Update product information
        try {
            
            $query = "UPDATE products SET name=?, price=?, original_price=?, description=?, stock=?, category_id=?, subcategory=?, brand=?, discount_percentage=?, is_featured=?, is_flash_sale=?, flash_sale_end=? WHERE id=?";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Error preparing update query: " . $conn->error);
            }
            
            $stmt->bind_param('sddssisssiiss', $name, $price, $original_price, $description, $stock, $category_id, $subcategory, $brand, $discount_percentage, $is_featured, $is_flash_sale, $flash_sale_end, $product_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Error executing update: " . $stmt->error);
            }
            
            // Insert new images into product_images table
            if (!empty($additional_images)) {
                try {
                    
                    $get_max_sort_order = "SELECT MAX(sort_order) as max_order FROM product_images WHERE product_id = ?";
                    $stmt = $conn->prepare($get_max_sort_order);
                    if (!$stmt) {
                        throw new Exception("Error preparing sort order query: " . $conn->error);
                    }
                    $stmt->bind_param('i', $product_id);
                    $stmt->execute();
                    $sort_result = $stmt->get_result();
                    $sort_data = $sort_result->fetch_assoc();
                    $next_sort_order = ($sort_data['max_order'] ?? 0) + 1;
                    $stmt->close();
                    
                    foreach ($additional_images as $index => $image_path) {
                        
                        $image_name = basename($image_path);
                        $is_main = ($index === 0) ? 1 : 0; // First image is main
                        $insert_image_query = "INSERT INTO product_images (product_id, image_path, image_name, is_main, sort_order) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($insert_image_query);
                        if (!$stmt) {
                            throw new Exception("Error preparing image insert query: " . $conn->error);
                        }
                        $stmt->bind_param('issii', $product_id, $image_path, $image_name, $is_main, $next_sort_order);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Error inserting image: " . $stmt->error);
                        }
                        
                        $next_sort_order++;
                        $stmt->close();
                    }
                    
                } catch (Exception $e) {
                    error_log("Error inserting product images: " . $e->getMessage());
                    // Don't break the entire update, just log the error
                }
            }
            
            $message = 'Product updated successfully!';
            $messageType = 'success';
            
            // Redirect to prevent rendering issues
            header("Location: edit_product.php?id=$product_id&success=1");
            exit();
        } catch (Exception $e) {
            $message = 'Error updating product: ' . $e->getMessage();
            $messageType = 'error';
        }
        
        // Only close the statement if it exists and hasn't been closed
        if (isset($stmt) && $stmt) {
            $stmt->close();
        }
    }
}

// Fetch product info and all its images
try {
    $product_query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_query);
    if (!$stmt) {
        throw new Exception("Error preparing product query: " . $conn->error);
    }
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $product_result = $stmt->get_result();

    if ($product_result->num_rows === 0) {
        header('Location: products.php');
        exit();
    }

    $product = $product_result->fetch_assoc();
    $stmt->close();
} catch (Exception $e) {
    die("Error fetching product: " . $e->getMessage());
}

// Fetch all images for this product
$product_images = [];
try {
    $images_query = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC, created_at ASC";
    $stmt = $conn->prepare($images_query);
    if (!$stmt) {
        throw new Exception("Error preparing images query: " . $conn->error);
    }
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $images_result = $stmt->get_result();
    
    if ($images_result) {
        while ($image = $images_result->fetch_assoc()) {
            $product_images[] = $image;
        }
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching product images: " . $e->getMessage());
    $product_images = [];
}

// Set page variables for layout
$page_title = 'Edit Product';
$page_description = 'Edit product details in the Kings Reign inventory';
$show_back_button = true;
$back_url = 'products.php';
$header_actions = '<button type="submit" form="editProductForm" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>';

// Get categories for the dropdown
$categories = getCategories($conn, true);

// Custom CSS for this page
$custom_css = '
.edit-product-container {
    padding: 2rem;
    max-width: 800px;
    margin: 0 auto;
}

.form-section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 16px;
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
    padding: 15px 20px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    font-size: 16px;
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

.current-image {
    width: 200px;
    height: 200px;
    border: 2px solid var(--border-color);
    border-radius: 12px;
    object-fit: cover;
    margin-top: 1rem;
}

.image-preview {
    width: 200px;
    height: 200px;
    border: 2px dashed var(--border-color);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1rem;
    background: var(--bg-secondary);
    overflow: hidden;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
}

.image-preview.empty {
    color: var(--text-light);
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

.image-item .new-badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    background: var(--success-color);
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

.current-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.current-image-item {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    background: white;
    border: 2px solid var(--primary-color);
}

.current-image-item img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    display: block;
}

.current-image-item .image-info {
    padding: 0.5rem;
    background: white;
    border-top: 1px solid #e2e8f0;
}

.current-image-item .image-name {
    font-size: 0.8rem;
    color: var(--text-primary);
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.current-image-item .image-size {
    font-size: 0.7rem;
    color: var(--text-secondary);
}

.current-image-item .main-badge {
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

.current-image-item .remove-btn {
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

.current-image-item .remove-btn:hover {
    background: #dc2626;
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

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
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
    .edit-product-container {
        padding: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .price-inputs {
        grid-template-columns: 1fr;
    }
}
';

// Custom JavaScript for this page
$custom_js = '
// Image preview functionality for multiple images
function previewMultipleImages(input) {
    const preview = document.getElementById(\'imagesPreview\');
    
    if (!preview) {
        return;
    }
    
    if (input.files && input.files.length > 0) {
        preview.innerHTML = \'\';
        
        Array.from(input.files).forEach((file, index) => {
            if (file.type.startsWith(\'image/\')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const imageItem = document.createElement(\'div\');
                    imageItem.className = \'image-item\';
                    
                    const img = document.createElement(\'img\');
                    img.src = e.target.result;
                    img.alt = file.name;
                    
                    const imageInfo = document.createElement(\'div\');
                    imageInfo.className = \'image-info\';
                    
                    const imageName = document.createElement(\'div\');
                    imageName.className = \'image-name\';
                    imageName.textContent = file.name;
                    
                    const imageSize = document.createElement(\'div\');
                    imageSize.className = \'image-size\';
                    imageSize.textContent = formatFileSize(file.size);
                    
                    // New badge for additional images
                    const newBadge = document.createElement(\'div\');
                    newBadge.className = \'new-badge\';
                    newBadge.textContent = \'NEW\';
                    imageItem.appendChild(newBadge);
                    
                    // Remove button
                    const removeBtn = document.createElement(\'button\');
                    removeBtn.className = \'remove-btn\';
                    removeBtn.innerHTML = \'×\';
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
        preview.innerHTML = \'<div class="no-images-message">No new images selected</div>\';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return \'0 Bytes\';
    const k = 1024;
    const sizes = [\'Bytes\', \'KB\', \'MB\', \'GB\'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + \' \' + sizes[i];
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
        const preview = document.getElementById(\'imagesPreview\');
        preview.innerHTML = \'<div class="no-images-message">No new images selected</div>\';
    }
}

// Remove product image (works for both main and additional images)
function removeProductImage(imageId, isMain) {
    const confirmMessage = isMain ? 
        \'Are you sure you want to remove the main image? This action cannot be undone.\' :
        \'Are you sure you want to remove this image? This action cannot be undone.\';
    
    if (confirm(confirmMessage)) {
        const imageItem = document.querySelector(\'[data-image-id="\' + imageId + \'"]\');
        if (imageItem) {
            imageItem.style.display = \'none\';
            // Mark as removed for form submission
            const removedImages = document.getElementById(\'removedImages\').value;
            const newRemovedImages = removedImages ? removedImages + \',\' + imageId : imageId;
            document.getElementById(\'removedImages\').value = newRemovedImages;
        }
    }
}

// Load and display additional images (if any exist)
function loadAdditionalImages() {
    console.log(\'Loading additional images...\');
    // This function will be expanded later to load additional images from database
}

// Initialize the page
document.addEventListener(\'DOMContentLoaded\', function() {
    loadAdditionalImages();
});

// Flash sale end date toggle
document.getElementById("is_flash_sale").addEventListener("change", function() {
    const endDateGroup = document.getElementById("flashSaleEndGroup");
    if (this.checked) {
        endDateGroup.style.display = "block";
    } else {
        endDateGroup.style.display = "none";
    }
});

// Auto-calculate discount percentage
document.getElementById("original_price").addEventListener("input", function() {
    const originalPrice = parseFloat(this.value) || 0;
    const currentPrice = parseFloat(document.getElementById("price").value) || 0;
    
    if (originalPrice > 0 && currentPrice > 0 && originalPrice > currentPrice) {
        const discount = Math.round(((originalPrice - currentPrice) / originalPrice) * 100);
        document.getElementById("discount_percentage").value = discount;
    }
});

// Form validation
document.getElementById("editProductForm").addEventListener("submit", function(e) {
    const price = parseFloat(document.getElementById("price").value);
    const originalPrice = parseFloat(document.getElementById("original_price").value);
    const discount = parseInt(document.getElementById("discount_percentage").value);
    const stock = parseInt(document.getElementById("stock").value);
    
    if (price <= 0) {
        e.preventDefault();
        showNotification("Current price must be greater than 0", "error");
        return;
    }
    
    if (originalPrice > 0 && originalPrice < price) {
        e.preventDefault();
        showNotification("Original price cannot be less than current price", "error");
        return;
    }
    
    if (discount < 0 || discount > 100) {
        e.preventDefault();
        showNotification("Discount percentage must be between 0 and 100", "error");
        return;
    }
    
    if (stock < 0) {
        e.preventDefault();
        showNotification("Stock quantity must be 0 or greater", "error");
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector("button[type=submit]");
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = "<i class=\"fas fa-spinner fa-spin\"></i> Saving...";
    submitBtn.disabled = true;
});

// Show notification function
function showNotification(message, type = "info") {
    const notification = document.createElement("div");
    notification.className = `alert alert-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-triangle" : "info"}"></i>
        ${message}
    `;
    
    notification.style.position = "fixed";
    notification.style.top = "20px";
    notification.style.left = "50%";
    notification.style.transform = "translateX(-50%)";
    notification.style.zIndex = "9999";
    notification.style.minWidth = "300px";
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Kings Reign Admin</title>
    <link rel="stylesheet" href="../styles/modern_admin.css">
    <link rel="shortcut icon" href="../images/logos/logo-black.jpg" type="image/x-icon">
    <style><?php echo $custom_css; ?></style>
</head>
<body>
    <div class="admin-layout">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Include Header -->
            <?php include 'includes/header.php'; ?>
            
            <!-- Page Content -->
            <div class="admin-content">
                <div class="edit-product-container">
                    <!-- Success/Error Messages -->
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data" id="editProductForm">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="name">Product Name *</label>
                                    <input type="text" id="name" name="name" class="form-input" 
                                           placeholder="Enter product name" required 
                                           value="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="category">Category *</label>
                                    <select id="category" name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['id']); ?>" 
                                                    <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="subcategory">Subcategory</label>
                                    <input type="text" id="subcategory" name="subcategory" class="form-input" 
                                           placeholder="Enter subcategory (optional)"
                                           value="<?php echo htmlspecialchars($product['subcategory'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="brand">Brand</label>
                                    <input type="text" id="brand" name="brand" class="form-input" 
                                           placeholder="Enter brand name (optional)"
                                           value="<?php echo htmlspecialchars($product['brand'] ?? ''); ?>">
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
                                           value="<?php echo htmlspecialchars($product['price']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="original_price">Original Price</label>
                                    <input type="number" id="original_price" name="original_price" class="form-input" 
                                           placeholder="0.00" step="0.01" min="0"
                                           value="<?php echo htmlspecialchars($product['original_price'] ?? $product['price']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="discount_percentage">Discount Percentage</label>
                                    <div class="discount-input">
                                        <input type="number" id="discount_percentage" name="discount_percentage" 
                                               class="form-input" placeholder="0" min="0" max="100"
                                               value="<?php echo htmlspecialchars($product['discount_percentage'] ?? '0'); ?>">
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
                                          placeholder="Enter product description..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="stock">Stock Quantity *</label>
                                <input type="number" id="stock" name="stock" class="form-input" 
                                       placeholder="0" min="0" required
                                       value="<?php echo htmlspecialchars($product['stock']); ?>">
                            </div>
                        </div>

                        <!-- Product Images -->
                        <div class="form-section">
                            <h3><i class="fas fa-images"></i> Product Images</h3>
                            
                            <!-- Current Images Display -->
                            <div class="form-group">
                                <label class="form-label">Current Images</label>
                                <div class="current-images-grid" id="currentImagesGrid">
                                    <?php if (empty($product_images)): ?>
                                        <div class="no-images-message">No images found for this product</div>
                                    <?php else: ?>
                                        <?php foreach ($product_images as $image): ?>
                                            <div class="image-item current-image-item" data-image-id="<?php echo $image['id']; ?>">
                                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                                     alt="<?php echo htmlspecialchars($image['image_name']); ?>" 
                                                     class="current-image">
                                                <div class="image-info">
                                                    <div class="image-name"><?php echo htmlspecialchars($image['image_name']); ?></div>
                                                    <div class="image-size"><?php echo $image['is_main'] ? 'Main Image' : 'Additional'; ?></div>
                                                </div>
                                                <?php if ($image['is_main']): ?>
                                                    <div class="main-badge">MAIN</div>
                                                <?php endif; ?>
                                                <button type="button" class="remove-btn" 
                                                        onclick="removeProductImage(<?php echo $image['id']; ?>, <?php echo $image['is_main'] ? 'true' : 'false'; ?>)" 
                                                        title="Remove image">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="removed_images" id="removedImages" value="">
                            </div>
                            
                            <!-- Add More Images -->
                            <div class="form-group">
                                <label class="form-label" for="images">Add More Images</label>
                                <input type="file" id="images" name="images[]" class="form-input" 
                                       accept="image/*" multiple onchange="previewMultipleImages(this)">
                                <small style="color: var(--text-secondary); font-size: 0.8rem;">
                                    You can select multiple images to add to this product.
                                </small>
                                <div class="images-preview" id="imagesPreview">
                                    <div class="no-images-message">No new images selected</div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Options -->
                        <div class="form-section">
                            <h3><i class="fas fa-cog"></i> Product Options</h3>
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                       <?php echo ($product['is_featured'] ? 'checked' : ''); ?>>
                                <label for="is_featured">Featured Product</label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_flash_sale" name="is_flash_sale" value="1" 
                                       <?php echo ($product['is_flash_sale'] ? 'checked' : ''); ?>>
                                <label for="is_flash_sale">Flash Sale Product</label>
                            </div>
                            
                            <div class="form-group" id="flashSaleEndGroup" style="display: <?php echo $product['is_flash_sale'] ? 'block' : 'none'; ?>;">
                                <label class="form-label" for="flash_sale_end">Flash Sale End Date</label>
                                <input type="datetime-local" id="flash_sale_end" name="flash_sale_end" class="form-input"
                                       value="<?php echo $product['flash_sale_end'] ? date('Y-m-d\TH:i', strtotime($product['flash_sale_end'])) : ''; ?>">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script><?php echo $custom_js; ?></script>
</body>
</html>
