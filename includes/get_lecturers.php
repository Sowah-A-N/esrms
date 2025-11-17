<?php
include '../config/db_connect.php';

// Query to get lecturer full names (f_name + ' ' + l_name)
$sql = "SELECT lecturer_id, CONCAT(lect_f_name, ' ', lect_l_name) AS full_name FROM lecturers";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop through all the rows and return options
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['full_name'] . "'>" . $row['full_name'] . "</option>";
    }
} else {
    echo "<option value=''>No lecturers found</option>";
}

$conn->close();
?>
