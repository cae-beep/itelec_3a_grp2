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
    // Establish a connection to the database
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Check if the user is logged in
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['error' => 'User not logged in']);
        exit;
    }

    // SQL query to fetch notifications with additional details for comments
    $sql = "
        SELECT n.notificationId, 
               r.name AS recipe_name, 
               u.user_username AS notifier_username, 
               n.created_at, 
               n.notification_status, 
               n.notification_type,
               (SELECT c.comment 
                FROM tbl_comments c 
                WHERE c.recipeId = n.recipeId 
                  AND c.user_Id = n.notifier_user_Id
                ORDER BY c.commentId DESC 
                LIMIT 1) AS comment_text
        FROM tbl_notifications n
        JOIN tbl_recipe r ON n.recipeId = r.recipeId
        LEFT JOIN tbl_users u ON n.notifier_user_Id = u.user_id
        WHERE n.user_Id = ?
        ORDER BY n.created_at DESC
    ";

    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement: ' . $conn->error);
    }

    // Bind the user ID parameter to the query
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute SQL statement: ' . $stmt->error);
    }

    // Get the result set from the query
    $result = $stmt->get_result();
    $notifications = [];

    // Process each row in the result set
    while ($row = $result->fetch_assoc()) {
        // Construct a message based on the notification type
        $message = $row['notification_type'] === 'like' 
            ? "{$row['notifier_username']} liked your recipe \"{$row['recipe_name']}\""
            : "{$row['notifier_username']} commented: \"{$row['comment_text']}\" on your recipe \"{$row['recipe_name']}\"";

        // Add the notification details to the response array
        $notifications[] = [
            'notificationId' => $row['notificationId'],
            'message' => $message,
            'created_at' => $row['created_at'],
            'notification_status' => $row['notification_status']
        ];
    }

    // Output the notifications as a JSON response
    echo json_encode($notifications);

} catch (Exception $e) {
    // Log any errors and return an error message in JSON format
    error_log($e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

// Close the statement and the database connection
$stmt->close();
$conn->close();
?>
