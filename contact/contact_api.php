<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration
$host = 'localhost';
$dbname = 'vue_crud';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        // Handle contact form submission
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        if (empty($input['name']) || empty($input['email']) || empty($input['subject']) || empty($input['message'])) {
            echo json_encode(['error' => 'All fields except phone are required']);
            exit();
        }
        
        // Validate email format
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Please enter a valid email address']);
            exit();
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $input['name'],
                $input['email'],
                $input['phone'],
                $input['subject'],
                $input['message']
            ]);
            
            echo json_encode(['success' => 'Contact form submitted successfully']);
        } catch(PDOException $e) {
            echo json_encode(['error' => 'Failed to insert data: ' . $e->getMessage()]);
        }
        exit();
}