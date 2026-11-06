<?php
require_once 'includes/config.php';
require_once 'includes/auth_check.php';

$pageTitle = 'Dashboard';

// Filters
$filterPage = $_GET['member_page'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM members WHERE 1=1";
$params = [];
$types = '';

if ($filterPage !== '') {
    $sql .= " AND member_page = ?";
    $params[] = $filterPage;
    $types .= 's';
}
if ($search !== '') {
    $sql .= " AND (name LIKE ? OR company_name LIKE ? OR position LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$members = $stmt->get_result();

// Counts for summary
$totalRMB = $conn->query("SELECT COUNT(*) c FROM members WHERE member_page = 'RMB Chennai Central Members'")->fetch_assoc()['c'];
$totalNon = $conn->query("SELECT COUNT(*) c FROM members WHERE member_page = 'Rotarians Non Members'")->fetch_assoc()['c'];

include 'includes/header.php';
?>

<div class="page-title">
    <h1>Members Dashboard</h1>
    <p>RMB Chennai Central Members: <strong><?php echo $totalRMB; ?></strong> &nbsp;|&nbsp;
       Rotarians Non Members: <strong><?php echo $totalNon; ?></strong></p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="toolbar">
        <form class="filter-form" method="GET">
            <input type="text" name="search" placeholder="Search by name, company, position..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="member_page">
                <option value="">All Pages</option>
                <option value="RMB Chennai Central Members" <?php echo $filterPage === 'RMB Chennai Central Members' ? 'selected' : ''; ?>>RMB Chennai Central Members</option>
                <option value="Rotarians Non Members" <?php echo $filterPage === 'Rotarians Non Members' ? 'selected' : ''; ?>>Rotarians Non Members</option>
            </select>
            <button type="submit" class="btn">Filter</button>
            <a href="dashboard.php" class="btn btn-secondary">Reset</a>
        </form>
        <a href="add_member.php" class="btn">+ Add Member</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Name</th>
                <th>Position / Company</th>
                <th>Club</th>
                <th>Phone</th>
                <th>Page</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($members->num_rows === 0): ?>
            <tr><td colspan="7" style="text-align:center; padding:25px;">No members found.</td></tr>
        <?php endif; ?>
        <?php while ($m = $members->fetch_assoc()): ?>
            <tr>
                <td>
                    <?php if ($m['profile_picture']): ?>
                        <img class="member-photo" src="uploads/<?php echo htmlspecialchars($m['profile_picture']); ?>" alt="">
                    <?php else: ?>
                        <div class="member-photo"></div>
                    <?php endif; ?>
                </td>
                <td><strong>Rtn. <?php echo htmlspecialchars($m['name']); ?></strong></td>
                <td><?php echo htmlspecialchars($m['position']); ?><br><small><?php echo htmlspecialchars($m['company_name']); ?></small></td>
                <td><?php echo htmlspecialchars($m['club_name']); ?></td>
                <td><?php echo htmlspecialchars($m['phone']); ?></td>
                <td>
                    <?php if ($m['member_page'] === 'RMB Chennai Central Members'): ?>
                        <span class="badge badge-rmb">RMB Central</span>
                    <?php else: ?>
                        <span class="badge badge-nonmember">Non Member</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <a class="edit" href="edit_member.php?id=<?php echo $m['id']; ?>">Edit</a>
                    <a class="delete" href="delete_member.php?id=<?php echo $m['id']; ?>" onclick="return confirm('Delete this member?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
