<?php
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "users_management";

// Create database connection
$db = new mysqli($servername, $username, $password, $dbname);
$db->set_charset("utf8mb4");

// Check connection
if ($db->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Read filter parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$user = isset($_GET['user']) ? $db->real_escape_string($_GET['user']) : '';
$action = isset($_GET['action']) ? $db->real_escape_string($_GET['action']) : '';
$date = isset($_GET['date']) ? $db->real_escape_string($_GET['date']) : '';

$offset = ($page - 1) * $limit;

// Build dynamic WHERE clause
$where = [];
if ($user !== '') {
    $where[] = "user_email LIKE '%$user%'";
}
if ($action !== '') {
    $where[] = "action_type = '$action'";
}
if ($date !== '') {
    $where[] = "DATE(created_at) = '$date'";
}
$whereSQL = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Get total number of records for pagination
$totalResult = $db->query("SELECT COUNT(*) AS total FROM system_logs $whereSQL");
$total = $totalResult->fetch_assoc()['total'] ?? 0;
$totalPages = ceil($total / $limit);

// Fetch logs based on filters and pagination
$sql = "SELECT * FROM system_logs $whereSQL ORDER BY created_at DESC LIMIT $offset, $limit";
$result = $db->query($sql);

$logs = [];
$stats = [
    'login' => 0,
    'create' => 0,
    'update' => 0,
    'delete' => 0,
    'unknown' => 0
];

// Loop through log records
while ($row = $result->fetch_assoc()) {
    $actionType = $row['action_type'] ?: 'unknown';
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

    // Count statistics by action type
    if (isset($stats[$actionType])) {
        $stats[$actionType]++;
    } else {
        $stats['unknown']++;
    }
}

// Return response as JSON
echo json_encode([
    'success' => true,
    'logs' => $logs,
    'total' => $total,
    'totalPages' => $totalPages,
    'stats' => $stats
]);

// Close connection
$db->close();
?>
