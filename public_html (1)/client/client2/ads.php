<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';
$user = require_login();
if ($user['role'] === 'employee' && !in_array('ads', work_types((int)$user['id']), true)) exit('Ads is not assigned.');
if ($user['role'] === 'client') exit('Use the client report.');
$clients = visible_clients($user);
$cid = (int)($_REQUEST['client_id'] ?? ($clients[0]['id'] ?? 0));
if ($cid && !can_client($user, $cid)) exit('Client not assigned.');

if (isset($_GET['del'])) {
    db()->prepare('DELETE FROM campaigns WHERE id = ? AND client_id = ?')->execute([(int)$_GET['del'], $cid]);
    header('Location: ads.php?client_id=' . $cid);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ages = [];
    foreach (AGE_BANDS as $band) $ages[$band] = post('age_' . $band) === '' ? 'nil' : post('age_' . $band);
    $show = checked_fields(array_keys(AD_FIELDS));
    foreach (AGE_BANDS as $band) $show['age_' . $band] = isset($_POST['show']['age_' . $band]) ? 1 : 0;
    db()->prepare('INSERT INTO campaigns (client_id, name, total_spend, amount_spent, from_date, to_date, objective, leads, cost_per_lead, cost_per_message, cost_per_click, ages_json, ig_reach, fb_reach, impressions, views, likes, shares, comments, ad_sets, ads, show_json) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$cid, trim(post('name')), post('total_spend'), post('amount_spent'), post('from_date'), post('to_date'), trim(post('objective')), post('leads'), post('cost_per_lead'), post('cost_per_message'), post('cost_per_click'), json_encode($ages), post('ig_reach'), post('fb_reach'), post('impressions'), post('views'), post('likes'), post('shares'), post('comments'), post('ad_sets'), post('ads'), json_encode($show)]);
    $_SESSION['flash'] = 'Campaign saved.';
    header('Location: ads.php?client_id=' . $cid);
    exit;
}
layout_start('Meta Ads', $user);
flash();
echo '<form class="card-box" method="get"><div class="row g-2"><div class="col-md-6">' . client_select($clients) . '</div><div class="col-md-2"><button class="btn btn-blue">Open</button></div></div></form>';
if (!$cid) { layout_end(); exit; }
echo '<script>document.querySelector("[name=client_id]").value=' . $cid . ';</script>';
echo '<div class="card-box"><h3>Add campaign report</h3><form method="post" class="row g-2"><input type="hidden" name="client_id" value="' . $cid . '">';
$inputs = ['name' => 'Campaign name', 'total_spend' => 'Total spend', 'amount_spent' => 'Amount spent', 'objective' => 'Objective', 'leads' => 'Number of leads', 'cost_per_lead' => 'Cost per lead', 'cost_per_message' => 'Cost per message', 'cost_per_click' => 'Cost per click', 'ig_reach' => 'Instagram reach', 'fb_reach' => 'Facebook reach', 'impressions' => 'Impressions', 'views' => 'Views', 'likes' => 'Likes', 'shares' => 'Shares', 'comments' => 'Comments', 'ad_sets' => 'Number of ad sets', 'ads' => 'Number of ads'];
echo '<div class="col-md-3"><input class="form-control" type="date" name="from_date" required></div><div class="col-md-3"><input class="form-control" type="date" name="to_date" required></div>';
foreach ($inputs as $k => $label) echo '<div class="col-md-4"><input class="form-control" name="' . h($k) . '" placeholder="' . h($label) . '"></div><div class="col-md-4"><label><input type="checkbox" name="show[' . h($k) . ']" checked> Show</label></div>';
foreach (AGE_BANDS as $band) echo '<div class="col-md-3"><input class="form-control" name="age_' . h($band) . '" placeholder="Age ' . h($band) . ' %"></div><div class="col-md-3"><label><input type="checkbox" name="show[age_' . h($band) . ']" checked> Show</label></div>';
echo '<div class="col-12"><button class="btn btn-red">Save campaign</button></div></form></div>';
$rows = db()->prepare('SELECT * FROM campaigns WHERE client_id = ? ORDER BY from_date DESC');
$rows->execute([$cid]);
echo '<div class="card-box"><table class="table"><thead><tr><th>Campaign</th><th>Spend</th><th>Range</th><th>Leads</th><th></th></tr></thead><tbody>';
foreach ($rows->fetchAll() as $r) echo '<tr><td>' . h($r['name']) . '</td><td>' . h($r['total_spend']) . '</td><td>' . h($r['from_date'] . ' to ' . $r['to_date']) . '</td><td>' . (int)$r['leads'] . '</td><td><a href="ads.php?client_id=' . $cid . '&del=' . (int)$r['id'] . '">Delete</a></td></tr>';
echo '</tbody></table></div>';
layout_end();
