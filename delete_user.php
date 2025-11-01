<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in']!==true) {
    echo json_encode(["success"=>false,"message"=>"User session not valid"]);
    exit;
}

$userEmail = $_SESSION['user_email'];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_demo";

$conn = new mysqli($servername,$username,$password,$dbname);
$conn->set_charset("utf8mb4");
if($conn->connect_error){die(json_encode(["success"=>false,"message"=>"DB connection failed"]));}

$id = intval($_POST['id'] ?? 0);
if($id<=0){echo json_encode(["success"=>false,"message"=>"Invalid ID"]); exit;}

// Get user before delete
$getStmt = $conn->prepare("SELECT name,email FROM user_profiles WHERE id=?");
$getStmt->bind_param("i",$id);
$getStmt->execute();
$res = $getStmt->get_result();
$userData = $res->fetch_assoc();
$getStmt->close();

if(!$userData){echo json_encode(["success"=>false,"message"=>"User not found"]); exit;}

$delStmt = $conn->prepare("DELETE FROM user_profiles WHERE id=?");
$delStmt->bind_param("i",$id);
if($delStmt->execute()){
    $details = "Deleted user ID $id: Name {$userData['name']}, Email {$userData['email']}";
    $action = 'delete';
    $status = 'success';

    $log = $conn->prepare("
        INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent)
        VALUES (?,?,?,?,?,?)
    ");
    $log->bind_param("ssssss",$userEmail,$action,$details,$status,$ip,$ua);
    $log->execute();
    $log->close();

    echo json_encode(["success"=>true,"message"=>"User deleted"]);
} else {
    echo json_encode(["success"=>false,"message"=>"Delete failed"]);
}
$delStmt->close();
$conn->close();
?>
