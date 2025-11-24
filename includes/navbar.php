<?php
// ===============================
// ESRMS Navbar Partial
// ===============================
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Variables
$role = $_SESSION['role'] ?? 'guest';
$username = htmlspecialchars($_SESSION['username'] ?? 'User');
$current_page = basename($_SERVER['PHP_SELF']);

// Helper for "active" class
function isActive($file) {
  global $current_page;
  return $current_page === $file ? 'active fw-semibold' : '';
}
?>

<nav class="navbar navbar-expand-lg bg-light border-bottom py-3 shadow-sm">
  <div class="container-fluid">

    <!-- Brand -->
    <a class="navbar-brand fw-bold text-primary text-uppercase" href="/esrms/dashboard.php">
      <i class="bi bi-mortarboard-fill me-1"></i> ESRMS
    </a>

    <!-- Mobile Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Content -->
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav ms-auto align-items-center">

        <!-- Dashboard -->
        <li class="nav-item">
          <a class="nav-link <?= isActive('dashboard.php') ?>" href="/esrms/dashboard.php">
            <i class="bi bi-house-door me-1"></i> Dashboard
          </a>
        </li>

        <!-- Secretary Upload -->
        <?php if ($role === 'secretary'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= in_array($current_page, ['upload_courses.php','upload_results.php']) ? 'active fw-semibold' : '' ?>" 
               href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-upload me-1"></i> Uploads
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="/esrms/courses/upload_courses.php">
                  <i class="bi bi-book me-2 text-primary"></i> Upload Courses
                </a>
              </li>
              
            </ul>
          </li>
        <?php endif; ?>

        <!-- HOD / Admin Management -->
        <?php if (in_array($role, ['hod', 'admin'])): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= in_array($current_page, ['view_results.php','view_courses.php']) ? 'active fw-semibold' : '' ?>" 
               href="#" role="button" data-bs-toggle="dropdown">
              <i class="bi bi-gear me-1"></i> Management
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/esrms/results/view_results.php">
                <i class="bi bi-journal-text me-2 text-primary"></i> View Results</a>
              </li>
              <li><a class="dropdown-item" href="/esrms/courses/upload_courses.php">
                <i class="bi bi-book-half me-2 text-primary"></i> View Courses</a>
              </li>
            </ul>
            <li><a class="dropdown-item" href="/esrms/activity/manage_users.php">
                <i class="bi bi-people-fill me-2 text-primary"></i> Manage Users</a>
              </li>
          </li>
        <?php endif; ?>

        <!-- Common Links -->
        <li class="nav-item">
          <a class="nav-link <?= isActive('view_results.php') ?>" href="/esrms/results/view_results.php">
            <i class="bi bi-search me-1"></i> View Results
          </a>
        </li>
        <?php if($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
            <a class="nav-link <?= isActive('logs.php') ?>" href="/esrms/activity/logs.php">
                <i class="bi bi-clock-history me-1"></i> Activity Logs
            </a>
            </li>
        <?php endif; ?>

        <!-- User Greeting -->
        <li class="nav-item">
          <span class="nav-link text-muted small">
            Welcome, <strong><?= $username ?></strong> (<?= ucfirst($role) ?>)
          </span>
        </li>

        <!-- Logout -->
        <li class="nav-item">
          <a href="/esrms/auth/logout.php" class="btn btn-outline-danger btn-sm ms-lg-2">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>

      </ul>
    </div>
  </div>
</nav>
