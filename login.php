<?php
// Database connection
$servername = "localhost";
$username = "root";     // change if needed
$password = "";         // change if needed
$dbname = "login_demo";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

if (isset($_POST['username']) && isset($_POST['password'])) {
  $user = $_POST['username'];
  $pass = $_POST['password'];

  // Prepare statement to avoid SQL injection
  $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
  $stmt->bind_param("s", $user);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows === 0) {
    // User not found
    echo json_encode([
      "success" => false,
      "message" => "❌ User not found"
    ]);
  } else {
    $stmt->bind_result($hashed_password);
    $stmt->fetch();

    if (hash('sha256', $pass) === $hashed_password) {
      // ✅ Successful login
      echo json_encode([
        "success" => true,
        "message" => "✅ Welcome, $user!",
        "redirect" => "Users.html"
      ]);
    } else {
      // Wrong password
      echo json_encode([
        "success" => false,
        "message" => "❌ Wrong password"
      ]);
    }
  }

  $stmt->close();
} else {
  echo json_encode([
    "success" => false,
    "message" => "❌ Invalid request"
  ]);
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

// إرجاع النتيجة على شكل JSON
header('Content-Type: application/json');
echo json_encode($users);

$conn->close();
?>
