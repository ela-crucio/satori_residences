<?php $pageTitle = 'Clients'; ?>
<?php include '_header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 style="font-family:'Cormorant Garamond',serif;font-size:1.8rem;margin:0;">Client Management</h4>
</div>

<!-- SEARCH -->
<div class="stat-card shadow-sm mb-4">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control form-control-sm"
             placeholder="Search by name, email, phone..."
             value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-sm" style="background:var(--gold);color:#fff;border-radius:2px;">Search</button>
    </div>
  </form>
</div>

<div class="stat-card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Phone</th><th>Total Bookings</th><th>Total Spent</th><th>Joined</th></tr>
      </thead>
      <tbody>
        <?php if (empty($clients)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No clients found.</td></tr>
        <?php endif; ?>
        <?php foreach ($clients as $c): ?>
        <tr>
          <td class="fw-semibold"><?= htmlspecialchars($c['full_name']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['phone']) ?></td>
          <td><span class="badge" style="background:#e8f5e9;color:#2d7a4f;"><?= $c['total_bookings'] ?> booking(s)</span></td>
          <td>₱<?= number_format($c['total_spent'], 2) ?></td>
          <td style="color:#888;font-size:0.82rem;"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include '_footer.php'; ?>
