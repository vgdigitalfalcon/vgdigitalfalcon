<?php
/**
 * WAVESTEK — AJAX Enquiry Handler
 * POST endpoint for contact / quote form submissions
 * Returns JSON response
 */
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once 'includes/config.php';

// Rate limiting via session (simple)
session_start();
$now = time();
$_SESSION['form_attempts'] = $_SESSION['form_attempts'] ?? [];
// Remove attempts older than 10 minutes
$_SESSION['form_attempts'] = array_filter($_SESSION['form_attempts'], fn($t) => ($now - $t) < 600);
if (count($_SESSION['form_attempts']) >= 5) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many submissions. Please wait a few minutes.']);
    exit;
}

// Honeypot check (spam bot detection)
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you soon.']);
    exit;
}

// Sanitize & validate inputs
$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$phone   = trim(strip_tags($_POST['phone']   ?? ''));
$subject = trim(strip_tags($_POST['subject'] ?? 'General Enquiry'));
$message = trim(strip_tags($_POST['message'] ?? ''));
$product = trim(strip_tags($_POST['product'] ?? ''));

// Validation
$errors = [];
if (strlen($name) < 2)              $errors[] = 'Please enter your full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
if (strlen($message) < 10)          $errors[] = 'Please enter a message (at least 10 characters).';

if ($errors) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Build email body (HTML)
$product_line = $product ? "<tr><td style='padding:8px 0;color:#666;width:140px'><strong>Product Interest:</strong></td><td style='padding:8px 0'>$product</td></tr>" : '';
$html_body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:20px">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.1)">
    <div style="background:linear-gradient(135deg,#D32F2F,#b71c1c);padding:28px 32px">
      <h1 style="color:#fff;margin:0;font-size:22px;font-family:Arial Black,sans-serif;text-transform:uppercase;letter-spacing:1px">New Enquiry — Wavestek</h1>
      <p style="color:rgba(255,255,255,0.8);margin:6px 0 0;font-size:13px">Received from website contact form</p>
    </div>
    <div style="padding:32px">
      <table style="width:100%;border-collapse:collapse;font-size:14px">
        <tr><td style="padding:8px 0;color:#666;width:140px"><strong>Name:</strong></td><td style="padding:8px 0"><strong>$name</strong></td></tr>
        <tr style="background:#f9f9f9"><td style="padding:8px 0;color:#666"><strong>Email:</strong></td><td style="padding:8px 0"><a href="mailto:$email" style="color:#D32F2F">$email</a></td></tr>
        <tr><td style="padding:8px 0;color:#666"><strong>Phone:</strong></td><td style="padding:8px 0"><a href="tel:$phone" style="color:#D32F2F">$phone</a></td></tr>
        <tr style="background:#f9f9f9"><td style="padding:8px 0;color:#666"><strong>Subject:</strong></td><td style="padding:8px 0">$subject</td></tr>
        $product_line
      </table>
      <div style="margin-top:24px;padding:20px;background:#f5f6f8;border-left:4px solid #D32F2F;border-radius:0 4px 4px 0">
        <p style="margin:0 0 8px;font-weight:700;font-size:13px;text-transform:uppercase;color:#333">Message:</p>
        <p style="margin:0;color:#444;line-height:1.7;white-space:pre-wrap">$message</p>
      </div>
      <div style="margin-top:24px;padding-top:20px;border-top:1px solid #eee;font-size:12px;color:#999">
        <p style="margin:0">Submitted: " . date('d M Y, h:i A') . " IST</p>
        <p style="margin:4px 0 0">IP: " . htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "</p>
      </div>
    </div>
    <div style="background:#111;padding:20px 32px;text-align:center">
      <p style="color:rgba(255,255,255,0.5);font-size:12px;margin:0">Wavestek Power Solutions — " . ADDRESS_SALES . "</p>
    </div>
  </div>
</body>
</html>
HTML;

// Also send auto-reply to customer
$auto_reply = <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f5f5f5;margin:0;padding:20px">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.1)">
    <div style="background:linear-gradient(135deg,#D32F2F,#b71c1c);padding:28px 32px">
      <h1 style="color:#fff;margin:0;font-size:20px;font-family:Arial Black,sans-serif">Thank You, $name!</h1>
      <p style="color:rgba(255,255,255,0.8);margin:6px 0 0;font-size:13px">We've received your enquiry</p>
    </div>
    <div style="padding:32px;font-size:15px;color:#333;line-height:1.7">
      <p>Dear <strong>$name</strong>,</p>
      <p>Thank you for contacting <strong>Wavestek Power Solutions</strong>. We have received your enquiry regarding <em>$subject</em> and will get back to you within <strong>24 hours</strong>.</p>
      <p>For urgent requirements, please call or WhatsApp us directly:</p>
      <div style="background:#f5f6f8;border-radius:6px;padding:20px;margin:20px 0">
        <p style="margin:0 0 8px"><strong>📞 Phone:</strong> <a href="tel:+919884152004" style="color:#D32F2F">+91 9884152004</a></p>
        <p style="margin:0 0 8px"><strong>💬 WhatsApp:</strong> <a href="https://wa.me/919884152004" style="color:#25D366">+91 9884152004</a></p>
        <p style="margin:0"><strong>📧 Email:</strong> <a href="mailto:admin@wavestekpowersolutions.in" style="color:#3A5F8A">admin@wavestekpowersolutions.in</a></p>
      </div>
      <p style="color:#666;font-size:13px">Wavestek Power Solutions — Manufacturer, Sales &amp; Service Provider for Online UPS, Servo Voltage Stabilizers, Inverters &amp; Batteries, Chennai.</p>
    </div>
    <div style="background:#111;padding:20px 32px;text-align:center">
      <p style="color:rgba(255,255,255,0.5);font-size:12px;margin:0">&copy; <?php echo date('Y'); ?> Wavestek Power Solutions, Chennai</p>
    </div>
  </div>
</body></html>
HTML;

// Send to admin
$admin_headers  = "MIME-Version: 1.0\r\n";
$admin_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$admin_headers .= "From: Wavestek Website <website@wavestekpowersolutions.in>\r\n";
$admin_headers .= "Reply-To: $name <$email>\r\n";

// Send auto-reply to customer
$reply_headers  = "MIME-Version: 1.0\r\n";
$reply_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$reply_headers .= "From: Wavestek Power Solutions <admin@wavestekpowersolutions.in>\r\n";

$sent = mail(EMAIL_ADMIN, "Website Enquiry: $subject — $name", $html_body, $admin_headers);
if ($email) {
    mail($email, "Thank you for contacting Wavestek Power Solutions", $auto_reply, $reply_headers);
}

// Log submission to file (optional, useful before DB is set up)
$log_dir  = __DIR__ . '/logs';
if (!is_dir($log_dir)) @mkdir($log_dir, 0750, true);
$log_line = date('Y-m-d H:i:s') . " | $name | $email | $phone | $subject\n";
@file_put_contents($log_dir . '/enquiries.log', $log_line, FILE_APPEND | LOCK_EX);

// Track attempt
$_SESSION['form_attempts'][] = $now;

if ($sent) {
    echo json_encode(['success' => true,  'message' => 'Thank you! Your message has been sent. We will contact you within 24 hours.']);
} else {
    // Mail failed but we still log it — tell user to call
    echo json_encode(['success' => true, 'message' => 'Message received! Our mail system is being set up — for fastest response please call ' . PHONE_PRIMARY . ' or WhatsApp us.']);
}
