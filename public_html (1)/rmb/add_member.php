<?php
require_once 'includes/config.php';
require_once 'includes/auth_check.php';

$pageTitle = 'Add Member';
$error = '';

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
        $picture_filename = null;

        if (!empty($_FILES['profile_picture']['name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, or WEBP images are allowed for profile picture.';
            } elseif ($_FILES['profile_picture']['size'] > 3 * 1024 * 1024) {
                $error = 'Profile picture must be smaller than 3MB.';
            } else {
                $picture_filename = 'member_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                move_uploaded_file($_FILES['profile_picture']['tmp_name'], UPLOAD_DIR . $picture_filename);
            }
        }

        if ($error === '') {
            $stmt = $conn->prepare("INSERT INTO members (profile_picture, name, position, company_name, website, club_name, linkedin, phone, member_page) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssss', $picture_filename, $name, $position, $company_name, $website, $club_name, $linkedin, $phone, $member_page);
            $stmt->execute();

            header('Location: dashboard.php?msg=' . urlencode('Member added successfully.'));
            exit;
        }
    }
}

include 'includes/header.php';
?>

<div class="page-title"><h1>Add New Member</h1></div>

<div class="card" style="max-width:650px;">
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Profile Picture</label>
        <input type="file" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">

        <label>Name</label>
        <div class="name-prefix-wrap">
            <span class="name-prefix">RTN.</span>
            <input type="text" name="name" placeholder="e.g. Karthik R" required>
        </div>

        <label>Position</label>
        <input type="text" name="position" placeholder="e.g. Chairman / Managing Director">

        <label>Company Name</label>
        <input type="text" name="company_name" placeholder="e.g. AS Pumps & Systems">

        <label>Website (if any)</label>
        <input type="url" name="website" placeholder="https://example.com">

        <label>Club Name</label>
        <input type="text" name="club_name" placeholder="e.g. Rotary Club of Chennai Central">

        <label>LinkedIn</label>
        <input type="url" name="linkedin" placeholder="https://linkedin.com/in/username">

        <label>Phone No.</label>
        <input type="tel" name="phone" placeholder="e.g. +91 98765 43210">

        <label>Members Page</label>
        <select name="member_page" required>
            <option value="RMB Chennai Central Members">RMB Chennai Central Members</option>
            <option value="Rotarians Non Members">Rotarians Non Members</option>
        </select>

        <button type="submit" class="btn">Save Member</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
