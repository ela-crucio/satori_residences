<?php
// admin/login.php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';

Security::startSecureSession();
if (!empty($_SESSION['admin_id'])) { header('Location: ' . ADMIN_URL . '/dashboard.php'); exit; }

$ctrl = new AdminController();
$ctrl->login();
