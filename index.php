<?php
/**
 * EZEE2FIND - Home Page
 */
$pageTitle = "EZEE2FIND - Smart Tourist Day-Visit Planner for Galle";
$currentPage = "home";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

try {
    $pdo = getDB();
    // Fetch featured attractions (e.g. Galle Fort, Lighthouse, Unawatuna, Handunugoda, Peace Pagoda, Maritime Museum)
    $stmt = $pdo->query("SELECT p.*, c.categoryname 
                         FROM Place p 
                         JOIN Category c ON p.categoryid = c.categoryid 
                         WHERE p.isdeleted = 0 
                         ORDER BY p.distancekm ASC LIMIT 6");
    $featuredPlaces = $stmt->fetchAll();

    // Categories
    $catStmt = $pdo->query("SELECT * FROM Category ORDER BY categoryid ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $featuredPlaces = [];
    $categories = [];
}
?>

<!-- Hero Section -->
<section class="hero-section" >
  <div class="container text-center py-4" >
    <div class="hero-stats-badge">
      <i class="bi bi-geo-alt-fill text-warning"></i>
      <span>UNESCO Heritage & Southern Coast • 25 km Study Scope</span>
    </div>
    
    <h1 class="hero-title mb-3">Plan Your Perfect One-Day Visit to Galle</h1>
    <p class="hero-subtitle mx-auto mb-4">
      Discover historical bastions, pristine beaches, sacred rock temples, and lush tea estates. 
      Generate a realistic, backtracking-free itinerary customized to your travel mode and pace.
    </p>

    <!-- Quick Search Box -->
    <div class="row justify-content-center mb-4">
      <div class="col-lg-8 col-md-10">
        <form action="attractions.php" method="GET" class="p-2 bg-white rounded-pill shadow-lg d-flex align-items-center gap-2">
          <i class="bi bi-search text-muted ms-3 fs-5"></i>
          <input type="text" name="q" class="form-control border-0 shadow-none px-2" placeholder="Search Fort ramparts, beaches, museums, entry fees...">
          <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
            Explore
          </button>
        </form>
      </div>
    </div>

    <!-- Quick Category Pills -->
    <div class="d-flex flex-wrap justify-content-center gap-2">
      <a href="attractions.php" class="btn btn-sm btn-light rounded-pill px-3 py-2 shadow-sm text-dark fw-medium">
        <i class="bi bi-stars text-warning me-1"></i> All Attractions
      </a>
      <?php foreach ($categories as $cat): ?>
        <a href="attractions.php?category=<?= $cat['categoryid'] ?>" class="btn btn-sm  btn-light  rounded-pill px-3 py-2 fw-medium">
          <i class="bi <?= htmlspecialchars($cat['icon']) ?> me-1"></i> <?= htmlspecialchars($cat['categoryname']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- System Features / Key Highlights Bar -->
<section class="container mb-5">
  <div class="row g-3 text-center">
    <div class="col-6 col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm h-100">
        <div class="display-6 text-primary mb-1"><i class="bi bi-compass"></i></div>
        <h6 class="fw-bold mb-1">15+ Curated Spots</h6>
        <p class="small text-muted mb-0">Covering Galle town & coastal 25 km radius</p>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm h-100">
        <div class="display-6 text-success mb-1"><i class="bi bi-signpost-split"></i></div>
        <h6 class="fw-bold mb-1">Smart Routing</h6>
        <p class="small text-muted mb-0">Backtracking reduction & travel modes</p>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 bg-red rounded-4 border shadow-sm h-100">
        <div class="display-6 text-warning mb-1"><i class="bi bi-clock-history"></i></div>
        <h6 class="fw-bold mb-1">Schedule Checks</h6>
        <p class="small text-muted mb-0">Opening hours & day-window alerts</p>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm h-100">
        <div class="display-6 text-info mb-1"><i class="bi bi-wallet2"></i></div>
        <h6 class="fw-bold mb-1">Estimated Budget</h6>
        <p class="small text-muted mb-0">Tuk-tuk fares, tickets & meals in LKR</p>
      </div>
    </div>
  </div>
</section>

<!-- Featured Attractions Grid -->
<section class="container mb-5">
  <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
    <div>
      <span class="text-primary fw-bold text-uppercase small tracking-wide">Handpicked Highlights</span>
      <h2 class="fw-bold mb-0">Featured Destinations</h2>
    </div>
    <a href="attractions.php" class="btn btn-outline-primary rounded-pill px-3 mt-2 mt-md-0">
      View All 15 Attractions <i class="bi bi-arrow-right ms-1"></i>
    </a>
  </div>

  <div class="row g-4">
    <?php foreach ($featuredPlaces as $place): ?>
      <div class="col-lg-4 col-md-6">
        <div class="place-card">
          <div class="place-card-img-wrap">
            <img src="<?= htmlspecialchars($place['imageurl']) ?>" alt="<?= htmlspecialchars($place['name']) ?>" class="place-card-img">
            <span class="place-category-badge">
              <?= htmlspecialchars($place['categoryname']) ?>
            </span>
            <span class="place-distance-badge">
              <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= $place['distancekm'] ?> km
            </span>
          </div>
          
          <div class="place-card-body">
            <h5 class="place-card-title"><?= htmlspecialchars($place['name']) ?></h5>
            <div class="place-card-meta">
              <span><i class="bi bi-clock me-1 text-primary"></i> <?= $place['visitduration'] ?> mins</span>
              <span><i class="bi bi-ticket-perforated me-1 text-success"></i> <?= $place['entrancefee'] > 0 ? 'LKR ' . number_format($place['entrancefee']) : 'Free Entry' ?></span>
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
</section>

<!-- How EZEE2FIND Works -->
<section class="bg-white py-5 border-top border-bottom mb-5">
  <div class="container text-center">
    <span class="text-primary fw-bold text-uppercase small">Simple & Practical</span>
    <h2 class="fw-bold mb-5">How It Works in 3 Steps</h2>

    <div class="row g-4">
      <div class="col-md-4">
        <div class="p-3">
          <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
            <i class="bi bi-check2-square fs-2"></i>
          </div>
          <h5 class="fw-bold">1. Select Sights</h5>
          <p class="text-muted small">
            Browse our curated 15 places across Galle Fort, Unawatuna, Koggala, and Hikkaduwa. Click "Add to Plan" on any attraction you wish to visit.
          </p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3">
          <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
            <i class="bi bi-sliders fs-2"></i>
          </div>
          <h5 class="fw-bold">2. Set Travel Mode & Pace</h5>
          <p class="text-muted small">
            Choose your preferred transport (walking, tuk-tuk, scooter, or car), set your start time, select a relaxed or active pace, and include lunch breaks.
          </p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-3">
          <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
            <i class="bi bi-map-fill fs-2"></i>
          </div>
          <h5 class="fw-bold">3. Get Optimized Day Plan</h5>
          <p class="text-muted small">
            The smart engine sequences your stops to minimize backtracking, verifies opening hours, estimates budget in LKR, and offers one-click PDF printing.
          </p>
        </div>
      </div>
    </div>

    <div class="mt-4">
      <a href="planner.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
        <i class="bi bi-calendar2-plus me-2"></i> Start Planning Now
      </a>
    </div>
  </div>
</section>

<!-- Interactive Map Teaser -->
<section class="container mb-5">
  <div class="row align-items-center bg-secondary text-white rounded-4 p-4 p-lg-5 shadow-lg g-4" >
    <div class="col-lg-7">
      <span class="badge bg-white text-primary mb-2 px-3 py-2 fw-semibold">Interactive Geographic View</span>
      <h2 class="fw-bold mb-3">Explore Galle on the Map</h2>
      <p class="mb-4 text-white-50">
        Understand distances between Galle Fort ramparts, Rumassala cliffs, and Koggala lake. Filter by categories, preview opening hours, and trace your day tour route visually.
      </p>
      <a href="map.php" class="btn btn-light text-primary fw-bold rounded-pill px-4 py-2">
        <i class="bi bi-map me-1"></i> Open Interactive Map
      </a>
    </div>
    <div class="col-lg-5 text-center">
      <img src="https://images.unsplash.com/photo-1546708973-b339540b5162?auto=format&fit=crop&w=600&q=80" alt="Galle Fort Lighthouse Map View" class="img-fluid rounded-4 shadow">
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
