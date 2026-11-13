<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs
    $name = strip_tags(trim($_POST["name"]));
    $website = strip_tags(trim($_POST["website"]));
    $whatsapp = strip_tags(trim($_POST["whatsapp"]));
    $captcha_input = trim($_POST["captcha"]);

    // Server-side validation
    if (empty($name) || empty($website) || empty($whatsapp) || empty($captcha_input)) {
        echo json_encode(['success' => false, 'message' => 'missing_fields']);
        exit;
    }

    if (!preg_match("/^\d{10}$/", $whatsapp)) {
        echo json_encode(['success' => false, 'message' => 'invalid_phone']);
        exit;
    }
    
    // Captcha validation
    // The client-side script in this case will handle captcha generation and checking.
    // If you were using a server-side session for captcha, you would need to implement
    // that logic here. Given your client-side approach, this is a placeholder.
    // You'd need to verify the captcha server-side to be truly secure.
    // For now, the client-side JS handles the logic, and we'll trust that for this response.
    // To implement a truly secure captcha, you'd need to use a session to store the answer.
    // However, the provided HTML and JS are set up for a client-side check.

    // Prepare the email
    $to_emails = "contact@vgdigitalfalcon.com, madan.n@vgdigitalfalcon.com";
    $email_subject = "New Ecommerce SEO Audit Request from Website";
    
    $email_body = "You have received a new request for an Ecommerce SEO Audit.\n\n";
    $email_body .= "Name: $name\n";
    $email_body .= "Website URL: $website\n";
    $email_body .= "WhatsApp Number: $whatsapp\n";

    $headers = "From: webmaster@vgdigitalfalcon.com\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send the email
    if (mail($to_emails, $email_subject, $email_body, $headers)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'mail_error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'invalid_request']);
}
?>