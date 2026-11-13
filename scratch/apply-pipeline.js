const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');

console.log('--- Applying Engineering Deployment Pipeline Upgrade ---');

// 1. Create assets/js/webdev-circuit.js
const circuitJsContent = `/**
 * ============================================================================
 * VG DIGITAL FALCON — ENGINEERING DEPLOYMENT PIPELINE CIRCUIT TRACK
 * File: assets/js/webdev-circuit.js
 * 
 * Features:
 * - Detects when the pipeline section scrolls into view via IntersectionObserver.
 * - Triggers sequential laser ignition from Step 01 -> Step 02 -> Step 03 -> Step 04 -> Step 05 -> Step 06.
 * - Coordinates glowing energy pulses along the Electric Cyan & Cyber Violet circuit bus.
 * - Provides interactive hover highlighting across connected pipeline nodes.
 * ============================================================================
 */

(function () {
  'use strict';

  function initPipelineCircuit() {
    const section = document.querySelector('.vg-pipeline-section');
    if (!section) return;

    const cards = section.querySelectorAll('.vg-pipeline-card');
    const laserTrack = section.querySelector('.vg-pipeline-laser-bus');
    let hasAnimated = false;

    // Sequential trigger function
    function runSequentialPulse() {
      if (hasAnimated) return;
      hasAnimated = true;
      section.classList.add('vg-pipeline-active');

      cards.forEach((card, index) => {
        setTimeout(() => {
          card.classList.add('is-ignited');
          // Brief power surge highlight
          card.classList.add('is-pulsing');
          setTimeout(() => {
            card.classList.remove('is-pulsing');
          }, 600);
        }, index * 380); // Smooth sequential flow: 0ms, 380ms, 760ms, 1140ms, 1520ms, 1900ms
      });
    }

    // IntersectionObserver to start animation as user scrolls into view
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !hasAnimated) {
            runSequentialPulse();
            observer.unobserve(section);
          }
        });
      }, {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
      });

      observer.observe(section);
    } else {
      // Fallback for older browsers
      runSequentialPulse();
    }

    // Interactive Hover: highlights node and intensifies surrounding circuit
    cards.forEach((card) => {
      card.addEventListener('mouseenter', () => {
        card.classList.add('is-hovered');
      });
      card.addEventListener('mouseleave', () => {
        card.classList.remove('is-hovered');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPipelineCircuit);
  } else {
    initPipelineCircuit();
  }
})();
`;

const jsCircuitPath = path.join(rootDir, 'assets', 'js', 'webdev-circuit.js');
fs.writeFileSync(jsCircuitPath, circuitJsContent, 'utf8');
console.log('Created assets/js/webdev-circuit.js');

// Sync to mirrors
const mirrorJs1 = path.join(rootDir, 'public_html (1)', 'assets', 'js', 'webdev-circuit.js');
const mirrorJs2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'js', 'webdev-circuit.js');
if (fs.existsSync(path.dirname(mirrorJs1))) fs.writeFileSync(mirrorJs1, circuitJsContent, 'utf8');
if (fs.existsSync(path.dirname(mirrorJs2))) fs.writeFileSync(mirrorJs2, circuitJsContent, 'utf8');
console.log('Mirrored webdev-circuit.js to public_html mirrors.');

// 2. Add pipeline CSS to assets/css/color.css
const cssPath = path.join(rootDir, 'assets', 'css', 'color.css');
let cssContent = fs.readFileSync(cssPath, 'utf8');

const pipelineCssBlock = `
/* ==========================================================================
   ENGINEERING DEPLOYMENT PIPELINE: CONNECTED LASER CIRCUIT TRACK
   ========================================================================== */
.vg-pipeline-section {
  position: relative !important;
  background-color: #07070F !important;
  background: radial-gradient(circle at 50% 15%, rgba(0, 240, 255, 0.05) 0%, rgba(168, 85, 247, 0.04) 45%, #07070F 75%) !important;
  padding: 48px 0 42px !important;
  border-bottom: 1px solid rgba(168, 85, 247, 0.15) !important;
  overflow: hidden;
}

/* Phase Headers (Compact & Integrated) */
.vg-pipeline-phase-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.vg-pipeline-phase-badge {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  padding: 4px 12px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  white-space: nowrap;
}

.vg-pipeline-phase-badge.phase-1 {
  background: rgba(0, 240, 255, 0.10);
  border: 1px solid rgba(0, 240, 255, 0.35);
  color: #00F0FF;
}

.vg-pipeline-phase-badge.phase-2 {
  background: rgba(168, 85, 247, 0.12);
  border: 1px solid rgba(168, 85, 247, 0.4);
  color: #c084fc;
}

.vg-phase-pip {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  box-shadow: 0 0 8px currentColor;
}

.vg-pipeline-phase-line {
  flex-grow: 1;
  height: 1px;
  background: linear-gradient(90deg, rgba(0, 240, 255, 0.35), rgba(168, 85, 247, 0.25), transparent);
}

/* Pipeline Container & Positioning */
.vg-pipeline-grid-wrap {
  position: relative;
  z-index: 2;
}

/* Connected Laser Circuit Track Line (Desktop Horizontal + Cross-Phase Bus) */
@media (min-width: 992px) {
  /* Connecting laser trace between cards in Row 1 & Row 2 */
  .vg-pipeline-row {
    position: relative;
  }

  .vg-pipeline-col {
    position: relative;
  }

  /* Laser line connecting cards 1->2 and 2->3, and 4->5 and 5->6 */
  .vg-pipeline-col:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 42px;
    right: -13px;
    width: 26px;
    height: 2px;
    background: linear-gradient(90deg, #00F0FF, #A855F7);
    box-shadow: 0 0 8px rgba(0, 240, 255, 0.6), 0 0 14px rgba(168, 85, 247, 0.4);
    z-index: 5;
    pointer-events: none;
  }

  /* Animated pulse dot traveling between cards */
  .vg-pipeline-col:not(:last-child)::before {
    content: '';
    position: absolute;
    top: 40px;
    right: -13px;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #ffffff;
    box-shadow: 0 0 10px #00F0FF, 0 0 16px #A855F7;
    z-index: 6;
    pointer-events: none;
    animation: vgLaserPulseFlow 2.8s linear infinite;
  }

  /* Phase Conduit Bridge connecting Row 1 (Step 3) to Row 2 (Step 4) */
  .vg-pipeline-bridge-conduit {
    position: absolute;
    right: 25px;
    top: 48%;
    transform: translateY(-50%);
    width: 2px;
    height: 42px;
    background: linear-gradient(180deg, #A855F7, #00F0FF);
    box-shadow: 0 0 10px rgba(168, 85, 247, 0.6);
    z-index: 4;
  }
}

@keyframes vgLaserPulseFlow {
  0% { transform: translateX(-14px); opacity: 0; }
  25% { opacity: 1; }
  75% { opacity: 1; }
  100% { transform: translateX(14px); opacity: 0; }
}

/* Compact Pipeline Step Card */
.vg-pipeline-card {
  background: rgba(14, 8, 32, 0.78);
  border: 1px solid rgba(168, 85, 247, 0.22);
  border-radius: 16px;
  padding: 18px 20px 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  overflow: hidden;
}

/* Sequential Ignition & Surge Highlight */
.vg-pipeline-card.is-ignited {
  border-color: rgba(0, 240, 255, 0.35);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5), 0 0 16px rgba(0, 240, 255, 0.12);
}

.vg-pipeline-card.is-pulsing {
  border-color: #00F0FF !important;
  box-shadow: 0 0 25px rgba(0, 240, 255, 0.45), inset 0 0 15px rgba(168, 85, 247, 0.25) !important;
  transform: translateY(-3px) scale(1.015);
}

.vg-pipeline-card:hover,
.vg-pipeline-card.is-hovered {
  transform: translateY(-4px);
  background: rgba(22, 12, 48, 0.92);
  border-color: #00F0FF !important;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6), 0 0 22px rgba(0, 240, 255, 0.3) !important;
}

/* Top Socket Bar: Glow Circle + Badge + Tech Icon */
.vg-pipeline-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.vg-pipeline-glow-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(20, 10, 44, 0.95);
  border: 1.5px solid #00F0FF;
  box-shadow: 0 0 12px rgba(0, 240, 255, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 800;
  color: #ffffff;
  flex-shrink: 0;
  transition: all 0.25s ease;
}

.vg-pipeline-card:hover .vg-pipeline-glow-circle {
  background: #00F0FF;
  color: #07070F;
  box-shadow: 0 0 16px #00F0FF;
}

.vg-pipeline-badge {
  background: rgba(168, 85, 247, 0.14);
  border: 1px solid rgba(168, 85, 247, 0.35);
  color: #c084fc;
  font-size: 10px;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 6px;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  line-height: 1.2;
}

/* Crisp Technology Icon (Replaces duplicate emoji image) */
.vg-pipeline-tech-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: var(--icon-color, #00F0FF);
  transition: all 0.25s ease;
  box-shadow: 0 0 10px rgba(0, 240, 255, 0.12);
}

.vg-pipeline-card:hover .vg-pipeline-tech-icon {
  border-color: var(--icon-color, #00F0FF);
  box-shadow: 0 0 14px var(--icon-color, #00F0FF);
  transform: scale(1.08);
}

/* Card Title & Description */
.vg-pipeline-title {
  font-size: 17px;
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 6px 0;
  line-height: 1.3;
  text-align: left;
}

.vg-pipeline-desc {
  font-size: 12.8px;
  line-height: 1.5;
  color: #cbd5e1;
  margin: 0 0 12px 0;
  text-align: left;
  flex-grow: 1;
}

/* Compact Deliverable Pill at Bottom */
.vg-pipeline-deliverable {
  margin-top: auto;
  padding: 6px 10px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(0, 240, 255, 0.22);
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  color: #94a3b8;
  text-align: left;
  line-height: 1.3;
  transition: all 0.25s ease;
}

.vg-pipeline-deliverable .deliverable-label {
  font-weight: 700;
  color: #cbd5e1;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.3px;
  flex-shrink: 0;
}

.vg-pipeline-deliverable .deliverable-val {
  color: #ffffff;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.vg-pipeline-card:hover .vg-pipeline-deliverable {
  background: rgba(0, 240, 255, 0.08);
  border-color: #00F0FF;
}

.vg-pipeline-card:hover .vg-pipeline-deliverable .deliverable-val {
  color: #00F0FF;
}

/* Responsive Handling */
@media (max-width: 991px) {
  .vg-pipeline-section {
    padding: 38px 0 !important;
  }
  .vg-pipeline-card {
    padding: 16px 18px 14px;
  }
  .vg-pipeline-deliverable .deliverable-val {
    white-space: normal;
  }
}
`;

// Append pipeline CSS block to color.css
if (!cssContent.includes('ENGINEERING DEPLOYMENT PIPELINE: CONNECTED LASER CIRCUIT TRACK')) {
  cssContent += '\n' + pipelineCssBlock.trim() + '\n';
  fs.writeFileSync(cssPath, cssContent, 'utf8');
  console.log('Appended pipeline CSS to color.css');
} else {
  // Replace existing block
  const startIdx = cssContent.indexOf('/* ==========================================================================\n   ENGINEERING DEPLOYMENT PIPELINE: CONNECTED LASER CIRCUIT TRACK');
  const altStartIdx = cssContent.indexOf('/* ==========================================================================\r\n   ENGINEERING DEPLOYMENT PIPELINE: CONNECTED LASER CIRCUIT TRACK');
  const actualStart = startIdx !== -1 ? startIdx : altStartIdx;
  if (actualStart !== -1) {
    cssContent = cssContent.substring(0, actualStart) + pipelineCssBlock.trim() + '\n';
    fs.writeFileSync(cssPath, cssContent, 'utf8');
    console.log('Updated existing pipeline CSS in color.css');
  }
}

// Sync CSS across mirrors
const mirrorCss1 = path.join(rootDir, 'public_html (1)', 'assets', 'css', 'color.css');
const mirrorCss2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'css', 'color.css');
if (fs.existsSync(mirrorCss1)) fs.writeFileSync(mirrorCss1, cssContent, 'utf8');
if (fs.existsSync(mirrorCss2)) fs.writeFileSync(mirrorCss2, cssContent, 'utf8');
console.log('Mirrored color.css across all mirrors.');


// 3. Update HTML in web-development.html & mirrors
const newPipelineHtml = `    <!-- ==========================================================================
         ENGINEERING DEPLOYMENT PIPELINE: 6-STAGE AGILE WORKFLOW
         ========================================================================== -->
    <section class="vg-seo-process-section vg-pipeline-section" id="how-it-works">
        <div class="container position-relative">
            <!-- Section Header -->
            <div class="text-center max-w-750 mx-auto mb-4 pb-1">
                <span class="vg-pill-badge wow fadeInUp" data-wow-delay=".1s"><i class="fa-solid fa-microchip me-2"></i> Agile Engineering Lifecycle</span>
                <h2 class="text-white mt-2 wow fadeInUp" data-wow-delay=".2s" style="font-size: clamp(26px, 2.4vw, 36px); font-weight: 800;">
                    How We Build Websites <span class="vg-gradient-text">That Actually Deliver Results?</span>
                </h2>
                <p class="wow fadeInUp" data-wow-delay=".3s" style="color: #cbd5e1; font-size: 15px; line-height: 1.6; margin-top: 10px;">
                    Want a website that loads fast, looks amazing, and converts visitors into customers?<br class="d-none d-lg-block">Here’s how our professional web development services work — simple, transparent, and built for results.
                </p>
            </div>

            <div class="vg-pipeline-grid-wrap">
                <!-- Algorithm Phase 1 Header -->
                <div class="vg-pipeline-phase-wrap wow fadeInUp" data-wow-delay=".15s">
                    <span class="vg-pipeline-phase-badge phase-1">
                        <span class="vg-phase-pip"></span> Phase 1: Architecture, Strategy &amp; UI/UX Blueprint
                    </span>
                    <div class="vg-pipeline-phase-line"></div>
                </div>

                <!-- Step Cards Row 1 (Steps 01 - 03) -->
                <div class="row g-3 g-xl-4 mb-4 vg-pipeline-row">
                    <!-- Step 1: Strategy Session -->
                    <div class="col-lg-4 col-md-6 vg-pipeline-col wow fadeInUp" data-wow-delay=".2s">
                        <div class="vg-pipeline-card" data-step="1">
                            <div class="vg-pipeline-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="vg-pipeline-glow-circle">01</div>
                                    <span class="vg-pipeline-badge">Step 01</span>
                                </div>
                                <div class="vg-pipeline-tech-icon" style="--icon-color: #00F0FF;">
                                    <i class="fa-solid fa-chess-knight"></i>
                                </div>
                            </div>
                            <h3 class="vg-pipeline-title">Strategy Session</h3>
                            <p class="vg-pipeline-desc">Let’s talk about your goals. We dive deep into your business, understand your audience, and map out the best strategy.</p>
                            <div class="vg-pipeline-deliverable">
                                <i class="fa-solid fa-file-contract text-cyan me-1"></i>
                                <span class="deliverable-label">Deliverable:</span>
                                <span class="deliverable-val">Strategic Architecture &amp; Roadmap</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Selecting TechStack -->
                    <div class="col-lg-4 col-md-6 vg-pipeline-col wow fadeInUp" data-wow-delay=".3s">
                        <div class="vg-pipeline-card" data-step="2">
                            <div class="vg-pipeline-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="vg-pipeline-glow-circle">02</div>
                                    <span class="vg-pipeline-badge">Step 02</span>
                                </div>
                                <div class="vg-pipeline-tech-icon" style="--icon-color: #38BDF8;">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                            </div>
                            <h3 class="vg-pipeline-title">Selecting TechStack</h3>
                            <p class="vg-pipeline-desc">From HTML to React / Node.js, we help you choose the right tools — no bloated code or unnecessary features.</p>
                            <div class="vg-pipeline-deliverable">
                                <i class="fa-solid fa-microchip text-sky me-1"></i>
                                <span class="deliverable-label">Deliverable:</span>
                                <span class="deliverable-val">Optimized Tech Stack Specification</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: UI/UX Design -->
                    <div class="col-lg-4 col-md-6 vg-pipeline-col wow fadeInUp" data-wow-delay=".4s">
                        <div class="vg-pipeline-card" data-step="3">
                            <div class="vg-pipeline-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="vg-pipeline-glow-circle">03</div>
                                    <span class="vg-pipeline-badge">Step 03</span>
                                </div>
                                <div class="vg-pipeline-tech-icon" style="--icon-color: #A855F7;">
                                    <i class="fa-solid fa-bezier-curve"></i>
                                </div>
                            </div>
                            <h3 class="vg-pipeline-title">UI/UX Design</h3>
                            <p class="vg-pipeline-desc">Our design team creates user experiences that are beautiful, intuitive, and conversion-focused in Figma.</p>
                            <div class="vg-pipeline-deliverable">
                                <i class="fa-solid fa-pen-ruler text-violet me-1"></i>
                                <span class="deliverable-label">Deliverable:</span>
                                <span class="deliverable-val">Interactive Figma Prototype</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Algorithm Phase 2 Header -->
                <div class="vg-pipeline-phase-wrap wow fadeInUp mt-3" data-wow-delay=".15s">
                    <span class="vg-pipeline-phase-badge phase-2">
                        <span class="vg-phase-pip"></span> Phase 2: Full-Stack Engineering, QA &amp; Continuous Growth
                    </span>
                    <div class="vg-pipeline-phase-line"></div>
                </div>

                <!-- Step Cards Row 2 (Steps 04 - 06) -->
                <div class="row g-3 g-xl-4 vg-pipeline-row">
                    <!-- Step 4: Full-Stack Development -->
                    <div class="col-lg-4 col-md-6 vg-pipeline-col wow fadeInUp" data-wow-delay=".2s">
                        <div class="vg-pipeline-card" data-step="4">
                            <div class="vg-pipeline-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="vg-pipeline-glow-circle">04</div>
                                    <span class="vg-pipeline-badge">Step 04</span>
                                </div>
                                <div class="vg-pipeline-tech-icon" style="--icon-color: #00F0FF;">
                                    <i class="fa-solid fa-code"></i>
                                </div>
                            </div>
                            <h3 class="vg-pipeline-title">Full-Stack Development</h3>
                            <p class="vg-pipeline-desc">Your website is built by our full-stack developers with clean code, fast loading times, and scalability in mind.</p>
                            <div class="vg-pipeline-deliverable">
                                <i class="fa-solid fa-code-branch text-cyan me-1"></i>
                                <span class="deliverable-label">Deliverable:</span>
                                <span class="deliverable-val">Clean Production Code &amp; API Integration</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: QA Testing & Launch -->
                    <div class="col-lg-4 col-md-6 vg-pipeline-col wow fadeInUp" data-wow-delay=".3s">
                        <div class="vg-pipeline-card" data-step="5">
                            <div class="vg-pipeline-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="vg-pipeline-glow-circle">05</div>
                                    <span class="vg-pipeline-badge">Step 05</span>
                                </div>
                                <div class="vg-pipeline-tech-icon" style="--icon-color: #10B981;">
                                    <i class="fa-solid fa-vial-circle-check"></i>
                                </div>
                            </div>
                            <h3 class="vg-pipeline-title">QA Testing &amp; Launch</h3>
                            <p class="vg-pipeline-desc">Before launch, we run a full suite of automated cross-browser tests, speed audits, security checks, and zero-downtime deployment.</p>
                            <div class="vg-pipeline-deliverable">
                                <i class="fa-solid fa-rocket text-emerald me-1"></i>
                                <span class="deliverable-label">Deliverable:</span>
                                <span class="deliverable-val">100% QA Audit &amp; Live Server Deployment</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 6: Monthly Growth & Reporting -->
                    <div class="col-lg-4 col-md-6 vg-pipeline-col wow fadeInUp" data-wow-delay=".4s">
                        <div class="vg-pipeline-card" data-step="6">
                            <div class="vg-pipeline-card-top">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="vg-pipeline-glow-circle">06</div>
                                    <span class="vg-pipeline-badge">Step 06</span>
                                </div>
                                <div class="vg-pipeline-tech-icon" style="--icon-color: #F59E0B;">
                                    <i class="fa-solid fa-chart-line"></i>
                                </div>
                            </div>
                            <h3 class="vg-pipeline-title">Monthly Growth &amp; Reporting</h3>
                            <p class="vg-pipeline-desc">We don’t stop at launch. Our custom website development agency offers ongoing support, updates, and continuous growth strategies.</p>
                            <div class="vg-pipeline-deliverable">
                                <i class="fa-solid fa-chart-pie text-amber me-1"></i>
                                <span class="deliverable-label">Deliverable:</span>
                                <span class="deliverable-val">Analytics Dashboard &amp; Monthly Growth Audits</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>`;

const htmlFiles = [
  path.join(rootDir, 'web-development.html'),
  path.join(rootDir, 'public_html (1)', 'web-development.html'),
  path.join(rootDir, 'public_html (1)', 'website', 'web-development.html')
];

htmlFiles.forEach((file) => {
  if (!fs.existsSync(file)) return;
  let html = fs.readFileSync(file, 'utf8');

  // Replace old section block
  const oldSectionRegex = /<!-- =+[\r\n\s]+HOW WE BUILD WEBSITES[\s\S]*?<\/section>/;
  if (oldSectionRegex.test(html)) {
    html = html.replace(oldSectionRegex, newPipelineHtml.trim());
    console.log(`Replaced process section in ${file}`);
  } else {
    console.error(`Could not match old process section in ${file}`);
  }

  // Ensure script webdev-circuit.js is included before main.js or closing body
  if (!html.includes('webdev-circuit.js')) {
    html = html.replace(
      '<script src="assets/js/webdev-services-showcase.js"></script>',
      '<script src="assets/js/webdev-services-showcase.js"></script>\n    <!-- Dedicated Web Development Laser Circuit Pipeline Track -->\n    <script src="assets/js/webdev-circuit.js"></script>'
    );
    console.log(`Added webdev-circuit.js script tag to ${file}`);
  }

  fs.writeFileSync(file, html, 'utf8');
});

console.log('--- Engineering Deployment Pipeline Upgrade Complete ---');

