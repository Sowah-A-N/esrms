<?php
include('../includes/auth_guard.php');
include('../config/db_connect.php');

// Ensure an ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "<p style='color:red;'>Invalid download request.</p>";
    exit();
}

$file_id = intval($_GET['id']);
$user_id = intval($_SESSION['user_id']);
$dept = mysqli_real_escape_string($conn, $_SESSION['department_code']);

// Fetch file record from DB
$sql = "SELECT * FROM uploads WHERE upload_id = $file_id AND department_code = '$dept' LIMIT 1";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    http_response_code(404);
    echo "<p style='color:red;'>File not found or access denied.</p>";
    exit();
}

$file = mysqli_fetch_assoc($result);

// Construct absolute file path
$absolute_path = realpath(__DIR__ . '/../' . $file['file_path']);

// Security: Ensure path is valid and inside allowed directory
$base_dir = realpath(__DIR__ . '/../uploads/files/');
if (!$absolute_path || strpos($absolute_path, $base_dir) !== 0) {
    http_response_code(403);
    echo "<p style='color:red;'>Unauthorized file access attempt.</p>";
    exit();
}

if (!file_exists($absolute_path)) {
    http_response_code(404);
    echo "<p style='color:red;'>The requested file does not exist on the server.</p>";
    exit();
}

// Log download
$log = "INSERT INTO activity_log (user_id, action_type, upload_id, ip_address)
        VALUES ($user_id, 'DOWNLOAD', $file_id, '{$_SERVER['REMOTE_ADDR']}')";
mysqli_query($conn, $log);

// Send headers for file download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($absolute_path) . '"');
header('Content-Length: ' . filesize($absolute_path));
header('Pragma: public');
header('Cache-Control: must-revalidate');

// Clear output buffer
ob_clean();
flush();

// Read the file
readfile($absolute_path);
exit();
?>
