<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Satori Residences') ?> | Satori Residences</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --gold:    #b8963e;
  --gold-lt: #d4af6a;
  --dark:    #0d0d0d;
  --dark2:   #1a1a1a;
  --dark3:   #2c2c2c;
  --cream:   #f8f5ef;
  --text:    #3a3a3a;
  --muted:   #888;
}
* { box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; color: var(--text); background: #fff; }
h1,h2,h3,.display-1,.display-2,.display-3,.display-4 {
  font-family: 'Cormorant Garamond', serif;
  font-weight: 300;
  letter-spacing: 0.02em;
}
.navbar {
  background: rgba(13,13,13,0.97) !important;
  backdrop-filter: blur(10px);
  padding: 1.1rem 0;
}
.navbar-brand {
  font-family: 'Cormorant Garamond', serif;
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--gold) !important;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}
.nav-link { color: rgba(255,255,255,0.8) !important; font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; }
.nav-link:hover { color: var(--gold) !important; }
.btn-gold {
  background: var(--gold);
  color: #fff;
  border: none;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.7rem 2rem;
  transition: background 0.2s;
}
.btn-gold:hover { background: var(--gold-lt); color: #fff; }
.btn-outline-gold {
  border: 1px solid var(--gold);
  color: var(--gold);
  background: transparent;
  letter-spacing: 0.07em;
  text-transform: uppercase;
  font-size: 0.8rem;
  font-weight: 600;
  padding: 0.7rem 2rem;
  transition: all 0.2s;
}
.btn-outline-gold:hover { background: var(--gold); color: #fff; }
.section-label {
  font-family: 'Inter', sans-serif;
  font-size: 0.72rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: var(--gold);
  font-weight: 600;
}
footer { background: var(--dark); color: rgba(255,255,255,0.55); padding: 3rem 0 1.5rem; }
footer a { color: var(--gold-lt); text-decoration: none; }
.form-control, .form-select {
  border: 1px solid #ddd;
  border-radius: 0;
  padding: 0.7rem 1rem;
  font-size: 0.9rem;
}
.form-control:focus, .form-select:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(184,150,62,0.15);
}
</style>
<?= $extraHead ?? '' ?>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container">
    <a class="navbar-brand" href="<?= APP_URL ?>">
      <i class="bi bi-building me-1"></i>Satori Residences
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon" style="filter:invert(1)"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= APP_URL ?>/units.php">Units</a></li>
        <li class="nav-item ms-2"><a class="btn btn-gold" href="<?= APP_URL ?>/booking.php">Book Now</a></li>
      </ul>
    </div>
  </div>
</nav>
