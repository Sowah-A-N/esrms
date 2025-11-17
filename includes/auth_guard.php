<?php
// include_once(__DIR__ . '../config/session_handler.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: ./auth/login.php");
    exit();
}

function require_role($allowed_roles = array()) {
    if (!isset($_SESSION['role'])) {
        header("Location: ./auth/login.php");
        exit();
    }

    $user_role = strtolower($_SESSION['role']);
    $allowed = array_map('strtolower', $allowed_roles);

    if (!in_array($user_role, $allowed)) {
        header("HTTP/1.1 403 Forbidden");
        echo "<h3>Access Denied</h3><p>You do not have permission to view this page.</p>";
        exit();
    }
}
?>