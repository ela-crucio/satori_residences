<?php
// public/index.php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/Security.php';
require_once __DIR__ . '/../app/models/UnitTypeModel.php';

Security::startSecureSession();

$unitModel = new UnitTypeModel();
$units     = $unitModel->getAll();

require_once __DIR__ . '/../app/views/public/home.php';
