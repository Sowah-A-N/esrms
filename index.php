<?php
include('config/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ESRMS Home</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<!-- <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom mb-4">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">ESRMS</a>
  </div>
</nav> -->

<!-- Main Content -->
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow-sm text-center">
        <div class="card-body">
          <i class="bi bi-journal-check fs-1 text-primary mb-3"></i>
          <h3 class="card-title mb-3">Welcome to ESRMS</h3>
          <p class="card-text text-muted mb-4">Manage and access end-of-semester results easily.</p>
          <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-primary btn-lg">
            <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle (for components like dropdowns) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
