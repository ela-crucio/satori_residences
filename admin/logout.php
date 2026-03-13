<?php
// admin/logout.php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../app/helpers/Security.php';
Security::startSecureSession();
Security::destroySession();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
