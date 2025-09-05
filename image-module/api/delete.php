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

    // Only accept DELETE requests
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        errorResponse('Only DELETE method allowed', 405);
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        errorResponse('Invalid JSON data');
    }

    // Check if it's single image delete or batch delete
    if (isset($input['image_id'])) {
        // Single image delete
        $imageId = (int)$input['image_id'];
        
        if ($imageId <= 0) {
            errorResponse('Valid image_id is required');
        }

        deleteSingleImage($db, $imageId);
        
    } elseif (isset($input['batch_id'])) {
        // Batch delete
        $batchId = $input['batch_id'];
        
        if (empty($batchId)) {
            errorResponse('Valid batch_id is required');
        }

        deleteBatch($db, $batchId);
        
    } elseif (isset($input['image_ids']) && is_array($input['image_ids'])) {
        // Multiple images delete
        $imageIds = array_map('intval', $input['image_ids']);
        $imageIds = array_filter($imageIds, function($id) { return $id > 0; });
        
        if (empty($imageIds)) {
            errorResponse('Valid image_ids array is required');
        }

        deleteMultipleImages($db, $imageIds);
        
    } else {
        errorResponse('Either image_id, batch_id, or image_ids is required');
    }

} catch (Exception $e) {
    errorResponse('Error deleting image(s): ' . $e->getMessage(), 500);
}

// Function to delete single image
function deleteSingleImage($db, $imageId) {
    try {
        // Start transaction
        $db->beginTransaction();

        // Get image information
        $query = "SELECT file_path, file_name, batch_id FROM images WHERE id = :image_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
        $stmt->execute();

        $image = $stmt->fetch();
        if (!$image) {
            $db->rollback();
            errorResponse('Image not found');
        }

        // Delete physical files
        deletePhysicalFiles($image['file_path'], $image['file_name']);

        // Delete from database
        $deleteQuery = "DELETE FROM images WHERE id = :image_id";
        $deleteStmt = $db->prepare($deleteQuery);
        $deleteStmt->bindParam(':image_id', $imageId, PDO::PARAM_INT);
        
        if (!$deleteStmt->execute()) {
            $db->rollback();
            errorResponse('Failed to delete image from database', 500);
        }

        // Update batch count if it's part of a batch
        if ($image['batch_id']) {
            updateBatchCount($db, $image['batch_id']);
        }

        // Log delete activity
        logUploadActivity('delete', $imageId, $image['batch_id'], $image['file_name'], null);

        // Commit transaction
        $db->commit();

        successResponse([
            'deleted_image_id' => $imageId,
            'deleted_files' => [
                'main' => $image['file_path'],
                'thumbnail' => ImageConfig::$thumbnailDir . 'thumb_' . $image['file_name']
            ]
        ], 'Image deleted successfully');

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

// Function to delete entire batch
function deleteBatch($db, $batchId) {
    try {
        // Start transaction
        $db->beginTransaction();

        // Get all images in the batch
        $query = "SELECT id, file_path, file_name FROM images WHERE batch_id = :batch_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':batch_id', $batchId);
        $stmt->execute();

        $images = $stmt->fetchAll();
        if (empty($images)) {
            $db->rollback();
            errorResponse('Batch not found or already empty');
        }

        $deletedFiles = [];
        $deletedImageIds = [];

        // Delete physical files for each image
        foreach ($images as $image) {
            deletePhysicalFiles($image['file_path'], $image['file_name']);
            $deletedFiles[] = [
                'main' => $image['file_path'],
                'thumbnail' => ImageConfig::$thumbnailDir . 'thumb_' . $image['file_name']
            ];
            $deletedImageIds[] = (int)$image['id'];
            
            // Log delete activity
            logUploadActivity('delete', $image['id'], $batchId, $image['file_name'], null);
        }

        // Delete all images in the batch
        $deleteImagesQuery = "DELETE FROM images WHERE batch_id = :batch_id";
        $deleteImagesStmt = $db->prepare($deleteImagesQuery);
        $deleteImagesStmt->bindParam(':batch_id', $batchId);
        
        if (!$deleteImagesStmt->execute()) {
            $db->rollback();
            errorResponse('Failed to delete images from database', 500);
        }

        // Delete batch record
        $deleteBatchQuery = "DELETE FROM upload_batches WHERE batch_id = :batch_id";
        $deleteBatchStmt = $db->prepare($deleteBatchQuery);
        $deleteBatchStmt->bindParam(':batch_id', $batchId);
        $deleteBatchStmt->execute();

        // Commit transaction
        $db->commit();

        successResponse([
            'deleted_batch_id' => $batchId,
            'deleted_image_ids' => $deletedImageIds,
            'deleted_images_count' => count($images),
            'deleted_files' => $deletedFiles
        ], 'Batch deleted successfully');

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

// Function to delete multiple images
function deleteMultipleImages($db, $imageIds) {
    try {
        // Start transaction
        $db->beginTransaction();

        $placeholders = str_repeat('?,', count($imageIds) - 1) . '?';
        
        // Get images information
        $query = "SELECT id, file_path, file_name, batch_id FROM images WHERE id IN ($placeholders)";
        $stmt = $db->prepare($query);
        $stmt->execute($imageIds);

        $images = $stmt->fetchAll();
        if (empty($images)) {
            $db->rollback();
            errorResponse('No images found with provided IDs');
        }

        $deletedFiles = [];
        $deletedImageIds = [];
        $affectedBatches = [];

        // Delete physical files for each image
        foreach ($images as $image) {
            deletePhysicalFiles($image['file_path'], $image['file_name']);
            $deletedFiles[] = [
                'main' => $image['file_path'],
                'thumbnail' => ImageConfig::$thumbnailDir . 'thumb_' . $image['file_name']
            ];
            $deletedImageIds[] = (int)$image['id'];
            
            if ($image['batch_id'] && !in_array($image['batch_id'], $affectedBatches)) {
                $affectedBatches[] = $image['batch_id'];
            }
            
            // Log delete activity
            logUploadActivity('delete', $image['id'], $image['batch_id'], $image['file_name'], null);
        }

        // Delete images from database
        $deleteQuery = "DELETE FROM images WHERE id IN ($placeholders)";
        $deleteStmt = $db->prepare($deleteQuery);
        
        if (!$deleteStmt->execute($imageIds)) {
            $db->rollback();
            errorResponse('Failed to delete images from database', 500);
        }

        // Update batch counts for affected batches
        foreach ($affectedBatches as $batchId) {
            updateBatchCount($db, $batchId);
        }

        // Commit transaction
        $db->commit();

        successResponse([
            'deleted_image_ids' => $deletedImageIds,
            'deleted_images_count' => count($images),
            'affected_batches' => $affectedBatches,
            'deleted_files' => $deletedFiles
        ], count($images) . ' images deleted successfully');

    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

// Function to delete physical files
function deletePhysicalFiles($filePath, $fileName) {
    try {
        // Delete main image file
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete thumbnail if exists
        $thumbnailPath = ImageConfig::$thumbnailDir . 'thumb_' . $fileName;
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
    } catch (Exception $e) {
        error_log("Failed to delete physical files: " . $e->getMessage());
        // Don't throw exception here, continue with database deletion
    }
}

// Function to update batch count after image deletion
function updateBatchCount($db, $batchId) {
    try {
        // Count remaining images in batch
        $countQuery = "SELECT COUNT(*) as count, COALESCE(SUM(file_size), 0) as total_size FROM images WHERE batch_id = :batch_id";
        $countStmt = $db->prepare($countQuery);
        $countStmt->bindParam(':batch_id', $batchId);
        $countStmt->execute();
        
        $result = $countStmt->fetch();
        $remainingCount = $result['count'];
        $totalSize = $result['total_size'];

        if ($remainingCount == 0) {
            // Delete empty batch
            $deleteBatchQuery = "DELETE FROM upload_batches WHERE batch_id = :batch_id";
            $deleteBatchStmt = $db->prepare($deleteBatchQuery);
            $deleteBatchStmt->bindParam(':batch_id', $batchId);
            $deleteBatchStmt->execute();
        } else {
            // Update batch count
            $updateBatchQuery = "UPDATE upload_batches SET total_images = :total_images, total_size = :total_size WHERE batch_id = :batch_id";
            $updateBatchStmt = $db->prepare($updateBatchQuery);
            $updateBatchStmt->bindParam(':total_images', $remainingCount, PDO::PARAM_INT);
            $updateBatchStmt->bindParam(':total_size', $totalSize, PDO::PARAM_INT);
            $updateBatchStmt->bindParam(':batch_id', $batchId);
            $updateBatchStmt->execute();
        }
    } catch (Exception $e) {
        error_log("Failed to update batch count: " . $e->getMessage());
        // Don't throw exception here
    }
}
?>