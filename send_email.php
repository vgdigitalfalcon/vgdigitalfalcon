<?php
// Start a session to store the captcha answer
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check for required fields and validate
    if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['phone']) || !isset($_POST['subject']) || !isset($_POST['message']) || !isset($_POST['captcha']) || !isset($_POST['consent'])) {
        header("Location: contact.html?status=error&message=missing_fields");
        exit;
    }
    
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $captcha_input = trim($_POST['captcha']);
    
    // Server-side validation
    if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($message) || empty($captcha_input)) {
        header("Location: contact.html?status=error&message=missing_fields");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: contact.html?status=error&message=invalid_email");
        exit;
    }
    
    if (!preg_match("/^\d{10}$/", $phone)) {
        header("Location: contact.html?status=error&message=invalid_phone");
        exit;
    }
    
    // Captcha validation
    // Check if the captcha session variable is set and matches the user input
    if (!isset($_SESSION['captcha_answer']) || $captcha_input != $_SESSION['captcha_answer']) {
        // Unset the session variable to prevent brute-force attacks on the same captcha
        unset($_SESSION['captcha_answer']);
        header("Location: contact.html?status=error&message=captcha");
        exit;
    }

    // Prepare the email
    $to_emails = "contact@vgdigitalfalcon.com, madan.n@vgdigitalfalcon.com";
    $email_subject = "New Enquiry from Contact Page - " . $subject;
    
    $email_body = "You have received a new message from your website contact form.\n\n";
    $email_body .= "Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Phone: $phone\n";
    $email_body .= "Subject: $subject\n\n";
    $email_body .= "Message:\n$message";

    $headers = "From: webmaster@vgdigitalfalcon.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send the email
    if (mail($to_emails, $email_subject, $email_body, $headers)) {
        // Clear the captcha session variable after successful submission
        unset($_SESSION['captcha_answer']);
        header("Location: contact.html?status=success");
        exit;
    } else {
        header("Location: contact.html?status=error");
        exit;
    }
} else {
    // Not a POST request
    header("Location: contact.html");
    exit;
}
?>