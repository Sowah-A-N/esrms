<?php
if (session_status() == PHP_SESSION_NONE) {
    include_once(__DIR__ . '/../config/session_handler.php');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ESRMS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { margin-bottom: 20px; }
        a { margin-right: 10px; }
    </style>
</head>
<body>
<nav>
    <a href="/esrms/index.php">Dashboard</a>
    <a href="/esrms/uploads/upload_form.php">Upload Form A</a>
    <a href="/esrms/results/search_results.php">Search Results</a>
    <a href="/esrms/activity/logs.php">Activity Logs</a>
    <a href="/esrms/auth/logout.php">Logout</a>
</nav>
