<?php
/**
 * Import database SQL file
 */

include_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Read the SQL file
    $sql = file_get_contents('database.sql');
    
    if ($sql === false) {
        throw new Exception("Could not read database.sql file");
    }
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                $executed++;
                echo "Executed statement " . $executed . "\n";
            } catch (PDOException $e) {
                $errors++;
                echo "Error executing statement: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nImport completed!\n";
    echo "Statements executed: " . $executed . "\n";
    echo "Errors: " . $errors . "\n";
    
    // Test the import
    $count_stmt = $db->query("SELECT COUNT(*) as total FROM users");
    $count = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "Total users in database: " . $count . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
