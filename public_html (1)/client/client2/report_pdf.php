<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$user = require_login();
$cid = 0;
if ($user['role'] === 'client') {
    $mine = visible_clients($user);
    $cid = (int)($mine[0]['id'] ?? 0);
    $name = $mine[0]['name'] ?? 'Client';
} else {
    $cid = (int)($_GET['client_id'] ?? 0);
    if (!$cid || !can_client($user, $cid)) exit('Client not available.');
    $name = client_row($cid)['name'];
}
$service = $_GET['service'] ?? 'seo';
$month = $_GET['month'] ?? date('Y-m');
$lines = ["Digital Falcon", "Growth Accelerator · Group of Viivaa Guru", $name . " · " . strtoupper($service) . " · " . $month, ""];
if ($service === 'seo') {
    $stmt = db()->prepare('SELECT k.keyword, r.position, r.log_date FROM keywords k JOIN keyword_ranks r ON r.keyword_id = k.id WHERE k.client_id = ? AND r.show_client = 1 AND r.log_date LIKE ? ORDER BY r.position LIMIT 5');
    $stmt->execute([$cid, $month . '%']);
    foreach ($stmt->fetchAll() as $r) $lines[] = $r['keyword'] . "  position " . $r['position'] . "  " . page_position((int)$r['position']) . "  " . $r['log_date'];
}
if ($service === 'smo') {
    $stmt = db()->prepare('SELECT * FROM social_logs WHERE client_id = ? AND from_date LIKE ?');
    $stmt->execute([$cid, $month . '%']);
    foreach ($stmt->fetchAll() as $log) {
        $show = json_decode($log['show_json'], true) ?: [];
        $m = json_decode($log['metrics_json'], true) ?: [];
        $lines[] = strtoupper($log['channel']) . " " . $log['from_date'] . " to " . $log['to_date'];
        foreach ($m as $k => $v) if (!empty($show[$k])) $lines[] = "  " . $k . ": " . $v;
    }
}
if ($service === 'ads') {
    $stmt = db()->prepare('SELECT * FROM campaigns WHERE client_id = ? AND from_date LIKE ?');
    $stmt->execute([$cid, $month . '%']);
    foreach ($stmt->fetchAll() as $r) {
        $show = json_decode($r['show_json'], true) ?: [];
        $lines[] = $r['name'];
        foreach (AD_FIELDS as $k => $label) if (!empty($show[$k])) $lines[] = "  " . $label . ": " . $r[$k];
    }
}
$lines[] = "";
$lines[] = "Client-safe report. Hidden metrics are not included.";
$text = implode("\n", $lines);

function pdf_escape(string $s): string {
    return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
}
$objects = [];
$content = "BT /F1 11 Tf 14 TL 50 800 Td\n";
$yUsed = 0;
foreach (explode("\n", $text) as $line) {
    $content .= "(" . pdf_escape($line) . ") Tj T* ";
}
$content .= "ET";
$stream = $content;
$pdf = "%PDF-1.4\n";
$offsets = [];
$add = function (string $obj) use (&$pdf, &$offsets) {
    $offsets[] = strlen($pdf);
    $pdf .= $obj . "\n";
};
$add("1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj");
$add("2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj");
$add("3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj");
$add("4 0 obj << /Length " . strlen($stream) . " >> stream\n" . $stream . "\nendstream endobj");
$add("5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj");
$xref = strlen($pdf);
$pdf .= "xref\n0 6\n0000000000 65535 f \n";
foreach ($offsets as $off) $pdf .= sprintf("%010d 00000 n \n", $off);
$pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="digital-falcon-' . $service . '-' . $month . '.pdf"');
echo $pdf;
