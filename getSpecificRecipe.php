<?php
session_start();
header('Content-Type: application/json');

$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

$userId = $_SESSION['user_id'] ?? null;

if (isset($_GET['recipeId'])) {
    $recipeId = $_GET['recipeId'];

    $sql = "
        SELECT
            r.recipeId,
            r.name,
            r.desc,
            r.ingredients,
            r.procedure,
            r.category,
            r.img,
            COALESCE(l.likeCount, 0) AS likeCount,
            CASE WHEN ul.user_Id IS NOT NULL THEN TRUE ELSE FALSE END AS userLiked
        FROM
            tbl_recipe r
        LEFT JOIN
            (SELECT recipeId, COUNT(*) AS likeCount FROM tbl_likes GROUP BY recipeId) l
        ON
            r.recipeId = l.recipeId
        LEFT JOIN
            tbl_likes ul ON r.recipeId = ul.recipeId AND ul.user_Id = ?
        WHERE r.recipeId = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $recipeId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $row['userLiked'] = (bool)$row['userLiked'];
        echo json_encode(['success' => true, 'recipe' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Recipe not found.']);
    }
    $stmt->close();
}

$conn->close();
?>
