<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Not authenticated']));
}

$user_id = $_SESSION['user_id'];

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
            
            $sql = "INSERT INTO tasks (user_id, title, description, due_date) VALUES ($user_id, '$title', '$description', '$due_date')";
            $success = mysqli_query($conn, $sql);
            
            echo json_encode(['success' => $success]);
            break;
            
        case 'update':
            $task_id = (int)$_POST['task_id'];
            $title = mysqli_real_escape_string($conn, $_POST['title']);
            $description = mysqli_real_escape_string($conn, $_POST['description']);
            $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
            
            $sql = "UPDATE tasks SET title='$title', description='$description', due_date='$due_date' 
                    WHERE id=$task_id AND user_id=$user_id";
            $success = mysqli_query($conn, $sql);
            
            echo json_encode(['success' => $success]);
            break;
            
        case 'delete':
            $task_id = (int)$_POST['task_id'];
            $sql = "DELETE FROM tasks WHERE id=$task_id AND user_id=$user_id";
            $success = mysqli_query($conn, $sql);
            
            echo json_encode(['success' => $success]);
            break;
            
        case 'complete':
            $task_id = (int)$_POST['task_id'];
            $sql = "UPDATE tasks SET status='completed' WHERE id=$task_id AND user_id=$user_id";
            $success = mysqli_query($conn, $sql);
            
            echo json_encode(['success' => $success]);
            break;
    }
}
// Handle GET requests
else {
    $task_id = $_GET['task_id'] ?? null;
    
    if ($task_id) {
        // Get specific task
        $task_id = (int)$task_id;
        $sql = "SELECT * FROM tasks WHERE id=$task_id AND user_id=$user_id";
        $result = mysqli_query($conn, $sql);
        $task = mysqli_fetch_assoc($result);
        
        echo json_encode($task);
    } elseif (isset($_GET['action']) && $_GET['action'] === 'stats') {
        // Get task statistics
        $sql_total = "SELECT COUNT(*) as count FROM tasks WHERE user_id=$user_id";
        $sql_completed = "SELECT COUNT(*) as count FROM tasks WHERE user_id=$user_id AND status='completed'";
        
        $total = mysqli_fetch_assoc(mysqli_query($conn, $sql_total))['count'];
        $completed = mysqli_fetch_assoc(mysqli_query($conn, $sql_completed))['count'];
        
        echo json_encode([
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed
        ]);
    } else {
        // Get all tasks
        $sql = "SELECT * FROM tasks WHERE user_id=$user_id ORDER BY due_date ASC";
        $result = mysqli_query($conn, $sql);
        $tasks = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $tasks[] = $row;
        }
        
        echo json_encode($tasks);
    }
}
?> 