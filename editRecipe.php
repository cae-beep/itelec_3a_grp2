<?php
// Start the session to access session variables
session_start();

// Database connection settings
$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$database = "schema_user";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Ensure the user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Handle form data securely
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recipeId'], $_POST['recipeName'], $_POST['description'], $_POST['ingredients'], $_POST['procedure'], $_POST['category'])) {
    // Retrieve form data
    $recipeId = $_POST['recipeId'];
    $recipeName = $_POST['recipeName'];
    $description = $_POST['description'];
    $ingredients = $_POST['ingredients'];
    $procedure = $_POST['procedure'];
    $category = $_POST['category'];
    $imagePath = null;

 // Handle image upload if a new file was provided
if (isset($_FILES['imageInput']) && $_FILES['imageInput']['tmp_name']) {
    // Ensure the uploads directory exists
    $uploadsDir = 'uploads/';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0777, true); // Create the uploads directory if it doesn't exist
    }

    // Set the image path and move the uploaded file
    $imagePath = $uploadsDir . basename($_FILES['imageInput']['name']);
    if (!move_uploaded_file($_FILES['imageInput']['tmp_name'], $imagePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
        exit();
    }
}


    // Fetch the existing recipe to keep the old image if no new image is uploaded
    if (!$imagePath) {
        $stmt = $conn->prepare("SELECT img FROM tbl_recipe WHERE recipeId = ? AND user_id = ?");
        $stmt->bind_param("ii", $recipeId, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $imagePath = $row['img']; // Use the existing image if no new one was uploaded
        }
        $stmt->close();
    }

    // Prepare the SQL statement for updating the recipe
    $sql = "UPDATE tbl_recipe SET name = ?, `desc` = ?, ingredients = ?, `procedure` = ?, category = ?, img = ? WHERE recipeId = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", $recipeName, $description, $ingredients, $procedure, $category, $imagePath, $recipeId, $user_id);

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Recipe updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update recipe']);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
