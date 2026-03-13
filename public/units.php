<?php
// public/units.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/models/UnitTypeModel.php';

Security::startSecureSession();
$unitModel = new UnitTypeModel();
$units     = $unitModel->getAll();

$pageTitle = 'Our Units';
$extraHead = <<<HTML
<style>
.units-wrap { padding:8rem 0 5rem; }
.unit-card  { border:none;border-radius:4px;box-shadow:0 4px 20px rgba(0,0,0,0.07);overflow:hidden;transition:transform 0.3s; }
.unit-card:hover { transform:translateY(-5px); }
.unit-hero { height:280px;object-fit:cover;width:100%; }
.price-tag { font-size:1.8rem;font-family:'Cormorant Garamond',serif;color:var(--gold); }
.amenity-chip { font-size:0.72rem;background:var(--cream);padding:0.25rem 0.7rem;border-radius:2px;display:inline-block;margin:2px; }
</style>
HTML;

require_once __DIR__ . '/../app/views/public/_header.php';
?>

<div class="units-wrap">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label mb-2">Accommodations</div>
      <h2 style="font-size:3rem;">Our Unit Types</h2>
      <p class="text-muted" style="max-width:480px;margin:0 auto;font-size:0.93rem;">
        All units are fully furnished with premium amenities, set within Satori Residences' luxury tower.
      </p>
    </div>

    <div class="row g-4">
      <?php foreach ($units as $unit): ?>
      <div class="col-12">
        <div class="card unit-card">
          <div class="row g-0">
            <div class="col-md-4">
              <img src="<?= htmlspecialchars($unit['primary_image'] ?? '/public/images/unit-placeholder.jpg') ?>"
                   class="unit-hero h-100" style="object-fit:cover;" alt="<?= htmlspecialchars($unit['name']) ?>">
            </div>
            <div class="col-md-8 p-4">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <div class="section-label mb-1">Max <?= $unit['max_guests'] ?> Guests</div>
                  <h3 style="font-size:2rem;margin-bottom:0.3rem;"><?= htmlspecialchars($unit['name']) ?></h3>
                </div>
                <div class="text-end">
                  <div class="price-tag">₱<?= number_format($unit['price_per_night']) ?></div>
                  <div style="font-size:0.8rem;color:var(--muted);">per night</div>
                </div>
              </div>
              <p class="text-muted mt-2" style="font-size:0.9rem;line-height:1.7;"><?= htmlspecialchars($unit['description']) ?></p>
              <div class="mb-3">
                <?php foreach ($unit['amenities'] as $a): ?>
                <span class="amenity-chip"><i class="bi bi-check me-1" style="color:var(--gold)"></i><?= htmlspecialchars($a) ?></span>
                <?php endforeach; ?>
              </div>
              <a href="/public/booking.php?unit=<?= $unit['slug'] ?>" class="btn btn-gold">
                <i class="bi bi-calendar-plus me-1"></i>Book This Unit
              </a>
              <a href="/public/unit-detail.php?slug=<?= $unit['slug'] ?>" class="btn btn-outline-gold ms-2">View Details</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../app/views/public/_footer.php'; ?>
