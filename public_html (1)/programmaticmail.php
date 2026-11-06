<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Required fields - matching HTML form field names
    $name       = isset($_POST['name']) ? trim($_POST['name']) : '';
    $business   = isset($_POST['business']) ? trim($_POST['business']) : '';  // Changed from 'channel' to 'business'
    $whatsapp   = isset($_POST['whatsapp']) ? trim($_POST['whatsapp']) : '';
    $city       = isset($_POST['city']) ? trim($_POST['city']) : '';
    
    // Optional hidden fields
    $page_service = isset($_POST['page_service']) ? trim($_POST['page_service']) : '';
    $page_city    = isset($_POST['page_city']) ? trim($_POST['page_city']) : '';

    // Validate empty fields
    if ($name == "" || $business == "" || $whatsapp == "" || $city == "") {
        echo "All fields are required!";
        exit;
    }

    // Email settings
    $to = "contact@vgdigitalfalcon.com, madan.n@vgdigitalfalcon.com";
    $subject = "New Business Plan Request - " . ($page_service ? $page_service : "Programmatic SEO") . " | VG Digital Falcon";

    $message = "
        <h2>New Business Lead - Programmatic SEO Page</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Business Name / Website URL:</strong> $business</p>
        <p><strong>WhatsApp Number:</strong> $whatsapp</p>
        <p><strong>City:</strong> $city</p>
        <hr>
        <p><strong>Page Service:</strong> " . ($page_service ? $page_service : "Not specified") . "</p>
        <p><strong>Page City:</strong> " . ($page_city ? $page_city : "Not specified") . "</p>
        <hr>
        <p>Generated from Programmatic SEO Landing Page</p>
    ";

    $headers = "From: VG Digital Falcon <contact@vgdigitalfalcon.com>\r\n";
    $headers .= "Reply-To: $name <$whatsapp>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    if (mail($to, $subject, $message, $headers)) {
        // redirect after success
        header("Location: thankyou.html");
        exit;
    } else {
        echo "Email sending failed. Please check server mail configuration.";
    }

} else {
    echo "Invalid Request!";
}
?>