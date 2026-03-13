<?php
// app/models/BookingModel.php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/app.php';

class BookingModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ---- AVAILABILITY CHECK -----------------------------------------

    /**
     * Check if a unit type is available for requested dates.
     * Uses standard overlap logic:
     *   existing_checkin < requested_checkout AND existing_checkout > requested_checkin
     */
    public function isUnitAvailable($unitTypeId, $checkIn, $checkOut, $excludeBookingId = null) {
        $sql = "SELECT COUNT(*) FROM bookings
                WHERE unit_type_id = :unit_type_id
                  AND booking_status NOT IN ('cancelled')
                  AND check_in_date  < :checkout
                  AND check_out_date > :checkin";

        if ($excludeBookingId) {
            $sql .= " AND booking_id != :exclude_id";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':unit_type_id', $unitTypeId, PDO::PARAM_INT);
        $stmt->bindValue(':checkin',      $checkIn,    PDO::PARAM_STR);
        $stmt->bindValue(':checkout',     $checkOut,   PDO::PARAM_STR);

        if ($excludeBookingId) {
            $stmt->bindValue(':exclude_id', $excludeBookingId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn() === 0;
    }

    /**
     * Get all booked date ranges for a unit type (for calendar rendering).
     */
    public function getBookedDates(int $unitTypeId): array {
        $stmt = $this->db->prepare(
            "SELECT check_in_date, check_out_date FROM bookings
             WHERE unit_type_id = :uid
               AND booking_status NOT IN ('cancelled')
               AND check_out_date >= CURDATE()
             ORDER BY check_in_date"
        );
        $stmt->execute([':uid' => $unitTypeId]);
        return $stmt->fetchAll();
    }

    // ---- GUEST UPSERT -----------------------------------------------

    public function upsertGuest(string $fullName, string $email, string $phone): int {
        // Try to find existing guest by email
        $stmt = $this->db->prepare("SELECT guest_id FROM guests WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $existing = $stmt->fetch();

        if ($existing) {
            // Update info
            $upd = $this->db->prepare(
                "UPDATE guests SET full_name = :name, phone = :phone, updated_at = NOW()
                 WHERE guest_id = :id"
            );
            $upd->execute([':name' => $fullName, ':phone' => $phone, ':id' => $existing['guest_id']]);
            return (int)$existing['guest_id'];
        }

        $ins = $this->db->prepare(
            "INSERT INTO guests (full_name, email, phone) VALUES (:name, :email, :phone)"
        );
        $ins->execute([':name' => $fullName, ':email' => $email, ':phone' => $phone]);
        return (int)$this->db->lastInsertId();
    }

    // ---- CREATE BOOKING ---------------------------------------------

    public function createBooking(array $data): int {
        $ref = $this->generateReference();

        $stmt = $this->db->prepare(
            "INSERT INTO bookings
               (booking_reference, guest_id, unit_type_id, check_in_date, check_out_date,
                number_of_guests, price_per_night, total_nights, total_price,
                special_requests, booking_status)
             VALUES
               (:ref, :guest_id, :unit_type_id, :checkin, :checkout,
                :num_guests, :price_night, :total_nights, :total_price,
                :special_req, 'pending')"
        );
        $stmt->execute([
            ':ref'          => $ref,
            ':guest_id'     => $data['guest_id'],
            ':unit_type_id' => $data['unit_type_id'],
            ':checkin'      => $data['check_in_date'],
            ':checkout'     => $data['check_out_date'],
            ':num_guests'   => $data['number_of_guests'],
            ':price_night'  => $data['price_per_night'],
            ':total_nights' => $data['total_nights'],
            ':total_price'  => $data['total_price'],
            ':special_req'  => $data['special_requests'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ---- CREATE PAYMENT ---------------------------------------------

    public function createPayment(int $bookingId, string $method, float $amount): string {
        $txRef = $this->generateTransactionRef($method);
        $status = ($method === 'cash_on_arrival') ? 'pending' : 'paid';
        $payDate = ($status === 'paid') ? date('Y-m-d H:i:s') : null;

        $stmt = $this->db->prepare(
            "INSERT INTO payments (booking_id, payment_method, payment_status,
               transaction_reference, amount, payment_date)
             VALUES (:bid, :method, :status, :txref, :amount, :paydate)"
        );
        $stmt->execute([
            ':bid'     => $bookingId,
            ':method'  => $method,
            ':status'  => $status,
            ':txref'   => $txRef,
            ':amount'  => $amount,
            ':paydate' => $payDate,
        ]);

        // Confirm booking if paid
        if ($status === 'paid') {
            $this->updateBookingStatus($bookingId, 'confirmed');
        }

        return $txRef;
    }

    // ---- LOG ACTION -------------------------------------------------

    public function logAction(int $bookingId, string $action, string $by, ?string $notes = null): void {
        $stmt = $this->db->prepare(
            "INSERT INTO booking_logs (booking_id, action, performed_by, notes)
             VALUES (:bid, :action, :by, :notes)"
        );
        $stmt->execute([':bid' => $bookingId, ':action' => $action, ':by' => $by, ':notes' => $notes]);
    }

    // ---- STATUS UPDATE ----------------------------------------------

    public function updateBookingStatus(int $bookingId, string $status): void {
        $stmt = $this->db->prepare(
            "UPDATE bookings SET booking_status = :status WHERE booking_id = :id"
        );
        $stmt->execute([':status' => $status, ':id' => $bookingId]);
    }

    // ---- FETCH SINGLE BOOKING ---------------------------------------

    public function getBookingByReference($ref) {
        $stmt = $this->db->prepare(
            "SELECT b.*, g.full_name, g.email, g.phone,
                    ut.name AS unit_name, ut.slug AS unit_slug,
                    p.payment_method, p.payment_status, p.transaction_reference, p.payment_date
             FROM bookings b
             JOIN guests g ON g.guest_id = b.guest_id
             JOIN unit_types ut ON ut.unit_type_id = b.unit_type_id
             LEFT JOIN payments p ON p.booking_id = b.booking_id
             WHERE b.booking_reference = :ref"
        );
        $stmt->execute([':ref' => $ref]);
        return $stmt->fetch();
    }

    public function getBookingById($id) {
        $stmt = $this->db->prepare(
            "SELECT b.*, g.full_name, g.email, g.phone,
                    ut.name AS unit_name, ut.slug AS unit_slug, ut.price_per_night,
                    p.payment_method, p.payment_status, p.transaction_reference, p.payment_date
             FROM bookings b
             JOIN guests g ON g.guest_id = b.guest_id
             JOIN unit_types ut ON ut.unit_type_id = b.unit_type_id
             LEFT JOIN payments p ON p.booking_id = b.booking_id
             WHERE b.booking_id = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // ---- ADMIN LISTING ----------------------------------------------

    public function getAllBookings(array $filters = []): array {
        $sql = "SELECT b.*, g.full_name, g.email, g.phone,
                       ut.name AS unit_name, p.payment_status, p.payment_method
                FROM bookings b
                JOIN guests g ON g.guest_id = b.guest_id
                JOIN unit_types ut ON ut.unit_type_id = b.unit_type_id
                LEFT JOIN payments p ON p.booking_id = b.booking_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['unit_type_id'])) {
            $sql .= " AND b.unit_type_id = :uid";
            $params[':uid'] = $filters['unit_type_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND b.booking_status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND b.check_in_date >= :dfrom";
            $params[':dfrom'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND b.check_out_date <= :dto";
            $params[':dto'] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (g.full_name LIKE :search OR b.booking_reference LIKE :search OR g.email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY b.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ---- DASHBOARD STATS --------------------------------------------

    public function getDashboardStats(): array {
        $stats = [];

        // Total bookings
        $stats['total_bookings'] = (int)$this->db->query(
            "SELECT COUNT(*) FROM bookings WHERE booking_status != 'cancelled'"
        )->fetchColumn();

        // Total revenue (paid)
        $stats['total_revenue'] = (float)$this->db->query(
            "SELECT COALESCE(SUM(p.amount),0) FROM payments p
             JOIN bookings b ON b.booking_id = p.booking_id
             WHERE p.payment_status = 'paid' AND b.booking_status != 'cancelled'"
        )->fetchColumn();

        // Upcoming check-ins (next 7 days)
        $stats['upcoming_checkins'] = (int)$this->db->query(
            "SELECT COUNT(*) FROM bookings
             WHERE booking_status = 'confirmed'
               AND check_in_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
        )->fetchColumn();

        // Active reservations (currently staying)
        $stats['active_reservations'] = (int)$this->db->query(
            "SELECT COUNT(*) FROM bookings
             WHERE booking_status = 'confirmed'
               AND check_in_date <= CURDATE()
               AND check_out_date > CURDATE()"
        )->fetchColumn();

        // Monthly bookings (last 6 months)
        $stats['monthly_bookings'] = $this->db->query(
            "SELECT DATE_FORMAT(check_in_date,'%Y-%m') AS month, COUNT(*) AS total
             FROM bookings WHERE booking_status != 'cancelled'
               AND check_in_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month"
        )->fetchAll();

        // Monthly revenue (last 6 months)
        $stats['monthly_revenue'] = $this->db->query(
            "SELECT DATE_FORMAT(b.check_in_date,'%Y-%m') AS month,
                    COALESCE(SUM(p.amount),0) AS revenue
             FROM bookings b
             LEFT JOIN payments p ON p.booking_id = b.booking_id AND p.payment_status='paid'
             WHERE b.booking_status != 'cancelled'
               AND b.check_in_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month"
        )->fetchAll();

        // Unit popularity
        $stats['unit_popularity'] = $this->db->query(
            "SELECT ut.name, COUNT(*) AS bookings
             FROM bookings b
             JOIN unit_types ut ON ut.unit_type_id = b.unit_type_id
             WHERE b.booking_status != 'cancelled'
             GROUP BY b.unit_type_id"
        )->fetchAll();

        return $stats;
    }

    // ---- HELPERS ----------------------------------------------------

    private function generateReference(): string {
        $year  = date('Y');
        $stmt  = $this->db->query("SELECT COUNT(*) FROM bookings");
        $count = (int)$stmt->fetchColumn() + 1;
        return sprintf('%s-%s-%06d', BOOKING_REF_PREFIX, $year, $count);
    }

    private function generateTransactionRef(string $method): string {
        $prefix = strtoupper(substr($method, 0, 3));
        return $prefix . '-' . strtoupper(bin2hex(random_bytes(6)));
    }
}
