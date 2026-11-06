<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login();
if ($user['role'] === 'employee' && !in_array('smo', work_types((int)$user['id']), true)) exit('SMO is not assigned.');
if ($user['role'] === 'client') exit('Use the client report.');
$clients = visible_clients($user);
$cid = (int)($_REQUEST['client_id'] ?? ($clients[0]['id'] ?? 0));
if ($cid && !can_client($user, $cid)) exit('Client not assigned.');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'channels') {
    db()->prepare('DELETE FROM client_channels WHERE client_id = ?')->execute([$cid]);
    $ins = db()->prepare('INSERT INTO client_channels (client_id, channel) VALUES (?, ?)');
    foreach (array_intersect(CHANNELS, (array)post('channels', [])) as $ch) $ins->execute([$cid, $ch]);
    $_SESSION['flash'] = 'Channels saved.';
    header('Location: social.php?client_id=' . $cid);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('action') === 'stats') {
    $channel = post('channel');
    $fields = CHANNEL_FIELDS[$channel] ?? [];
    $metrics = [];
    foreach ($fields as $k => $label) $metrics[$k] = post($k);
    db()->prepare('INSERT INTO social_logs (client_id, channel, from_date, to_date, metrics_json, show_json) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$cid, $channel, post('from_date'), post('to_date'), json_encode($metrics), json_encode(checked_fields(array_keys($fields)))]);
    $_SESSION['flash'] = 'Stats saved.';
    header('Location: social.php?client_id=' . $cid . '&channel=' . urlencode($channel));
    exit;
}
if (isset($_GET['del'])) {
    db()->prepare('DELETE FROM social_logs WHERE id = ? AND client_id = ?')->execute([(int)$_GET['del'], $cid]);
    header('Location: social.php?client_id=' . $cid);
    exit;
}
layout_start('Social media', $user);
flash();
echo '<form class="card-box" method="get"><div class="row g-2"><div class="col-md-6">' . client_select($clients) . '</div><div class="col-md-2"><button class="btn btn-blue">Open</button></div></div></form>';
if (!$cid) { layout_end(); exit; }
echo '<script>document.querySelector("[name=client_id]").value=' . $cid . ';</script>';
$saved = db()->prepare('SELECT channel FROM client_channels WHERE client_id = ?');
$saved->execute([$cid]);
$saved = array_column($saved->fetchAll(), 'channel');
echo '<div class="card-box"><h3>Clients · channel update</h3><form method="post">';
echo '<input type="hidden" name="action" value="channels"><input type="hidden" name="client_id" value="' . $cid . '">';
foreach (CHANNELS as $ch) {
    $on = in_array($ch, $saved, true) ? 'checked' : '';
    echo '<label class="form-check form-check-inline"><input class="form-check-input" type="checkbox" name="channels[]" value="' . h($ch) . '" ' . $on . '> ' . h(ucfirst($ch)) . '</label>';
}
echo '<button class="btn btn-red ms-2">Save channels</button></form></div>';
$channel = $_GET['channel'] ?? ($saved[0] ?? '');
if ($saved) {
    echo '<div class="mb-2">';
    foreach ($saved as $ch) echo '<a class="btn btn-sm ' . ($channel === $ch ? 'btn-red' : 'btn-outline-dark') . ' me-1" href="social.php?client_id=' . $cid . '&channel=' . h($ch) . '">' . h(ucfirst($ch)) . '</a>';
    echo '</div>';
}
if ($channel && isset(CHANNEL_FIELDS[$channel])) {
    $fields = CHANNEL_FIELDS[$channel];
    echo '<div class="card-box"><h3>Add stats</h3><form method="post" class="row g-2"><input type="hidden" name="action" value="stats"><input type="hidden" name="client_id" value="' . $cid . '"><input type="hidden" name="channel" value="' . h($channel) . '">';
    echo '<div class="col-md-3"><input class="form-control" type="date" name="from_date" required></div><div class="col-md-3"><input class="form-control" type="date" name="to_date" required></div>';
    foreach ($fields as $k => $label) echo '<div class="col-md-4"><input class="form-control" name="' . h($k) . '" placeholder="' . h($label) . '"></div><div class="col-md-4"><label><input type="checkbox" name="show[' . h($k) . ']" checked> Show</label></div>';
    echo '<div class="col-12"><button class="btn btn-red">Save</button></div></form></div>';
    $logs = db()->prepare('SELECT * FROM social_logs WHERE client_id = ? AND channel = ? ORDER BY from_date');
    $logs->execute([$cid, $channel]);
    $logs = $logs->fetchAll();
    echo '<div class="card-box"><table class="table"><thead><tr><th>Range</th><th>Days</th>';
    foreach ($fields as $label) echo '<th>' . h($label) . '</th>';
    echo '<th></th></tr></thead><tbody>';
    $prev = null;
    foreach ($logs as $log) {
        $m = json_decode($log['metrics_json'], true) ?: [];
        echo '<tr><td>' . h($log['from_date'] . ' to ' . $log['to_date']) . '</td><td>' . day_count($log['from_date'], $log['to_date']) . '</td>';
        foreach ($fields as $k => $label) echo '<td>' . h($m[$k] ?? '') . ($prev ? ' (' . ((float)($m[$k] ?? 0) - (float)($prev[$k] ?? 0)) . ')' : '') . '</td>';
        echo '<td><a href="social.php?client_id=' . $cid . '&channel=' . h($channel) . '&del=' . (int)$log['id'] . '">Delete</a></td></tr>';
        $prev = $m;
    }
    echo '</tbody></table></div>';
}
layout_end();
