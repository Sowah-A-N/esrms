<?php
include('../includes/auth_guard.php');
include('../config/db_connect.php');

// Restrict access
if ($_SESSION['role'] !== 'hod') {
    http_response_code(403);
    die('Unauthorized access.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Sanitize and assign inputs ---
    $original_id = intval($_POST['original_upload_id']);
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $course_title = mysqli_real_escape_string($conn, $_POST['course_title']);
    $lecturer = mysqli_real_escape_string($conn, $_POST['lecturer_name']);
    $semester = mysqli_real_escape_string($conn, $_POST['semester']);
    $session = mysqli_real_escape_string($conn, $_POST['academic_year']);
    $dept = mysqli_real_escape_string($conn, $_SESSION['department_code']);
    $hod_id = intval($_SESSION['user_id']);
    $ip = $_SERVER['REMOTE_ADDR'];

    // --- Validate file upload ---
    if (!isset($_FILES['result_file']) || $_FILES['result_file']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Invalid or missing file upload.";
        header("Location: ./view_results.php?replace=error");
        exit();
    }

    $file = $_FILES['result_file'];
    $file_name = basename($file['name']);
    $file_type = strtoupper(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed = ['PDF', 'XLS', 'XLSX'];
    if (!in_array($file_type, $allowed)) {
        $_SESSION['error'] = "Invalid file type. Only PDF, XLS, and XLSX are allowed.";
        header("Location: ./view_results.php?replace=error");
        exit();
    }

    // --- Prepare target path ---
    $safe_name = preg_replace('/[^A-Za-z0-9_\.\-]/', '_', $file_name);
    $unique_name = time() . "_" . $safe_name;
    $target_dir = __DIR__ . "/files/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $target_file = $target_dir . $unique_name;
    $file_path_db = 'uploads/files/' . $unique_name;

    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        $_SESSION['error'] = "Error saving uploaded file.";
        header("Location: ./view_results.php?replace=error");
        exit();
    }

    // --- Fetch original upload record ---
    $result = mysqli_query($conn, "SELECT * FROM uploads WHERE upload_id = $original_id LIMIT 1");
    if (!$result || mysqli_num_rows($result) === 0) {
        $_SESSION['error'] = "Original upload not found.";
        header("Location: ./view_results.php?replace=error");
        exit();
    }

    $original = mysqli_fetch_assoc($result);

    // --- Determine parent/version info ---
    $parent_id = $original['parent_upload_id'] ?: $original['upload_id'];
    $new_version = intval($original['version']) + 1;

    // --- Archive the original record ---
    mysqli_query($conn, "UPDATE uploads SET is_archived = 1 WHERE upload_id = $original_id");

    // --- Insert new version ---
    $sql_new = "
        INSERT INTO uploads 
        (course_code, course_title, lecturer_name, department_code, semester, session,
         file_name, file_path, file_type, uploaded_by, parent_upload_id, version)
        VALUES (
            '$course_code', '$course_title', '$lecturer', '$dept', '$semester', '$session',
            '$file_name', '$file_path_db', '$file_type', $hod_id, $parent_id, $new_version
        )
    ";

    if (mysqli_query($conn, $sql_new)) {
        $new_id = mysqli_insert_id($conn);

        // --- Optional logging table: upload_replacements ---
        $has_replacements_table = mysqli_query($conn, "SHOW TABLES LIKE 'upload_replacements'");
        if ($has_replacements_table && mysqli_num_rows($has_replacements_table) > 0) {
            mysqli_query(
                $conn,
                "INSERT INTO upload_replacements (original_upload_id, new_upload_id, replaced_by)
                 VALUES ($original_id, $new_id, $hod_id)"
            );
        }

        // --- Activity logging ---
        $desc = sprintf(
            "Replaced upload ID %d with new version %d (new upload ID %d)",
            $original_id,
            $new_version,
            $new_id
        );
        mysqli_query($conn, "
            INSERT INTO activity_log (user_id, action_type, upload_id, ip_address, description)
            VALUES ($hod_id, 'REPLACE', $new_id, '$ip', '$desc')
        ");

        // --- Success redirect ---
        $_SESSION['success'] = "✅ File replaced successfully. Version $new_version is now active.";
        header("Location: ./view_results.php?replace=success");
        exit();
    } else {
        error_log("SQL Error: " . mysqli_error($conn));
        $_SESSION['error'] = "Database error: could not save replacement.";
        header("Location: ./view_results.php?replace=error");
        exit();
    }
}
?>
