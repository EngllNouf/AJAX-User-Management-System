<?php
session_start(); // Start the session

header('Content-Type: application/json'); // Set response type to JSON

// Database connection info
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_management";

// Create connection
$db = new mysqli($servername, $username, $password, $dbname);
if ($db->connect_error) {
    
    die(json_encode(["success" => false, "message" => "Database connection failed: " . $db->connect_error]));
}

// Check if username and password are sent
if (isset($_POST['username']) && isset($_POST['password'])) {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Prepare SQL statement to verify user exists
    $stmt = $db->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // User not found
        echo json_encode(["success" => false, "message" => " User not found"]);
        exit;
    }

    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    // Check password
    if (hash('sha256', $pass) === $hashed_password) {
        // Successful login: save session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['user_email'] = $user;

        // Log the successful login in system_logs table
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $action = 'login';
        $status = 'success';
        $details = "User $user logged in successfully";

        $logStmt = $db->prepare("
            INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->bind_param("ssssss", $user, $action, $details, $status, $ipAddress, $userAgent);
        $logStmt->execute();
        $logStmt->close();

        // Return success JSON with redirect URL
        echo json_encode([
            "success" => true,
            "message" => " Welcome, $user!",
            "redirect" => "frontend/Users.html"
        ]);
    } else {
        // Failed login attempt: log it
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $action = 'login';
        $status = 'failed';
        $details = "Failed login attempt for user $user";

        $logStmt = $db->prepare("
            INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $logStmt->bind_param("ssssss", $user, $action, $details, $status, $ipAddress, $userAgent);
        $logStmt->execute();
        $logStmt->close();

        // Return JSON error for wrong password
        echo json_encode(["success" => false, "message" => " Wrong password"]);
    }

    $stmt->close();
} else {
    // Invalid request if username or password not sent
    echo json_encode(["success" => false, "message" => " Invalid request"]);
}

// Close connection
$db->close();
exit;
?>
