<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');

$message = '';
$messageType = '';

// Get category ID
if (!isset($_GET['id'])) {
    header('Location: categories.php');
    exit();
}
$category_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $slug = $conn->real_escape_string($_POST['slug']);
    $sort_order = intval($_POST['sort_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image_path = $_POST['current_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed_extensions)) {
            $img_name = uniqid('cat_', true) . '.' . $ext;
            $target_dir = '../images/categories/';
            
            // Create directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target = $target_dir . $img_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // Delete old image if exists
                if (!empty($_POST['current_image']) && file_exists('../' . $_POST['current_image'])) {
                    unlink('../' . $_POST['current_image']);
                }
                $image_path = 'images/categories/' . $img_name;
            } else {
                $message = 'Image upload failed!';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid image format. Please use JPG, PNG, GIF, or WebP.';
            $messageType = 'error';
        }
    }
    
    if (empty($message)) {
        // Check if name already exists (excluding current category)
        $check_query = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param('si', $name, $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = 'A category with this name already exists!';
            $messageType = 'error';
        } else {
            // Check if slug already exists (excluding current category)
            $check_slug_query = "SELECT id FROM categories WHERE slug = ? AND id != ?";
            $stmt = $conn->prepare($check_slug_query);
            $stmt->bind_param('si', $slug, $category_id);
            $stmt->execute();
            $slug_result = $stmt->get_result();
            
            if ($slug_result->num_rows > 0) {
                $message = 'A category with this slug already exists!';
                $messageType = 'error';
            } else {
                // Update category
                $update_query = "UPDATE categories SET name = ?, description = ?, slug = ?, image_path = ?, sort_order = ?, is_active = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param('ssssiii', $name, $description, $slug, $image_path, $sort_order, $is_active, $category_id);
                
                if ($stmt->execute()) {
                    $message = 'Category updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error updating category: ' . $stmt->error;
                    $messageType = 'error';
                }
            }
        }
        $stmt->close();
    }
}

// Fetch category data
$category_query = "SELECT * FROM categories WHERE id = ?";
$stmt = $conn->prepare($category_query);
$stmt->bind_param('i', $category_id);
$stmt->execute();
$category_result = $stmt->get_result();

if ($category_result->num_rows === 0) {
    header('Location: categories.php');
    exit();
}

$category = $category_result->fetch_assoc();
$stmt->close();

$page_title = 'Edit Category';
$page_description = 'Edit category details';
$show_back_button = true;
$back_url = 'categories.php';
$header_actions = '<button type="submit" form="editCategoryForm" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>';

// Custom CSS for this page
$custom_css = '
.edit-category-container {
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

.form-text {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
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
    .edit-category-container {
        padding: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
';

// Custom JavaScript for this page
$custom_js = '
// Image preview functionality
function previewImage(input) {
    const preview = document.getElementById("imagePreview");
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = \'<img src="\' + e.target.result + \'" alt="Category Image">\';
            preview.classList.remove("empty");
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = \'<div class="empty">No new image selected</div>\';
        preview.classList.add("empty");
    }
}

// Form validation
document.getElementById("editCategoryForm").addEventListener("submit", function(e) {
    const name = document.getElementById("name").value.trim();
    const slug = document.getElementById("slug").value.trim();
    const sortOrder = parseInt(document.getElementById("sort_order").value);
    
    if (name.length < 2) {
        e.preventDefault();
        showNotification("Category name must be at least 2 characters long", "error");
        return;
    }
    
    if (slug.length < 2) {
        e.preventDefault();
        showNotification("Slug must be at least 2 characters long", "error");
        return;
    }
    
    if (sortOrder < 0) {
        e.preventDefault();
        showNotification("Sort order must be 0 or greater", "error");
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
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <div class="edit-category-container">
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data" id="editCategoryForm">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="name">Category Name *</label>
                                    <input type="text" id="name" name="name" class="form-input" 
                                           placeholder="Enter category name" required 
                                           value="<?php echo htmlspecialchars($category['name']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="slug">Slug *</label>
                                    <input type="text" id="slug" name="slug" class="form-input" 
                                           placeholder="category-slug" required
                                           value="<?php echo htmlspecialchars($category['slug']); ?>">
                                    <div class="form-text">URL-friendly version of the name</div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="description">Description</label>
                                <textarea id="description" name="description" class="form-input form-textarea" 
                                          placeholder="Enter category description..."><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <!-- Category Image -->
                        <div class="form-section">
                            <h3><i class="fas fa-image"></i> Category Image</h3>
                            <div class="form-group">
                                <label class="form-label" for="image">Category Image</label>
                                <?php if ($category['image_path']): ?>
                                    <div style="margin-bottom: 1rem;">
                                        <img src="../<?php echo htmlspecialchars($category['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                             class="current-image">
                                    </div>
                                <?php endif; ?>
                                <input type="file" id="image" name="image" class="form-input" 
                                       accept="image/*" onchange="previewImage(this)">
                                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($category['image_path'] ?? ''); ?>">
                                <div class="form-text">Leave empty to keep current image</div>
                                <div class="image-preview empty" id="imagePreview">
                                    <div class="empty">No new image selected</div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Settings -->
                        <div class="form-section">
                            <h3><i class="fas fa-cog"></i> Category Settings</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="sort_order">Sort Order</label>
                                    <input type="number" id="sort_order" name="sort_order" class="form-input" 
                                           placeholder="0" min="0" 
                                           value="<?php echo htmlspecialchars($category['sort_order']); ?>">
                                    <div class="form-text">Lower numbers appear first in lists</div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="is_active" name="is_active" 
                                               <?php echo $category['is_active'] ? 'checked' : ''; ?>>
                                        <label for="is_active">Active Category</label>
                                    </div>
                                    <div class="form-text">Inactive categories won\'t appear in product forms</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
    
    <script><?php echo $custom_js; ?></script>
    <script>
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        });
    </script>
</body>
</html> 