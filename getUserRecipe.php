<?php
session_start(); // Start session to access user data

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User not logged in']));
}

// Database connection settings
$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Prepare the SQL statement to fetch recipes added by the logged-in user
$sql = "SELECT recipeId, name, `desc`, ingredients, `procedure`, category, `img` FROM tbl_recipe WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // Bind the user ID
$stmt->execute();
$result = $stmt->get_result();

// Fetch recipes and store them in an array
$recipes = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['img'])) {
            // Base64 encode the image BLOB data
            $row['img'] = base64_encode($row['img']);
        } else {
            $row['img'] = null; // In case there is no image
        }
        $recipes[] = $row;
    }
}

// Output the recipes in JSON format
header('Content-Type: application/json');
echo json_encode($recipes);

// Close the connection
$stmt->close();
$conn->close();
?>
