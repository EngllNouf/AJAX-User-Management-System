<?php
session_start(); // مهم جدًا لاستخدام $_SESSION
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_demo";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// المستخدم الحالي
$userEmail = $_SESSION['user_email'] ?? 'Unknown';
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// استلام البيانات من AJAX
$name = trim($_POST['name'] ?? '');
$age = intval($_POST['age'] ?? 0);
$email = trim($_POST['email'] ?? '');

if (!empty($name) && $age > 0 && !empty($email)) {
    $stmt = $conn->prepare("INSERT INTO user_profiles (name, age, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $name, $age, $email);

    if ($stmt->execute()) {
        $userId = $stmt->insert_id;

        // تسجيل العملية في system_logs
        $details = "Created user ID $userId: Name: $name, Age: $age, Email: $email";
        $action = 'create';
        $status = 'success';

        $logStmt = $conn->prepare("
            INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->bind_param("ssssss", $userEmail, $action, $details, $status, $ipAddress, $userAgent);
        $logStmt->execute();
        $logStmt->close();

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
        echo json_encode(["success" => false, "message" => "Database insert failed"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Please fill all fields"]);
}

$conn->close();
?>
