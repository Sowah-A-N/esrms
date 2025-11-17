<?php
include('../includes/auth_guard.php');
include('../config/db_connect.php');

// Check if the request method is POST and a file is being uploaded
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Sanitize input data
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $course_title = mysqli_real_escape_string($conn, $_POST['course_title']);
    $lecturer = mysqli_real_escape_string($conn, $_POST['lecturer_name']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $session = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $dept = mysqli_real_escape_string($conn, $_SESSION['department_code']);
    $uploaded_by = intval($_SESSION['user_id']);

    if (!isset($_FILES['result_file'])) {
        echo "<p style='color:red;'>No file was uploaded.</p>";
        exit();
    }

    $file = $_FILES['result_file'];

    // Handle any file upload errors
    if ($file['error'] != UPLOAD_ERR_OK) {
        echo "<p style='color:red;'>File upload error: " . $file['error'] . "</p>";
        exit();
    }

    // Create uploads directory if not exists
    $target_dir = __DIR__ . "/files/";
    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            echo "<p style='color:red;'>Failed to create the directory for file uploads.</p>";
            exit();
        }
    }

    // Validate file type
    $file_name = basename($file['name']);
    $file_type = strtoupper(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed = array('PDF', 'XLS', 'XLSX');
    if (!in_array($file_type, $allowed)) {
        echo "<p style='color:red;'>Invalid file type. Only PDF, XLS, XLSX files are allowed.</p>";
        exit();
    }

    // 🔹 Auto-generate new filename
    $safe_course_code = preg_replace('/[^A-Za-z0-9_-]/', '', $course_code);
    $safe_semester = preg_replace('/[^A-Za-z0-9_-]/', '', $semester);
    $safe_session = preg_replace('/[^A-Za-z0-9_-]/', '', str_replace('/', '-', $session)); // convert 2024/2025 → 2024-2025

    $new_filename = "{$safe_course_code}_{$safe_semester}_{$safe_session}." . strtolower($file_type);
    $target_file = $target_dir . $new_filename;

    // Avoid overwriting existing files (append timestamp if exists)
    if (file_exists($target_file)) {
        $timestamp = time();
        $new_filename = "{$safe_course_code}_{$safe_semester}_{$safe_session}_{$timestamp}." . strtolower($file_type);
        $target_file = $target_dir . $new_filename;
    }

    // Attempt to move the uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        // Path for DB (relative)
        $file_path_db = 'uploads/files/' . $new_filename;

        // Insert record into DB
        $sql = "INSERT INTO uploads 
                (course_code, course_title, lecturer_name, department_code, semester, session, file_name, file_path, file_type, uploaded_by)
                VALUES 
                ('$course_code', '$course_title', '$lecturer', '$dept', '$semester', '$session', '$new_filename', '$file_path_db', '$file_type', $uploaded_by)";

        if (mysqli_query($conn, $sql)) {
            // Log upload
            $log = "INSERT INTO activity_log (user_id, action_type, upload_id, ip_address)
                    VALUES ($uploaded_by, 'UPLOAD', 0, '{$_SERVER['REMOTE_ADDR']}')";
            mysqli_query($conn, $log);

            $_SESSION['upload_success'] = true;
            header("Location: ../dashboard.php");
            exit();
        } else {
            echo "<p style='color:red;'>Database error: Could not insert file details.</p>";
        }
    } else {
        echo "<p style='color:red;'>Upload failed. Could not move the uploaded file.</p>";
    }
}
?>
