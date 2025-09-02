<?php
/**
 * Test database connection
 */

include_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "Database connection successful!\n";
        
        // Test if users table exists
        $stmt = $db->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "Users table exists!\n";
            
            // Test if we can count users
            $count_stmt = $db->query("SELECT COUNT(*) as total FROM users");
            $count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "Total users in database: " . $count . "\n";
        } else {
            echo "Users table does not exist!\n";
        }
    } else {
        echo "Database connection failed!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
