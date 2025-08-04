<?php
// Products Listing Page
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selected_products = isset($_POST['selected_products']) ? $_POST['selected_products'] : [];
    
    if (!empty($selected_products)) {
        $product_ids = implode(',', array_map('intval', $selected_products));
        
        switch ($action) {
            case 'delete':
                $delete_query = "DELETE FROM products WHERE id IN ($product_ids)";
                if ($conn->query($delete_query)) {
                    $success_message = count($selected_products) . " product(s) deleted successfully!";
                } else {
                    $error_message = "Error deleting products: " . $conn->error;
                }
                break;
                
            case 'feature':
                $feature_query = "UPDATE products SET is_featured = 1 WHERE id IN ($product_ids)";
                if ($conn->query($feature_query)) {
                    $success_message = count($selected_products) . " product(s) marked as featured!";
                } else {
                    $error_message = "Error updating products: " . $conn->error;
                }
                break;
                
            case 'unfeature':
                $unfeature_query = "UPDATE products SET is_featured = 0 WHERE id IN ($product_ids)";
                if ($conn->query($unfeature_query)) {
                    $success_message = count($selected_products) . " product(s) unmarked as featured!";
                } else {
                    $error_message = "Error updating products: " . $conn->error;
                }
                break;
        }
    }
}

// Handle single product actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $product_id = intval($_GET['id']);
    
    switch ($action) {
        case 'delete':
            $delete_query = "DELETE FROM products WHERE id = $product_id";
            if ($conn->query($delete_query)) {
                $success_message = "Product deleted successfully!";
            } else {
                $error_message = "Error deleting product: " . $conn->error;
            }
            break;
            
        case 'toggle_featured':
            $toggle_query = "UPDATE products SET is_featured = NOT is_featured WHERE id = $product_id";
            if ($conn->query($toggle_query)) {
                $success_message = "Product featured status updated!";
            } else {
                $error_message = "Error updating product: " . $conn->error;
            }
            break;
    }
}

// Build search and filter query
$where_conditions = [];
$params = [];

// Search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_conditions[] = "(p.name LIKE '%$search%' OR p.description LIKE '%$search%' OR p.brand LIKE '%$search%')";
}

// Category filter
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category_id = intval($_GET['category']);
    $where_conditions[] = "p.category_id = $category_id";
}

// Status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $_GET['status'];
    switch ($status) {
        case 'featured':
            $where_conditions[] = "p.is_featured = 1";
            break;
        case 'flash_sale':
            $where_conditions[] = "p.is_flash_sale = 1";
            break;
        case 'out_of_stock':
            $where_conditions[] = "p.stock = 0";
            break;
        case 'low_stock':
            $where_conditions[] = "p.stock <= 5 AND p.stock > 0";
            break;
    }
}

// Price range filter
if (isset($_GET['price_min']) && !empty($_GET['price_min'])) {
    $price_min = floatval($_GET['price_min']);
    $where_conditions[] = "p.price >= $price_min";
}

if (isset($_GET['price_max']) && !empty($_GET['price_max'])) {
    $price_max = floatval($_GET['price_max']);
    $where_conditions[] = "p.price <= $price_max";
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

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM products p $where_clause";
$count_result = $conn->query($count_query);
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products with pagination
$query = "SELECT p.*, pi.image_path as main_image, c.name as category_name 
          FROM products p 
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
          LEFT JOIN categories c ON p.category_id = c.id
          $where_clause 
          ORDER BY p.created_at DESC 
          LIMIT $per_page OFFSET $offset";
$products_result = $conn->query($query);

// Get categories for filter
$categories_query = "SELECT id, name FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
$categories_result = $conn->query($categories_query);

// Set page variables for layout
$page_title = 'Products';
$page_description = 'Manage all products in the Kings Reign inventory';
$show_back_button = true;
$back_url = 'dashboard_modern.php';
$header_actions = '
    <a href="add_product.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Product
    </a>
    <button class="btn btn-secondary" onclick="exportProducts()">
        <i class="fas fa-download"></i> Export
    </button>
';

// Custom CSS for this page
$custom_css = '
.products-container {
    padding: 2rem;
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

.products-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.products-stats {
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

.products-table {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table-header {
    background: var(--bg-secondary);
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.bulk-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.bulk-select {
    margin-right: 0.5rem;
}

.product-row {
    display: grid;
    grid-template-columns: 50px 100px 2fr 1fr 1fr 1fr 1fr 120px;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    align-items: center;
}

.product-row:hover {
    background: var(--bg-secondary);
}

.product-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
}

.product-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.product-category {
    font-size: 0.8rem;
    color: var(--text-secondary);
}

.product-price {
    font-weight: 600;
    color: var(--success-color);
}

.product-stock {
    font-weight: 500;
}

.stock-low {
    color: var(--warning-color);
}

.stock-out {
    color: var(--danger-color);
}

.product-status {
    display: flex;
    gap: 0.25rem;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

.status-featured {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-flash {
    background: #fef3c7;
    color: #d97706;
}

.product-actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 4px;
    font-size: 0.8rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.action-edit {
    background: var(--primary-color);
    color: white;
}

.action-delete {
    background: var(--danger-color);
    color: white;
}

.action-toggle {
    background: var(--warning-color);
    color: white;
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
    border: 1px solid var(--border-color);
    background: white;
    color: var(--text-primary);
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9rem;
}

.pagination-btn:hover {
    background: var(--bg-secondary);
}

.pagination-btn.active {
    background: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .product-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .product-image {
        width: 100px;
        height: 100px;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
}
';

// Custom JavaScript for this page
$custom_js = '
// Bulk actions functionality
function selectAllProducts() {
    const checkboxes = document.querySelectorAll(".product-checkbox");
    const selectAllCheckbox = document.getElementById("selectAll");
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll(".product-checkbox:checked");
    const bulkActions = document.getElementById("bulkActions");
    
    if (checkboxes.length > 0) {
        bulkActions.style.display = "flex";
    } else {
        bulkActions.style.display = "none";
    }
}

function performBulkAction(action) {
    const checkboxes = document.querySelectorAll(".product-checkbox:checked");
    const selectedProducts = Array.from(checkboxes).map(cb => cb.value);
    
    if (selectedProducts.length === 0) {
        showNotification("Please select products to perform this action.", "error");
        return;
    }
    
    if (action === "delete") {
        if (!confirm("Are you sure you want to delete " + selectedProducts.length + " product(s)?")) {
            return;
        }
    }
    
    // Create form and submit
    const form = document.createElement("form");
    form.method = "POST";
    form.innerHTML = `
        <input type="hidden" name="bulk_action" value="${action}">
        ${selectedProducts.map(id => `<input type="hidden" name="selected_products[]" value="${id}">`).join("")}
    `;
    document.body.appendChild(form);
    form.submit();
}

// Search and filter functionality
function applyFilters() {
    const search = document.getElementById("search").value;
    const category = document.getElementById("category").value;
    const status = document.getElementById("status").value;
    const priceMin = document.getElementById("priceMin").value;
    const priceMax = document.getElementById("priceMax").value;
    
    const params = new URLSearchParams();
    if (search) params.append("search", search);
    if (category) params.append("category", category);
    if (status) params.append("status", status);
    if (priceMin) params.append("price_min", priceMin);
    if (priceMax) params.append("price_max", priceMax);
    
    window.location.href = "products.php?" + params.toString();
}

function clearFilters() {
    window.location.href = "products.php";
}

function exportProducts() {
    const currentParams = new URLSearchParams(window.location.search);
    currentParams.append("export", "1");
    window.location.href = "export_products.php?" + currentParams.toString();
}

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

// Initialize page
document.addEventListener("DOMContentLoaded", function() {
    updateBulkActions();
    
    // Auto-submit search on enter
    document.getElementById("search").addEventListener("keypress", function(e) {
        if (e.key === "Enter") {
            applyFilters();
        }
    });
});
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
                <div class="products-container">
                    <!-- Success/Error Messages -->
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Filters Section -->
                    <div class="filters-section">
                        <h3><i class="fas fa-filter"></i> Filters & Search</h3>
                        <div class="filters-grid">
                            <div class="filter-group">
                                <label class="filter-label" for="search">Search Products</label>
                                <input type="text" id="search" class="filter-input" 
                                       placeholder="Search by name, description, or brand"
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label" for="category">Category</label>
                                <select id="category" class="filter-input">
                                    <option value="">All Categories</option>
                                    <?php while ($category = $categories_result->fetch_assoc()): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>"
                                                <?php echo (isset($_GET['category']) && $_GET['category'] === $category['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label" for="status">Status</label>
                                <select id="status" class="filter-input">
                                    <option value="">All Status</option>
                                    <option value="featured" <?php echo (isset($_GET['status']) && $_GET['status'] === 'featured') ? 'selected' : ''; ?>>Featured</option>
                                    <option value="flash_sale" <?php echo (isset($_GET['status']) && $_GET['status'] === 'flash_sale') ? 'selected' : ''; ?>>Flash Sale</option>
                                    <option value="out_of_stock" <?php echo (isset($_GET['status']) && $_GET['status'] === 'out_of_stock') ? 'selected' : ''; ?>>Out of Stock</option>
                                    <option value="low_stock" <?php echo (isset($_GET['status']) && $_GET['status'] === 'low_stock') ? 'selected' : ''; ?>>Low Stock</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label" for="priceMin">Min Price</label>
                                <input type="number" id="priceMin" class="filter-input" 
                                       placeholder="0.00" step="0.01" min="0"
                                       value="<?php echo isset($_GET['price_min']) ? htmlspecialchars($_GET['price_min']) : ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label" for="priceMax">Max Price</label>
                                <input type="number" id="priceMax" class="filter-input" 
                                       placeholder="999999.99" step="0.01" min="0"
                                       value="<?php echo isset($_GET['price_max']) ? htmlspecialchars($_GET['price_max']) : ''; ?>">
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
                    
                    <!-- Products Header -->
                    <div class="products-header">
                        <div class="products-stats">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo number_format($total_products); ?></div>
                                <div class="stat-label">Total Products</div>
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
                        
                        <div class="bulk-actions" id="bulkActions" style="display: none;">
                            <select id="bulkActionSelect" class="filter-input">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete Selected</option>
                                <option value="feature">Mark as Featured</option>
                                <option value="unfeature">Unmark as Featured</option>
                            </select>
                            <button class="btn btn-secondary" onclick="performBulkAction(document.getElementById(\'bulkActionSelect\').value)">
                                Apply
                            </button>
                        </div>
                    </div>
                    
                    <!-- Products Table -->
                    <div class="products-table">
                        <div class="table-header">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <input type="checkbox" id="selectAll" onchange="selectAllProducts()">
                                <span>Select All</span>
                            </div>
                            <div>
                                Showing <?php echo number_format($offset + 1); ?> - <?php echo number_format(min($offset + $per_page, $total_products)); ?> 
                                of <?php echo number_format($total_products); ?> products
                            </div>
                        </div>
                        
                        <?php if ($products_result->num_rows > 0): ?>
                            <?php while ($product = $products_result->fetch_assoc()): ?>
                                <div class="product-row">
                                    <div>
                                        <input type="checkbox" class="product-checkbox" value="<?php echo $product['id']; ?>" onchange="updateBulkActions()">
                                    </div>
                                    
                                    <div>
                                        <img src="../<?php echo htmlspecialchars($product['main_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             class="product-image">
                                    </div>
                                    
                                    <div>
                                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                        <?php if ($product['brand']): ?>
                                            <div class="product-category">Brand: <?php echo htmlspecialchars($product['brand']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-price">
                                        GH ₵<?php echo number_format($product['price'], 2); ?>
                                        <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                            <br><small style="text-decoration: line-through; color: var(--text-secondary);">
                                                GH ₵<?php echo number_format($product['original_price'], 2); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-stock <?php echo $product['stock'] == 0 ? 'stock-out' : ($product['stock'] <= 5 ? 'stock-low' : ''); ?>">
                                        <?php echo $product['stock']; ?> in stock
                                    </div>
                                    
                                    <div class="product-status">
                                        <?php if ($product['is_featured']): ?>
                                            <span class="status-badge status-featured">Featured</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_flash_sale']): ?>
                                            <span class="status-badge status-flash">Flash Sale</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                           class="action-btn action-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="products.php?action=toggle_featured&id=<?php echo $product['id']; ?>" 
                                           class="action-btn action-toggle" title="<?php echo $product['is_featured'] ? 'Unmark as Featured' : 'Mark as Featured'; ?>">
                                            <i class="fas fa-star"></i>
                                        </a>
                                        <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                           class="action-btn action-delete" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <i class="fas fa-box" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <h3>No products found</h3>
                                <p>Try adjusting your search criteria or add some products.</p>
                                <a href="add_product.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Product
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>" 
                                   class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>" 
                                   class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['category']) ? '&category=' . urlencode($_GET['category']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>" 
                                   class="pagination-btn">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <script><?php echo $custom_js; ?></script>
</body>
</html>