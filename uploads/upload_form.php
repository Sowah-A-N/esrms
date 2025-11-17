<?php
include('../includes/auth_guard.php');
include('../includes/header.php');
?>
<h2>Upload End-of-Semester Result (Form A)</h2>
<form method="POST" action="upload_handler.php" enctype="multipart/form-data">
    <label>Course Code:</label><input type="text" name="course_code" required><br>
    <label>Course Title:</label><input type="text" name="course_title" required><br>
    <label>Lecturer Name:</label><input type="text" name="lecturer_name" required><br>
    <label>Semester:</label>
    <select name="semester" required>
        <option value="First">First</option>
        <option value="Second">Second</option>
    </select><br>
    <label>Session:</label><input type="text" name="session" placeholder="2024/2025" required><br>
    <label>File:</label><input type="file" name="form_file" accept=".pdf,.xls,.xlsx" required><br>
    <button type="submit">Upload</button>
</form>
<?php include('../includes/footer.php'); ?>