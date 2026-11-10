<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $mobile = htmlspecialchars($_POST['mobile']);
    $linkedin = htmlspecialchars($_POST['linkedin']);

    $to = "contact@vgdigitalfalcon.com, madan.n@vgdigitalfalcon.com";
    $subject = "New Personal Branding Enquiry";
    $message = "
    <h2>Personal Branding Enquiry</h2>
    <p><strong>Name:</strong> $name</p>
    <p><strong>Whatsapp:</strong> $mobile</p>
    <p><strong>LinkedIn:</strong> $linkedin</p>
    ";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@vgdigitalfalcon.com";

    if (mail($to, $subject, $message, $headers)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
