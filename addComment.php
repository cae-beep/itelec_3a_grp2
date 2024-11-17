<?php
session_start();
header('Content-Type: application/json');


$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;


$response = ['success' => false, 'message' => 'An unknown error occurred.'];


try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed.');
    }


    $data = json_decode(file_get_contents("php://input"), true);
    $recipeId = $data['recipeId'] ?? null;
    $comment = $data['comment'] ?? null;


    // Check if user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception('User not logged in.');
    }


    // Validate parameters
    if (!$recipeId || !$comment) {
        throw new Exception('Recipe ID and comment are required.');
    }


    // Insert the comment into the database
    $stmt = $conn->prepare("INSERT INTO tbl_comments (recipeId, user_Id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $recipeId, $userId, $comment);


    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Comment added successfully!'];
    } else {
        throw new Exception('Error adding comment.');
    }


} catch (Exception $e) {
    error_log($e->getMessage());
    $response['message'] = $e->getMessage();
}


echo json_encode($response);
$conn->close();
exit;
?>
