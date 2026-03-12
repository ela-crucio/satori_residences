<?php require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/models/BookingModel.php';
require_once __DIR__ . '/../app/models/UnitTypeModel.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
Security::startSecureSession();
(new AdminController())->clients();
