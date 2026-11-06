<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login();
sync_notifications($user);
layout_start('Overview', $user);
flash();
$clients = visible_clients($user);
echo '<div class="row">';
echo '<div class="col-md-4"><div class="card-box stat"><div class="text-muted">Clients in view</div><h2>' . count($clients) . '</h2></div></div>';
echo '<div class="col-md-8"><div class="card-box"><strong>This month</strong><p class="mb-0">Retention date is the report-share reminder. Package cost is visible to admin only.</p></div></div>';
echo '</div>';
echo '<div class="card-box"><table class="table"><thead><tr><th>Client</th><th>Status</th><th>Retention day</th>';
if ($user['role'] === 'admin') echo '<th>Package INR</th>';
echo '<th>Last month log</th><th>This month</th></tr></thead><tbody>';
$month = date('Y-m');
$prev = date('Y-m', strtotime('first day of last month'));
foreach ($clients as $c) {
    $thisLog = db()->prepare('SELECT COUNT(*) n FROM range_logs WHERE client_id = ? AND from_date LIKE ?');
    $thisLog->execute([$c['id'], $month . '%']);
    $prevLog = db()->prepare('SELECT COUNT(*) n FROM range_logs WHERE client_id = ? AND from_date LIKE ?');
    $prevLog->execute([$c['id'], $prev . '%']);
    echo '<tr><td>' . h($c['name']) . '</td><td>' . h($c['status']) . '</td><td>' . (int)$c['retention_day'] . '</td>';
    if ($user['role'] === 'admin') echo '<td>' . number_format((float)$c['package_cost'], 2) . '</td>';
    echo '<td>' . ((int)$prevLog->fetch()['n'] ? 'Updated' : 'Not updated') . '</td>';
    echo '<td>' . ((int)$thisLog->fetch()['n'] ? 'Updated' : 'Not updated') . '</td></tr>';
}
echo '</tbody></table></div>';
layout_end();
