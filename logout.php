<?php
session_start(); // Start the session

// Destroy all session data
session_destroy();

// Redirect to the index.html page
header("Location: http://127.0.0.1/index.html#"); // Redirect to the home page with the hash
exit();
?>
