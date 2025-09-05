<?php
require_once '../config/database.php';

// Set CORS headers
setCorsHeaders();

try {
    // Create database connection
    $database = new Database();
    $db = $database->getConnection();

    if (!$db) {
        errorResponse('Database connection failed', 500);
    }

    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        errorResponse('Only POST method allowed', 405);
    }

    // Create upload directories
    createUploadDirectories();

    // Check if file was uploaded
    if (!isset($_FILES['image']) || empty($_FILES['image']['tmp_name'])) {
        errorResponse('No image file uploaded');
    }

    $file = $_FILES['image'];

    // Validate the uploaded file
    $validationErrors = validateImageFile($file, false);
    if (!empty($validationErrors)) {
        errorResponse('Validation failed: ' . implode(', ', $validationErrors));
    }

    // Get image information
    $imageInfo = getimagesize($file['tmp_name']);
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    
    // Get MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    // Generate unique filename
    $uniqueFilename = generateUniqueFilename($file['name']);
    $uploadPath = ImageConfig::$uploadDir . $uniqueFilename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        errorResponse('Failed to move uploaded file', 500);
    }

    // Insert image record into database
    $query = "INSERT INTO images (original_name, file_name, file_path, file_size, mime_type, width, height, upload_type) 
              VALUES (:original_name, :file_name, :file_path, :file_size, :mime_type, :width, :height, :upload_type)";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':original_name', $file['name']);
    $stmt->bindParam(':file_name', $uniqueFilename);
    $stmt->bindParam(':file_path', $uploadPath);
    $stmt->bindParam(':file_size', $file['size'], PDO::PARAM_INT);
    $stmt->bindParam(':mime_type', $mimeType);
    $stmt->bindParam(':width', $width, PDO::PARAM_INT);
    $stmt->bindParam(':height', $height, PDO::PARAM_INT);
    $stmt->bindValue(':upload_type', 'single');

    if (!$stmt->execute()) {
        // Delete uploaded file if database insert fails
        unlink($uploadPath);
        errorResponse('Failed to save image information to database', 500);
    }

    $imageId = $db->lastInsertId();

    // Log upload activity
    logUploadActivity('upload', $imageId, null, $uniqueFilename, $file['size']);

    // Create thumbnail (optional)
    $thumbnailPath = createThumbnail($uploadPath, $uniqueFilename);
    
    // Convert server paths to web URLs
    $baseUrl = '/vue-js/image-module/';
    $fileUrl = $baseUrl . str_replace('../', '', $uploadPath);
    $thumbnailUrl = $thumbnailPath ? $baseUrl . str_replace('../', '', $thumbnailPath) : null;

    // Return success response
    successResponse([
        'id' => (int)$imageId,
        'original_name' => $file['name'],
        'file_name' => $uniqueFilename,
        'file_path' => $fileUrl,  // Web URL instead of server path
        'file_size' => (int)$file['size'],
        'file_size_formatted' => formatBytes($file['size']),
        'mime_type' => $mimeType,
        'width' => $width,
        'height' => $height,
        'dimensions' => $width . 'x' . $height,
        'upload_type' => 'single',
        'thumbnail_path' => $thumbnailUrl,  // Web URL instead of server path
        'uploaded_at' => date('Y-m-d H:i:s')
    ], 'Image uploaded successfully');

} catch (Exception $e) {
    // Clean up uploaded file if it exists
    if (isset($uploadPath) && file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    
    errorResponse('Error uploading image: ' . $e->getMessage(), 500);
}

// Function to create thumbnail
function createThumbnail($sourcePath, $filename) {
    try {
        $thumbnailDir = ImageConfig::$thumbnailDir;
        $thumbnailPath = $thumbnailDir . 'thumb_' . $filename;
        
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        $sourceWidth = $imageInfo[0];
        $sourceHeight = $imageInfo[1];
        $mimeType = $imageInfo['mime'];
        
        // Calculate thumbnail dimensions (max 200x200)
        $maxThumbSize = 200;
        $ratio = min($maxThumbSize / $sourceWidth, $maxThumbSize / $sourceHeight);
        $thumbWidth = (int)($sourceWidth * $ratio);
        $thumbHeight = (int)($sourceHeight * $ratio);
        
        // Create source image resource
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($sourcePath);
                break;
            default:
                return null;
        }
        
        if (!$sourceImage) {
            return null;
        }
        
        // Create thumbnail image
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);
        
        // Save thumbnail
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($thumbImage, $thumbnailPath, 85);
                break;
            case 'image/png':
                imagepng($thumbImage, $thumbnailPath, 8);
                break;
            case 'image/gif':
                imagegif($thumbImage, $thumbnailPath);
                break;
            case 'image/webp':
                imagewebp($thumbImage, $thumbnailPath, 85);
                break;
        }
        
        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
        
        return $thumbnailPath;
        
    } catch (Exception $e) {
        error_log("Failed to create thumbnail: " . $e->getMessage());
        return null;
    }
}
?>