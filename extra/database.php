<?php
// db_connection.php

$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = "secretsecret4"; // Replace with your actual password
$dbname = "schema_user"; // Ensure this matches your database name
$port = 3306; // Default port for MySQL

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
