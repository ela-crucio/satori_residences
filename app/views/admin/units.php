<?php $pageTitle = 'Units'; ?>
<?php
require_once __DIR__ . '/../../helpers/Security.php';
$csrf = Security::generateCsrfToken();
?>
<?php include '_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;margin:0;">Unit Management</h4>
</div>

<?php if (isset($_GET['updated'])): ?>
<div class="alert alert-success alert-dismissible fade show" style="border-radius:2px;">
  <i class="bi bi-check-circle me-2"></i>Unit updated successfully.
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">
  <?php foreach ($units as $unit): ?>
  <div class="col-md-4">
    <div class="stat-card shadow-sm">
      <div style="font-family:'Cormorant Garamond',serif;font-size:1.5rem;margin-bottom:0.3rem;">
        <?= htmlspecialchars($unit['name']) ?>
      </div>
      <div style="font-size:1.2rem;color:var(--gold);margin-bottom:1rem;">
        ₱<?= number_format($unit['price_per_night']) ?><span style="font-size:0.8rem;color:#888;">/night</span>
      </div>
      <div style="font-size:0.83rem;color:#888;margin-bottom:0.5rem;">Max Guests: <?= $unit['max_guests'] ?></div>
      <div style="font-size:0.83rem;color:#888;margin-bottom:1rem;"><?= count($unit['amenities']) ?> amenities listed</div>
      <button class="btn btn-sm w-100" style="background:var(--dark);color:#fff;border-radius:2px;"
              onclick="openEditModal(<?= $unit['unit_type_id'] ?>, '<?= htmlspecialchars(addslashes($unit['name'])) ?>',
                '<?= htmlspecialchars(addslashes($unit['description'])) ?>',
                <?= $unit['max_guests'] ?>, <?= $unit['price_per_night'] ?>,
                <?= htmlspecialchars(json_encode($unit['amenities'])) ?>)">
        <i class="bi bi-pencil me-1"></i>Edit Unit
      </button>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editUnitModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius:4px;">
      <form method="POST" action="<?= ADMIN_URL ?>/ajax/update-unit.php">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="unit_type_id" id="editUnitId">
        <div class="modal-header">
          <h6 class="modal-title">Edit Unit: <span id="editUnitTitle"></span></h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.07em;">Unit Name</label>
              <input type="text" name="name" id="editName" class="form-control form-control-sm" required>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.07em;">Price/Night (₱)</label>
              <input type="number" name="price_per_night" id="editPrice" class="form-control form-control-sm" step="0.01" required>
            </div>
            <div class="col-md-3">
              <label class="form-label" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.07em;">Max Guests</label>
              <input type="number" name="max_guests" id="editMaxGuests" class="form-control form-control-sm" required>
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.07em;">Description</label>
              <textarea name="description" id="editDesc" class="form-control form-control-sm" rows="3" required></textarea>
            </div>
            <div class="col-12">
              <label class="form-label" style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.07em;">Amenities (one per line)</label>
              <textarea name="amenities_raw" id="editAmenities" class="form-control form-control-sm" rows="6"></textarea>
              <div style="font-size:0.76rem;color:#aaa;margin-top:0.3rem;">Enter each amenity on a new line. These will be saved as a list.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm" style="background:var(--gold);color:#fff;border-radius:2px;">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php $extraScripts = '<script>
function openEditModal(id, name, desc, maxGuests, price, amenities) {
  document.getElementById("editUnitId").value = id;
  document.getElementById("editUnitTitle").textContent = name;
  document.getElementById("editName").value = name;
  document.getElementById("editDesc").value = desc;
  document.getElementById("editMaxGuests").value = maxGuests;
  document.getElementById("editPrice").value = price;
  document.getElementById("editAmenities").value = (amenities||[]).join("\n");
  new bootstrap.Modal(document.getElementById("editUnitModal")).show();
}
</script>'; ?>

<?php include '_footer.php'; ?>
