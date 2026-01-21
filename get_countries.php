<?php
header('Content-Type: application/json');
require_once 'config.php';

$conn = getDBConnection();

$sql = "SELECT id, name, code 
        FROM countries 
        WHERE status = 1 
        AND deleted_at IS NULL 
        ORDER BY name ASC";

$result = $conn->query($sql);

$countries = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $countries[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'code' => $row['code']
        ];
    }
}

echo json_encode($countries);
$conn->close();
?>
