<?php
include('../includes/auth_guard.php');
include('../config/db_connect.php');

header('Content-Type: application/json');

if (!in_array($_SESSION['role'], ['admin', 'secretary'])) {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$id = intval($_POST['course_id'] ?? 0);

if ($action === 'delete') {
    $del = mysqli_query($conn, "DELETE FROM courses WHERE course_id=$id");
    echo json_encode(['success'=>$del, 'message'=>$del?'Course deleted successfully.':'Failed to delete.']);
}

elseif ($action === 'edit') {
    $code = strtoupper(mysqli_real_escape_string($conn, $_POST['course_code']));
    $title = mysqli_real_escape_string($conn, $_POST['course_title']);
    $unit = intval($_POST['credit_unit']);
    $sem = mysqli_real_escape_string($conn, $_POST['semester']);

    $sql = "UPDATE courses SET course_code='$code', course_title='$title', credit_unit=$unit, semester='$sem' WHERE course_id=$id";
    $upd = mysqli_query($conn, $sql);
    echo json_encode(['success'=>$upd, 'message'=>$upd?'Course updated successfully.':'Failed to update.']);
}
else {
    echo json_encode(['success'=>false, 'message'=>'Invalid action.']);
}
?>
