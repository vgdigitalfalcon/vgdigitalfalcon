<?php
require_once 'includes/config.php';
require_once 'includes/auth_check.php';

$pageTitle = 'Edit Member';
$error = '';

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    header('Location: dashboard.php?msg=' . urlencode('Member not found.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $club_name = trim($_POST['club_name'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $member_page = $_POST['member_page'] ?? 'RMB Chennai Central Members';

    if ($name === '') {
        $error = 'Name is required.';
    } else {
        $picture_filename = $member['profile_picture'];

        if (!empty($_FILES['profile_picture']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, or WEBP images are allowed.';
            } elseif ($_FILES['profile_picture']['size'] > 3 * 1024 * 1024) {
                $error = 'Profile picture must be smaller than 3MB.';
            } else {
                $new_filename = 'member_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], UPLOAD_DIR . $new_filename)) {
                    // Remove old picture
                    if ($picture_filename && file_exists(UPLOAD_DIR . $picture_filename)) {
                        unlink(UPLOAD_DIR . $picture_filename);
                    }
                    $picture_filename = $new_filename;
                }
            }
        }

        if ($error === '') {
            $stmt = $conn->prepare("UPDATE members SET profile_picture=?, name=?, position=?, company_name=?, website=?, club_name=?, linkedin=?, phone=?, member_page=? WHERE id=?");
            $stmt->bind_param('sssssssssi', $picture_filename, $name, $position, $company_name, $website, $club_name, $linkedin, $phone, $member_page, $id);
            $stmt->execute();

            header('Location: dashboard.php?msg=' . urlencode('Member updated successfully.'));
            exit;
        }
    }
}

include 'includes/header.php';
?>

<div class="page-title"><h1>Edit Member</h1></div>

<div class="card" style="max-width:650px;">
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <?php if ($member['profile_picture']): ?>
        <img class="current-photo" src="uploads/<?php echo htmlspecialchars($member['profile_picture']); ?>" alt="">
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $member['id']; ?>">

        <label>Profile Picture (leave empty to keep current)</label>
        <input type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">

        <label>Name</label>
        <div class="name-prefix-wrap">
            <span class="name-prefix">RTN.</span>
            <input type="text" name="name" value="<?php echo htmlspecialchars($member['name']); ?>" required>
        </div>

        <label>Position</label>
        <input type="text" name="position" value="<?php echo htmlspecialchars($member['position']); ?>">

        <label>Company Name</label>
        <input type="text" name="company_name" value="<?php echo htmlspecialchars($member['company_name']); ?>">

        <label>Website (if any)</label>
        <input type="url" name="website" value="<?php echo htmlspecialchars($member['website']); ?>">

        <label>Club Name</label>
        <input type="text" name="club_name" value="<?php echo htmlspecialchars($member['club_name']); ?>">

        <label>LinkedIn</label>
        <input type="url" name="linkedin" value="<?php echo htmlspecialchars($member['linkedin']); ?>">

        <label>Phone No.</label>
        <input type="tel" name="phone" value="<?php echo htmlspecialchars($member['phone']); ?>">

        <label>Members Page</label>
        <select name="member_page" required>
            <option value="RMB Chennai Central Members" <?php echo $member['member_page'] === 'RMB Chennai Central Members' ? 'selected' : ''; ?>>RMB Chennai Central Members</option>
            <option value="Rotarians Non Members" <?php echo $member['member_page'] === 'Rotarians Non Members' ? 'selected' : ''; ?>>Rotarians Non Members</option>
        </select>

        <button type="submit" class="btn">Update Member</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
