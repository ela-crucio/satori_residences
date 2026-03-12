<?php
$pageTitle = 'Luxury Condo Suites';
$extraHead = <<<HTML
<style>
.hero {
  height:100vh; min-height:680px;
  background: linear-gradient(135deg, rgba(13,13,13,0.82) 0%, rgba(13,13,13,0.55) 100%),
              url('/public/images/hero-bg.jpg') center/cover no-repeat;
  display:flex; align-items:center;
}
.hero-eyebrow { font-size:0.72rem; letter-spacing:0.25em; text-transform:uppercase; color:var(--gold); font-weight:600; }
.hero h1 { font-size: clamp(3rem,6vw,5rem); color:#fff; line-height:1.1; }
.hero p { color:rgba(255,255,255,0.75); font-size:1rem; max-width:520px; }
.unit-card { border:none; border-radius:0; transition:transform 0.3s, box-shadow 0.3s; overflow:hidden; }
.unit-card:hover { transform:translateY(-6px); box-shadow:0 20px 40px rgba(0,0,0,0.12); }
.unit-card .card-img-top { height:240px; object-fit:cover; }
.unit-card .card-body { padding:1.8rem; }
.price-tag { font-size:1.5rem; font-family:'Cormorant Garamond',serif; color:var(--gold); }
.amenity-badge { font-size:0.72rem; background:#f5f0e8; color:var(--text); padding:0.25rem 0.6rem; border-radius:2px; display:inline-block; margin:2px; }
.feature-icon { width:56px;height:56px;background:var(--cream);display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:var(--gold); }
</style>
HTML;
require_once __DIR__ . '/../../controllers/BookingController.php'; // already loaded via index
?>
<?php include '_header.php'; ?>

<!-- HERO -->
<section class="hero">
  <div class="container">
    <div class="hero-eyebrow mb-3">Welcome to Satori Residences</div>
    <h1>Live Above<br><em>the Ordinary</em></h1>
    <p class="mt-3 mb-4">
      Premium condo suites in the heart of the city — where every detail is
      crafted for comfort, elegance, and unforgettable experiences.
    </p>
    <div class="d-flex gap-3 flex-wrap">
      <a href="/public/booking.php" class="btn btn-gold btn-lg">Book Your Stay</a>
      <a href="/public/units.php"   class="btn btn-outline-gold btn-lg">Explore Units</a>
    </div>
  </div>
</section>

<!-- INTRO STRIP -->
<section style="background:var(--dark);padding:4rem 0;">
  <div class="container">
    <div class="row g-4 text-center">
      <?php $features = [
        ['bi-shield-check','Verified & Secure','Verified ID checks and secure payment processing'],
        ['bi-calendar-check','Instant Confirmation','Get your booking confirmed in minutes'],
        ['bi-headset','24/7 Support','Round-the-clock concierge and guest support'],
        ['bi-gem','Premium Quality','Curated amenities and luxury furnishings'],
      ];
      foreach ($features as [$icon,$title,$desc]): ?>
      <div class="col-6 col-md-3">
        <div class="feature-icon mx-auto mb-3"><i class="bi <?= $icon ?>"></i></div>
        <div style="color:#fff;font-weight:600;font-size:0.9rem;"><?= $title ?></div>
        <div style="color:rgba(255,255,255,0.5);font-size:0.8rem;margin-top:0.3rem;"><?= $desc ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- UNIT TYPES PREVIEW -->
<section style="padding:6rem 0; background:var(--cream);">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label mb-2">Accommodations</div>
      <h2 style="font-size:2.8rem;">Our Unit Types</h2>
      <p class="text-muted" style="max-width:480px;margin:0 auto;">
        Choose from three carefully designed unit configurations — each offering space, style, and serenity.
      </p>
    </div>
    <div class="row g-4">
      <?php foreach ($units as $unit): ?>
      <div class="col-md-4">
        <div class="card unit-card shadow-sm h-100">
          <img src="<?= htmlspecialchars($unit['primary_image'] ?? '/public/images/unit-placeholder.jpg') ?>"
               class="card-img-top" alt="<?= htmlspecialchars($unit['name']) ?>">
          <div class="card-body">
            <div class="section-label mb-1"><?= $unit['max_guests'] ?> Guests Max</div>
            <h3 style="font-size:1.8rem;margin-bottom:0.2rem;"><?= htmlspecialchars($unit['name']) ?></h3>
            <div class="price-tag mb-3">₱<?= number_format($unit['price_per_night']) ?><span style="font-size:0.9rem;color:var(--muted);font-family:Inter,sans-serif"> / night</span></div>
            <p style="font-size:0.87rem;color:var(--muted);line-height:1.7;"><?= htmlspecialchars(substr($unit['description'], 0, 140)) ?>...</p>
            <div class="mb-3">
              <?php foreach (array_slice($unit['amenities'], 0, 4) as $a): ?>
                <span class="amenity-badge"><i class="bi bi-check me-1"></i><?= htmlspecialchars($a) ?></span>
              <?php endforeach; ?>
            </div>
            <a href="/public/unit-detail.php?slug=<?= $unit['slug'] ?>" class="btn btn-outline-gold w-100">View Details</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4">
      <a href="/public/booking.php" class="btn btn-gold btn-lg">Book Now</a>
    </div>
  </div>
</section>

<!-- PROPERTY HIGHLIGHTS -->
<section style="padding:6rem 0;">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-md-5">
        <div class="section-label mb-2">The Satori Experience</div>
        <h2 style="font-size:2.6rem;line-height:1.2;">Where Luxury<br>Meets Home</h2>
        <p class="mt-3 text-muted" style="font-size:0.93rem;line-height:1.8;">
          Satori Residences offers an unparalleled urban living experience. Our fully-furnished
          premium condominiums blend contemporary design with thoughtful amenities, all within
          a secure, professionally managed building.
        </p>
        <ul class="list-unstyled mt-3" style="font-size:0.9rem;">
          <?php foreach (['Rooftop Swimming Pool','Fully Equipped Gym','24/7 Concierge Service','Covered Parking','High-Speed Fiber Internet'] as $f): ?>
          <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color:var(--gold)"></i><?= $f ?></li>
          <?php endforeach; ?>
        </ul>
        <a href="/public/units.php" class="btn btn-gold mt-3">Explore All Units</a>
      </div>
      <div class="col-md-7">
        <div class="row g-3">
          <div class="col-8">
            <img src="/public/images/property-1.jpg" alt="Pool" class="img-fluid w-100" style="height:320px;object-fit:cover;">
          </div>
          <div class="col-4 d-flex flex-column gap-3">
            <img src="/public/images/property-2.jpg" alt="Gym" class="img-fluid" style="height:150px;object-fit:cover;flex:1;">
            <img src="/public/images/property-3.jpg" alt="Lobby" class="img-fluid" style="height:150px;object-fit:cover;flex:1;">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA BANNER -->
<section style="background:var(--dark);padding:5rem 0;text-align:center;">
  <div class="container">
    <div class="section-label mb-2">Ready to Book?</div>
    <h2 style="color:#fff;font-size:3rem;margin-bottom:1rem;">Your Perfect Stay Awaits</h2>
    <p style="color:rgba(255,255,255,0.6);max-width:440px;margin:0 auto 2rem;">
      Check availability and secure your unit in just a few steps. No hidden fees.
    </p>
    <a href="/public/booking.php" class="btn btn-gold btn-lg px-5">Reserve Your Unit</a>
  </div>
</section>

<?php include '_footer.php'; ?>
