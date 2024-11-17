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

    // Get the current user's ID from the session
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(['error' => 'User not logged in']);
        exit;
    }

    // Fetch notifications for the user's recipes
    $sql = "
        SELECT n.notificationId, u.user_username AS liker_username, r.name AS recipe_name, 
               n.created_at, n.notification_status
        FROM tbl_notifications n
        JOIN tbl_recipe r ON n.recipeId = r.recipeId
        LEFT JOIN tbl_users u ON n.notifier_user_Id = u.user_id  -- Fetch notifier's username
        WHERE n.user_Id = ?
        ORDER BY n.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare SQL statement: ' . $conn->error);
    }

    // Bind the user ID to fetch notifications for the logged-in user
    $stmt->bind_param("i", $userId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute SQL statement: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $notifications = [];

    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'notificationId' => $row['notificationId'],
            'message' => "{$row['liker_username']} liked your recipe \"{$row['recipe_name']}\"",
            'created_at' => $row['created_at'],
            'notification_status' => $row['notification_status']
        ];
    }

    // Output notifications as JSON
    echo json_encode($notifications);

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
