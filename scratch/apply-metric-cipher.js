const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');

console.log('--- Applying AI Laser Scan & Cipher Decrypt Upgrade ---');

// 1. Create assets/js/ecom-metric-cipher.js
const cipherJsCode = `/**
 * ============================================================================
 * VG DIGITAL FALCON — AI LASER SCAN & CIPHER DECRYPT METRICS ENGINE
 * File: assets/js/ecom-metric-cipher.js
 * 
 * Features:
 * 1. Laser Scan & Cipher Decrypt:
 *    - Detects scroll into view via IntersectionObserver.
 *    - An Electric Cyan (#00F0FF) laser beam sweeps across each card.
 *    - High-speed cipher decryption scrambles cyber glyphs into final crisp metrics
 *      (250, 3,200, 1.2M+, 3X).
 *    - Locks in with a Cyber Violet (#A855F7) glow flash.
 * 2. 3D Glass Perspective Tilt & Interactive Specular Glare:
 *    - Real-time 3D cursor tracking with dynamic tilt (rotateX, rotateY) and
 *      specular glare spotlight on mouse movement.
 *    - Smooth spring-back on mouse leave.
 * ============================================================================
 */

(function () {
  'use strict';

  const GLYPHS = '0123456789ABCDEF#@%&<>X$/!*+';

  function initMetricsCipher() {
    const section = document.querySelector('.vg-ecom-stats-section');
    if (!section) return;

    const cards = section.querySelectorAll('.vg-ecom-counter-card');
    let hasTriggered = false;

    // Laser Scan & Cipher Decryption Runner
    function runCipherDecryption(card, index) {
      const valueEl = card.querySelector('.vg-ecom-cipher-val');
      if (!valueEl) return;

      const targetText = card.getAttribute('data-cipher-target') || valueEl.textContent.trim();
      const targetLen = targetText.length;
      const duration = 750; // ms
      const startTime = performance.now();

      // Trigger Laser Beam Sweep across card
      card.classList.add('is-scanning');

      function updateCipher(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Calculate how many characters are locked in from left to right
        const lockedCount = Math.floor(progress * targetLen);

        let output = '';
        for (let i = 0; i < targetLen; i++) {
          if (i < lockedCount || progress >= 1) {
            output += targetText[i];
          } else {
            // Random cipher glyph
            output += GLYPHS[Math.floor(Math.random() * GLYPHS.length)];
          }
        }

        valueEl.textContent = output;

        if (progress < 1) {
          requestAnimationFrame(updateCipher);
        } else {
          // Lock-in Complete: Set final text and trigger Cyber Violet Glow Flash!
          valueEl.textContent = targetText;
          card.classList.remove('is-scanning');
          card.classList.add('is-locked');
          valueEl.classList.add('is-flash');

          setTimeout(() => {
            valueEl.classList.remove('is-flash');
          }, 800);
        }
      }

      requestAnimationFrame(updateCipher);
    }

    // IntersectionObserver to trigger when section enters viewport
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !hasTriggered) {
            hasTriggered = true;
            cards.forEach((card, index) => {
              setTimeout(() => {
                runCipherDecryption(card, index);
              }, index * 180); // Stagger cards: 0ms, 180ms, 360ms, 540ms
            });
            observer.unobserve(section);
          }
        });
      }, {
        threshold: 0.25,
        rootMargin: '0px 0px -40px 0px'
      });

      observer.observe(section);
    } else {
      // Fallback
      cards.forEach((card, index) => {
        setTimeout(() => runCipherDecryption(card, index), index * 180);
      });
    }

    // 3D Glass Perspective Tilt & Interactive Glare Tracking
    cards.forEach(card => {
      const glare = card.querySelector('.vg-card-glass-glare');

      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const xNorm = (x / rect.width) - 0.5; // -0.5 to 0.5
        const yNorm = (y / rect.height) - 0.5;

        // 3D Tilt Angle (max ~12 deg)
        const tiltX = -yNorm * 12;
        const tiltY = xNorm * 12;

        card.style.transform = \`perspective(800px) rotateX(\${tiltX.toFixed(2)}deg) rotateY(\${tiltY.toFixed(2)}deg) translateY(-5px) scale3d(1.02, 1.02, 1.02)\`;

        if (glare) {
          glare.style.opacity = '1';
          glare.style.background = \`radial-gradient(circle at \${x}px \${y}px, rgba(0, 240, 255, 0.22) 0%, rgba(168, 85, 247, 0.08) 45%, transparent 70%)\`;
        }
      }, { passive: true });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(800px) rotateX(0deg) rotateY(0deg) translateY(0) scale3d(1, 1, 1)';
        if (glare) {
          glare.style.opacity = '0';
        }
      }, { passive: true });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMetricsCipher);
  } else {
    initMetricsCipher();
  }
})();
`;

const cipherJsPath = path.join(rootDir, 'assets', 'js', 'ecom-metric-cipher.js');
fs.writeFileSync(cipherJsPath, cipherJsCode, 'utf8');
console.log('Created assets/js/ecom-metric-cipher.js');

// Mirror JS
const mirrorJs1 = path.join(rootDir, 'public_html (1)', 'assets', 'js', 'ecom-metric-cipher.js');
const mirrorJs2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'js', 'ecom-metric-cipher.js');
if (fs.existsSync(path.dirname(mirrorJs1))) fs.writeFileSync(mirrorJs1, cipherJsCode, 'utf8');
if (fs.existsSync(path.dirname(mirrorJs2))) fs.writeFileSync(mirrorJs2, cipherJsCode, 'utf8');
console.log('Mirrored ecom-metric-cipher.js to public_html mirrors.');

// 2. Add Styles for Laser Scan, 3D Depth, and Cipher Decrypt Flash in assets/css/color.css
const cssPath = path.join(rootDir, 'assets', 'css', 'color.css');
let cssContent = fs.readFileSync(cssPath, 'utf8');

const metricStyles = `
/* ==========================================================================
   AI LASER SCAN & CIPHER DECRYPT METRICS CARDS (3D GLASS DEPTH)
   ========================================================================== */
.vg-ecom-counter-card {
  position: relative !important;
  background: rgba(14, 8, 32, 0.78) !important;
  border: 1px solid rgba(168, 85, 247, 0.25) !important;
  border-radius: 18px !important;
  padding: 26px 22px 22px !important;
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.55), inset 0 1px 0 rgba(255, 255, 255, 0.1) !important;
  backdrop-filter: blur(16px) !important;
  -webkit-backdrop-filter: blur(16px) !important;
  transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease, box-shadow 0.25s ease !important;
  text-align: center !important;
  overflow: hidden !important;
  transform-style: preserve-3d;
  will-change: transform;
}

/* Corner Telemetry Bracket Accents */
.vg-ecom-counter-card::before {
  content: '';
  position: absolute;
  top: 8px;
  left: 8px;
  width: 10px;
  height: 10px;
  border-top: 1.5px solid rgba(0, 240, 255, 0.5);
  border-left: 1.5px solid rgba(0, 240, 255, 0.5);
  pointer-events: none;
}

.vg-ecom-counter-card::after {
  content: '';
  position: absolute;
  bottom: 8px;
  right: 8px;
  width: 10px;
  height: 10px;
  border-bottom: 1.5px solid rgba(168, 85, 247, 0.5);
  border-right: 1.5px solid rgba(168, 85, 247, 0.5);
  pointer-events: none;
}

.vg-ecom-counter-card:hover {
  border-color: #00F0FF !important;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.65), 0 0 25px rgba(0, 240, 255, 0.3) !important;
}

/* Interactive Dynamic Specular Glare */
.vg-card-glass-glare {
  position: absolute;
  inset: 0;
  border-radius: 18px;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: 2;
}

/* Sleek Electric Cyan Laser Scan Beam */
.vg-laser-scanner-line {
  position: absolute;
  top: 0;
  left: -20px;
  width: 3px;
  height: 100%;
  background: linear-gradient(180deg, transparent 0%, #00F0FF 25%, #ffffff 50%, #00F0FF 75%, transparent 100%);
  box-shadow: 0 0 16px #00F0FF, 0 0 30px rgba(0, 240, 255, 0.8), -6px 0 18px rgba(0, 240, 255, 0.35);
  opacity: 0;
  z-index: 5;
  pointer-events: none;
}

.vg-ecom-counter-card.is-scanning .vg-laser-scanner-line {
  opacity: 1;
  animation: vgLaserSweepAnim 0.75s cubic-bezier(0.25, 1, 0.5, 1) forwards;
}

@keyframes vgLaserSweepAnim {
  0% {
    transform: translateX(0);
    opacity: 0;
  }
  15% {
    opacity: 1;
  }
  85% {
    opacity: 1;
  }
  100% {
    transform: translateX(360px);
    opacity: 0;
  }
}

/* Custom Sharp Glowing SVG Icon Container */
.vg-ecom-counter-icon {
  width: 54px !important;
  height: 54px !important;
  margin: 0 auto 12px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  border-radius: 14px !important;
  background: rgba(255, 255, 255, 0.04) !important;
  border: 1px solid rgba(255, 255, 255, 0.09) !important;
  transition: all 0.3s ease !important;
  box-shadow: 0 0 14px rgba(0, 240, 255, 0.12) !important;
}

.vg-ecom-counter-card:hover .vg-ecom-counter-icon {
  transform: scale(1.08) translateZ(10px);
  border-color: #00F0FF !important;
  box-shadow: 0 0 20px rgba(0, 240, 255, 0.35) !important;
}

.vg-ecom-metric-svg {
  width: 38px;
  height: 38px;
  display: block;
}

/* Cipher Decrypted Value Display */
.vg-ecom-cipher-wrap {
  min-height: 44px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 0 6px 0;
}

.vg-ecom-cipher-val {
  font-size: 38px;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.5px;
  line-height: 1.1;
  font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  transition: color 0.25s ease, text-shadow 0.25s ease;
  text-shadow: 0 0 12px rgba(0, 240, 255, 0.25);
}

/* Subtle Cyber Violet Glow Flash Upon Locking In */
.vg-ecom-cipher-val.is-flash {
  color: #00F0FF !important;
  text-shadow: 0 0 24px #A855F7, 0 0 14px #00F0FF, 0 0 35px rgba(168, 85, 247, 0.8) !important;
  animation: vgCipherFlashPulse 0.8s ease-out forwards;
}

@keyframes vgCipherFlashPulse {
  0% {
    transform: scale(1.08);
    color: #00F0FF;
    text-shadow: 0 0 24px #A855F7, 0 0 14px #00F0FF, 0 0 35px rgba(168, 85, 247, 0.8);
  }
  50% {
    transform: scale(1.03);
    color: #ffffff;
    text-shadow: 0 0 18px rgba(168, 85, 247, 0.5), 0 0 10px rgba(0, 240, 255, 0.4);
  }
  100% {
    transform: scale(1);
    color: #ffffff;
    text-shadow: 0 0 12px rgba(0, 240, 255, 0.25);
  }
}

.vg-ecom-counter-label {
  font-size: 13.5px !important;
  color: #94a3b8 !important;
  margin: 0 !important;
  font-weight: 600 !important;
  letter-spacing: 0.2px !important;
}
`;

if (!cssContent.includes('AI LASER SCAN & CIPHER DECRYPT METRICS CARDS')) {
  cssContent += '\n' + metricStyles.trim() + '\n';
  fs.writeFileSync(cssPath, cssContent, 'utf8');
  console.log('Appended AI Laser Scan & Cipher Decrypt CSS to color.css');
} else {
  // Replace existing block
  const startIdx = cssContent.indexOf('/* ==========================================================================\n   AI LASER SCAN & CIPHER DECRYPT METRICS CARDS');
  const altStartIdx = cssContent.indexOf('/* ==========================================================================\r\n   AI LASER SCAN & CIPHER DECRYPT METRICS CARDS');
  const actualStart = startIdx !== -1 ? startIdx : altStartIdx;
  if (actualStart !== -1) {
    cssContent = cssContent.substring(0, actualStart) + metricStyles.trim() + '\n';
    fs.writeFileSync(cssPath, cssContent, 'utf8');
    console.log('Updated AI Laser Scan & Cipher Decrypt CSS in color.css');
  }
}

// Mirror color.css
const mirrorCss1 = path.join(rootDir, 'public_html (1)', 'assets', 'css', 'color.css');
const mirrorCss2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'css', 'color.css');
if (fs.existsSync(mirrorCss1)) fs.writeFileSync(mirrorCss1, cssContent, 'utf8');
if (fs.existsSync(mirrorCss2)) fs.writeFileSync(mirrorCss2, cssContent, 'utf8');
console.log('Synchronized color.css across mirrors.');

// 3. New HTML markup for the 4 Metrics Cards with Sharp Custom SVG Icons & Clean 1.2M+ Format
const newMetricsHtml = `        <!-- ==========================================================================
             FEATURE COUNTERS STRIP (AI LASER SCAN & CIPHER DECRYPT REVEAL)
             ========================================================================== -->
        <section class="vg-ecom-stats-section">
            <div class="container">
                <div class="row g-4">
                    <!-- Card 1: 250 Ecommerce Brands Served -->
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                        <div class="vg-ecom-counter-card" data-cipher-target="250">
                            <div class="vg-laser-scanner-line"></div>
                            <div class="vg-card-glass-glare"></div>
                            <div class="vg-ecom-counter-icon" title="Ecommerce Brands Network">
                                <svg class="vg-ecom-metric-svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Ecommerce Brands Icon">
                                    <defs>
                                        <linearGradient id="ecom-metric-grad-1" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#00F0FF"/>
                                            <stop offset="100%" stop-color="#A855F7"/>
                                        </linearGradient>
                                    </defs>
                                    <polygon points="24,5 41,15 41,33 24,43 7,33 7,15" fill="rgba(0, 240, 255, 0.08)" stroke="url(#ecom-metric-grad-1)" stroke-width="1.8"/>
                                    <path d="M 15 20 L 24 14 L 33 20 L 31 31 L 17 31 Z" fill="none" stroke="#00F0FF" stroke-width="1.6" stroke-linejoin="round"/>
                                    <path d="M 20 18 Q 24 13 28 18" fill="none" stroke="#A855F7" stroke-width="1.6" stroke-linecap="round"/>
                                    <circle cx="24" cy="24" r="2.4" fill="#00F0FF"/>
                                </svg>
                            </div>
                            <div class="vg-ecom-cipher-wrap">
                                <span class="vg-ecom-cipher-val">250</span>
                            </div>
                            <p class="vg-ecom-counter-label">Ecommerce Brands Served</p>
                        </div>
                    </div>

                    <!-- Card 2: 3,200 #1 Ranking Keywords -->
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                        <div class="vg-ecom-counter-card" data-cipher-target="3,200">
                            <div class="vg-laser-scanner-line"></div>
                            <div class="vg-card-glass-glare"></div>
                            <div class="vg-ecom-counter-icon" title="Top Ranked Keywords">
                                <svg class="vg-ecom-metric-svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Ranking Keywords Icon">
                                    <defs>
                                        <linearGradient id="ecom-metric-grad-2" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#A855F7"/>
                                            <stop offset="100%" stop-color="#00F0FF"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="24" cy="24" r="18" fill="rgba(168, 85, 247, 0.08)" stroke="url(#ecom-metric-grad-2)" stroke-width="1.6" stroke-dasharray="6 3"/>
                                    <circle cx="24" cy="24" r="11" fill="none" stroke="#00F0FF" stroke-width="1.4" opacity="0.8"/>
                                    <line x1="24" y1="3" x2="24" y2="9" stroke="#00F0FF" stroke-width="1.8" stroke-linecap="round"/>
                                    <line x1="24" y1="39" x2="24" y2="45" stroke="#00F0FF" stroke-width="1.8" stroke-linecap="round"/>
                                    <line x1="3" y1="24" x2="9" y2="24" stroke="#00F0FF" stroke-width="1.8" stroke-linecap="round"/>
                                    <line x1="39" y1="24" x2="45" y2="24" stroke="#00F0FF" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M 24 17 L 25.5 21 L 29.5 21.5 L 26.5 24.5 L 27.5 29 L 24 26.5 L 20.5 29 L 21.5 24.5 L 18.5 21.5 L 22.5 21 Z" fill="#00F0FF" stroke="#A855F7" stroke-width="0.8"/>
                                </svg>
                            </div>
                            <div class="vg-ecom-cipher-wrap">
                                <span class="vg-ecom-cipher-val">3,200</span>
                            </div>
                            <p class="vg-ecom-counter-label">#1 Ranking Keywords</p>
                        </div>
                    </div>

                    <!-- Card 3: 1.2M+ Monthly Organic Clicks (Formatted Cleanly as 1.2M+) -->
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".6s">
                        <div class="vg-ecom-counter-card" data-cipher-target="1.2M+">
                            <div class="vg-laser-scanner-line"></div>
                            <div class="vg-card-glass-glare"></div>
                            <div class="vg-ecom-counter-icon" title="Monthly Organic Clicks Traffic">
                                <svg class="vg-ecom-metric-svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Organic Clicks Icon">
                                    <defs>
                                        <linearGradient id="ecom-metric-grad-3" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#10B981"/>
                                            <stop offset="100%" stop-color="#00F0FF"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="24" cy="24" r="18" fill="rgba(16, 185, 129, 0.08)" stroke="url(#ecom-metric-grad-3)" stroke-width="1.6"/>
                                    <path d="M 11 29 L 18 21 L 24 26 L 31 16 L 37 16" fill="none" stroke="#00F0FF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <polyline points="33,13 37,16 33,20" fill="none" stroke="#00F0FF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="31" cy="16" r="3" fill="#10B981"/>
                                    <circle cx="18" cy="21" r="2.2" fill="#00F0FF"/>
                                </svg>
                            </div>
                            <div class="vg-ecom-cipher-wrap">
                                <span class="vg-ecom-cipher-val">1.2M+</span>
                            </div>
                            <p class="vg-ecom-counter-label">Monthly Organic Clicks</p>
                        </div>
                    </div>

                    <!-- Card 4: 3X Increase in Sales -->
                    <div class="col-xl-3 col-lg-6 col-md-6 wow fadeInUp" data-wow-delay=".8s">
                        <div class="vg-ecom-counter-card" data-cipher-target="3X">
                            <div class="vg-laser-scanner-line"></div>
                            <div class="vg-card-glass-glare"></div>
                            <div class="vg-ecom-counter-icon" title="Sales Multiplier Growth">
                                <svg class="vg-ecom-metric-svg" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Sales Increase Icon">
                                    <defs>
                                        <linearGradient id="ecom-metric-grad-4" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#F59E0B"/>
                                            <stop offset="100%" stop-color="#00F0FF"/>
                                        </linearGradient>
                                    </defs>
                                    <rect x="7" y="7" width="34" height="34" rx="10" fill="rgba(245, 158, 11, 0.08)" stroke="url(#ecom-metric-grad-4)" stroke-width="1.6"/>
                                    <path d="M 16 32 L 24 16 L 32 32 L 24 28 Z" fill="rgba(0, 240, 255, 0.15)" stroke="#00F0FF" stroke-width="1.8" stroke-linejoin="round"/>
                                    <path d="M 21 31 L 24 38 L 27 31" fill="none" stroke="#F59E0B" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="12" y1="16" x2="16" y2="16" stroke="#00F0FF" stroke-width="1.5" stroke-linecap="round"/>
                                    <line x1="32" y1="16" x2="36" y2="16" stroke="#00F0FF" stroke-width="1.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <div class="vg-ecom-cipher-wrap">
                                <span class="vg-ecom-cipher-val">3X</span>
                            </div>
                            <p class="vg-ecom-counter-label">Increase in Sales</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>`;

// 4. Update the HTML files
const htmlFiles = [
  path.join(rootDir, 'ecommerce-seo-service.html'),
  path.join(rootDir, 'public_html (1)', 'ecommerce-seo-service.html'),
  path.join(rootDir, 'public_html (1)', 'website', 'ecommerce-seo-service.html')
];

htmlFiles.forEach((file) => {
  if (!fs.existsSync(file)) return;
  let html = fs.readFileSync(file, 'utf8');

  // Replace old section block
  const oldStatsRegex = /<!-- =+[\r\n\s]+FEATURE COUNTERS STRIP[\s\S]*?<\/section>/;
  if (oldStatsRegex.test(html)) {
    html = html.replace(oldStatsRegex, newMetricsHtml.trim());
    console.log('Replaced metrics section in:', file);
  } else {
    console.error('Could not find old stats section in:', file);
  }

  // Include script ecom-metric-cipher.js
  if (!html.includes('assets/js/ecom-metric-cipher.js')) {
    html = html.replace(
      '<script src="assets/js/ecom-vortex-canvas.js"></script>',
      '<script src="assets/js/ecom-vortex-canvas.js"></script>\n        <!-- Dedicated AI Laser Scan & Cipher Decrypt Metrics Engine -->\n        <script src="assets/js/ecom-metric-cipher.js"></script>'
    );
    console.log('Added ecom-metric-cipher.js script tag to:', file);
  }

  fs.writeFileSync(file, html, 'utf8');
});

console.log('--- Upgrade Applied Successfully ---');

