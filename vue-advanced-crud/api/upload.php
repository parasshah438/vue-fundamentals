<?php
/**
 * API endpoint for file upload
 * POST /api/upload.php
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once '../config.php';

try {
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }

    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        sendJsonResponse([
            'success' => false,
            'message' => 'No file uploaded or upload error'
        ], 400);
    }

    $file = $_FILES['file'];
    $upload_dir = '../uploads/';

    // Create uploads directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Get file type from request or determine from file
    $file_type = isset($_POST['type']) ? $_POST['type'] : 'image';
    
    // Define allowed file types based on upload type
    if ($file_type === 'resume') {
        $allowed_types = ['pdf', 'doc', 'docx'];
        $max_size = 10000000; // 10MB for resumes
    } else {
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5000000; // 5MB for images
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_types)) {
        $allowed_list = implode(', ', array_map('strtoupper', $allowed_types));
        sendJsonResponse([
            'success' => false,
            'message' => "Invalid file type. Only {$allowed_list} files are allowed."
        ], 400);
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        $size_mb = $max_size / 1000000;
        sendJsonResponse([
            'success' => false,
            'message' => "File too large. Maximum size is {$size_mb}MB."
        ], 400);
    }

    // Generate unique filename
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Generate URL for the uploaded file
        $file_url = 'uploads/' . $new_filename;
        
        sendJsonResponse([
            'success' => true,
            'message' => 'File uploaded successfully',
            'data' => [
                'filename' => $new_filename,
                'url' => $file_url,
                'size' => $file['size'],
                'type' => $file['type']
            ]
        ]);
    } else {
        sendJsonResponse([
            'success' => false,
            'message' => 'Failed to upload file'
        ], 500);
    }

} catch (Exception $e) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ], 500);
}
?>
