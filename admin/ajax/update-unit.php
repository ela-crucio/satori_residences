<?php
// admin/ajax/update-unit.php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../app/helpers/Security.php';
require_once __DIR__ . '/../../app/models/UnitTypeModel.php';

Security::startSecureSession();
Security::requireAdminAuth();

if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
    die('Security error.');
}

$id          = Security::sanitizeInt($_POST['unit_type_id'] ?? 0);
$amenitiesRaw= $_POST['amenities_raw'] ?? '';
$amenities   = array_filter(array_map('trim', explode("\n", $amenitiesRaw)));

$data = [
    'name'           => Security::sanitize($_POST['name']          ?? ''),
    'description'    => Security::sanitize($_POST['description']   ?? ''),
    'amenities'      => array_values($amenities),
    'max_guests'     => Security::sanitizeInt($_POST['max_guests'] ?? 2),
    'price_per_night'=> (float)($_POST['price_per_night']          ?? 0),
];

(new UnitTypeModel())->update($id, $data);
header('Location: ' . ADMIN_URL . '/units.php?updated=1');
exit;
