<?php
session_start();
// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Make sure no output is sent before redirection
ob_clean();

// Redirect to login page with absolute path
header("Location: ./login.php");
exit();
?>