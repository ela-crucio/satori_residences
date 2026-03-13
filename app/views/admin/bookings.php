<?php $pageTitle = 'Bookings'; ?>
<?php include '_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;margin:0;">Booking Management</h4>
</div>

<!-- FILTERS -->
<div class="stat-card shadow-sm mb-4">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label" style="font-size:0.78rem;letter-spacing:0.07em;text-transform:uppercase;">Search</label>
      <input type="text" name="search" class="form-control form-control-sm"
             placeholder="Reference, name, email..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label" style="font-size:0.78rem;letter-spacing:0.07em;text-transform:uppercase;">Unit Type</label>
      <select name="unit_type_id" class="form-select form-select-sm">
        <option value="">All Units</option>
        <?php foreach ($units as $u): ?>
        <option value="<?= $u['unit_type_id'] ?>" <?= ($_GET['unit_type_id'] ?? '') == $u['unit_type_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($u['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label" style="font-size:0.78rem;letter-spacing:0.07em;text-transform:uppercase;">Status</label>
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <?php foreach (['pending','confirmed','cancelled','completed'] as $s): ?>
        <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label" style="font-size:0.78rem;letter-spacing:0.07em;text-transform:uppercase;">From</label>
      <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label" style="font-size:0.78rem;letter-spacing:0.07em;text-transform:uppercase;">To</label>
      <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-sm w-100" style="background:var(--gold);color:#fff;border-radius:2px;">Filter</button>
    </div>
  </form>
</div>

<!-- TABLE -->
<div class="stat-card shadow-sm">
  <div class="d-flex justify-content-between mb-3">
    <div style="font-size:0.82rem;color:#888;"><?= count($bookings) ?> booking(s) found</div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>#</th><th>Reference</th><th>Guest</th><th>Unit</th>
          <th>Check-in</th><th>Check-out</th><th>Nights</th><th>Amount</th>
          <th>Payment</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
        <tr><td colspan="11" class="text-center text-muted py-4">No bookings found.</td></tr>
        <?php endif; ?>
        <?php foreach ($bookings as $i => $b): ?>
        <tr>
          <td style="color:#aaa;"><?= $i+1 ?></td>
          <td><code style="font-size:0.78rem;"><?= htmlspecialchars($b['booking_reference']) ?></code></td>
          <td>
            <div class="fw-semibold" style="font-size:0.87rem;"><?= htmlspecialchars($b['full_name']) ?></div>
            <div style="font-size:0.78rem;color:#888;"><?= htmlspecialchars($b['email']) ?></div>
          </td>
          <td><?= htmlspecialchars($b['unit_name']) ?></td>
          <td><?= $b['check_in_date'] ?></td>
          <td><?= $b['check_out_date'] ?></td>
          <td><?= $b['total_nights'] ?></td>
          <td>₱<?= number_format($b['total_price']) ?></td>
          <td>
            <span class="badge badge-<?= $b['payment_status'] ?? 'pending' ?>" style="font-size:0.72rem;">
              <?= ucfirst($b['payment_status'] ?? 'pending') ?>
            </span>
          </td>
          <td>
            <span class="badge badge-<?= $b['booking_status'] ?>" style="font-size:0.72rem;">
              <?= ucfirst($b['booking_status']) ?>
            </span>
          </td>
          <td>
            <button class="btn btn-sm" style="background:#f0f0f0;border-radius:2px;font-size:0.75rem;"
                    onclick="openStatusModal(<?= $b['booking_id'] ?>, '<?= $b['booking_status'] ?>', '<?= htmlspecialchars(addslashes($b['full_name'])) ?>')">
              <i class="bi bi-pencil"></i>
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- STATUS MODAL -->
<div class="modal fade" id="statusModal" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content" style="border-radius:4px;">
      <div class="modal-header" style="border-bottom:1px solid #eee;">
        <h6 class="modal-title">Update Booking Status</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3" style="font-size:0.85rem;">Guest: <strong id="modalGuestName"></strong></div>
        <select class="form-select form-select-sm" id="statusSelect">
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="cancelled">Cancelled</option>
          <option value="completed">Completed</option>
        </select>
        <div id="statusUpdateMsg" class="mt-2" style="font-size:0.82rem;"></div>
      </div>
      <div class="modal-footer" style="border-top:1px solid #eee;">
        <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-sm" style="background:var(--gold);color:#fff;border-radius:2px;" onclick="saveStatus()">Save</button>
      </div>
    </div>
  </div>
</div>

<?php
require_once __DIR__ . '/../../helpers/Security.php';
$csrfToken = Security::generateCsrfToken();
$extraScripts = '<script>
const CSRF = "'. $csrfToken .'";
const ADMIN_URL = "'. ADMIN_URL .'";
let currentBookingId = null;

function openStatusModal(id, status, name) {
  currentBookingId = id;
  document.getElementById("modalGuestName").textContent = name;
  document.getElementById("statusSelect").value = status;
  document.getElementById("statusUpdateMsg").textContent = "";
  new bootstrap.Modal(document.getElementById("statusModal")).show();
}

async function saveStatus() {
  const status = document.getElementById("statusSelect").value;
  const f = new FormData();
  f.append("csrf_token", CSRF);
  f.append("booking_id", currentBookingId);
  f.append("status", status);

  const r = await fetch(ADMIN_URL+"/ajax/update-status.php", {method:"POST",body:f});
  const d = await r.json();
  const msg = document.getElementById("statusUpdateMsg");
  if (d.success) {
    msg.style.color = "green";
    msg.textContent = "Updated successfully!";
    setTimeout(() => window.location.reload(), 800);
  } else {
    msg.style.color = "red";
    msg.textContent = d.message;
  }
}
</script>';
?>

<?php include '_footer.php'; ?>
