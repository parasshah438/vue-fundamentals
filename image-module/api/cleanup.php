<?php
require_once '../config/database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

echo "<h2>Database and File System Cleanup</h2>";

if (!$db) {
    die("Database connection failed");
}

// Get all images from database
$query = "SELECT * FROM images ORDER BY uploaded_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Found " . count($images) . " images in database</h3>";

$uploadDir = '../uploads/';
$thumbnailDir = '../uploads/thumbnails/';

$issues = [];
$fixed = 0;

foreach ($images as $image) {
    $imagePath = $uploadDir . $image['file_name'];
    $thumbnailPath = $thumbnailDir . 'thumb_' . $image['file_name'];
    
    $imageExists = file_exists($imagePath);
    $thumbnailExists = file_exists($thumbnailPath);
    
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<strong>ID:</strong> " . $image['id'] . "<br>";
    echo "<strong>Original Name:</strong> " . $image['original_name'] . "<br>";
    echo "<strong>File Name:</strong> " . $image['file_name'] . "<br>";
    echo "<strong>Image File:</strong> " . ($imageExists ? "✅ EXISTS" : "❌ MISSING") . "<br>";
    echo "<strong>Thumbnail:</strong> " . ($thumbnailExists ? "✅ EXISTS" : "❌ MISSING") . "<br>";
    
    // If image exists but thumbnail doesn't, try to recreate thumbnail
    if ($imageExists && !$thumbnailExists) {
        echo "<strong>Action:</strong> Attempting to recreate thumbnail...<br>";
        
        if (createThumbnail($imagePath, $image['file_name'])) {
            echo "<span style='color: green;'>✅ Thumbnail recreated successfully!</span><br>";
            $fixed++;
        } else {
            echo "<span style='color: red;'>❌ Failed to recreate thumbnail</span><br>";
        }
    }
    
    // If neither file exists, mark for cleanup
    if (!$imageExists && !$thumbnailExists) {
        echo "<span style='color: orange;'>⚠️ Orphaned database record - consider deleting</span><br>";
        $issues[] = $image['id'];
    }
    
    echo "</div>";
}

echo "<h3>Summary</h3>";
echo "<p>Thumbnails recreated: $fixed</p>";
echo "<p>Orphaned records: " . count($issues) . "</p>";

if (!empty($issues)) {
    echo "<p>To clean up orphaned records, you can delete these IDs: " . implode(', ', $issues) . "</p>";
}

// Function to create thumbnail (copied from upload script)
function createThumbnail($sourcePath, $filename) {
    try {
        $thumbnailDir = '../uploads/thumbnails/';
        $thumbnailPath = $thumbnailDir . 'thumb_' . $filename;
        
        // Get image info
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) return false;
        
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
                return false;
        }
        
        if (!$sourceImage) return false;
        
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
        
        return file_exists($thumbnailPath);
        
    } catch (Exception $e) {
        return false;
    }
}
?>
