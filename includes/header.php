<?php
/**
 * EZEE2FIND - Global Header Template
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = $pageTitle ?? 'EZEE2FIND - Galle Day-Visit Planner';
$currentPage = $currentPage ?? 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  
  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  
  <!-- Custom Styles -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/print.css" media="print">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <i class="bi bi-compass text-primary me-2 fs-4"></i>
      <span>(E)ZEE2FIND</span>
      
    </a>
    
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item">
          <a class="nav-link <?= ($currentPage === 'home') ? 'active' : '' ?>" href="index.php">
            <i class="bi bi-house me-1"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($currentPage === 'attractions') ? 'active' : '' ?>" href="attractions.php">
            <i class="bi bi-grid me-1"></i> Attractions
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($currentPage === 'map') ? 'active' : '' ?>" href="map.php">
            <i class="bi bi-map me-1"></i> Interactive Map
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($currentPage === 'planner') ? 'active' : '' ?>" href="planner.php">
            <i class="bi bi-calendar2-range me-1"></i> Day Planner
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= ($currentPage === 'guide') ? 'active' : '' ?>" href="user_guide.php">
            <i class="bi bi-book me-1"></i> User Guide
          </a>
        </li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
          <a class="btn btn-outline-primary btn-sm rounded-pill px-3" href="/ezee2find/assets/admin/index.php">
            <i class="bi bi-shield-lock me-1"></i> Admin Portal
          </a>
        </li>
        <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
          <a class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-flex align-items-center gap-1" href="planner.php">
            <i class="bi bi-bag-check-fill"></i>
            <span>My Plan</span>
            <span class="badge bg-white text-primary rounded-pill ms-1" id="nav-plan-count">0</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main>
