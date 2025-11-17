<?php
include '../config/db_connect.php';

if (isset($_POST['course_code'])) {
    $course_code = $_POST['course_code'];

    // Query to fetch course names based on the selected course code
    $sql = "SELECT course_title FROM courses WHERE course_code = '$course_code'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row['course_title'] . "'>" . $row['course_title'] . "</option>";
        }
    } else {
        echo "<option value=''>No courses available</option>";
    }
}

$conn->close();
?>
