<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userId'])) {
    die(json_encode(["status" => "error", "message" => "User not logged in"]));
}

$userId = $_SESSION['userId']; // Assuming userId is stored in the session

// Database connection function
function openDatabaseConnection() {
    $servername = "127.0.0.1";
    $username = "root";
    $password = "secretsecret4"; // Your MySQL password
    $dbname = "schema_user";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
    }

    return $conn;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required parameters are provided
    if (isset($_POST['recipeId']) && isset($_POST['userId'])) {
        $recipeId = intval($_POST['recipeId']); // Ensure recipeId is an integer
        $userId = intval($_POST['userId']);     // Ensure userId is an integer

        // Debugging output
        error_log("Received recipeId: $recipeId, userId: $userId");

        // Open the database connection
        $conn = openDatabaseConnection();

        // Check if the user already liked the recipe
        $stmt = $conn->prepare("SELECT * FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        $stmt->bind_param("ii", $recipeId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "You already liked this recipe"]);
        } else {
            // Prepare and execute the insert statement
            $stmt = $conn->prepare("INSERT INTO tbl_likes (recipeId, user_Id, created_at) VALUES (?, ?, NOW())");
            
            if ($stmt) {
                $stmt->bind_param("ii", $recipeId, $userId); // Assuming recipeId and userId are integers

                // Execute the statement
                if ($stmt->execute()) {
                    echo json_encode(["status" => "success"]);
                } else {
                    error_log("SQL Error: " . $stmt->error); // Log SQL errors
                    echo json_encode(["status" => "error", "message" => "Execution failed: " . $stmt->error]);
                }

                $stmt->close();
            } else {
                error_log("Prepare statement failed: " . $conn->error); // Log prepare errors
                echo json_encode(["status" => "error", "message" => "Prepare statement failed: " . $conn->error]);
            }
        }

        $conn->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
