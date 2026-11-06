<?php
function h(?string $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function post(string $key, $default = '') {
    return $_POST[$key] ?? $default;
}

function client_row(int $id): ?array {
    $stmt = db()->prepare('SELECT c.*, u.name, u.username FROM clients c JOIN users u ON u.id = c.user_id WHERE c.id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function services_of(int $clientId): array {
    $stmt = db()->prepare('SELECT service FROM client_services WHERE client_id = ?');
    $stmt->execute([$clientId]);
    return array_column($stmt->fetchAll(), 'service');
}

function employee_clients(int $userId, bool $activeOnly = true): array {
    $sql = 'SELECT c.*, u.name FROM employee_clients ec
            JOIN clients c ON c.id = ec.client_id
            JOIN users u ON u.id = c.user_id
            WHERE ec.user_id = ?';
    if ($activeOnly) {
        $sql .= " AND c.status = 'active'";
    }
    $sql .= ' ORDER BY u.name';
    $stmt = db()->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function work_types(int $userId): array {
    $stmt = db()->prepare('SELECT work_type FROM employee_work WHERE user_id = ?');
    $stmt->execute([$userId]);
    return array_column($stmt->fetchAll(), 'work_type');
}

function can_client(array $user, int $clientId): bool {
    if ($user['role'] === 'admin') {
        return true;
    }
    if ($user['role'] === 'client') {
        $stmt = db()->prepare('SELECT id FROM clients WHERE id = ? AND user_id = ?');
        $stmt->execute([$clientId, $user['id']]);
        return (bool)$stmt->fetch();
    }
    $stmt = db()->prepare("SELECT 1 FROM employee_clients ec JOIN clients c ON c.id = ec.client_id
        WHERE ec.user_id = ? AND ec.client_id = ? AND c.status = 'active'");
    $stmt->execute([$user['id'], $clientId]);
    return (bool)$stmt->fetch();
}

function visible_clients(array $user): array {
    if ($user['role'] === 'admin') {
        return db()->query('SELECT c.*, u.name FROM clients c JOIN users u ON u.id = c.user_id ORDER BY u.name')->fetchAll();
    }
    if ($user['role'] === 'employee') {
        return employee_clients((int)$user['id'], true);
    }
    $stmt = db()->prepare('SELECT c.*, u.name FROM clients c JOIN users u ON u.id = c.user_id WHERE c.user_id = ?');
    $stmt->execute([$user['id']]);
    return $stmt->fetchAll();
}

function page_position(int $position): string {
    $page = (int)ceil($position / 10);
    $onPage = (($position - 1) % 10) + 1;
    return 'Page ' . $page . ', position ' . $onPage;
}

function day_count(string $from, string $to): int {
    $a = new DateTime($from);
    $b = new DateTime($to);
    return (int)$a->diff($b)->days + 1;
}

function show_map(array $fields, ?string $json): array {
    $saved = $json ? json_decode($json, true) : null;
    $out = [];
    foreach ($fields as $f) {
        $out[$f] = $saved === null ? true : !empty($saved[$f]);
    }
    return $out;
}

function checked_fields(array $fields): array {
    $out = [];
    foreach ($fields as $f) {
        $out[$f] = isset($_POST['show'][$f]) ? 1 : 0;
    }
    return $out;
}

function sync_notifications(array $user): void {
    $today = (int)date('j');
    $month = date('Y-m');
    $targets = [];
    if ($user['role'] === 'admin') {
        $targets = db()->query('SELECT c.id, u.name, c.retention_day FROM clients c JOIN users u ON u.id = c.user_id')->fetchAll();
        $uid = (int)$user['id'];
    } elseif ($user['role'] === 'employee') {
        $targets = employee_clients((int)$user['id'], true);
        $uid = (int)$user['id'];
    } else {
        return;
    }
    foreach ($targets as $c) {
        if ((int)$c['retention_day'] === $today) {
            note_once($uid, 'Report share due today for ' . $c['name']);
        }
        $has = db()->prepare('SELECT 1 FROM range_logs WHERE client_id = ? AND from_date LIKE ? LIMIT 1');
        $has->execute([$c['id'], $month . '%']);
        $has2 = db()->prepare('SELECT 1 FROM keyword_ranks kr JOIN keywords k ON k.id = kr.keyword_id WHERE k.client_id = ? AND kr.log_date LIKE ? LIMIT 1');
        $has2->execute([$c['id'], $month . '%']);
        if (!$has->fetch() && !$has2->fetch()) {
            note_once($uid, 'Current month not updated for ' . $c['name']);
        }
    }
}

function note_once(int $userId, string $message): void {
    $stmt = db()->prepare('SELECT id FROM notifications WHERE user_id = ? AND message = ? AND DATE(created_at) = CURDATE()');
    $stmt->execute([$userId, $message]);
    if (!$stmt->fetch()) {
        db()->prepare('INSERT INTO notifications (user_id, message) VALUES (?, ?)')->execute([$userId, $message]);
    }
}

function unread_count(int $userId): int {
    $stmt = db()->prepare('SELECT COUNT(*) c FROM notifications WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int)$stmt->fetch()['c'];
}

const GSC_FIELDS = ['total_impressions' => 'Total impressions', 'total_clicks' => 'Total clicks', 'average_ctr' => 'Average CTR', 'ai_impressions' => 'AI impressions'];
const ANALYTICS_FIELDS = [
    'total_users' => 'Total users', 'new_users' => 'New users', 'sessions' => 'Sessions',
    'engaged_sessions' => 'Engaged sessions', 'event_count' => 'Event count', 'active_users' => 'Active users',
    'views' => 'Views', 'returning_users' => 'Returning users', 'bounce_rate' => 'Bounce rate', 'organic_traffic' => 'Organic traffic'
];
const SITE_FIELDS = ['da' => 'DA', 'pa' => 'PA', 'spam_score' => 'Spam score', 'backlinks' => 'Backlinks', 'referring_domains' => 'Referring domains', 'dofollow_pct' => 'Do-follow %', 'nofollow_pct' => 'No-follow %'];
const GBP_FIELDS = ['overview' => 'Overview', 'calls' => 'Calls', 'direction' => 'Direction', 'website_click' => 'Website click', 'chat_click' => 'Chat click'];
const AGE_BANDS = ['13-17', '18-24', '25-34', '35-44', '45-54', '55-64', '65+'];
const CHANNELS = ['facebook', 'instagram', 'linkedin', 'youtube'];
const CHANNEL_FIELDS = [
    'facebook' => ['views' => 'Views', 'follows' => 'Follows', 'visits' => 'Visits', 'interactions' => 'Interactions', 'conversations' => 'Conversations', 'link_clicks' => 'Link clicks', 'likes' => 'Likes', 'shares' => 'Shares', 'comments' => 'Comments', 'followers' => 'Followers', 'unfollowers' => 'Unfollowers', 'unfollows' => 'Unfollows', 'posts' => 'Number of posts'],
    'instagram' => ['views' => 'Views', 'follows' => 'Follows', 'reach' => 'Reach', 'interactions' => 'Interactions', 'conversations' => 'Conversations', 'likes' => 'Likes', 'shares' => 'Shares', 'comments' => 'Comments', 'followers' => 'Followers', 'unfollowers' => 'Unfollowers', 'posts' => 'Number of posts'],
    'linkedin' => ['impressions' => 'Impressions', 'reactions' => 'Reactions', 'comments' => 'Comments', 'reposts' => 'Reposts', 'organic_impressions' => 'Organic impressions', 'new_followers' => 'New followers', 'total_followers' => 'Total followers', 'visitors' => 'Visitors', 'growth_pct' => 'Follower growth %', 'search_appearance' => 'Search appearance'],
    'youtube' => ['subscribers' => 'Total subscribers', 'views' => 'Views', 'watch_hours' => 'Watch hours', 'likes' => 'Likes', 'comments' => 'Comments', 'shares' => 'Shares'],
];
const AD_FIELDS = ['total_spend' => 'Total spend', 'amount_spent' => 'Amount spent', 'objective' => 'Objective', 'leads' => 'Leads', 'cost_per_lead' => 'Cost per lead', 'cost_per_message' => 'Cost per message', 'cost_per_click' => 'Cost per click', 'ig_reach' => 'Instagram reach', 'fb_reach' => 'Facebook reach', 'impressions' => 'Impressions', 'views' => 'Views', 'likes' => 'Likes', 'shares' => 'Shares', 'comments' => 'Comments', 'ad_sets' => 'Ad sets', 'ads' => 'Ads'];
