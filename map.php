<?php
/**
 * EZEE2FIND - Interactive Geographic Map View
 */
$pageTitle = "Interactive Map of Galle - EZEE2FIND";
$currentPage = "map";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$places = $pdo->query("SELECT p.*, c.categoryname, c.icon as category_icon 
                       FROM Place p 
                       JOIN Category c ON p.categoryid = c.categoryid 
                       WHERE p.isdeleted = 0 
                       ORDER BY p.distancekm ASC")->fetchAll();
$categories = $pdo->query("SELECT * FROM Category ORDER BY categoryid ASC")->fetchAll();
?>

<div class="container-fluid px-lg-4 py-3">
  <!-- Top Bar Controls -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
      <h2 class="fw-bold mb-0">Interactive Map of Galle Destinations</h2>
      <p class="text-muted small mb-0">Explore spatial relationships, 25 km radius study area, and plan routes.</p>
    </div>

    <!-- Mode Filters & Actions -->
    <div class="d-flex flex-wrap gap-2">
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-primary btn-sm active" id="btn-show-all" onclick="filterMapCategory(0)">
          All Sights (<?= count($places) ?>)
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-show-plan" onclick="showOnlyPlannedPlaces()">
          <i class="bi bi-pin-map-fill text-danger me-1"></i> My Planned Stops (<span id="map-plan-count">0</span>)
        </button>
      </div>

      <a href="planner.php" class="btn btn-primary btn-sm rounded-pill px-3">
        <i class="bi bi-calendar2-range me-1"></i> Open Planner
      </a>
    </div>
  </div>

  <div class="row g-3">
    <!-- Map Canvas Container (Left) -->
    <div class="col-lg-9 col-md-8">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden position-relative">
        <!-- Category Filter Overlay Pills -->
        <div class="position-absolute top-0 start-0 m-3 z-3 bg-white p-2 rounded-4 shadow-sm border d-none d-md-flex flex-wrap gap-1" style="max-width: 90%;">
          <button class="btn btn-xs btn-primary rounded-pill cat-filter-btn" data-cat="0" onclick="filterMapCategory(0)">All</button>
          <?php foreach ($categories as $c): ?>
            <button class="btn btn-xs btn-outline-secondary rounded-pill cat-filter-btn" data-cat="<?= $c['categoryid'] ?>" onclick="filterMapCategory(<?= $c['categoryid'] ?>)">
              <?= htmlspecialchars($c['categoryname']) ?>
            </button>
          <?php endforeach; ?>
        </div>

        <div id="leaflet-map" style="height: 650px;"></div>
      </div>
    </div>

    <!-- Side Directory Drawer (Right) -->
    <div class="col-lg-3 col-md-4">
      <div class="card border-0 shadow-sm rounded-4 h-100" style="max-height: 650px;">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold mb-0">Destinations Index</h6>
          <span class="badge bg-light text-dark border rounded-pill" id="list-count-badge"><?= count($places) ?> places</span>
        </div>
        
        <div class="card-body p-2 overflow-auto" id="places-drawer-list">
          <?php foreach ($places as $p): ?>
            <div class="p-2 mb-2 rounded-3 border bg-white place-list-card hover-shadow" 
                 data-place-id="<?= $p['placeid'] ?>"
                 data-category-id="<?= $p['categoryid'] ?>"
                 style="cursor: pointer; transition: background 0.2s;"
                 onclick="zoomToPlace(<?= $p['latitude'] ?>, <?= $p['longitude'] ?>)">
              <div class="d-flex align-items-center gap-2">
                <img src="<?= htmlspecialchars($p['imageurl']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                <div class="flex-grow-1 overflow-hidden">
                  <h6 class="fw-semibold mb-0 text-truncate small" style="font-size: 0.85rem;"><?= htmlspecialchars($p['name']) ?></h6>
                  <span class="text-primary small d-block" style="font-size: 0.75rem;">
                    <i class="bi bi-geo-alt"></i> <?= $p['distancekm'] ?> km • <?= htmlspecialchars($p['categoryname']) ?>
                  </span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/map.js"></script>
<script>
  const allPlaces = <?= json_encode($places, JSON_UNESCAPED_UNICODE) ?>;
  let activeCategoryId = 0;

  document.addEventListener('DOMContentLoaded', () => {
    initGalleMap('leaflet-map', {
      centerLat: 6.0355,
      centerLon: 80.2170,
      zoom: 12,
      showScopeCircle: true
    });

    renderPlacesOnMap(allPlaces, false);
    updateMapPlanCount();
  });

  function updateMapPlanCount() {
    const planned = getSelectedPlanPlaces();
    const countEl = document.getElementById('map-plan-count');
    if (countEl) countEl.textContent = planned.length;
  }

  function filterMapCategory(catId) {
    activeCategoryId = catId;
    document.querySelectorAll('.cat-filter-btn').forEach(btn => {
      const bCat = parseInt(btn.getAttribute('data-cat'), 10);
      if (bCat === catId) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary');
      } else {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-secondary');
      }
    });

    document.getElementById('btn-show-all').classList.add('active');
    document.getElementById('btn-show-plan').classList.remove('active');

    let filtered = allPlaces;
    if (catId > 0) {
      filtered = allPlaces.filter(p => parseInt(p.categoryid, 10) === catId);
    }

    renderPlacesOnMap(filtered, false);

    // Update list drawer visibility
    document.querySelectorAll('#places-drawer-list .place-list-card').forEach(card => {
      const cardCat = parseInt(card.getAttribute('data-category-id'), 10);
      if (catId === 0 || cardCat === catId) {
        card.style.display = 'block';
      } else {
        card.style.display = 'none';
      }
    });
  }

  function showOnlyPlannedPlaces() {
    const plannedIds = getSelectedPlanPlaces();
    if (plannedIds.length === 0) {
      alert('You have not added any attractions to your plan yet! Click "+ Add to Plan" on any attraction card or map marker.');
      return;
    }

    document.getElementById('btn-show-all').classList.remove('active');
    document.getElementById('btn-show-plan').classList.add('active');

    const plannedPlaces = allPlaces.filter(p => plannedIds.includes(parseInt(p.placeid, 10)));
    renderPlacesOnMap(plannedPlaces, true); // true draws route polyline!
  }

  function zoomToPlace(lat, lon) {
    if (mapInstance) {
      mapInstance.flyTo([lat, lon], 15, { duration: 1.2 });
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
