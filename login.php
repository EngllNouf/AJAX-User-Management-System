<?php
session_start();
header('Content-Type: application/json');

// اتصال قاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_demo";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode(["success"=>false, "message"=>"Database connection failed"]));
}

if (!isset($_POST['username'], $_POST['password'])) {
    echo json_encode(["success"=>false, "message"=>"❌ Invalid request"]);
    exit;
}

$user = trim($_POST['username']);
$pass = trim($_POST['password']);

$stmt = $conn->prepare("SELECT email, password FROM users WHERE username=?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    // استخدام username كسجل عند الفشل
    $userForLog = $user;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $action = 'login';
    $status = 'failed';
    $details = "Failed login attempt for user $user";

    $logStmt = $conn->prepare("
        INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $logStmt->bind_param("ssssss", $userForLog, $action, $details, $status, $ipAddress, $userAgent);
    $logStmt->execute();
    $logStmt->close();

    echo json_encode(["success"=>false, "message"=>"❌ User not found"]);
    exit;
}

$stmt->bind_result($email, $hashed_password);
$stmt->fetch();

$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$userForLog = $email ?: $user; // دائمًا نستخدم البريد أو username

if (hash('sha256', $pass) === $hashed_password) {
    $_SESSION['logged_in'] = true;
    $_SESSION['user_email'] = $email;

    $action = 'login';
    $status = 'success';
    $details = "User $user logged in successfully";

    $logStmt = $conn->prepare("
        INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $logStmt->bind_param("ssssss", $userForLog, $action, $details, $status, $ipAddress, $userAgent);
    $logStmt->execute();
    $logStmt->close();

    echo json_encode(["success"=>true, "message"=>"✅ Welcome, $user!", "redirect"=>"Users.html"]);
} else {
    $action = 'login';
    $status = 'failed';
    $details = "Wrong password attempt for $user";

    $logStmt = $conn->prepare("
        INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $logStmt->bind_param("ssssss", $userForLog, $action, $details, $status, $ipAddress, $userAgent);
    $logStmt->execute();
    $logStmt->close();

    echo json_encode(["success"=>false, "message"=>"❌ Wrong password"]);
}

$stmt->close();
$conn->close();
