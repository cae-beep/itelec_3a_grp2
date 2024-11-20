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

// Check if the recipe ID is provided
$recipeId = $_GET['recipeId'] ?? null;

if (!$recipeId) {
    echo json_encode(['success' => false, 'message' => 'Recipe ID not provided']);
    exit();
}

// Delete the recipe with the provided ID
$sql = "DELETE FROM tbl_recipe WHERE recipeId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recipeId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Recipe deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Recipe not found or could not be deleted']);
}

$stmt->close();
$conn->close();
?>
