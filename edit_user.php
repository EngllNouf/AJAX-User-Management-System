<?php
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['logged_in'])){
    echo json_encode(["success"=>false,"message"=>"User session not valid"]);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_demo";

$conn = new mysqli($servername,$username,$password,$dbname);
$conn->set_charset("utf8mb4");

$id = intval($_POST['id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$age = intval($_POST['age'] ?? 0);
$email = trim($_POST['email'] ?? '');
$userEmail = $_SESSION['user_email'];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

if($id<=0 || empty($name) || $age<=0 || empty($email)){
    echo json_encode(["success"=>false,"message"=>"Invalid input"]);
    exit;
}

// جلب البيانات القديمة
$oldStmt = $conn->prepare("SELECT name, age, email FROM user_profiles WHERE id=?");
$oldStmt->bind_param("i",$id);
$oldStmt->execute();
$oldData = $oldStmt->get_result()->fetch_assoc();
$oldStmt->close();

if(!$oldData){
    echo json_encode(["success"=>false,"message"=>"User not found"]);
    exit;
}

// التحقق من البريد
$checkStmt = $conn->prepare("SELECT id FROM user_profiles WHERE email=? AND id!=?");
$checkStmt->bind_param("si",$email,$id);
$checkStmt->execute();
$checkStmt->store_result();
if($checkStmt->num_rows>0){
    echo json_encode(["success"=>false,"message"=>"Email already exists"]);
    exit;
}
$checkStmt->close();

// التحديث
$stmt = $conn->prepare("UPDATE user_profiles SET name=?, age=?, email=? WHERE id=?");
$stmt->bind_param("sisi",$name,$age,$email,$id);

if($stmt->execute()){
    $details = "Updated user ID $id: from [Name: {$oldData['name']}, Age: {$oldData['age']}, Email: {$oldData['email']}] to [Name=$name, Age=$age, Email=$email]";
    $logStmt = $conn->prepare("INSERT INTO system_logs (user_email, action_type, details, status, ip_address, user_agent) VALUES (?,?,?,'success',?,?)");
    $action = 'update';
    $logStmt->bind_param("sssss",$userEmail,$action,$details,$ip,$agent);
    $logStmt->execute();
    $logStmt->close();

    echo json_encode(["success"=>true,"message"=>"User updated successfully","user"=>["id"=>$id,"name"=>$name,"age"=>$age,"email"=>$email]]);
}else{
    echo json_encode(["success"=>false,"message"=>"Update failed"]);
}

$stmt->close();
$conn->close();
?>
