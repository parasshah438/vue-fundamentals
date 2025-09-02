<?php
/**
 * API endpoint to delete a user
 * DELETE /api/delete.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once '../config.php';

try {
    // Check if request method is DELETE
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        sendJsonResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }

    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || empty($data['id'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'User ID is required'
        ], 400);
    }

    // Initialize database and user object
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Check if user exists
    $user->id = $data['id'];
    if (!$user->readOne()) {
        sendJsonResponse([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

    // Store user info before deletion
    $deleted_user = [
        'id' => $user->id,
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email
    ];

    // Delete user
    if ($user->delete()) {
        sendJsonResponse([
            'success' => true,
            'message' => 'User deleted successfully',
            'data' => $deleted_user
        ]);
    } else {
        sendJsonResponse([
            'success' => false,
            'message' => 'Unable to delete user'
        ], 500);
    }

} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
?>
