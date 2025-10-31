<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_demo";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die(json_encode(["error" => "Database connection failed"]));
}

// جلب المستخدمين من جدول user_profiles
$sql = "SELECT id, name, age, email FROM user_profiles ORDER BY id DESC";
$result = $conn->query($sql);

$users = [];

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
}

header('Content-Type: application/json');
echo json_encode($users);

$conn->close();
?>
