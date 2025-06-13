<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// For debugging
error_log("Upload request received");
error_log("POST: " . print_r($_POST, true));
error_log("FILES: " . print_r($_FILES, true));

// Check user authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Check if files were uploaded
if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
    echo json_encode(['success' => false, 'error' => 'No files uploaded']);
    exit;
}

// Create upload directory if it doesn't exist
$upload_dir = 'uploads/notes/';
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        exit;
    }
    chmod($upload_dir, 0777); // Ensure write permissions
}

// Allowed file types and their extensions
$allowed_extensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
$uploaded_files = [];
$errors = [];

// Process each uploaded file
foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
    // Skip empty uploads
    if (empty($tmp_name)) continue;
    
    $file_name = $_FILES['files']['name'][$key];
    $file_size = $_FILES['files']['size'][$key];
    $file_error = $_FILES['files']['error'][$key];
    
    error_log("Processing file: $file_name");
    
    // Check for upload errors
    if ($file_error !== UPLOAD_ERR_OK) {
        $errors[] = "Error uploading $file_name: " . upload_error_message($file_error);
        continue;
    }
    
    // Check file extension
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    if (!in_array($file_ext, $allowed_extensions)) {
        $errors[] = "$file_name has invalid file type. Allowed types: " . implode(', ', $allowed_extensions);
        continue;
    }
    
    // Create unique filename
    $safe_filename = preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
    $unique_filename = uniqid() . '_' . $safe_filename;
    $file_path = $upload_dir . $unique_filename;
    
    error_log("Attempting to save file to: $file_path");
    
    // Get file type for database
    $file_type = '';
    switch ($file_ext) {
        case 'pdf':
            $file_type = 'application/pdf';
            break;
        case 'doc':
            $file_type = 'application/msword';
            break;
        case 'docx':
            $file_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            break;
        case 'ppt':
            $file_type = 'application/vnd.ms-powerpoint';
            break;
        case 'pptx':
            $file_type = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            break;
    }
    
    // Move uploaded file
    if (move_uploaded_file($tmp_name, $file_path)) {
        // Set proper permissions
        chmod($file_path, 0644);
        
        // Get title from filename (without extension)
        $title = pathinfo($file_name, PATHINFO_FILENAME);
        
        // Insert into database
        try {
            $stmt = $conn->prepare("INSERT INTO notes (user_id, title, filename, file_type, file_size, file_path, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            // Set file path to match upload directory
            $file_path = $upload_dir . $unique_filename;
            $description = "Uploaded on " . date('Y-m-d H:i:s');
            
            $stmt->bind_param("isssiss", $_SESSION['user_id'], $title, $unique_filename, $file_type, $file_size, $file_path, $description);
            
            if ($stmt->execute()) {
                $uploaded_files[] = [
                    'id' => $stmt->insert_id,
                    'name' => $file_name,
                    'type' => $file_type,
                    'size' => format_file_size($file_size)
                ];
                error_log("File uploaded successfully: $file_name");
            } else {
                unlink($file_path); // Remove file if DB insert fails
                $errors[] = "Database error for $file_name: " . $stmt->error;
                error_log("Database error: " . $stmt->error);
            }
        } catch (Exception $e) {
            unlink($file_path); // Remove file if exception
            $errors[] = "Error saving $file_name: " . $e->getMessage();
            error_log("Exception: " . $e->getMessage());
            
            // If the error might be due to missing columns, suggest running setup
            if (strpos($e->getMessage(), "Unknown column") !== false) {
                $errors[] = "Database structure issue detected. Please run setup_database.php to fix it.";
            }
        }
    } else {
        $errors[] = "Failed to move uploaded file $file_name";
        error_log("Failed to move uploaded file: $file_name");
    }
}

// Return response
if (empty($errors)) {
    echo json_encode([
        'success' => true,
        'files' => $uploaded_files
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => implode(', ', $errors),
        'uploaded' => $uploaded_files
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

// Helper function to get upload error message
function upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'The uploaded file was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing a temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error';
    }
}
?> 