<?php
// Connect to the MySQL database
$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4"; // Your MySQL password
$dbname = "schema_user";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get recipeId, user_Id, and comment from the request
if (isset($_POST['recipeId']) && isset($_POST['user_Id']) && isset($_POST['commentText'])) {
    $recipeId = $_POST['recipeId'];
    $userId = $_POST['user_Id'];
    $comment = $_POST['commentText'];

    // Prepare the SQL statement to insert the comment
    $stmt = $conn->prepare("INSERT INTO tbl_comments (recipeId, user_Id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $recipeId, $userId, $comment);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'commentId' => $stmt->insert_id]); // Return the ID of the new comment
    } else {
        echo json_encode(['status' => 'failed']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
}

// Close connection
$conn->close();
?>
