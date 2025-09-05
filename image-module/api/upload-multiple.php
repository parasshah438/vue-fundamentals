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

    // Check if files were uploaded
    if (!isset($_FILES['images']) || empty($_FILES['images']['tmp_name'])) {
        errorResponse('No image files uploaded');
    }

    $files = $_FILES['images'];
    
    // Handle both single file and multiple files
    if (!is_array($files['tmp_name'])) {
        // Convert single file to array format
        $files = [
            'name' => [$files['name']],
            'tmp_name' => [$files['tmp_name']],
            'error' => [$files['error']],
            'size' => [$files['size']],
            'type' => [$files['type']]
        ];
    }

    $fileCount = count($files['tmp_name']);

    // Check maximum files limit
    if ($fileCount > ImageConfig::$maxFilesPerUpload) {
        errorResponse('Too many files. Maximum allowed: ' . ImageConfig::$maxFilesPerUpload);
    }

    // Validate all files first
    $validationErrors = [];
    $totalSize = 0;
    
    for ($i = 0; $i < $fileCount; $i++) {
        if (empty($files['tmp_name'][$i])) {
            continue;
        }
        
        $file = [
            'name' => $files['name'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i],
            'type' => $files['type'][$i]
        ];
        
        $errors = validateImageFile($file, true);
        if (!empty($errors)) {
            $validationErrors[] = "File '{$file['name']}': " . implode(', ', $errors);
        }
        
        $totalSize += $file['size'];
    }

    // Check total size limit (50MB for multiple uploads)
    $maxTotalSize = 50 * 1024 * 1024; // 50MB
    if ($totalSize > $maxTotalSize) {
        $validationErrors[] = 'Total file size (' . formatBytes($totalSize) . ') exceeds maximum allowed (' . formatBytes($maxTotalSize) . ')';
    }

    if (!empty($validationErrors)) {
        errorResponse('Validation failed: ' . implode('; ', $validationErrors));
    }

    // Generate batch ID for this upload
    $batchId = generateBatchId();
    
    // Start database transaction
    $db->beginTransaction();

    try {
        // Create batch record
        $batchQuery = "INSERT INTO upload_batches (batch_id, total_images, total_size, upload_type) 
                       VALUES (:batch_id, :total_images, :total_size, :upload_type)";
        $batchStmt = $db->prepare($batchQuery);
        $batchStmt->bindParam(':batch_id', $batchId);
        $batchStmt->bindParam(':total_images', $fileCount, PDO::PARAM_INT);
        $batchStmt->bindParam(':total_size', $totalSize, PDO::PARAM_INT);
        $batchStmt->bindValue(':upload_type', 'multiple');
        $batchStmt->execute();

        $uploadedImages = [];
        $uploadedFiles = [];

        // Process each file
        for ($i = 0; $i < $fileCount; $i++) {
            if (empty($files['tmp_name'][$i])) {
                continue;
            }

            $file = [
                'name' => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
                'type' => $files['type'][$i]
            ];

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
                throw new Exception("Failed to move uploaded file: {$file['name']}");
            }

            $uploadedFiles[] = $uploadPath; // Track for cleanup on error

            // Insert image record into database
            $query = "INSERT INTO images (original_name, file_name, file_path, file_size, mime_type, width, height, upload_type, batch_id) 
                      VALUES (:original_name, :file_name, :file_path, :file_size, :mime_type, :width, :height, :upload_type, :batch_id)";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':original_name', $file['name']);
            $stmt->bindParam(':file_name', $uniqueFilename);
            $stmt->bindParam(':file_path', $uploadPath);
            $stmt->bindParam(':file_size', $file['size'], PDO::PARAM_INT);
            $stmt->bindParam(':mime_type', $mimeType);
            $stmt->bindParam(':width', $width, PDO::PARAM_INT);
            $stmt->bindParam(':height', $height, PDO::PARAM_INT);
            $stmt->bindValue(':upload_type', 'multiple');
            $stmt->bindParam(':batch_id', $batchId);

            if (!$stmt->execute()) {
                throw new Exception("Failed to save image information to database: {$file['name']}");
            }

            $imageId = $db->lastInsertId();

            // Create thumbnail
            $thumbnailPath = createThumbnail($uploadPath, $uniqueFilename);

            // Add to uploaded images array
            $uploadedImages[] = [
                'id' => (int)$imageId,
                'original_name' => $file['name'],
                'file_name' => $uniqueFilename,
                'file_path' => $uploadPath,
                'file_size' => (int)$file['size'],
                'file_size_formatted' => formatBytes($file['size']),
                'mime_type' => $mimeType,
                'width' => $width,
                'height' => $height,
                'dimensions' => $width . 'x' . $height,
                'upload_type' => 'multiple',
                'batch_id' => $batchId,
                'thumbnail_path' => $thumbnailPath,
                'uploaded_at' => date('Y-m-d H:i:s')
            ];

            // Log upload activity
            logUploadActivity('upload', $imageId, $batchId, $uniqueFilename, $file['size']);
        }

        // Commit transaction
        $db->commit();

        // Return success response
        successResponse([
            'batch_id' => $batchId,
            'total_images' => count($uploadedImages),
            'total_size' => $totalSize,
            'total_size_formatted' => formatBytes($totalSize),
            'upload_type' => 'multiple',
            'images' => $uploadedImages,
            'uploaded_at' => date('Y-m-d H:i:s')
        ], count($uploadedImages) . ' images uploaded successfully');

    } catch (Exception $e) {
        // Rollback transaction
        $db->rollback();
        
        // Clean up uploaded files
        foreach ($uploadedFiles as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        throw $e;
    }

} catch (Exception $e) {
    errorResponse('Error uploading images: ' . $e->getMessage(), 500);
}

// Function to create thumbnail (same as single upload)
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