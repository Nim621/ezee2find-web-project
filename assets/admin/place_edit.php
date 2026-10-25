<?php
/**
 * EZEE2FIND - Edit Attraction
 */
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

$pdo = getDB();

// Fetch place
$stmt = $pdo->prepare("SELECT * FROM Place WHERE placeid = ?");
$stmt->execute([$id]);
$place = $stmt->fetch();

if (!$place) {
    header("Location: index.php?msg=" . urlencode("Attraction not found."));
    exit;
}

$categories = $pdo->query("SELECT * FROM Category ORDER BY categoryid ASC")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryid = (int)($_POST['categoryid'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $distancekm = trim($_POST['distancekm'] ?? '0');
    $openinginfo = trim($_POST['openinginfo'] ?? '');
    $open_hour = trim($_POST['open_hour'] ?? '08:00');
    $close_hour = trim($_POST['close_hour'] ?? '18:00');
    $entrancefee = trim($_POST['entrancefee'] ?? '0');
    $fee_foreign = trim($_POST['fee_foreign'] ?? '0');
    $fee_notes = trim($_POST['fee_notes'] ?? '');
    $visitduration = (int)($_POST['visitduration'] ?? 0);
    $besttime = trim($_POST['besttime'] ?? '');
    $restrictions = trim($_POST['restrictions'] ?? '');
    $accessibility = trim($_POST['accessibility'] ?? '');
    $traveltips = trim($_POST['traveltips'] ?? '');
    $imageurl = trim($_POST['imageurl'] ?? '');

    // Validation 
    if (empty($name)) {
        $errors[] = 'Place name cannot be empty.';
    }
    if ($categoryid <= 0) {
        $errors[] = 'Please select a valid category.';
    }
    if (empty($description)) {
        $errors[] = 'Description is required.';
    }
    if (!is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
        $errors[] = 'Latitude must be a valid numeric coordinate between -90 and 90.';
    }
    if (!is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
        $errors[] = 'Longitude must be a valid numeric coordinate between -180 and 180.';
    }
    if (!is_numeric($distancekm) || (float)$distancekm < 0) {
        $errors[] = 'Distance cannot be negative.';
    }
    if ($visitduration <= 0) {
        $errors[] = 'Duration must be a positive number of minutes.';
    }
    if ((float)$entrancefee < 0 || (float)$fee_foreign < 0) {
        $errors[] = 'Entrance fees cannot be negative.';
    }

    if (empty($errors)) {
        try {
            $upStmt = $pdo->prepare("UPDATE Place SET 
                name = ?, description = ?, categoryid = ?, latitude = ?, longitude = ?, distancekm = ?,
                openinginfo = ?, open_hour = ?, close_hour = ?, entrancefee = ?, fee_foreign = ?, fee_notes = ?,
                visitduration = ?, besttime = ?, restrictions = ?, accessibility = ?, traveltips = ?, imageurl = ?
                WHERE placeid = ?");
            
            $upStmt->execute([
                $name, $description, $categoryid, $latitude, $longitude, $distancekm,
                $openinginfo, $open_hour, $close_hour, $entrancefee, $fee_foreign, $fee_notes,
                $visitduration, $besttime, $restrictions, $accessibility, $traveltips, $imageurl,
                $id
            ]);

            header("Location: index.php?msg=" . urlencode("Attraction '$name' updated successfully!"));
            exit;
        } catch (Exception $e) {
            $errors[] = 'Database update failed: ' . $e->getMessage();
        }
    }
} else {
    // Populate form with existing data
    $name = $place['name'];
    $categoryid = $place['categoryid'];
    $description = $place['description'];
    $latitude = $place['latitude'];
    $longitude = $place['longitude'];
    $distancekm = $place['distancekm'];
    $openinginfo = $place['openinginfo'];
    $open_hour = substr($place['open_hour'] ?? '08:00', 0, 5);
    $close_hour = substr($place['close_hour'] ?? '18:00', 0, 5);
    $entrancefee = $place['entrancefee'];
    $fee_foreign = $place['fee_foreign'];
    $fee_notes = $place['fee_notes'];
    $visitduration = $place['visitduration'];
    $besttime = $place['besttime'];
    $restrictions = $place['restrictions'];
    $accessibility = $place['accessibility'];
    $traveltips = $place['traveltips'];
    $imageurl = $place['imageurl'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Attraction - <?= htmlspecialchars($name) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark py-2">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
    </a>
  </div>
</nav>

<div class="container py-4" style="max-width: 860px;">
  <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-header bg-primary text-white p-3 p-sm-4">
      <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square me-2"></i> Edit Attraction: <?= htmlspecialchars($place['name']) ?></h4>
      <p class="small mb-0 opacity-75">Modify operating schedule, admission fees, or visitor notes.</p>
    </div>

    <div class="card-body p-4 p-sm-5">
      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger rounded-3 mb-4">
          <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i> Validation Errors:</h6>
          <ul class="mb-0 small ps-3">
            <?php foreach ($errors as $err): ?>
              <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="place_edit.php?id=<?= $id ?>">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label small fw-semibold text-muted">Place Name *</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Category *</label>
            <select name="categoryid" class="form-select" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['categoryid'] ?>" <?= ($categoryid == $cat['categoryid']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cat['categoryname']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label small fw-semibold text-muted">Detailed Description *</label>
            <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($description) ?></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Latitude *</label>
            <input type="text" name="latitude" class="form-control" value="<?= htmlspecialchars($latitude) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Longitude *</label>
            <input type="text" name="longitude" class="form-control" value="<?= htmlspecialchars($longitude) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Distance to Galle Hub (km) *</label>
            <input type="number" step="0.1" name="distancekm" class="form-control" value="<?= htmlspecialchars($distancekm) ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Opening Time (24h)</label>
            <input type="time" name="open_hour" class="form-control" value="<?= htmlspecialchars($open_hour) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Closing Time (24h)</label>
            <input type="time" name="close_hour" class="form-control" value="<?= htmlspecialchars($close_hour) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Visit Stay Duration (Minutes) *</label>
            <input type="number" name="visitduration" class="form-control" value="<?= htmlspecialchars($visitduration) ?>" required min="10">
          </div>

          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Local Entrance Fee (LKR)</label>
            <input type="number" step="10" name="entrancefee" class="form-control" value="<?= htmlspecialchars($entrancefee) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Foreign Entrance Fee (LKR)</label>
            <input type="number" step="50" name="fee_foreign" class="form-control" value="<?= htmlspecialchars($fee_foreign) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-semibold text-muted">Fee Notes / Summary</label>
            <input type="text" name="fee_notes" class="form-control" value="<?= htmlspecialchars($fee_notes) ?>">
          </div>

          <div class="col-12">
            <label class="form-label small fw-semibold text-muted">Opening Hours Display Text</label>
            <input type="text" name="openinginfo" class="form-control" value="<?= htmlspecialchars($openinginfo) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Best Visiting Time / Window</label>
            <input type="text" name="besttime" class="form-control" value="<?= htmlspecialchars($besttime) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Accessibility Details</label>
            <input type="text" name="accessibility" class="form-control" value="<?= htmlspecialchars($accessibility) ?>">
          </div>

          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Restrictions & Cultural Etiquette</label>
            <textarea name="restrictions" class="form-control" rows="2"><?= htmlspecialchars($restrictions) ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Travel Tips & Advice</label>
            <textarea name="traveltips" class="form-control" rows="2"><?= htmlspecialchars($traveltips) ?></textarea>
          </div>

          <div class="col-12">
            <label class="form-label small fw-semibold text-muted">Photo Image URL</label>
            <input type="text" name="imageurl" class="form-control" value="<?= htmlspecialchars($imageurl) ?>">
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <a href="index.php" class="btn btn-light rounded-pill px-4">Cancel</a>
          <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
            <i class="bi bi-save me-1"></i> Update Attraction
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
