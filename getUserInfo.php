<?php
session_start();

// Check if the session variable for username is set
if (isset($_SESSION['username'])) {
    echo json_encode(['user_name' => $_SESSION['username']]); 
} else {
    echo json_encode(['error' => 'User not logged in']);
}
?>
