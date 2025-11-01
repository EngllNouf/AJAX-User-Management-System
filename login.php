<?php
session_start(); // بدء الجلسة

header('Content-Type: application/json');

// معلومات الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_demo";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]));
}

// التحقق من إرسال البيانات
if (isset($_POST['username']) && isset($_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // استعلام للتحقق من المستخدم
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "❌ User not found"]);
        exit;
    }

    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if (hash('sha256', $pass) === $hashed_password) {
        // تسجيل الدخول في الجلسة
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $user;

        // تسجيل الدخول في جدول system_logs
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $action = 'login';
        $status = 'success';
        $details = "User $user logged in successfully";

        $logStmt = $conn->prepare("
            INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->bind_param("ssssss", $user, $action, $details, $status, $ipAddress, $userAgent);
        $logStmt->execute();
        $logStmt->close();

        echo json_encode([
            "success" => true,
            "message" => "✅ Welcome, $user!",
            "redirect" => "Users.html"
        ]);
    } else {
        // تسجيل فشل محاولة تسجيل الدخول
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $action = 'login';
        $status = 'failed';
        $details = "Failed login attempt for user $user";

        $logStmt = $conn->prepare("
            INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->bind_param("ssssss", $user, $action, $details, $status, $ipAddress, $userAgent);
        $logStmt->execute();
        $logStmt->close();

        echo json_encode(["success" => false, "message" => "❌ Wrong password"]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "❌ Invalid request"]);
}

$conn->close();
exit;
?>
