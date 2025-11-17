<?php
include('../includes/auth_guard.php');
include('../config/db_connect.php');

// Validate ID from query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "Invalid download request.";
    exit();
}

$upload_id = intval($_GET['id']);
$current_user_id = intval($_SESSION['user_id']);
$current_user_dept = mysqli_real_escape_string($conn, $_SESSION['department_code']);

// Fetch file metadata from database
$sql = "
    SELECT 
        file_name, 
        file_path, 
        file_type, 
        department_code, 
        is_archived 
    FROM uploads 
    WHERE upload_id = $upload_id 
    LIMIT 1
";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    http_response_code(404);
    echo "File not found.";
    exit();
}

$file = mysqli_fetch_assoc($result);

// Optional: Prevent download of archived files
if (!empty($file['is_archived'])) {
    http_response_code(403);
    echo "This file is archived and cannot be downloaded.";
    exit();
}

// Optional: Restrict to same department
// if ($file['department_code'] !== $current_user_dept) {
//     http_response_code(403);
//     echo "You are not authorized to download this file.";
//     exit();
// }

// Resolve safe file path
$relative_path = $file['file_path']; // e.g. uploads/files/1760538136_PCx.pdf
$raw_file_path = realpath(dirname(__DIR__) . '/' . $relative_path);
$uploads_dir = realpath(dirname(__DIR__) . '/uploads/files');

// echo "<pre>";
// echo "Relative path: " . htmlspecialchars($relative_path) . "\n";
// echo "Raw file path: " . htmlspecialchars($raw_file_path) . "\n";
// echo "Uploads dir: " . htmlspecialchars($uploads_dir) . "\n";
// echo "File exists (raw): " . (file_exists($raw_file_path) ? 'YES' : 'NO') . "\n";
// echo "</pre>";
// exit();


if (
    !$raw_file_path || 
    !$uploads_dir || 
    strpos($raw_file_path, $uploads_dir) !== 0 || 
    !file_exists($raw_file_path)
) {
    http_response_code(404);
    echo "File is missing or access is not permitted.";
    exit();
}

// Log the download activity
$ip_address = mysqli_real_escape_string($conn, $_SERVER['REMOTE_ADDR']);
$log_sql = "
    INSERT INTO activity_log (user_id, action_type, upload_id, ip_address)
    VALUES ($current_user_id, 'DOWNLOAD', $upload_id, '$ip_address')
";
mysqli_query($conn, $log_sql);

// Serve the file securely
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($raw_file_path));

readfile($raw_file_path);
exit();
?>
