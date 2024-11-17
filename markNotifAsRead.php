<?php
session_start();
header('Content-Type: application/json');

// Database credentials
$servername = "127.0.0.1";
$username = "root";
$password = "secretsecret4";
$dbname = "schema_user";
$port = 3306;

$response = ['success' => false, 'message' => 'An unknown error occurred.'];

try {
    // Create database connection
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    if ($conn->connect_error) {
        throw new Exception('Database connection failed.');
    }

    // Fetch the request data from the frontend
    $data = json_decode(file_get_contents("php://input"), true);
    $notificationId = $data['notificationId'] ?? null;

    if (!$notificationId) {
        echo json_encode(['success' => false, 'message' => 'Invalid notification ID.']);
        exit;
    }

    // Update the notification status to mark it as read
    $stmt = $conn->prepare("UPDATE tbl_notifications SET notification_status = 1 WHERE notificationId = ?");
    $stmt->bind_param("i", $notificationId);
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Notification marked as read.'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to mark notification as read.'];
    }

    // Output the response
    echo json_encode($response);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
