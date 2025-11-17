<?php
include('../includes/auth_guard.php');
include('../config/db_connect.php');

// Restrict access
if (!in_array($_SESSION['role'], ['admin', 'secretary', 'hod'])) {
    die("<div class='alert alert-danger text-center mt-4'>Unauthorized Access</div>");
}

$dept_code = mysqli_real_escape_string($conn, $_SESSION['department_code']);

require_once('../vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

$msg = '';

/* -------------------- 1️⃣ Template Download -------------------- */
if (isset($_GET['download_template'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray(['Course Code', 'Course Title', 'Credit Unit', 'Semester'], NULL, 'A1');
    $sheet->fromArray([
        ['CSC101', 'Intro to Computing', 3, 'First'],
        ['CSC102', 'Algorithms', 3, 'Second']
    ], NULL, 'A2');

    foreach (range('A', 'D') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="course_upload_template.xlsx"');
    IOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
    exit();
}

/* -------------------- 2️⃣ Upload Handlers -------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // SINGLE UPLOAD
    if (isset($_POST['single_upload'])) {
        $code = strtoupper(trim($_POST['course_code']));
        $title = trim($_POST['course_title']);
        $unit = intval($_POST['credit_unit']);
        $sem = $_POST['semester'];

        if ($code && $title && $sem) {
            $sql = "INSERT INTO courses (course_code, course_title, department_code, credit_unit, semester)
                    VALUES ('$code', '$title', '$dept_code', $unit, '$sem')";
            $msg = mysqli_query($conn, $sql)
                ? "<div class='alert alert-success'>Course <b>$code</b> added successfully.</div>"
                : "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Please fill all fields.</div>";
        }
    }

    // BULK UPLOAD
    if (isset($_POST['bulk_upload'])) {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $msg = "<div class='alert alert-danger'>Please select a valid Excel file.</div>";
        } else {
            try {
                $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
                $added = $fail = 0;

                foreach ($rows as $i => $r) {
                    if ($i === 0) continue;
                    [$code, $title, $unit, $sem] = $r;
                    if (!$code || !$title || !$sem) { $fail++; continue; }
                    $code = strtoupper(trim($code));
                    $title = trim($title);
                    $unit = intval($unit);
                    $sem = trim($sem);
                    $sql = "INSERT INTO courses (course_code, course_title, department_code, credit_unit, semester)
                            VALUES ('$code', '$title', '$dept_code', $unit, '$sem')";
                    if (mysqli_query($conn, $sql)) $added++; else $fail++;
                }

                $msg = "<div class='alert alert-info'>
                            Bulk upload finished. <strong>$added</strong> added, <strong>$fail</strong> failed.
                        </div>";

            } catch (Exception $e) {
                $msg = "<div class='alert alert-danger'>Error reading Excel: {$e->getMessage()}</div>";
            }
        }
    }
}

/* -------------------- 3️⃣ Fetch Courses -------------------- */
$courses = mysqli_query($conn, "SELECT * FROM courses WHERE department_code='$dept_code' ORDER BY semester, course_code ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Upload & View Courses</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">

    <?php include '../includes/navbar.php' ?>

<div class="container my-5">
<div class="card shadow-lg p-4">
    <div class="text-center mb-4">
        <i class="bi bi-journal-text display-5 text-primary"></i>
        <h3 class="fw-bold mt-2">Course Upload & Management</h3>
        <p class="text-muted">Department: <b><?php echo htmlspecialchars($dept_code); ?></b></p>
    </div>

    <?php echo $msg; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs justify-content-center mb-4">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#single">Single Upload</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#bulk">Bulk Upload</button></li>
    </ul>

    <div class="tab-content">
        <!-- Single Upload -->
        <div class="tab-pane fade show active" id="single">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-3"><input type="text" name="course_code" placeholder="Course Code" class="form-control" required></div>
                    <div class="col-md-4"><input type="text" name="course_title" placeholder="Course Title" class="form-control" required></div>
                    <div class="col-md-2"><input type="number" name="credit_unit" class="form-control" value="3" min="1" max="6" required></div>
                    <div class="col-md-2">
                        <select name="semester" class="form-select" required>
                            <option value="">Semester</option>
                            <option>First</option><option>Second</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-grid">
                        <button type="submit" name="single_upload" class="btn btn-primary"><i class="bi bi-plus"></i></button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bulk Upload -->
        <div class="tab-pane fade" id="bulk">
            <form method="POST" enctype="multipart/form-data" class="mt-3">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <input type="file" name="excel_file" accept=".xlsx,.xls" class="form-control" required>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button type="submit" name="bulk_upload" class="btn btn-success"><i class="bi bi-upload"></i> Upload</button>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="?download_template=1" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Download Template</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <hr class="my-4">
    <h4 class="fw-bold text-center mb-3"><i class="bi bi-list-ul text-primary me-2"></i>Uploaded Courses</h4>

    <?php if ($courses && mysqli_num_rows($courses) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-primary text-center">
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Title</th>
                        <th>Unit</th>
                        <th>Semester</th>
                        <?php if (in_array($_SESSION['role'], ['admin','secretary'])): ?><th>Actions</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $n=1; while($row=mysqli_fetch_assoc($courses)): ?>
                    <tr data-id="<?= $row['course_id'] ?>">
                        <td class="text-center"><?= $n++ ?></td>
                        <td><?= htmlspecialchars($row['course_code']) ?></td>
                        <td><?= htmlspecialchars($row['course_title']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['credit_unit']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($row['semester']) ?></td>
                        <?php if (in_array($_SESSION['role'], ['admin','secretary'])): ?>
                        <td class="text-center">
                            <button class="btn btn-sm btn-warning editBtn"><i class="bi bi-pencil"></i></button>
                            <button class="btn btn-sm btn-danger deleteBtn"><i class="bi bi-trash"></i></button>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-secondary text-center">No courses uploaded yet.</div>
    <?php endif; ?>
</div>
</div>

<!-- 🔸 Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog">
<form id="editForm" class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Edit Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <input type="hidden" name="course_id" id="edit_id">
        <div class="mb-3"><label>Course Code</label><input type="text" class="form-control" id="edit_code" name="course_code" required></div>
        <div class="mb-3"><label>Course Title</label><input type="text" class="form-control" id="edit_title" name="course_title" required></div>
        <div class="mb-3"><label>Credit Unit</label><input type="number" class="form-control" id="edit_unit" name="credit_unit" min="1" max="6" required></div>
        <div class="mb-3"><label>Semester</label>
            <select class="form-select" id="edit_semester" name="semester" required>
                <option>First</option><option>Second</option>
            </select>
        </div>
    </div>
    <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
    </div>
</form>
</div>
</div>

<script>
$(function(){
    // Edit Button
    $('.editBtn').click(function(){
        const tr = $(this).closest('tr');
        $('#edit_id').val(tr.data('id'));
        $('#edit_code').val(tr.find('td:eq(1)').text());
        $('#edit_title').val(tr.find('td:eq(2)').text());
        $('#edit_unit').val(tr.find('td:eq(3)').text());
        $('#edit_semester').val(tr.find('td:eq(4)').text());
        new bootstrap.Modal('#editModal').show();
    });

    // Submit Edit
    $('#editForm').submit(function(e){
        e.preventDefault();
        $.post('course_action.php', $(this).serialize() + '&action=edit', function(res){
            alert(res.message);
            if(res.success) location.reload();
        }, 'json');
    });

    // Delete Button
    $('.deleteBtn').click(function(){
        if(!confirm('Are you sure you want to delete this course?')) return;
        const id = $(this).closest('tr').data('id');
        $.post('course_action.php', {action:'delete', course_id:id}, function(res){
            alert(res.message);
            if(res.success) location.reload();
        }, 'json');
    });
});
</script>

</body>
</html>
