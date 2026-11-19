<?php
/**
 * VG Digital Falcon - Unified Lead & Enquiry API Endpoint
 * Handles submissions for SEO, SMO, Performance Marketing, YouTube, Web Dev, Personal Branding, Ecommerce, and Contact.
 */

header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../core/Validator.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Mailer.php';

use VGDigitalFalcon\Core\Validator;
use VGDigitalFalcon\Core\Database;
use VGDigitalFalcon\Core\Mailer;

// Determine if request expects JSON
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
    || (isset($_POST['format']) && $_POST['format'] === 'json');

function respond(bool $success, string $message, ?string $redirectUrl = null, int $statusCode = 200) {
    global $isAjax;
    http_response_code($statusCode);

    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'redirect' => $redirectUrl
        ]);
        exit;
    }

    if ($redirectUrl) {
        header("Location: " . $redirectUrl);
        exit;
    }

    // Fallback HTML response if no redirect specified
    echo "<!DOCTYPE html><html><head><title>Submission Result</title></head><body><p>" . htmlspecialchars($message) . "</p></body></html>";
    exit;
}

// 1. Method verification
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, "Invalid request method. Only POST is accepted.", null, 405);
}

// 2. Anti-spam honeypot detection
if (Validator::isSpam($_POST, 'address')) {
    // Silently succeed to mislead bots
    respond(true, "Thank you! Your inquiry has been received.", "thankyou.html");
}

// 3. CAPTCHA verification (if submitted)
if (isset($_POST['captcha_answer']) && !Validator::verifyCaptcha($_POST['captcha_answer'])) {
    respond(false, "Verification failed. Please enter the correct security answer.", null, 400);
}

// 4. Capture and sanitize inputs
$name     = Validator::sanitizeString($_POST['name'] ?? '');
$email    = Validator::sanitizeString($_POST['email'] ?? '');
$phone    = Validator::sanitizeString($_POST['phone'] ?? $_POST['whatsapp'] ?? '');
$website  = Validator::sanitizeString($_POST['website'] ?? '');
$channel  = Validator::sanitizeString($_POST['channel'] ?? '');
$budget   = Validator::sanitizeString($_POST['budget'] ?? '');
$subject  = Validator::sanitizeString($_POST['subject'] ?? $_POST['service_subject'] ?? '');
$message  = Validator::sanitizeString($_POST['message'] ?? '', 3000);
$service  = Validator::sanitizeString($_POST['service'] ?? '');

// Infer service if not explicitly passed
if (empty($service)) {
    if (!empty($channel)) {
        $service = 'youtube-seo';
    } elseif (!empty($budget)) {
        $service = 'performance-marketing';
    } elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'social-media') !== false) {
        $service = 'smo';
    } elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'ecommerce') !== false) {
        $service = 'ecommerce-seo';
    } elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'personal-branding') !== false) {
        $service = 'personal-branding';
    } elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'web-development') !== false) {
        $service = 'web-development';
    } elseif (strpos($_SERVER['HTTP_REFERER'] ?? '', 'seo-service') !== false) {
        $service = 'seo';
    } else {
        $service = 'general-inquiry';
    }
}

// 5. Validation rules
if (empty($name)) {
    respond(false, "Please provide your full name.", null, 422);
}

if (!empty($email) && !Validator::validateEmail($email)) {
    respond(false, "Please provide a valid email address.", null, 422);
}

if (!empty($phone) && !Validator::validatePhone($phone)) {
    respond(false, "Please provide a valid phone or WhatsApp number.", null, 422);
}

// 6. Record in Database
$dbData = [
    'service' => $service,
    'name'    => $name,
    'email'   => $email,
    'phone'   => $phone,
    'website' => $website,
    'channel' => $channel,
    'budget'  => $budget,
    'subject' => $subject,
    'message' => $message
];

Database::getInstance()->saveLead($dbData);

// 7. Dispatch Mailer notification
$mailer = new Mailer();
$emailSubject = !empty($subject) ? "New {$service} Lead: {$subject}" : "New Lead Submission [{$service}] - {$name}";

$fields = [
    'Service / Channel' => strtoupper(str_replace('-', ' ', $service)),
    'Name'              => $name,
    'Email'             => $email,
    'Phone / WhatsApp'  => $phone,
    'Website'           => $website,
    'YouTube Channel'   => $channel,
    'Budget Range'      => $budget,
    'Subject'           => $subject,
    'Message / Goals'   => $message,
];

$mailer->sendNotification($emailSubject, $fields, !empty($email) ? $email : null);

// 8. Determine redirect destination
$redirectTarget = "thankyou.html";
if (!empty($_SERVER['HTTP_REFERER'])) {
    $parsedReferer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH);
    $pageName = basename($parsedReferer);
    if (!empty($pageName) && $pageName !== 'thankyou.html') {
        // If form originated from a service page without AJAX, can redirect back with success query
        $redirectTarget = "thankyou.html";
    }
}

respond(true, "Thank you! Your request has been received. An executive strategist will connect with you shortly.", $redirectTarget);
