<?php
require_once __DIR__ . '/includes/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: services.php');
    exit;
}
$db = getDB();
$stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$id]);
$service = $stmt->fetch();
if (!$service) {
    header('Location: services.php');
    exit;
}
$pageTitle = $service['name'];

// Related projects pagination
$projPage = max(1, (int)($_GET['proj_page'] ?? 1));
$projPerPage = 8;
$projOffset = ($projPage - 1) * $projPerPage;

$countStmt = $db->prepare("SELECT COUNT(*) FROM projects WHERE service_id = ?");
$countStmt->execute([$id]);
$totalProjects = (int)$countStmt->fetchColumn();
$totalProjPages = max(1, (int)ceil($totalProjects / $projPerPage));

$stmt = $db->prepare("
  SELECT p.*, 
         (SELECT file_path FROM project_media 
          WHERE project_id = p.id AND media_type = 'image' 
          ORDER BY CASE WHEN phase = 'after' THEN 0 ELSE 1 END, sort_order ASC, id ASC 
          LIMIT 1) as cover_image
  FROM projects p 
  WHERE p.service_id = ? 
  ORDER BY COALESCE(p.duration, '0000-00') DESC, p.created_at DESC
  LIMIT $projPerPage OFFSET $projOffset
");
$stmt->execute([$id]);
$projects = $stmt->fetchAll();

$serviceImage = getPublicMediaUrl(
    getServiceImage($service['name'], $service['image_path'] ?? null)
);

require_once __DIR__ . '/includes/header.php';
?>
<style>
.premium-service { padding-top: 20px; padding-bottom: 40px; }
.premium-header {
  display: flex; justify-content: space-between; align-items: flex-end;
  gap: 24px; margin-bottom: 32px; flex-wrap: wrap;
}
.premium-header .eyebrow { font-size: 11px; letter-spacing: 1.8px; color: #5422c8; font-weight: 800; }
.premium-header h1 {
  font-family: Manrope, sans-serif; font-size: clamp(36px, 4.5vw, 52px);
  font-weight: 800; letter-spacing: -1.2px; margin: 6px 0 8px; color: #19152c; line-height: 1.1;
}
.premium-header .subtitle { color: #716c81; font-size: 15px; margin: 0; }
.back-link { font-weight: 700; font-size: 13px; color: #5422c8; text-decoration: none; white-space: nowrap; }
.back-link:hover { text-decoration: underline; }
.premium-grid { display: grid; grid-template-columns: 0.95fr 1.15fr; gap: 48px; align-items: start; }
.premium-visual { position: sticky; top: 100px; }
.premium-visual img {
  width: 100%; height: 460px; object-fit: cover; border-radius: 20px; display: block;
  box-shadow: 0 20px 50px rgba(47, 23, 104, 0.12); background: #f4f0ff;
}
.premium-content h2 { font-family: Manrope, sans-serif; font-size: 22px; font-weight: 800; margin: 0 0 12px; color: #19152c; }
.premium-content .about-text { color: #5a546c; font-size: 15.5px; line-height: 1.75; margin: 0 0 32px; }
.benefits-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 14px 28px; margin: 0 0 32px;
}
.benefit-item {
  display: flex; align-items: flex-start; gap: 10px; font-size: 14.5px;
  color: #2d2740; line-height: 1.55; padding: 2px 0;
}
.benefit-item::before {
  content: ""; width: 8px; height: 8px; border-radius: 50%; background: #5422c8;
  flex-shrink: 0; margin-top: 7px;
}
.process-section {
  margin-top: 56px; padding: 48px 0 20px; text-align: center; border-top: 1px solid #eee8f7;
}
.process-section .eyebrow { font-size: 11px; letter-spacing: 1.8px; color: #5422c8; font-weight: 800; }
.process-section h2 {
  font-family: Manrope, sans-serif; font-size: clamp(28px, 3.5vw, 36px);
  font-weight: 800; margin: 8px 0 28px; color: #19152c;
}
.process-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 18px; text-align: left; max-width: 1100px; margin: 0 auto;
}
.process-box {
  background: #fff; border: 1px solid #e8e2f4; border-radius: 16px; padding: 20px 18px;
  box-shadow: 0 4px 16px rgba(47, 23, 104, 0.05); min-height: 110px;
}
.process-box .step-num {
  display: inline-flex; align-items: center; justify-content: center;
  width: 28px; height: 28px; border-radius: 50%; background: #5422c8; color: #fff;
  font-size: 13px; font-weight: 800; font-family: Manrope, sans-serif; margin-bottom: 10px;
}
.process-box p { margin: 0; font-size: 14px; color: #3a344c; line-height: 1.55; }
.premium-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 8px; }
.premium-actions .button {
  padding: 14px 22px; font-size: 14px; font-weight: 700; border-radius: 12px;
  text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
}
.premium-actions .button-primary {
  background: linear-gradient(135deg, #5422c8, #6b3bd5); color: #fff !important;
  box-shadow: 0 8px 20px rgba(84, 34, 200, 0.25);
}
.premium-actions .button-primary:hover { transform: translateY(-1px); }
.premium-actions .button-outline {
  background: #fff; border: 1.5px solid #e0d8f0; color: #19152c !important;
}
.premium-actions .button-outline:hover { border-color: #5422c8; color: #5422c8 !important; }
.related-section { margin-top: 64px; padding-top: 40px; border-top: 1px solid #eee8f7; }
.related-header {
  display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
}
.related-section h2 {
  font-family: Manrope, sans-serif; font-size: 32px; font-weight: 800; margin: 8px 0 0; color: #19152c;
}
.related-count {
  font-family: Manrope, sans-serif; font-size: 14px; font-weight: 700;
  color: #5422c8; background: #f2edff; padding: 8px 14px; border-radius: 10px;
}
.related-note { color: #716c81; font-size: 14px; margin: 0 0 24px; }
.related-grid {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px;
}
.related-card {
  background: #fff; border: 1px solid #e8e2f4; border-radius: 16px; overflow: hidden;
  text-decoration: none; color: inherit; display: flex; flex-direction: column;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.related-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(84,34,200,0.12); }
.related-card img { width: 100%; height: 160px; object-fit: cover; display: block; background: #f4f0ff; }
.related-card-body { padding: 14px 14px 16px; }
.related-card-body h3 {
  font-family: Manrope, sans-serif; font-size: 15px; font-weight: 800; margin: 0 0 6px; color: #19152c;
}
.related-card-body span { font-size: 12px; color: #716c81; }
.pagination {
  display: flex; justify-content: center; gap: 8px; margin-top: 28px; flex-wrap: wrap;
}
.pagination a, .pagination span {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 38px; height: 38px; padding: 0 12px; border-radius: 10px;
  border: 1px solid #e8e2f4; background: #fff; color: #19152c;
  text-decoration: none; font-weight: 600; font-size: 14px;
}
.pagination a:hover { border-color: #5422c8; color: #5422c8; }
.pagination .current { background: #5422c8; color: #fff; border-color: #5422c8; }
@media (max-width: 1100px) {
  .related-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 980px) {
  .premium-grid { grid-template-columns: 1fr; gap: 32px; }
  .premium-visual { position: static; }
  .premium-visual img { height: 320px; }
  .process-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 700px) {
  .related-grid { grid-template-columns: 1fr 1fr; }
  .process-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
  .premium-header { flex-direction: column; align-items: flex-start; }
  .premium-actions { flex-direction: column; }
  .premium-actions .button { width: 100%; justify-content: center; }
  .premium-visual img { height: 240px; }
  .related-grid { grid-template-columns: 1fr; }
}
</style>

<section class="section section-wrap premium-service">
  <div class="premium-header">
    <div>
      <span class="eyebrow">SERVICE DETAILS</span>
      <h1><?php echo sanitize($service['name']); ?></h1>
      <p class="subtitle">Professional waterproofing & construction solution</p>
    </div>
    <a class="back-link" href="services.php">← All Services</a>
  </div>

  <div class="premium-grid">
    <div class="premium-visual">
      <img src="<?php echo sanitize($serviceImage); ?>" alt="<?php echo sanitize($service['name']); ?>">
    </div>
    <div class="premium-content">
      <h2>About this service</h2>
      <p class="about-text"><?php echo nl2br(sanitize($service['about'] ?? 'No description available.')); ?></p>

      <?php if (!empty($service['key_benefits'])): ?>
        <h2>Key Benefits</h2>
        <div class="benefits-grid">
          <?php foreach (array_filter(array_map('trim', explode("\n", $service['key_benefits']))) as $benefit): ?>
            <div class="benefit-item"><?php echo sanitize($benefit); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="premium-actions">
        <a class="button button-primary" href="https://wa.me/919841044994?text=Hello%20A.R.%20Waterproofing%2C%20I%20would%20like%20to%20enquire%20about%20<?php echo urlencode($service['name']); ?>." target="_blank" rel="noopener">Enquire on WhatsApp ↗</a>
        <a class="button button-outline" href="tel:+919841044994">Call Now</a>
      </div>
    </div>
  </div>

  <?php if (!empty($service['process'])): ?>
    <div class="process-section">
      <span class="eyebrow">HOW WE WORK</span>
      <h2>Our Process</h2>
      <div class="process-grid">
        <?php 
        $steps = array_filter(array_map('trim', explode("\n", $service['process'])));
        $stepNum = 1;
        foreach ($steps as $step): 
        ?>
          <div class="process-box">
            <span class="step-num"><?php echo $stepNum++; ?></span>
            <p><?php echo sanitize($step); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <div class="related-section">
    <div class="related-header">
      <div>
        <span class="eyebrow">RELATED WORK</span>
        <h2>Projects for this service</h2>
      </div>
      <?php if ($totalProjects > 0): ?>
        <div class="related-count"><?php echo $totalProjects; ?> Project<?php echo $totalProjects !== 1 ? 's' : ''; ?></div>
      <?php endif; ?>
    </div>

    <?php if (!empty($projects)): ?>
      <div class="related-grid">
        <?php foreach ($projects as $project): 
          $cover = $project['cover_image'] 
            ? getPublicMediaUrl($project['cover_image']) 
            : 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=800&q=85';
        ?>
          <a class="related-card" href="project.php?id=<?php echo (int)$project['id']; ?>">
            <img src="<?php echo sanitize($cover); ?>" alt="<?php echo sanitize($project['name']); ?>" loading="lazy">
            <div class="related-card-body">
              <h3><?php echo sanitize($project['name']); ?></h3>
              <?php if (!empty($project['location'])): ?>
                <span><?php echo sanitize($project['location']); ?></span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($totalProjPages > 1): ?>
        <div class="pagination">
          <?php if ($projPage > 1): ?>
            <a href="?id=<?php echo $id; ?>&proj_page=<?php echo $projPage - 1; ?>">← Prev</a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalProjPages; $i++): ?>
            <?php if ($i === $projPage): ?>
              <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
              <a href="?id=<?php echo $id; ?>&proj_page=<?php echo $i; ?>"><?php echo $i; ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($projPage < $totalProjPages): ?>
            <a href="?id=<?php echo $id; ?>&proj_page=<?php echo $projPage + 1; ?>">Next →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p class="related-note">Related projects will appear here once they are added from the admin panel.</p>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>