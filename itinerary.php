<?php
/**
 * EZEE2FIND - Itinerary Output & Print
 */
$pageTitle = "Your One-Day Galle Itinerary - EZEE2FIND";
$currentPage = "planner";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
  <!-- Print Header Only visible when printed-->
  <div class="print-header d-none">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h1>(E)ZEE2FIND - Smart Galle Day Itinerary</h1>
        <p>Prepared for One-Day Tourist Exploration • Sri Lanka</p>
      </div>
      <div class="text-end">
        <small id="print-date-display"></small><br>
        <small>Generated on: <?= date('d M Y, H:i') ?> SLST</small>
      </div>
    </div>
  </div>

  <!-- Top Action & Navigation Bar (Screen only) -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 no-print gap-3">
    <div>
      <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill mb-1 fw-semibold">
        <i class="bi bi-check2-circle me-1"></i> Optimized Schedule Ready
      </span>
      <h1 class="fw-bold mb-0">Your Galle One-Day Tour Itinerary</h1>
    </div>

    <div class="d-flex gap-2">
      <a href="planner.php" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-arrow-left me-1"></i> Edit Plan
      </a>
      <button type="button" class="btn btn-outline-primary rounded-pill px-3" onclick="copyItinerarySummary()">
        <i class="bi bi-share me-1"></i> Copy Summary
      </button>
      <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" onclick="window.print()" id="print-itinerary-btn">
        <i class="bi bi-printer me-1"></i> Print / Save PDF
      </button>
    </div>
  </div>

  <!-- Feasibility Warnings Alert Box-->
  <div id="itinerary-warnings-container" class="mb-4 d-none"></div>

  <!-- Trip Overview Statistics -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm text-center">
        <span class="text-muted small d-block">Scheduled Day</span>
        <strong class="fs-6 text-dark" id="stat-date">-</strong>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm text-center">
        <span class="text-muted small d-block">Time Window</span>
        <strong class="fs-6 text-primary" id="stat-time-window">-</strong>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm text-center">
        <span class="text-muted small d-block">Total Distance</span>
        <strong class="fs-6 text-info" id="stat-distance">-</strong>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="p-3 bg-white rounded-4 border shadow-sm text-center">
        <span class="text-muted small d-block">Transport Mode</span>
        <strong class="fs-6 text-success" id="stat-mode">-</strong>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: Chronological Timeline -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
          <h5 class="fw-bold mb-0"><i class="bi bi-calendar2-range text-primary me-2"></i> Tour Schedule Timeline</h5>
          <span class="badge bg-light text-dark border rounded-pill" id="stat-stops-count">0 stops</span>
        </div>
        <div class="card-body p-4">
          <div class="timeline-container" id="timeline-list">
            <!-- Timeline elements dynamically injected -->
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column-Budget Breakdown & Map -->
    <div class="col-lg-5">
      <!-- Budget Estimation Card -->
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0"><i class="bi bi-cash-coin text-success me-2"></i> Estimated Day Budget</h5>
            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill small">Estimated (LKR)</span>
          </div>

          <div class="budget-summary-card mb-3">
            <div class="small opacity-75">Estimated Total Trip Cost</div>
            <div class="budget-val-main" id="budget-total">LKR 0</div>
            <div class="small mt-1 opacity-75">Calculated for 1 person</div>
          </div>

          <ul class="list-group list-group-flush small mb-3">
            <li class="list-group-item d-flex justify-content-between px-0 py-2">
              <span class="text-muted"><i class="bi bi-ticket-perforated me-2"></i> Attraction Entrance Fees</span>
              <strong id="budget-fees">LKR 0</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0 py-2">
              <span class="text-muted"><i class="bi bi-fuel-pump me-2"></i> Local Transport / Fares</span>
              <strong id="budget-transport">LKR 0</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between px-0 py-2">
              <span class="text-muted"><i class="bi bi-cup-hot me-2"></i> Meals & Refreshment Allowance</span>
              <strong id="budget-meals">LKR 0</strong>
            </li>
          </ul>

          <div class="p-3 bg-light rounded-3 small text-muted">
            <i class="bi bi-info-circle text-primary me-1"></i>
            <strong>Note (REQ-29):</strong> All budget figures are approximate estimates and may vary based on vehicle negotiations, peak season pricing, and dietary choices.
          </div>
        </div>
      </div>

      <!-- Route Map Preview-->
      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-3">
          <h6 class="fw-bold mb-0"><i class="bi bi-map text-primary me-2"></i> Planned Sequence Map</h6>
        </div>
        <div class="card-body p-2">
          <div id="leaflet-map" style="height: 380px;"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/map.js"></script>
<script>
  let itineraryData = null;

  document.addEventListener('DOMContentLoaded', () => {
    const cached = sessionStorage.getItem('current_itinerary_result');
    if (!cached) {
      // If direct navigation with no cached plan, check localStorage or redirect
      const selected = getSelectedPlanPlaces();
      if (selected.length > 0) {
        window.location.href = 'planner.php';
      } else {
        alert('Please select attractions and generate your itinerary first.');
        window.location.href = 'attractions.php';
      }
      return;
    }

    try {
      itineraryData = JSON.parse(cached);
      renderItineraryPage(itineraryData);
    } catch (e) {
      console.error(e);
      window.location.href = 'planner.php';
    }
  });

  function renderItineraryPage(data) {
    const { summary, budget, warnings, timeline, places } = data;

    // Print Header date
    const printDate = document.getElementById('print-date-display');
    if (printDate) printDate.textContent = `Date: ${summary.date}`;

    // Stats bar
    document.getElementById('stat-date').textContent = summary.date;
    document.getElementById('stat-time-window').textContent = `${summary.start_time} - ${summary.estimated_finish}`;
    document.getElementById('stat-distance').textContent = `${summary.total_distance_km} km`;
    document.getElementById('stat-mode').textContent = `${summary.travel_mode} (${summary.pace})`;
    document.getElementById('stat-stops-count').textContent = `${summary.stops_count} Attractions`;

    // Warnings
    const warnContainer = document.getElementById('itinerary-warnings-container');
    if (warnings && warnings.length > 0) {
      warnContainer.classList.remove('d-none');
      let warnHtml = `
        <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-0">
          <div class="d-flex gap-3">
            <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
            <div>
              <h6 class="fw-bold mb-1">Itinerary Feasibility & Advisory Alerts</h6>
              <ul class="mb-0 small ps-3">
                ${warnings.map(w => `<li class="mb-1">${w}</li>`).join('')}
              </ul>
            </div>
          </div>
        </div>
      `;
      warnContainer.innerHTML = warnHtml;
    }

    // Budget
    document.getElementById('budget-total').textContent = formatLKR(budget.total_estimated);
    document.getElementById('budget-fees').textContent = formatLKR(budget.entrance_fees);
    document.getElementById('budget-transport').textContent = formatLKR(budget.estimated_transport);
    document.getElementById('budget-meals').textContent = formatLKR(budget.meal_budget);

    // Timeline Items
    const timelineEl = document.getElementById('timeline-list');
    timelineEl.innerHTML = '';

    timeline.forEach(item => {
      if (item.type === 'travel') {
        const html = `
          <div class="timeline-item">
            <div class="timeline-icon-node travel"><i class="bi bi-arrow-down"></i></div>
            <div class="timeline-travel-box d-flex justify-content-between align-items-center">
              <div>
                <strong>${item.start_time_str} - ${item.end_time_str}</strong> • Transit to ${item.to_name}
                <span class="text-muted ms-1">(${item.distance_km} km)</span>
              </div>
              <span class="badge bg-secondary rounded-pill small">${item.duration_mins} mins</span>
            </div>
          </div>
        `;
        timelineEl.insertAdjacentHTML('beforeend', html);
      } else if (item.type === 'place') {
        const html = `
          <div class="timeline-item">
            <div class="timeline-icon-node">${item.sequence}</div>
            <div class="timeline-card">
              <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <div>
                  <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill small mb-1">
                    ${item.category}
                  </span>
                  <h5 class="fw-bold mb-0 text-dark">${item.name}</h5>
                </div>
                <div class="text-end">
                  <span class="badge bg-light text-primary border fw-bold fs-6">
                    ${item.start_time_str} - ${item.end_time_str}
                  </span>
                  <div class="small text-muted mt-1">${item.duration_mins} mins stay</div>
                </div>
              </div>

              <div class="row g-2 small text-muted mb-2">
                <div class="col-sm-6">
                  <i class="bi bi-ticket-perforated text-success me-1"></i>
                  ${item.entrance_fee > 0 ? 'Fee: ' + formatLKR(item.entrance_fee) : 'Free Admission'}
                </div>
                <div class="col-sm-6">
                  <i class="bi bi-sun text-warning me-1"></i> Best: ${item.besttime}
                </div>
              </div>

              ${item.restrictions ? `
                <div class="p-2 rounded bg-light border-start border-3 border-warning small text-secondary mb-2">
                  <strong>Etiquette:</strong> ${item.restrictions}
                </div>
              ` : ''}

              ${item.traveltips ? `
                <div class="small text-muted fst-italic">
                  <i class="bi bi-lightbulb text-info me-1"></i> ${item.traveltips}
                </div>
              ` : ''}
            </div>
          </div>
        `;
        timelineEl.insertAdjacentHTML('beforeend', html);
      } else if (item.type === 'break') {
        const html = `
          <div class="timeline-item">
            <div class="timeline-icon-node break"><i class="bi bi-cup-hot-fill"></i></div>
            <div class="p-3 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-25">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <strong class="text-warning-emphasis">${item.title}</strong>
                <span class="badge bg-warning text-dark">${item.start_time_str} - ${item.end_time_str} (${item.duration_mins} mins)</span>
              </div>
              <p class="small text-muted mb-0">${item.description}</p>
            </div>
          </div>
        `;
        timelineEl.insertAdjacentHTML('beforeend', html);
      }
    });

    // Map Route Display
    initGalleMap('leaflet-map', {
      centerLat: 6.0355,
      centerLon: 80.2170,
      zoom: 12,
      showScopeCircle: true
    });
    renderPlacesOnMap(places, true);
  }

  function copyItinerarySummary() {
    if (!itineraryData) return;
    const { summary, places, budget } = itineraryData;
    let text = `🧭 EZEE2FIND Galle One-Day Trip Plan\n`;
    text += `Date: ${summary.date} | Schedule: ${summary.start_time} to ${summary.estimated_finish}\n`;
    text += `Transport: ${summary.travel_mode} | Total Distance: ${summary.total_distance_km} km\n\n`;
    text += `Scheduled Stops:\n`;
    places.forEach((p, i) => {
      text += `${i + 1}. ${p.name} (~${p.distancekm} km)\n`;
    });
    text += `\nEstimated Budget: ${formatLKR(budget.total_estimated)} (Fees: ${formatLKR(budget.entrance_fees)}, Transit: ${formatLKR(budget.estimated_transport)}, Meals: ${formatLKR(budget.meal_budget)})\n`;

    navigator.clipboard.writeText(text).then(() => {
      showToastNotification('Itinerary summary copied to clipboard!', 'success');
    }).catch(() => {
      alert(text);
    });
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
