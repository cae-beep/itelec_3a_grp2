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
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Fetch the request data from the frontend
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? null;
    $recipeId = $data['recipeId'] ?? null;

    // Get the user ID from the session
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception('User not logged in.');
    }

    // Validate action and recipeId
    if (!$action || !$recipeId) {
        throw new Exception('Invalid parameters provided.');
    }

    // Log the incoming data
    error_log("User ID: $userId, Action: $action, Recipe ID: $recipeId");

    // Start a transaction to ensure consistency
    $conn->begin_transaction();

    if ($action === 'like') {
        // Check if the user has already liked this recipe
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        $checkStmt->bind_param("ii", $recipeId, $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $alreadyLiked = $result->fetch_assoc()['count'] > 0;
        $checkStmt->close();

        if ($alreadyLiked) {
            throw new Exception('Recipe already liked.');
        }

        // Insert a new like
        $likeStmt = $conn->prepare("INSERT INTO tbl_likes (recipeId, user_Id, created_at) VALUES (?, ?, NOW())");
        $likeStmt->bind_param("ii", $recipeId, $userId);
        if (!$likeStmt->execute()) {
            throw new Exception('Error inserting like: ' . $likeStmt->error);
        }
        $likeStmt->close();
    } elseif ($action === 'unlike') {
        // Check if the user has liked this recipe
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        $checkStmt->bind_param("ii", $recipeId, $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $alreadyLiked = $result->fetch_assoc()['count'] > 0;
        $checkStmt->close();

        if (!$alreadyLiked) {
            throw new Exception('Recipe not liked by this user.');
        }

        // Delete the like
        $unlikeStmt = $conn->prepare("DELETE FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        $unlikeStmt->bind_param("ii", $recipeId, $userId);
        if (!$unlikeStmt->execute()) {
            throw new Exception('Error deleting like: ' . $unlikeStmt->error);
        }
        $unlikeStmt->close();
    } else {
        throw new Exception('Invalid action specified.');
    }

    // Update the likeCount in the tbl_recipe table based on the actual count of likes
    $updateCountStmt = $conn->prepare("UPDATE tbl_recipe SET likeCount = (SELECT COUNT(*) FROM tbl_likes WHERE recipeId = ?) WHERE recipeId = ?");
    $updateCountStmt->bind_param("ii", $recipeId, $recipeId);
    if (!$updateCountStmt->execute()) {
        throw new Exception('Error updating like count: ' . $updateCountStmt->error);
    }
    $updateCountStmt->close();

    // Fetch the updated like count
    $countStmt = $conn->prepare("SELECT likeCount FROM tbl_recipe WHERE recipeId = ?");
    $countStmt->bind_param("i", $recipeId);
    $countStmt->execute();
    $result = $countStmt->get_result();
    $likeCount = $result->fetch_assoc()['likeCount'];
    $countStmt->close();

    // Commit the transaction
    $conn->commit();

    $response = [
        'success' => true,
        'message' => ucfirst($action) . ' action successful.',
        'likeCount' => $likeCount,
    ];
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    error_log($e->getMessage());
    $response['message'] = $e->getMessage();
} finally {
    // Return the response to the client
    echo json_encode($response);

    // Close the database connection
    $conn->close();
}
