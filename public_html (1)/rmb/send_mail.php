<?php
// send_mail.php
// Premium Email Template for RMB Chennai Central
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data and sanitize
$fullname = isset($_POST['fullname']) ? htmlspecialchars(strip_tags(trim($_POST['fullname']))) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
$phone = isset($_POST['phone']) ? htmlspecialchars(strip_tags(trim($_POST['phone']))) : '';
$subject = isset($_POST['subject']) ? htmlspecialchars(strip_tags(trim($_POST['subject']))) : '';
$message_text = isset($_POST['message']) ? htmlspecialchars(strip_tags(trim($_POST['message']))) : '';
$recipient = isset($_POST['recipient']) ? filter_var(trim($_POST['recipient']), FILTER_SANITIZE_EMAIL) : 'madannirmal20@gmail.com';

// Validate
$errors = [];
if (empty($fullname)) $errors[] = 'Full name is required';
if (empty($email)) $errors[] = 'Email is required';
if (empty($phone)) $errors[] = 'Phone is required';
if (empty($subject)) $errors[] = 'Subject is required';
if (empty($message_text)) $errors[] = 'Message is required';
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) $recipient = 'madannirmal20@gmail.com';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

$to = $recipient;
$email_subject = "RMB Chennai Central - New Inquiry: $subject";
$submission_date = date('F j, Y \a\t g:i A');
$submission_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

// ==================== PREMIUM EMAIL TEMPLATE ====================
$email_body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RMB Chennai Central - New Contact</title>
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap");
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: "Inter", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0f4f8 0%, #e8eef5 100%);
            padding: 40px 20px;
            min-height: 100vh;
        }
        
        .email-container {
            max-width: 620px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(10, 25, 47, 0.15), 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        /* Header Section */
        .email-header {
            background: linear-gradient(135deg, #0A192F 0%, #1A3D76 40%, #0A192F 100%);
            padding: 40px 35px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .email-header::before {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 150px;
            height: 150px;
            background: rgba(245, 164, 25, 0.08);
            border-radius: 50%;
        }
        
        .email-header::after {
            content: "";
            position: absolute;
            bottom: -40px;
            left: -40px;
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
        }
        
        .header-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }
        
        .header-logos img {
            height: 50px;
            width: auto;
            filter: brightness(1.1);
            background: rgba(255, 255, 255, 0.95);
            padding: 6px 10px;
            border-radius: 8px;
        }
        
        .header-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            color: #F5A419;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }
        
        .email-header h2 {
            margin: 0;
            font-size: 28px;
            color: #ffffff;
            font-weight: 800;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
            line-height: 1.3;
        }
        
        .email-header h2 .gold {
            color: #F5A419;
            position: relative;
        }
        
        .email-header .subtitle {
            color: rgba(255, 255, 255, 0.75);
            font-size: 14px;
            margin-top: 10px;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }
        
        /* Decorative line */
        .header-line {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #F5A419, #e09600);
            margin: 20px auto 0;
            border-radius: 2px;
            position: relative;
            z-index: 1;
        }
        
        /* Content Section */
        .email-content {
            padding: 40px 35px;
            background: #ffffff;
        }
        
        .content-intro {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .content-intro .icon-circle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #1A3D76 0%, #0A192F 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            box-shadow: 0 10px 25px rgba(26, 61, 118, 0.25);
        }
        
        .content-intro h3 {
            color: #1A3D76;
            font-size: 18px;
            font-weight: 700;
        }
        
        /* Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-card {
            background: #f8fafc;
            border-radius: 14px;
            padding: 18px 16px;
            border: 1px solid #e8ecf1;
            transition: all 0.3s ease;
        }
        
        .info-card .card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: #ffffff;
            border-radius: 10px;
            margin-bottom: 10px;
            font-size: 16px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
        }
        
        .info-card .card-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: 1.2px;
            margin-bottom: 5px;
        }
        
        .info-card .card-value {
            font-size: 14px;
            color: #1a202c;
            font-weight: 600;
            word-break: break-word;
        }
        
        /* Message Card */
        .message-card {
            background: #ffffff;
            border-radius: 14px;
            padding: 22px 20px;
            border: 1px solid #e8ecf1;
            border-left: 4px solid #F5A419;
            margin-bottom: 20px;
        }
        
        .message-card .card-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 700;
            letter-spacing: 1.2px;
            margin-bottom: 10px;
        }
        
        .message-card .card-value {
            font-size: 14px;
            color: #334155;
            line-height: 1.7;
            white-space: pre-wrap;
            font-weight: 400;
        }
        
        /* Action Button */
        .action-section {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e8ecf1;
        }
        
        .reply-btn {
            display: inline-block;
            background: linear-gradient(135deg, #F5A419 0%, #e09600 100%);
            color: #0A192F;
            padding: 14px 32px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(245, 164, 25, 0.3);
        }
        
        .reply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(245, 164, 25, 0.4);
        }
        
        /* Footer */
        .email-footer {
            background: #0A192F;
            padding: 25px 35px;
            text-align: center;
        }
        
        .email-footer .footer-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .email-footer .footer-logos img {
            height: 30px;
            width: auto;
            opacity: 0.8;
            background: rgba(255,255,255,0.9);
            padding: 3px 6px;
            border-radius: 4px;
        }
        
        .email-footer p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 11px;
            margin: 4px 0;
        }
        
        .email-footer .footer-meta {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        
        .email-footer .footer-meta span {
            color: rgba(255, 255, 255, 0.4);
            font-size: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .info-grid { grid-template-columns: 1fr; }
            .email-header { padding: 30px 20px; }
            .email-content { padding: 30px 20px; }
            .email-header h2 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
           
            <div class="header-badge">✦ New Inquiry Received ✦</div>
            <h2>RMB <span class="gold">Chennai Central</span></h2>
            <p class="subtitle">A new contact form submission has been received</p>
            <div class="header-line"></div>
        </div>
        
        <!-- Content -->
        <div class="email-content">
          
            <!-- Info Cards Grid -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="card-icon">👤</div>
                    <div class="card-label">Full Name</div>
                    <div class="card-value">' . $fullname . '</div>
                </div><br>
                <div class="info-card">
                    <div class="card-icon">📧</div>
                    <div class="card-label">Email Address</div>
                    <div class="card-value">' . $email . '</div>
                </div><br>
                <div class="info-card">
                    <div class="card-icon">📱</div>
                    <div class="card-label">Phone Number</div>
                    <div class="card-value">' . $phone . '</div>
                </div><br>
                <div class="info-card">
                    <div class="card-icon">📋</div>
                    <div class="card-label">Subject</div>
                    <div class="card-value">' . $subject . '</div>
                </div><br>
            </div>
            
            <!-- Message Card -->
            <div class="message-card">
                <div class="card-label">💬 Message</div>
                <div class="card-value">' . nl2br($message_text) . '</div>
            </div>
            
            <!-- Action -->
            <div class="action-section">
                <a href="mailto:' . $email . '?subject=Re:%20' . urlencode($subject) . '%20-%20RMB%20Chennai%20Central" class="reply-btn">
                    ✉️ Reply to ' . $fullname . '
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
           
            <p>&copy; ' . date('Y') . ' RMB Chennai Central. All Rights Reserved.</p>
            <div class="footer-meta">
                <span>📅 ' . $submission_date . '</span>
                <span>🌐 IP: ' . $submission_ip . '</span>
            </div>
        </div>
    </div>
</body>
</html>';

// ==================== CONFIRMATION EMAIL TO SENDER ====================
$confirmation_subject = "✅ Thank You for Contacting RMB Chennai Central";
$confirmation_body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");
        body { font-family: "Inter", "Segoe UI", sans-serif; background: linear-gradient(135deg, #f0f4f8, #e8eef5); padding: 40px 20px; margin: 0; }
        .confirm-container { max-width: 520px; margin: 0 auto; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 45px rgba(10,25,47,0.12); }
        .confirm-header { background: linear-gradient(135deg, #1A3D76, #0A192F); padding: 35px 30px; text-align: center; position: relative; overflow: hidden; }
        .confirm-header .check-circle { width: 70px; height: 70px; background: rgba(245,164,25,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 32px; border: 3px solid #F5A419; }
        .confirm-header h2 { color: #fff; font-size: 24px; font-weight: 800; margin: 0; }
        .confirm-header p { color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 8px; }
        .confirm-body { padding: 35px 30px; text-align: center; }
        .confirm-body .name { color: #1A3D76; font-size: 20px; font-weight: 700; }
        .confirm-body .message { color: #64748b; font-size: 14px; line-height: 1.7; margin: 15px 0 25px; }
        .confirm-body .info-box { background: #f8fafc; border-radius: 14px; padding: 20px; text-align: left; margin-bottom: 20px; border: 1px solid #e8ecf1; }
        .confirm-body .info-box strong { color: #1A3D76; }
        .confirm-footer { background: #f8fafc; padding: 20px 30px; text-align: center; border-top: 1px solid #e8ecf1; }
        .confirm-footer .logos { display: flex; justify-content: center; gap: 10px; margin-bottom: 10px; }
        .confirm-footer img { height: 28px; opacity: 0.8; background: #fff; padding: 3px 6px; border-radius: 4px; }
        .confirm-footer p { color: #94a3b8; font-size: 11px; margin: 0; }
    </style>
</head>
<body>
    <div class="confirm-container">
        <div class="confirm-header">
            <div class="check-circle">✅</div>
            <h2>Message Received!</h2>
            <p>We will get back to you shortly</p>
        </div>
        <div class="confirm-body">
            <p class="name">Dear ' . $fullname . ',</p>
            <p class="message">Thank you for reaching out to <strong>RMB Chennai Central</strong>. We have received your inquiry and our team will review it shortly. You can expect a response within <strong style="color:#F5A419;">24-48 business hours</strong>.</p>
            <div class="info-box">
                <p style="margin:0 0 8px;"><strong>📋 Subject:</strong> ' . $subject . '</p>
                <p style="margin:0;"><strong>📧 Your Email:</strong> ' . $email . '</p>
            </div>
            <p style="font-size:13px;color:#94a3b8;">If you have any urgent queries, feel free to call us at <strong style="color:#1A3D76;">+91 95662 44450</strong></p>
        </div>
        <div class="confirm-footer">
            <div class="logos">
                <img src="https://via.placeholder.com/80x28/ffffff/012169?text=ROTARY" alt="Rotary" />
                <img src="https://via.placeholder.com/80x28/ffffff/1A3D76?text=RMB" alt="RMB" />
            </div>
            <p>&copy; ' . date('Y') . ' RMB Chennai Central. All Rights Reserved.</p>
        </div>
    </div>
</body>
</html>';

// ==================== SEND EMAILS ====================
$headers = "From: RMB Chennai Central <noreply@rmbchennai.org>\r\n";
$headers .= "Reply-To: $fullname <$email>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();
$headers .= "X-Priority: 1\r\n";
$headers .= "Importance: High\r\n";

$confirmation_headers = "From: RMB Chennai Central <noreply@rmbchennai.org>\r\n";
$confirmation_headers .= "MIME-Version: 1.0\r\n";
$confirmation_headers .= "Content-Type: text/html; charset=UTF-8\r\n";

$mail_sent = mail($to, $email_subject, $email_body, $headers);
@mail($email, $confirmation_subject, $confirmation_body, $confirmation_headers);

if ($mail_sent) {
    echo json_encode([
        'success' => true, 
        'message' => 'Message sent successfully! We will get back to you soon.'
    ]);
} else {
    error_log("RMB Contact Form - Failed to send email from: $email");
    echo json_encode([
        'success' => false, 
        'message' => 'Email could not be sent. Please try again or email us at chennaicentralrmb@gmail.com'
    ]);
}
?>