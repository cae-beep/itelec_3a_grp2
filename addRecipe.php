<?php
session_start(); // Start the session to access session variables

// Database connection settings
$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

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
    // Check if all required fields are present
    if (
        isset($_POST['recipeName'], $_POST['description'], $_POST['ingredients'], $_POST['procedure'], $_POST['category'], $_POST['base64Image']) &&
        !empty($_POST['recipeName']) && !empty($_POST['description']) && !empty($_POST['ingredients']) &&
        !empty($_POST['procedure']) && !empty($_POST['category'])
    ) {
        // Retrieve form data
        $recipeName = htmlspecialchars($_POST['recipeName'], ENT_QUOTES, 'UTF-8');
        $description = nl2br(htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8'));
        $ingredients = nl2br(htmlspecialchars($_POST['ingredients'], ENT_QUOTES, 'UTF-8'));
        $procedure = nl2br(htmlspecialchars($_POST['procedure'], ENT_QUOTES, 'UTF-8'));
        $category = htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');
        $base64Image = $_POST['base64Image']; // Image data already expected in base64 format

        // Prepare the SQL statement to prevent SQL injection, and include user_id
        $stmt = $conn->prepare("INSERT INTO tbl_recipe (name, `desc`, ingredients, `procedure`, `category`, img, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Preparation failed: " . $conn->error);
        }
        
        $stmt->bind_param("ssssssi", $recipeName, $description, $ingredients, $procedure, $category, $base64Image, $user_id); // Bind user_id along with the other parameters

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to home page
            header("Location: http://127.0.0.1/home.html#");
            exit(); 
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Please fill in all required fields.";
    }
}

// Close the connection
$conn->close();
?>
