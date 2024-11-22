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
        throw new Exception('Database connection failed.');
    }

    // Fetch the request data from the frontend
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? null;
    $recipeId = $data['recipeId'] ?? null;

    // Get the user ID from the session
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }

    // Validate action and recipeId
    if (!$action || !$recipeId) {
        throw new Exception('Invalid parameters provided.');
    }

    // Handle the like/unlike action
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
        if ($likeStmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $likeStmt->bind_param("ii", $recipeId, $userId);
        if ($likeStmt->execute()) {
            // Update the like count in the tbl_recipe table
            $updateStmt = $conn->prepare("UPDATE tbl_recipe SET likeCount = likeCount + 1 WHERE recipeId = ?");
            if ($updateStmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $updateStmt->bind_param("i", $recipeId);
            $updateStmt->execute();
            $updateStmt->close();

            // Fetch the updated like count
            $countStmt = $conn->prepare("SELECT likeCount FROM tbl_recipe WHERE recipeId = ?");
            $countStmt->bind_param("i", $recipeId);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $likeCount = $countResult->fetch_assoc()['likeCount'];
            $countStmt->close();

            $response = ['success' => true, 'message' => 'Recipe liked successfully!', 'likeCount' => $likeCount];
        } else {
            throw new Exception('Error executing like: ' . $likeStmt->error);
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
        if ($unlikeStmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $unlikeStmt->bind_param("ii", $recipeId, $userId);
        if ($unlikeStmt->execute()) {
            // Decrease the like count in the tbl_recipe table
            $updateStmt = $conn->prepare("UPDATE tbl_recipe SET likeCount = GREATEST(likeCount - 1, 0) WHERE recipeId = ?");
            if ($updateStmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $updateStmt->bind_param("i", $recipeId);
            $updateStmt->execute();
            $updateStmt->close();

            // Fetch the updated like count
            $countStmt = $conn->prepare("SELECT likeCount FROM tbl_recipe WHERE recipeId = ?");
            $countStmt->bind_param("i", $recipeId);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $likeCount = $countResult->fetch_assoc()['likeCount'];
            $countStmt->close();

            $response = ['success' => true, 'message' => 'Recipe unliked successfully!', 'likeCount' => $likeCount];
        } else {
            throw new Exception('Error executing unlike: ' . $unlikeStmt->error);
        }
        $unlikeStmt->close();
    } else {
        throw new Exception('Invalid action specified.');
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    $response['message'] = $e->getMessage();
}

// Return the response to the client
echo json_encode($response);

// Close the database connection
$conn->close();
