<?php
/**
 * Migration script to add resume field to users table
 */

include_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Check if resume column already exists
    $check_query = "SHOW COLUMNS FROM users LIKE 'resume'";
    $stmt = $db->prepare($check_query);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add resume column if it doesn't exist
        $alter_query = "ALTER TABLE users ADD COLUMN resume VARCHAR(500) NULL AFTER avatar";
        $db->exec($alter_query);
        echo "✅ Resume column added successfully!\n";
    } else {
        echo "ℹ️  Resume column already exists.\n";
    }
    
    // Verify the column was added
    $verify_query = "DESCRIBE users";
    $stmt = $db->prepare($verify_query);
    $stmt->execute();
    
    echo "\n📋 Current users table structure:\n";
    echo "Column Name\t\tType\t\t\tNull\tKey\tDefault\n";
    echo "----------------------------------------------------------------\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-20s\t%-20s\t%s\t%s\t%s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'] ?? 'NULL'
        );
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
