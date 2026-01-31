<?php
require_once 'db.php';

$sql = file_get_contents('update_notes_table.sql');

if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "Notes table structure updated successfully!";
} else {
    echo "Error updating table structure: " . $conn->error;
} 