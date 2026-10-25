<?php
/**
 * EZEE2FIND - One-Day Itinerary Planner Interface
 */
$pageTitle = "One-Day Itinerary Planner - EZEE2FIND";
$currentPage = "planner";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$allPlaces = $pdo->query("SELECT p.*, c.categoryname 
                          FROM Place p 
                          JOIN Category c ON p.categoryid = c.categoryid 
                          WHERE p.isdeleted = 0 
                          ORDER BY p.distancekm ASC")->fetchAll();
?>

<div class="container py-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h1 class="fw-bold mb-1">One-Day Visit Itinerary Planner</h1>
      <p class="text-muted mb-0">Customize your stops, travel modes, and pace to generate a realistic schedule.</p>
    </div>
    <div class="mt-2 mt-md-0 d-flex gap-2">
      <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="clearAllPlanPlaces()">
        <i class="bi bi-trash3 me-1"></i> Clear Selected Stops
      </button>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: Selected Attractions & Quick Selector -->
    <div class="col-lg-7">
      <!-- Selected Stops Card  -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
          <h5 class="fw-bold mb-0">
            <i class="bi bi-geo-alt text-primary me-2"></i> Your Selected Attractions (<span id="selected-count">0</span>)
          </h5>
          <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill small">Drag or click to reorder</span>
        </div>
        <div class="card-body p-3">
          <!-- Empty State -->
          <div id="no-places-selected" class="text-center py-4 d-none">
            <div class="display-6 text-muted mb-2"><i class="bi bi-signpost-2"></i></div>
            <h6 class="fw-bold text-secondary">No attractions added to your day plan yet</h6>
            <p class="text-muted small mb-3">Choose from the recommendations below or browse the full catalog.</p>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" onclick="loadSuggestedSamplePlan()">
              <i class="bi bi-magic me-1"></i> Load Popular Galle 1-Day Highlights
            </button>
          </div>

          <!-- Selected List -->
          <div id="selected-places-list" class="d-flex flex-column gap-2"></div>
        </div>
      </div>

      <!-- Quick Add from All Attractions-->
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-3">
          <h6 class="fw-bold mb-0"><i class="bi bi-plus-circle text-success me-2"></i> Add More Places from Galle Catalog</h6>
        </div>
        <div class="card-body p-3" style="max-height: 400px; overflow-y: auto;">
          <div class="row g-2" id="available-places-quick-add">
            <?php foreach ($allPlaces as $p): ?>
              <div class="col-md-6">
                <div class="p-2 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2 hover-shadow">
                  <div class="overflow-hidden">
                    <strong class="d-block small text-truncate" title="<?= htmlspecialchars($p['name']) ?>"><?= htmlspecialchars($p['name']) ?></strong>
                    <span class="text-muted" style="font-size: 0.75rem;">
                      <i class="bi bi-tag"></i> <?= htmlspecialchars($p['categoryname']) ?> • <?= $p['distancekm'] ?> km
                    </span>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary rounded-pill flex-shrink-0" data-plan-btn="<?= $p['placeid'] ?>" onclick="togglePlanPlace(<?= $p['placeid'] ?>); refreshPlannerView();">
                    <i class="bi bi-plus"></i> Add
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Preferences, Budget & Generate-->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top: 85px;">
        <h5 class="fw-bold mb-3"><i class="bi bi-sliders text-primary me-2"></i> Visit Preferences</h5>
        
        <form id="planner-form" onsubmit="handleGenerateItinerary(event)">
          <!-- Visit Date -->
          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Visit Date</label>
            <input type="date" id="plan-date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>

          <!-- Time Window -->
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-semibold text-muted">Trip Start Time</label>
              <input type="time" id="plan-start-time" class="form-control" value="08:30" required>
            </div>
            <div class="col-6">
              <label class="form-label small fw-semibold text-muted">Target End Time</label>
              <input type="time" id="plan-end-time" class="form-control" value="18:30" required>
            </div>
          </div>

          <!-- Travel Mode-->
          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Travel Mode</label>
            <select id="plan-travel-mode" class="form-select">
              <option value="tuk-tuk" selected>🛺 Tuk-Tuk / Three-Wheeler (Recommended for Galle)</option>
              <option value="walking">🚶 Walking (Best for Fort & surrounding ramparts)</option>
              <option value="scooter">🛵 Scooter / Motorbike Rental</option>
              <option value="private_car">🚗 Private Car / Taxi with Air-Con</option>
            </select>
          </div>

          <!-- Travel Pace-->
          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Travel Pace</label>
            <select id="plan-pace" class="form-select">
              <option value="relaxed">🌿 Relaxed & Slow (Extra buffer, longer stays)</option>
              <option value="moderate" selected>⚖️ Balanced & Moderate (Standard duration)</option>
              <option value="fast">⚡ Fast-Paced (See everything, quick stops)</option>
            </select>
          </div>

          <!-- Visitor Type (Local vs Foreign Ticket Pricing) -->
          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Visitor Admission Pricing</label>
            <select id="plan-visitor-type" class="form-select">
              <option value="local" selected>🇱🇰 Sri Lankan Resident / Local Rates</option>
              <option value="foreign">🌍 Foreign Tourist Rates</option>
            </select>
          </div>

          <!-- Buffers & Options-->
          <div class="bg-light p-3 rounded-3 mb-3">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="plan-include-lunch" checked>
              <label class="form-check-label small fw-semibold" for="plan-include-lunch">
                Include Midday Meal Break (45 mins)
              </label>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="plan-optimize-route" checked>
              <label class="form-check-label small fw-semibold" for="plan-optimize-route">
                Reduce Backtracking (Route Optimization)
              </label>
            </div>
          </div>

          <!-- Budget Preferences-->
          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">
              Estimated Food & Drinks Budget (LKR / person)
            </label>
            <input type="number" id="plan-food-budget" class="form-control" value="2000" step="100" min="0">
          </div>

          <div class="d-grid mt-4">
            <button type="submit" id="btn-generate-plan" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
              <i class="bi bi-lightning-charge-fill me-1"></i> Generate Optimized Itinerary
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  const allAttractionsMap = <?= json_encode(array_column($allPlaces, null, 'placeid'), JSON_UNESCAPED_UNICODE) ?>;

  document.addEventListener('DOMContentLoaded', () => {
    refreshPlannerView();
  });

  function refreshPlannerView() {
    const selectedIds = getSelectedPlanPlaces();
    const countEl = document.getElementById('selected-count');
    const emptyEl = document.getElementById('no-places-selected');
    const listEl = document.getElementById('selected-places-list');

    if (countEl) countEl.textContent = selectedIds.length;

    if (selectedIds.length === 0) {
      emptyEl.classList.remove('d-none');
      listEl.innerHTML = '';
      return;
    }

    emptyEl.classList.add('d-none');
    listEl.innerHTML = '';

    selectedIds.forEach((pid, idx) => {
      const place = allAttractionsMap[pid];
      if (!place) return;

      const itemHtml = `
        <div class="p-3 border rounded-3 bg-white shadow-sm d-flex align-items-center justify-content-between gap-3" data-id="${pid}">
          <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
              ${idx + 1}
            </span>
            <img src="${place.imageurl}" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
            <div>
              <h6 class="fw-bold mb-0 text-dark">${place.name}</h6>
              <span class="text-muted small">
                <i class="bi bi-clock"></i> ${place.visitduration} mins • ${place.distancekm} km from Galle
              </span>
            </div>
          </div>
          <div class="d-flex align-items-center gap-1">
            ${idx > 0 ? `<button class="btn btn-sm btn-light border" onclick="movePlaceOrder(${idx}, -1)" title="Move Up"><i class="bi bi-chevron-up"></i></button>` : ''}
            ${idx < selectedIds.length - 1 ? `<button class="btn btn-sm btn-light border" onclick="movePlaceOrder(${idx}, 1)" title="Move Down"><i class="bi bi-chevron-down"></i></button>` : ''}
            <button class="btn btn-sm btn-outline-danger border" onclick="togglePlanPlace(${pid}); refreshPlannerView();" title="Remove"><i class="bi bi-x-lg"></i></button>
          </div>
        </div>
      `;
      listEl.insertAdjacentHTML('beforeend', itemHtml);
    });
  }

  function movePlaceOrder(index, direction) {
    const list = getSelectedPlanPlaces();
    const newIndex = index + direction;
    if (newIndex >= 0 && newIndex < list.length) {
      const temp = list[index];
      list[index] = list[newIndex];
      list[newIndex] = temp;
      saveSelectedPlanPlaces(list);
      refreshPlannerView();
    }
  }

  function loadSuggestedSamplePlan() {
    // Galle Fort, Lighthouse, Maritime Museum, Peace Pagoda, Unawatuna Beach
    const sampleIds = [1, 2, 5, 8, 6];
    saveSelectedPlanPlaces(sampleIds);
    refreshPlannerView();
    showToastNotification('Loaded 5 classic Galle destinations!', 'success');
  }

  async function handleGenerateItinerary(e) {
    e.preventDefault();
    const selectedIds = getSelectedPlanPlaces();

    if (selectedIds.length === 0) {
      alert('Please select at least 1 attraction to generate a day itinerary.');
      return;
    }

    const payload = {
      place_ids: selectedIds,
      date: document.getElementById('plan-date').value,
      start_time: document.getElementById('plan-start-time').value,
      end_time: document.getElementById('plan-end-time').value,
      travel_mode: document.getElementById('plan-travel-mode').value,
      pace: document.getElementById('plan-pace').value,
      visitor_type: document.getElementById('plan-visitor-type').value,
      include_lunch: document.getElementById('plan-include-lunch').checked,
      optimize_route: document.getElementById('plan-optimize-route').checked,
      meal_budget: parseFloat(document.getElementById('plan-food-budget').value || 2000)
    };

    const btn = document.getElementById('btn-generate-plan');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Calculating Optimal Route...';

    try {
      const resp = await fetch('api/plan_itinerary.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await resp.json();

      if (data.status === 'success') {
        // Cache the result in sessionstorage so    itinerary.php displays it 
        sessionStorage.setItem('current_itinerary_result', JSON.stringify(data));
        window.location.href = 'itinerary.php';
      } else {
        alert('Planning error: ' + (data.message || 'Unknown error'));
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> Generate Optimized Itinerary';
      }
    } catch (err) {
      console.error(err);
      alert('Server communication error. Please check your connection.');
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> Generate Optimized Itinerary';
    }
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
