<?php
/**
 * API: Get Single Attraction Details with Facilities
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Valid Place ID is required']);
    exit;
}

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("SELECT p.*, c.categoryname, c.icon as category_icon 
                           FROM Place p 
                           JOIN Category c ON p.categoryid = c.categoryid 
                           WHERE p.placeid = ? AND p.isdeleted = 0");
    $stmt->execute([$id]);
    $place = $stmt->fetch();

    if (!$place) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Attraction not found']);
        exit;
    }

    // Fetch associated facilities
    $facStmt = $pdo->prepare("SELECT f.*, pf.distance_meters, pf.notes as relation_notes 
                             FROM PlaceFacility pf 
                             JOIN Facility f ON pf.facilityid = f.facilityid 
                             WHERE pf.placeid = ?");
    $facStmt->execute([$id]);
    $facilities = $facStmt->fetchAll();

    $place['facilities'] = $facilities;

    echo json_encode([
        'status' => 'success',
        'data' => $place
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
