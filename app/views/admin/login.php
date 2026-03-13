<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Satori Residences</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;600&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root { --gold:#b8963e; --dark:#0d0d0d; }
body {
  font-family:'Inter',sans-serif; margin:0;
  min-height:100vh; display:flex; align-items:center; justify-content:center;
  background: linear-gradient(135deg,#0d0d0d 0%,#1a1a1a 100%);
}
.login-card { background:#fff; width:420px; padding:3rem; border-radius:4px; }
.login-logo { font-family:'Cormorant Garamond',serif; font-size:1.5rem; color:var(--gold); letter-spacing:0.1em; text-transform:uppercase; margin-bottom:0.3rem; }
.form-control { border-radius:0; border:1px solid #ddd; padding:0.75rem 1rem; }
.form-control:focus { border-color:var(--gold); box-shadow:0 0 0 3px rgba(184,150,62,0.15); }
.btn-login { background:var(--gold); color:#fff; border:none; width:100%; padding:0.85rem; letter-spacing:0.08em; text-transform:uppercase; font-size:0.85rem; font-weight:600; border-radius:2px; }
.btn-login:hover { background:#c4a04e; color:#fff; }
</style>
</head>
<body>
<div class="login-card">
  <div class="text-center mb-4">
    <div class="login-logo"><i class="bi bi-building me-1"></i>Satori Residences</div>
    <div style="font-size:0.8rem;color:#888;letter-spacing:0.08em;text-transform:uppercase;margin-top:0.2rem;">Administration Portal</div>
  </div>

  <?php if (!empty($error)): ?>
  <div class="alert alert-danger" style="font-size:0.87rem;border-radius:2px;">
    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <form method="POST" action="">
    <?php require_once __DIR__ . '/../../helpers/Security.php'; ?>
    <?= Security::csrfField() ?>
    <div class="mb-3">
      <label class="form-label" style="font-size:0.8rem;letter-spacing:0.07em;text-transform:uppercase;font-weight:600;">Email Address</label>
      <input type="email" name="email" class="form-control" required autofocus
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-4">
      <label class="form-label" style="font-size:0.8rem;letter-spacing:0.07em;text-transform:uppercase;font-weight:600;">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-login">
      <i class="bi bi-lock me-2"></i>Sign In
    </button>
  </form>

  <div class="text-center mt-4" style="font-size:0.78rem;color:#aaa;">
    Satori Residences &copy; <?= date('Y') ?> — Secure Admin Access
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
