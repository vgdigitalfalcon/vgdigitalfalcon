<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Our Services';

$db = getDB();
$stmt = $db->query("SELECT * FROM services ORDER BY name ASC");
$services = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Enhanced services page – same theme, better visibility & layout */
.services-hero {
  text-align: center;
  padding: 48px 0 32px;
}
.services-hero .eyebrow { margin-bottom: 12px; }
.services-hero h2 {
  font-size: clamp(36px, 4vw, 48px);
  margin: 0 0 12px;
  color: var(--ink, #19152c);
}
.services-hero p {
  color: var(--muted, #716c81);
  font-size: 16px;
  max-width: 560px;
  margin: 0 auto;
}

.service-grid-enhanced {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 22px;
  margin-top: 12px;
}

.service-card-enhanced {
  background: #fff;
  border: 1px solid var(--line, #e8e2f4);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 6px 22px rgba(47, 23, 104, 0.06);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
  display: flex;
  flex-direction: column;
  height: 100%;
}
.service-card-enhanced:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 40px rgba(47, 23, 104, 0.12);
}

.service-card-enhanced img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  display: block;
  background: #f4f0ff;
}

.service-card-body {
  padding: 18px 18px 20px;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.service-card-body h3 {
  font-family: Manrope, sans-serif;
  font-weight: 800;
  font-size: 16px;
  margin: 0 0 8px;
  color: var(--ink, #19152c);
  line-height: 1.35;
}
.service-card-body p {
  font-size: 13px;
  color: var(--muted, #716c81);
  line-height: 1.55;
  margin: 0 0 16px;
  flex: 1;
}

.service-card-actions {
  display: flex;
  gap: 10px;
  margin-top: auto;
}
.service-card-actions .button {
  flex: 1;
  justify-content: center;
  font-size: 12px;
  padding: 10px 12px;
  text-decoration: none;
}
.service-card-actions .button-primary {
  background: linear-gradient(135deg, #5422c8, #713ee3);
  color: #fff !important;
}
.service-card-actions .button-light {
  background: #fff;
  border: 1px solid var(--line, #e8e2f4);
  color: var(--ink, #19152c) !important;
}

@media (max-width: 760px) {
  .service-grid-enhanced { grid-template-columns: 1fr 1fr; gap: 14px; }
  .service-card-enhanced img { height: 130px; }
  .service-card-body { padding: 12px; }
  .service-card-body h3 { font-size: 13px; }
  .service-card-body p { font-size: 11px; }
  .service-card-actions { flex-direction: column; }
}
@media (max-width: 480px) {
  .service-grid-enhanced { grid-template-columns: 1fr; }
}
</style>

<section class="section section-wrap" id="services">
  <div class="services-hero">
    <span class="eyebrow">WHAT WE DO</span>
    <h2>Our <em>services</em></h2>
    <p>Practical waterproofing, repair and construction solutions tailored to your building and site requirements.</p>
  </div>

  <div class="service-grid-enhanced">
    <?php if (empty($services)): ?>
      <p style="grid-column: 1 / -1; text-align: center; color: var(--muted);">No services available yet. Please check back later.</p>
    <?php else: ?>
      <?php foreach ($services as $service): 
$img = getPublicMediaUrl(
    getServiceImage($service['name'], $service['image_path'] ?? null)
);        $excerpt = sanitize(mb_substr(strip_tags($service['about'] ?? ''), 0, 100));
        if (mb_strlen($service['about'] ?? '') > 100) $excerpt .= '...';
      ?>
        <article class="service-card-enhanced">
          <a href="service.php?id=<?php echo (int)$service['id']; ?>" style="display:block;">
            <img src="<?php echo sanitize($img); ?>" alt="<?php echo sanitize($service['name']); ?>" loading="lazy">
          </a>
          <div class="service-card-body">
            <h3><?php echo sanitize($service['name']); ?></h3>
            <p><?php echo $excerpt ?: 'Professional service tailored to your site requirements.'; ?></p>
            <div class="service-card-actions">
              <a class="button button-primary" href="service.php?id=<?php echo (int)$service['id']; ?>">View Details</a>
              <a class="button button-light" href="https://wa.me/919841044994?text=Hello%20A.R.%20Waterproofing%2C%20I%20would%20like%20to%20enquire%20about%20<?php echo urlencode($service['name']); ?>." target="_blank" rel="noopener">Enquire</a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div style="text-align: center; margin-top: 48px;">
    <a class="button button-primary" href="index.html#contact" style="padding: 14px 28px; font-size: 14px;">Need help choosing a service? Talk to us →</a>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>