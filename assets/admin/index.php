<?php
/**
 * EZEE2FIND - Administrator Dashboard
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../config/db.php';

$pdo = getDB();

// Statistics
$totalPlaces = $pdo->query("SELECT COUNT(*) FROM Place WHERE isdeleted = 0")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM Category")->fetchColumn();
$totalFacilities = $pdo->query("SELECT COUNT(*) FROM Facility")->fetchColumn();
$totalItineraries = $pdo->query("SELECT COUNT(*) FROM Itinerary")->fetchColumn();

// Fetch all active places
$places = $pdo->query("SELECT p.*, c.categoryname 
                       FROM Place p 
                       JOIN Category c ON p.categoryid = c.categoryid 
                       WHERE p.isdeleted = 0 
                       ORDER BY p.distancekm ASC")->fetchAll();

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - EZEE2FIND Galle</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<!-- Admin Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark py-2">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <i class="bi bi-shield-check text-primary me-2 fs-4"></i>
      <span>EZEE2FIND</span>
      <span class="badge bg-primary ms-2">Admin Console</span>
    </a>
    
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="text-light small d-none d-md-inline">
        <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['admin_fullname'] ?? 'Admin') ?>
      </span>
      <a href="http://<?php echo $_SERVER["HTTP_HOST"]; ?>/ezee2find/index.php" class="btn btn-outline-light btn-sm rounded-pill" target="_blank">
        <i class="bi bi-box-arrow-up-right me-1"></i> Tourist Portal
      </a>
      <a href="logout.php" class="btn btn-danger btn-sm rounded-pill">
        <i class="bi bi-box-arrow-right me-1"></i> Log Out
      </a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <!--Status Message -->
  <?php if (!empty($msg)): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <!-- Page Title & Add Button -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
      <h2 class="fw-bold mb-1">Attractions & Destinations Management</h2>
      <p class="text-muted small mb-0">Maintain tourist places, operational hours, admission fees, and facilities within 25 km of Galle.</p>
    </div>
    <div>
      <a href="place_add.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
        <i class="bi bi-plus-lg me-1"></i> Add New Tourist Place
      </a>
    </div>
  </div>

  <!-- Metric Overview Cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small">Active Attractions</span>
            <h3 class="fw-bold mb-0 text-primary mt-1"><?= $totalPlaces ?></h3>
          </div>
          <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-circle"><i class="bi bi-geo-alt fs-4"></i></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small">Categories</span>
            <h3 class="fw-bold mb-0 text-success mt-1"><?= $totalCategories ?></h3>
          </div>
          <div class="p-3 bg-success bg-opacity-10 text-success rounded-circle"><i class="bi bi-tags fs-4"></i></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small">Key Facilities</span>
            <h3 class="fw-bold mb-0 text-warning mt-1"><?= $totalFacilities ?></h3>
          </div>
          <div class="p-3 bg-warning bg-opacity-10 text-warning rounded-circle"><i class="bi bi-building fs-4"></i></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <span class="text-muted small">Saved Itineraries</span>
            <h3 class="fw-bold mb-0 text-info mt-1"><?= $totalItineraries ?></h3>
          </div>
          <div class="p-3 bg-info bg-opacity-10 text-info rounded-circle"><i class="bi bi-calendar2-check fs-4"></i></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Places Table -->
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
      <h5 class="fw-bold mb-0">Galle Tourist Destinations (Dataset)</h5>
      <span class="badge bg-light text-dark border rounded-pill"><?= count($places) ?> records</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light small text-muted text-uppercase">
          <tr>
            <th class="ps-3">ID</th>
            <th>Attraction</th>
            <th>Category</th>
            <th>Distance</th>
            <th>Hours</th>
            <th>Local Fee</th>
            <th>Foreign Fee</th>
            <th>Duration</th>
            <th class="text-end pe-3">Actions</th>
          </tr>
        </thead>
        <tbody class="small">
          <?php foreach ($places as $p): ?>
            <tr>
              <td class="ps-3 fw-bold text-muted"><?= $p['placeid'] ?></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <img src="<?= htmlspecialchars($p['imageurl']) ?>" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px;">
                  <div>
                    <strong class="d-block text-dark"><?= htmlspecialchars($p['name']) ?></strong>
                    <span class="text-muted" style="font-size: 0.75rem;">Lat: <?= $p['latitude'] ?>, Lon: <?= $p['longitude'] ?></span>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge bg-light text-secondary border">
                  <?= htmlspecialchars($p['categoryname']) ?>
                </span>
              </td>
              <td><strong><?= $p['distancekm'] ?> km</strong></td>
              <td><?= htmlspecialchars(substr($p['open_hour'] ?? '08:00', 0, 5)) ?> - <?= htmlspecialchars(substr($p['close_hour'] ?? '18:00', 0, 5)) ?></td>
              <td>
                <span class="text-success fw-semibold">
                  <?= $p['entrancefee'] > 0 ? 'LKR ' . number_format($p['entrancefee']) : 'Free' ?>
                </span>
              </td>
              <td>
                <span class="text-secondary">
                  <?= $p['fee_foreign'] > 0 ? 'LKR ' . number_format($p['fee_foreign']) : 'Free' ?>
                </span>
              </td>
              <td><?= $p['visitduration'] ?> mins</td>
              <td class="text-end pe-3">
                <div class="btn-group btn-group-sm">
                  <a href="../../details.php?id=<?= $p['placeid'] ?>" class="btn btn-outline-secondary" target="_blank" title="View Public Page">
                    <i class="bi bi-eye"></i>
                  </a>
                  <a href="place_edit.php?id=<?= $p['placeid'] ?>" class="btn btn-outline-primary" title="Edit Place">
                    <i class="bi bi-pencil"></i>
                  </a>
                  <a href="place_delete.php?id=<?= $p['placeid'] ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete \'<?= htmlspecialchars(addslashes($p['name'])) ?>\'?');" title="Delete Place">
                    <i class="bi bi-trash3"></i>
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
