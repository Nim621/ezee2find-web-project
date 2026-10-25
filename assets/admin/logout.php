<?php
/**
 * Administrator Logout Script
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = [];
session_destroy();
header("Location: login.php?msg=" . urlencode("You have been securely logged out."));
exit;
