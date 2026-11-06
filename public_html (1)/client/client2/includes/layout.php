<?php
function layout_start(string $title, array $user): void {
    $base = BASE_URL;
    $notes = unread_count((int)$user['id']);
    $nav = '';
    if ($user['role'] === 'admin') {
        $nav = item($base . '/dashboard.php', 'Overview') . item($base . '/clients.php', 'Clients') . item($base . '/employees.php', 'Employees') . item($base . '/seo.php', 'SEO') . item($base . '/social.php', 'Social') . item($base . '/ads.php', 'Meta Ads');
    } elseif ($user['role'] === 'employee') {
        $works = work_types((int)$user['id']);
        $nav = item($base . '/dashboard.php', 'Overview');
        if (in_array('seo', $works, true)) $nav .= item($base . '/seo.php', 'SEO');
        if (in_array('smo', $works, true)) $nav .= item($base . '/social.php', 'Social');
        if (in_array('ads', $works, true)) $nav .= item($base . '/ads.php', 'Meta Ads');
    } else {
        $mine = visible_clients($user);
        $services = $mine ? services_of((int)$mine[0]['id']) : [];
        $nav = item($base . '/dashboard.php', 'Dashboard');
        if (in_array('seo', $services, true)) $nav .= item($base . '/client_report.php?service=seo', 'SEO');
        if (in_array('smo', $services, true)) $nav .= item($base . '/client_report.php?service=smo', 'SMO');
        if (in_array('ads', $services, true)) $nav .= item($base . '/client_report.php?service=ads', 'Ads report');
    }
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="theme-color" content="#1A1A1A"><link rel="manifest" href="' . $base . '/manifest.webmanifest">';
    echo '<link rel="icon" href="' . $base . '/assets/img/logo.png">';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">';
    echo '<link rel="stylesheet" href="' . $base . '/assets/css/app.css"><title>' . h($title) . ' · Digital Falcon</title></head><body>';
    echo '<div class="app-shell"><aside class="side"><div class="brand"><img src="' . $base . '/assets/img/logo.png" alt="Digital Falcon"><div><strong>Digital Falcon</strong><small>Growth Accelerator</small></div></div>';
    echo '<nav>' . $nav . '</nav><div class="side-foot">' . h($user['name']) . '<br><span class="role">' . h($user['role']) . '</span></div></aside>';
    echo '<main><header class="top"><h1>' . h($title) . '</h1><div class="top-actions"><a class="note-link" href="' . $base . '/notifications.php">Notifications' . ($notes ? ' <span class="badge">' . $notes . '</span>' : '') . '</a><a href="' . $base . '/logout.php">Log out</a></div></header><section class="content">';
}

function item(string $href, string $label): string {
    return '<a href="' . h($href) . '">' . h($label) . '</a>';
}

function layout_end(): void {
    $base = BASE_URL;
    echo '</section></main></div><script src="' . $base . '/assets/js/app.js"></script>';
    echo '<script>if("serviceWorker" in navigator){navigator.serviceWorker.register("' . $base . '/sw.js");}</script></body></html>';
}

function flash(): void {
    if (!empty($_SESSION['flash'])) {
        echo '<div class="alert alert-success">' . h($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
}

function client_select(array $clients, string $name = 'client_id'): string {
    $html = '<select class="form-select" name="' . h($name) . '" required><option value="">Select client</option>';
    foreach ($clients as $c) {
        $html .= '<option value="' . (int)$c['id'] . '">' . h($c['name']) . '</option>';
    }
    return $html . '</select>';
}
