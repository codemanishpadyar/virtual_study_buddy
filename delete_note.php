<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid note ID']);
    exit;
}

$note_id = (int)$_POST['id'];
$user_id = $_SESSION['user_id'];

// First check if the note exists and belongs to the user
$stmt = $conn->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Note not found or unauthorized']);
    exit;
}

// Get the file path to delete the actual file
$file = $result->fetch_assoc();
$file_path = $file['file_path'] ?? ('uploads/notes/' . $file['filename']);

// Delete the note from the database
$stmt = $conn->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $note_id, $user_id);

if ($stmt->execute()) {
    // Try to delete the actual file
    if (file_exists($file_path)) {
        @unlink($file_path);
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to delete note']);
} 