<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
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
$input = json_decode(file_get_contents('php://input'), true);

switch($method) {
    case 'GET':
        // Read all users
        if (isset($_GET['id'])) {
            // Get single user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user ? $user : ['error' => 'User not found']);
        } else {
            // Get all users
            $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
        break;
        
    case 'POST':
        // Create new user
        if (!empty($input['name']) && !empty($input['email'])) {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone) VALUES (?, ?, ?)");
                $stmt->execute([$input['name'], $input['email'], $input['phone'] ?? '']);
                $id = $pdo->lastInsertId();
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'User created successfully']);
            } catch(PDOException $e) {
                echo json_encode(['error' => 'Error creating user: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Name and email are required']);
        }
        break;
        
    case 'PUT':
        // Update user
        if (!empty($input['id']) && !empty($input['name']) && !empty($input['email'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                $result = $stmt->execute([$input['name'], $input['email'], $input['phone'] ?? '', $input['id']]);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'User updated successfully']);
                } else {
                    echo json_encode(['error' => 'Failed to update user']);
                }
            } catch(PDOException $e) {
                echo json_encode(['error' => 'Error updating user: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'ID, name and email are required']);
        }
        break;
        
    case 'DELETE':
        // Delete user
        if (isset($_GET['id'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $result = $stmt->execute([$_GET['id']]);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
                } else {
                    echo json_encode(['error' => 'Failed to delete user']);
                }
            } catch(PDOException $e) {
                echo json_encode(['error' => 'Error deleting user: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'User ID is required']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>
