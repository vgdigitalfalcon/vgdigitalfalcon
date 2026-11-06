<?php
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Invalid request.'));
    exit;
}

function clean_field($value) {
    $value = trim(strip_tags((string) $value));
    $value = str_replace(array("\r", "\n", "%0a", "%0d"), ' ', $value);
    return $value;
}

$name     = clean_field(isset($_POST['name']) ? $_POST['name'] : '');
$phone    = clean_field(isset($_POST['phone']) ? $_POST['phone'] : '');
$location = clean_field(isset($_POST['location']) ? $_POST['location'] : '');
$service  = clean_field(isset($_POST['service']) ? $_POST['service'] : '');

if ($name === '' || $phone === '' || $location === '' || $service === '') {
    http_response_code(400);
    echo json_encode(array('success' => false, 'message' => 'Please fill in all the fields.'));
    exit;
}

$to = 'auracladding@gmail.com';
$subject = 'New Quote Request - Aura Cladding';
$message = "A new quote request was submitted from the Aura Cladding website.\n\n";
$message .= "Name: " . $name . "\n";
$message .= "Phone Number: " . $phone . "\n";
$message .= "Location: " . $location . "\n";
$message .= "Service Looking For: " . $service . "\n";
$message .= "Submitted On: " . date('d M Y, h:i A') . "\n";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: Aura Cladding <info@auracladding.com>\r\n";
$headers .= "Reply-To: " . $name . " <info@auracladding.com>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent = @mail($to, $subject, $message, $headers);

if ($sent) {
    echo json_encode(array('success' => true, 'message' => 'Enquiry sent successfully.'));
    exit;
}

http_response_code(500);
echo json_encode(array('success' => false, 'message' => 'Unable to send mail right now.'));