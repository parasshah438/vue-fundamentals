<?php
/**
 * API endpoint to update an existing user
 * PUT /api/update.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once '../config.php';

try {
    // Check if request method is PUT
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

    // Check if ID is provided
    if (empty($data['id'])) {
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

    // Check if email exists for other users
    $checkUser = new User($db);
    $checkUser->email = $data['email'];
    if ($checkUser->emailExists()) {
        // Get the user with this email
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bindParam(1, $data['email']);
        $stmt->bindParam(2, $data['id']);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Email already exists for another user'
            ], 400);
        }
    }

    // Update user properties
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

    // Update user
    if ($user->update()) {
        // Get updated user data
        $user->readOne();

        $response = [
            'success' => true,
            'message' => 'User updated successfully',
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'status' => $user->status
            ]
        ];

        sendJsonResponse($response);
    } else {
        sendJsonResponse([
            'success' => false,
            'message' => 'Unable to update user'
        ], 500);
    }

} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
?>
