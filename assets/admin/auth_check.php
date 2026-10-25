<?php
/**
 * Admin Authentication Guard
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php?msg=" . urlencode("Authentication required. Please log in to continue."));
    exit;
}
