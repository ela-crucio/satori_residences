<?php
// app/controllers/BookingController.php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/BookingModel.php';
require_once __DIR__ . '/../models/UnitTypeModel.php';
require_once __DIR__ . '/../helpers/Security.php';

class BookingController {

    private BookingModel  $bookingModel;
    private UnitTypeModel $unitModel;

    public function __construct() {
        Security::startSecureSession();
        $this->bookingModel = new BookingModel();
        $this->unitModel    = new UnitTypeModel();
    }

    // ---- STEP 1 & 2: Show booking form ------------------------------

    public function showBookingPage(?string $unitSlug = null): void {
        $units = $this->unitModel->getAll();
        $selectedUnit = $unitSlug ? $this->unitModel->getBySlug($unitSlug) : null;
        include __DIR__ . '/../views/public/booking.php';
    }

    // ---- AJAX: Check availability -----------------------------------

    public function checkAvailability(): void {
        header('Content-Type: application/json');

        $unitTypeId = Security::sanitizeInt($_POST['unit_type_id'] ?? 0);
        $checkIn    = Security::sanitizeDate($_POST['check_in']    ?? '');
        $checkOut   = Security::sanitizeDate($_POST['check_out']   ?? '');

        if (!$unitTypeId || !$checkIn || !$checkOut) {
            echo json_encode(['available' => false, 'message' => 'Invalid input.']);
            return;
        }

        if ($checkIn >= $checkOut) {
            echo json_encode(['available' => false, 'message' => 'Check-out must be after check-in.']);
            return;
        }

        $today = date('Y-m-d');
        if ($checkIn < $today) {
            echo json_encode(['available' => false, 'message' => 'Check-in cannot be in the past.']);
            return;
        }

        $nights = (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400);
        if ($nights < MIN_NIGHTS) {
            echo json_encode(['available' => false, 'message' => 'Minimum stay is ' . MIN_NIGHTS . ' night(s).']);
            return;
        }
        if ($nights > MAX_NIGHTS) {
            echo json_encode(['available' => false, 'message' => 'Maximum stay is ' . MAX_NIGHTS . ' nights.']);
            return;
        }

        $available = $this->bookingModel->isUnitAvailable($unitTypeId, $checkIn, $checkOut);
        $unit      = $this->unitModel->getById($unitTypeId);

        if ($available) {
            echo json_encode([
                'available'      => true,
                'message'        => 'Great news! This unit is available.',
                'nights'         => $nights,
                'price_per_night'=> $unit['price_per_night'],
                'total_price'    => $nights * $unit['price_per_night'],
            ]);
        } else {
            echo json_encode([
                'available' => false,
                'message'   => 'Sorry, the selected unit type is not available for these dates.',
            ]);
        }
    }

    // ---- STEP 3: Process booking form submission --------------------

    public function submitBooking(): void {
        header('Content-Type: application/json');

        // Validate CSRF
        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Security validation failed.']);
            return;
        }

        // Sanitize inputs
        $unitTypeId    = Security::sanitizeInt($_POST['unit_type_id']     ?? 0);
        $checkIn       = Security::sanitizeDate($_POST['check_in_date']   ?? '');
        $checkOut      = Security::sanitizeDate($_POST['check_out_date']  ?? '');
        $fullName      = Security::sanitize($_POST['full_name']           ?? '');
        $email         = Security::sanitizeEmail($_POST['email']          ?? '');
        $phone         = Security::sanitize($_POST['phone']               ?? '');
        $numGuests     = Security::sanitizeInt($_POST['number_of_guests'] ?? 1);
        $specialReq    = Security::sanitize($_POST['special_requests']    ?? '');
        $paymentMethod = Security::sanitize($_POST['payment_method']      ?? '');

        // Basic validation
        $errors = [];
        if (!$unitTypeId)                 $errors[] = 'Invalid unit type.';
        if (!$checkIn)                    $errors[] = 'Invalid check-in date.';
        if (!$checkOut)                   $errors[] = 'Invalid check-out date.';
        if (empty($fullName))             $errors[] = 'Full name is required.';
        if (!$email)                      $errors[] = 'Valid email is required.';
        if (empty($phone))                $errors[] = 'Phone number is required.';
        if (!$numGuests || $numGuests < 1)$errors[] = 'Number of guests is required.';
        if (!in_array($paymentMethod, ['gcash','online_payment','cash_on_arrival'])) {
            $errors[] = 'Invalid payment method.';
        }

        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
            return;
        }

        // Date logic
        if ($checkIn >= $checkOut) {
            echo json_encode(['success' => false, 'message' => 'Check-out must be after check-in.']);
            return;
        }

        $nights = (int)((strtotime($checkOut) - strtotime($checkIn)) / 86400);
        $unit   = $this->unitModel->getById($unitTypeId);

        if (!$unit) {
            echo json_encode(['success' => false, 'message' => 'Unit not found.']);
            return;
        }

        if ($numGuests > $unit['max_guests']) {
            echo json_encode(['success' => false, 'message' => 'Too many guests for this unit type.']);
            return;
        }

        // Re-check availability (double check before insert)
        if (!$this->bookingModel->isUnitAvailable($unitTypeId, $checkIn, $checkOut)) {
            echo json_encode([
                'success' => false,
                'message' => 'Selected unit type is not available for these dates.',
            ]);
            return;
        }

        // Compute price
        $pricePerNight = (float)$unit['price_per_night'];
        $totalPrice    = $pricePerNight * $nights;

        try {
            // Upsert guest
            $guestId = $this->bookingModel->upsertGuest($fullName, $email, $phone);

            // Create booking
            $bookingId = $this->bookingModel->createBooking([
                'guest_id'        => $guestId,
                'unit_type_id'    => $unitTypeId,
                'check_in_date'   => $checkIn,
                'check_out_date'  => $checkOut,
                'number_of_guests'=> $numGuests,
                'price_per_night' => $pricePerNight,
                'total_nights'    => $nights,
                'total_price'     => $totalPrice,
                'special_requests'=> $specialReq ?: null,
            ]);

            // Simulate payment
            $txRef = $this->bookingModel->createPayment($bookingId, $paymentMethod, $totalPrice);

            // Log it
            $this->bookingModel->logAction($bookingId, 'Booking created', $fullName, 'Payment: ' . $paymentMethod);

            // Fetch confirmation data
            $booking = $this->bookingModel->getBookingById($bookingId);

            echo json_encode([
                'success'           => true,
                'message'           => 'Booking confirmed!',
                'booking_reference' => $booking['booking_reference'],
                'redirect'          => APP_URL . '/booking-confirmation.php?ref=' . urlencode($booking['booking_reference']),
            ]);

        } catch (Exception $e) {
            error_log('Booking error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
        }
    }

    // ---- Confirmation page ------------------------------------------

    public function showConfirmation(string $ref): void {
        $ref     = Security::sanitize($ref);
        $booking = $this->bookingModel->getBookingByReference($ref);
        if (!$booking) {
            header('Location: ' . APP_URL);
            exit;
        }
        include __DIR__ . '/../views/public/booking-confirmation.php';
    }

    // ---- AJAX: Get booked dates for calendar -----------------------

    public function getBookedDates(): void {
        header('Content-Type: application/json');
        $unitTypeId = Security::sanitizeInt($_GET['unit_type_id'] ?? 0);
        if (!$unitTypeId) { echo json_encode([]); return; }
        echo json_encode($this->bookingModel->getBookedDates($unitTypeId));
    }
}
