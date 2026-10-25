<?php
/**
 * API: Get Attractions
 */
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

try {
    $pdo = getDB();

    $q = trim($_GET['q'] ?? '');
    $categoryId = !empty($_GET['category']) ? (int)$_GET['category'] : 0;
    $maxDist = !empty($_GET['max_distance']) ? (float)$_GET['max_distance'] : 0;
    $maxFee = isset($_GET['max_fee']) && $_GET['max_fee'] !== '' ? (float)$_GET['max_fee'] : -1;
    $accessibleOnly = !empty($_GET['accessible']);

    $sql = "SELECT p.*, c.categoryname, c.icon as category_icon 
            FROM Place p 
            JOIN Category c ON p.categoryid = c.categoryid 
            WHERE p.isdeleted = 0";
    $params = [];

    if ($q !== '') {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.traveltips LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }

    if ($categoryId > 0) {
        $sql .= " AND p.categoryid = ?";
        $params[] = $categoryId;
    }

    if ($maxDist > 0) {
        $sql .= " AND p.distancekm <= ?";
        $params[] = $maxDist;
    }

    if ($maxFee >= 0) {
        $sql .= " AND p.entrancefee <= ?";
        $params[] = $maxFee;
    }

    if ($accessibleOnly) {
        $sql .= " AND (p.accessibility LIKE '%wheelchair%' OR p.accessibility LIKE '%accessible%' OR p.accessibility LIKE '%level%')";
    }

    $sql .= " ORDER BY p.distancekm ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $attractions = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'count' => count($attractions),
        'database_driver' => Database::getDriver(),
        'data' => $attractions
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to retrieve attractions: ' . $e->getMessage()
    ]);
}
