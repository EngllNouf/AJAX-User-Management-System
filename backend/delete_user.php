<?php
session_start();
header('Content-Type: application/json');

// Ensure the user session is valid
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(["success" => false, "message" => "User session not valid"]);
    exit;
}

// Get session and environment details
$userEmail = $_SESSION['user_email'];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_management";

// Create database connection
$db = new mysqli($servername, $username, $password, $dbname);
$db->set_charset("utf8mb4");

// Check connection
if ($db->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// Get user ID from POST request
$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit;
}

// Fetch user details before deleting
$getStmt = $db->prepare("SELECT name, email FROM user_profiles WHERE id = ?");
$getStmt->bind_param("i", $id);
$getStmt->execute();
$res = $getStmt->get_result();
$userData = $res->fetch_assoc();
$getStmt->close();

if (!$userData) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

// Delete the user record
$delStmt = $db->prepare("DELETE FROM user_profiles WHERE id = ?");
$delStmt->bind_param("i", $id);

if ($delStmt->execute()) {
    // Log deletion in system_logs
    $details = "Deleted user ID $id: Name {$userData['name']}, Email {$userData['email']}";
    $action = 'delete';
    $status = 'success';

    $log = $db->prepare("
        INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $log->bind_param("ssssss", $userEmail, $action, $details, $status, $ip, $ua);
    $log->execute();
    $log->close();

    echo json_encode(["success" => true, "message" => "User deleted successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "User deletion failed"]);
}

// Close connection
$delStmt->close();
$db->close();
?>
