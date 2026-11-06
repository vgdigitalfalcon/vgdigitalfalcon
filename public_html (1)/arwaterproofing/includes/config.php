<?php
// Database configuration
// Update these values for your server
define('DB_HOST', '127.0.0.1:3306');
define('DB_NAME', 'u670120666_arwater');
define('DB_USER', 'u670120666_arwater');
define('DB_PASS', 'Arwater@2026');
define('DB_CHARSET', 'utf8mb4');

// Site settings
define('SITE_URL', 'https://vgdigitalfalcon.com/arwaterproofing/');

// Fixed: correct public path for the subdirectory
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', '/arwaterproofing/uploads/');

// Session
session_start();

// Database connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Auth helpers
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}
function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

/**
 * Converts stored file path to correct public URL
 * Works with both old paths (/uploads/...) and new paths
 */
function getPublicMediaUrl($filePath) {
    if (empty($filePath)) {
        return '';
    }

    // Already full URL
    if (strpos($filePath, 'http://') === 0 || strpos($filePath, 'https://') === 0) {
        return $filePath;
    }

    // Old stored path: /uploads/file.jpg → /arwaterproofing/uploads/file.jpg
    if (strpos($filePath, '/uploads/') === 0) {
        return '/arwaterproofing' . $filePath;
    }

    // Relative path without leading slash
    if (strpos($filePath, 'uploads/') === 0) {
        return '/arwaterproofing/' . $filePath;
    }

    return $filePath;
}

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

/**
 * Get a relevant image URL based on service name.
 * Prefers the exact local asset you provided; falls back to Unsplash only if no match.
 */
function getServiceImage($serviceName, $customPath = null) {
    if (!empty($customPath)) {
        return $customPath;
    }
    
    $name = strtolower(trim($serviceName));
    
    // Exact local assets for your 20 services
    $imageMap = [
        'old residential building'              => 'assets/ar-service-01.png',
        'plastic felt treatment'                => 'assets/ar-service-02.png',
        'epoxy tile grout'                      => 'assets/ar-service-03.png',
        'high rise building outer wall'         => 'assets/ar-service-04.png',
        'high rise building structural'         => 'assets/ar-service-05.png',
        'fountain'                              => 'assets/ar-service-06.png',
        'new terrace'                           => 'assets/ar-service-07.png',
        'pu grouting'                           => 'assets/ar-service-08.png',
        'basement waterproofing'                => 'assets/ar-service-09.png',
        'construction works'                    => 'assets/ar-service-10.png',
        'swimming pool'                         => 'assets/ar-service-11.png',
        'old terrace'                           => 'assets/ar-service-12.png',
        'scaffolding'                           => 'assets/ar-service-13.png',
        'painting'                              => 'assets/ar-service-14.png',
        'water tank'                            => 'assets/ar-service-15.png',
        'ug sump'                               => 'assets/ar-service-16.png',
        'landscaping'                           => 'assets/ar-service-17.png',
        'building renovation'                   => 'assets/ar-service-18.png',
        'sunken slab'                           => 'assets/ar-service-19.png',
        'structural repairs'                    => 'assets/ar-service-20.png',
        // Broader keyword fallbacks
        'terrace'     => 'assets/ar-service-07.png',
        'roof'        => 'assets/ar-service-12.png',
        'basement'    => 'assets/ar-service-09.png',
        'pool'        => 'assets/ar-service-11.png',
        'grout'       => 'assets/ar-service-03.png',
        'sump'        => 'assets/ar-service-16.png',
        'tank'        => 'assets/ar-service-15.png',
        'scaffold'    => 'assets/ar-service-13.png',
        'landscape'   => 'assets/ar-service-17.png',
        'garden'      => 'assets/ar-service-17.png',
        'renovat'     => 'assets/ar-service-18.png',
        'paint'       => 'assets/ar-service-14.png',
        'structural'  => 'assets/ar-service-20.png',
        'repair'      => 'assets/ar-service-20.png',
        'construct'   => 'assets/ar-service-10.png',
        'old house'   => 'assets/ar-service-01.png',
        'house'       => 'assets/ar-service-01.png',
        'building'    => 'assets/ar-service-04.png',
        'high rise'   => 'assets/ar-service-04.png',
        'wall'        => 'assets/ar-service-04.png',
        'epoxy'       => 'assets/ar-service-03.png',
        'felt'        => 'assets/ar-service-02.png',
        'fountain'    => 'assets/ar-service-06.png',
    ];
    
    // Prefer longer / more specific matches first
    uksort($imageMap, function($a, $b) {
        return strlen($b) - strlen($a);
    });
    
    foreach ($imageMap as $keyword => $url) {
        if (strpos($name, $keyword) !== false) {
            return $url;
        }
    }
    
    // Final fallback
    return 'assets/ar-service-01.png';
}