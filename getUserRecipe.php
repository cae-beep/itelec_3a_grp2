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
        $response = ['error' => 'User not logged in'];
    } else {
        // Prepare the SQL statement to fetch recipes created by the logged-in user
        $query = "SELECT recipeId, name, `desc`, ingredients, `procedure`, category, img FROM tbl_recipe WHERE user_Id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare SQL statement: ' . $conn->error);
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Use the image path directly without converting to base64
                $row['img'] = !empty($row['img']) ? $row['img'] : 'default-image.png';

                // Append each recipe to the response
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
            $response = ['error' => 'No recipes found for the user'];
        }

        $stmt->close();
    }
} catch (Exception $e) {
    // Log the error and send back the error message
    error_log($e->getMessage());
    $response = ['error' => $e->getMessage()];
}

// Send the response back as JSON
echo json_encode($response);

// Close the database connection
$conn->close();
?>
