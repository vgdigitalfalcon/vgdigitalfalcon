<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login('client');
$mine = visible_clients($user);
$client = $mine[0] ?? null;
if (!$client) exit('No client record.');
$cid = (int)$client['id'];
$service = $_GET['service'] ?? 'seo';
$month = $_GET['month'] ?? date('Y-m');
layout_start(strtoupper($service) . ' report', $user);
echo '<div class="card-box"><strong>' . h($client['name']) . '</strong> · ' . h($month);
echo ' <a class="btn btn-red btn-sm" href="report_pdf.php?service=' . h($service) . '&month=' . h($month) . '">Download PDF</a></div>';
echo '<div class="mb-3">';
for ($i = 0; $i < 6; $i++) {
    $m = date('Y-m', strtotime("-$i month"));
    echo '<a class="btn btn-sm ' . ($m === $month ? 'btn-blue' : 'btn-outline-dark') . ' me-1" href="client_report.php?service=' . h($service) . '&month=' . $m . '">' . h($m) . '</a>';
}
echo '</div>';

if ($service === 'seo') {
    $ranks = db()->prepare('SELECT k.keyword, r.position, r.log_date FROM keywords k JOIN keyword_ranks r ON r.keyword_id = k.id WHERE k.client_id = ? AND r.show_client = 1 AND r.log_date LIKE ? ORDER BY r.position ASC LIMIT 5');
    $ranks->execute([$cid, $month . '%']);
    echo '<div class="card-box"><h3>Ranking</h3><table class="table"><thead><tr><th>Keyword</th><th>Position</th><th>Page position</th><th>Date</th></tr></thead><tbody>';
    foreach ($ranks->fetchAll() as $r) echo '<tr><td>' . h($r['keyword']) . '</td><td>' . (int)$r['position'] . '</td><td>' . h(page_position((int)$r['position'])) . '</td><td>' . h($r['log_date']) . '</td></tr>';
    echo '</tbody></table></div>';
    $off = db()->prepare('SELECT * FROM offpage_rows WHERE client_id = ? AND month = ? AND show_client = 1');
    $off->execute([$cid, $month]);
    echo '<div class="card-box"><h3>Off Page</h3><table class="table"><thead><tr><th>Type</th><th>Count</th></tr></thead><tbody>';
    foreach ($off->fetchAll() as $r) echo '<tr><td>' . h($r['link_type']) . '</td><td>' . (int)$r['counts'] . '</td></tr>';
    echo '</tbody></table></div>';
    $issues = db()->prepare('SELECT * FROM onpage_issues WHERE client_id = ? AND show_client = 1 AND created_at LIKE ?');
    $issues->execute([$cid, $month . '%']);
    echo '<div class="card-box"><h3>On Page and Technical</h3><table class="table"><thead><tr><th>Issue</th><th>Fixation</th><th>Status</th></tr></thead><tbody>';
    foreach ($issues->fetchAll() as $r) echo '<tr><td>' . h($r['issue']) . '</td><td>' . h($r['fixation']) . '</td><td>' . h($r['status']) . '</td></tr>';
    echo '</tbody></table></div>';
}
if ($service === 'smo') {
    $logs = db()->prepare('SELECT * FROM social_logs WHERE client_id = ? AND from_date LIKE ?');
    $logs->execute([$cid, $month . '%']);
    foreach ($logs->fetchAll() as $log) {
        $show = json_decode($log['show_json'], true) ?: [];
        $m = json_decode($log['metrics_json'], true) ?: [];
        echo '<div class="card-box"><h3>' . h(ucfirst($log['channel'])) . ' · ' . h($log['from_date'] . ' to ' . $log['to_date']) . ' · ' . day_count($log['from_date'], $log['to_date']) . ' days</h3><ul>';
        foreach ($m as $k => $v) if (!empty($show[$k])) echo '<li>' . h($k) . ': ' . h($v) . '</li>';
        echo '</ul></div>';
    }
}
if ($service === 'ads') {
    $rows = db()->prepare('SELECT * FROM campaigns WHERE client_id = ? AND from_date LIKE ?');
    $rows->execute([$cid, $month . '%']);
    foreach ($rows->fetchAll() as $r) {
        $show = json_decode($r['show_json'], true) ?: [];
        echo '<div class="card-box"><h3>' . h($r['name']) . '</h3><ul>';
        foreach (AD_FIELDS as $k => $label) if (!empty($show[$k])) echo '<li>' . h($label) . ': ' . h($r[$k]) . '</li>';
        echo '</ul></div>';
    }
}
layout_end();
