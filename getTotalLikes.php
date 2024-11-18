<?php
// Database connection
$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$database = "schema_user";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Assuming you have a logged-in user ID stored in session
session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Query to get total likes for all recipes posted by the logged-in user
$sql = "
    SELECT SUM(l.total_likes) AS total_user_likes
    FROM tbl_recipe r
    LEFT JOIN (
        SELECT recipeId, COUNT(*) AS total_likes
        FROM tbl_likes
        GROUP BY recipeId
    ) l ON r.recipeId = l.recipeId
    WHERE r.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['success' => true, 'total_likes' => $row['total_user_likes'] ?? 0]);
} else {
    echo json_encode(['success' => false, 'message' => 'No likes found']);
}

$stmt->close();
$conn->close();
?>
