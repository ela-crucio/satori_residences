<?php $pageTitle = 'Dashboard'; ?>
<?php
$extraHead = <<<HTML
<style>
.chart-container { position:relative; height:280px; }
</style>
HTML;
?>
<?php include '_header.php'; ?>

<!-- STATS ROW -->
<div class="row g-3 mb-4">
<?php
$statCards = [
  ['Total Bookings',     $stats['total_bookings'],     '₱',     'bi-calendar-check', '#d4edda','#2d7a4f', false],
  ['Total Revenue',      '₱'.number_format($stats['total_revenue']),   '', 'bi-cash-coin',    '#fff3cd','#856404', false],
  ['Upcoming Check-ins', $stats['upcoming_checkins'],  '',      'bi-door-open',      '#cff4fc','#055160', false],
  ['Active Now',         $stats['active_reservations'],'',      'bi-house-check',    '#f8d7da','#842029', false],
];
foreach ($statCards as [$label,$val,$prefix,$icon,$bg,$color,$_]):
?>
<div class="col-6 col-md-3">
  <div class="stat-card shadow-sm">
    <div class="d-flex align-items-center gap-3 mb-2">
      <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
      <div style="font-size:0.75rem;color:#888;letter-spacing:0.07em;text-transform:uppercase;"><?= $label ?></div>
    </div>
    <div class="stat-value"><?= htmlspecialchars((string)$val) ?></div>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- CHARTS ROW -->
<div class="row g-3 mb-4">
  <div class="col-md-8">
    <div class="stat-card shadow-sm">
      <div style="font-size:0.8rem;letter-spacing:0.1em;text-transform:uppercase;color:#888;margin-bottom:1rem;">Monthly Bookings & Revenue</div>
      <div class="chart-container"><canvas id="bookingsChart"></canvas></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stat-card shadow-sm">
      <div style="font-size:0.8rem;letter-spacing:0.1em;text-transform:uppercase;color:#888;margin-bottom:1rem;">Unit Popularity</div>
      <div class="chart-container"><canvas id="popularityChart"></canvas></div>
    </div>
  </div>
</div>

<!-- RECENT BOOKINGS -->
<div class="stat-card shadow-sm">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div style="font-size:0.8rem;letter-spacing:0.1em;text-transform:uppercase;color:#888;">Recent Bookings</div>
    <a href="<?= ADMIN_URL ?>/bookings.php" class="btn btn-sm" style="background:var(--gold);color:#fff;font-size:0.78rem;border-radius:2px;">View All</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>Reference</th><th>Guest</th><th>Unit</th><th>Check-in</th><th>Check-out</th><th>Amount</th><th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recentBookings)): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">No bookings yet.</td></tr>
        <?php else: ?>
        <?php foreach (array_slice($recentBookings,0,10) as $b): ?>
        <tr>
          <td><code style="font-size:0.8rem;"><?= htmlspecialchars($b['booking_reference']) ?></code></td>
          <td><?= htmlspecialchars($b['full_name']) ?></td>
          <td><?= htmlspecialchars($b['unit_name']) ?></td>
          <td><?= $b['check_in_date'] ?></td>
          <td><?= $b['check_out_date'] ?></td>
          <td>₱<?= number_format($b['total_price']) ?></td>
          <td><span class="badge badge-<?= $b['booking_status'] ?>"><?= ucfirst($b['booking_status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
// Prepare chart data
$months  = array_column($stats['monthly_bookings'], 'month');
$mbTots  = array_column($stats['monthly_bookings'], 'total');
$revMap  = array_column($stats['monthly_revenue'],  'revenue', 'month');
$revData = array_map(fn($m) => $revMap[$m] ?? 0, $months);
$unitNames = array_column($stats['unit_popularity'], 'name');
$unitCnts  = array_column($stats['unit_popularity'], 'bookings');

$extraScripts = '<script>
const months = '.json_encode($months).';
const mbTots  = '.json_encode($mbTots).';
const revData = '.json_encode($revData).';

new Chart(document.getElementById("bookingsChart"), {
  type:"bar",
  data:{
    labels: months,
    datasets:[
      { label:"Bookings", data:mbTots, backgroundColor:"rgba(184,150,62,0.7)", yAxisID:"y" },
      { label:"Revenue (₱)", data:revData, type:"line", borderColor:"#1a1a1a",
        backgroundColor:"transparent", tension:0.4, yAxisID:"y1" }
    ]
  },
  options:{responsive:true,maintainAspectRatio:false,
    scales:{y:{beginAtZero:true},y1:{beginAtZero:true,position:"right",grid:{drawOnChartArea:false}}}}
});

new Chart(document.getElementById("popularityChart"), {
  type:"doughnut",
  data:{
    labels: '.json_encode($unitNames).',
    datasets:[{data:'.json_encode($unitCnts).',
      backgroundColor:["#b8963e","#1a1a1a","#888"]}]
  },
  options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:"bottom"}}}
});
</script>';
?>

<?php include '_footer.php'; ?>
