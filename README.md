# Satori Residences Booking System
## Complete PHP + MySQL + Bootstrap Web Application

---

## PROJECT STRUCTURE

```
/satori-booking-system
│
├── /public                         # Public web root (set Apache/Nginx document root here)
│   ├── index.php                   # Homepage
│   ├── booking.php                 # Multi-step booking form
│   ├── booking-confirmation.php    # Confirmation page
│   ├── units.php                   # Unit listing page
│   ├── unit-detail.php             # Individual unit detail
│   ├── /ajax
│   │   ├── check-availability.php  # AJAX availability endpoint
│   │   └── submit-booking.php      # AJAX booking submission
│   ├── /css                        # Custom stylesheets
│   ├── /js                         # Custom JavaScript
│   └── /images                     # Property / unit images
│
├── /admin                          # Admin panel (protected)
│   ├── login.php
│   ├── dashboard.php
│   ├── bookings.php
│   ├── clients.php
│   ├── units.php
│   ├── reports.php
│   ├── calendar.php
│   ├── logout.php
│   └── /ajax
│       ├── update-status.php
│       └── update-unit.php
│
├── /app
│   ├── /controllers
│   │   ├── BookingController.php   # Public booking logic
│   │   └── AdminController.php     # Admin panel logic
│   ├── /models
│   │   ├── BookingModel.php        # Booking, guest, payment DB operations
│   │   └── UnitTypeModel.php       # Unit type DB operations
│   ├── /views
│   │   ├── /public
│   │   │   ├── _header.php
│   │   │   ├── _footer.php
│   │   │   ├── home.php
│   │   │   ├── booking.php
│   │   │   └── booking-confirmation.php
│   │   └── /admin
│   │       ├── _header.php
│   │       ├── _footer.php
│   │       ├── login.php
│   │       ├── dashboard.php
│   │       ├── bookings.php
│   │       ├── clients.php
│   │       ├── units.php
│   │       ├── reports.php
│   │       └── calendar.php
│   └── /helpers
│       └── Security.php            # CSRF, sanitization, password, session
│
├── /config
│   ├── app.php                     # App constants & settings
│   └── database.php                # PDO DB connection singleton
│
├── /database
│   └── schema.sql                  # Full DB schema + seed data
│
└── README.md
```

---

## SYSTEM ARCHITECTURE

### Design Pattern: MVC (Model-View-Controller)

```
Request → Entry Point (public/*.php)
              ↓
         Controller (validates input, calls models, loads views)
              ↓
          Model (PDO prepared statements, DB operations)
              ↓
           View (PHP templates, Bootstrap UI)
              ↓
         Response (HTML page or JSON for AJAX)
```

### Database Singleton Pattern
```php
$db = Database::getInstance(); // Returns single PDO instance
```

---

## DATABASE SCHEMA (SUMMARY)

| Table          | Purpose                                       |
|----------------|-----------------------------------------------|
| `admins`       | Admin/staff accounts with bcrypt passwords    |
| `unit_types`   | 1BR / 2BR / 3BR unit configurations           |
| `unit_images`  | Photos linked to each unit type               |
| `guests`       | Guest profiles (email-deduplicated)           |
| `bookings`     | Core booking records with date ranges         |
| `payments`     | Simulated payment records per booking         |
| `booking_logs` | Audit trail of all booking actions            |

---

## BOOKING VALIDATION LOGIC

### Overlap Detection Algorithm

```sql
-- Conflict exists if:
existing_check_in  < requested_check_out
AND
existing_check_out > requested_check_in

-- Example:
-- Existing booking: June 10 → June 15
-- Requested:        June 12 → June 14  ← CONFLICT
-- Requested:        June 9  → June 11  ← CONFLICT (June 11 > June 10)
-- Requested:        June 15 → June 17  ← ALLOWED  (June 15 = June 15, not >)
-- Requested:        June 5  → June 10  ← ALLOWED  (June 10 = June 10, not <)
```

PHP implementation:
```php
public function isUnitAvailable(int $unitTypeId, string $checkIn, string $checkOut): bool {
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE unit_type_id = :unit_type_id
              AND booking_status NOT IN ('cancelled')
              AND check_in_date  < :checkout
              AND check_out_date > :checkin";
    // Returns true if COUNT = 0 (no conflicts)
}
```

---

## PAYMENT SIMULATION LOGIC

### How it works:
1. User selects: GCash / Online Payment / Cash on Arrival
2. Frontend simulates redirect delay (setTimeout)
3. Backend determines payment status:
   - **GCash / Online Payment** → `payment_status = 'paid'`, `booking_status = 'confirmed'`
   - **Cash on Arrival** → `payment_status = 'pending'`, `booking_status = 'pending'`
4. Transaction reference generated: `GCA-A1B2C3D4E5F6`

```php
private function generateTransactionRef(string $method): string {
    $prefix = strtoupper(substr($method, 0, 3));
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(6)));
}
```

---

## SECURITY IMPLEMENTATION

| Feature             | Implementation                                    |
|---------------------|---------------------------------------------------|
| Password Hashing    | `password_hash()` with COST=12 bcrypt             |
| SQL Injection       | PDO prepared statements with bound parameters     |
| CSRF Protection     | Token per session, validated on every POST        |
| XSS Prevention      | `htmlspecialchars()` on all output                |
| Input Validation    | `filter_var()`, custom sanitize helpers           |
| Session Security    | `httponly`, `samesite=Strict`, `use_strict_mode`  |
| Auth Guard          | `Security::requireAdminAuth()` on all admin pages |

---

## DEPLOYMENT INSTRUCTIONS

### Requirements
- PHP 8.1+ with PDO and PDO_MySQL extensions
- MySQL 5.7+ or MariaDB 10.4+
- Apache 2.4+ with mod_rewrite OR Nginx
- Composer (optional, for future packages)

### Step 1 — Clone / Upload Files
```bash
# Upload satori-booking-system/ to your web server root
# e.g.: /var/www/html/satori-booking-system/
```

### Step 2 — Create Database
```bash
mysql -u root -p < database/schema.sql
```

### Step 3 — Configure DB Connection
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'satori_booking');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
```

### Step 4 — Configure App URLs
Edit `config/app.php`:
```php
define('APP_URL',   'https://yourdomain.com/public');
define('ADMIN_URL', 'https://yourdomain.com/admin');
```

### Step 5 — Set Admin Password
The default seed data uses a placeholder hash. Generate a real bcrypt hash:
```php
echo password_hash('YourSecurePassword!', PASSWORD_BCRYPT, ['cost' => 12]);
```
Then update the `admins` table:
```sql
UPDATE admins SET password_hash = '$2y$12$...' WHERE email = 'admin@satoriresidences.com';
```

### Step 6 — Apache Virtual Host (if needed)
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/satori-booking-system/public
    <Directory /var/www/html/satori-booking-system/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Step 7 — Add Property Images
Upload your property photos to `/public/images/`:
- `hero-bg.jpg` — homepage hero background
- `1br-main.jpg`, `2br-main.jpg`, `3br-main.jpg` — unit main images
- `property-1.jpg`, `property-2.jpg`, `property-3.jpg` — feature section

### Step 8 — Set File Permissions
```bash
chmod -R 755 /var/www/html/satori-booking-system/
chmod -R 777 /var/www/html/satori-booking-system/public/images/
```

---

## DEFAULT ADMIN CREDENTIALS

| Field    | Value                              |
|----------|------------------------------------|
| URL      | http://yourdomain.com/admin/login  |
| Email    | admin@satoriresidences.com         |
| Password | *(set via Step 5 above)*           |

---

## BOOKING REFERENCE FORMAT

```
SRB-2026-000001
 ^    ^      ^
 |    |      └── Sequential number (6 digits, zero-padded)
 |    └── Year
 └── Prefix: Satori Residences Booking
```

---

## FEATURES CHECKLIST

### Public Website
- [x] Hero homepage with CTA
- [x] Unit listing with prices & amenities
- [x] Multi-step booking wizard (5 steps)
- [x] Real-time availability checking (AJAX)
- [x] Booking confirmation page with reference number

### Booking System
- [x] Date overlap validation (critical)
- [x] Guest upsert (deduplicate by email)
- [x] Simulated payment (GCash / Online / Cash)
- [x] Payment status tracking
- [x] Booking reference generation

### Admin Panel
- [x] Secure login with bcrypt + CSRF
- [x] Dashboard with live stats + charts
- [x] Booking management with filters
- [x] Status update modal (AJAX)
- [x] Client/guest management
- [x] Unit pricing & amenity editor
- [x] FullCalendar booking calendar
- [x] Reports with CSV export + print

### Security
- [x] bcrypt password hashing
- [x] PDO prepared statements
- [x] CSRF token validation
- [x] XSS output escaping
- [x] Session hardening
- [x] Input sanitization & validation
- [x] Auth guards on all admin routes
