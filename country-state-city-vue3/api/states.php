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

    // Get country_id from GET parameter
    $country_id = isset($_GET['country_id']) ? (int)$_GET['country_id'] : 0;

    if ($country_id <= 0) {
        errorResponse('Valid country_id is required');
    }

    // Verify country exists
    $country_check = "SELECT id FROM countries WHERE id = :country_id";
    $country_stmt = $db->prepare($country_check);
    $country_stmt->bindParam(':country_id', $country_id, PDO::PARAM_INT);
    $country_stmt->execute();

    if ($country_stmt->rowCount() == 0) {
        errorResponse('Country not found');
    }

    // Get states for the country
    $query = "SELECT id, name FROM states WHERE country_id = :country_id ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':country_id', $country_id, PDO::PARAM_INT);
    $stmt->execute();

    $states = [];
    while ($row = $stmt->fetch()) {
        $states[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'countryId' => $country_id
        ];
    }

    // Add artificial delay to simulate real API
    usleep(200000); // 200ms delay

    successResponse($states, 'States loaded successfully');

} catch (Exception $e) {
    errorResponse('Error loading states: ' . $e->getMessage(), 500);
}
?>