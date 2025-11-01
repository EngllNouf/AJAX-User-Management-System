<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login_demo";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Read filters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$user = isset($_GET['user']) ? $conn->real_escape_string($_GET['user']) : '';
$action = isset($_GET['action']) ? $conn->real_escape_string($_GET['action']) : '';
$date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';

$offset = ($page - 1) * $limit;

// Build WHERE clause
$where = [];
if ($user !== '') {
    $where[] = "user_email LIKE '%$user%'";
}
if ($action !== '') {
    $where[] = "action_type='$action'";
}
if ($date !== '') {
    $where[] = "DATE(created_at)='$date'";
}
$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Get total records
$totalResult = $conn->query("SELECT COUNT(*) as total FROM system_logs $whereSQL");
$total = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

// Fetch logs
$sql = "SELECT * FROM system_logs $whereSQL ORDER BY created_at DESC LIMIT $offset, $limit";
$result = $conn->query($sql);

$logs = [];
$stats = ['login' => 0, 'create' => 0, 'update' => 0, 'delete' => 0, 'unknown' => 0];

while ($row = $result->fetch_assoc()) {
    $actionType = $row['action_type'] ?: 'unknown'; // تعويض القيم الفارغة
    $logs[] = [
        'id' => $row['id'],
        'user' => $row['user_email'],
        'action' => $actionType,
        'details' => $row['details'],
        'status' => $row['status'],
        'ip' => $row['ip_address'],
        'user_agent' => $row['user_agent'],
        'timestamp' => $row['created_at']
    ];

    // عد الإحصائيات
    if (isset($stats[$actionType])) {
        $stats[$actionType]++;
    } else {
        $stats['unknown']++;
    }
}

echo json_encode([
    'success' => true,
    'logs' => $logs,
    'total' => $total,
    'totalPages' => $totalPages,
    'stats' => $stats
]);
?>
