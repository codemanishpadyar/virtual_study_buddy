<?php
session_start();
require_once 'db.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

// Function to sanitize input
function sanitize_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($data))));
}

// Debug log function
function debug_log($message, $data = null) {
    error_log("DEBUG: " . $message);
    if ($data !== null) {
        error_log("DATA: " . print_r($data, true));
    }
}

// Handle different actions
switch ($action) {
    case 'list':
        debug_log("Fetching topics list");
        debug_log("Session user_id", $_SESSION['user_id']);
        
        // Get topics list with optional category filter
        $category = isset($_GET['category']) ? sanitize_input($_GET['category']) : null;
        $search = isset($_GET['search']) ? sanitize_input($_GET['search']) : null;
        
        debug_log("List parameters", [
            'category' => $category,
            'search' => $search
        ]);
        
        // Test database connection
        if (!$conn) {
            debug_log("Database connection failed", mysqli_connect_error());
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Database connection failed']);
            exit();
        }
        
        // Simple test query
        $test_query = "SELECT COUNT(*) as count FROM forum_topics";
        $test_result = mysqli_query($conn, $test_query);
        if ($test_result) {
            $count = mysqli_fetch_assoc($test_result)['count'];
            debug_log("Total topics in database", ['count' => $count]);
        } else {
            debug_log("Test query failed", mysqli_error($conn));
        }
        
        $sql = "SELECT t.id, t.title, t.content, t.category, t.created_at, t.user_id, t.is_pinned,
                       u.username, COUNT(DISTINCT r.id) as reply_count,
                       COUNT(DISTINCT l.id) as like_count,
                       EXISTS(SELECT 1 FROM forum_likes WHERE user_id = ? AND content_type = 'topic' AND content_id = t.id) as is_liked
                FROM forum_topics t 
                LEFT JOIN users u ON t.user_id = u.id 
                LEFT JOIN forum_replies r ON t.id = r.topic_id AND r.deleted = 0
                LEFT JOIN forum_likes l ON t.id = l.content_id AND l.content_type = 'topic'
                WHERE t.deleted = 0";
        $params = [$user_id];
        $types = "i";
        
        if ($category && $category !== 'All Topics') {
            $sql .= " AND t.category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        if ($search) {
            $sql .= " AND (t.title LIKE CONCAT('%', ?, '%') OR t.content LIKE CONCAT('%', ?, '%'))";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }
        
        $sql .= " GROUP BY t.id, t.title, t.content, t.category, t.created_at, t.user_id, u.username 
                  ORDER BY t.is_pinned DESC, t.created_at DESC";
        
        debug_log("SQL Query", $sql);
        debug_log("Parameters", $params);
        
        if (empty($params)) {
            debug_log("Executing simple query");
            $result = mysqli_query($conn, $sql);
            if (!$result) {
                debug_log("Simple query failed", mysqli_error($conn));
            }
        } else {
            debug_log("Preparing statement");
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                if (!empty($types)) {
                    debug_log("Binding parameters", ['types' => $types, 'params' => $params]);
                    mysqli_stmt_bind_param($stmt, $types, ...$params);
                }
                debug_log("Executing prepared statement");
                $success = mysqli_stmt_execute($stmt);
                if (!$success) {
                    debug_log("Statement execution failed", mysqli_stmt_error($stmt));
                }
                $result = mysqli_stmt_get_result($stmt);
                if (!$result) {
                    debug_log("Failed to get result", mysqli_stmt_error($stmt));
                }
            } else {
                debug_log("Failed to prepare statement", ['error' => mysqli_error($conn)]);
                $result = false;
            }
        }
        
        if ($result) {
            $topics = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $topics[] = $row;
            }
            debug_log("Found topics", ['count' => count($topics), 'first_topic' => !empty($topics) ? $topics[0] : null]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'topics' => $topics]);
        } else {
            $error = mysqli_error($conn);
            debug_log("Failed to fetch topics", ['error' => $error]);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to fetch topics', 'sql_error' => $error]);
        }
        break;

    case 'create':
        debug_log("Creating new topic");
        debug_log("POST data", $_POST);
        
        // Create new topic
        $title = isset($_POST['title']) ? sanitize_input($_POST['title']) : '';
        $content = isset($_POST['content']) ? sanitize_input($_POST['content']) : '';
        $category = isset($_POST['category']) ? sanitize_input($_POST['category']) : '';

        debug_log("Sanitized inputs", [
            'title' => $title,
            'content' => $content,
            'category' => $category,
            'user_id' => $user_id
        ]);

        if (empty($title) || empty($content) || empty($category)) {
            debug_log("Missing required fields", [
                'title_empty' => empty($title),
                'content_empty' => empty($content),
                'category_empty' => empty($category)
            ]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Missing required fields',
                'data' => [
                    'title' => $title,
                    'content' => $content,
                    'category' => $category
                ]
            ]);
            exit();
        }

        $sql = "INSERT INTO forum_topics (user_id, title, content, category) 
                VALUES (?, ?, ?, ?)";
        
        debug_log("Preparing SQL", $sql);
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isss", $user_id, $title, $content, $category);
            
            if (mysqli_stmt_execute($stmt)) {
                $new_id = mysqli_insert_id($conn);
                debug_log("Topic created successfully", ['topic_id' => $new_id]);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'topic_id' => $new_id]);
            } else {
                $error = mysqli_stmt_error($stmt);
                debug_log("Failed to execute statement", ['error' => $error]);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => 'Failed to create topic',
                    'sql_error' => $error
                ]);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = mysqli_error($conn);
            debug_log("Failed to prepare statement", ['error' => $error]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Failed to create topic',
                'sql_error' => $error
            ]);
        }
        break;

    case 'get_topic':
        // Get single topic with replies
        $topic_id = isset($_GET['topic_id']) ? (int)$_GET['topic_id'] : 0;
        
        // Get topic details with reactions
        $sql = "SELECT t.*, u.username,
                COUNT(DISTINCT l.id) as like_count,
                COUNT(DISTINCT r.id) as reply_count,
                GROUP_CONCAT(DISTINCT CONCAT(fr.reaction_type, ':', COUNT(fr.id)) SEPARATOR ',') as reactions,
                EXISTS(SELECT 1 FROM forum_likes WHERE user_id = ? AND content_type = 'topic' AND content_id = t.id) as is_liked
                FROM forum_topics t 
                LEFT JOIN users u ON t.user_id = u.id 
                LEFT JOIN forum_likes l ON t.id = l.content_id AND l.content_type = 'topic'
                LEFT JOIN forum_replies r ON t.id = r.topic_id AND r.deleted = 0
                LEFT JOIN forum_reactions fr ON t.id = fr.topic_id
                WHERE t.id = ? AND t.deleted = 0
                GROUP BY t.id";
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $topic_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($topic = mysqli_fetch_assoc($result)) {
                // Get replies with nested structure
                $sql = "SELECT r.*, u.username,
                        COUNT(DISTINCT l.id) as like_count,
                        EXISTS(SELECT 1 FROM forum_likes WHERE user_id = ? AND content_type = 'reply' AND content_id = r.id) as is_liked
                        FROM forum_replies r 
                        LEFT JOIN users u ON r.user_id = u.id 
                        LEFT JOIN forum_likes l ON r.id = l.content_id AND l.content_type = 'reply'
                        WHERE r.topic_id = ? AND r.deleted = 0
                        GROUP BY r.id
                        ORDER BY r.parent_id ASC NULLS FIRST, r.created_at ASC";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $topic_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    $replies = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $replies[] = $row;
                    }
                    $topic['replies'] = $replies;
                    
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'topic' => $topic]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Failed to fetch replies']);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Topic not found']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to fetch topic']);
        }
        break;

    case 'react':
        // Add reaction to topic
        $topic_id = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
        $reaction = isset($_POST['reaction']) ? sanitize_input($_POST['reaction']) : '';
        
        if ($topic_id && $reaction) {
            // Check if reaction already exists
            $sql = "SELECT id FROM forum_reactions WHERE topic_id = ? AND user_id = ? AND reaction_type = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iis", $topic_id, $user_id, $reaction);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                // Remove existing reaction
                $sql = "DELETE FROM forum_reactions WHERE topic_id = ? AND user_id = ? AND reaction_type = ?";
            } else {
                // Add new reaction
                $sql = "INSERT INTO forum_reactions (topic_id, user_id, reaction_type) VALUES (?, ?, ?)";
            }
            
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iis", $topic_id, $user_id, $reaction);
            
            if (mysqli_stmt_execute($stmt)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Failed to update reaction']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid parameters']);
        }
        break;

    case 'reply':
        // Add reply to topic
        $topic_id = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
        $content = isset($_POST['content']) ? sanitize_input($_POST['content']) : '';
        $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        
        if ($topic_id && $content) {
            $sql = "INSERT INTO forum_replies (topic_id, user_id, content, parent_id) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iisi", $topic_id, $user_id, $content, $parent_id);
            
            if (mysqli_stmt_execute($stmt)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Failed to add reply']);
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid parameters']);
        }
        break;

    case 'delete_topic':
        // Soft delete topic
        $topic_id = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
        
        $sql = "UPDATE forum_topics SET deleted = 1 
                WHERE id = $topic_id AND user_id = $user_id";
        
        if (mysqli_query($conn, $sql)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to delete topic']);
        }
        break;

    case 'delete_reply':
        // Soft delete reply
        $reply_id = isset($_POST['reply_id']) ? (int)$_POST['reply_id'] : 0;
        
        $sql = "UPDATE forum_replies SET deleted = 1 
                WHERE id = $reply_id AND user_id = $user_id";
        
        if (mysqli_query($conn, $sql)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to delete reply']);
        }
        break;

    case 'like':
        $content_type = sanitize_input($_POST['content_type']); // 'topic' or 'reply'
        $content_id = (int)$_POST['content_id'];

        if (!in_array($content_type, ['topic', 'reply'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid content type']);
            exit();
        }

        // Check if content exists and is not deleted
        if ($content_type === 'topic') {
            $check_sql = "SELECT id FROM forum_topics WHERE id = ? AND deleted = 0";
        } else {
            $check_sql = "SELECT id FROM forum_replies WHERE id = ? AND deleted = 0";
        }

        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "i", $content_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!mysqli_fetch_assoc($result)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Content not found']);
            exit();
        }

        // Try to insert the like
        $sql = "INSERT INTO forum_likes (user_id, content_type, content_id) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isi", $user_id, $content_type, $content_id);

        if (mysqli_stmt_execute($stmt)) {
            // Get updated like count
            $count_sql = "SELECT COUNT(*) as count FROM forum_likes WHERE content_type = ? AND content_id = ?";
            $stmt = mysqli_prepare($conn, $count_sql);
            mysqli_stmt_bind_param($stmt, "si", $content_type, $content_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $like_count = mysqli_fetch_assoc($result)['count'];

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'like_count' => $like_count]);
        } else {
            // If duplicate like, return success but with existing count
            if (mysqli_errno($conn) === 1062) { // Duplicate entry error
                $count_sql = "SELECT COUNT(*) as count FROM forum_likes WHERE content_type = ? AND content_id = ?";
                $stmt = mysqli_prepare($conn, $count_sql);
                mysqli_stmt_bind_param($stmt, "si", $content_type, $content_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $like_count = mysqli_fetch_assoc($result)['count'];

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'like_count' => $like_count]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Failed to like content']);
            }
        }
        break;

    case 'unlike':
        $content_type = sanitize_input($_POST['content_type']); // 'topic' or 'reply'
        $content_id = (int)$_POST['content_id'];

        if (!in_array($content_type, ['topic', 'reply'])) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid content type']);
            exit();
        }

        // Delete the like
        $sql = "DELETE FROM forum_likes WHERE user_id = ? AND content_type = ? AND content_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isi", $user_id, $content_type, $content_id);

        if (mysqli_stmt_execute($stmt)) {
            // Get updated like count
            $count_sql = "SELECT COUNT(*) as count FROM forum_likes WHERE content_type = ? AND content_id = ?";
            $stmt = mysqli_prepare($conn, $count_sql);
            mysqli_stmt_bind_param($stmt, "si", $content_type, $content_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $like_count = mysqli_fetch_assoc($result)['count'];

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'like_count' => $like_count]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Failed to unlike content']);
        }
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid action']);
        break;
} 