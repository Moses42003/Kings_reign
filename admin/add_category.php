<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $slug = $conn->real_escape_string($_POST['slug']);
    $sort_order = intval($_POST['sort_order']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image_path = '';
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
        // Check if name already exists
        $check_query = "SELECT id FROM categories WHERE name = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = 'A category with this name already exists!';
            $messageType = 'error';
        } else {
            // Check if slug already exists
            $check_slug_query = "SELECT id FROM categories WHERE slug = ?";
            $stmt = $conn->prepare($check_slug_query);
            $stmt->bind_param('s', $slug);
            $stmt->execute();
            $slug_result = $stmt->get_result();
            
            if ($slug_result->num_rows > 0) {
                $message = 'A category with this slug already exists!';
                $messageType = 'error';
            } else {
                // Insert category
                $insert_query = "INSERT INTO categories (name, description, slug, image_path, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param('ssssii', $name, $description, $slug, $image_path, $sort_order, $is_active);
                
                if ($stmt->execute()) {
                    $message = 'Category added successfully!';
                    $messageType = 'success';
                    // Redirect to categories list
                    header('Location: categories.php?success=1');
                    exit();
                } else {
                    $message = 'Error adding category: ' . $stmt->error;
                    $messageType = 'error';
                }
            }
        }
        $stmt->close();
    }
}

$page_title = 'Add Category';
$page_description = 'Add a new product category';
$show_back_button = true;
$back_url = 'categories.php';
$header_actions = '<button type="submit" form="addCategoryForm" class="btn btn-primary"><i class="fas fa-save"></i> Save Category</button>';

// Custom CSS for this page
$custom_css = '
.add-category-container {
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
    .add-category-container {
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
        preview.innerHTML = \'<div class="empty">No image selected</div>\';
        preview.classList.add("empty");
    }
}

// Form validation
document.getElementById("addCategoryForm").addEventListener("submit", function(e) {
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
                <div class="add-category-container">
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data" id="addCategoryForm">
                        <!-- Basic Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label" for="name">Category Name *</label>
                                    <input type="text" id="name" name="name" class="form-input" 
                                           placeholder="Enter category name" required 
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label" for="slug">Slug *</label>
                                    <input type="text" id="slug" name="slug" class="form-input" 
                                           placeholder="category-slug" required
                                           value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ''; ?>">
                                    <div class="form-text">URL-friendly version of the name (e.g., "electronics" for "Electronics")</div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="description">Description</label>
                                <textarea id="description" name="description" class="form-input form-textarea" 
                                          placeholder="Enter category description..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                        </div>

                        <!-- Category Image -->
                        <div class="form-section">
                            <h3><i class="fas fa-image"></i> Category Image</h3>
                            <div class="form-group">
                                <label class="form-label" for="image">Category Image</label>
                                <input type="file" id="image" name="image" class="form-input" 
                                       accept="image/*" onchange="previewImage(this)">
                                <div class="form-text">Upload an image to represent this category (optional)</div>
                                <div class="image-preview empty" id="imagePreview">
                                    <div class="empty">No image selected</div>
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
                                           value="<?php echo isset($_POST['sort_order']) ? htmlspecialchars($_POST['sort_order']) : '0'; ?>">
                                    <div class="form-text">Lower numbers appear first in lists</div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="checkbox-group">
                                        <input type="checkbox" id="is_active" name="is_active" 
                                               <?php echo (!isset($_POST['is_active']) || $_POST['is_active']) ? 'checked' : ''; ?>>
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