<?php
header('Content-Type: application/json');
require_once 'config.php';

$state_id = isset($_GET['state_id']) ? intval($_GET['state_id']) : 0;

if ($state_id <= 0) {
    echo json_encode(['error' => 'Invalid state ID']);
    exit;
}

$conn = getDBConnection();

$sql = "SELECT id, name, state_id, cost 
        FROM cities 
        WHERE state_id = ? 
        AND status = 1 
        AND deleted_at IS NULL 
        ORDER BY name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $state_id);
$stmt->execute();
$result = $stmt->get_result();

$cities = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $cities[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'state_id' => $row['state_id'],
            'cost' => $row['cost']
        ];
    }
}

echo json_encode($cities);
$stmt->close();
$conn->close();
?>
