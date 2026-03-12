<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> | Satori Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --gold:#b8963e; --dark:#0d0d0d; --dark2:#1a1a1a; --sidebar-w:260px;
  --cream:#f8f5ef; --success:#2d7a4f;
}
body { font-family:'Inter',sans-serif; background:#f4f4f6; margin:0; }
.admin-sidebar {
  width:var(--sidebar-w); position:fixed; top:0; left:0; height:100vh;
  background:var(--dark); overflow-y:auto; z-index:1000;
  display:flex; flex-direction:column;
}
.sidebar-brand {
  padding:1.5rem 1.5rem 1rem;
  border-bottom:1px solid rgba(255,255,255,0.07);
  font-family:'Cormorant Garamond',serif; font-size:1.2rem;
  color:var(--gold); letter-spacing:0.08em; text-transform:uppercase;
}
.sidebar-nav { flex:1; padding:1rem 0; }
.nav-section  { font-size:0.62rem; letter-spacing:0.18em; color:rgba(255,255,255,0.3);
  text-transform:uppercase; padding:0.8rem 1.5rem 0.3rem; font-weight:600; }
.sidebar-link {
  display:flex; align-items:center; gap:0.8rem;
  padding:0.7rem 1.5rem; color:rgba(255,255,255,0.65);
  text-decoration:none; font-size:0.87rem;
  transition:all 0.15s; border-left:3px solid transparent;
}
.sidebar-link:hover, .sidebar-link.active {
  color:#fff; background:rgba(255,255,255,0.06);
  border-left-color:var(--gold);
}
.sidebar-link i { font-size:1.05rem; width:20px; text-align:center; }
.admin-main { margin-left:var(--sidebar-w); min-height:100vh; }
.admin-topbar {
  background:#fff; padding:0.9rem 2rem;
  border-bottom:1px solid #eee;
  display:flex; align-items:center; justify-content:space-between;
  position:sticky; top:0; z-index:100;
}
.admin-content { padding:2rem; }
.stat-card { background:#fff; border-radius:8px; padding:1.5rem; border:none; }
.stat-card .stat-icon { width:52px;height:52px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.3rem; }
.stat-card .stat-value { font-size:2rem;font-family:'Cormorant Garamond',serif;font-weight:600;line-height:1; }
.table th { font-size:0.72rem;letter-spacing:0.1em;text-transform:uppercase;color:#888;font-weight:600;background:#fafafa; }
.table td { font-size:0.87rem;vertical-align:middle; }
.badge-pending    { background:#fff3cd;color:#856404; }
.badge-confirmed  { background:#d1e7dd;color:#0f5132; }
.badge-cancelled  { background:#f8d7da;color:#842029; }
.badge-completed  { background:#cff4fc;color:#055160; }
.badge-paid       { background:#d1e7dd;color:#0f5132; }
.badge-failed     { background:#f8d7da;color:#842029; }
</style>
<?= $extraHead ?? '' ?>
</head>
<body>

<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$navLinks = [
  ['dashboard',  'bi-speedometer2',   'Dashboard',  ADMIN_URL.'/dashboard.php'],
  ['bookings',   'bi-calendar-check', 'Bookings',   ADMIN_URL.'/bookings.php'],
  ['clients',    'bi-people',         'Clients',    ADMIN_URL.'/clients.php'],
  ['units',      'bi-building',       'Units',      ADMIN_URL.'/units.php'],
  ['calendar',   'bi-calendar3',      'Calendar',   ADMIN_URL.'/calendar.php'],
  ['reports',    'bi-bar-chart-line', 'Reports',    ADMIN_URL.'/reports.php'],
];
?>

<div class="admin-sidebar">
  <div class="sidebar-brand">
    <i class="bi bi-building me-1"></i>Satori Admin
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Main</div>
    <?php foreach ($navLinks as [$page,$icon,$label,$url]): ?>
    <a href="<?= $url ?>" class="sidebar-link <?= $currentPage === $page ? 'active' : '' ?>">
      <i class="bi <?= $icon ?>"></i><?= $label ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div style="padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,0.07);">
    <a href="<?= ADMIN_URL ?>/logout.php" class="sidebar-link" style="color:rgba(255,100,100,0.8);">
      <i class="bi bi-box-arrow-right"></i>Logout
    </a>
  </div>
</div>

<div class="admin-main">
  <div class="admin-topbar">
    <div style="font-size:0.9rem;color:#888;">
      <?= date('l, F j, Y') ?>
    </div>
    <div class="d-flex align-items-center gap-3">
      <span style="font-size:0.87rem;">
        <i class="bi bi-person-circle me-1" style="color:var(--gold)"></i>
        <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
        <span class="badge bg-secondary ms-1" style="font-size:0.65rem;"><?= ucfirst($_SESSION['admin_role'] ?? '') ?></span>
      </span>
    </div>
  </div>
  <div class="admin-content">
