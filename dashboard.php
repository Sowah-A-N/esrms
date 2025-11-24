<?php
session_start();
include('includes/auth_guard.php');
include('config/config.php');
include('config/db_connect.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ESRMS Dashboard</title>
  <!-- Bootstrap 5 and Icons (assumed CDN already included) -->
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<?php 
  print_r($_SESSION);
?>

<?php include './includes/navbar.php' ?>

<div class="offcanvas offcanvas-start bg-light" tabindex="-1" id="sidebar">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="list-group list-group-flush">
      <a href="<?php echo BASE_URL; ?>dashboard/dashboard.php" class="list-group-item list-group-item-action active">
        <i class="bi bi-speedometer2 me-2"></i> Dashboard
      </a>
      <a href="<?php echo BASE_URL; ?>uploads/upload_form.php" class="list-group-item list-group-item-action">
        <i class="bi bi-upload me-2"></i> Upload Form A
      </a>

      
        
      <a href="<?php echo BASE_URL; ?>results/view_results.php" class="list-group-item list-group-item-action">
        <i class="bi bi-table me-2"></i> View Results
      </a>
      <a href="<?php echo BASE_URL; ?>activity/logs.php" class="list-group-item list-group-item-action">
        <i class="bi bi-clock-history me-2"></i> Activity Log
      </a>
    </ul>
  </div>
</div>

<div class="container-fluid mt-4">
  
  <?php     
        if (!empty($_SESSION['upload_success'])) {
            echo '<div class="alert alert-success" role="alert">
                    File uploaded successfully.
                </div>';
            unset($_SESSION['upload_success']);
        }
  ?>
  <div class="row">

    <?php if($_SESSION['role'] == "secretary" || "hod"): ?>
        <div class="col-lg-3 col-md-6 mb-3">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body">
            <i class="bi bi-upload fs-1 text-primary"></i>
            <h5 class="card-title mt-2">Upload Form A</h5>
            <p class="text-muted small">Add new course results</p>
            <a data-bs-toggle="modal" data-bs-target="#uploadModal" class="btn btn-primary btn-sm">Upload</a>
            </div>
        </div>
        </div>
    <?php endif; ?>

    <!-- <div class="d-flex justify-content-center gap-3">
        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bi bi-upload me-2"></i> Upload Form A
        </button>
      </div> -->

    <div class="col-lg-3 col-md-6 mb-3">
      <div class="card text-center shadow-sm border-0">
        <div class="card-body">
          <i class="bi bi-table fs-1 text-success"></i>
          <h5 class="card-title mt-2">View Results</h5>
          <p class="text-muted small">Browse or download existing results</p>
          <a href="<?php echo BASE_URL; ?>results/view_results.php" class="btn btn-success btn-sm">View</a>
        </div>
      </div>
    </div>

    <?php if($_SESSION['role'] == "admin"): ?>
        <!-- <div class="col-lg-3 col-md-6 mb-3">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body">
            <i class="bi bi-person-gear fs-1 text-warning"></i>
            <h5 class="card-title mt-2">Manage Users</h5>
            <p class="text-muted small">Admins can manage users & access</p>
            <a href="<?php echo BASE_URL; ?>users/manage_users.php" class="btn btn-warning btn-sm text-white">Manage</a>
            </div>
        </div>
        </div> -->

        <div class="col-lg-3 col-md-6 mb-3">
        <div class="card text-center shadow-sm border-0">
            <div class="card-body">
            <i class="bi bi-clock-history fs-1 text-danger"></i>
            <h5 class="card-title mt-2">Activity Logs</h5>
            <p class="text-muted small">Track system activity</p>
            <a href="<?php echo BASE_URL; ?>activity/logs.php" class="btn btn-danger btn-sm">View</a>
            </div>
        </div>
        </div>
    <?php endif; ?>
  </div>
</div>

<!-- 🔹 Modal: Upload Form A -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?php echo BASE_URL; ?>uploads/upload_handler.php" method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="uploadModalLabel">
            <i class="bi bi-upload me-2"></i>Upload End-of-Semester Result (Form A)
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!--Upload Results File Modal-->
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">

              <label for="course_code" class="form-label">Course Code</label>
              <!-- <input type="text" class="form-control" name="course_code" id="course_code" required> -->
               <select class="form-control" id="course_code" name="course_code">
                    <option value="">Select a Course Code</option>
                    <?php                       
                        $sql = "SELECT course_code FROM courses"; // Adjust this query as needed
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row['course_code'] . "'>" . $row['course_code'] . "</option>";
                            }
                        }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
              <label for="course_title" class="form-label">Course Title</label>
              <!-- <input type="text" class="form-control" name="course_title" id="course_title" required> -->
              <select class="form-control" id="course_title" name="course_title">
                   <option value="">Select a Course Name</option>
               </select>
            </div>
            <div class="col-md-6">
              <label for="semester" class="form-label">Semester</label>
              <select class="form-select" name="semester" id="semester" required>
                <option value="">Choose...</option>
                <option value="First">First Semester</option>
                <option value="Second">Second Semester</option>
              </select>
            </div>
            <div class="col-md-6">
                <label for="academic_year" class="form-label">Academic Year</label>
                <select class="form-select" name="academic_year" id="academic_year" required>
                    <option value="">Select Academic Year</option>

                    <?php
                        $currentYear = date("Y");
                        $startYear = $currentYear - 3;
                        $endYear = $currentYear + 3;

                        $currentAcademic = $currentYear . "/" . ($currentYear + 1);

                        for ($year = $startYear; $year <= $endYear; $year++) {
                            $value = $year . "/" . ($year + 1);
                            $selected = ($value === $currentAcademic) ? "selected" : "";
                            echo "<option value=\"$value\" $selected>$value</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="col-md-12">
              <label for="lecturer_name" class="form-label">Lecturer Name</label>
              <!-- <input type="text" class="form-control" name="lecturer_name" id="lecturer_name" required> -->
              <select  class="form-control" id="lecturer_name" name="lecturer_name">
                    <option value="">Select a Lecturer</option>
                </select>
            </div>
            <div class="col-md-12">
              <label for="file" class="form-label">Upload Result File (PDF or Excel)</label>
              <input type="file" class="form-control" name="result_file" id="file" accept=".pdf,.xls,.xlsx" required>
              <div class="form-text">Allowed formats: PDF, XLS, XLSX (max 5MB)</div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-cloud-arrow-up me-1"></i> Upload
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


        <!-- Replace Uploaded File Modal -->
        <div class="modal fade" id="replaceModal" tabindex="-1" aria-labelledby="replaceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                <form id="replaceForm" method="POST" enctype="multipart/form-data" action="replace_upload.php">
                    <div class="modal-header">
                    <h5 class="modal-title" id="replaceModalLabel">
                        <i class="bi bi-pencil-square me-2"></i> Replace Upload
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                    <input type="hidden" name="original_upload_id" id="original_upload_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                        <label class="form-label">Course Code</label>
                        <input type="text" class="form-control" name="course_code" id="replace_course_code" readonly>
                        </div>
                        <div class="col-md-6">
                        <label class="form-label">Course Title</label>
                        <input type="text" class="form-control" name="course_title" id="replace_course_title" readonly>
                        </div>
                        <div class="col-md-6">
                        <label class="form-label">Lecturer</label>
                        <input type="text" class="form-control" name="lecturer_name" id="replace_lecturer" readonly>
                        </div>
                        <div class="col-md-6">
                        <label class="form-label">Semester</label>
                        <input type="text" class="form-control" name="semester" id="replace_semester" readonly>
                        </div>
                        <div class="col-md-6">
                        <label class="form-label">Session</label>
                        <input type="text" class="form-control" name="academic_year" id="replace_session" readonly>
                        </div>
                        <div class="col-md-12">
                        <label class="form-label">New Result File</label>
                        <input type="file" class="form-control" name="result_file" accept=".pdf,.xls,.xlsx" required>
                        </div>
                    </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> Replace File
                    </button>
                    </div>
                </form>
                </div>
            </div>
        </div>



<!-- Bootstrap JS (assumed already imported if in template footer) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // jQuery for handling the dropdown change
    $(document).ready(function(){
        $('#course_code').change(function(){
            var course_code = $(this).val();  // Get the selected course code

            if(course_code != "") {
                // Make an AJAX call to fetch course names
                $.ajax({
                    url: "./includes/get_course_names.php",  // PHP script that returns course names
                    type: "POST",
                    data: { course_code: course_code },
                    success: function(response){
                        $('#course_title').html(response);  // Populate the second dropdown
                    }
                });
            } else {
                $('#course_title').html("<option value=''>Select a Course Name</option>");  // Reset the second dropdown
            }
        });
    });
</script>

<script>

    // jQuery to handle loading lecturers into the dropdown
    $(document).ready(function(){
        // Make an AJAX call to populate the lecturer dropdown when the page loads
        $.ajax({
            url: "./includes/get_lecturers.php",  // PHP script that returns lecturer names
            type: "GET",
            success: function(response){
                $('#lecturer_name').html(response);  // Populate the dropdown with lecturer names
            }
        });
    });
</script>

<script>
  const replaceModal = document.getElementById('replaceModal');
  replaceModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    document.getElementById('original_upload_id').value = button.getAttribute('data-upload-id');
    document.getElementById('replace_course_code').value = button.getAttribute('data-course-code');
    document.getElementById('replace_course_title').value = button.getAttribute('data-course-title');
    document.getElementById('replace_lecturer').value = button.getAttribute('data-lecturer');
    document.getElementById('replace_semester').value = button.getAttribute('data-semester');
    document.getElementById('replace_session').value = button.getAttribute('data-session');
  });
</script>

<script>
    document.getElementById('file').addEventListener('change', function() {
    const courseCode = document.getElementById('course_code').value;
    const semester = document.getElementById('semester').value;
    const session = document.getElementById('session').value;
    const fileInput = this;

    if (fileInput.files.length > 0 && courseCode && semester) {
        const file = fileInput.files[0];
        const ext = file.name.split('.').pop();
        const newName = `${courseCode}_${semester}_${session}.${ext}`;
        alert(`Your file will be saved as: ${newName}`);
    } else {
        alert("Please select course code and semester first.");
    }
    });
</script>

</body>
</html>
