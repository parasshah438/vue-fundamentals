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

    // Get all countries
    $query = "SELECT id, name, code FROM countries ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();

    $countries = [];
    while ($row = $stmt->fetch()) {
        $countries[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'code' => $row['code']
        ];
    }

    // Add artificial delay to simulate real API
    usleep(300000); // 300ms delay

    successResponse($countries, 'Countries loaded successfully');

} catch (Exception $e) {
    errorResponse('Error loading countries: ' . $e->getMessage(), 500);
}
?>