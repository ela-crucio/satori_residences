<?php
// config/app.php

define('APP_NAME', 'Satori Residences Booking System');
define('APP_URL',  'http://localhost/satori-booking-system/public');
define('ADMIN_URL', 'http://localhost/satori-booking-system/admin');

// Timezone
date_default_timezone_set('Asia/Manila');

// Session settings (call before session_start())
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// CSRF token lifetime (seconds)
define('CSRF_TOKEN_LIFETIME', 3600);

// Booking reference prefix
define('BOOKING_REF_PREFIX', 'SRB');

// Min nights booking
define('MIN_NIGHTS', 1);

// Max nights booking
define('MAX_NIGHTS', 30);

// Min days ahead required to book
define('MIN_DAYS_AHEAD', 0);
