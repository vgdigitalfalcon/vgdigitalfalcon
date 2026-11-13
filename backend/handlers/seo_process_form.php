<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Honeypot check
    if (!empty($_POST['address'])) {
        header("Location: seo-service.html?status=error_spam");
        exit;
    }

    // Capture form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $website = trim($_POST['website']);
    $whatsapp = trim($_POST['whatsapp']);
    $service_subject = trim($_POST['service_subject']);

    // Server-side validation
    if (empty($name) || empty($email) || empty($website) || empty($whatsapp) || empty($service_subject)) {
        header("Location: seo-service.html?status=error");
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: seo-service.html?status=error_email");
        exit;
    }

    // Validate phone number (simple check for 10 digits)
    if (!preg_match('/^[0-9]{10}$/', $whatsapp)) {
        header("Location: seo-service.html?status=error_phone");
        exit;
    }

    // Recipients
    $to = "contact@vgdigitalfalcon.com, madan.n@vgdigitalfalcon.com";

    // Subject of the email
    $subject = $service_subject;

    // Email content
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Website URL: $website\n";
    $email_content .= "WhatsApp Number: $whatsapp\n\n";
    $email_content .= "This is a new SEO audit request from the website.\n";

    // Email headers
    $email_headers = "From: VG Digital Falcon Website <noreply@vgdigitalfalcon.com>\r\n";
    $email_headers .= "Reply-To: $email\r\n";
    $email_headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send the email
    if (mail($to, $subject, $email_content, $email_headers)) {
        // Redirect to success page (or back to the form with a success message)
        header("Location: seo-service.html?status=success");
        exit;
    } else {
        // Redirect to error page (or back to the form with an error message)
        header("Location: seo-service.html?status=error");
        exit;
    }
} else {
    // If the request is not a POST request, redirect back to the form
    header("Location: seo-service.html");
    exit;
}
?>