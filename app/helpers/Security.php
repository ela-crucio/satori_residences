<?php
// app/helpers/Security.php

class Security {

    // ---- CSRF -------------------------------------------------------

    public static function generateCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token']) ||
            (time() - ($_SESSION['csrf_token_time'] ?? 0)) > CSRF_TOKEN_LIFETIME) {
            $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(string $token): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function csrfField(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    // ---- Input Sanitization -----------------------------------------

    public static function sanitize($input) {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeEmail($email) {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }

    public static function sanitizeInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    public static function sanitizeDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return ($d && $d->format('Y-m-d') === $date) ? $date : false;
    }

    // ---- Password ---------------------------------------------------

    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    // ---- Session ----------------------------------------------------

    public static function startSecureSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function regenerateSession(): void {
        session_regenerate_id(true);
    }

    public static function destroySession(): void {
        session_unset();
        session_destroy();
    }

    // ---- Auth Guards ------------------------------------------------

    public static function requireAdminAuth(): void {
        self::startSecureSession();
        if (empty($_SESSION['admin_id'])) {
            header('Location: ' . ADMIN_URL . '/login.php');
            exit;
        }
    }

    public static function requireAdminRole(string $role = 'admin'): void {
        self::requireAdminAuth();
        if (($_SESSION['admin_role'] ?? '') !== $role) {
            http_response_code(403);
            die('Access denied.');
        }
    }
}
