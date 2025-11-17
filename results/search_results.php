<?php
session_start();

include('../includes/auth_guard.php');
include('../config/db_connect.php');

$filters = [];
$params = [];
$types = '';

if (!empty($_GET['course_code'])) {
    $filters[] = "course_code = ?";
    $params[] = trim($_GET['course_code']);
    $types .= 's';
}

if (!empty($_GET['course_title'])) {
    $filters[] = "course_title LIKE ?";
    $params[] = '%' . trim($_GET['course_title']) . '%';
    $types .= 's';
}

if (!empty($_GET['lecturer_name'])) {
    $filters[] = "lecturer_name LIKE ?";
    $params[] = '%' . trim($_GET['lecturer_name']) . '%';
    $types .= 's';
}

if (!empty($_GET['semester'])) {
    $filters[] = "semester = ?";
    $params[] = trim($_GET['semester']);
    $types .= 's';
}

if (!empty($_GET['session'])) {
    $filters[] = "session LIKE ?";
    $params[] = '%' . trim($_GET['session']) . '%';
    $types .= 's';
}

// Always exclude archived files
$filters[] = "is_archived = 0";

$sql = "SELECT * FROM uploads";
if (!empty($filters)) {
    $sql .= " WHERE " . implode(" AND ", $filters);
}

// If no filter other than is_archived, optionally block full table return:
if (count($filters) === 1) {
    // Comment out this section if you actually want to show all records by default
    $_SESSION['error'] = "Please enter at least one search filter.";
    header("Location: ./view_results.php");
    exit();
}

// Prepare the query
$stmt = $conn->prepare($sql);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();




if ($result && mysqli_num_rows($result) > 0) {
    echo '<div class="table-responsive">';
    echo '<table class="table table-bordered table-hover align-middle">';
    echo '<thead class="table-primary">
            <tr>
              <th>Course Code</th>
              <th>Title</th>
              <th>Lecturer</th>
              <th>Semester</th>
              <th>Session</th>
              <th>Download</th>';
    if ($_SESSION['role'] === 'hod') {
        echo '<th>Actions</th>';
    }
    echo '</tr></thead><tbody>';

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>{$row['course_code']}</td>
                <td>{$row['course_title']}</td>
                <td>{$row['lecturer_name']}</td>
                <td>{$row['semester']}</td>
                <td>{$row['session']}</td>
                <td>
                    <a class='btn btn-sm btn-success' href='download_file.php?id={$row['upload_id']}'>
                      <i class='bi bi-download'></i> Download
                    </a>
                </td>";

        if ($_SESSION['role'] === 'hod') {
            echo "<td>
                    <button class='btn btn-sm btn-warning' 
                        data-bs-toggle='modal' 
                        data-bs-target='#replaceModal'
                        data-upload-id='{$row['upload_id']}'
                        data-course-code='" . htmlspecialchars($row['course_code']) . "'
                        data-course-title='" . htmlspecialchars($row['course_title']) . "'
                        data-lecturer='" . htmlspecialchars($row['lecturer_name']) . "'
                        data-semester='" . htmlspecialchars($row['semester']) . "'
                        data-session='" . htmlspecialchars($row['session']) . "'
                        data-version='" . htmlspecialchars($row['version']) . "'
                        data-modified='" . htmlspecialchars($row['last_modified']) . "'
                        data-uploaded-by='" . htmlspecialchars($row['uploaded_by_name'] ?? 'Unknown') . "'>
                        <i class='bi bi-pencil-square'></i> Amend
                    </button>
                </td>";
        }

        echo "</tr>";
    }
    echo '</tbody></table>';
    echo '</div>';
} else {
    echo '<div class="alert alert-warning text-center">No records found for the selected criteria.</div>';
}

mysqli_close($conn);
?>
