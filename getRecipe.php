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
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }

    $recipeId = $_POST['recipeId'];
    $comment = $_POST['comment'];

    // Insert comment into the database
    $stmt = $conn->prepare("INSERT INTO tbl_comments (recipeId, comment, user_Id) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssi", $recipeId, $comment, $userId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comment added successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add comment: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
    }
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
        if ($checkStmt) {
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
                if ($stmt) {
                    $stmt->bind_param("ii", $recipeId, $userId);
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Recipe liked successfully.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to like recipe: ' . $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to prepare like statement: ' . $conn->error]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare check statement: ' . $conn->error]);
        }
    } elseif ($action === 'unlike') {
        // Check if the recipe is liked before attempting to unlike
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM tbl_likes WHERE recipeId = ? AND user_Id = ?");
        if ($checkStmt) {
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
                if ($stmt) {
                    $stmt->bind_param("ii", $recipeId, $userId);
                    if ($stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Recipe unliked successfully.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to unlike recipe: ' . $stmt->error]);
                    }
                    $stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to prepare unlike statement: ' . $conn->error]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare check statement: ' . $conn->error]);
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
            COUNT(l.recipeId) AS likeCount,
            u.user_username AS author
        FROM
            tbl_recipe r
        LEFT JOIN
            tbl_likes l ON r.recipeId = l.recipeId
        LEFT JOIN
            tbl_users u ON r.user_id = u.user_id
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
        if ($stmt) {
            $stmt->bind_param("s", $categoryFilter);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
            exit;
        }
    } else {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
            exit;
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $topRecipes = [];

    while ($row = $result->fetch_assoc()) {
        $row['author'] = htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8') ?? 'Unknown'; // Author name handling
        $topRecipes[$row['category']][] = $row;
    }

    echo json_encode($topRecipes);
    $stmt->close();
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
        u.user_username AS username,
        CASE WHEN EXISTS (SELECT 1 FROM tbl_likes ul WHERE ul.recipeId = r.recipeId AND ul.user_Id = ?) THEN TRUE ELSE FALSE END AS userLiked,
        (
            SELECT GROUP_CONCAT(CONCAT(u2.user_username, ': ', c.comment) SEPARATOR '||')
            FROM tbl_comments c
            LEFT JOIN tbl_users u2 ON c.user_Id = u2.user_id
            WHERE c.recipeId = r.recipeId
        ) AS comments
    FROM
        tbl_recipe r
    LEFT JOIN
        (SELECT recipeId, COUNT(*) AS likeCount FROM tbl_likes GROUP BY recipeId) l
    ON
        r.recipeId = l.recipeId
    LEFT JOIN
        tbl_users u ON r.user_id = u.user_id
";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $recipes = [];
    while ($row = $result->fetch_assoc()) {
        $row['name'] = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?? 'No title available';
        $row['desc'] = htmlspecialchars($row['desc'], ENT_QUOTES, 'UTF-8') ?? 'No description available';
        $row['ingredients'] = htmlspecialchars($row['ingredients'], ENT_QUOTES, 'UTF-8') ?? 'No ingredients listed';
        $row['procedure'] = htmlspecialchars($row['procedure'], ENT_QUOTES, 'UTF-8') ?? 'No procedure provided';
        $row['category'] = htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8') ?? 'Not categorized';
        $row['img'] = $row['img'] ?? 'default-image.png'; // Default image path
        $row['userLiked'] = (bool)($row['userLiked'] ?? false);
        $row['comments'] = $row['comments'] ? explode('||', htmlspecialchars($row['comments'], ENT_QUOTES, 'UTF-8')) : [];
        $row['username'] = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') ?? 'Unknown'; // Ensure username is included

        $recipes[] = $row;
    }

    echo json_encode($recipes);
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
}

$conn->close();

?>
