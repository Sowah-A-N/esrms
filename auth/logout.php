<?php
session_start();
include('../config/session_handler.php');

echo "<pre>";
echo "Session ID: " . session_id() . "\n";
print_r($_SESSION);
echo "</pre>";

session_unset();
session_destroy();

echo "Session destroyed.";

header("Location: login.php");
exit();
