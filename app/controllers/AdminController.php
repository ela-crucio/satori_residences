<?php
// app/controllers/AdminController.php

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/BookingModel.php';
require_once __DIR__ . '/../models/UnitTypeModel.php';
require_once __DIR__ . '/../helpers/Security.php';

class AdminController {

    private BookingModel  $bookingModel;
    private UnitTypeModel $unitModel;
    private PDO           $db;

    public function __construct() {
        Security::startSecureSession();
        $this->bookingModel = new BookingModel();
        $this->unitModel    = new UnitTypeModel();
        $this->db           = Database::getInstance();
    }

    // ---- AUTH -------------------------------------------------------

    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $error = 'Security error. Please try again.';
                include __DIR__ . '/../views/admin/login.php';
                return;
            }

            $email    = Security::sanitizeEmail($_POST['email']    ?? '');
            $password = $_POST['password'] ?? '';

            $stmt = $this->db->prepare("SELECT * FROM admins WHERE email = :email AND is_active = 1");
            $stmt->execute([':email' => $email]);
            $admin = $stmt->fetch();

            if ($admin && Security::verifyPassword($password, $admin['password_hash'])) {
                Security::regenerateSession();
                $_SESSION['admin_id']   = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['full_name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['admin_email']= $admin['email'];

                // Update last login
                $this->db->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = :id")
                         ->execute([':id' => $admin['admin_id']]);

                header('Location: ' . ADMIN_URL . '/dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
                include __DIR__ . '/../views/admin/login.php';
                return;
            }
        }
        include __DIR__ . '/../views/admin/login.php';
    }

    public function logout(): void {
        Security::destroySession();
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }

    // ---- DASHBOARD --------------------------------------------------

    public function dashboard(): void {
        Security::requireAdminAuth();
        $stats = $this->bookingModel->getDashboardStats();
        $recentBookings = $this->bookingModel->getAllBookings(['limit' => 10]);
        include __DIR__ . '/../views/admin/dashboard.php';
    }

    // ---- BOOKINGS ---------------------------------------------------

    public function bookings(): void {
        Security::requireAdminAuth();
        $filters  = [
            'unit_type_id' => Security::sanitizeInt($_GET['unit_type_id'] ?? 0) ?: null,
            'status'       => Security::sanitize($_GET['status']      ?? ''),
            'date_from'    => Security::sanitizeDate($_GET['date_from'] ?? '') ?: null,
            'date_to'      => Security::sanitizeDate($_GET['date_to']   ?? '') ?: null,
            'search'       => Security::sanitize($_GET['search']       ?? ''),
        ];
        $bookings  = $this->bookingModel->getAllBookings(array_filter($filters));
        $units     = $this->unitModel->getAll(false);
        include __DIR__ . '/../views/admin/bookings.php';
    }

    public function updateBookingStatus(): void {
        Security::requireAdminAuth();
        header('Content-Type: application/json');

        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Security error.']);
            return;
        }

        $bookingId = Security::sanitizeInt($_POST['booking_id'] ?? 0);
        $status    = Security::sanitize($_POST['status']        ?? '');

        if (!$bookingId || !in_array($status, ['pending','confirmed','cancelled','completed'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            return;
        }

        $this->bookingModel->updateBookingStatus($bookingId, $status);
        $this->bookingModel->logAction($bookingId, 'Status updated to ' . $status,
            $_SESSION['admin_name'] ?? 'Admin');

        echo json_encode(['success' => true, 'message' => 'Status updated.']);
    }

    // ---- CLIENTS ----------------------------------------------------

    public function clients(): void {
        Security::requireAdminAuth();
        $search  = Security::sanitize($_GET['search'] ?? '');
        $sql     = "SELECT g.*, COUNT(b.booking_id) AS total_bookings,
                           COALESCE(SUM(b.total_price),0) AS total_spent
                    FROM guests g
                    LEFT JOIN bookings b ON b.guest_id = g.guest_id AND b.booking_status != 'cancelled'
                    WHERE 1=1";
        $params  = [];
        if ($search) {
            $sql .= " AND (g.full_name LIKE :s OR g.email LIKE :s OR g.phone LIKE :s)";
            $params[':s'] = '%' . $search . '%';
        }
        $sql    .= " GROUP BY g.guest_id ORDER BY g.created_at DESC";
        $stmt    = $this->db->prepare($sql);
        $stmt->execute($params);
        $clients = $stmt->fetchAll();
        include __DIR__ . '/../views/admin/clients.php';
    }

    // ---- UNITS ------------------------------------------------------

    public function units(): void {
        Security::requireAdminAuth();
        $units = $this->unitModel->getAll(false);
        include __DIR__ . '/../views/admin/units.php';
    }

    public function updateUnit(): void {
        Security::requireAdminAuth();
        if (!Security::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            die('Security error.');
        }
        $id   = Security::sanitizeInt($_POST['unit_type_id'] ?? 0);
        $data = [
            'name'          => Security::sanitize($_POST['name']          ?? ''),
            'description'   => Security::sanitize($_POST['description']   ?? ''),
            'amenities'     => array_map([Security::class, 'sanitize'], $_POST['amenities'] ?? []),
            'max_guests'    => Security::sanitizeInt($_POST['max_guests'] ?? 2),
            'price_per_night'=> (float)($_POST['price_per_night']         ?? 0),
        ];
        $this->unitModel->update($id, $data);
        header('Location: ' . ADMIN_URL . '/units.php?updated=1');
        exit;
    }

    // ---- REPORTS ----------------------------------------------------

    public function reports(): void {
        Security::requireAdminAuth();
        $dateFrom = Security::sanitizeDate($_GET['date_from'] ?? date('Y-m-01')) ?: date('Y-m-01');
        $dateTo   = Security::sanitizeDate($_GET['date_to']   ?? date('Y-m-t'))  ?: date('Y-m-t');

        $stmt = $this->db->prepare(
            "SELECT b.*, g.full_name, g.email, g.phone,
                    ut.name AS unit_name, p.payment_method, p.payment_status
             FROM bookings b
             JOIN guests g ON g.guest_id = b.guest_id
             JOIN unit_types ut ON ut.unit_type_id = b.unit_type_id
             LEFT JOIN payments p ON p.booking_id = b.booking_id
             WHERE b.check_in_date BETWEEN :f AND :t
             ORDER BY b.check_in_date"
        );
        $stmt->execute([':f' => $dateFrom, ':t' => $dateTo]);
        $reportData = $stmt->fetchAll();

        $totalRevenue = array_sum(array_map(fn($r) => $r['payment_status'] === 'paid' ? $r['total_price'] : 0, $reportData));

        include __DIR__ . '/../views/admin/reports.php';
    }

    // ---- CALENDAR ---------------------------------------------------

    public function calendar(): void {
        Security::requireAdminAuth();
        $stmt = $this->db->query(
            "SELECT b.booking_reference, b.check_in_date, b.check_out_date,
                    b.booking_status, g.full_name, ut.name AS unit_name, ut.slug
             FROM bookings b
             JOIN guests g ON g.guest_id = b.guest_id
             JOIN unit_types ut ON ut.unit_type_id = b.unit_type_id
             WHERE b.booking_status NOT IN ('cancelled')
               AND b.check_out_date >= CURDATE()
             ORDER BY b.check_in_date"
        );
        $calendarBookings = $stmt->fetchAll();
        include __DIR__ . '/../views/admin/calendar.php';
    }
}
