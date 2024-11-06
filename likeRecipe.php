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
    $action = $data['action'] ?? null;
    $recipeId = $data['recipeId'] ?? null;

    // Check if user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        throw new Exception('User not logged in.');
    }

    // Validate action and recipeId
    if (!$action || !$recipeId) {
        throw new Exception('Invalid parameters provided.');
    }

    // Prepare the logic for like and unlike actions
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

        // Insert a new like and increment likeCount
        $likeStmt = $conn->prepare("INSERT INTO tbl_likes (recipeId, user_Id, created_at) VALUES (?, ?, NOW())");
        $likeStmt->bind_param("ii", $recipeId, $userId);

        if ($likeStmt->execute()) {
            $updateStmt = $conn->prepare("UPDATE tbl_recipe SET likeCount = likeCount + 1 WHERE recipeId = ?");
            $updateStmt->bind_param("i", $recipeId);
            $updateStmt->execute();
            $updateStmt->close();

            $countStmt = $conn->prepare("SELECT likeCount FROM tbl_recipe WHERE recipeId = ?");
            $countStmt->bind_param("i", $recipeId);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $likeCount = $countResult->fetch_assoc()['likeCount'];

            $response = ['success' => true, 'message' => 'Recipe liked successfully!', 'likeCount' => $likeCount];
        } else {
            throw new Exception('Error executing like.');
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

        // Delete the like and decrement likeCount
        $unlikeStmt = $conn->prepare("DELETE FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        $unlikeStmt->bind_param("ii", $recipeId, $userId);

        if ($unlikeStmt->execute()) {
            $updateStmt = $conn->prepare("UPDATE tbl_recipe SET likeCount = GREATEST(likeCount - 1, 0) WHERE recipeId = ?");
            $updateStmt->bind_param("i", $recipeId);
            $updateStmt->execute();
            $updateStmt->close();

            $countStmt = $conn->prepare("SELECT likeCount FROM tbl_recipe WHERE recipeId = ?");
            $countStmt->bind_param("i", $recipeId);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $likeCount = $countResult->fetch_assoc()['likeCount'];

            $response = ['success' => true, 'message' => 'Recipe unliked successfully!', 'likeCount' => $likeCount];
        } else {
            throw new Exception('Error executing unlike.');
        }
        $unlikeStmt->close();

    } else {
        throw new Exception('Invalid action specified.');
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
$conn->close();
exit;
?>
