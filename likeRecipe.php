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
    $action = $data['action'] ?? '';

    // Check if user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception('User not logged in.');
    }

    if ($action === 'like') {
        if (!isset($data['recipeId'])) {
            throw new Exception('Recipe ID is required.');
        }

        $recipeId = $data['recipeId'];

        // Check if the user has already liked this recipe
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        $checkStmt->bind_param("ii", $recipeId, $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $alreadyLiked = $result->fetch_assoc()['count'] > 0;
        $checkStmt->close();

        if ($alreadyLiked) {
            // Unlike: Delete the existing like
            $deleteStmt = $conn->prepare("DELETE FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
            $deleteStmt->bind_param("ii", $recipeId, $userId);
            if ($deleteStmt->execute()) {
                // Decrement the likeCount in tbl_recipe
                $updateStmt = $conn->prepare("UPDATE tbl_recipe SET likeCount = likeCount - 1 WHERE recipeId = ?");
                $updateStmt->bind_param("i", $recipeId);
                $updateStmt->execute();
                $updateStmt->close();

                // Fetch the updated like count
                $countStmt = $conn->prepare("SELECT likeCount FROM tbl_recipe WHERE recipeId = ?");
                $countStmt->bind_param("i", $recipeId);
                $countStmt->execute();
                $countResult = $countStmt->get_result();
                $likeCount = $countResult->fetch_assoc()['likeCount'];

                $response = ['success' => true, 'message' => 'Recipe unliked successfully!', 'likeCount' => $likeCount];
            } else {
                throw new Exception('Error unliking recipe.');
            }
            $deleteStmt->close();
        } else {
            // Like: Insert a new like
            $stmt = $conn->prepare("INSERT INTO tbl_likes (recipeId, user_Id, created_at) VALUES (?, ?, NOW())");
            if (!$stmt) {
                throw new Exception('Database prepare failed.');
            }

            $stmt->bind_param("ii", $recipeId, $userId);

            if ($stmt->execute()) {
                // Increment the likeCount in tbl_recipe
                $updateStmt = $conn->prepare("UPDATE tbl_recipe SET likeCount = likeCount + 1 WHERE recipeId = ?");
                $updateStmt->bind_param("i", $recipeId);
                $updateStmt->execute();
                $updateStmt->close();

                // Fetch the updated like count
                $countStmt = $conn->prepare("SELECT likeCount FROM tbl_recipe WHERE recipeId = ?");
                $countStmt->bind_param("i", $recipeId);
                $countStmt->execute();
                $countResult = $countStmt->get_result();
                $likeCount = $countResult->fetch_assoc()['likeCount'];

                $response = ['success' => true, 'message' => 'Recipe liked successfully!', 'likeCount' => $likeCount];
            } else {
                throw new Exception('Error liking recipe.');
            }

            $stmt->close();
        }
    } else {
        throw new Exception('Invalid action.');
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
exit;
