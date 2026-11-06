<?php
// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and validate inputs
    $name    = htmlspecialchars(strip_tags(trim($_POST["name"])));
    $email   = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $phone   = htmlspecialchars(strip_tags(trim($_POST["phone"])));
    $subject = htmlspecialchars(strip_tags(trim($_POST["subject"])));
    $message = htmlspecialchars(strip_tags(trim($_POST["message"])));

    // Basic server-side validation
    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo "Invalid input.";
        exit;
    }

    // --- EMAIL SETTINGS ---
    // The email address where you want to receive the messages
$to = 'auracladding@gmail.com';
    
    // Email Subject
    $email_subject = "New Contact Form Submission: " . $subject;

    // --- EMAIL HEADERS ---
    // To send HTML mail, the Content-type header must be set
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    
    // Additional headers
    // Note: It is best practice to use an email address from your own domain here (e.g., info@auracladding.com)
    // so it doesn't get flagged as spam. We set the user's email as the Reply-To.
    $headers .= "From: info@auracladding.com\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";

    // --- HTML EMAIL BODY ---
    $email_body = "
        <h2 style='color: #051329;'>New Contact Request</h2>
        <table style='width: 100%; border-collapse: collapse; max-width: 600px; font-family: sans-serif;'>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa; width: 120px;'><strong>Name</strong></td>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$name}</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Email</strong></td>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$email}</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Phone</strong></td>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$phone}</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Subject</strong></td>
                <td style='padding: 10px; border: 1px solid #ddd;'>{$subject}</td>
            </tr>
            <tr>
                <td style='padding: 10px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Message</strong></td>
                <td style='padding: 10px; border: 1px solid #ddd;'>" . nl2br($message) . "</td>
            </tr>
        </table>
    ";

    // --- SEND EMAIL ---
    if (mail($to, $email_subject, $email_body, $headers)) {
        // Output exactly this string so the JavaScript knows it succeeded
        echo 'success';
    } else {
        http_response_code(500);
        echo "Failed to send email. Check your server's mail configuration.";
    }

} else {
    // Reject direct browser access to this file
    http_response_code(403);
    echo "There was a problem with your submission, please try again.";
}
?>