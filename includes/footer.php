</main>

<!-- Floating Planner Counter Button-->
<a href="planner.php" class="planner-pill-floating d-none" id="floating-plan-pill">
  <i class="bi bi-calendar-check-fill fs-5"></i>
  <span>Plan Day Trip (<span id="floating-plan-count">0</span>)</span>
  <i class="bi bi-arrow-right-short fs-4"></i>
</a>

<!-- Global Footer -->
<footer>
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <h5 class="text-white fw-bold mb-3 d-flex align-items-center">
          <i class="bi bi-compass text-primary me-2"></i> (E)ZEE2FIND GALLE
        </h5>
        <p class="small text-black mb-3">
          Smart Tourist Day-Visit Planner and Information System for Galle and surrounding destinations within a 25 km radius. Designed to eliminate fragmented travel info and optimize one-day itineraries with reduced backtracking.
        </p>
        <div class="d-flex align-items-center gap-2 small text-light">
          <i class="bi bi-clock-history text-info"></i>
          <span>Sri Lanka Time: <strong id="sl-live-time"><?= date('H:i') ?></strong></span>
        </div>
      </div>

      <div class="col-lg-2 col-md-6">
        <h6 class="text-white fw-semibold mb-3">Quick Navigation</h6>
        <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
          <li><a href="index.php"><i class="bi bi-chevron-right me-1"></i> Home</a></li>
          <li><a href="attractions.php"><i class="bi bi-chevron-right me-1"></i> Explore Attractions</a></li>
          <li><a href="map.php"><i class="bi bi-chevron-right me-1"></i> Interactive Map</a></li>
          <li><a href="planner.php"><i class="bi bi-chevron-right me-1"></i> Day Planner</a></li>
          <li><a href="user_guide.php"><i class="bi bi-chevron-right me-1"></i> User Guide</a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="text-white fw-semibold mb-3">Academic References</h6>
        <ul class="list-unstyled small mb-0 d-flex flex-column gap-2 text-black">
          <li>UNESCO World Heritage Centre </li>
          <li>Sri Lanka Tourism Promotion Bureau (SLTPB) </li>
          <li>Department of National Museums, Sri Lanka </li>
          <li>Martin Wickramasinghe Trust </li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-6">
        <h6 class="text-white fw-semibold mb-3">Traveler Notice </h6>
        <div class="p-3 rounded bg-dark border border-secondary border-opacity-25 small text-light">
          <i class="bi bi-info-circle text-warning me-1"></i>
          <strong>Notice:</strong> Opening hours, admission fees, and transit times are approximate and subject to change based on weather, tides, and local events. Please verify on-site.
        </div>
      </div>
    </div>

    <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
      <div>
        &copy; <?= date('Y') ?> <strong>(E)ZEE2FIND</strong>
      </div>
      <div class="d-flex gap-3">
        <a href="user_guide.php" class="small">Documentation</a>
        <span class="text-secondary">•</span>
        <a href="admin/login.php" class="small">Admin Login</a>
      </div>
    </div>
  </div>
</footer>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- App Scripts -->
<script src="assets/js/main.js"></script>

<script>
  // Live Sri Lanka Time clock 
  function updateSLTime() {
    try {
      const now = new Date();
      const options = { timeZone: 'Asia/Colombo', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
      const formatter = new Intl.DateTimeFormat([], options);
      const el = document.getElementById('sl-live-time');
      if (el) el.textContent = formatter.format(now) + ' SLST';
    } catch (e) {}
  }
  setInterval(updateSLTime, 1000);
  updateSLTime();
</script>

</body>
</html>
