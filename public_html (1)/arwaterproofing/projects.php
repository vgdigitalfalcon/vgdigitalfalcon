<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Our Projects';

$db = getDB();

// Filters & sorting
$sort = $_GET['sort'] ?? 'newest'; // newest | oldest
$year = isset($_GET['year']) && $_GET['year'] !== '' ? (int)$_GET['year'] : null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 9;
$offset = ($page - 1) * $perPage;

// Build WHERE clause
$where = [];
$params = [];
if ($year) {
    $where[] = "p.duration LIKE ?";
    $params[] = $year . '-%';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Order by
$orderSql = ($sort === 'oldest')
    ? "ORDER BY COALESCE(p.duration, '0000-00') ASC, p.created_at ASC"
    : "ORDER BY COALESCE(p.duration, '0000-00') DESC, p.created_at DESC";

// Total count for pagination
$countStmt = $db->prepare("SELECT COUNT(*) FROM projects p $whereSql");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));

// Fetch projects
$sql = "
  SELECT p.*, s.name as service_name,
         (SELECT file_path FROM project_media 
          WHERE project_id = p.id AND media_type = 'image' 
          ORDER BY CASE WHEN phase = 'after' THEN 0 ELSE 1 END, sort_order ASC, id ASC 
          LIMIT 1) as cover_image
  FROM projects p 
  LEFT JOIN services s ON p.service_id = s.id 
  $whereSql
  $orderSql
  LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$projects = $stmt->fetchAll();

// Available years for filter
$yearStmt = $db->query("
  SELECT DISTINCT LEFT(duration, 4) as y 
  FROM projects 
  WHERE duration REGEXP '^[0-9]{4}-[0-9]{2}$'
  ORDER BY y DESC
");
$availableYears = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

// Helper to format YYYY-MM
function formatProjectDate($duration) {
    if (empty($duration) || !preg_match('/^(\d{4})-(\d{2})$/', $duration, $m)) {
        return '';
    }
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
               7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
    return ($months[(int)$m[2]] ?? '') . ' ' . $m[1];
}

require_once __DIR__ . '/includes/header.php';
?>
<style>
.corporate-header {
  padding: 12px 0 40px;
  border-bottom: 1px solid var(--line);
  margin-bottom: 28px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 30px;
  flex-wrap: wrap;
}
.corporate-header h1 {
  font-family: Manrope, sans-serif;
  font-size: clamp(34px, 4vw, 48px);
  font-weight: 800;
  letter-spacing: -1.4px;
  margin: 8px 0 6px;
  color: var(--ink);
  line-height: 1.05;
}
.corporate-header p {
  color: var(--muted);
  font-size: 16px;
  margin: 0;
  max-width: 460px;
}
.project-count {
  font-family: Manrope, sans-serif;
  font-size: 15px;
  font-weight: 700;
  color: var(--purple);
  background: var(--lavender);
  padding: 10px 16px;
  border-radius: 12px;
  white-space: nowrap;
}
.filters-bar {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
  margin-bottom: 28px;
}
.filters-bar select, .filters-bar a {
  border: 1px solid var(--line);
  background: #fff;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 14px;
  font-weight: 600;
  color: var(--ink);
  text-decoration: none;
}
.filters-bar select:focus {
  outline: none;
  border-color: #5422c8;
}
.corporate-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 28px;
}
.corp-card {
  background: #fff;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid var(--line);
  box-shadow: 0 6px 24px rgba(47, 23, 104, 0.05);
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  transition: transform 0.28s ease, box-shadow 0.28s ease, border-color 0.28s ease;
}
.corp-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 40px rgba(84, 34, 200, 0.12);
  border-color: #d4c8f0;
}
.corp-card-image {
  position: relative;
  height: 220px;
  background: #160a31;
  overflow: hidden;
}
.corp-card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.45s ease;
}
.corp-card:hover .corp-card-image img {
  transform: scale(1.05);
}
.corp-card-body {
  padding: 22px 24px 26px;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.corp-service {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.1px;
  color: var(--purple);
  text-transform: uppercase;
  margin-bottom: 8px;
}
.corp-card-body h3 {
  font-family: Manrope, sans-serif;
  font-size: 20px;
  font-weight: 800;
  margin: 0 0 10px;
  letter-spacing: -0.4px;
  line-height: 1.25;
  color: var(--ink);
}
.corp-meta {
  color: var(--muted);
  font-size: 14px;
  margin-bottom: 18px;
  line-height: 1.5;
}
.corp-footer {
  margin-top: auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 14px;
  border-top: 1px solid #f0ecf8;
  font-size: 13px;
  font-weight: 700;
  color: var(--purple);
}
.corp-footer span {
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 70px 20px;
  background: var(--lavender);
  border-radius: 20px;
  border: 1px solid var(--line);
  color: var(--muted);
}
.pagination {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 40px;
  flex-wrap: wrap;
}
.pagination a, .pagination span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 40px;
  height: 40px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1px solid var(--line);
  background: #fff;
  color: var(--ink);
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
}
.pagination a:hover {
  border-color: #5422c8;
  color: #5422c8;
}
.pagination .current {
  background: #5422c8;
  color: #fff;
  border-color: #5422c8;
}
@media (max-width: 740px) {
  .corporate-header { flex-direction: column; align-items: flex-start; }
  .corporate-grid { grid-template-columns: 1fr; }
  .corp-card-image { height: 200px; }
}
</style>

<section class="section section-wrap" id="projects">
  <div class="corporate-header">
    <div>
      <span class="eyebrow">OUR WORK</span>
      <h1>Completed Projects</h1>
      <p>Waterproofing and construction work delivered with precision across residential, commercial, and industrial sites.</p>
    </div>
    <?php if ($total > 0): ?>
      <div class="project-count"><?php echo $total; ?> Projects</div>
    <?php endif; ?>
  </div>

  <form class="filters-bar" method="get" action="">
    <select name="year" onchange="this.form.submit()">
      <option value="">All Years</option>
      <?php foreach ($availableYears as $y): ?>
        <option value="<?php echo (int)$y; ?>" <?php echo $year === (int)$y ? 'selected' : ''; ?>>
          <?php echo (int)$y; ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select name="sort" onchange="this.form.submit()">
      <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
      <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
    </select>

    <?php if ($year || $sort !== 'newest'): ?>
      <a href="projects.php">Clear filters</a>
    <?php endif; ?>
  </form>

  <div class="corporate-grid">
    <?php if (empty($projects)): ?>
      <div class="empty-state">
        <p>No projects found for the selected filters.</p>
      </div>
    <?php else: ?>
      <?php foreach ($projects as $project): 
        $cover = $project['cover_image'] 
          ? getPublicMediaUrl($project['cover_image']) 
          : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=85';
        $dateLabel = formatProjectDate($project['duration']);
      ?>
        <a class="corp-card" href="project.php?id=<?php echo (int)$project['id']; ?>">
          <div class="corp-card-image">
            <img src="<?php echo sanitize($cover); ?>" alt="<?php echo sanitize($project['name']); ?>" loading="lazy">
          </div>
          <div class="corp-card-body">
            <div class="corp-service"><?php echo sanitize($project['service_name'] ?? 'Project'); ?></div>
            <h3><?php echo sanitize($project['name']); ?></h3>
            <div class="corp-meta">
              <?php if (!empty($project['location'])): ?>
                <?php echo sanitize($project['location']); ?>
              <?php endif; ?>
              <?php if ($dateLabel): ?>
                <?php echo !empty($project['location']) ? ' · ' : ''; ?><?php echo sanitize($dateLabel); ?>
              <?php endif; ?>
            </div>
            <div class="corp-footer">
              <span>View Project</span>
              <span>→</span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">← Prev</a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
          <span class="current"><?php echo $i; ?></span>
        <?php else: ?>
          <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next →</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>