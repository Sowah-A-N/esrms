<?php
include('../includes/auth_guard.php');
include('../config/db_connect.php');

$search = '';
$results = null;
if (isset($_GET['q'])) {
    // Sanitize user input
    $search = mysqli_real_escape_string($conn, $_GET['q']);
    
    // Query the database
    $sql = "SELECT * FROM uploads WHERE course_code LIKE '%$search%' OR course_title LIKE '%$search%' OR lecturer_name LIKE '%$search%' OR session LIKE '%$search%'";
    $results = mysqli_query($conn, $sql);
    
    // Check if we have any results
    if ($results && mysqli_num_rows($results) > 0) {
        echo '<table class="table table-striped">';
        echo '<thead><tr><th>Course Code</th><th>Title</th><th>Lecturer</th><th>Semester</th><th>Session</th><th>Action</th></tr></thead>';
        echo '<tbody>';
        while ($row = mysqli_fetch_assoc($results)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['course_code']) . '</td>';
            echo '<td>' . htmlspecialchars($row['course_title']) . '</td>';
            echo '<td>' . htmlspecialchars($row['lecturer_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['semester']) . '</td>';
            echo '<td>' . htmlspecialchars($row['session']) . '</td>';
            echo '<td><a href="download_file.php?id=' . intval($row['upload_id']) . '" class="btn btn-primary">Download</a></td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No results found.</p>';
    }
} else {
    echo '<p>Start typing to search...</p>';
}

mysqli_close($conn);
?>
