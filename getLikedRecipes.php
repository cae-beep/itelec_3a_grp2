<?php
session_start();
header('Content-Type: application/json');

// Database credentials
$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;

$response = [];

try {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed.');
    }

    // Get the user ID from the session
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['error' => 'User not logged in.']);
        exit; // Stop further processing if the user is not logged in
    }

    // Fetch liked recipes for the user
    $query = "SELECT r.recipeId, r.name, r.desc, r.ingredients, r.procedure, r.img, r.category, r.likeCount 
              FROM tbl_likes l
              JOIN tbl_recipe r ON l.recipeId = r.recipeId
              WHERE l.user_Id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = [
                'recipeId' => $row['recipeId'],
                'name' => $row['name'],
                'desc' => $row['desc'],
                'ingredients' => $row['ingredients'],
                'procedure' => $row['procedure'],
                'img' => $row['img'],
                'category' => $row['category'],
                'likeCount' => $row['likeCount']
            ];
        }
    } else {
        echo json_encode(['error' => 'No liked recipes found.']);
        exit;
    }

    // Send the response back as JSON
    echo json_encode($response);

} catch (Exception $e) {
    // Log the error and send back the error message
    error_log($e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

// Close the database connection
$conn->close();
?>
