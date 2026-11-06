<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$db = getDB();
$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $db->prepare("SELECT image_path FROM services WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $row = $stmt->fetch();
    if ($row && !empty($row['image_path'])) {
        $fullPath = UPLOAD_DIR . basename($row['image_path']);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([(int)$_GET['delete']]);
    $message = 'Service deleted successfully.';
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $about = trim($_POST['about'] ?? '');
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    // Collect benefits (max 8) and process (max 9)
    $benefits = array_filter(array_map('trim', $_POST['benefits'] ?? []));
    $benefits = array_slice($benefits, 0, 8);
    $key_benefits = implode("\n", $benefits);

    $processSteps = array_filter(array_map('trim', $_POST['process'] ?? []));
    $processSteps = array_slice($processSteps, 0, 9);
    $process = implode("\n", $processSteps);

    if (!$name) {
        $error = 'Service name is required.';
    } else {
        // Normalize name for duplicate check (lowercase + single spaces)
        $normalizedName = preg_replace('/\s+/', ' ', mb_strtolower(trim($name)));

        // Check for duplicate only when adding a new service
        if ($id == 0) {
            $allServices = $db->query("SELECT id, name FROM services")->fetchAll();
            foreach ($allServices as $existing) {
                $existingNormalized = preg_replace('/\s+/', ' ', mb_strtolower(trim($existing['name'])));
                if ($existingNormalized === $normalizedName) {
                    $error = 'This service already exists. Please use a different name.';
                    break;
                }
            }
        }

        if (!$error) {
            // Existing image path (for updates)
            $image_path = null;
            if ($id > 0) {
                $stmt = $db->prepare("SELECT image_path FROM services WHERE id = ?");
                $stmt->execute([$id]);
                $existing = $stmt->fetch();
                $image_path = $existing['image_path'] ?? null;
            }

            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $maxSize = 1.5 * 1024 * 1024; // 1.5 MB

                if ($file['size'] > $maxSize) {
                    $error = 'Image must be under 1.5 MB.';
                } else {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if (!in_array($mime, $allowedTypes)) {
                        $error = 'Only JPG, PNG, WebP or GIF images are allowed.';
                    } else {
                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                            $ext = 'jpg';
                        }

                        $filename = 'service_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                        $dest = UPLOAD_DIR . $filename;

                        if (move_uploaded_file($file['tmp_name'], $dest)) {
                            // Remove old file if replacing
                            if (!empty($image_path)) {
                                $oldFull = UPLOAD_DIR . basename($image_path);
                                if (is_file($oldFull)) {
                                    @unlink($oldFull);
                                }
                            }
                            $image_path = 'uploads/' . $filename;
                        } else {
                            $error = 'Failed to upload image.';
                        }
                    }
                }
            }

            if (!$error) {
                if ($id > 0) {
                    $stmt = $db->prepare("UPDATE services SET name=?, about=?, key_benefits=?, process=?, image_path=? WHERE id=?");
                    $stmt->execute([$name, $about, $key_benefits, $process, $image_path, $id]);
                    $message = 'Service updated successfully.';
                } else {
                    $stmt = $db->prepare("INSERT INTO services (name, about, key_benefits, process, image_path) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $about, $key_benefits, $process, $image_path]);
                    $message = 'Service added successfully.';
                }
            }
        }
    }
}

$editService = null;
$existingBenefits = [];
$existingProcess = [];
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editService = $stmt->fetch();
    if ($editService) {
        $existingBenefits = array_filter(array_map('trim', explode("\n", $editService['key_benefits'] ?? '')));
        $existingProcess = array_filter(array_map('trim', explode("\n", $editService['process'] ?? '')));
    }
}

$services = $db->query("SELECT * FROM services ORDER BY name ASC")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage Services | A.R. Admin</title>
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
      max-width: 1120px;
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
    /* ----- LAYOUT ----- */
    .admin-grid {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
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
    .form-group input[type="file"] {
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
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(84, 34, 200, 0.1);
      background: #fff;
    }
    .form-group textarea {
      resize: vertical;
      min-height: 100px;
    }
    .form-group input[type="file"] {
      padding: 10px 14px;
      cursor: pointer;
      background: #fff;
    }
    .form-group input[type="file"]::file-selector-button {
      background: var(--primary-soft);
      color: var(--primary);
      border: none;
      border-radius: 8px;
      padding: 8px 14px;
      font-weight: 600;
      font-size: 13px;
      margin-right: 12px;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    .form-group input[type="file"]::file-selector-button:hover {
      background: #dcd0ff;
    }
    .preferred-size {
      display: block;
      margin-top: 8px;
      font-size: 13px;
      color: var(--text-muted);
    }
    .current-image {
      margin-top: 12px;
      max-width: 240px;
      border-radius: 12px;
      border: 1.5px solid var(--border);
      box-shadow: var(--shadow-sm);
    }
    /* ----- DYNAMIC LIST ----- */
    .dynamic-list {
      margin-top: 4px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .dynamic-row {
      display: flex;
      gap: 8px;
      align-items: center;
      animation: slideIn 0.2s ease;
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateY(-4px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .dynamic-row input {
      flex: 1;
    }
    .btn-remove {
      background: #fdecea;
      color: var(--danger);
      border: 1.5px solid #f5c6c0;
      border-radius: 10px;
      width: 38px;
      height: 38px;
      cursor: pointer;
      font-size: 18px;
      line-height: 1;
      flex-shrink: 0;
      transition: all 0.15s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }
    .btn-remove:hover {
      background: var(--danger);
      color: #fff;
      border-color: var(--danger);
      transform: scale(1.05);
    }
    .btn-add {
      background: var(--primary-soft);
      color: var(--primary);
      border: 1.5px dashed #c8b8f0;
      border-radius: 10px;
      padding: 9px 16px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      margin-top: 10px;
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-add:hover:not(:disabled) {
      background: var(--primary);
      color: #fff;
      border-color: var(--primary);
    }
    .btn-add:disabled {
      background: #f0edf5;
      color: #b0a8c0;
      border-color: #e0d8f0;
      cursor: not-allowed;
    }
    .limit-note {
      font-size: 12px;
      color: var(--text-muted);
      margin-top: 6px;
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
    .alert::before {
      font-size: 18px;
      font-weight: 700;
    }
    .alert-success::before {
      content: '✓';
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
    }
    /* ----- FOCUS ----- */
    .button:focus-visible,
    .btn-add:focus-visible,
    .btn-remove:focus-visible,
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
      <h1>Manage Services</h1>
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
        <h2><?php echo $editService ? 'Edit Service' : 'Add New Service'; ?></h2>
        <form method="POST" enctype="multipart/form-data" id="serviceForm">
          <?php if ($editService): ?>
            <input type="hidden" name="id" value="<?php echo $editService['id']; ?>">
          <?php endif; ?>

          <div class="form-group">
            <label for="name">Service Name <span style="color:var(--danger);">*</span></label>
            <input type="text" id="name" name="name" required value="<?php echo sanitize($editService['name'] ?? ''); ?>" placeholder="e.g. Terrace Waterproofing">
          </div>

          <div class="form-group">
            <label for="about">About the Service</label>
            <textarea id="about" name="about" rows="4" placeholder="Describe what this service offers..."><?php echo sanitize($editService['about'] ?? ''); ?></textarea>
          </div>

          <!-- Key Benefits -->
          <div class="form-group">
            <label>Key Benefits <span style="font-weight:400; color:var(--text-muted); font-size:13px;">(max 8)</span></label>
            <div class="dynamic-list" id="benefitsList">
              <?php
              $benefitRows = $existingBenefits ?: [''];
              foreach ($benefitRows as $i => $b):
              ?>
                <div class="dynamic-row">
                  <input type="text" name="benefits[]" value="<?php echo sanitize($b); ?>" placeholder="Benefit <?php echo $i + 1; ?>">
                  <button type="button" class="btn-remove" onclick="removeRow(this, 'benefits')" aria-label="Remove benefit">×</button>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add" id="addBenefit" onclick="addRow('benefits', 8)">+ Add Benefit</button>
            <div class="limit-note">You can add up to 8 benefits.</div>
          </div>

          <!-- Our Process -->
          <div class="form-group">
            <label>Our Process <span style="font-weight:400; color:var(--text-muted); font-size:13px;">(max 9)</span></label>
            <div class="dynamic-list" id="processList">
              <?php
              $processRows = $existingProcess ?: [''];
              foreach ($processRows as $i => $p):
              ?>
                <div class="dynamic-row">
                  <input type="text" name="process[]" value="<?php echo sanitize($p); ?>" placeholder="Step <?php echo $i + 1; ?>">
                  <button type="button" class="btn-remove" onclick="removeRow(this, 'process')" aria-label="Remove step">×</button>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="btn-add" id="addProcess" onclick="addRow('process', 9)">+ Add Step</button>
            <div class="limit-note">You can add up to 9 process steps.</div>
          </div>

          <div class="form-group">
            <label for="image">Service Image</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
            <span class="preferred-size">Preferred size: 1200 × 800 px. Maximum file size: 1.5 MB.</span>

            <?php if ($editService && !empty($editService['image_path'])): ?>
              <div style="margin-top:14px;">
                <strong style="font-size:13px; color:#19152c; display:block; margin-bottom:6px;">Current image:</strong>
                <img src="<?php echo htmlspecialchars(getPublicMediaUrl($editService['image_path'])); ?>" alt="Current service image" class="current-image">
              </div>
            <?php endif; ?>
          </div>

          <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-top:24px;">
            <button type="submit" class="button button-primary"><?php echo $editService ? 'Update Service' : 'Add Service'; ?></button>
            <?php if ($editService): ?>
              <a href="services.php" class="button button-light">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <!-- LIST CARD -->
      <div class="card">
        <h2>Existing Services</h2>
        <?php if (empty($services)): ?>
          <div class="empty-state">
            <div class="icon">📋</div>
            <p>No services yet. Add one using the form.</p>
          </div>
        <?php else: ?>
          <div class="table-wrapper">
            <table class="table">
              <thead>
                <tr><th>Name</th><th style="width:150px;">Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($services as $s): ?>
                  <tr>
                    <td style="font-weight:600; color:#19152c;"><?php echo sanitize($s['name']); ?></td>
                    <td>
                      <div class="actions">
                        <a href="?edit=<?php echo $s['id']; ?>" class="edit-link">✎ Edit</a>
                        <a href="?delete=<?php echo $s['id']; ?>" class="delete-link" onclick="return confirm('Delete this service and all its projects?')">✕ Delete</a>
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
  function addRow(type, max) {
    const listId = type === 'benefits' ? 'benefitsList' : 'processList';
    const list = document.getElementById(listId);
    const rows = list.querySelectorAll('.dynamic-row');
    if (rows.length >= max) return;

    const row = document.createElement('div');
    row.className = 'dynamic-row';
    const placeholder = type === 'benefits' ? 'Benefit ' + (rows.length + 1) : 'Step ' + (rows.length + 1);
    row.innerHTML = `
      <input type="text" name="${type}[]" placeholder="${placeholder}">
      <button type="button" class="btn-remove" onclick="removeRow(this, '${type}')" aria-label="Remove">×</button>
    `;
    list.appendChild(row);
    updateAddButtons();
  }

  function removeRow(btn, type) {
    const listId = type === 'benefits' ? 'benefitsList' : 'processList';
    const list = document.getElementById(listId);
    const row = btn.closest('.dynamic-row');
    if (list.querySelectorAll('.dynamic-row').length > 1) {
      row.remove();
    } else {
      row.querySelector('input').value = '';
    }
    updateAddButtons();
  }

  function updateAddButtons() {
    const benefitsCount = document.querySelectorAll('#benefitsList .dynamic-row').length;
    const processCount = document.querySelectorAll('#processList .dynamic-row').length;
    document.getElementById('addBenefit').disabled = benefitsCount >= 8;
    document.getElementById('addProcess').disabled = processCount >= 9;
  }

  // Initial state
  updateAddButtons();
  </script>
</body>
</html>