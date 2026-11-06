<?php
// ==================== mail.php ====================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name         = strip_tags(trim($_POST['name'] ?? ''));
$email        = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone        = strip_tags(trim($_POST['phone'] ?? ''));
$city         = strip_tags(trim($_POST['city'] ?? ''));
$organisation = strip_tags(trim($_POST['organisation'] ?? ''));
$services     = strip_tags(trim($_POST['services'] ?? 'None selected'));
$source       = strip_tags(trim($_POST['source'] ?? 'Website Contact Form'));

// Validation
if (empty($name) || empty($email) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// ================ 1. SEND EMAIL (Multiple Recipients) ================
$to = "contact@vgdigitalfalcon.com, 917806871524@beepmate.io";   // ← Added second email

$subject = "New Enquiry from $name - VG Digital Falcon";

$message = "🌟 New Contact Form Submission\n\n";
$message .= "Name: $name\n";
$message .= "Email: $email\n";
$message .= "Phone: $phone\n";
$message .= "City: $city\n";
$message .= "Organisation: $organisation\n";
$message .= "Services Interested: $services\n";
$message .= "Source: $source\n";
$message .= "Submitted At: " . date('d M Y, h:i A') . "\n";

$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$emailSent = mail($to, $subject, $message, $headers);

// ================ 2. BEEPMATE WHATSAPP NOTIFICATION ================

$beepmateApiKey = "lay7ZxGq0gZT";
$whatsappNumberID = "917806871524";

$adminWhatsAppMessage = "🔔 *New Enquiry Received*\n\n";
$adminWhatsAppMessage .= "👤 *Name:* $name\n";
$adminWhatsAppMessage .= "📧 *Email:* $email\n";
$adminWhatsAppMessage .= "📱 *Phone:* $phone\n";
$adminWhatsAppMessage .= "📍 *City:* $city\n";
$adminWhatsAppMessage .= "🏥 *Organisation:* $organisation\n";
$adminWhatsAppMessage .= "🔖 *Services:* $services\n";
$adminWhatsAppMessage .= "📅 *Time:* " . date('d M Y, h:i A') . "\n";

$beepmatePayload = [
    "api_key" => $beepmateApiKey,
    "number_id" => $whatsappNumberID,
    "message" => $adminWhatsAppMessage,
    "to" => "917806871524"
];

$ch = curl_init("https://api.beepmate.com/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($beepmatePayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Accept: application/json"
]);

$beepmateResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// ================ RESPONSE ================
if ($emailSent) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Email sending failed'
    ]);
}

// Log Beepmate response (for debugging)
file_put_contents('beepmate_log.txt', date('Y-m-d H:i:s') . " | HTTP $httpCode | " . $beepmateResponse . "\n", FILE_APPEND);
?>