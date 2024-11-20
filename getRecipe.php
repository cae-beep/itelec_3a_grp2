<?php
session_start();
header('Content-Type: application/json');

$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Get the current user's ID
$userId = $_SESSION['user_id'] ?? null;

// Add a Comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipeId'], $_POST['comment'])) {
    $recipeId = $_POST['recipeId'];
    $comment = $_POST['comment'];

    // Insert comment into the database
    $stmt = $conn->prepare("INSERT INTO tbl_comments (recipeId, comment, user_Id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $recipeId, $comment, $userId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Comment added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add comment.']);
    }
    $stmt->close();
    exit;
}

// Like/Unlike a Recipe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['recipeId'])) {
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }

    $action = $_POST['action'];
    $recipeId = $_POST['recipeId'];

    if ($action === 'like') {
        // Check if already liked
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        $checkStmt->bind_param("ii", $recipeId, $userId);
        $checkStmt->execute();
        $checkStmt->bind_result($alreadyLiked);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($alreadyLiked) {
            echo json_encode(['success' => false, 'message' => 'Recipe already liked.']);
        } else {
            // Insert like
            $stmt = $conn->prepare("INSERT INTO tbl_likes (recipeId, user_Id) VALUES (?, ?)");
            $stmt->bind_param("ii", $recipeId, $userId);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Recipe liked successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to like recipe.']);
            }
            $stmt->close();
        }
    } elseif ($action === 'unlike') {
        // Check if the recipe is liked before attempting to unlike
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        $checkStmt->bind_param("ii", $recipeId, $userId);
        $checkStmt->execute();
        $checkStmt->bind_result($alreadyLiked);
        $checkStmt->fetch();
        $checkStmt->close();

        if (!$alreadyLiked) {
            echo json_encode(['success' => false, 'message' => 'Recipe not liked by this user.']);
        } else {
            // Remove like
            $stmt = $conn->prepare("DELETE FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
            $stmt->bind_param("ii", $recipeId, $userId);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Recipe unliked successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to unlike recipe.']);
            }
            $stmt->close();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
    exit;
}

// Fetch Top Recipes
if (isset($_GET['top'])) {
    $categoryFilter = $_GET['category'] ?? null;

    $sql = "
        SELECT
            r.category,
            r.recipeId,
            r.name,
            r.desc,
            r.ingredients,
            r.procedure,
            r.img,
            COUNT(l.recipeId) AS likeCount
        FROM
            tbl_recipe r
        LEFT JOIN
            tbl_likes l ON r.recipeId = l.recipeId
    ";

    if ($categoryFilter) {
        $sql .= " WHERE r.category = ?";
    }

    $sql .= "
        GROUP BY
            r.category, r.recipeId
        HAVING
            likeCount > 0
        ORDER BY
            r.category, likeCount DESC
    ";

    if ($categoryFilter) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $categoryFilter);
    } else {
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $topRecipes = [];

    while ($row = $result->fetch_assoc()) {
        $topRecipes[$row['category']][] = $row;
    }

    echo json_encode($topRecipes);
    exit;
}


// Fetch Regular Recipes 
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
        CASE WHEN ul.user_Id IS NOT NULL THEN TRUE ELSE FALSE END AS userLiked,
        (
            SELECT GROUP_CONCAT(CONCAT(u.user_username, ': ', c.comment) SEPARATOR '||')
            FROM tbl_comments c
            LEFT JOIN tbl_users u ON c.user_Id = u.user_id
            WHERE c.recipeId = r.recipeId
        ) AS comments
    FROM
        tbl_recipe r
    LEFT JOIN
        (SELECT recipeId, COUNT(*) AS likeCount FROM tbl_likes GROUP BY recipeId) l
    ON
        r.recipeId = l.recipeId
    LEFT JOIN
        tbl_likes ul ON r.recipeId = ul.recipeId AND ul.user_Id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$recipes = [];
while ($row = $result->fetch_assoc()) {
    $row['userLiked'] = (bool)$row['userLiked'];
    $row['comments'] = $row['comments'] ? explode('||', $row['comments']) : [];
    $recipes[] = $row;
}

echo json_encode($recipes);

$stmt->close();
$conn->close();

?>
