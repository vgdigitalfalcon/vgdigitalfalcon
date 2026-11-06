<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login();
sync_notifications($user);
layout_start('Overview', $user);
flash();
$clients = visible_clients($user);
$month = date('Y-m');
$prev = date('Y-m', strtotime('first day of last month'));
echo '<div class="stat-grid">';
echo '<div class="card-box stat"><div class="text-muted">Clients in view</div><h2>' . count($clients) . '</h2></div>';
echo '<div class="card-box stat"><div class="text-muted">This month</div><h2>' . h(date('F Y')) . '</h2></div>';
echo '<div class="card-box stat"><div class="text-muted">History month</div><h2>' . h(date('F Y', strtotime('first day of last month'))) . '</h2></div>';
echo '</div>';
if ($user['role'] === 'admin') {
    echo '<div class="card-box"><strong>Portals</strong><p class="mb-0"><a href="admin/index.php">Admin login</a> · <a href="employee/index.php">Employee login</a> · <a href="client/index.php">Client login</a></p></div>';
}
echo '<div class="card-box"><h3>Last month and this month</h3><p class="text-muted">History is counted from ranking, DA/PA, Search Console, Analytics, GBP, and Off Page logs.</p>';
echo '<table class="table"><thead><tr><th>Client</th><th>Status</th><th>Retention day</th>';
if ($user['role'] === 'admin') echo '<th>Package INR</th>';
echo '<th>Last month</th><th>This month</th><th>Latest log</th></tr></thead><tbody>';
foreach ($clients as $c) {
    $counts = month_activity((int)$c['id'], $prev);
    $now = month_activity((int)$c['id'], $month);
    echo '<tr><td><a href="seo.php?client_id=' . (int)$c['id'] . '">' . h($c['name']) . '</a></td><td>' . h($c['status']) . '</td><td>' . (int)$c['retention_day'] . '</td>';
    if ($user['role'] === 'admin') echo '<td>' . number_format((float)$c['package_cost'], 2) . '</td>';
    echo '<td>' . ($counts['parts'] ? 'Updated' : 'Not updated') . '</td><td>' . ($now['parts'] ? 'Updated' : 'Not updated') . '</td><td>' . h($now['latest'] ?: $counts['latest'] ?: '—') . '</td></tr>';
}
echo '</tbody></table></div>';
layout_end();

function month_activity(int $cid, string $ym): array {
    $like = $ym . '%';
    $parts = [];
    $latest = '';
    $q = [
        'Ranking' => 'SELECT COUNT(*) n, MAX(log_date) d FROM keyword_ranks r JOIN keywords k ON k.id = r.keyword_id WHERE k.client_id = ? AND r.log_date LIKE ?',
        'DA/PA' => 'SELECT COUNT(*) n, MAX(log_date) d FROM site_metrics WHERE client_id = ? AND log_date LIKE ?',
        'Search Console' => 'SELECT COUNT(*) n, MAX(from_date) d FROM range_logs WHERE client_id = ? AND kind = "gsc" AND from_date LIKE ?',
        'Analytics' => 'SELECT COUNT(*) n, MAX(from_date) d FROM range_logs WHERE client_id = ? AND kind = "analytics" AND from_date LIKE ?',
        'GBP' => 'SELECT COUNT(*) n, MAX(month) d FROM gbp_logs WHERE client_id = ? AND month = ?',
        'Off Page' => 'SELECT COUNT(*) n, MAX(month) d FROM offpage_rows WHERE client_id = ? AND month = ?',
    ];
    foreach ($q as $label => $sql) {
        $stmt = db()->prepare($sql);
        $stmt->execute([$cid, str_contains($sql, 'month = ?') ? $ym : $like]);
        $row = $stmt->fetch();
        if ((int)$row['n'] > 0) {
            $parts[] = $label . ' ' . (int)$row['n'];
            $latest = $row['d'] ?: $latest;
        }
    }
    return ['parts' => $parts, 'latest' => $latest];
}
function activity_label(array $a): string {
    return $a['parts'] ? implode(', ', $a['parts']) : 'Not updated';
}
function history_block(int $cid): string {
    $html = '<table class="table"><thead><tr><th>Module</th><th>When</th><th>Status</th></tr></thead><tbody>';
    $site = db()->prepare('SELECT * FROM site_metrics WHERE client_id = ? ORDER BY log_date DESC LIMIT 3');
    $site->execute([$cid]);
    foreach ($site->fetchAll() as $r) $html .= '<tr><td>DA / PA</td><td>' . h($r['log_date']) . '</td><td>DA ' . h($r['da']) . ' · PA ' . h($r['pa']) . ' · Spam ' . h($r['spam_score']) . '</td></tr>';
    $gsc = db()->prepare('SELECT * FROM range_logs WHERE client_id = ? AND kind = "gsc" ORDER BY from_date DESC LIMIT 3');
    $gsc->execute([$cid]);
    foreach ($gsc->fetchAll() as $r) {
        $m = json_decode($r['metrics_json'], true) ?: [];
        $html .= '<tr><td>Search Console</td><td>' . h($r['from_date'] . ' to ' . $r['to_date']) . '</td><td>Impressions ' . h($m['total_impressions'] ?? '') . ' · Clicks ' . h($m['total_clicks'] ?? '') . ' · AI ' . h($m['ai_impressions'] ?? '') . '</td></tr>';
    }
    return $html . '</tbody></table>';
}
