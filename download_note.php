<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit('You must be logged in to download files');
}

// Check if note ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid note ID');
}

$note_id = (int)$_GET['id'];

// Get file information from database
try {
    $stmt = $conn->prepare("SELECT * FROM notes WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("i", $note_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('HTTP/1.1 404 Not Found');
        exit('File not found');
    }
    
    $file = $result->fetch_assoc();
    
    // Use the stored file path or build it from filename
    $file_path = $file['file_path'] ?? ('uploads/notes/' . $file['filename']);
    
    // Check if file exists
    if (!file_exists($file_path)) {
        header('HTTP/1.1 404 Not Found');
        exit('File not found on server');
    }
    
    // Update download count if the column exists
    try {
        $update = $conn->prepare("UPDATE notes SET downloads = downloads + 1 WHERE id = ?");
        if ($update) {
            $update->bind_param("i", $note_id);
            $update->execute();
        }
    } catch (Exception $e) {
        // Ignore errors with download counter
        error_log("Error updating download count: " . $e->getMessage());
    }
    
    // Get original filename from database or use current filename
    $original_name = $file['title'];
    $file_ext = pathinfo($file['filename'], PATHINFO_EXTENSION);
    $download_filename = $original_name . '.' . $file_ext;
    
    // Set content type based on file type
    $content_type = $file['file_type'];
    if (empty($content_type)) {
        // Default content type if not stored in the database
        $content_type = 'application/octet-stream';
    }
    
    // Set appropriate headers for file download
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: attachment; filename="' . $download_filename . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    
    // Output file content
    readfile($file_path);
    exit;
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    exit('Error: ' . $e->getMessage());
}
?> 