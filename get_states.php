<?php
header('Content-Type: application/json');
require_once 'config.php';

$country_id = isset($_GET['country_id']) ? intval($_GET['country_id']) : 0;

if ($country_id <= 0) {
    echo json_encode(['error' => 'Invalid country ID']);
    exit;
}

$conn = getDBConnection();

$sql = "SELECT id, name, country_id 
        FROM states 
        WHERE country_id = ? 
        AND status = 1 
        AND deleted_at IS NULL 
        ORDER BY name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $country_id);
$stmt->execute();
$result = $stmt->get_result();

$states = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $states[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'country_id' => $row['country_id']
        ];
    }
}

echo json_encode($states);
$stmt->close();
$conn->close();
?>
