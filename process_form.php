<?php
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$name = htmlspecialchars(trim($_POST['name'] ?? ''));
$channel = htmlspecialchars(trim($_POST['channel'] ?? ''));
$whatsapp = htmlspecialchars(trim($_POST['whatsapp'] ?? ''));

if (empty($name) || empty($channel) || empty($whatsapp)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit;
}

// 1. Try database connection if available
$db_saved = false;
$db_files = [__DIR__ . '/db.php', __DIR__ . '/backend/db.php', __DIR__ . '/public_html (1)/db.php'];
foreach ($db_files as $db_file) {
    if (file_exists($db_file)) {
        try {
            @include_once $db_file;
            if (isset($pdo) && $pdo instanceof PDO) {
                $pdo->exec("CREATE TABLE IF NOT EXISTS youtube_seo_audit (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    channel VARCHAR(255) NOT NULL,
                    whatsapp VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                $stmt = $pdo->prepare("INSERT INTO youtube_seo_audit (name, channel, whatsapp, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute([$name, $channel, $whatsapp]);
                $db_saved = true;
                break;
            }
        } catch (Exception $e) {
            error_log("Database lead error: " . $e->getMessage());
        }
    }
}

// 2. Email notification
$to = "contact@vgdigitalfalcon.com, madan.n@vgdigitalfalcon.com";
$subject = "New YouTube SEO Audit Request - " . $name;
$body = "New YouTube SEO Audit Form Submission:\n\n"
      . "Name: " . $name . "\n"
      . "Channel Name / URL: " . $channel . "\n"
      . "WhatsApp Number: " . $whatsapp . "\n"
      . "Submitted At: " . date("Y-m-d H:i:s") . "\n";
$headers = "From: noreply@vgdigitalfalcon.com\r\n"
         . "Reply-To: contact@vgdigitalfalcon.com\r\n"
         . "X-Mailer: PHP/" . phpversion();

@mail($to, $subject, $body, $headers);

echo json_encode([
    'success' => true,
    'message' => 'Thank you! Your free YouTube SEO audit request has been submitted successfully. Our team will review your channel and contact you via WhatsApp shortly.'
]);
exit;
