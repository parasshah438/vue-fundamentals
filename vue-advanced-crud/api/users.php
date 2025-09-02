<?php
/**
 * API endpoint to get all users with pagination and search
 * GET /api/users.php?page=1&limit=10&search=john
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once '../config.php';

try {
    // Initialize database and user object
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Get query parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';

    // Validate pagination parameters
    if ($page < 1) $page = 1;
    if ($limit < 1 || $limit > 100) $limit = 10; // Max 100 records per page

    // Get users
    $stmt = $user->read($page, $limit, $search);
    $users = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Convert JSON fields
        $row['skills'] = json_decode($row['skills'], true) ?: [];
        
        // Convert boolean fields
        $row['email_notifications'] = (bool)$row['email_notifications'];
        $row['sms_notifications'] = (bool)$row['sms_notifications'];
        
        // Format dates
        $row['date_of_birth'] = $row['date_of_birth'] ?: null;
        $row['hire_date'] = $row['hire_date'] ?: null;
        $row['preferred_work_time'] = $row['preferred_work_time'] ?: null;
        $row['last_login'] = $row['last_login'] ?: null;
        
        $users[] = $row;
    }

    // Get total count for pagination
    $total_records = $user->count($search);
    $total_pages = ceil($total_records / $limit);

    // Get statistics
    $stats = $user->getStats();

    // Prepare response
    $response = [
        'success' => true,
        'data' => $users,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total_records,
            'per_page' => $limit,
            'has_next' => $page < $total_pages,
            'has_prev' => $page > 1
        ],
        'stats' => $stats,
        'message' => 'Users retrieved successfully'
    ];

    sendJsonResponse($response);

} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
?>
