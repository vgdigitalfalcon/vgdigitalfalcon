<?php
require_once __DIR__ . '/includes/config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: projects.php');
    exit;
}
$db = getDB();
$stmt = $db->prepare("
  SELECT p.*, s.name as service_name 
  FROM projects p 
  LEFT JOIN services s ON p.service_id = s.id 
  WHERE p.id = ?
");
$stmt->execute([$id]);
$project = $stmt->fetch();
if (!$project) {
    header('Location: projects.php');
    exit;
}
$pageTitle = $project['name'];

// Media split by phase
$stmt = $db->prepare("SELECT * FROM project_media WHERE project_id = ? ORDER BY phase, sort_order ASC, id ASC");
$stmt->execute([$id]);
$allMedia = $stmt->fetchAll();

$beforeMedia = [];
$afterMedia = [];
foreach ($allMedia as $m) {
    if (($m['phase'] ?? 'after') === 'before') {
        $beforeMedia[] = $m;
    } else {
        $afterMedia[] = $m;
    }
}

// Prefer first After image for hero, then any image
$mainImage = null;
foreach (array_merge($afterMedia, $beforeMedia) as $m) {
    if ($m['media_type'] === 'image') {
        $mainImage = getPublicMediaUrl($m['file_path']);
        break;
    }
}
if (!$mainImage) {
    $mainImage = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=85';
}

// Format project completion date
$completedLabel = '';
if (!empty($project['duration']) && preg_match('/^(\d{4})-(\d{2})$/', $project['duration'], $m)) {
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',
               7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
    $completedLabel = ($months[(int)$m[2]] ?? '') . ' ' . $m[1];
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
.project-hero { position: relative; margin-bottom: 48px; }
.project-hero-image { position: relative; border-radius: 24px; overflow: hidden; min-height: 420px; background: #160a31; }
.project-hero-image img { width: 100%; height: 520px; object-fit: cover; display: block; }
.project-hero-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(25,21,44,0.85) 0%, rgba(25,21,44,0.2) 50%, transparent 100%);
  display: flex; align-items: flex-end; padding: 40px;
}
.project-hero-content h1 {
  font-family: Manrope, sans-serif; font-size: clamp(32px, 4vw, 48px); font-weight: 800;
  color: #fff; margin: 0 0 8px; letter-spacing: -1px; line-height: 1.1;
}
.project-meta-pills { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
.project-meta-pills span {
  background: rgba(255,255,255,0.15); backdrop-filter: blur(8px); color: #fff;
  font-size: 13px; font-weight: 600; padding: 6px 14px; border-radius: 999px;
  border: 1px solid rgba(255,255,255,0.2);
}
.project-details-grid { display: grid; grid-template-columns: 1.6fr 1fr; gap: 40px; margin-bottom: 64px; }
@media (max-width: 860px) {
  .project-details-grid { grid-template-columns: 1fr; }
  .project-hero-image img { height: 380px; }
  .project-hero-overlay { padding: 28px; }
}
.project-overview { background: #fff; border: 1px solid var(--line); border-radius: 20px; padding: 36px; box-shadow: 0 8px 30px rgba(47,23,104,0.06); }
.project-overview h3 { font-family: Manrope, sans-serif; font-size: 22px; margin: 0 0 16px; color: var(--ink); }
.project-overview p { color: var(--muted); font-size: 16px; line-height: 1.75; margin: 0; }
.project-sidebar { display: flex; flex-direction: column; gap: 20px; }
.info-card { background: #fff; border: 1px solid var(--line); border-radius: 18px; padding: 24px; box-shadow: 0 6px 24px rgba(47,23,104,0.05); }
.info-card h4 { font-family: Manrope, sans-serif; font-size: 15px; font-weight: 700; margin: 0 0 14px; color: var(--ink); }
.info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0ecf8; font-size: 14px; }
.info-row:last-child { border-bottom: none; }
.info-row strong { color: var(--ink); font-weight: 600; }
.info-row span { color: var(--muted); }
.cta-card { background: linear-gradient(145deg, #5422c8, #6b3bd5); color: #fff; border-radius: 18px; padding: 28px; text-align: center; }
.cta-card h4 { font-family: Manrope, sans-serif; margin: 0 0 8px; font-size: 18px; }
.cta-card p { margin: 0 0 20px; font-size: 14px; opacity: 0.9; }

.gallery-section { margin-top: 20px; }
.phase-heading { margin: 40px 0 16px; }
.phase-heading h3 { font-family: Manrope, sans-serif; font-size: 26px; font-weight: 800; margin: 6px 0 0; color: var(--ink); }
.gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; }
.gallery-item {
  position: relative; border-radius: 16px; overflow: hidden; cursor: pointer;
  background: #160a31; aspect-ratio: 4/3; border: 1px solid var(--line);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.gallery-item:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(84,34,200,0.15); }
.gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
.gallery-item:hover img { transform: scale(1.05); }
.gallery-item .play-overlay {
  position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
  background: rgba(22,10,49,0.55); color: #fff; font-size: 36px;
}
.gallery-caption-bar {
  position: absolute; bottom: 0; left: 0; right: 0; padding: 12px 14px;
  background: linear-gradient(transparent, rgba(0,0,0,0.7)); color: #fff;
  font-size: 13px; font-weight: 600; opacity: 0; transition: opacity 0.25s;
}
.gallery-item:hover .gallery-caption-bar { opacity: 1; }
</style>

<section class="section section-wrap">
  <div class="section-heading" style="margin-bottom: 28px;">
    <div>
      <span class="eyebrow">PROJECT DETAILS</span>
    </div>
    <a class="text-link" href="projects.php">← All Projects</a>
  </div>

  <div class="project-hero">
    <div class="project-hero-image">
      <img src="<?php echo sanitize($mainImage); ?>" alt="<?php echo sanitize($project['name']); ?>">
      <div class="project-hero-overlay">
        <div class="project-hero-content">
          <h1><?php echo sanitize($project['name']); ?></h1>
          <div class="project-meta-pills">
            <?php if (!empty($project['service_name'])): ?>
              <span><?php echo sanitize($project['service_name']); ?></span>
            <?php endif; ?>
            <?php if (!empty($project['location'])): ?>
              <span><?php echo sanitize($project['location']); ?></span>
            <?php endif; ?>
            <?php if ($completedLabel): ?>
              <span><?php echo sanitize($completedLabel); ?></span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="project-details-grid">
    <div class="project-overview">
      <h3>Project Overview</h3>
      <p><?php echo nl2br(sanitize($project['overview'] ?? 'No overview available.')); ?></p>
    </div>

    <div class="project-sidebar">
      <div class="info-card">
        <h4>Project Information</h4>
        <?php if (!empty($project['client_name'])): ?>
          <div class="info-row"><strong>Client</strong><span><?php echo sanitize($project['client_name']); ?></span></div>
        <?php endif; ?>
        <?php if ($completedLabel): ?>
          <div class="info-row"><strong>Completed</strong><span><?php echo sanitize($completedLabel); ?></span></div>
        <?php endif; ?>
        <?php if (!empty($project['location'])): ?>
          <div class="info-row"><strong>Location</strong><span><?php echo sanitize($project['location']); ?></span></div>
        <?php endif; ?>
        <?php if (!empty($project['service_name'])): ?>
          <div class="info-row"><strong>Service</strong><span><?php echo sanitize($project['service_name']); ?></span></div>
        <?php endif; ?>
      </div>

      <div class="cta-card">
        <h4>Interested in this work?</h4>
        <p>Speak with our team about a similar solution for your property.</p>
        <a class="button button-white" href="https://wa.me/919841044994?text=Hello%20A.R.%20Waterproofing%2C%20I%20would%20like%20to%20enquire%20about%20the%20<?php echo urlencode($project['name']); ?>%20project." target="_blank" rel="noopener" style="width:100%; justify-content:center;">Enquire on WhatsApp <span>↗</span></a>
        <a class="button button-light" href="tel:+919841044994" style="width:100%; justify-content:center; margin-top:10px; background:#fff; color:var(--purple);">Call Now</a>
      </div>
    </div>
  </div>

  <!-- BEFORE Gallery -->
  <?php if (!empty($beforeMedia)): ?>
    <div class="gallery-section">
      <div class="phase-heading">
        <span class="eyebrow">TRANSFORMATION</span>
        <h3>Before</h3>
      </div>
      <div class="gallery-grid" id="beforeGrid">
        <?php foreach ($beforeMedia as $index => $item): ?>
          <article class="gallery-item" data-phase="before" data-index="<?php echo $index; ?>">
            <?php if ($item['media_type'] === 'image'): ?>
              <img src="<?php echo sanitize(getPublicMediaUrl($item['file_path'])); ?>" alt="Before image <?php echo $index + 1; ?>" loading="lazy">
            <?php else: ?>
              <div class="play-overlay">▶</div>
            <?php endif; ?>
            <div class="gallery-caption-bar">Before <?php echo ucfirst($item['media_type']); ?> <?php echo $index + 1; ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- AFTER Gallery -->
  <?php if (!empty($afterMedia)): ?>
    <div class="gallery-section">
      <div class="phase-heading">
        <span class="eyebrow">TRANSFORMATION</span>
        <h3>After</h3>
      </div>
      <div class="gallery-grid" id="afterGrid">
        <?php foreach ($afterMedia as $index => $item): ?>
          <article class="gallery-item" data-phase="after" data-index="<?php echo $index; ?>">
            <?php if ($item['media_type'] === 'image'): ?>
              <img src="<?php echo sanitize(getPublicMediaUrl($item['file_path'])); ?>" alt="After image <?php echo $index + 1; ?>" loading="lazy">
            <?php else: ?>
              <div class="play-overlay">▶</div>
            <?php endif; ?>
            <div class="gallery-caption-bar">After <?php echo ucfirst($item['media_type']); ?> <?php echo $index + 1; ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<dialog class="gallery-dialog" id="galleryDialog">
  <button class="dialog-close gallery-close" type="button" onclick="document.getElementById('galleryDialog').close()" aria-label="Close">×</button>
  <button class="gallery-nav gallery-prev" type="button" id="galleryPrev" aria-label="Previous">‹</button>
  <div id="galleryContent" style="width:100%; height:100%; display:flex; align-items:center; justify-content:center;"></div>
  <button class="gallery-nav gallery-next" type="button" id="galleryNext" aria-label="Next">›</button>
  <div class="gallery-caption">
    <strong id="galleryTitle"></strong>
    <span id="galleryCounter"></span>
  </div>
</dialog>

<script>
const beforeItems = <?php echo json_encode(array_map(function($m) {
  return ['type' => $m['media_type'], 'src' => getPublicMediaUrl($m['file_path'])];
}, $beforeMedia)); ?>;

const afterItems = <?php echo json_encode(array_map(function($m) {
  return ['type' => $m['media_type'], 'src' => getPublicMediaUrl($m['file_path'])];
}, $afterMedia)); ?>;

let currentPhase = 'after';
let currentIndex = 0;

function openGallery(phase, index) {
  currentPhase = phase;
  currentIndex = index;
  showMedia();
  document.getElementById('galleryDialog').showModal();
}

function getCurrentItems() {
  return currentPhase === 'before' ? beforeItems : afterItems;
}

function showMedia() {
  const items = getCurrentItems();
  const item = items[currentIndex];
  if (!item) return;

  const content = document.getElementById('galleryContent');
  const title = document.getElementById('galleryTitle');
  const counter = document.getElementById('galleryCounter');

  if (item.type === 'image') {
    content.innerHTML = `<img src="${item.src}" alt="Gallery" style="max-width:100%; max-height:calc(100dvh - 120px); object-fit:contain;">`;
  } else {
    content.innerHTML = `<video controls autoplay style="max-width:100%; max-height:calc(100dvh - 120px);"><source src="${item.src}" type="video/mp4"></video>`;
  }

  const phaseLabel = currentPhase === 'before' ? 'Before' : 'After';
  title.textContent = phaseLabel + ' ' + (item.type === 'image' ? 'Photo' : 'Video');
  counter.textContent = `${currentIndex + 1} / ${items.length}`;
}

document.querySelectorAll('#beforeGrid .gallery-item').forEach(card => {
  card.addEventListener('click', () => openGallery('before', parseInt(card.dataset.index, 10)));
});
document.querySelectorAll('#afterGrid .gallery-item').forEach(card => {
  card.addEventListener('click', () => openGallery('after', parseInt(card.dataset.index, 10)));
});

document.getElementById('galleryPrev')?.addEventListener('click', (e) => {
  e.stopPropagation();
  const items = getCurrentItems();
  currentIndex = (currentIndex - 1 + items.length) % items.length;
  showMedia();
});
document.getElementById('galleryNext')?.addEventListener('click', (e) => {
  e.stopPropagation();
  const items = getCurrentItems();
  currentIndex = (currentIndex + 1) % items.length;
  showMedia();
});

document.getElementById('galleryDialog')?.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowLeft') document.getElementById('galleryPrev')?.click();
  if (e.key === 'ArrowRight') document.getElementById('galleryNext')?.click();
  if (e.key === 'Escape') document.getElementById('galleryDialog').close();
});
document.getElementById('galleryDialog')?.addEventListener('click', (e) => {
  if (e.target === document.getElementById('galleryDialog')) {
    document.getElementById('galleryDialog').close();
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>