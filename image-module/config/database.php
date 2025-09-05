<?php
// Database Configuration
class Database {
    private $host = 'localhost';
    private $db_name = 'image_module';
    private $username = 'root';
    private $password = '';
    private $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Image Upload Configuration
class ImageConfig {
    // Allowed file types
    public static $allowedTypes = [
        'image/jpeg',
        'image/jpg', 
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    // Allowed file extensions
    public static $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp'
    ];
    
    // Maximum file size (5MB)
    public static $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
    
    // Maximum image dimensions
    public static $maxWidth = 4000;
    public static $maxHeight = 4000;
    
    // Minimum image dimensions
    public static $minWidth = 50;
    public static $minHeight = 50;
    
    // Maximum files per multiple upload
    public static $maxFilesPerUpload = 10;
    
    // Upload directory (relative to API scripts)
    public static $uploadDir = '../uploads/';
    
    // Thumbnail directory (relative to API scripts)
    public static $thumbnailDir = '../uploads/thumbnails/';
}

// CORS Headers for API
function setCorsHeaders() {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// JSON Response Helper
function jsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit();
}

// Error Response Helper
function errorResponse($message, $status_code = 400) {
    jsonResponse([
        'success' => false,
        'message' => $message
    ], $status_code);
}

// Success Response Helper
function successResponse($data, $message = 'Success') {
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

// Validate Image File
function validateImageFile($file, $isMultiple = false) {
    $errors = [];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $errors[] = 'No file uploaded';
        return $errors;
    }
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = 'File size exceeds maximum allowed size';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = 'File was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errors[] = 'No file was uploaded';
                break;
            default:
                $errors[] = 'Unknown upload error';
        }
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > ImageConfig::$maxFileSize) {
        $errors[] = 'File size (' . formatBytes($file['size']) . ') exceeds maximum allowed size (' . formatBytes(ImageConfig::$maxFileSize) . ')';
    }
    
    if ($file['size'] <= 0) {
        $errors[] = 'File is empty';
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ImageConfig::$allowedTypes)) {
        $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', ImageConfig::$allowedTypes);
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ImageConfig::$allowedExtensions)) {
        $errors[] = 'Invalid file extension. Allowed extensions: ' . implode(', ', ImageConfig::$allowedExtensions);
    }
    
    // Check if it's actually an image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'File is not a valid image';
    } else {
        $width = $imageInfo[0];
        $height = $imageInfo[1];
        
        // Check image dimensions
        if ($width > ImageConfig::$maxWidth || $height > ImageConfig::$maxHeight) {
            $errors[] = "Image dimensions ({$width}x{$height}) exceed maximum allowed dimensions (" . ImageConfig::$maxWidth . "x" . ImageConfig::$maxHeight . ")";
        }
        
        if ($width < ImageConfig::$minWidth || $height < ImageConfig::$minHeight) {
            $errors[] = "Image dimensions ({$width}x{$height}) are below minimum required dimensions (" . ImageConfig::$minWidth . "x" . ImageConfig::$minHeight . ")";
        }
    }
    
    return $errors;
}

// Format bytes to human readable format
function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

// Generate unique filename
function generateUniqueFilename($originalName) {
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $basename = pathinfo($originalName, PATHINFO_FILENAME);
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
    
    return $basename . '_' . uniqid() . '_' . time() . '.' . $extension;
}

// Generate batch ID for multiple uploads
function generateBatchId() {
    return 'batch_' . uniqid() . '_' . time();
}

// Create upload directories if they don't exist
function createUploadDirectories() {
    $directories = [
        ImageConfig::$uploadDir,
        ImageConfig::$thumbnailDir
    ];
    
    foreach ($directories as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

// Log upload activity
function logUploadActivity($action, $imageId = null, $batchId = null, $fileName = null, $fileSize = null) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO upload_logs (action, image_id, batch_id, file_name, file_size, ip_address, user_agent) 
                  VALUES (:action, :image_id, :batch_id, :file_name, :file_size, :ip_address, :user_agent)";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':image_id', $imageId);
        $stmt->bindParam(':batch_id', $batchId);
        $stmt->bindParam(':file_name', $fileName);
        $stmt->bindParam(':file_size', $fileSize);
        $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');
        
        $stmt->execute();
    } catch (Exception $e) {
        // Log error but don't fail the main operation
        error_log("Failed to log upload activity: " . $e->getMessage());
    }
}
?>