<?php
// public/booking-confirmation.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/models/BookingModel.php';
require_once __DIR__ . '/../app/controllers/BookingController.php';

$ref = $_GET['ref'] ?? '';
if (!$ref) { header('Location: ' . APP_URL); exit; }

$ctrl = new BookingController();
$ctrl->showConfirmation($ref);
