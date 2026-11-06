<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$db = getDB();
$message = '';
$error = '';

// Handle actions
if (isset($_GET['action'], $_GET['type'], $_GET['name'])) {
    $action = $_GET['action'];
    $type = $_GET['type'];
    $rawName = urldecode($_GET['name']);
    $normalized = preg_replace('/\s+/', ' ', mb_strtolower(trim($rawName)));

    if ($action === 'approve' && in_array($type, ['service', 'project'])) {
        $stmt = $db->prepare("INSERT IGNORE INTO ignored_duplicates (type, normalized_name) VALUES (?, ?)");
        $stmt->execute([$type, $normalized]);
        $message = 'Approved. This duplicate group will no longer appear.';
    }

    if ($action === 'hold' && in_array($type, ['service', 'project'])) {
        if (!isset($_SESSION['held_duplicates'])) {
            $_SESSION['held_duplicates'] = [];
        }
        $_SESSION['held_duplicates'][$type][$normalized] = true;
        $message = 'Held. It will appear again after your next login.';
    }

    if ($action === 'delete' && in_array($type, ['service', 'project']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = (int)$_GET['id'];
        if ($type === 'service') {
            $stmt = $db->prepare("SELECT image_path FROM services WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if ($row && !empty($row['image_path'])) {
                $full = UPLOAD_DIR . basename($row['image_path']);
                if (is_file($full)) @unlink($full);
            }
            $db->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
        } else {
            $stmt = $db->prepare("SELECT file_path FROM project_media WHERE project_id = ?");
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll() as $m) {
                $full = UPLOAD_DIR . basename($m['file_path']);
                if (is_file($full)) @unlink($full);
            }
            $db->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
        }
        $message = 'Deleted successfully.';
    }
}

// Stats
$serviceCount = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
$projectCount = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$mediaCount = $db->query("SELECT COUNT(*) FROM project_media")->fetchColumn();

// Find duplicates
function getNormalized($name) {
    return preg_replace('/\s+/', ' ', mb_strtolower(trim($name)));
}

function getDuplicates($db, $table, $type) {
    $rows = $db->query("SELECT id, name, created_at FROM $table")->fetchAll();
    $groups = [];
    foreach ($rows as $row) {
        $norm = getNormalized($row['name']);
        if ($norm === '') continue;
        $groups[$norm][] = $row;
    }

    $approved = $db->query("SELECT normalized_name FROM ignored_duplicates WHERE type = '$type'")->fetchAll(PDO::FETCH_COLUMN);
    $approved = array_flip($approved);
    $held = $_SESSION['held_duplicates'][$type] ?? [];

    $result = [];
    foreach ($groups as $norm => $items) {
        if (count($items) < 2) continue;
        if (isset($approved[$norm]) || isset($held[$norm])) continue;
        // Sort by created_at ascending so oldest is #1
        usort($items, function($a, $b) {
            return strcmp($a['created_at'], $b['created_at']);
        });
        $result[$norm] = $items;
    }
    return $result;
}

$duplicateServices = getDuplicates($db, 'services', 'service');
$duplicateProjects = getDuplicates($db, 'projects', 'project');

$dupServiceCount = count($duplicateServices);
$dupProjectCount = count($duplicateProjects);

$view = $_GET['view'] ?? '';

function formatDate($dt) {
    if (empty($dt)) return '—';
    return date('d M Y', strtotime($dt));
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard | A.R. Admin</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { background: #f7f5fc; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #19152c; line-height: 1.5; }
    :root {
      --primary: #5422c8; --primary-light: #7c4dff; --primary-soft: #ede7ff;
      --surface: #ffffff; --border: #e8e2f4; --text-muted: #716c81;
      --shadow-sm: 0 4px 12px rgba(47, 23, 104, 0.04); --shadow-md: 0 8px 24px rgba(47, 23, 104, 0.08);
      --radius-lg: 20px; --radius-md: 14px; --danger: #c0392b;
    }
    .admin-topbar { background: rgba(255,255,255,0.92); border-bottom: 1px solid var(--border); padding: 18px 32px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; backdrop-filter: blur(8px); }
    .admin-topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .eyebrow { display: block; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: var(--primary); margin-bottom: 2px; }
    .user-info { display: flex; align-items: center; gap: 20px; }
    .welcome-text { font-size: 14px; color: var(--text-muted); }
    .welcome-text strong { color: #19152c; }
    .button { display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; border-radius: 10px; font-weight: 600; font-size: 13px; text-decoration: none; transition: all 0.2s ease; border: none; cursor: pointer; }
    .button-light { background: #f7f5fc; color: #19152c; border: 1px solid var(--border); }
    .button-light:hover { background: #ede7ff; }
    .button-primary { background: var(--primary); color: #fff; }
    .button-danger { background: var(--danger); color: #fff; }
    .admin-container { max-width: 1120px; margin: 40px auto 48px; padding: 0 32px; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 40px; }
    .stat-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px 20px; text-align: center; box-shadow: var(--shadow-sm); position: relative; }
    .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--primary), var(--primary-light)); }
    .stat-card .num { font-size: 36px; font-weight: 800; color: var(--primary); margin: 0 0 6px; }
    .stat-card .num a { color: var(--primary); text-decoration: none; }
    .stat-card p { margin: 0; color: var(--text-muted); font-size: 13px; font-weight: 600; text-transform: uppercase; }
    .quick-actions { display: flex; gap: 14px; flex-wrap: wrap; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 20px 24px; margin-bottom: 28px; }
    .dup-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 24px; }
    .dup-group { border: 1px solid #eee8f7; border-radius: 12px; padding: 16px; margin-bottom: 16px; background: #faf8ff; }
    .dup-group h3 { margin: 0 0 12px; font-size: 16px; }
    .dup-item { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f0edf5; flex-wrap: wrap; }
    .dup-item:last-child { border-bottom: none; }
    .dup-meta { font-size: 14px; }
    .dup-meta .num { font-weight: 700; color: var(--primary); margin-right: 6px; }
    .dup-meta .date { color: var(--text-muted); font-size: 13px; margin-left: 8px; }
    .dup-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .alert { border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; }
    .alert-success { background: #e6f7ee; color: #1e8e5a; }
    @media (max-width: 900px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .admin-topbar { flex-direction: column; align-items: flex-start; gap: 12px; }
    }
  </style>
</head>
<body>
  <div class="admin-topbar">
    <div>
      <span class="eyebrow">A.R. Waterproofing</span>
      <h1>Admin Dashboard</h1>
    </div>
    <div class="user-info">
      <span class="welcome-text">Welcome, <strong><?php echo sanitize($_SESSION['admin_username'] ?? 'Admin'); ?></strong></span>
      <a href="logout.php" class="button button-light">Logout</a>
    </div>
  </div>

  <div class="admin-container">
    <?php if ($message): ?>
      <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="num"><?php echo $serviceCount; ?></div>
        <p>Services</p>
      </div>
      <div class="stat-card">
        <div class="num"><?php echo $projectCount; ?></div>
        <p>Projects</p>
      </div>
      <div class="stat-card">
        <div class="num"><?php echo $mediaCount; ?></div>
        <p>Media Files</p>
      </div>
      <div class="stat-card">
        <div class="num"><a href="?view=services"><?php echo $dupServiceCount; ?></a></div>
        <p>Duplicate Services</p>
      </div>
      <div class="stat-card">
        <div class="num"><a href="?view=projects"><?php echo $dupProjectCount; ?></a></div>
        <p>Duplicate Projects</p>
      </div>
      <div class="stat-card">
        <div class="num"><a href="../services.php" target="_blank">View</a></div>
        <p>Public Site</p>
      </div>
    </div>

    <div class="quick-actions">
      <a href="services.php" class="button button-primary">Manage Services</a>
      <a href="projects.php" class="button button-primary">Manage Projects</a>
      <a href="../index.php" target="_blank" class="button button-light">Open Homepage</a>
    </div>

    <?php if ($view === 'services' && $dupServiceCount > 0): ?>
      <div class="dup-section">
        <h2 style="margin-bottom:16px;">Duplicate Services</h2>
        <?php foreach ($duplicateServices as $norm => $items): ?>
          <div class="dup-group">
            <h3><?php echo sanitize($items[0]['name']); ?> <small style="color:#716c81;">(<?php echo count($items); ?> copies)</small></h3>
            <?php foreach ($items as $i => $item): ?>
              <div class="dup-item">
                <div class="dup-meta">
                  <span class="num"><?php echo $i + 1; ?>.</span>
                  <?php echo sanitize($item['name']); ?>
                  <span class="date">Posted: <?php echo formatDate($item['created_at']); ?></span>
                </div>
                <div class="dup-actions">
                  <a class="button button-light" href="../service.php?id=<?php echo $item['id']; ?>" target="_blank">View</a>
                  <a class="button button-light" href="?action=approve&type=service&name=<?php echo urlencode($norm); ?>" onclick="return confirm('Approve and hide this group forever?')">Approve</a>
                  <a class="button button-light" href="?action=hold&type=service&name=<?php echo urlencode($norm); ?>">Hold</a>
                  <a class="button button-danger" href="?action=delete&type=service&id=<?php echo $item['id']; ?>&name=<?php echo urlencode($norm); ?>" onclick="return confirm('Delete this service permanently?')">Delete</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($view === 'projects' && $dupProjectCount > 0): ?>
      <div class="dup-section">
        <h2 style="margin-bottom:16px;">Duplicate Projects</h2>
        <?php foreach ($duplicateProjects as $norm => $items): ?>
          <div class="dup-group">
            <h3><?php echo sanitize($items[0]['name']); ?> <small style="color:#716c81;">(<?php echo count($items); ?> copies)</small></h3>
            <?php foreach ($items as $i => $item): ?>
              <div class="dup-item">
                <div class="dup-meta">
                  <span class="num"><?php echo $i + 1; ?>.</span>
                  <?php echo sanitize($item['name']); ?>
                  <span class="date">Posted: <?php echo formatDate($item['created_at']); ?></span>
                </div>
                <div class="dup-actions">
                  <a class="button button-light" href="../project.php?id=<?php echo $item['id']; ?>" target="_blank">View</a>
                  <a class="button button-light" href="?action=approve&type=project&name=<?php echo urlencode($norm); ?>" onclick="return confirm('Approve and hide this group forever?')">Approve</a>
                  <a class="button button-light" href="?action=hold&type=project&name=<?php echo urlencode($norm); ?>">Hold</a>
                  <a class="button button-danger" href="?action=delete&type=project&id=<?php echo $item['id']; ?>&name=<?php echo urlencode($norm); ?>" onclick="return confirm('Delete this project permanently?')">Delete</a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($view && (($view === 'services' && $dupServiceCount === 0) || ($view === 'projects' && $dupProjectCount === 0))): ?>
      <div class="dup-section">
        <p>No duplicates found (or all have been approved/held).</p>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>