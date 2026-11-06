<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$user = require_login();
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
$lines = ['DIGITAL FALCON', 'Growth Accelerator  |  Group of Viivaa Guru', 'Client report  |  ' . $name, strtoupper($service) . '  |  ' . $month, ''];
if ($service === 'seo') {
    $lines[] = 'RANKING';
    $stmt = db()->prepare('SELECT k.keyword, r.position, r.log_date FROM keywords k JOIN keyword_ranks r ON r.keyword_id = k.id WHERE k.client_id = ? AND r.show_client = 1 AND r.log_date LIKE ? ORDER BY r.log_date DESC');
    $stmt->execute([$cid, $month . '%']);
    foreach ($stmt->fetchAll() as $r) $lines[] = $r['log_date'] . '  ' . $r['keyword'] . '  #' . $r['position'] . '  ' . page_position((int)$r['position']);
    $lines[] = '';
    $lines[] = 'DA / PA';
    $stmt = db()->prepare('SELECT * FROM site_metrics WHERE client_id = ? AND log_date LIKE ? ORDER BY log_date DESC');
    $stmt->execute([$cid, $month . '%']);
    foreach ($stmt->fetchAll() as $row) {
        $show = json_decode($row['show_json'], true) ?: [];
        $bits = [$row['log_date']];
        foreach (SITE_FIELDS as $k => $label) if (!empty($show[$k])) $bits[] = $label . ' ' . $row[$k];
        $lines[] = implode('  |  ', $bits);
    }
    $lines[] = '';
    $lines[] = 'SEARCH CONSOLE';
    $stmt = db()->prepare('SELECT * FROM range_logs WHERE client_id = ? AND kind = "gsc" AND from_date LIKE ?');
    $stmt->execute([$cid, $month . '%']);
    foreach ($stmt->fetchAll() as $log) {
        $show = json_decode($log['show_json'], true) ?: [];
        $m = json_decode($log['metrics_json'], true) ?: [];
        $bits = [$log['from_date'] . ' to ' . $log['to_date'] . ' (' . day_count($log['from_date'], $log['to_date']) . ' days)'];
        foreach (GSC_FIELDS as $k => $label) if (!empty($show[$k])) $bits[] = $label . ' ' . ($m[$k] ?? '');
        $lines[] = implode('  |  ', $bits);
    }
    $lines[] = '';
    $lines[] = 'Only metrics marked Show to client are included.';
}
$lines[] = '';
$lines[] = 'Digital Falcon  ·  client-safe file';
function pdf_escape(string $s): string { return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], preg_replace('/[^\x20-\x7E]/', '', $s)); }
$y = 790;
$stream = "0.1 0.1 0.1 rg 0 800 595 42 re f 1 0.27 0.28 rg 0 800 8 42 re f BT /F1 16 Tf 1 1 1 rg 24 816 Td (DIGITAL FALCON) Tj ET\n";
$stream .= "BT /F1 11 Tf 0 0 0 rg 14 TL 40 770 Td\n";
foreach ($lines as $line) $stream .= '(' . pdf_escape($line) . ") Tj T* ";
$stream .= 'ET';
$pdf = "%PDF-1.4\n";
$offsets = [];
$add = function (string $obj) use (&$pdf, &$offsets) { $offsets[] = strlen($pdf); $pdf .= $obj . "\n"; };
$add("1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj");
$add("2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj");
$add("3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj");
$add("4 0 obj << /Length " . strlen($stream) . " >> stream\n" . $stream . "\nendstream endobj");
$add("5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj");
$xref = strlen($pdf);
$pdf .= "xref\n0 6\n0000000000 65535 f \n";
foreach ($offsets as $off) $pdf .= sprintf("%010d 00000 n \n", $off);
$pdf .= "trailer << /Size 6 /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="digital-falcon-' . $service . '-' . $month . '.pdf"');
echo $pdf;
