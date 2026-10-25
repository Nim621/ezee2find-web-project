<?php
/**
 * EZEE2FIND - Attraction Details
 */
$pageTitle = "Attraction Details - EZEE2FIND";
$currentPage = "attractions";
require_once __DIR__ . '/config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: attractions.php");
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare("SELECT p.*, c.categoryname, c.icon as category_icon 
                       FROM Place p 
                       JOIN Category c ON p.categoryid = c.categoryid 
                       WHERE p.placeid = ? AND p.isdeleted = 0");
$stmt->execute([$id]);
$place = $stmt->fetch();

if (!$place) {
    header("Location: attractions.php");
    exit;
}

// Fetch nearby facilities for this place
$facStmt = $pdo->prepare("SELECT f.*, pf.distance_meters, pf.notes as relation_notes 
                         FROM PlaceFacility pf 
                         JOIN Facility f ON pf.facilityid = f.facilityid 
                         WHERE pf.placeid = ?");
$facStmt->execute([$id]);
$facilities = $facStmt->fetchAll();

$pageTitle = htmlspecialchars($place['name']) . " - EZEE2FIND";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item"><a href="attractions.php">Attractions</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($place['name']) ?></li>
    </ol>
  </nav>

  <!-- Title & Action Bar -->
  <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
      <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold mb-2">
        <i class="bi <?= htmlspecialchars($place['category_icon']) ?> me-1"></i> <?= htmlspecialchars($place['categoryname']) ?>
      </span>
      <h1 class="fw-bold mb-2"><?= htmlspecialchars($place['name']) ?></h1>
      <p class="text-muted mb-0">
        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Approx <strong><?= $place['distancekm'] ?> km</strong> from Galle City Center
      </p>
    </div>

    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-primary rounded-pill px-4" data-plan-btn="<?= $place['placeid'] ?>">
        <i class="bi bi-plus-circle me-1"></i> Add to Plan
      </button>
      <a href="planner.php" class="btn btn-primary rounded-pill px-4">
        <i class="bi bi-calendar2-check me-1"></i> View Plan
      </a>
    </div>
  </div>

  <!-- Main Hero Image Banner -->
  <div class="rounded-4 overflow-hidden shadow-sm mb-4" style="max-height: 440px;">
    <img src="<?= htmlspecialchars($place['imageurl']) ?>" alt="<?= htmlspecialchars($place['name']) ?>" class="w-100 h-100 object-fit-cover" style="object-position: center;">
  </div>

  <div class="row g-4">
    <!--left- Details, Description, Tips, Restrictions -->
    <div class="col-lg-8">
      <!-- Quick Info Bar  -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
          <div class="row g-3 text-center">
            <div class="col-6 col-md-3 border-end">
              <span class="text-muted small d-block">Recommended Stay</span>
              <strong class="fs-5 text-primary"><?= $place['visitduration'] ?> Mins</strong>
            </div>
            <div class="col-6 col-md-3 border-end-md">
              <span class="text-muted small d-block">Opening Hours</span>
              <strong class="fs-6 text-secondary"><?= htmlspecialchars(substr($place['open_hour'] ?? '08:00', 0, 5)) ?> - <?= htmlspecialchars(substr($place['close_hour'] ?? '18:00', 0, 5)) ?></strong>
            </div>
            <div class="col-6 col-md-3 border-end">
              <span class="text-muted small d-block">Local Entrance Fee</span>
              <strong class="fs-6 text-success"><?= $place['entrancefee'] > 0 ? 'LKR ' . number_format($place['entrancefee']) : 'Free Entry' ?></strong>
            </div>
            <div class="col-6 col-md-3">
              <span class="text-muted small d-block">Foreign Entrance Fee</span>
              <strong class="fs-6 text-success"><?= $place['fee_foreign'] > 0 ? 'LKR ' . number_format($place['fee_foreign']) : 'Free Entry' ?></strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold mb-3"><i class="bi bi-card-text text-primary me-2"></i> About this Destination</h4>
          <p class="lead fs-6 text-secondary" style="line-height: 1.8;">
            <?= nl2br(htmlspecialchars($place['description'])) ?>
          </p>
          <div class="p-3 bg-light rounded-3 mt-3">
            <span class="small text-muted d-block fw-semibold"><i class="bi bi-clock me-1 text-primary"></i> Operating Schedule & Entry Notes:</span>
            <p class="mb-0 small text-dark mt-1"><?= htmlspecialchars($place['openinginfo']) ?></p>
            <?php if (!empty($place['fee_notes'])): ?>
              <p class="mb-0 small text-muted mt-1"><i class="bi bi-info-circle me-1"></i> <?= htmlspecialchars($place['fee_notes']) ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Travel Tips, Restrictions, Accessibility -->
      <div class="row g-4 mb-4">
        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3 text-warning">
                <i class="bi bi-shield-exclamation me-2"></i> Restrictions & Etiquette
              </h5>
              <p class="small text-muted mb-0">
                <?= nl2br(htmlspecialchars($place['restrictions'] ?? 'No special restrictions specified. Standard respectful tourism etiquette applies.')) ?>
              </p>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-3 text-info">
                <i class="bi bi-person-wheelchair me-2"></i> Accessibility
              </h5>
              <p class="small text-muted mb-0">
                <?= nl2br(htmlspecialchars($place['accessibility'] ?? 'Standard pedestrian access.')) ?>
              </p>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
              <h5 class="fw-bold mb-2 text-success">
                <i class="bi bi-lightbulb-fill me-2"></i> Local Travel Advice & Best Visiting Period
              </h5>
              <div class="mb-3">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill">
                  <i class="bi bi-sun me-1"></i> Best Time: <?= htmlspecialchars($place['besttime'] ?? 'Morning or afternoon') ?>
                </span>
              </div>
              <p class="small text-secondary mb-0">
                <?= nl2br(htmlspecialchars($place['traveltips'] ?? 'Carry plenty of water, wear comfortable footwear, and prepare for tropical coastal warmth.')) ?>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Nearby Facilities -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
          <h4 class="fw-bold mb-3"><i class="bi bi-building-check text-primary me-2"></i> Nearby Facilities</h4>
          <?php if (empty($facilities)): ?>
            <p class="text-muted small mb-0">Local street facilities, tuk-tuk stands, and small stalls are available in the immediate vicinity.</p>
          <?php else: ?>
            <div class="row g-3">
              <?php foreach ($facilities as $fac): ?>
                <div class="col-md-6">
                  <div class="p-3 bg-light rounded-3 border h-100">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                      <strong class="text-dark small"><?= htmlspecialchars($fac['name']) ?></strong>
                      <span class="badge bg-secondary rounded-pill small"><?= htmlspecialchars($fac['type']) ?></span>
                    </div>
                    <div class="text-muted small">
                      <i class="bi bi-signpost me-1"></i> <?= $fac['distance_meters'] ?> meters away
                    </div>
                    <?php if (!empty($fac['relation_notes'])): ?>
                      <div class="small text-secondary mt-1"><?= htmlspecialchars($fac['relation_notes']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Right Column- Location Map & Coordinates -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 90px; z-index: 10;">
        <div class="card-body p-4">
          <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt text-danger me-2"></i> Geographic Location</h5>
          <div id="details-map" style="height: 280px;" class="rounded-3 mb-3"></div>
          
          <ul class="list-unstyled small mb-4">
            <li class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Latitude</span>
              <strong><?= $place['latitude'] ?></strong>
            </li>
            <li class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Longitude</span>
              <strong><?= $place['longitude'] ?></strong>
            </li>
            <li class="d-flex justify-content-between py-1 border-bottom">
              <span class="text-muted">Distance to Galle Hub</span>
              <strong><?= $place['distancekm'] ?> km</strong>
            </li>
          </ul>

          <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary rounded-pill py-2 fw-semibold" data-plan-btn="<?= $place['placeid'] ?>">
              <i class="bi bi-calendar2-plus me-1"></i> Add to Day Plan
            </button>
            <a href="https://www.google.com/maps/search/?api=1&query=<?= $place['latitude'] ?>,<?= $place['longitude'] ?>" target="_blank" class="btn btn-outline-secondary rounded-pill py-2 small">
              <i class="bi bi-box-arrow-up-right me-1"></i> Open in Google Maps
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/map.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const lat = <?= (float)$place['latitude'] ?>;
    const lon = <?= (float)$place['longitude'] ?>;
    const map = L.map('details-map').setView([lat, lon], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const pin = createCustomPin('#0284c7');
    L.marker([lat, lon], { icon: pin }).addTo(map)
      .bindPopup("<strong><?= htmlspecialchars(addslashes($place['name'])) ?></strong><br><small><?= $place['distancekm'] ?> km from Galle Center</small>")
      .openPopup();
  });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
