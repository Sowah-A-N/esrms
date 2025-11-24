<?php
session_start();
//include('includes/auth_guard.php');
include('../config/config.php');
include('../config/db_connect.php');
?>

<?php 
    print_r($_SESSION);
    //die();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Form A Records</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <!-- Custom Minimalist Styles -->
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333; /* Dark text for better readability */
        }
        
        h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #343a40; /* Dark gray for contrast */
            margin-bottom: 1.5rem;
            text-align: center;
            letter-spacing: 1px;
        }

        .search-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .search-container input {
            font-size: 1rem;
            padding: 1rem;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            transition: border-color 0.3s ease-in-out;
        }

        .search-container input:focus {
            border-color: #007bff; /* Blue color for focus */
            outline: none;
        }

        .btn-search {
            width: 100%;
            padding: 1rem;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-search:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        .table {
            margin-top: 2rem;
            width: 100%;
        }

        .table th, .table td {
            text-align: center;
            padding: 1rem;
        }

        .table th {
            background-color: #007bffb7;
            color: #fff;
        }

        .table td a {
            color: #fdfdfdff;
            text-decoration: none;
            font-weight: 600;
        }

        .table td a:hover {
            text-decoration: underline;
        }

        .results-not-found {
            text-align: center;
            font-size: 1.2rem;
            color: #ee374fff;
        }

        .results-not-found p {
            margin-top: 1rem;
        }
    </style>
</head>
<body>

    <?php include '../includes/navbar.php' ?>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="container mt-5">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success! </strong><?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php $_SESSION['success'] = ""; ?>

    <div class="container-lg mt-4">
        <!-- Search Form Section -->
        <div class="container mt-4">
            <h2>Search Form A Records</h2>

            <form id="multiSearchForm">
                <div class="row g-3">

                    <!-- Course Code -->
                    <div class="col-md-6">
                        <label for="course_code" class="form-label">Course Code</label>
                        <select class="form-control" id="course_code" name="course_code">
                            <option value="">Select a Course Code</option>
                            <?php
                                $sql = "SELECT DISTINCT course_code FROM courses"; // Make sure `courses` table exists
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['course_code']}'>{$row['course_code']}</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <!-- Course Title -->
                    <div class="col-md-6">
                        <label for="course_title" class="form-label">Course Title</label>
                        <select class="form-control" id="course_title" name="course_title">
                            <option value="">Select a Course Title</option>
                            <?php
                                $sql = "SELECT DISTINCT course_title FROM uploads";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['course_title']}'>{$row['course_title']}</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <!-- Semester -->
                    <div class="col-md-6">
                        <label for="semester" class="form-label">Semester</label>
                        <select class="form-select" name="semester" id="semester">
                            <option value="">Choose...</option>
                            <option value="First">First</option>
                            <option value="Second">Second</option>
                        </select>
                    </div>

                    <!-- Academic Year -->
                    <div class="col-md-6">
                        <label for="session" class="form-label">Academic Year</label>
                        <input type="text" class="form-control" name="session" id="session" placeholder="e.g. 2024/2025">
                    </div>

                    <!-- Lecturer Name -->
                    <div class="col-md-12">
                        <label for="lecturer_name" class="form-label">Lecturer Name</label>
                        <select class="form-control" id="lecturer_name" name="lecturer_name">
                            <option value="">Select a Lecturer</option>
                            <?php
                                $sql = "SELECT DISTINCT lecturer_name FROM uploads";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='{$row['lecturer_name']}'>{$row['lecturer_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>

                    <!-- Search Button -->
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary w-100 mt-3">Search</button>
                    </div>
                </div>
            </form>

            <!-- Result Table Placeholder -->
            <div id="searchResults" class="mt-5"></div>

        </div>

    </div>

<!-- Replace Upload Modal -->
<div class="modal fade" id="replaceModal" tabindex="-1" aria-labelledby="replaceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="replaceForm" method="POST" enctype="multipart/form-data" action="replace_upload.php">
        <div class="modal-header bg-warning bg-opacity-25 border-bottom-0">
          <h5 class="modal-title fw-bold text-warning-emphasis" id="replaceModalLabel">
            <i class="bi bi-arrow-repeat me-2"></i> Replace Upload
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Hidden fields -->
          <input type="hidden" name="original_upload_id" id="original_upload_id">
          <input type="hidden" name="replace_id" id="replace_id" value="">

          <!-- Current File Info -->
          <div class="alert alert-secondary small" id="currentVersionInfo">
            <div class="d-flex align-items-start">
              <i class="bi bi-info-circle-fill me-2 text-secondary"></i>
              <div>
                <strong>Current File Information</strong>
                <div class="mt-1">
                  <div>📘 <strong>Version:</strong> <span id="current_version">1</span></div>
                  <div>🗓️ <strong>Last Modified:</strong> <span id="current_modified">2025-10-29 14:33</span></div>
                  <div>👤 <strong>Uploaded By:</strong> <span id="current_uploaded_by">John Doe</span></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Replacement Form Fields -->
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

            <!-- <div class="alert alert-secondary small" id="currentVersionInfo">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill me-2 text-secondary fs-5"></i>
                    <div>
                    <strong>Current File Information</strong>
                    <div class="mt-1">
                        <div>📘 <strong>Version:</strong> <span id="current_version">—</span></div>
                        <div>🗓️ <strong>Last Modified:</strong> <span id="current_modified">—</span></div>
                        <div>👤 <strong>Uploaded By:</strong> <span id="current_uploaded_by">—</span></div>
                    </div>
                    </div>
                </div>
            </div> -->


            <div class="col-md-12">
              <label class="form-label fw-semibold text-danger">New Result File</label>
              <input type="file" class="form-control" name="result_file" accept=".pdf,.xls,.xlsx" required>
              <div class="form-text text-muted">
                Accepted formats: PDF, XLS, XLSX only.
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i> Cancel
          </button>
          <button type="submit" class="btn btn-warning">
            <i class="bi bi-arrow-repeat me-1"></i> Replace File
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


<script>
  const replaceModal = document.getElementById('replaceModal');

  replaceModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    // Populate hidden + readonly fields
    document.getElementById('original_upload_id').value = button.getAttribute('data-upload-id');
    document.getElementById('replace_course_code').value = button.getAttribute('data-course-code');
    document.getElementById('replace_course_title').value = button.getAttribute('data-course-title');
    document.getElementById('replace_lecturer').value = button.getAttribute('data-lecturer');
    document.getElementById('replace_semester').value = button.getAttribute('data-semester');
    document.getElementById('replace_session').value = button.getAttribute('data-session');

    // Populate version info
    document.getElementById('current_version').textContent = button.getAttribute('data-version') || '1';
    document.getElementById('current_modified').textContent = button.getAttribute('data-modified') || '—';
    document.getElementById('current_uploaded_by').textContent = button.getAttribute('data-uploaded-by') || 'Unknown';
  });
</script>



    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

    <!-- AJAX Script -->
    <script>
        $(document).ready(function () {
            $('#multiSearchForm').on('submit', function (e) {
                e.preventDefault();

                $.ajax({
                    url: 'search_results.php',
                    method: 'GET',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#searchResults').html(response);
                    },
                    error: function () {
                        $('#searchResults').html('<p class="text-danger">Error fetching data.</p>');
                    }
                });
            });
        });
    </script>
</body>
</html>
