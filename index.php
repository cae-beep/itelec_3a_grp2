<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Database connection parameters
$servername = "127.0.0.1";
$db_username = "root"; // MySQL username
$db_password = "secretsecret4"; // MySQL password
$dbname = "schema_user"; // Your database name
$port = 3306;

// Create a connection to the database
$conn = new mysqli($servername, $db_username, $db_password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Login functionality
if ($action == "login") {
    $email = $conn->real_escape_string($_POST['email']);
    $raw_password = trim($_POST['password']);

    // Prepare and execute the SQL statement to get the user password, username, and user ID
    $sql = "SELECT user_username, user_password, user_id FROM tbl_users WHERE user_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_password = $row['user_password'];

        // Verify the hashed password
        if (password_verify($raw_password, $stored_password)) {
            // Store both user ID, email, and username in session
            $_SESSION['user_id'] = $row['user_id']; // Store the user ID
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $row['user_username']; // Store username for later use
            header("Location: http://127.0.0.1/home.html");
            exit();
        } else {
            echo "Invalid email or password.";
        }
    } else {
        echo "No user found with this email.";
    }

    $stmt->close();
}
   
    // Signup functionality
    elseif ($action == "signup") {
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $raw_password = trim($_POST['password']);
       
        // Hash the password
        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

        // Check if username or email already exists
        $checkSql = "SELECT * FROM tbl_users WHERE user_username = ? OR user_email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        // If username or email exists, inform the user
        if ($checkResult->num_rows > 0) {
            echo "Username or email already exists. <a href='index.html'>Try again.</a>";
        } else {
            // Insert new user into the database
            $sql = "INSERT INTO tbl_users (user_username, user_email, user_password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($stmt->execute()) {
                // Store the username and email in session
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                // Redirect to the home page after signup
                header("Location: http://127.0.0.1/index.html");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }

        $checkStmt->close();
    }
}

// Close the database connection
$conn->close();
?>
