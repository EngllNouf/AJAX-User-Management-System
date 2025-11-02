<?php
session_start(); // Start the session to access $_SESSION variables
header('Content-Type: application/json');

// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_management";

// Create a new database connection
$db = new mysqli($servername, $username, $password, $dbname);
$db->set_charset("utf8mb4");

// Check connection
if ($db->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Get the current logged-in user and environment info
$userEmail = $_SESSION['user_email'] ?? 'Unknown';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Receive data from AJAX request
$name = trim($_POST['name'] ?? '');
$age = intval($_POST['age'] ?? 0);
$email = trim($_POST['email'] ?? '');

// Validate input fields
if (!empty($name) && $age > 0 && !empty($email)) {
    // Insert new user into user_profiles table
    $stmt = $db->prepare("INSERT INTO user_profiles (name, age, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $name, $age, $email);

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;

        // Log the action in system_logs table
        $details = "Created user ID $userId: Name: $name, Age: $age, Email: $email";
        $action = 'create';
        $status = 'success';

        $logStmt = $db->prepare("
            INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->bind_param("ssssss", $userEmail, $action, $details, $status, $ipAddress, $userAgent);
        $logStmt->execute();
        $logStmt->close();

        // Send JSON response with user data
        echo json_encode([
            "success" => true,
            "message" => "User added successfully",
            "user" => [
                "id" => $userId,
                "name" => $name,
                "age" => $age,
                "email" => $email
            ]
        ]);
    } else {
        // Handle insert failure
        echo json_encode(["success" => false, "message" => "Database insert failed"]);
    }

    $stmt->close();
} else {
    // Handle missing input
    echo json_encode(["success" => false, "message" => "Please fill all fields"]);
}

// Close the database connection
$db->close();
?>
