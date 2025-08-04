<?php
session_start();
include('db.php');
header('Content-Type: application/json');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Build the WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'sss';
}

if (!empty($category)) {
    $where_conditions[] = "c.name = ?";
    $params[] = $category;
    $param_types .= 's';
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $where_clause";

if (!empty($params)) {
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $count_result = $stmt->get_result();
} else {
    $count_result = $conn->query($count_query);
}

$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

// Get products
$query = "SELECT p.*, pi.image_path as main_image, c.name as category_name
          FROM products p 
          LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1 
          LEFT JOIN categories c ON p.category_id = c.id 
          $where_clause 
          ORDER BY p.created_at DESC 
          LIMIT $per_page OFFSET $offset";

if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get all categories for filter dropdown
$categories_query = "SELECT name FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC";
$categories_result = $conn->query($categories_query);
$categories = [];
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['name'];
}

echo json_encode([
    'success' => true,
    'products' => $products,
    'total_products' => $total_products,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'categories' => $categories,
    'search' => $search,
    'category' => $category
]); 