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

    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom py-3">
  <div class="container-fluid">

    <!-- Sidebar toggle for mobile -->
    <button class="btn btn-outline-secondary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
      <i class="bi bi-list"></i>
    </button>

    <!-- Brand -->
    <a class="navbar-brand fw-semibold text-uppercase" href="/esrms/index.php">ESRMS</a>

    <!-- Toggler for collapsing navbar -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar content -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item">
          <a class="nav-link text-uppercase small fw-medium" href="/esrms/index.php">Dashboard</a>
        </li>
        <!--  c -->

        <li class="nav-item">
          <a class="nav-link text-uppercase small fw-medium" href="/esrms/results/view_results.php">View Results</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-uppercase small fw-medium" href="/esrms/activity/logs.php">Activity Logs</a>
        </li>
        <li class="nav-item">
          <span class="nav-link text-muted small">
            Welcome, <strong><?php echo $_SESSION['username'] ?? 'User'; ?></strong>
          </span>
        </li>
        <li class="nav-item">
          <a href="/esrms/auth/logout.php" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
          </a>
        </li>
      </ul>
    </div>

  </div>
</nav>

    <div class="container mt-5">
        <!-- Search Form Section -->
        <div class="search-container">
            <h2>Search Form A Records</h2>

            <!-- Search Bar -->
            <input type="text" id="searchBar" class="form-control" placeholder="Search by course, lecturer, or session">

            <!-- Search Button (optional, but can be used for static forms) -->
            <button type="button" class="btn btn-search">Search</button>
        </div>

        <!-- Results Display -->
        <div id="searchResults"></div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>

    <!-- AJAX Script -->
    <script>
        $(document).ready(function() {
            $('#searchBar').on('keyup', function() {
                var searchQuery = $(this).val();

                if (searchQuery.length > 2) {  // Start searching after 3 characters
                    $.ajax({
                        url: './search_results.php',  // Adjust the path if needed
                        method: 'GET',
                        data: { q: searchQuery },
                        success: function(response) {
                            $('#searchResults').html(response);
                        }
                    });
                } else {
                    $('#searchResults').html('');  // Clear results if query is too short
                }
            });
        });
    </script>
</body>
</html>
