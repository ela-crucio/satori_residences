<?php $pageTitle = 'Reports'; ?>
<?php include '_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;margin:0;">Reports</h4>
  <button class="btn btn-sm" style="background:var(--dark);color:#fff;border-radius:2px;" onclick="window.print()">
    <i class="bi bi-printer me-1"></i>Print Report
  </button>
</div>

<!-- DATE FILTER -->
<div class="stat-card shadow-sm mb-4">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label" style="font-size:0.78rem;letter-spacing:0.07em;text-transform:uppercase;">From Date</label>
      <input type="date" name="date_from" class="form-control form-control-sm" value="<?= $dateFrom ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label" style="font-size:0.78rem;letter-spacing:0.07em;text-transform:uppercase;">To Date</label>
      <input type="date" name="date_to" class="form-control form-control-sm" value="<?= $dateTo ?>">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-sm" style="background:var(--gold);color:#fff;border-radius:2px;">Generate Report</button>
    </div>
  </form>
</div>

<!-- SUMMARY STATS -->
<div class="row g-3 mb-4">
  <?php
  $totalBookings = count($reportData);
  $paidBookings  = count(array_filter($reportData, fn($r) => $r['payment_status'] === 'paid'));
  $cancelledB    = count(array_filter($reportData, fn($r) => $r['booking_status'] === 'cancelled'));
  ?>
  <div class="col-md-3">
    <div class="stat-card shadow-sm">
      <div style="font-size:0.75rem;color:#888;text-transform:uppercase;letter-spacing:0.07em;">Total Bookings</div>
      <div class="stat-value mt-1"><?= $totalBookings ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card shadow-sm">
      <div style="font-size:0.75rem;color:#888;text-transform:uppercase;letter-spacing:0.07em;">Total Revenue</div>
      <div class="stat-value mt-1" style="font-size:1.6rem;">₱<?= number_format($totalRevenue, 2) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card shadow-sm">
      <div style="font-size:0.75rem;color:#888;text-transform:uppercase;letter-spacing:0.07em;">Paid Bookings</div>
      <div class="stat-value mt-1"><?= $paidBookings ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stat-card shadow-sm">
      <div style="font-size:0.75rem;color:#888;text-transform:uppercase;letter-spacing:0.07em;">Cancelled</div>
      <div class="stat-value mt-1"><?= $cancelledB ?></div>
    </div>
  </div>
</div>

<!-- REPORT TABLE -->
<div class="stat-card shadow-sm">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div style="font-size:0.82rem;color:#888;">
      Booking Report: <?= $dateFrom ?> to <?= $dateTo ?>
    </div>
    <button class="btn btn-sm" style="background:#e8f5e9;color:#2d7a4f;border-radius:2px;font-size:0.78rem;" onclick="exportCSV()">
      <i class="bi bi-download me-1"></i>Export CSV
    </button>
  </div>
  <div class="table-responsive">
    <table class="table table-hover" id="reportTable">
      <thead>
        <tr>
          <th>Reference</th><th>Guest</th><th>Unit</th><th>Check-in</th>
          <th>Check-out</th><th>Nights</th><th>Amount</th><th>Payment</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($reportData)): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">No records found for this period.</td></tr>
        <?php endif; ?>
        <?php foreach ($reportData as $r): ?>
        <tr>
          <td><code style="font-size:0.78rem;"><?= htmlspecialchars($r['booking_reference']) ?></code></td>
          <td><?= htmlspecialchars($r['full_name']) ?></td>
          <td><?= htmlspecialchars($r['unit_name']) ?></td>
          <td><?= $r['check_in_date'] ?></td>
          <td><?= $r['check_out_date'] ?></td>
          <td><?= $r['total_nights'] ?></td>
          <td>₱<?= number_format($r['total_price'], 2) ?></td>
          <td>
            <span class="badge badge-<?= $r['payment_status'] ?? 'pending' ?>" style="font-size:0.72rem;">
              <?= ucfirst($r['payment_status'] ?? 'pending') ?>
            </span>
          </td>
          <td>
            <span class="badge badge-<?= $r['booking_status'] ?>" style="font-size:0.72rem;">
              <?= ucfirst($r['booking_status']) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $extraScripts = '<script>
function exportCSV() {
  const rows = [];
  const table = document.getElementById("reportTable");
  for (const row of table.rows) {
    const cells = Array.from(row.cells).map(c => `"${c.innerText.trim()}"`);
    rows.push(cells.join(","));
  }
  const csv  = rows.join("\n");
  const blob = new Blob([csv], {type:"text/csv"});
  const url  = URL.createObjectURL(blob);
  const a    = document.createElement("a");
  a.href = url; a.download = "satori-report.csv"; a.click();
}
</script>'; ?>

<?php include '_footer.php'; ?>
