<?php
session_start();
header('Content-Type: application/json');

// Ensure the user is logged in
if (!isset($_SESSION['logged_in'])) {
    echo json_encode(["success" => false, "message" => "User session not valid"]);
    exit;
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_management";

// Create a database connection
$db = new mysqli($servername, $username, $password, $dbname);
$db->set_charset("utf8mb4");

// Collect data from POST request
$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$age = intval($_POST['age'] ?? 0);
$email = trim($_POST['email'] ?? '');
$userEmail = $_SESSION['user_email'];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Validate input
if ($id <= 0 || empty($name) || $age <= 0 || empty($email)) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

// Fetch old user data before updating
$oldStmt = $db->prepare("SELECT name, age, email FROM user_profiles WHERE id = ?");
$oldStmt->bind_param("i", $id);
$oldStmt->execute();
$oldData = $oldStmt->get_result()->fetch_assoc();
$oldStmt->close();

if (!$oldData) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit;
}

// Check if the new email already exists for another user
$checkStmt = $db->prepare("SELECT id FROM user_profiles WHERE email = ? AND id != ?");
$checkStmt->bind_param("si", $email, $id);
$checkStmt->execute();
$checkStmt->store_result();
if ($checkStmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already exists"]);
    $checkStmt->close();
    exit;
}
$checkStmt->close();

// Update user record
$stmt = $db->prepare("UPDATE user_profiles SET name = ?, age = ?, email = ? WHERE id = ?");
$stmt->bind_param("sisi", $name, $age, $email, $id);

if ($stmt->execute()) {
    // Prepare log details
    $details = "Updated user ID $id: from [Name: {$oldData['name']}, Age: {$oldData['age']}, Email: {$oldData['email']}] to [Name: $name, Age: $age, Email: $email]";
    $action = 'update';

    // Insert log record into system_logs
    $logStmt = $db->prepare("
        INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
        VALUES (?, ?, ?, 'success', ?, ?)
    ");
    $logStmt->bind_param("sssss", $userEmail, $action, $details, $ip, $agent);
    $logStmt->execute();
    $logStmt->close();

    echo json_encode([
        "success" => true,
        "message" => "User updated successfully",
        "user" => [
            "id" => $id,
            "name" => $name,
            "age" => $age,
            "email" => $email
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}

// Close connection
$stmt->close();
$db->close();
?>
