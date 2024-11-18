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
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Get the user ID from the session
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['error' => 'User not logged in']);
        exit; // Stop further processing if the user is not logged in
    }

    // Prepare the SQL statement to fetch recipes created by the logged-in user
    $query = "SELECT recipeId, name, `desc`, ingredients, `procedure`, category, img
              FROM tbl_recipe
            WHERE user_Id = ?";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement: ' . $conn->error);
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Convert binary image data to base64
            if (!empty($row['img'])) {
                $imageData = base64_encode($row['img']);
                $row['img'] = "data:image/jpeg;base64,{$imageData}";
                // Uncomment the following line to debug image data
                // error_log("Image Data for Recipe ID {$row['recipeId']}: " . $row['img']);
            } else {
                $row['img'] = null; // Set to null if no image is available
            }

            $response[] = [
                'recipeId' => $row['recipeId'],
                'name' => $row['name'],
                'desc' => $row['desc'],
                'ingredients' => $row['ingredients'],
                'procedure' => $row['procedure'],
                'img' => $row['img'],
                'category' => $row['category']
            ];
        }
    } else {
        echo json_encode(['error' => 'No recipes found for the user']);
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
