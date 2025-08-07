<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

include('../db.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$message = '';
$messageType = '';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $category_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    switch ($action) {
        case 'delete':
            $delete_query = "DELETE FROM categories WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param('i', $category_id);
            if ($stmt->execute()) {
                $message = 'Category deleted successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error deleting category: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
            break;
            
        case 'toggle_status':
            $toggle_query = "UPDATE categories SET is_active = NOT is_active WHERE id = ?";
            $stmt = $conn->prepare($toggle_query);
            $stmt->bind_param('i', $category_id);
            if ($stmt->execute()) {
                $message = 'Category status updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating category status: ' . $stmt->error;
                $messageType = 'error';
            }
            $stmt->close();
            break;
    }
}

// Build search and filter query
$where_conditions = [];
$params = [];

// Search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);
    $where_conditions[] = "(c.name LIKE '%$search%' OR c.description LIKE '%$search%' OR c.slug LIKE '%$search%')";
}

// Status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status = $_GET['status'];
    switch ($status) {
        case 'active':
            $where_conditions[] = "c.is_active = 1";
            break;
        case 'inactive':
            $where_conditions[] = "c.is_active = 0";
            break;
    }
}

// Product count filter
if (isset($_GET['product_count']) && !empty($_GET['product_count'])) {
    $product_count = $_GET['product_count'];
    switch ($product_count) {
        case 'empty':
            $where_conditions[] = "(SELECT COUNT(*) FROM products WHERE category_id = c.id) = 0";
            break;
        case 'has_products':
            $where_conditions[] = "(SELECT COUNT(*) FROM products WHERE category_id = c.id) > 0";
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

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM categories c $where_clause";
$count_result = $conn->query($count_query);
$total_categories = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_categories / $per_page);

// Get categories with pagination
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count
          FROM categories c 
          $where_clause 
          ORDER BY c.sort_order ASC, c.name ASC 
          LIMIT $per_page OFFSET $offset";

$categories_result = $conn->query($query);

$page_title = 'Categories';
$page_description = 'Manage product categories';
$show_back_button = false;
$header_actions = '<a href="add_category.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Category</a>';

// Custom CSS for this page
$custom_css = '
.categories-container {
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

.categories-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.categories-stats {
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

.categories-table {
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

.category-row {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr 1fr 1fr 120px;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    align-items: center;
}

.category-row:hover {
    background: var(--bg-secondary);
}

.category-name {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.category-description {
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.category-slug {
    background: var(--bg-secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    color: var(--text-secondary);
    font-family: monospace;
}

.product-count {
    font-weight: 600;
    color: var(--primary-color);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.category-actions {
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
    .category-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
}
';

// Custom JavaScript for this page
$custom_js = '
// Search and filter functionality
function applyFilters() {
    const search = document.getElementById("search").value;
    const status = document.getElementById("status").value;
    const productCount = document.getElementById("productCount").value;
    
    const params = new URLSearchParams();
    if (search) params.append("search", search);
    if (status) params.append("status", status);
    if (productCount) params.append("product_count", productCount);
    
    window.location.href = "categories.php?" + params.toString();
}

function clearFilters() {
    window.location.href = "categories.php";
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
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <?php include 'includes/header.php'; ?>
            
            <div class="admin-content">
                <div class="categories-container">
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
                                <label class="filter-label" for="search">Search Categories</label>
                                <input type="text" id="search" class="filter-input" 
                                       placeholder="Search by name, description, or slug"
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label" for="status">Status</label>
                                <select id="status" class="filter-input">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label" for="productCount">Product Count</label>
                                <select id="productCount" class="filter-input">
                                    <option value="">All Categories</option>
                                    <option value="empty" <?php echo (isset($_GET['product_count']) && $_GET['product_count'] === 'empty') ? 'selected' : ''; ?>>Empty Categories</option>
                                    <option value="has_products" <?php echo (isset($_GET['product_count']) && $_GET['product_count'] === 'has_products') ? 'selected' : ''; ?>>Has Products</option>
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
                    
                    <!-- Categories Header -->
                    <div class="categories-header">
                        <div class="categories-stats">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo number_format($total_categories); ?></div>
                                <div class="stat-label">Total Categories</div>
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
                    
                    <!-- Categories Table -->
                    <div class="categories-table">
                        <div class="table-header">
                            <div>
                                Showing <?php echo number_format($offset + 1); ?> - <?php echo number_format(min($offset + $per_page, $total_categories)); ?> 
                                of <?php echo number_format($total_categories); ?> categories
                            </div>
                        </div>
                        
                        <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                            <?php while ($category = $categories_result->fetch_assoc()): ?>
                                <div class="category-row">
                                    <div>
                                        <div class="category-name"><?php echo htmlspecialchars($category['name']); ?></div>
                                        <div class="category-description"><?php echo htmlspecialchars($category['description'] ?? ''); ?></div>
                                    </div>
                                    
                                    <div>
                                        <span class="category-slug"><?php echo htmlspecialchars($category['slug']); ?></span>
                                    </div>
                                    
                                    <div class="product-count">
                                        <?php echo $category['product_count']; ?> products
                                    </div>
                                    
                                    <div>
                                        <span class="status-badge status-<?php echo $category['is_active'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $category['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    
                                    <div>
                                        Sort: <?php echo $category['sort_order']; ?>
                                    </div>
                                    
                                    <div class="category-actions">
                                        <a href="edit_category.php?id=<?php echo $category['id']; ?>" 
                                           class="action-btn action-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="categories.php?action=toggle_status&id=<?php echo $category['id']; ?>" 
                                           class="action-btn action-toggle" title="<?php echo $category['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                            <i class="fas fa-<?php echo $category['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                        </a>
                                        <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" 
                                           class="action-btn action-delete" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this category?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding: 2rem; text-align: center; color: var(--text-secondary);">
                                <i class="fas fa-tags" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <h3>No categories found</h3>
                                <p>Try adjusting your search criteria or add some categories.</p>
                                <a href="add_category.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Category
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo isset($_GET['product_count']) ? '&product_count=' . urlencode($_GET['product_count']) : ''; ?>" 
                                   class="pagination-btn">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo isset($_GET['product_count']) ? '&product_count=' . urlencode($_GET['product_count']) : ''; ?>" 
                                   class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?><?php echo isset($_GET['product_count']) ? '&product_count=' . urlencode($_GET['product_count']) : ''; ?>" 
                                   class="pagination-btn">
                                    <i class="fas fa-chevron-right"></i> Next
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