<?php
session_start(); // Start the session to access session variables

// Database connection settings
$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Handle form data securely
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $recipeName = $_POST['recipeName'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $procedure = $_POST['procedure'];
    $category = $_POST['category'];
    $base64Image = $_POST['base64Image'];

    // Prepare the SQL statement to prevent SQL injection, and include user_id
    $stmt = $conn->prepare("INSERT INTO tbl_recipe (name, `desc`, ingredients, `procedure`, `category`, img, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }
    $stmt->bind_param("ssssssi", $recipeName, $description, $ingredients, $procedure, $category, $base64Image, $user_id); // Bind user_id along with the other parameters

    // Execute the statement
    if ($stmt->execute()) {
        echo "<h2>Recipe Submitted Successfully!</h2>";
        echo "<p>You can now go back to <a href='http://127.0.0.1/home.html'>home</a>.</p>";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
