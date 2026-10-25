<?php
/**
 * EZEE2FIND - Attractions Page
 */
$pageTitle = "Explore Galle Attractions - EZEE2FIND";
$currentPage = "attractions";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();


$q = trim($_GET['q'] ?? '');
$catFilter = !empty($_GET['category']) ? (int)$_GET['category'] : 0;
$maxDist = !empty($_GET['distance']) ? (float)$_GET['distance'] : 0;
$feeFilter = isset($_GET['fee']) && $_GET['fee'] !== '' ? $_GET['fee'] : 'all';
$accessibleOnly = !empty($_GET['accessible']);

// Categories for dropdown
$categories = $pdo->query("SELECT * FROM Category ORDER BY categoryid ASC")->fetchAll();

// Dynamic Query
$sql = "SELECT p.*, c.categoryname, c.icon as category_icon 
        FROM Place p 
        JOIN Category c ON p.categoryid = c.categoryid 
        WHERE p.isdeleted = 0";
$params = [];

if ($q !== '') {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.traveltips LIKE ? OR p.restrictions LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}

if ($catFilter > 0) {
    $sql .= " AND p.categoryid = ?";
    $params[] = $catFilter;
}

if ($maxDist > 0) {
    $sql .= " AND p.distancekm <= ?";
    $params[] = $maxDist;
}

if ($feeFilter === 'free') {
    $sql .= " AND p.entrancefee = 0";
} elseif ($feeFilter === 'paid') {
    $sql .= " AND p.entrancefee > 0";
}

if ($accessibleOnly) {
    $sql .= " AND (p.accessibility LIKE '%wheelchair%' OR p.accessibility LIKE '%accessible%' OR p.accessibility LIKE '%level%')";
}

$sql .= " ORDER BY p.distancekm ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$attractions = $stmt->fetchAll();
?>

<div class="container py-4">
  <!-- Header / Breadcrumb -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h1 class="fw-bold mb-1">Explore Tourist Attractions</h1>
      <p class="text-muted mb-0">Discover 15 curated destinations across Galle town and the coastal 25 km zone.</p>
    </div>
    <div class="mt-3 mt-md-0 d-flex gap-2">
      <a href="map.php" class="btn btn-outline-primary rounded-pill px-3">
        <i class="bi bi-map me-1"></i> View On Map
      </a>
      <a href="planner.php" class="btn btn-primary rounded-pill px-3">
        <i class="bi bi-calendar2-check me-1"></i> Open Day Planner
      </a>
    </div>
  </div>

  <!-- Search & Filter Controls -->
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-lg-4">
      <form action="attractions.php" method="GET" class="row g-3 align-items-end">
        <!-- Keyword Search ( -->
        <div class="col-lg-4 col-md-6">
          <label class="form-label small fw-semibold text-muted">Keyword Search</label>
          <div class="input-group">
            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control border-start-0 bg-light" placeholder="Search by name, tags, tips...">
          </div>
        </div>

        <!-- Category Filter  -->
        <div class="col-lg-3 col-md-6">
          <label class="form-label small fw-semibold text-muted">Category</label>
          <select name="category" class="form-select bg-light">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['categoryid'] ?>" <?= ($catFilter == $cat['categoryid']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['categoryname']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Distance Scope Filter -->
        <div class="col-lg-2 col-md-4">
          <label class="form-label small fw-semibold text-muted">Distance (from Galle)</label>
          <select name="distance" class="form-select bg-light">
            <option value="0">Any Distance (≤25km)</option>
            <option value="2" <?= ($maxDist == 2) ? 'selected' : '' ?>>Walking (≤ 2 km)</option>
            <option value="8" <?= ($maxDist == 8) ? 'selected' : '' ?>>Short Ride (≤ 8 km)</option>
            <option value="18" <?= ($maxDist == 18) ? 'selected' : '' ?>>Coastal (≤ 18 km)</option>
            <option value="25" <?= ($maxDist == 25) ? 'selected' : '' ?>>Full Scope (≤ 25 km)</option>
          </select>
        </div>

        <!-- Entrance Fee Filter-->
        <div class="col-lg-2 col-md-4">
          <label class="form-label small fw-semibold text-muted">Entrance Cost</label>
          <select name="fee" class="form-select bg-light">
            <option value="all" <?= ($feeFilter === 'all') ? 'selected' : '' ?>>All Places</option>
            <option value="free" <?= ($feeFilter === 'free') ? 'selected' : '' ?>>Free Only</option>
            <option value="paid" <?= ($feeFilter === 'paid') ? 'selected' : '' ?>>Paid Only</option>
          </select>
        </div>

        <!-- filter Button & Reset -->
        <div class="col-lg-1 col-md-4 d-flex gap-2">
          <button type="submit"  class="btn btn-primary w-100" title="Apply Filters" name="Filter color-white">
            <i class="bi bi-funnel-fill"></i>
          </button>
          <a href="attractions.php" class="btn btn-outline-secondary" title="Reset Filters">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>

        <!-- Accessibility Checkbox -->
        <div class="col-12 mt-2">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="accessible" value="1" id="accessibleCheck" <?= $accessibleOnly ? 'checked' : '' ?>>
            <label class="form-check-label small text-muted" for="accessibleCheck">
              <i class="bi bi-person-wheelchair text-primary me-1"></i> Wheelchair & step-free accessible only
            </label>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Results Count Indicator -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <span class="text-muted small">
      Showing <strong><?= count($attractions) ?></strong> attractions within the Galle study zone
    </span>
    <?php if ($q !== '' || $catFilter > 0 || $maxDist > 0 || $feeFilter !== 'all' || $accessibleOnly): ?>
      <span class="badge bg-primary bg-opacity-10 text-primary py-2 px-3 rounded-pill small">
        <i class="bi bi-check-circle me-1"></i> Filters Applied
      </span>
    <?php endif; ?>
  </div>

  <!-- Attractions Cards Grid (REQ-01, REQ-02, REQ-03, REQ-04) -->
  <?php if (empty($attractions)): ?>
    <!-- No-Results Notification (REQ-09) -->
    <div class="text-center py-5 bg-white rounded-4 border p-4">
      <div class="display-3 text-muted mb-3"><i class="bi bi-search"></i></div>
      <h4 class="fw-bold text-secondary">No attractions match your criteria</h4>
      <p class="text-muted max-w-md mx-auto mb-4">
        Try broadening your search keywords, clearing specific filters, or checking all categories.
      </p>
      <a href="attractions.php" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All Filters
      </a>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($attractions as $place): ?>
        <div class="col-lg-4 col-md-6">
          <div class="place-card">
            <div class="place-card-img-wrap">
              <img src="<?= htmlspecialchars($place['imageurl']) ?>" alt="<?= htmlspecialchars($place['name']) ?>" class="place-card-img" loading="lazy">
              <span class="place-category-badge">
                <i class="bi <?= htmlspecialchars($place['category_icon']) ?> me-1"></i><?= htmlspecialchars($place['categoryname']) ?>
              </span>
              <span class="place-distance-badge">
                <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= $place['distancekm'] ?> km
              </span>
            </div>

            <div class="place-card-body">
              <h5 class="place-card-title"><?= htmlspecialchars($place['name']) ?></h5>
              
              <div class="place-card-meta">
                <span><i class="bi bi-clock me-1 text-primary"></i> <?= $place['visitduration'] ?> mins</span>
                <span>
                  <i class="bi bi-ticket-perforated me-1 text-success"></i> 
                  <?= $place['entrancefee'] > 0 ? 'LKR ' . number_format($place['entrancefee']) : 'Free' ?>
                </span>
                <?php if (!empty($place['accessibility']) && stripos($place['accessibility'], 'accessible') !== false): ?>
                  <span><i class="bi bi-person-wheelchair text-info" title="<?= htmlspecialchars($place['accessibility']) ?>"></i></span>
                <?php endif; ?>
              </div>

              <p class="place-card-desc"><?= htmlspecialchars($place['description']) ?></p>

              <div class="place-card-footer">
                <a href="details.php?id=<?= $place['placeid'] ?>" class="btn btn-light btn-sm flex-grow-1 rounded-pill">
                  View Details
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-plan-btn="<?= $place['placeid'] ?>">
                  <i class="bi bi-plus-circle me-1"></i> Add to Plan
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
