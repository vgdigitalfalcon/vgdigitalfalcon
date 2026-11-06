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

// Media
$stmt = $db->prepare("SELECT * FROM project_media WHERE project_id = ? ORDER BY sort_order ASC, id ASC");
$stmt->execute([$id]);
$media = $stmt->fetchAll();

$mainImage = null;
foreach ($media as $m) {
    if ($m['media_type'] === 'image') {
        $mainImage = $m['file_path'];
        break;
    }
}
if (!$mainImage) {
    $mainImage = 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=85';
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="section section-wrap">
  <div class="section-heading">
    <div>
      <span class="eyebrow">PROJECT DETAILS</span>
      <h2><?php echo sanitize($project['name']); ?></h2>
      <p><?php echo sanitize($project['service_name'] ?? ''); ?><?php echo $project['location'] ? ' • ' . sanitize($project['location']) : ''; ?></p>
    </div>
    <a class="text-link" href="projects.php">← All Projects</a>
  </div>

  <div class="about-grid" style="margin-bottom: 48px;">
    <div class="about-image">
      <img src="<?php echo sanitize($mainImage); ?>" alt="<?php echo sanitize($project['name']); ?>">
      <div class="image-caption">
        <span>AR</span>
        <strong><?php echo sanitize($project['service_name'] ?? 'Project'); ?><br>Completed with care</strong>
      </div>
    </div>
    <div class="about-copy" style="color: inherit;">
      <div style="margin-bottom: 18px;">
        <?php if (!empty($project['client_name'])): ?>
          <p><strong>Client:</strong> <?php echo sanitize($project['client_name']); ?></p>
        <?php endif; ?>
        <?php if (!empty($project['duration'])): ?>
          <p><strong>Duration:</strong> <?php echo sanitize($project['duration']); ?></p>
        <?php endif; ?>
        <?php if (!empty($project['location'])): ?>
          <p><strong>Location:</strong> <?php echo sanitize($project['location']); ?></p>
        <?php endif; ?>
      </div>
      <h3 style="font-family: Manrope, sans-serif; margin: 0 0 10px;">Project Overview</h3>
      <p><?php echo nl2br(sanitize($project['overview'] ?? 'No overview available.')); ?></p>
      
      <div style="margin-top: 28px;">
        <a class="button button-primary" href="https://wa.me/919841044994?text=Hello%20A.R.%20Waterproofing%2C%20I%20would%20like%20to%20enquire%20about%20the%20<?php echo urlencode($project['name']); ?>%20project." target="_blank" rel="noopener">Enquire about this project <span>↗</span></a>
        <a class="button button-light" href="tel:+919841044994" style="margin-left: 10px;">Call Now</a>
      </div>
    </div>
  </div>

  <?php if (!empty($media)): ?>
    <div class="section-heading">
      <div>
        <span class="eyebrow">GALLERY</span>
        <h2>Project <em>photos & videos</em></h2>
      </div>
    </div>
    <div class="service-grid" id="galleryGrid">
      <?php foreach ($media as $index => $item): ?>
        <article class="service-card" style="cursor: pointer;" data-index="<?php echo $index; ?>">
          <?php if ($item['media_type'] === 'image'): ?>
            <img src="<?php echo sanitize($item['file_path']); ?>" alt="Gallery image <?php echo $index + 1; ?>" loading="lazy">
          <?php else: ?>
            <div style="height: 145px; background: #160a31; display: flex; align-items: center; justify-content: center; color: white; font-size: 42px;">▶</div>
          <?php endif; ?>
          <div class="service-content">
            <span class="service-icon"><?php echo $item['media_type'] === 'image' ? '📷' : '🎬'; ?></span>
            <h3><?php echo ucfirst($item['media_type']); ?> <?php echo $index + 1; ?></h3>
            <p>Click to view in slideshow</p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- Gallery Slideshow Dialog (uses existing .gallery-dialog styles) -->
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
const mediaItems = <?php echo json_encode(array_map(function($m) {
  return ['type' => $m['media_type'], 'src' => $m['file_path']];
}, $media)); ?>;
let currentIndex = 0;

function openGallery(index) {
  if (!mediaItems.length) return;
  currentIndex = index;
  showMedia();
  document.getElementById('galleryDialog').showModal();
}

function showMedia() {
  const item = mediaItems[currentIndex];
  const content = document.getElementById('galleryContent');
  const title = document.getElementById('galleryTitle');
  const counter = document.getElementById('galleryCounter');
  
  if (!item) return;
  
  if (item.type === 'image') {
    content.innerHTML = `<img src="${item.src}" alt="Gallery image" style="max-width:100%; max-height:calc(100dvh - 120px); object-fit:contain;">`;
  } else {
    content.innerHTML = `<video controls autoplay style="max-width:100%; max-height:calc(100dvh - 120px);"><source src="${item.src}" type="video/mp4">Your browser does not support the video tag.</video>`;
  }
  
  title.textContent = item.type === 'image' ? 'Photo' : 'Video';
  counter.textContent = `${currentIndex + 1} / ${mediaItems.length}`;
}

document.querySelectorAll('#galleryGrid .service-card').forEach(card => {
  card.addEventListener('click', () => {
    openGallery(parseInt(card.dataset.index, 10));
  });
});

document.getElementById('galleryPrev')?.addEventListener('click', (e) => {
  e.stopPropagation();
  currentIndex = (currentIndex - 1 + mediaItems.length) % mediaItems.length;
  showMedia();
});

document.getElementById('galleryNext')?.addEventListener('click', (e) => {
  e.stopPropagation();
  currentIndex = (currentIndex + 1) % mediaItems.length;
  showMedia();
});

document.getElementById('galleryDialog')?.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowLeft') document.getElementById('galleryPrev')?.click();
  if (e.key === 'ArrowRight') document.getElementById('galleryNext')?.click();
  if (e.key === 'Escape') document.getElementById('galleryDialog').close();
});

// Close when clicking backdrop
document.getElementById('galleryDialog')?.addEventListener('click', (e) => {
  if (e.target === document.getElementById('galleryDialog')) {
    document.getElementById('galleryDialog').close();
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>