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

    // Only accept GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        errorResponse('Only GET method allowed', 405);
    }

    // Get query parameters
    $uploadType = isset($_GET['type']) ? $_GET['type'] : 'all';
    $batchId = isset($_GET['batch_id']) ? $_GET['batch_id'] : null;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'uploaded_at';
    $sortOrder = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

    // Validate parameters
    $allowedTypes = ['all', 'single', 'multiple'];
    if (!in_array($uploadType, $allowedTypes)) {
        $uploadType = 'all';
    }

    $allowedSortFields = ['id', 'original_name', 'file_size', 'uploaded_at', 'width', 'height'];
    if (!in_array($sortBy, $allowedSortFields)) {
        $sortBy = 'uploaded_at';
    }

    $allowedSortOrders = ['ASC', 'DESC'];
    if (!in_array(strtoupper($sortOrder), $allowedSortOrders)) {
        $sortOrder = 'DESC';
    }

    // Build query
    $whereConditions = [];
    $params = [];

    if ($uploadType !== 'all') {
        $whereConditions[] = "upload_type = :upload_type";
        $params[':upload_type'] = $uploadType;
    }

    if ($batchId) {
        $whereConditions[] = "batch_id = :batch_id";
        $params[':batch_id'] = $batchId;
    }

    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM images $whereClause";
    $countStmt = $db->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalCount = $countStmt->fetch()['total'];

    // Get images
    $query = "SELECT 
                id, 
                original_name, 
                file_name, 
                file_path, 
                file_size, 
                mime_type, 
                width, 
                height, 
                upload_type, 
                batch_id, 
                uploaded_at 
              FROM images 
              $whereClause 
              ORDER BY $sortBy $sortOrder 
              LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($query);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $images = [];

    while ($row = $stmt->fetch()) {
        // Check if thumbnail exists
        $thumbnailPath = ImageConfig::$thumbnailDir . 'thumb_' . $row['file_name'];
        $hasThumbnail = file_exists($thumbnailPath);
        
        // Convert server paths to web URLs
        $baseUrl = '/vue-js/image-module/';
        $fileUrl = $baseUrl . str_replace('../', '', $row['file_path']);
        $thumbnailUrl = $hasThumbnail ? $baseUrl . str_replace('../', '', $thumbnailPath) : null;

        $images[] = [
            'id' => (int)$row['id'],
            'original_name' => $row['original_name'],
            'file_name' => $row['file_name'],
            'file_path' => $fileUrl,  // Web URL instead of server path
            'file_size' => (int)$row['file_size'],
            'file_size_formatted' => formatBytes($row['file_size']),
            'mime_type' => $row['mime_type'],
            'width' => (int)$row['width'],
            'height' => (int)$row['height'],
            'dimensions' => $row['width'] . 'x' . $row['height'],
            'upload_type' => $row['upload_type'],
            'batch_id' => $row['batch_id'],
            'thumbnail_path' => $thumbnailUrl,  // Web URL instead of server path
            'has_thumbnail' => $hasThumbnail,
            'uploaded_at' => $row['uploaded_at'],
            'uploaded_at_formatted' => date('M j, Y g:i A', strtotime($row['uploaded_at']))
        ];
    }

    // Get batch information if needed
    $batches = [];
    if ($uploadType === 'multiple' || $uploadType === 'all') {
        $batchQuery = "SELECT 
                        batch_id, 
                        total_images, 
                        total_size, 
                        upload_type, 
                        created_at 
                       FROM upload_batches 
                       ORDER BY created_at DESC";
        
        $batchStmt = $db->prepare($batchQuery);
        $batchStmt->execute();
        
        while ($batchRow = $batchStmt->fetch()) {
            $batches[] = [
                'batch_id' => $batchRow['batch_id'],
                'total_images' => (int)$batchRow['total_images'],
                'total_size' => (int)$batchRow['total_size'],
                'total_size_formatted' => formatBytes($batchRow['total_size']),
                'upload_type' => $batchRow['upload_type'],
                'created_at' => $batchRow['created_at'],
                'created_at_formatted' => date('M j, Y g:i A', strtotime($batchRow['created_at']))
            ];
        }
    }

    // Calculate pagination info
    $totalPages = ceil($totalCount / $limit);
    $currentPage = floor($offset / $limit) + 1;
    $hasNextPage = $currentPage < $totalPages;
    $hasPrevPage = $currentPage > 1;

    // Return success response
    successResponse([
        'images' => $images,
        'batches' => $batches,
        'pagination' => [
            'total_count' => (int)$totalCount,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'limit' => $limit,
            'offset' => $offset,
            'has_next_page' => $hasNextPage,
            'has_prev_page' => $hasPrevPage
        ],
        'filters' => [
            'upload_type' => $uploadType,
            'batch_id' => $batchId,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ]
    ], 'Images retrieved successfully');

} catch (Exception $e) {
    errorResponse('Error retrieving images: ' . $e->getMessage(), 500);
}
?>