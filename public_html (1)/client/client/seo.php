<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login();
if ($user['role'] === 'employee' && !in_array('seo', work_types((int)$user['id']), true)) {
    exit('SEO is not assigned to this login.');
}
if ($user['role'] === 'client') exit('Use the client report.');
$clients = visible_clients($user);
$tab = $_GET['tab'] ?? 'ranking';
$cid = (int)($_GET['client_id'] ?? ($clients[0]['id'] ?? 0));
if ($cid && !can_client($user, $cid)) exit('Client not assigned.');

if (isset($_GET['del'], $_GET['table'], $_GET['id'])) {
    $allowed = ['keywords', 'keyword_ranks', 'site_metrics', 'range_logs', 'gbp_logs', 'offpage_rows', 'onpage_issues'];
    if (in_array($_GET['table'], $allowed, true)) {
        db()->prepare('DELETE FROM ' . $_GET['table'] . ' WHERE id = ?')->execute([(int)$_GET['id']]);
    }
    header('Location: seo.php?tab=' . urlencode($tab) . '&client_id=' . $cid);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid = (int)post('client_id');
    if (!can_client($user, $cid)) exit('Client not assigned.');
    $action = post('action');
    if ($action === 'keyword') {
        db()->prepare('INSERT INTO keywords (client_id, keyword, search_volume, competition) VALUES (?, ?, ?, ?)')
            ->execute([$cid, trim(post('keyword')), (int)post('search_volume'), trim(post('competition'))]);
    }
    if ($action === 'rank') {
        db()->prepare('INSERT INTO keyword_ranks (keyword_id, log_date, position, show_client) VALUES (?, ?, ?, ?)')
            ->execute([(int)post('keyword_id'), post('log_date'), (int)post('position'), isset($_POST['show_client']) ? 1 : 0]);
    }
    if ($action === 'site') {
        $show = checked_fields(array_keys(SITE_FIELDS));
        db()->prepare('INSERT INTO site_metrics (client_id, log_date, da, pa, spam_score, backlinks, referring_domains, dofollow_pct, nofollow_pct, show_json) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute([$cid, post('log_date'), post('da'), post('pa'), post('spam_score'), post('backlinks'), post('referring_domains'), post('dofollow_pct'), post('nofollow_pct'), json_encode($show)]);
    }
    if ($action === 'range') {
        $fields = post('kind') === 'analytics' ? ANALYTICS_FIELDS : GSC_FIELDS;
        $metrics = [];
        foreach ($fields as $k => $label) $metrics[$k] = post($k);
        db()->prepare('INSERT INTO range_logs (client_id, kind, from_date, to_date, metrics_json, show_json) VALUES (?, ?, ?, ?, ?, ?)')
            ->execute([$cid, post('kind'), post('from_date'), post('to_date'), json_encode($metrics), json_encode(checked_fields(array_keys($fields)))]);
    }
    if ($action === 'gbp') {
        $show = checked_fields(array_keys(GBP_FIELDS));
        db()->prepare('INSERT INTO gbp_logs (client_id, month, overview, calls, direction, website_click, chat_click, show_json) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$cid, post('month'), post('overview'), post('calls'), post('direction'), post('website_click'), post('chat_click'), json_encode($show)]);
    }
    if ($action === 'offpage') {
        db()->prepare('INSERT INTO offpage_rows (client_id, month, link_type, counts, show_client) VALUES (?, ?, ?, ?, ?)')
            ->execute([$cid, post('month'), trim(post('link_type')), (int)post('counts'), isset($_POST['show_client']) ? 1 : 0]);
    }
    if ($action === 'onpage') {
        db()->prepare('INSERT INTO onpage_issues (client_id, issue, fixation, status, show_client) VALUES (?, ?, ?, ?, ?)')
            ->execute([$cid, trim(post('issue')), trim(post('fixation')), post('status'), isset($_POST['show_client']) ? 1 : 0]);
    }
    $_SESSION['flash'] = 'Saved.';
    header('Location: seo.php?tab=' . urlencode(post('tab')) . '&client_id=' . $cid);
    exit;
}

layout_start('SEO', $user);
flash();
$tabs = ['ranking' => 'Ranking', 'site' => 'Site metrics', 'gsc' => 'Performance', 'analytics' => 'Analytics', 'gbp' => 'GBP', 'offpage' => 'Off Page', 'onpage' => 'On Page and Technical'];
echo '<div class="mb-3">';
foreach ($tabs as $k => $label) echo '<a class="btn btn-sm ' . ($tab === $k ? 'btn-red' : 'btn-outline-dark') . ' me-1" href="seo.php?tab=' . $k . '&client_id=' . $cid . '">' . h($label) . '</a>';
echo '</div><form class="card-box row g-2" method="get"><input type="hidden" name="tab" value="' . h($tab) . '"><div class="col-md-6">' . client_select($clients) . '</div><div class="col-md-2"><button class="btn btn-blue">Open</button></div></form>';
if (!$cid) { layout_end(); exit; }
echo '<script>document.querySelector("[name=client_id]").value=' . $cid . ';</script>';

if ($tab === 'ranking') {
    echo '<div class="card-box"><h3>Add keywords</h3><form method="post" class="row g-2"><input type="hidden" name="action" value="keyword"><input type="hidden" name="tab" value="ranking"><input type="hidden" name="client_id" value="' . $cid . '">';
    echo '<div class="col-md-4"><input class="form-control" name="keyword" placeholder="Keyword" required></div><div class="col-md-3"><input class="form-control" name="search_volume" placeholder="Search volume"></div><div class="col-md-3"><input class="form-control" name="competition" placeholder="Competition"></div><div class="col-md-2"><button class="btn btn-red">Add</button></div></form></div>';
    $kws = db()->prepare('SELECT * FROM keywords WHERE client_id = ?');
    $kws->execute([$cid]);
    $kws = $kws->fetchAll();
    echo '<div class="card-box"><h3>Add keyword ranking</h3><form method="post" class="row g-2"><input type="hidden" name="action" value="rank"><input type="hidden" name="tab" value="ranking"><input type="hidden" name="client_id" value="' . $cid . '">';
    echo '<div class="col-md-4"><select class="form-select" name="keyword_id">';
    foreach ($kws as $k) echo '<option value="' . (int)$k['id'] . '">' . h($k['keyword']) . '</option>';
    echo '</select></div><div class="col-md-3"><input class="form-control" type="date" name="log_date" required></div><div class="col-md-2"><input class="form-control" name="position" placeholder="Position" required></div><div class="col-md-2"><label><input type="checkbox" name="show_client" checked> Show to client</label></div><div class="col-md-1"><button class="btn btn-red">Save</button></div></form></div>';
    echo '<div class="card-box"><h3>Best 5 by position</h3><table class="table"><thead><tr><th>Keyword</th><th>Volume</th><th>Competition</th><th>Position</th><th>Page position</th><th>Date</th><th></th></tr></thead><tbody>';
    $best = db()->prepare('SELECT k.*, r.id rid, r.position, r.log_date FROM keywords k JOIN keyword_ranks r ON r.keyword_id = k.id WHERE k.client_id = ? AND r.id IN (SELECT MAX(id) FROM keyword_ranks GROUP BY keyword_id) ORDER BY r.position ASC LIMIT 5');
    $best->execute([$cid]);
    foreach ($best->fetchAll() as $row) {
        echo '<tr><td>' . h($row['keyword']) . '</td><td>' . (int)$row['search_volume'] . '</td><td>' . h($row['competition']) . '</td><td>' . (int)$row['position'] . '</td><td>' . h(page_position((int)$row['position'])) . '</td><td>' . h($row['log_date']) . '</td><td><a href="seo.php?tab=ranking&client_id=' . $cid . '&del=1&table=keyword_ranks&id=' . (int)$row['rid'] . '">Delete log</a></td></tr>';
    }
    echo '</tbody></table></div>';
    $rankRows = db()->prepare('SELECT k.id, k.keyword, k.search_volume, r.position, r.log_date, r.id rid FROM keywords k JOIN keyword_ranks r ON r.keyword_id = k.id WHERE k.client_id = ? ORDER BY r.log_date');
    $rankRows->execute([$cid]);
    $byKeyword = [];
    $dates = [];
    foreach ($rankRows->fetchAll() as $row) {
        $byKeyword[$row['id']]['keyword'] = $row['keyword'];
        $byKeyword[$row['id']]['volume'] = $row['search_volume'];
        $byKeyword[$row['id']]['logs'][$row['log_date']] = ['position' => (int)$row['position'], 'id' => (int)$row['rid']];
        $dates[$row['log_date']] = true;
    }
    $dates = array_keys($dates);
    sort($dates);
    $dates = array_slice($dates, -6);
    uasort($byKeyword, function ($a, $b) use ($dates) {
        $pa = 999; $pb = 999;
        foreach (array_reverse($dates) as $d) {
            if (isset($a['logs'][$d])) { $pa = $a['logs'][$d]['position']; break; }
        }
        foreach (array_reverse($dates) as $d) {
            if (isset($b['logs'][$d])) { $pb = $b['logs'][$d]['position']; break; }
        }
        return $pa <=> $pb;
    });
    $chunks = array_chunk($byKeyword, 10, true);
    $pageNo = 1;
    foreach ($chunks as $chunk) {
        $from = (($pageNo - 1) * 10) + 1;
        $to = $from + count($chunk) - 1;
        echo '<div class="card-box compare-card"><div class="compare-head"><h3>Comparison · keywords ' . $from . '–' . $to . '</h3><span>10 keywords per block · Google page is 10 results</span></div>';
        echo '<div class="table-scroll"><table class="table compare-table"><thead><tr><th>Keyword</th>';
        foreach ($dates as $d) echo '<th>' . h($d) . '</th>';
        echo '<th>Change</th></tr></thead><tbody>';
        foreach ($chunk as $item) {
            echo '<tr><td><strong>' . h($item['keyword']) . '</strong><small>Vol ' . (int)$item['volume'] . '</small></td>';
            $prev = null; $last = null;
            foreach ($dates as $d) {
                if (!isset($item['logs'][$d])) { echo '<td class="muted">—</td>'; continue; }
                $pos = $item['logs'][$d]['position'];
                $last = $pos;
                $move = '';
                if ($prev !== null) $move = $pos < $prev ? ' up' : ($pos > $prev ? ' down' : '');
                echo '<td class="pos' . $move . '">' . $pos . '<small>' . h(page_position($pos)) . '</small></td>';
                $prev = $pos;
            }
            $first = null;
            foreach ($dates as $d) if (isset($item['logs'][$d])) { $first = $item['logs'][$d]['position']; break; }
            $delta = ($first !== null && $last !== null) ? $first - $last : 0;
            $cls = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : '');
            echo '<td class="' . $cls . '">' . ($delta > 0 ? '+' : '') . $delta . '</td></tr>';
        }
        echo '</tbody></table></div></div>';
        $pageNo++;
    }
}
if ($tab === 'site') {
    echo '<div class="card-box"><form method="post" class="row g-2"><input type="hidden" name="action" value="site"><input type="hidden" name="tab" value="site"><input type="hidden" name="client_id" value="' . $cid . '"><div class="col-md-3"><input class="form-control" type="date" name="log_date" required></div>';
    foreach (SITE_FIELDS as $k => $label) echo '<div class="col-md-3"><input class="form-control" name="' . h($k) . '" placeholder="' . h($label) . '"></div><div class="col-md-3"><label><input type="checkbox" name="show[' . h($k) . ']" checked> Show ' . h($label) . '</label></div>';
    echo '<div class="col-12"><button class="btn btn-red">Add status</button></div></form></div>';
    $rows = db()->prepare('SELECT * FROM site_metrics WHERE client_id = ? ORDER BY log_date DESC');
    $rows->execute([$cid]);
    echo '<div class="card-box"><h3>DA / PA history</h3><table class="table"><thead><tr><th>Date</th>';
    foreach (SITE_FIELDS as $label) echo '<th>' . h($label) . '</th>';
    echo '<th></th></tr></thead><tbody>';
    foreach ($rows->fetchAll() as $row) {
        echo '<tr><td>' . h($row['log_date']) . '</td>';
        foreach (SITE_FIELDS as $k => $label) echo '<td>' . h($row[$k]) . '</td>';
        echo '<td><a href="seo.php?tab=site&client_id=' . $cid . '&del=1&table=site_metrics&id=' . (int)$row['id'] . '">Delete</a></td></tr>';
    }
    echo '</tbody></table></div>';
}
if ($tab === 'gsc' || $tab === 'analytics') {
    $kind = $tab === 'analytics' ? 'analytics' : 'gsc';
    $fields = $kind === 'analytics' ? ANALYTICS_FIELDS : GSC_FIELDS;
    echo '<div class="card-box"><form method="post" class="row g-2"><input type="hidden" name="action" value="range"><input type="hidden" name="kind" value="' . $kind . '"><input type="hidden" name="tab" value="' . $tab . '"><input type="hidden" name="client_id" value="' . $cid . '">';
    echo '<div class="col-md-3"><input class="form-control" type="date" name="from_date" required></div><div class="col-md-3"><input class="form-control" type="date" name="to_date" required></div>';
    foreach ($fields as $k => $label) echo '<div class="col-md-4"><input class="form-control" name="' . h($k) . '" placeholder="' . h($label) . '"></div><div class="col-md-4"><label><input type="checkbox" name="show[' . h($k) . ']" checked> Show to client</label></div>';
    echo '<div class="col-12"><button class="btn btn-red">Save range</button></div></form></div>';
    $logs = db()->prepare('SELECT * FROM range_logs WHERE client_id = ? AND kind = ? ORDER BY from_date');
    $logs->execute([$cid, $kind]);
    $logs = $logs->fetchAll();
    echo '<div class="card-box"><table class="table"><thead><tr><th>Range</th><th>Days</th>';
    foreach ($fields as $label) echo '<th>' . h($label) . '</th>';
    echo '</tr></thead><tbody>';
    $prev = null;
    foreach ($logs as $log) {
        $m = json_decode($log['metrics_json'], true) ?: [];
        echo '<tr><td>' . h($log['from_date'] . ' to ' . $log['to_date']) . '</td><td>' . day_count($log['from_date'], $log['to_date']) . '</td>';
        foreach ($fields as $k => $label) {
            $diff = $prev ? ' (' . ((float)$m[$k] - (float)($prev[$k] ?? 0)) . ')' : '';
            echo '<td>' . h($m[$k] ?? '') . $diff . '</td>';
        }
        echo '</tr>';
        $prev = $m;
    }
    echo '</tbody></table></div>';
}
if ($tab === 'gbp') {
    echo '<div class="card-box"><form method="post" class="row g-2"><input type="hidden" name="action" value="gbp"><input type="hidden" name="tab" value="gbp"><input type="hidden" name="client_id" value="' . $cid . '"><div class="col-md-3"><input class="form-control" name="month" placeholder="YYYY-MM" required></div>';
    foreach (GBP_FIELDS as $k => $label) echo '<div class="col-md-3"><input class="form-control" name="' . h($k) . '" placeholder="' . h($label) . '"></div><div class="col-md-3"><label><input type="checkbox" name="show[' . h($k) . ']" checked> Show</label></div>';
    echo '<div class="col-12"><button class="btn btn-red">Save month</button></div></form></div>';
    $rows = db()->prepare('SELECT * FROM gbp_logs WHERE client_id = ? ORDER BY month DESC');
    $rows->execute([$cid]);
    echo '<div class="card-box"><h3>GBP history</h3><table class="table"><thead><tr><th>Month</th>';
    foreach (GBP_FIELDS as $label) echo '<th>' . h($label) . '</th>';
    echo '<th></th></tr></thead><tbody>';
    foreach ($rows->fetchAll() as $row) {
        echo '<tr><td>' . h($row['month']) . '</td>';
        foreach (GBP_FIELDS as $k => $label) echo '<td>' . h($row[$k]) . '</td>';
        echo '<td><a href="seo.php?tab=gbp&client_id=' . $cid . '&del=1&table=gbp_logs&id=' . (int)$row['id'] . '">Delete</a></td></tr>';
    }
    echo '</tbody></table></div>';
}
if ($tab === 'offpage') {
    echo '<div class="card-box"><form method="post" class="row g-2"><input type="hidden" name="action" value="offpage"><input type="hidden" name="tab" value="offpage"><input type="hidden" name="client_id" value="' . $cid . '">';
    echo '<div class="col-md-3"><input class="form-control" name="month" placeholder="YYYY-MM" required></div><div class="col-md-4"><input class="form-control" name="link_type" placeholder="Bookmarking, direct, classified" required></div><div class="col-md-2"><input class="form-control" name="counts" placeholder="Count" required></div><div class="col-md-2"><label><input type="checkbox" name="show_client" checked> Show</label></div><div class="col-md-1"><button class="btn btn-red">Add</button></div></form></div>';
    $rows = db()->prepare('SELECT * FROM offpage_rows WHERE client_id = ? ORDER BY month, id');
    $rows->execute([$cid]);
    echo '<div class="card-box"><table class="table"><thead><tr><th>Month</th><th>Type</th><th>Count</th><th></th></tr></thead><tbody>';
    foreach ($rows->fetchAll() as $r) echo '<tr><td>' . h($r['month']) . '</td><td>' . h($r['link_type']) . '</td><td>' . (int)$r['counts'] . '</td><td><a href="seo.php?tab=offpage&client_id=' . $cid . '&del=1&table=offpage_rows&id=' . (int)$r['id'] . '">Delete</a></td></tr>';
    echo '</tbody></table></div>';
}
if ($tab === 'onpage') {
    echo '<div class="card-box"><form method="post" class="row g-2"><input type="hidden" name="action" value="onpage"><input type="hidden" name="tab" value="onpage"><input type="hidden" name="client_id" value="' . $cid . '">';
    echo '<div class="col-md-4"><input class="form-control" name="issue" placeholder="Issue" required></div><div class="col-md-4"><input class="form-control" name="fixation" placeholder="What was fixed"></div><div class="col-md-2"><select class="form-select" name="status"><option>done</option><option>pending</option><option>working</option></select></div><div class="col-md-2"><label><input type="checkbox" name="show_client" checked> Show</label></div><div class="col-12"><button class="btn btn-red">Add issue</button></div></form></div>';
    $issues = db()->prepare('SELECT * FROM onpage_issues WHERE client_id = ? ORDER BY created_at DESC');
    $issues->execute([$cid]);
    echo '<div class="card-box"><h3>Issue history</h3><table class="table"><thead><tr><th>Issue</th><th>Fixation</th><th>Status</th><th>Shown</th><th></th></tr></thead><tbody>';
    foreach ($issues->fetchAll() as $row) echo '<tr><td>' . h($row['issue']) . '</td><td>' . h($row['fixation']) . '</td><td>' . h($row['status']) . '</td><td>' . ($row['show_client'] ? 'Yes' : 'No') . '</td><td><a href="seo.php?tab=onpage&client_id=' . $cid . '&del=1&table=onpage_issues&id=' . (int)$row['id'] . '">Delete</a></td></tr>';
    echo '</tbody></table></div>';
}
layout_end();
