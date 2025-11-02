<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_management";

// Create database connection
$db = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($db->connect_error) {
  die(json_encode(["error" => "Database connection failed"]));
}

// Fetch users from the user_profiles table
$sql = "SELECT id, name, age, email FROM user_profiles ORDER BY id DESC";
$result = $db->query($sql);

$users = [];

// If there are users----store them in the array
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $users[] = $row;
  }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($users);

// Close connection
$db->close();
?>
