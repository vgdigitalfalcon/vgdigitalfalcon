<?php
/**
 * VG Digital Falcon - Executive Mailer Dispatcher
 * Sends branded HTML/Text lead notifications with header-injection guards.
 */

namespace VGDigitalFalcon\Core;

class Mailer
{
    private array $config;

    public function __construct()
    {
        $configFile = __DIR__ . '/../config/config.php';
        if (!file_exists($configFile)) {
            $configFile = __DIR__ . '/../config/config.sample.php';
        }

        if (file_exists($configFile)) {
            $fullConfig = include $configFile;
            $this->config = $fullConfig['mail'] ?? [];
        } else {
            $this->config = [];
        }
    }

    public function sendNotification(string $subject, array $fields, ?string $replyTo = null): bool
    {
        $fromEmail = $this->config['from_email'] ?? 'noreply@vgdigitalfalcon.com';
        $fromName  = $this->config['from_name']  ?? 'VG Digital Falcon Notification';
        $toEmails  = $this->config['to_emails']  ?? ['contact@vgdigitalfalcon.com', 'madan.n@vgdigitalfalcon.com'];
        $toHeader  = implode(', ', (array)$toEmails);

        // Sanitize headers to prevent email injection
        $cleanSubject = str_replace(["\r", "\n"], ' ', $subject);
        $cleanReplyTo = filter_var($replyTo ?? $fromEmail, FILTER_VALIDATE_EMAIL) ? $replyTo : $fromEmail;

        // Build HTML template
        $htmlBody = $this->renderHtmlTemplate($cleanSubject, $fields);

        // Build Plain-Text alternative
        $plainBody = "=== VG DIGITAL FALCON NOTIFICATION ===\n\n";
        $plainBody .= "Subject: {$cleanSubject}\n";
        $plainBody .= "Timestamp: " . date("Y-m-d H:i:s T") . "\n";
        $plainBody .= "IP Address: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n\n";
        $plainBody .= "--- SUBMISSION DETAILS ---\n";
        foreach ($fields as $label => $val) {
            if ($val !== null && $val !== '') {
                $plainBody .= ucfirst($label) . ": " . strip_tags($val) . "\n";
            }
        }
        $plainBody .= "\n======================================";

        $boundary = "==Multipart_Boundary_x" . md5(time()) . "x";

        $headers  = "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$cleanReplyTo}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
        $headers .= "X-Mailer: VG-DigitalFalcon-Core/2.0\r\n";

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $plainBody . "\r\n\r\n";

        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= $htmlBody . "\r\n\r\n";
        $body .= "--{$boundary}--";

        return @mail($toHeader, $cleanSubject, $body, $headers);
    }

    private function renderHtmlTemplate(string $title, array $fields): string
    {
        $tableRows = '';
        foreach ($fields as $label => $val) {
            if ($val !== null && $val !== '') {
                $safeLabel = htmlspecialchars(ucwords(str_replace('_', ' ', $label)));
                $safeVal = nl2br(htmlspecialchars((string)$val));
                $tableRows .= "<tr>
                    <td style=\"padding: 12px 16px; border-bottom: 1px solid #1f2937; color: #9ca3af; font-weight: 600; width: 35%;\">{$safeLabel}</td>
                    <td style=\"padding: 12px 16px; border-bottom: 1px solid #1f2937; color: #f9fafb;\">{$safeVal}</td>
                </tr>";
            }
        }

        $now = date('Y-m-d H:i:s T');
        $ip = htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'N/A');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>{$title}</title>
</head>
<body style="margin: 0; padding: 30px; background-color: #030712; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #e5e7eb;">
  <div style="max-width: 600px; margin: 0 auto; background-color: #0b1329; border: 1px solid #1e293b; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
    <div style="padding: 24px 30px; background: linear-gradient(135deg, rgba(6,182,212,0.15), rgba(139,92,246,0.15)); border-bottom: 1px solid #1e293b;">
      <h2 style="margin: 0; color: #38bdf8; font-size: 20px; font-weight: 700; letter-spacing: -0.5px;">VG Digital Falcon</h2>
      <p style="margin: 4px 0 0 0; color: #94a3b8; font-size: 13px;">{$title}</p>
    </div>
    <div style="padding: 24px 30px;">
      <table style="width: 100%; border-collapse: collapse; font-size: 14px; text-align: left;">
        {$tableRows}
      </table>
    </div>
    <div style="padding: 16px 30px; background-color: #030712; border-top: 1px solid #1e293b; font-size: 12px; color: #64748b; display: flex; justify-content: space-between;">
      <span>Received: {$now}</span>
      <span style="float: right;">IP: {$ip}</span>
    </div>
  </div>
</body>
</html>
HTML;
    }
}
