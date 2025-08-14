<?php
session_start();
session_destroy(); // Destroy all session data
header("Location: adviser_login.php"); // Redirect to login page
exit();
?>
