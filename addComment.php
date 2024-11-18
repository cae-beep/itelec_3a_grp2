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
    // Establish a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed.');
    }

    // Get the data from the request body
    $data = json_decode(file_get_contents("php://input"), true);
    $recipeId = $data['recipeId'] ?? null;
    $comment = $data['comment'] ?? null;

    // Check if the user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception('User not logged in.');
    }

    // Validate parameters
    if (!$recipeId || !$comment) {
        throw new Exception('Recipe ID and comment are required.');
    }

    // Insert the comment into the tbl_comments table
    $stmt = $conn->prepare("INSERT INTO tbl_comments (recipeId, user_Id, comment) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception('Failed to prepare comment SQL statement.');
    }
    $stmt->bind_param("iis", $recipeId, $userId, $comment);

    if ($stmt->execute()) {
        // Fetch the owner of the recipe to add a notification
        $ownerSql = "SELECT user_Id FROM tbl_recipe WHERE recipeId = ?";
        $ownerStmt = $conn->prepare($ownerSql);
        if (!$ownerStmt) {
            throw new Exception('Failed to prepare SQL statement for recipe owner.');
        }
        $ownerStmt->bind_param("i", $recipeId);
        if (!$ownerStmt->execute()) {
            throw new Exception('Failed to execute SQL statement for recipe owner.');
        }
        $ownerResult = $ownerStmt->get_result();
        $recipeOwner = $ownerResult->fetch_assoc()['user_Id'];

        // Insert a notification into tbl_notifications if the recipe owner is different from the commenter
        if ($recipeOwner != $userId) {
            $notificationSql = "INSERT INTO tbl_notifications (recipeId, user_Id, notifier_user_Id, notification_type) VALUES (?, ?, ?, 'comment')";
            $notificationStmt = $conn->prepare($notificationSql);
            if (!$notificationStmt) {
                throw new Exception('Failed to prepare SQL statement for notification.');
            }
            $notificationStmt->bind_param("iii", $recipeId, $recipeOwner, $userId);
            if (!$notificationStmt->execute()) {
                throw new Exception('Failed to execute SQL statement for notification.');
            }
        }

        $response = ['success' => true, 'message' => 'Comment added and notification created successfully!'];
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
