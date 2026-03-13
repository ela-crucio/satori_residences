<?php $pageTitle = 'Calendar'; ?>
<?php
$extraHead = <<<HTML
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<style>
.fc .fc-toolbar-title { font-family:'Cormorant Garamond',serif; font-size:1.4rem; }
.fc .fc-button-primary { background:var(--gold) !important; border-color:var(--gold) !important; }
.fc .fc-daygrid-event { border-radius:2px; font-size:0.75rem; }
.legend-dot { width:12px;height:12px;border-radius:2px;display:inline-block; }
</style>
HTML;
?>
<?php include '_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;margin:0;">Booking Calendar</h4>
  <div class="d-flex gap-3 align-items-center" style="font-size:0.82rem;">
    <span><span class="legend-dot me-1" style="background:#b8963e;"></span>1 Bedroom</span>
    <span><span class="legend-dot me-1" style="background:#1a1a1a;"></span>2 Bedroom</span>
    <span><span class="legend-dot me-1" style="background:#6c757d;"></span>3 Bedroom</span>
  </div>
</div>

<div class="stat-card shadow-sm">
  <div id="calendarEl"></div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content" style="border-radius:4px;">
      <div class="modal-header"><h6 class="modal-title">Booking Details</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="eventModalBody" style="font-size:0.87rem;"></div>
    </div>
  </div>
</div>

<?php
// Build calendar events JSON
$colors = ['1br' => '#b8963e', '2br' => '#1a1a1a', '3br' => '#6c757d'];
$events = [];
foreach ($calendarBookings as $b) {
    $events[] = [
        'title'           => $b['unit_name'] . ' — ' . $b['full_name'],
        'start'           => $b['check_in_date'],
        'end'             => $b['check_out_date'],
        'backgroundColor' => $colors[$b['slug']] ?? '#b8963e',
        'borderColor'     => $colors[$b['slug']] ?? '#b8963e',
        'extendedProps'   => [
            'guest'    => $b['full_name'],
            'unit'     => $b['unit_name'],
            'ref'      => $b['booking_reference'],
            'status'   => $b['booking_status'],
            'checkin'  => $b['check_in_date'],
            'checkout' => $b['check_out_date'],
        ],
    ];
}

$eventsJson = json_encode($events);

$extraScripts = <<<SCRIPT
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const cal = new FullCalendar.Calendar(document.getElementById('calendarEl'), {
    initialView: 'dayGridMonth',
    headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,listMonth' },
    events: $eventsJson,
    eventClick: function(info) {
      const p = info.event.extendedProps;
      document.getElementById('eventModalBody').innerHTML = `
        <table class="table table-borderless table-sm mb-0">
          <tr><td class="text-muted">Reference</td><td><code style="font-size:0.8rem">\${p.ref}</code></td></tr>
          <tr><td class="text-muted">Guest</td><td><b>\${p.guest}</b></td></tr>
          <tr><td class="text-muted">Unit</td><td>\${p.unit}</td></tr>
          <tr><td class="text-muted">Check-in</td><td>\${p.checkin}</td></tr>
          <tr><td class="text-muted">Check-out</td><td>\${p.checkout}</td></tr>
          <tr><td class="text-muted">Status</td><td><span class="badge badge-\${p.status}">\${p.status}</span></td></tr>
        </table>`;
      new bootstrap.Modal(document.getElementById('eventModal')).show();
    }
  });
  cal.render();
});
</script>
SCRIPT;
?>

<?php include '_footer.php'; ?>
