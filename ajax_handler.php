

<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce');

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die(json_encode(['success' => false, 'message' => 'Connection failed']));
}

mysqli_set_charset($conn, 'utf8mb4');

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get_categories') {
    $parent_id = $_POST['parent_id'] ?? '';
    
    // Build query based on parent_id
    if ($parent_id === '') {
        // Get main categories (parent_id IS NULL)
        //$query = "SELECT id, name, parent_id FROM categories WHERE parent_id IS NULL ORDER BY name ASC";
        $query = "SELECT id, name, parent_id FROM categories WHERE (parent_id IS NULL OR parent_id = 0) ORDER BY name ASC";
    } else {
        // Get subcategories
        $query = "SELECT id, name, parent_id FROM categories WHERE parent_id = ? ORDER BY name ASC";
    }
    
    $stmt = mysqli_prepare($conn, $query);
    
    if ($parent_id !== '') {
        mysqli_stmt_bind_param($stmt, 'i', $parent_id);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $categories = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Check if this category has children
        $check_query = "SELECT COUNT(*) as count FROM categories WHERE parent_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, 'i', $row['id']);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $check_row = mysqli_fetch_assoc($check_result);
        
        $categories[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'parent_id' => $row['parent_id'],
            'has_children' => $check_row['count'] > 0
        ];
        
        mysqli_stmt_close($check_stmt);
    }
    
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} elseif ($action === 'save_selection') {
    $data = json_decode($_POST['data'], true);
    
    // Here you can save to your database
    // Example: INSERT INTO user_selections (category_id_1, category_id_2, category_id_3, ...) VALUES (?, ?, ?, ...)
    
    // For demonstration, just return success
    echo json_encode([
        'success' => true,
        'message' => 'Selection saved successfully',
        'data' => $data
    ]);
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
}

mysqli_close($conn);
?>

