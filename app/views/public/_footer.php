<footer class="mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="mb-3" style="font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:var(--gold);letter-spacing:0.1em;">
          <i class="bi bi-building me-1"></i>SATORI RESIDENCES
        </div>
        <p style="font-size:0.85rem;line-height:1.7;">
          Experience elevated urban living in the heart of the city.
          Luxury condo units with world-class amenities.
        </p>
      </div>
      <div class="col-md-3 offset-md-1">
        <div class="section-label mb-3">Quick Links</div>
        <ul class="list-unstyled" style="font-size:0.87rem;">
          <li class="mb-2"><a href="<?= APP_URL ?>">Home</a></li>
          <li class="mb-2"><a href="<?= APP_URL ?>/units.php">Our Units</a></li>
          <li class="mb-2"><a href="<?= APP_URL ?>/booking.php">Book Now</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <div class="section-label mb-3">Contact</div>
        <p style="font-size:0.87rem;line-height:1.9;">
          <i class="bi bi-geo-alt me-2"></i>Satori Tower, BGC, Taguig City<br>
          <i class="bi bi-telephone me-2"></i>(02) 8888-1234<br>
          <i class="bi bi-envelope me-2"></i>reservations@satoriresidences.com
        </p>
      </div>
    </div>
    <hr style="border-color:rgba(255,255,255,0.1);margin:2rem 0 1rem">
    <div class="text-center" style="font-size:0.78rem;">
      &copy; <?= date('Y') ?> Satori Residences. All rights reserved.
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
