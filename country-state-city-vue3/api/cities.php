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

    // Get state_id from GET parameter
    $state_id = isset($_GET['state_id']) ? (int)$_GET['state_id'] : 0;

    if ($state_id <= 0) {
        errorResponse('Valid state_id is required');
    }

    // Verify state exists
    $state_check = "SELECT id FROM states WHERE id = :state_id";
    $state_stmt = $db->prepare($state_check);
    $state_stmt->bindParam(':state_id', $state_id, PDO::PARAM_INT);
    $state_stmt->execute();

    if ($state_stmt->rowCount() == 0) {
        errorResponse('State not found');
    }

    // Get cities for the state
    $query = "SELECT id, name FROM cities WHERE state_id = :state_id ORDER BY name ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':state_id', $state_id, PDO::PARAM_INT);
    $stmt->execute();

    $cities = [];
    while ($row = $stmt->fetch()) {
        $cities[] = [
            'id' => (int)$row['id'],
            'name' => $row['name'],
            'stateId' => $state_id
        ];
    }

    // Add artificial delay to simulate real API
    usleep(200000); // 200ms delay

    successResponse($cities, 'Cities loaded successfully');

} catch (Exception $e) {
    errorResponse('Error loading cities: ' . $e->getMessage(), 500);
}
?>