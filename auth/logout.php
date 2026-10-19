<?php
// auth/logout.php
session_start();
session_unset();
session_destroy();
header("Location: login.php"); // Ini udah bener, karena file ini ada di dalam folder auth
exit;
?>