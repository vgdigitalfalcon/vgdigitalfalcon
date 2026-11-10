<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $companyName = trim($_POST['companyName'] ?? '');
    $mobile      = trim($_POST['mobile'] ?? '');
    $website     = trim($_POST['website'] ?? '');
    $captcha     = trim($_POST['captcha'] ?? '');

    // validate captcha
    if (!isset($_SESSION['captcha_answer']) || (int)$captcha !== (int)$_SESSION['captcha_answer']) {
        echo json_encode(["success" => false, "message" => "captcha"]);
        exit;
    }

    // validate phone
    if (!preg_match('/^\d{10}$/', $mobile)) {
        echo json_encode(["success" => false, "message" => "invalid_phone"]);
        exit;
    }

    // ========= SEND EMAIL =========
    $to      = "contact@vgdigitalfalcon.com, madan.n@vgdigitalfalcon.com"; 
    $subject = "New Performance Marketing Enquiry - VG Digital Falcon";
    $message = "
    <html>
    <head>
      <title>New Performance Marketing Enquiry</title>
    </head>
    <body>
      <h2>New Enquiry Details</h2>
      <p><strong>Company Name:</strong> {$companyName}</p>
      <p><strong>WhatsApp No:</strong> {$mobile}</p>
      <p><strong>Website URL:</strong> {$website}</p>
    </body>
    </html>
    ";

    // headers
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: VG Digital Falcon <no-reply@vgdigitalfalcon.com>" . "\r\n";

    // send mail
    @mail($to, $subject, $message, $headers);

    // ========= DB SAVE (optional) =========
    // Example (uncomment + configure):
    // $db = new mysqli("localhost", "user", "pass", "dbname");
    // if(!$db->connect_error){
    //     $stmt = $db->prepare("INSERT INTO performance_enquiries (company, mobile, website) VALUES (?,?,?)");
    //     $stmt->bind_param("sss", $companyName, $mobile, $website);
    //     $stmt->execute();
    // }

    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "message" => "invalid_request"]);
