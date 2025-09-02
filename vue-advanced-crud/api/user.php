<?php
/**
 * API endpoint to get a single user by ID
 * GET /api/user.php?id=1
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
    // Check if ID is provided
    if (empty($_GET['id'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'User ID is required'
        ], 400);
    }

    // Initialize database and user object
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Get user
    $user->id = $_GET['id'];
    if ($user->readOne()) {
        // Prepare user data
        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'date_of_birth' => $user->date_of_birth,
            'gender' => $user->gender,
            'age' => $user->age,
            'marital_status' => $user->marital_status,
            'address' => $user->address,
            'city' => $user->city,
            'state' => $user->state,
            'zip_code' => $user->zip_code,
            'country' => $user->country,
            'job_title' => $user->job_title,
            'company' => $user->company,
            'department' => $user->department,
            'salary' => $user->salary,
            'hire_date' => $user->hire_date,
            'employment_type' => $user->employment_type,
            'status' => $user->status,
            'date_joined' => $user->date_joined,
            'avatar' => $user->avatar,
            'website' => $user->website,
            'linkedin_profile' => $user->linkedin_profile,
            'favorite_color' => $user->favorite_color,
            'experience_level' => $user->experience_level,
            'preferred_work_time' => $user->preferred_work_time,
            'last_login' => $user->last_login,
            'skills' => json_decode($user->skills, true) ?: [],
            'work_mode' => $user->work_mode,
            'email_notifications' => (bool)$user->email_notifications,
            'sms_notifications' => (bool)$user->sms_notifications,
            'notes' => $user->notes
        ];

        sendJsonResponse([
            'success' => true,
            'data' => $userData,
            'message' => 'User retrieved successfully'
        ]);
    } else {
        sendJsonResponse([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
?>
