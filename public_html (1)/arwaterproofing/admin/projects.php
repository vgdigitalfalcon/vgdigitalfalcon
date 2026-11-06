<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$db = getDB();
$message = '';
$error = '';

// Handle delete project
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("SELECT file_path FROM project_media WHERE project_id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    foreach ($stmt->fetchAll() as $media) {
        $fullPath = UPLOAD_DIR . basename($media['file_path']);
        if (file_exists($fullPath)) @unlink($fullPath);
    }
    $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $message = 'Project deleted successfully.';
}

// Handle delete media
if (isset($_GET['delete_media']) && is_numeric($_GET['delete_media'])) {
    $stmt = $db->prepare("SELECT file_path, project_id FROM project_media WHERE id = ?");
    $stmt->execute([(int)$_GET['delete_media']]);
    $media = $stmt->fetch();
    if ($media) {
        $fullPath = UPLOAD_DIR . basename($media['file_path']);
        if (file_exists($fullPath)) @unlink($fullPath);
        $db->prepare("DELETE FROM project_media WHERE id = ?")->execute([(int)$_GET['delete_media']]);
        header('Location: projects.php?edit=' . $media['project_id'] . '&msg=media_deleted');
        exit;
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'media_deleted') {
    $message = 'Media removed successfully.';
}
if (isset($_GET['msg']) && $_GET['msg'] === 'added') {
    $message = 'Project created. You can now add more images/videos below.';
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $service_id = (int)($_POST['service_id'] ?? 0);
    $client_name = trim($_POST['client_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $overview = trim($_POST['overview'] ?? '');
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // Project Month & Year → stored in duration column as YYYY-MM
    $project_month = (int)($_POST['project_month'] ?? 0);
    $project_year  = (int)($_POST['project_year'] ?? 0);
    $duration = ($project_month && $project_year) 
        ? sprintf('%04d-%02d', $project_year, $project_month) 
        : '';

    if (!$name || $service_id <= 0) {
        $error = 'Project name and service are required.';
    } else {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE projects SET service_id=?, name=?, client_name=?, location=?, duration=?, overview=? WHERE id=?");
            $stmt->execute([$service_id, $name, $client_name, $location, $duration, $overview, $id]);
            $projectId = $id;
            $message = 'Project updated successfully.';
        } else {
            $stmt = $db->prepare("INSERT INTO projects (service_id, name, client_name, location, duration, overview) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$service_id, $name, $client_name, $location, $duration, $overview]);
            $projectId = $db->lastInsertId();
            $message = 'Project added successfully.';
        }

        // Handle Before & After uploads
        $phases = ['before', 'after'];
        $maxImageSize = 2 * 1024 * 1024;   // 2 MB
        $maxVideoSize = 3 * 1024 * 1024;   // 3 MB
        $maxVideosPerPhase = 4;

        foreach ($phases as $phase) {
            $field = 'gallery_' . $phase;
            if (empty($_FILES[$field]['name'][0])) continue;

            // Count existing videos for this phase (for edit)
            $existingVideoCount = 0;
            if ($id > 0) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM project_media WHERE project_id = ? AND phase = ? AND media_type = 'video'");
                $stmt->execute([$projectId, $phase]);
                $existingVideoCount = (int)$stmt->fetchColumn();
            }

            $newVideoCount = 0;

            foreach ($_FILES[$field]['tmp_name'] as $key => $tmpName) {
                if ($_FILES[$field]['error'][$key] !== UPLOAD_ERR_OK || empty($tmpName)) continue;

                $originalName = $_FILES[$field]['name'][$key];
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $size = $_FILES[$field]['size'][$key];

                $allowedImage = ['jpg','jpeg','png','webp','gif'];
                $allowedVideo = ['mp4','webm','mov'];

                $type = null;
                if (in_array($ext, $allowedImage)) {
                    $type = 'image';
                    if ($size > $maxImageSize) {
                        $error = "Image in $phase exceeds 2 MB.";
                        continue;
                    }
                } elseif (in_array($ext, $allowedVideo)) {
                    $type = 'video';
                    if ($size > $maxVideoSize) {
                        $error = "Video in $phase exceeds 3 MB.";
                        continue;
                    }
                    if ($existingVideoCount + $newVideoCount >= $maxVideosPerPhase) {
                        $error = "Maximum 4 videos allowed in $phase section.";
                        continue;
                    }
                    $newVideoCount++;
                } else {
                    continue;
                }

                if ($type) {
                    $filename = uniqid('media_') . '.' . $ext;
                    $dest = UPLOAD_DIR . $filename;
                    if (move_uploaded_file($tmpName, $dest)) {
                        $filePath = 'uploads/' . $filename;
                        $stmt = $db->prepare("INSERT INTO project_media (project_id, media_type, phase, file_path) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$projectId, $type, $phase, $filePath]);
                    }
                }
            }
        }

        if ($id == 0 && !$error) {
            header('Location: projects.php?edit=' . $projectId . '&msg=added');
            exit;
        }
    }
}

$editProject = null;
$beforeMedia = [];
$afterMedia = [];
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editProject = $stmt->fetch();
    if ($editProject) {
        $stmt = $db->prepare("SELECT * FROM project_media WHERE project_id = ? ORDER BY phase, sort_order, id");
        $stmt->execute([$editProject['id']]);
        foreach ($stmt->fetchAll() as $m) {
            if ($m['phase'] === 'before') $beforeMedia[] = $m;
            else $afterMedia[] = $m;
        }
    }
}

$projects = $db->query("
  SELECT p.*, s.name as service_name 
  FROM projects p 
  LEFT JOIN services s ON p.service_id = s.id 
  ORDER BY p.created_at DESC
")->fetchAll();

$services = $db->query("SELECT id, name FROM services ORDER BY name")->fetchAll();

// For month/year dropdowns
$months = [
    1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
    7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'
];
$currentYear = (int)date('Y');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Projects | A.R. Admin</title>
  <link rel="stylesheet" href="../assets/styles.css">
  <style>
    /* ----- RESET & GLOBALS ----- */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      background: #f7f5fc;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      color: #19152c;
      line-height: 1.5;
    }
    :root {
      --primary: #5422c8;
      --primary-light: #7c4dff;
      --primary-soft: #ede7ff;
      --surface: #ffffff;
      --border: #e8e2f4;
      --text-muted: #716c81;
      --shadow-sm: 0 4px 12px rgba(47, 23, 104, 0.04);
      --shadow-md: 0 8px 24px rgba(47, 23, 104, 0.08);
      --radius-lg: 20px;
      --radius-md: 14px;
      --danger: #c0392b;
      --success: #1e8e5a;
    }

    /* ----- TOP BAR ----- */
    .admin-topbar {
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      padding: 18px 32px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 10;
      backdrop-filter: blur(8px);
      background: rgba(255, 255, 255, 0.92);
    }
    .admin-topbar h1 {
      font-size: 22px;
      font-weight: 700;
      color: #19152c;
      letter-spacing: -0.3px;
      margin: 0;
    }
    .eyebrow {
      display: block;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: var(--primary);
      margin-bottom: 2px;
      opacity: 0.9;
    }
    .admin-container {
      max-width: 1180px;
      margin: 40px auto 48px;
      padding: 0 32px;
    }

    /* ----- BUTTONS ----- */
    .button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 22px;
      border-radius: 12px;
      font-weight: 600;
      font-size: 14px;
      text-decoration: none;
      transition: all 0.2s ease;
      border: none;
      cursor: pointer;
      font-family: inherit;
    }
    .button-light {
      background: #f7f5fc;
      color: #19152c;
      border: 1px solid var(--border);
    }
    .button-light:hover {
      background: var(--primary-soft);
      border-color: #d4c8f0;
      transform: translateY(-1px);
    }
    .button-primary {
      background: var(--primary);
      color: #fff;
      box-shadow: 0 4px 12px rgba(84, 34, 200, 0.25);
    }
    .button-primary:hover {
      background: #4a1cb8;
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(84, 34, 200, 0.35);
    }
    .button-sm {
      padding: 8px 16px;
      font-size: 13px;
      border-radius: 10px;
    }

    /* ----- LAYOUT ----- */
    .admin-grid {
      display: grid;
      grid-template-columns: 1.15fr 0.85fr;
      gap: 28px;
      align-items: start;
    }
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 28px 26px;
      box-shadow: var(--shadow-sm);
    }
    .card h2 {
      font-size: 20px;
      font-weight: 700;
      color: #19152c;
      margin-bottom: 22px;
      letter-spacing: -0.3px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .card h2::before {
      content: '';
      width: 6px;
      height: 22px;
      background: linear-gradient(180deg, var(--primary), var(--primary-light));
      border-radius: 4px;
      display: inline-block;
    }

    /* ----- FORM ----- */
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      font-weight: 600;
      color: #19152c;
      font-size: 14px;
      display: block;
      margin-bottom: 8px;
    }
    .form-group input[type="text"],
    .form-group textarea,
    .form-group select {
      border: 1.5px solid var(--border);
      border-radius: 12px;
      padding: 12px 14px;
      font-size: 14px;
      width: 100%;
      box-sizing: border-box;
      background: #fdfcff;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
      font-family: inherit;
      color: #19152c;
    }
    .form-group input[type="text"]:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(84, 34, 200, 0.1);
      background: #fff;
    }
    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }
    .form-group select {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23716c81' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 14px center;
      padding-right: 36px;
      cursor: pointer;
    }
    .form-group small {
      font-size: 12px;
      color: var(--text-muted);
      margin-top: 6px;
      display: block;
    }

    /* ----- MONTH/YEAR ROW ----- */
    .month-year {
      display: flex;
      gap: 12px;
    }
    .month-year select {
      flex: 1;
    }

    /* ----- MEDIA SECTIONS ----- */
    .media-section {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1.5px dashed var(--border);
    }
    .media-section h3 {
      font-size: 16px;
      font-weight: 700;
      margin: 0 0 12px;
      color: #19152c;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .media-section h3 .badge {
      font-size: 11px;
      font-weight: 600;
      background: var(--primary-soft);
      color: var(--primary);
      padding: 3px 10px;
      border-radius: 20px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .media-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-top: 12px;
    }

    /* ----- DYNAMIC MEDIA INPUTS ----- */
    .media-input-row {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 10px;
      background: #f7f5fc;
      padding: 10px 14px;
      border-radius: 12px;
      border: 1px solid var(--border);
      animation: slideIn 0.2s ease;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-4px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .media-input-row .type-label {
      font-size: 12px;
      font-weight: 700;
      color: var(--primary);
      min-width: 52px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .media-input-row input[type="file"] {
      flex: 1;
      font-size: 13px;
      border: none;
      background: transparent;
      padding: 0;
    }
    .media-input-row input[type="file"]::file-selector-button {
      background: #fff;
      color: var(--primary);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 6px 12px;
      font-weight: 600;
      font-size: 12px;
      margin-right: 10px;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    .media-input-row input[type="file"]::file-selector-button:hover {
      background: var(--primary-soft);
    }
    .media-remove-btn {
      background: #fdecea;
      color: var(--danger);
      border: 1.5px solid #f5c6c0;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 16px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      transition: all 0.15s ease;
    }
    .media-remove-btn:hover {
      background: var(--danger);
      color: #fff;
      border-color: var(--danger);
    }

    /* ----- MEDIA GALLERY ----- */
    .media-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 14px;
    }
    .media-item {
      position: relative;
      width: 112px;
      height: 92px;
      border-radius: 12px;
      overflow: hidden;
      border: 1.5px solid var(--border);
      background: #f7f5fc;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .media-item:hover {
      transform: scale(1.04);
      box-shadow: var(--shadow-md);
    }
    .media-item img,
    .media-item .video-thumb {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .media-item .video-thumb {
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #1a1230, #2d1f52);
      color: #fff;
      font-size: 30px;
    }
    .media-remove {
      position: absolute;
      top: 5px;
      right: 5px;
      width: 24px;
      height: 24px;
      background: rgba(192, 57, 43, 0.92);
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      text-decoration: none;
      font-weight: 700;
      transition: all 0.15s ease;
      backdrop-filter: blur(4px);
    }
    .media-remove:hover {
      background: #a93226;
      transform: scale(1.1);
    }

    /* ----- TABLE ----- */
    .table-wrapper {
      overflow-x: auto;
      border-radius: var(--radius-md);
      border: 1px solid var(--border);
    }
    .table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
    }
    .table th {
      background: #f7f5fc;
      color: #19152c;
      font-weight: 700;
      text-align: left;
      padding: 14px 16px;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      border-bottom: 1px solid var(--border);
    }
    .table td {
      padding: 14px 16px;
      border-bottom: 1px solid #f0edf5;
      vertical-align: middle;
    }
    .table tr:last-child td {
      border-bottom: none;
    }
    .table tbody tr {
      transition: background 0.15s ease;
    }
    .table tbody tr:hover {
      background: #faf8ff;
    }
    .table .actions {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }
    .table .actions a {
      font-weight: 600;
      text-decoration: none;
      padding: 6px 14px;
      border-radius: 8px;
      font-size: 13px;
      transition: all 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }
    .table .actions .edit-link {
      color: var(--primary);
      background: var(--primary-soft);
    }
    .table .actions .edit-link:hover {
      background: var(--primary);
      color: #fff;
    }
    .table .actions .delete-link {
      color: var(--danger);
      background: #fdecea;
    }
    .table .actions .delete-link:hover {
      background: var(--danger);
      color: #fff;
    }

    /* ----- ALERTS ----- */
    .alert {
      border-radius: 12px;
      padding: 14px 18px;
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: slideIn 0.3s ease;
    }
    .alert-success {
      background: #e6f7ee;
      color: var(--success);
      border: 1px solid #b8e6cc;
    }
    .alert-error {
      background: #fdecea;
      color: var(--danger);
      border: 1px solid #f5c6c0;
    }
    .alert-success::before {
      content: '✓';
      font-weight: 700;
      font-size: 16px;
    }
    .alert-error::before {
      content: '!';
      background: var(--danger);
      color: #fff;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 13px;
      font-weight: 700;
    }

    /* ----- EMPTY STATE ----- */
    .empty-state {
      text-align: center;
      padding: 32px 16px;
      color: var(--text-muted);
      font-size: 14px;
    }
    .empty-state .icon {
      font-size: 36px;
      margin-bottom: 8px;
      opacity: 0.5;
    }

    /* ----- RESPONSIVE ----- */
    @media (max-width: 980px) {
      .admin-grid {
        grid-template-columns: 1fr;
      }
    }
    @media (max-width: 700px) {
      .admin-topbar {
        padding: 16px 20px;
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }
      .admin-container {
        padding: 0 20px;
        margin: 28px auto 32px;
      }
      .card {
        padding: 22px 18px;
      }
      .table th, .table td {
        padding: 12px;
      }
      .month-year {
        flex-direction: column;
      }
    }

    /* ----- FOCUS ----- */
    .button:focus-visible,
    .media-remove-btn:focus-visible,
    .table .actions a:focus-visible {
      outline: 2px solid var(--primary);
      outline-offset: 2px;
    }
  </style>
</head>
<body>
  <!-- TOP BAR -->
  <div class="admin-topbar">
    <div>
      <span class="eyebrow">Admin Panel</span>
      <h1>Manage Projects</h1>
    </div>
    <a href="dashboard.php" class="button button-light">← Back to Dashboard</a>
  </div>

  <!-- MAIN -->
  <div class="admin-container">
    <!-- ALERTS -->
    <?php if ($message): ?>
      <div class="alert alert-success"><?php echo sanitize($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo sanitize($error); ?></div>
    <?php endif; ?>

    <div class="admin-grid">
      <!-- FORM CARD -->
      <div class="card">
        <h2><?php echo $editProject ? 'Edit Project' : 'Add New Project'; ?></h2>
        <form method="POST" enctype="multipart/form-data">
          <?php if ($editProject): ?>
            <input type="hidden" name="id" value="<?php echo $editProject['id']; ?>">
          <?php endif; ?>

          <div class="form-group">
            <label for="name">Project Name <span style="color:var(--danger);">*</span></label>
            <input type="text" id="name" name="name" required value="<?php echo sanitize($editProject['name'] ?? ''); ?>" placeholder="e.g. Sunrise Apartments Terrace">
          </div>

          <div class="form-group">
            <label for="service_id">Service <span style="color:var(--danger);">*</span></label>
            <select id="service_id" name="service_id" required>
              <option value="">-- Select Service --</option>
              <?php foreach ($services as $s): ?>
                <option value="<?php echo $s['id']; ?>" <?php echo (isset($editProject['service_id']) && $editProject['service_id'] == $s['id']) ? 'selected' : ''; ?>>
                  <?php echo sanitize($s['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="client_name">Client Name</label>
            <input type="text" id="client_name" name="client_name" value="<?php echo sanitize($editProject['client_name'] ?? ''); ?>" placeholder="e.g. Mr. Sharma">
          </div>

          <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" value="<?php echo sanitize($editProject['location'] ?? ''); ?>" placeholder="e.g. Andheri, Mumbai">
          </div>

          <div class="form-group">
            <label>Project Month & Year</label>
            <div class="month-year">
              <?php
              $savedMonth = 0;
              $savedYear = 0;
              if (!empty($editProject['duration']) && preg_match('/^(\d{4})-(\d{2})$/', $editProject['duration'], $m)) {
                  $savedYear = (int)$m[1];
                  $savedMonth = (int)$m[2];
              }
              ?>
              <select name="project_month">
                <option value="">Month</option>
                <?php foreach ($months as $num => $name): ?>
                  <option value="<?php echo $num; ?>" <?php echo $savedMonth == $num ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
              </select>
              <select name="project_year">
                <option value="">Year</option>
                <?php for ($y = $currentYear + 1; $y >= $currentYear - 15; $y--): ?>
                  <option value="<?php echo $y; ?>" <?php echo $savedYear == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <small>When this project was completed.</small>
          </div>

          <div class="form-group">
            <label for="overview">Project Overview</label>
            <textarea id="overview" name="overview" rows="4" placeholder="Brief description of the project..."><?php echo sanitize($editProject['overview'] ?? ''); ?></textarea>
          </div>

          <!-- BEFORE Gallery -->
          <div class="media-section">
            <h3>Before <span class="badge">Images &amp; Videos</span></h3>
            <div id="beforeInputs"></div>
            <div class="media-actions">
              <button type="button" class="button button-light button-sm" onclick="addMediaInput('before','image')">+ Add Image</button>
              <button type="button" class="button button-light button-sm" id="addBeforeVideo" onclick="addMediaInput('before','video')">+ Add Video</button>
            </div>
            <small style="color:var(--text-muted); display:block; margin-top:8px;">
              Images max 2 MB. Videos max 3 MB (max 4 videos).
            </small>

            <?php if (!empty($beforeMedia)): ?>
              <div class="media-grid">
                <?php foreach ($beforeMedia as $m): ?>
                  <div class="media-item">
                    <?php if ($m['media_type'] === 'image'): ?>
                      <img src="<?php echo htmlspecialchars(getPublicMediaUrl($m['file_path'])); ?>" alt="">
                    <?php else: ?>
                      <div class="video-thumb">▶</div>
                    <?php endif; ?>
                    <a href="?edit=<?php echo $editProject['id']; ?>&delete_media=<?php echo $m['id']; ?>" class="media-remove" onclick="return confirm('Remove this media?')" aria-label="Remove media">×</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- AFTER Gallery -->
          <div class="media-section">
            <h3>After <span class="badge">Images &amp; Videos</span></h3>
            <div id="afterInputs"></div>
            <div class="media-actions">
              <button type="button" class="button button-light button-sm" onclick="addMediaInput('after','image')">+ Add Image</button>
              <button type="button" class="button button-light button-sm" id="addAfterVideo" onclick="addMediaInput('after','video')">+ Add Video</button>
            </div>
            <small style="color:var(--text-muted); display:block; margin-top:8px;">
              Images max 2 MB. Videos max 3 MB (max 4 videos).
            </small>

            <?php if (!empty($afterMedia)): ?>
              <div class="media-grid">
                <?php foreach ($afterMedia as $m): ?>
                  <div class="media-item">
                    <?php if ($m['media_type'] === 'image'): ?>
                      <img src="<?php echo htmlspecialchars(getPublicMediaUrl($m['file_path'])); ?>" alt="">
                    <?php else: ?>
                      <div class="video-thumb">▶</div>
                    <?php endif; ?>
                    <a href="?edit=<?php echo $editProject['id']; ?>&delete_media=<?php echo $m['id']; ?>" class="media-remove" onclick="return confirm('Remove this media?')" aria-label="Remove media">×</a>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-top:26px;">
            <button type="submit" class="button button-primary"><?php echo $editProject ? 'Update Project' : 'Add Project'; ?></button>
            <?php if ($editProject): ?>
              <a href="projects.php" class="button button-light">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- LIST CARD -->
      <div class="card">
        <h2>Existing Projects</h2>
        <?php if (empty($projects)): ?>
          <div class="empty-state">
            <div class="icon">📁</div>
            <p>No projects yet. Add one using the form.</p>
          </div>
        <?php else: ?>
          <div class="table-wrapper">
            <table class="table">
              <thead>
                <tr><th>Name</th><th>Service</th><th style="width:150px;">Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($projects as $p): ?>
                  <tr>
                    <td style="font-weight:600; color:#19152c;"><?php echo sanitize($p['name']); ?></td>
                    <td style="color:var(--text-muted); font-size:13px;"><?php echo sanitize($p['service_name'] ?? '-'); ?></td>
                    <td>
                      <div class="actions">
                        <a href="?edit=<?php echo $p['id']; ?>" class="edit-link">✎ Edit</a>
                        <a href="?delete=<?php echo $p['id']; ?>" class="delete-link" onclick="return confirm('Delete this project and all its media?')">✕ Delete</a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
  let beforeVideoCount = <?php echo count(array_filter($beforeMedia, fn($m) => $m['media_type']==='video')); ?>;
  let afterVideoCount  = <?php echo count(array_filter($afterMedia, fn($m) => $m['media_type']==='video')); ?>;

  function addMediaInput(phase, type) {
    if (type === 'video') {
      const current = phase === 'before' ? beforeVideoCount : afterVideoCount;
      if (current >= 4) {
        alert('Maximum 4 videos allowed in ' + phase + ' section.');
        return;
      }
      if (phase === 'before') beforeVideoCount++;
      else afterVideoCount++;
    }

    const container = document.getElementById(phase + 'Inputs');
    const wrapper = document.createElement('div');
    wrapper.className = 'media-input-row';

    const label = document.createElement('span');
    label.className = 'type-label';
    label.textContent = (type === 'image' ? 'Image' : 'Video');

    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'gallery_' + phase + '[]';
        input.accept = type === 'image' ? 'image/*' : 'video/*';

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'media-remove-btn';
    removeBtn.textContent = '×';
    removeBtn.setAttribute('aria-label', 'Remove');
    removeBtn.onclick = function() {
      if (type === 'video') {
        if (phase === 'before') beforeVideoCount--;
        else afterVideoCount--;
      }
      wrapper.remove();
    };

    wrapper.appendChild(label);
    wrapper.appendChild(input);
    wrapper.appendChild(removeBtn);
    container.appendChild(wrapper);
  }
  </script>
</body>
</html>