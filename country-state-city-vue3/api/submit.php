<?php
require_once '../config/database.php';

// Set CORS headers
setCorsHeaders();

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        errorResponse('Database connection failed', 500);
    }

    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Only POST method allowed', 405);
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        errorResponse('Invalid JSON data');
    }

    // Validate required fields
    $required_fields = ['countryId', 'stateId', 'cityId'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty($input[$field])) {
            errorResponse("Field '$field' is required");
        }
    }

    $country_id = (int)$input['countryId'];
    $state_id = (int)$input['stateId'];
    $city_id = (int)$input['cityId'];

    // Validate IDs
    if ($country_id <= 0 || $state_id <= 0 || $city_id <= 0) {
        errorResponse('Invalid ID values provided');
    }

    // Get country, state, and city details with validation
    $query = "SELECT 
                c.id as country_id, c.name as country_name, c.code as country_code,
                s.id as state_id, s.name as state_name,
                ct.id as city_id, ct.name as city_name
              FROM countries c
              JOIN states s ON c.id = s.country_id
              JOIN cities ct ON s.id = ct.state_id
              WHERE c.id = :country_id AND s.id = :state_id AND ct.id = :city_id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':country_id', $country_id, PDO::PARAM_INT);
    $stmt->bindParam(':state_id', $state_id, PDO::PARAM_INT);
    $stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch();

    if (!$result) {
        errorResponse('Invalid country, state, or city combination');
    }

    // Insert form submission into database
    $insert_query = "INSERT INTO form_submissions 
                     (country_id, state_id, city_id, country_name, state_name, city_name) 
                     VALUES (:country_id, :state_id, :city_id, :country_name, :state_name, :city_name)";

    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':country_id', $country_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':state_id', $state_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':city_id', $city_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':country_name', $result['country_name'], PDO::PARAM_STR);
    $insert_stmt->bindParam(':state_name', $result['state_name'], PDO::PARAM_STR);
    $insert_stmt->bindParam(':city_name', $result['city_name'], PDO::PARAM_STR);

    if (!$insert_stmt->execute()) {
        errorResponse('Failed to save submission', 500);
    }

    // Add artificial delay to simulate processing
    usleep(1000000); // 1 second delay

    // Return success response
    successResponse([
        'country' => $result['country_name'],
        'state' => $result['state_name'],
        'city' => $result['city_name'],
        'timestamp' => date('Y-m-d H:i:s'),
        'submission_id' => $db->lastInsertId()
    ], 'Form submitted successfully');

} catch (Exception $e) {
    errorResponse('Error processing submission: ' . $e->getMessage(), 500);
}
?>