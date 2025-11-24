<?php
    include('../includes/auth_guard.php');
    require ('./../vendor/autoload.php'); // PhpSpreadsheet
    require_role(array('admin', 'hod'));
    include('../config/db_connect.php');
    // include('../includes/header.php');

    $userRole = $_SESSION['role'];

    use PhpOffice\PhpSpreadsheet\IOFactory;

    $msg = "";

    // --- Handle Add User ---
    if (isset($_POST['add_user'])) {
        $full_name = trim($_POST['full_name']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $dept = trim($_POST['department_code']);
        //$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, role, department_code) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $username, $email, $role, $dept);

        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">✅ User added successfully.</div>';
        } else {
            $msg = '<div class="alert alert-danger">⚠️ Error: ' . htmlspecialchars($stmt->error) . '</div>';
        }
        $stmt->close();
    }

    // --- Handle Archive / Restore ---
    if (isset($_GET['toggle'])) {
        $id = (int) $_GET['toggle'];
        $conn->query("UPDATE users SET status = IF(status='active','archived','active') WHERE user_id=$id");
        header("Location: manage_users.php");
        exit;
    }

    // --- Handle Bulk Upload ---
    if (isset($_POST['bulk_upload'])) {
        $file = $_FILES['excel_file']['tmp_name'];

        if ($file) {
            try {
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                $inserted = 0;
                foreach ($rows as $index => $row) {
                    if ($index == 0) continue; // Skip header row
                    [$full_name, $username, $email, $role, $dept, $password_plain] = $row;

                    if (empty($username)) continue;

                    $password_hash = password_hash(trim($password_plain ?: "123456"), PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT IGNORE INTO users (full_name, username, password_hash, email, role, department_code) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssss", $full_name, $username, $password_hash, $email, $role, $dept);
                    if ($stmt->execute()) $inserted++;
                }

                $msg = "<div class='alert alert-success'>✅ $inserted users uploaded successfully.</div>";

            } catch (Exception $e) {
                $msg = "<div class='alert alert-danger'>❌ Upload failed: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }

    // --- Fetch All Users ---
    $users = $conn->query("SELECT * FROM users ORDER BY date_created DESC");

    print_r($_SESSION);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management </title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; }
        table td, table th { vertical-align: middle; }
    </style>
</head>
<body>

<?php include '../includes/navbar.php' ?>

<div class="container mt-5">
    <div class="card shadow p-4">
        <h3 class="text-center mb-4"><i class="bi bi-people-fill text-primary"></i> Manage Users <?php echo "" ?></h3>

        <?= $msg; ?>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-3">
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus"></i> 
                Add User
            </button>
            <?php if($userRole == 'admin'): ?>
                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                    <i class="bi bi-file-earmark-excel"></i> 
                    Bulk Upload
                </button>
            <?php endif; ?>
            
        </div>

        <!-- User Table -->
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dept</th>
                        <th>Status</th>
                        <th>Date Created</th>

                        <?php if($userRole && $userRole == 'admin'): ?>
                            <th>Action</th>
                        <?php endif; ?>

                    </tr>
                </thead>
                <tbody>
                <?php if ($users->num_rows > 0): ?>
                    <?php while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?= $u['user_id']; ?></td>
                        <td><?= htmlspecialchars($u['full_name']); ?></td>
                        <td><?= htmlspecialchars($u['username']); ?></td>
                        <td><?= htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge bg-secondary"><?= ucfirst($u['role']); ?></span></td>
                        <td><?= htmlspecialchars($u['department_code']); ?></td>
                        <td>
                            <?php if ($u['status'] === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Archived</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('l, d-m-Y', strtotime($u['date_created'])); ?></td>
                        <?php if($userRole && $userRole == 'admin'): ?>

                            <td>
                                <a href="?toggle=<?= $u['user_id']; ?>" class="btn btn-outline-<?= $u['status']=='active'?'danger':'success'; ?> btn-sm">
                                    <i class="bi bi-<?= $u['status']=='active'?'archive':'arrow-counterclockwise'; ?>"></i>
                                </a>
                            </td>

                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <?php if ($userRole !== 'admin') : ?>
                                <option value="secretary">Secretary</option>
                            <?php else: ?>
                                <option value="secretary">Secretary</option>
                                <option value="hod">HOD</option>
                                <option value="admin">Admin</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'hod') : ?>
                        <div class="mb-3">
                            <label class="form-label">Department Code</label>
                            <input type="text" name="department_code" class="form-control" 
                                value="<?php echo isset($_SESSION['department_code']) ? htmlspecialchars($_SESSION['department_code']) : ''; ?>">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer"><button type="submit" name="add_user" class="btn btn-success"><i class="bi bi-check-circle"></i> Save</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Upload Modal -->
<div class="modal fade" id="bulkUploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-file-earmark-excel"></i> Bulk User Upload</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Excel columns must follow this order:</p>
                    <div class="alert alert-secondary p-2"><strong>Full Name | Username | Email | Role | Department | Password</strong></div>
                    <div class="mb-3">
                        <label class="form-label">Select Excel File</label>
                        <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" name="bulk_upload" class="btn btn-success"><i class="bi bi-upload"></i> Upload</button></div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    $('#usersTable').DataTable({
        "pageLength": 10,
        "order": [[ 0, "desc" ]]
    });
});
</script>

</body>
</html>

<?php $conn->close(); ?>
