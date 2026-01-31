<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// For debugging
error_log("Notes request received");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
error_log("Filter: $filter");

try {
    // Base query - using the actual database structure
    $sql = "SELECT * FROM notes";
    
    // Apply filter
    if ($filter !== 'all') {
        switch ($filter) {
            case 'pdf':
                $sql .= " WHERE file_type = 'application/pdf'";
                break;
            case 'doc':
                $sql .= " WHERE (file_type = 'application/msword' OR file_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')";
                break;
            case 'ppt':
                $sql .= " WHERE (file_type = 'application/vnd.ms-powerpoint' OR file_type = 'application/vnd.openxmlformats-officedocument.presentationml.presentation')";
                break;
        }
    }
    
    // Order by newest first (using ID as a proxy for upload time)
    $sql .= " ORDER BY id DESC";
    
    error_log("SQL query: $sql");
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error preparing statement: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if query was successful
    if (!$result) {
        throw new Exception("Database error executing query: " . $conn->error);
    }
    
    // Process results
    $notes = [];
    while ($row = $result->fetch_assoc()) {
        error_log("Found note: " . print_r($row, true));
        
        // Format file size
        $file_size = format_file_size($row['file_size']);
        
        // Create a timestamp or use current time if not available
        $uploaded_at = date('Y-m-d H:i:s');
        if (!empty($row['description']) && strpos($row['description'], 'Uploaded on') === 0) {
            // Try to extract date from description
            $date_str = str_replace('Uploaded on ', '', $row['description']);
            if (strtotime($date_str) !== false) {
                $uploaded_at = $date_str;
            }
        }
        
        $notes[] = [
            'id' => $row['id'],
            'user_id' => $row['user_id'],
            'title' => $row['title'],
            'filename' => $row['filename'],
            'file_type' => $row['file_type'],
            'file_size' => $file_size,
            'uploaded_at' => $uploaded_at,
            'downloads' => $row['downloads'] ?? 0
        ];
    }
    
    // Return response
    echo json_encode([
        'success' => true,
        'notes' => $notes,
        'count' => count($notes)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_notes.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error retrieving notes: ' . $e->getMessage()
    ]);
}

// Helper function to format file size
function format_file_size($size) {
    if ($size < 1024) {
        return $size . " B";
    } elseif ($size < 1048576) {
        return round($size / 1024, 1) . " KB";
    } else {
        return round($size / 1048576, 1) . " MB";
    }
}
?> 