<?php
session_start();

$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the current user's ID
$userId = $_SESSION['user_id'] ?? null;

$sql = "
    SELECT 
        r.recipeId, 
        r.name, 
        r.`desc`, 
        r.ingredients, 
        r.`procedure`, 
        r.`category`, 
        r.`img`,
        COALESCE(l.likeCount, 0) AS likeCount,
        EXISTS(SELECT 1 FROM tbl_likes WHERE recipeId = r.recipeId AND user_Id = ?) AS userLiked
    FROM 
        tbl_recipe r
    LEFT JOIN 
        (SELECT recipeId, COUNT(*) AS likeCount FROM tbl_likes GROUP BY recipeId) l 
    ON 
        r.recipeId = l.recipeId
";

// Prepare statement and bind parameters to securely insert the user ID
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$recipes = [];
while ($row = $result->fetch_assoc()) {
    // Convert userLiked to a boolean
    $row['userLiked'] = (bool)$row['userLiked'];
    $recipes[] = $row;
}

// Output the result as JSON
header('Content-Type: application/json');
echo json_encode($recipes);

$stmt->close();
$conn->close();
?>
