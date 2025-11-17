<?php
include('../includes/auth_guard.php');
include('../config/db_connect.php');

if (!isset($_GET['id'])) { die('Missing id'); }
$id = intval($_GET['id']);
$sql = "SELECT * FROM uploads WHERE upload_id = $id LIMIT 1";
$res = mysqli_query($conn, $sql);
if ($row = mysqli_fetch_assoc($res)) {
    $file = __DIR__ . "/" . $row['file_path'];
    if (file_exists($file)) {
        $user = intval($_SESSION['user_id']);
        $log = "INSERT INTO activity_log (user_id, action_type, upload_id, ip_address)
                VALUES ($user, 'DOWNLOAD', $id, '{$_SERVER['REMOTE_ADDR']}')";
        mysqli_query($conn, $log);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        readfile($file);
        exit;
    } else {
        echo 'File not found.';
    }
} else {
    echo 'Invalid record.';
}
?>