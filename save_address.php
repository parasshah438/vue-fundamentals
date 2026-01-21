<?php
header('Content-Type: application/json');
require_once 'config.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

$country_id = isset($input['country_id']) ? intval($input['country_id']) : 0;
$state_id = isset($input['state_id']) ? intval($input['state_id']) : 0;
$city_id = isset($input['city_id']) ? intval($input['city_id']) : 0;
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0; // Get from session in production

// Validation
if ($country_id <= 0 || $state_id <= 0 || $city_id <= 0) {
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

$conn = getDBConnection();

// Example: Save to user_addresses table
// Adjust table name and columns according to your schema
$sql = "INSERT INTO user_addresses (user_id, country_id, state_id, city_id, created_at) 
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE 
        country_id = ?, state_id = ?, city_id = ?, updated_at = NOW()";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiiii", $user_id, $country_id, $state_id, $city_id, 
                              $country_id, $state_id, $city_id);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Address saved successfully',
        'data' => [
            'country_id' => $country_id,
            'state_id' => $state_id,
            'city_id' => $city_id
        ]
    ]);
} else {
    echo json_encode(['error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
