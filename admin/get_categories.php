<?php
// Helper function to get categories from database
function getCategories($conn, $active_only = true) {
    $where_clause = $active_only ? "WHERE is_active = 1" : "";
    $query = "SELECT id, name, slug FROM categories $where_clause ORDER BY sort_order ASC, name ASC";
    $result = $conn->query($query);
    
    $categories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    
    return $categories;
}

// Function to generate category options HTML for forms
function generateCategoryOptions($categories, $selected_id = '') {
    $html = '<option value="">Select Category</option>';
    foreach ($categories as $category) {
        $is_selected = ($selected_id == $category['id']) ? 'selected' : '';
        $html .= '<option value="' . $category['id'] . '" ' . $is_selected . '>' . 
                 htmlspecialchars($category['name']) . '</option>';
    }
    return $html;
}

// Function to get category name by ID
function getCategoryName($conn, $category_id) {
    $query = "SELECT name FROM categories WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc()['name'];
    }
    
    return 'Unknown Category';
}
?> 