<?php
/**
 * EZEE2FIND - User Guide & System Documentation
 */
$pageTitle = "User Guide & Help Documentation - EZEE2FIND";
$currentPage = "guide";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <!-- Breadcrumb -->
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">User Guide</li>
    </ol>
  </nav>

  <div class="row g-4">
    <!-- Sidebar navigation -->
    <div class="col-lg-3">
      <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 85px;">
        <div class="card-body p-3">
          <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-book me-2"></i> Guide Contents</h6>
          <div class="list-group list-group-flush small">
            <a href="#overview" class="list-group-item list-group-item-action border-0 py-2">1. System Overview</a>
            <a href="#browsing" class="list-group-item list-group-item-action border-0 py-2">2. Browsing & Filtering</a>
            <a href="#details" class="list-group-item list-group-item-action border-0 py-2">3. Attraction Profiles</a>
            <a href="#map" class="list-group-item list-group-item-action border-0 py-2">4. Using the Interactive Map</a>
            <a href="#planner" class="list-group-item list-group-item-action border-0 py-2">5. Building a One-Day Plan</a>
            <a href="#budget" class="list-group-item list-group-item-action border-0 py-2">6. Budget & Warnings</a>
            <a href="#print" class="list-group-item list-group-item-action border-0 py-2">7. Printing & PDF Export</a>
            <a href="#admin" class="list-group-item list-group-item-action border-0 py-2">8. Administrator Portal</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="col-lg-9">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 p-lg-5">
          <div class="border-bottom pb-4 mb-4">
            <h2 class="fw-bold mb-2">EZEE2FIND User Guide & Help System</h2>
            <p class="text-muted mb-0">
              Clear, step-by-step instructions for tourists and system administrators to maximize their one-day exploration of Galle, Sri Lanka.
            </p>
          </div>

          <!-- Section 1 -->
          <section id="overview" class="mb-5">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-compass text-primary me-2"></i> 1. System Overview</h4>
            <p>
              <strong>EZEE2FIND</strong> is a smart tourist day-visit planner and information system designed specifically for Galle town and the surrounding coastal strip within a <strong>25 km radius</strong>. It consolidates scattered tourist information into a single unified platform and intelligently organizes your visit sequence to reduce unnecessary backtracking and travel delays.
            </p>
          </section>

          <!-- Section 2 -->
          <section id="browsing" class="mb-5">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-search text-primary me-2"></i> 2. Browsing & Filtering Attractions</h4>
            <p>To discover destinations matching your exact preferences:</p>
            <ul>
              <li>Navigate to <strong>Attractions</strong> via the top navigation bar.</li>
              <li><strong>Keyword Search:</strong> Type names, descriptions, or cultural tags (e.g., <em>"Fort"</em>, <em>"murals"</em>, <em>"snorkeling"</em>, <em>"tea"</em>).</li>
              <li><strong>Category Filter:</strong> Filter across 6 distinct categories: <em>Heritage & Architecture</em>, <em>Museums & Culture</em>, <em>Religious & Sacred</em>, <em>Beach & Coastal</em>, <em>Nature & Wildlife</em>, and <em>Culture & Agriculture</em>.</li>
              <li><strong>Distance Scope:</strong> Limit results to places within walking distance (≤ 2 km), short ride (≤ 8 km), or full 25 km scope.</li>
              <li><strong>Entrance Cost:</strong> Toggle between free admissions and paid sights.</li>
              <li><strong>Accessibility Check:</strong> Check the wheelchair box to filter for destinations offering step-free or level access.</li>
              <li>Click <strong>Reset</strong> (<i class="bi bi-arrow-counterclockwise"></i>) at any time to clear filters.</li>
            </ul>
          </section>

          <!-- Section 3 -->
          <section id="details" class="mb-5">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i> 3. Viewing Attraction Profiles</h4>
            <p>Click <strong>View Details</strong> on any attraction card to inspect its full profile:</p>
            <ul>
              <li><strong>Operating Schedule:</strong> Opening and closing times and recommended stay duration.</li>
              <li><strong>Dual Admission Fees:</strong> Breakdown of Sri Lankan local resident rates vs. foreign tourist rates in Sri Lankan Rupees (LKR).</li>
              <li><strong>Cultural Etiquette & Dress Codes:</strong> Crucial for religious places like <em>Dutch Reformed Church</em>, <em>Yatagala Temple</em>, and <em>Japanese Peace Pagoda</em> (e.g., covering shoulders/knees, removing shoes).</li>
              <li><strong>Travel Advice:</strong> Peak sun hours, best photography timings (sunset at Flag Rock, sunrise at Koggala stilt fishermen), hydration reminders.</li>
              <li><strong>Nearby Facilities:</strong> Verified locations of nearby parking lots, municipal restrooms, public transit hubs, and tourist police stations.</li>
            </ul>
          </section>

          <!-- Section 4 -->
          <section id="map" class="mb-5">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-map text-primary me-2"></i> 4. Using the Interactive Map</h4>
            <p>The interactive map provides visual geographic awareness:</p>
            <ul>
              <li>Click <strong>Interactive Map</strong> in the navigation header.</li>
              <li>Pins are color-coded by category. Click any pin to view thumbnail, distance from Galle, opening hours, and an <strong>Add to Plan</strong> button.</li>
              <li>Click <strong>My Planned Stops</strong> to filter the map view exclusively to your chosen stops with a dashed polyline showing the travel route.</li>
              <li>The dashed boundary circle illustrates the system's 25 km study radius.</li>
            </ul>
          </section>

          <!-- Section 5 -->
          <section id="planner" class="mb-5">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-calendar2-range text-primary me-2"></i> 5. Building & Optimizing a One-Day Plan</h4>
            <p>Generating a realistic schedule takes three intuitive steps:</p>
            <ol>
              <li><strong>Select Attractions:</strong> Add between 2 to 6 destinations to your plan using the "+ Add to Plan" buttons. Use the Up/Down arrows to adjust order, or leave order optimization enabled.</li>
              <li><strong>Set Preferences:</strong>
                <ul>
                  <li><strong>Travel Mode:</strong> Select Tuk-Tuk (Three-wheeler), Walking, Scooter, or Private Car. The algorithm calculates realistic transit speed and parking time based on your mode.</li>
                  <li><strong>Travel Pace:</strong> Choose <em>Relaxed</em> (longer stay + 20 min buffers), <em>Moderate</em>, or <em>Fast-Paced</em> (abbreviated highlights).</li>
                  <li><strong>Time Window:</strong> Specify your day start time (e.g. 08:30) and required end time (e.g. 18:30).</li>
                  <li><strong>Meal & Buffer Rest:</strong> Leave checked to automatically schedule a midday lunch break between 12:30 and 13:30.</li>
                  <li><strong>Reduce Backtracking:</strong> Check the box to let the Greedy Nearest-Neighbor algorithm sort your stops in geographic order starting from Galle Railway Hub.</li>
                </ul>
              </li>
              <li>Click <strong>Generate Optimized Itinerary</strong>.</li>
            </ol>
          </section>

          <!-- Section 6 -->
          <section id="budget" class="mb-5">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-wallet2 text-primary me-2"></i> 6. Budget Estimation & Feasibility Alerts</h4>
            <p>The planner generates both time feasibility checks and budget estimations:</p>
            <ul>
              <li><strong>Time Feasibility Alerts:</strong> If the generated plan extends past your desired end time, or if an attraction will be closed at your projected arrival hour, an amber warning box informs you with exact time deltas so you can adjust your itinerary.</li>
              <li><strong>Budget Breakdown</strong>
                <ul>
                  <li>Calculates exact ticket fees for your chosen visitor type (Local / Foreign).</li>
                  <li>Calculates transport fares based on actual road distance traveled and travel mode rates.</li>
                  <li>Incorporates your food & beverage allowance.</li>
                  <li>Displays total estimated expenditure in <strong>LKR</strong>.</li>
                </ul>
              </li>
            </ul>
          </section>

          <!-- Section 7 -->
          <section id="print" class="mb-5">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-printer text-primary me-2"></i> 7. Printing & PDF Export</h4>
            <p>
              When your itinerary is generated on the results screen, click <strong>Print / Save PDF</strong>. 
              The page utilizes dedicated print stylesheets to hide headers, navigation, and unnecessary buttons, formatting your full schedule, travel legs, notes, and budget table into an elegant, clean document suitable for printing or saving as a PDF via your browser's Print dialog.
            </p>
          </section>

          <!-- Section 8 -->
          <section id="admin" class="mb-0">
            <h4 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock text-primary me-2"></i> 8. Administrator Management Guide </h4>
            <p>Authorized administrators maintain destination accuracy:</p>
            <ul>
              <li>Access the admin portal via <a href="assets/admin/login.php" class="text-primary fw-semibold">Admin Login</a>.</li>
              
              <li><strong>Add Place</strong> Enter mandatory fields (Name, Category, Coordinates, Hours, Fees, Duration, Tips).</li>
              <li><strong>Edit Place:</strong> Update seasonal entrance fees, opening adjustments, or road construction notes.</li>
              <li><strong>Delete:</strong> Safe deletion removes outdated places from user-facing search without breaking historical itineraries.</li>
            </ul>
          </section>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
