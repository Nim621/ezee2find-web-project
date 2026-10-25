<?php
/**
 * EZEE2FIND - Delete Attraction Action
  */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../config/db.php';


$id = isset($_GET['placeid']) ? (int)$_GET['placeid'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

try {
    // soft delete 
    $stmt = $pdo->prepare("UPDATE Place SET isdeleted = 1 WHERE placeid = ?");
    $stmt->execute([$id]);

    header("Location: index.php?msg=" . urlencode("Attraction ID #$id was removed from the active catalogue."));
    exit;
} catch (Exception $e) {
    header("Location: index.php?msg=" . urlencode("Error deleting attraction: " . $e->getMessage()));
    exit;
}
