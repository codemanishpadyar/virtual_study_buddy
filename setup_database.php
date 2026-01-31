<?php
require_once 'db.php';

// First check if notes table exists
$tableExists = $conn->query("SHOW TABLES LIKE 'notes'");
$createTable = false;

if ($tableExists->num_rows == 0) {
    $createTable = true;
    // Create notes table
    $sql = "CREATE TABLE notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        filename VARCHAR(255) NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        file_size INT NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        deleted TINYINT(1) DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Notes table created successfully<br>";
    } else {
        echo "Error creating notes table: " . $conn->error . "<br>";
    }
} else {
    echo "Notes table already exists<br>";
    
    // Check if filename column exists
    $result = $conn->query("SHOW COLUMNS FROM notes LIKE 'filename'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE notes ADD COLUMN filename VARCHAR(255) NOT NULL AFTER title";
        if ($conn->query($sql) === TRUE) {
            echo "Filename column added successfully<br>";
        } else {
            echo "Error adding filename column: " . $conn->error . "<br>";
        }
    } else {
        echo "Filename column already exists<br>";
    }
    
    // Check if deleted column exists
    $result = $conn->query("SHOW COLUMNS FROM notes LIKE 'deleted'");
    if ($result->num_rows == 0) {
        $sql = "ALTER TABLE notes ADD COLUMN deleted TINYINT(1) DEFAULT 0";
        if ($conn->query($sql) === TRUE) {
            echo "Deleted column added successfully<br>";
        } else {
            echo "Error adding deleted column: " . $conn->error . "<br>";
        }
    } else {
        echo "Deleted column already exists<br>";
    }
}

// Create uploads directory if it doesn't exist
$upload_dir = 'uploads/notes';
if (!file_exists($upload_dir)) {
    if (mkdir($upload_dir, 0777, true)) {
        echo "Uploads directory created successfully<br>";
    } else {
        echo "Error creating uploads directory<br>";
    }
} else {
    echo "Uploads directory already exists<br>";
}

// Set proper permissions for the uploads directory
chmod($upload_dir, 0777);
echo "Directory permissions updated<br>";

echo "Setup completed! You can now upload and view notes.";
?> 