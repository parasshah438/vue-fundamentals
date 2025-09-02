<?php
/**
 * Test the users API endpoint
 */

// Change to the script directory
chdir(__DIR__);

// Set up environment to simulate HTTP request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_GET['page'] = 1;
$_GET['limit'] = 10;
$_GET['search'] = '';

// Capture output
ob_start();

// Include the users API
include 'api/users.php';

// Get the output
$output = ob_get_clean();

echo "API Response:\n";
echo $output;
?>
