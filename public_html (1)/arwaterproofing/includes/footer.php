</main>
  <footer class="site-footer">
    <div class="section-wrap footer-grid">
      <div class="footer-brand">
        <a class="brand brand-footer" href="index.php">
          <img class="brand-logo" src="assets/ar-logo.png" alt="A.R. Waterproofing logo">
          <span class="brand-name">A.R. <strong>WATERPROOFING</strong><small>PROTECTING WHAT MATTERS</small></span>
        </a>
        <p>Waterproofing and construction solutions for homes, commercial buildings and industrial properties in Chennai.</p>
      </div>
      <div>
        <h3>Explore</h3>
        <a href="index.php#about">About us</a>
        <a href="services.php">Our services</a>
        <a href="projects.php">Project showcase</a>
        <a href="index.php#reviews">Reviews</a>
      </div>
      <div>
        <h3>Contact</h3>
        <a href="tel:+919841044994">+91 98410 44994</a>
        <a href="mailto:rgn.1972@gmail.com">rgn.1972@gmail.com</a>
        <a href="https://www.arwaterproofing.com" target="_blank" rel="noopener">arwaterproofing.com</a>
      </div>
      <div class="footer-cta">
        <h3>Have a site requirement?</h3>
        <p>Talk directly to the team.</p>
        <a class="button button-primary" href="https://wa.me/919841044994" target="_blank" rel="noopener">Message on WhatsApp ↗</a>
      </div>
    </div>
    <div class="footer-bottom section-wrap">
      <span>© <span id="year"></span> A.R. Waterproofing. All rights reserved.</span>
      <a href="#main">Back to top ↑</a>
      <span>Made for a stronger tomorrow.</span>
    </div>
  </footer>
  <nav class="mobile-action-bar" aria-label="Quick profile actions">
    <a class="quick-call" href="tel:+919841044994"><span>☎</span><small>Call</small></a>
    <a class="quick-review" href="https://www.google.com/search?q=A.R.+Waterproofing+Chennai+Google+reviews" target="_blank" rel="noopener"><span>☆</span><small>Review</small></a>
    <a class="quick-whatsapp" href="https://wa.me/919841044994?text=Hello%20A.R.%20Waterproofing" target="_blank" rel="noopener"><span>◉</span><small>WhatsApp</small></a>
    <button class="quick-save" type="button" id="bottomSaveContactButton"><span>＋</span><small>Save</small></button>
    <button class="quick-share" type="button" id="bottomShareContactButton"><span>↗</span><small>Share</small></button>
  </nav>
  <button class="install-button" id="installButton" type="button"><span aria-hidden="true">↓</span> Install App</button>
  <div class="toast" id="toast" role="status" aria-live="polite"></div>
  <script>
    // Year
    document.getElementById('year').textContent = new Date().getFullYear();
    // Mobile menu
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    if (menuToggle && mobileMenu) {
      menuToggle.addEventListener('click', () => {
        const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
        menuToggle.setAttribute('aria-expanded', String(!expanded));
        mobileMenu.hidden = expanded;
      });
    }
    // Hide splash after load
    window.addEventListener('load', () => {
      const splash = document.getElementById('appSplash');
      if (splash) {
        setTimeout(() => splash.classList.add('is-hidden'), 600);
      }
    });

    // Animated counters
    function animateCounters() {
      const counters = document.querySelectorAll('.count-up');
      counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'), 10) || 0;
        const suffix = counter.getAttribute('data-suffix') || '';
        const duration = 1800;
        const start = performance.now();
        function update(now) {
          const elapsed = now - start;
          const progress = Math.min(elapsed / duration, 1);
          const ease = 1 - Math.pow(1 - progress, 3);
          const current = Math.floor(ease * target);
          counter.textContent = current + suffix;
          if (progress < 1) {
            requestAnimationFrame(update);
          } else {
            counter.textContent = target + suffix;
          }
        }
        requestAnimationFrame(update);
      });
    }
    // Run counters when page loads (and when they come into view if needed)
    if (document.querySelector('.count-up')) {
      window.addEventListener('load', animateCounters);
      // Also trigger after a short delay in case of late DOM
      setTimeout(animateCounters, 300);
    }
  </script>
  
  <script>
// PWA Install Button
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  const installBtn = document.getElementById('installButton');
  if (installBtn) {
    installBtn.style.display = 'inline-flex';
    installBtn.addEventListener('click', async () => {
      if (!deferredPrompt) return;
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      if (outcome === 'accepted') {
        console.log('User accepted the install prompt');
      }
      deferredPrompt = null;
      installBtn.style.display = 'none';
    });
  }
});

// Register Service Worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('./sw.js')
      .then((reg) => console.log('Service Worker registered:', reg.scope))
      .catch((err) => console.log('Service Worker registration failed:', err));
  });
}

// Hide button if already installed or not supported
window.addEventListener('appinstalled', () => {
  const installBtn = document.getElementById('installButton');
  if (installBtn) installBtn.style.display = 'none';
});
</script>

<script>
// ===== Save Contact (vCard download) =====
function downloadVCard() {
  const vcard = `BEGIN:VCARD
VERSION:3.0
FN:Rajendran C
N:C;Rajendran;;;
ORG:A.R. Waterproofing
TITLE:Proprietor
TEL;TYPE=CELL,VOICE:+919841044994
TEL;TYPE=CELL,VOICE:+918680070461
EMAIL:rgn.1972@gmail.com
URL:https://www.arwaterproofing.com
ADR;TYPE=WORK:;;9/3, Pushpa Nagar Main Road;Nungambakkam;Chennai;600034;India
NOTE:Waterproofing and construction solutions in Chennai.
END:VCARD`;

  const blob = new Blob([vcard], { type: 'text/vcard;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'AR-Waterproofing-Rajendran.vcf';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

// ===== Share Profile =====
async function shareProfile() {
  const shareData = {
    title: 'A.R. Waterproofing',
    text: 'Contact Rajendran C — A.R. Waterproofing, Chennai\nPhone: +91 98410 44994\nEmail: rgn.1972@gmail.com',
    url: window.location.origin + '/arwaterproofing/'
  };

  if (navigator.share) {
    try {
      await navigator.share(shareData);
    } catch (err) {
      if (err.name !== 'AbortError') {
        // Fallback if share fails
        downloadVCard();
      }
    }
  } else {
    // Desktop / unsupported browsers → download contact
    downloadVCard();
  }
}

// ===== Wire up buttons =====
document.addEventListener('DOMContentLoaded', () => {
  // Main buttons
  const saveBtn = document.getElementById('saveContactButton');
  const downloadProfileBtn = document.getElementById('shareContactButton');
  const bottomSaveBtn = document.getElementById('bottomSaveContactButton');
  const bottomShareBtn = document.getElementById('bottomShareContactButton');

  if (saveBtn) saveBtn.addEventListener('click', downloadVCard);
  if (downloadProfileBtn) downloadProfileBtn.addEventListener('click', downloadVCard);
  if (bottomSaveBtn) bottomSaveBtn.addEventListener('click', downloadVCard);
  if (bottomShareBtn) bottomShareBtn.addEventListener('click', shareProfile);
});
</script>
</body>
</html>