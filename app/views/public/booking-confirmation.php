<?php
$pageTitle = 'Booking Confirmed';
$extraHead = <<<HTML
<style>
.confirm-wrap { padding:8rem 0 5rem; background:var(--cream); min-height:100vh; }
.confirm-card { background:#fff; border-radius:4px; box-shadow:0 4px 40px rgba(0,0,0,0.08); overflow:hidden; }
.confirm-hero  { background:var(--dark); padding:2.5rem; text-align:center; }
.ref-badge     { display:inline-block; background:var(--gold); color:#fff; padding:0.5rem 1.5rem;
                 font-family:'Inter',sans-serif; font-size:1.1rem; font-weight:700; letter-spacing:0.1em; border-radius:2px; }
.detail-row    { display:flex; justify-content:space-between; padding:0.7rem 0;
                 border-bottom:1px solid #f0f0f0; font-size:0.9rem; }
.detail-row:last-child { border:none; }
.status-badge  { display:inline-block; padding:0.3rem 1rem; border-radius:2px; font-size:0.8rem; font-weight:600; letter-spacing:0.05em; text-transform:uppercase; }
</style>
HTML;
?>
<?php include '_header.php'; ?>

<div class="confirm-wrap">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7">
        <div class="confirm-card">

          <div class="confirm-hero">
            <div style="font-size:3rem;color:var(--gold);margin-bottom:0.5rem;">
              <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 style="color:#fff;font-family:'Cormorant Garamond',serif;font-size:2.2rem;">Booking Confirmed!</h2>
            <p style="color:rgba(255,255,255,0.65);margin-bottom:1.2rem;font-size:0.9rem;">
              Thank you, <strong style="color:#fff;"><?= htmlspecialchars($booking['full_name']) ?></strong>. Your reservation is confirmed.
            </p>
            <div class="ref-badge"><?= htmlspecialchars($booking['booking_reference']) ?></div>
          </div>

          <div class="p-4">

            <!-- PAYMENT STATUS -->
            <?php
            $ps = isset($booking['payment_status']) ? $booking['payment_status'] : 'pending';
            if ($ps === 'paid') {
                $payStatusColor = 'success';
                $payStatusLabel = 'Payment Confirmed';
            } elseif ($ps === 'failed') {
                $payStatusColor = 'danger';
                $payStatusLabel = 'Payment Failed';
            } else {
                $payStatusColor = 'warning';
                $payStatusLabel = 'Payment Pending';
            }
            ?>
            <div class="text-center mb-4">
              <span class="status-badge bg-<?= $payStatusColor ?> text-white"><?= $payStatusLabel ?></span>
            </div>

            <div class="section-label mb-3">Reservation Details</div>

            <div class="detail-row">
              <span class="text-muted">Booking Reference</span>
              <span class="fw-bold"><?= htmlspecialchars($booking['booking_reference']) ?></span>
            </div>
            <div class="detail-row">
              <span class="text-muted">Unit Type</span>
              <span class="fw-semibold"><?= htmlspecialchars($booking['unit_name']) ?></span>
            </div>
            <div class="detail-row">
              <span class="text-muted">Check-in</span>
              <span class="fw-semibold"><?= date('F j, Y', strtotime($booking['check_in_date'])) ?></span>
            </div>
            <div class="detail-row">
              <span class="text-muted">Check-out</span>
              <span class="fw-semibold"><?= date('F j, Y', strtotime($booking['check_out_date'])) ?></span>
            </div>
            <div class="detail-row">
              <span class="text-muted">Total Nights</span>
              <span><?= $booking['total_nights'] ?> night<?= $booking['total_nights'] > 1 ? 's' : '' ?></span>
            </div>
            <div class="detail-row">
              <span class="text-muted">Guests</span>
              <span><?= $booking['number_of_guests'] ?></span>
            </div>

            <hr>
            <div class="section-label mb-3">Payment</div>

            <div class="detail-row">
              <span class="text-muted">Method</span>
              <span><?= ucwords(str_replace('_',' ', $booking['payment_method'] ?? '—')) ?></span>
            </div>
            <?php if ($booking['transaction_reference']): ?>
            <div class="detail-row">
              <span class="text-muted">Transaction Ref</span>
              <span style="font-family:monospace;font-size:0.87rem;"><?= htmlspecialchars($booking['transaction_reference']) ?></span>
            </div>
            <?php endif; ?>
            <div class="detail-row">
              <span class="text-muted fw-semibold">Total Amount</span>
              <span style="color:var(--gold);font-size:1.3rem;font-family:'Cormorant Garamond',serif;font-weight:600;">
                ₱<?= number_format($booking['total_price'], 2) ?>
              </span>
            </div>

            <div class="alert alert-light mt-4 text-center" style="border:1px solid #e5e5e5;font-size:0.85rem;">
              <i class="bi bi-envelope me-2" style="color:var(--gold)"></i>
              A confirmation has been noted under <strong><?= htmlspecialchars($booking['email']) ?></strong>.<br>
              Please present your booking reference upon check-in.
            </div>

            <div class="d-flex gap-3 justify-content-center mt-4">
              <a href="/public/" class="btn btn-outline-gold">Back to Home</a>
              <button class="btn btn-gold" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
            </div>

          </div><!-- /p-4 -->
        </div><!-- /confirm-card -->
      </div>
    </div>
  </div>
</div>

<?php include '_footer.php'; ?>