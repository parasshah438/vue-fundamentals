<?php
/**
 * API endpoint to create a new user
 * POST /api/create.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once '../config.php';

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }

    // Get posted data
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        sendJsonResponse([
            'success' => false,
            'message' => 'No data received'
        ], 400);
    }

    // Initialize database and user object
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'email'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendJsonResponse([
                'success' => false,
                'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
            ], 400);
        }
    }

    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Invalid email format'
        ], 400);
    }

    // Check if email already exists
    $user->email = $data['email'];
    if ($user->emailExists()) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Email already exists'
        ], 400);
    }

    // Set user properties
    $user->first_name = $data['first_name'];
    $user->last_name = $data['last_name'];
    $user->email = $data['email'];
    $user->phone = $data['phone'] ?? null;
    $user->date_of_birth = !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
    $user->gender = $data['gender'] ?? null;
    $user->age = !empty($data['age']) ? (int)$data['age'] : null;
    $user->marital_status = $data['marital_status'] ?? null;
    $user->address = $data['address'] ?? null;
    $user->city = $data['city'] ?? null;
    $user->state = $data['state'] ?? null;
    $user->zip_code = $data['zip_code'] ?? null;
    $user->country = $data['country'] ?? null;
    $user->job_title = $data['job_title'] ?? null;
    $user->company = $data['company'] ?? null;
    $user->department = $data['department'] ?? null;
    $user->salary = !empty($data['salary']) ? (float)$data['salary'] : null;
    $user->hire_date = !empty($data['hire_date']) ? $data['hire_date'] : null;
    $user->employment_type = $data['employment_type'] ?? null;
    $user->status = $data['status'] ?? 'active';
    $user->date_joined = $data['date_joined'] ?? date('Y-m-d');
    $user->avatar = $data['avatar'] ?? null;
    $user->resume = $data['resume'] ?? null;
    $user->website = $data['website'] ?? null;
    $user->linkedin_profile = $data['linkedin_profile'] ?? null;
    $user->favorite_color = $data['favorite_color'] ?? '#667eea';
    $user->experience_level = !empty($data['experience_level']) ? (int)$data['experience_level'] : 0;
    $user->preferred_work_time = !empty($data['preferred_work_time']) ? $data['preferred_work_time'] : null;
    $user->last_login = !empty($data['last_login']) ? $data['last_login'] : null;
    $user->skills = json_encode($data['skills'] ?? []);
    $user->work_mode = $data['work_mode'] ?? null;
    $user->email_notifications = isset($data['email_notifications']) ? (bool)$data['email_notifications'] : true;
    $user->sms_notifications = isset($data['sms_notifications']) ? (bool)$data['sms_notifications'] : false;
    $user->notes = $data['notes'] ?? null;

    // Create user
    $user_id = $user->create();

    if ($user_id) {
        // Get the created user data
        $user->id = $user_id;
        $user->readOne();

        $response = [
            'success' => true,
            'message' => 'User created successfully',
            'data' => [
                'id' => $user_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'status' => $user->status
            ]
        ];

        sendJsonResponse($response, 201);
    } else {
        sendJsonResponse([
            'success' => false,
            'message' => 'Unable to create user'
        ], 500);
    }

} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
?>
