<?php
// ============================================================
// VG Digital Falcon — Database Configuration
// ============================================================
define('DB_HOST',     '127.0.0.1');
define('DB_USER',     'u670120666_programaticseo');          // Change to your DB user
define('DB_PASS',     'Programaticseo@2026');              // Change to your DB password
define('DB_NAME',     'u670120666_programaticseo');
define('DB_CHARSET',  'utf8mb4');

// Site Configuration
define('SITE_NAME',   'VG Digital Falcon');
define('SITE_URL',    'https://vgdigitalfalcon.com');  // Change to your domain
define('SITE_PHONE',  '+91 99999 00000');
define('SITE_EMAIL',  'contact@vgdigitalfalcon.com');
define('SITE_ADDRESS','Chennai, Tamil Nadu, India');

// Google Tag / Analytics (optional)
define('GTM_ID',      '');   // e.g. GTM-XXXXXXX
define('GA4_ID',      '');   // e.g. G-XXXXXXXXXX

// ============================================================
// PDO Connection (singleton)
// ============================================================
class DB {
    private static ?PDO $pdo = null;

    public static function connect(): PDO {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Database connection failed.']));
            }
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): array {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function queryOne(string $sql, array $params = []): ?array {
        $result = self::query($sql, $params);
        return $result[0] ?? null;
    }

    public static function execute(string $sql, array $params = []): int {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return (int)self::connect()->lastInsertId();
    }
}
