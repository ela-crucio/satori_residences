<?php
// admin/ajax/update-status.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/Security.php';
require_once __DIR__ . '/../../app/models/BookingModel.php';
require_once __DIR__ . '/../../app/models/UnitTypeModel.php';
require_once __DIR__ . '/../../app/controllers/AdminController.php';

Security::startSecureSession();
(new AdminController())->updateBookingStatus();
