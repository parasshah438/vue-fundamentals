<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../classes/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!User::isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $userData = $user->getUserById($_SESSION['user_id']);
    if ($userData) {
        echo json_encode(['success' => true, 'user' => $userData]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!User::isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"));
    
    if (!$data || !isset($data->first_name) || !isset($data->last_name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'First name and last name are required']);
        exit;
    }

    $user->id = $_SESSION['user_id'];
    $user->first_name = $data->first_name;
    $user->last_name = $data->last_name;
    $user->phone = $data->phone ?? '';
    $user->profile_image = $data->profile_image ?? '';

    if ($user->updateProfile()) {
        // Update session
        $_SESSION['user_name'] = $user->first_name . ' ' . $user->last_name;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Profile update failed']);
    }
}
?>
