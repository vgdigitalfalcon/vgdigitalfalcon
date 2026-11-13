const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');

console.log('Starting upgrade script...');

// 1. Update assets/js/webdev-services-showcase.js
const jsPath = path.join(rootDir, 'assets', 'js', 'webdev-services-showcase.js');
let jsContent = fs.readFileSync(jsPath, 'utf8');

// Define new renderDefaultRobot function with high-end 3D dtapex.com-style robot mascot
const newRenderDefaultRobot = `  // Helper to render Default State (High-End 3D/Lottie-Style Waving AI Robot Mascot inspired by dtapex.com)
  function renderDefaultRobot(previewEl, onReset) {
    if (!previewEl) return;

    previewEl.style.opacity = '0';
    previewEl.style.transform = 'translateY(6px)';

    setTimeout(() => {
      previewEl.innerHTML = \`
        <div class="vg-robot-showcase-view">
          <div class="vg-robot-flex-wrap">
            <!-- 3D Futuristic AI Robot Mascot (Reference: dtapex.com) -->
            <div class="vg-robot-character-col">
              <div class="dtapex-robot-stage">
                <!-- Ambient Cyber Glow Aura Behind Mascot -->
                <div class="dtapex-robot-halo"></div>

                <!-- Floating Mascot Speech Bubble ("Hi!") -->
                <div class="dtapex-speech-bubble">
                  <span class="dtapex-wave-hand">👋</span> <strong>"Hi!"</strong>
                </div>

                <!-- Sleek 3D Articulated Robot Character SVG -->
                <svg class="dtapex-robot-svg" viewBox="0 0 240 280" width="180" height="210" xmlns="http://www.w3.org/2000/svg" aria-label="Futuristic 3D AI Robot Mascot">
                  <defs>
                    <!-- Cyan Glow Filter -->
                    <filter id="dtapex-cyan-glow" x="-30%" y="-30%" width="160%" height="160%">
                      <feGaussianBlur stdDeviation="4.5" result="blur" />
                      <feComposite in="SourceGraphic" in2="blur" operator="over" />
                    </filter>

                    <!-- Violet Glow Filter -->
                    <filter id="dtapex-violet-glow" x="-30%" y="-30%" width="160%" height="160%">
                      <feGaussianBlur stdDeviation="5.5" result="blur" />
                      <feComposite in="SourceGraphic" in2="blur" operator="over" />
                    </filter>

                    <!-- Volumetric Pearlescent Metallic Chassis Gradient (3D Sphere Lighting) -->
                    <radialGradient id="dtapex-pearl-white" cx="35%" cy="30%" r="70%">
                      <stop offset="0%" stop-color="#FFFFFF" />
                      <stop offset="35%" stop-color="#F1F5F9" />
                      <stop offset="70%" stop-color="#CBD5E1" />
                      <stop offset="95%" stop-color="#94A3B8" />
                      <stop offset="100%" stop-color="#64748B" />
                    </radialGradient>

                    <!-- Cyber Titanium Torso Plates -->
                    <linearGradient id="dtapex-cyber-armor" x1="0%" y1="0%" x2="100%" y2="100%">
                      <stop offset="0%" stop-color="#2D1B69" />
                      <stop offset="50%" stop-color="#160D38" />
                      <stop offset="100%" stop-color="#070314" />
                    </linearGradient>

                    <!-- Curved Glass Visor 3D Specular Sheen -->
                    <radialGradient id="dtapex-visor-glass" cx="40%" cy="30%" r="75%">
                      <stop offset="0%" stop-color="#1E1242" />
                      <stop offset="55%" stop-color="#0E0722" />
                      <stop offset="100%" stop-color="#04010A" />
                    </radialGradient>

                    <linearGradient id="dtapex-glass-sheen" x1="15%" y1="0%" x2="85%" y2="100%">
                      <stop offset="0%" stop-color="rgba(255, 255, 255, 0.65)" />
                      <stop offset="30%" stop-color="rgba(0, 240, 255, 0.25)" />
                      <stop offset="70%" stop-color="transparent" />
                    </linearGradient>

                    <!-- Glowing Gyroscopic Reactor Core -->
                    <radialGradient id="dtapex-reactor-glow" cx="50%" cy="50%" r="50%">
                      <stop offset="0%" stop-color="#FFFFFF" />
                      <stop offset="35%" stop-color="#00F0FF" />
                      <stop offset="75%" stop-color="#A855F7" />
                      <stop offset="100%" stop-color="transparent" />
                    </radialGradient>

                    <!-- Magnetic Ion Thruster Flame -->
                    <linearGradient id="dtapex-ion-flame-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                      <stop offset="0%" stop-color="#00F0FF" stop-opacity="0.95" />
                      <stop offset="60%" stop-color="#A855F7" stop-opacity="0.6" />
                      <stop offset="100%" stop-color="#00F0FF" stop-opacity="0" />
                    </linearGradient>
                  </defs>

                  <!-- Ground Soft Kinetic Shadow -->
                  <ellipse class="dtapex-robot-shadow" cx="120" cy="265" rx="52" ry="7.5" fill="rgba(0, 240, 255, 0.24)" filter="url(#dtapex-cyan-glow)" />

                  <!-- Continuous Hover Floating Group (60fps) -->
                  <g class="dtapex-robot-floater">
                    
                    <!-- Ion Anti-Gravity Thruster Glow & Stream -->
                    <g class="dtapex-thruster-group">
                      <polygon points="106,220 134,220 125,248 115,248" fill="url(#dtapex-ion-flame-grad)" class="dtapex-ion-flame" />
                      <ellipse cx="120" cy="221" rx="16" ry="4" fill="#00F0FF" filter="url(#dtapex-cyan-glow)" />
                      <ellipse cx="120" cy="220" rx="9" ry="2.2" fill="#FFFFFF" />
                    </g>

                    <!-- Cybernetic Torso & Lower Chassis Armor -->
                    <g id="dtapex-torso-group">
                      <!-- Lower Body Chassis Cup -->
                      <path d="M 92 188 C 92 216, 148 216, 148 188 Z" fill="url(#dtapex-cyber-armor)" stroke="#38BDF8" stroke-width="1.4" />
                      
                      <!-- Main Upper Torso (Volumetric Rounded Shield) -->
                      <rect x="76" y="126" width="88" height="74" rx="28" fill="url(#dtapex-pearl-white)" stroke="rgba(255,255,255,0.7)" stroke-width="1.8" />
                      
                      <!-- Side Carbon Accents -->
                      <path d="M 80 148 Q 72 165 80 182" fill="none" stroke="#A855F7" stroke-width="3" stroke-linecap="round" />
                      <path d="M 160 148 Q 168 165 160 182" fill="none" stroke="#A855F7" stroke-width="3" stroke-linecap="round" />

                      <!-- Chest Gyroscopic Reactor Enclosure -->
                      <rect x="94" y="142" width="52" height="46" rx="18" fill="url(#dtapex-cyber-armor)" stroke="#00F0FF" stroke-width="1.6" />
                      
                      <!-- Rotating Gyroscopic Outer Ring -->
                      <circle cx="120" cy="165" r="17" fill="none" stroke="#00F0FF" stroke-width="1.6" stroke-dasharray="8 4" class="dtapex-reactor-ring" />
                      <!-- Violet Inner Ring -->
                      <circle cx="120" cy="165" r="12" fill="none" stroke="#A855F7" stroke-width="1.4" />
                      <!-- Pulsing Energetic Core -->
                      <circle cx="120" cy="165" r="8" fill="url(#dtapex-reactor-glow)" class="dtapex-reactor-pulse" />
                    </g>

                    <!-- Left Arm (Articulated at Side, Resting in Balance) -->
                    <g id="dtapex-left-arm">
                      <!-- Shoulder Joint -->
                      <circle cx="68" cy="142" r="11" fill="url(#dtapex-pearl-white)" stroke="#38BDF8" stroke-width="1.2" />
                      <circle cx="68" cy="142" r="4.5" fill="#160D38" />
                      <!-- Bicep & Forearm -->
                      <path d="M 66 150 Q 52 174 62 196" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="8" stroke-linecap="round" />
                      <path d="M 66 150 Q 52 174 62 196" fill="none" stroke="#00F0FF" stroke-width="1.5" stroke-linecap="round" opacity="0.6" />
                      <!-- Left Hand Sphere with Cyan Tip -->
                      <circle cx="63" cy="199" r="6.5" fill="url(#dtapex-pearl-white)" />
                      <circle cx="63" cy="201" r="2.8" fill="#00F0FF" />
                    </g>

                    <!-- 3D Spherical Cyber Helmet / Head Unit -->
                    <g id="dtapex-head-group">
                      <!-- Crown Antenna Beacon Mast -->
                      <line x1="120" y1="52" x2="120" y2="28" stroke="#00F0FF" stroke-width="3" stroke-linecap="round" />
                      <circle cx="120" cy="24" r="7.5" fill="#A855F7" filter="url(#dtapex-violet-glow)" />
                      <circle cx="120" cy="24" r="3.2" fill="#FFFFFF" />

                      <!-- Left & Right Cyber Ear Headset Pods with Neon Rings -->
                      <!-- Left Ear Pod -->
                      <rect x="58" y="70" width="10" height="28" rx="5" fill="#160D38" stroke="#00F0FF" stroke-width="1.4" />
                      <line x1="63" y1="74" x2="63" y2="94" stroke="#00F0FF" stroke-width="2" stroke-linecap="round" />
                      <!-- Right Ear Pod -->
                      <rect x="172" y="70" width="10" height="28" rx="5" fill="#160D38" stroke="#00F0FF" stroke-width="1.4" />
                      <line x1="177" y1="74" x2="177" y2="94" stroke="#00F0FF" stroke-width="2" stroke-linecap="round" />

                      <!-- Main Helmet Dome (Glossy White 3D Sphere) -->
                      <rect x="66" y="46" width="108" height="78" rx="36" fill="url(#dtapex-pearl-white)" stroke="rgba(255,255,255,0.85)" stroke-width="2" />

                      <!-- Aerodynamic Curved Glass Visor (Deep Obsidian with Cyan Border) -->
                      <rect x="76" y="58" width="88" height="52" rx="24" fill="url(#dtapex-visor-glass)" stroke="#00F0FF" stroke-width="1.8" />
                      
                      <!-- Visor Gloss Reflection Sheen Curve -->
                      <path d="M 86 64 Q 120 54 154 64 Q 120 72 86 64 Z" fill="url(#dtapex-glass-sheen)" />

                      <!-- Blinking Expressive Cyan Eyes & White Pupils -->
                      <g class="dtapex-robot-eyes">
                        <!-- Left Eye -->
                        <ellipse cx="102" cy="84" rx="7.5" ry="9.5" fill="#00F0FF" filter="url(#dtapex-cyan-glow)" />
                        <circle cx="104.5" cy="81.5" r="2.8" fill="#FFFFFF" />
                        <!-- Right Eye -->
                        <ellipse cx="138" cy="84" rx="7.5" ry="9.5" fill="#00F0FF" filter="url(#dtapex-cyan-glow)" />
                        <circle cx="140.5" cy="81.5" r="2.8" fill="#FFFFFF" />
                      </g>

                      <!-- Soft Friendly Violet Blush Accents -->
                      <circle cx="92" cy="97" r="3.6" fill="rgba(168, 85, 247, 0.7)" filter="url(#dtapex-violet-glow)" />
                      <circle cx="148" cy="97" r="3.6" fill="rgba(168, 85, 247, 0.7)" filter="url(#dtapex-violet-glow)" />
                    </g>

                    <!-- Right Arm: Sleek Articulated WAVING Hand (dtapex.com style) -->
                    <g class="dtapex-waving-arm">
                      <!-- Shoulder Joint (Pivot Center: 172, 142) -->
                      <circle cx="172" cy="142" r="11" fill="url(#dtapex-pearl-white)" stroke="#38BDF8" stroke-width="1.2" />
                      <circle cx="172" cy="142" r="4.5" fill="#160D38" />

                      <!-- Raised Forearm angled up in friendly wave -->
                      <path d="M 172 138 Q 200 120 196 92" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="8.5" stroke-linecap="round" />
                      <path d="M 172 138 Q 200 120 196 92" fill="none" stroke="#00F0FF" stroke-width="1.5" stroke-linecap="round" opacity="0.6" />

                      <!-- Articulated Wrist & Palm Sphere -->
                      <circle cx="196" cy="86" r="8" fill="url(#dtapex-pearl-white)" stroke="#00F0FF" stroke-width="1.4" />
                      <circle cx="196" cy="86" r="3.5" fill="#160D38" />

                      <!-- 4 Articulated Waving Cyber Fingers with Glowing Tips -->
                      <!-- Thumb -->
                      <path d="M 189 86 Q 180 82 178 77" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="2.8" stroke-linecap="round" />
                      <circle cx="178" cy="77" r="1.8" fill="#00F0FF" />
                      <!-- Index Finger -->
                      <path d="M 193 80 Q 190 70 188 62" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="2.8" stroke-linecap="round" />
                      <circle cx="188" cy="62" r="1.8" fill="#00F0FF" />
                      <!-- Middle Finger -->
                      <path d="M 197 79 Q 198 68 200 59" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="2.8" stroke-linecap="round" />
                      <circle cx="200" cy="59" r="1.8" fill="#00F0FF" />
                      <!-- Pinky Finger -->
                      <path d="M 201 82 Q 208 73 212 66" fill="none" stroke="url(#dtapex-pearl-white)" stroke-width="2.8" stroke-linecap="round" />
                      <circle cx="212" cy="66" r="1.8" fill="#00F0FF" />
                    </g>
                  </g>
                </svg>
              </div>
            </div>

            <!-- Greeting Prompt & Blueprint Overview -->
            <div class="vg-robot-intro-col">
              <div class="vg-robot-pill-badge mb-2">
                <span class="vg-live-pulse-dot" style="background: #00F0FF; box-shadow: 0 0 10px #00F0FF;"></span>
                <span>AI Solution Architect • Ready to Assist</span>
              </div>
              <h3 class="vg-robot-intro-title">Select a service on the left to explore how we engineer it</h3>
              <p class="vg-robot-intro-sub">
                Click or tap any of the 8 services on the left to inspect our custom tech blueprints, performance benchmarks, and deployment deliverables.
              </p>

              <!-- 3 Interactive Feature Metric Badges -->
              <div class="vg-robot-features-row">
                <div class="vg-robot-feature-chip">
                  <i class="fa-solid fa-gauge-high" style="color: #00F0FF;"></i>
                  <span>99+ Speed Vitals</span>
                </div>
                <div class="vg-robot-feature-chip">
                  <i class="fa-solid fa-code" style="color: #A855F7;"></i>
                  <span>Clean Scalable Code</span>
                </div>
                <div class="vg-robot-feature-chip">
                  <i class="fa-solid fa-shield-halved" style="color: #10B981;"></i>
                  <span>100% Code Ownership</span>
                </div>
              </div>

              <div class="vg-robot-action-prompt">
                <i class="fa-solid fa-arrow-pointer me-2" style="color: #00F0FF;"></i>
                <span>Click any service topic to reveal full architecture specs</span>
              </div>
            </div>
          </div>
        </div>
      \`;

      previewEl.style.opacity = '1';
      previewEl.style.transform = 'translateY(0)';
    }, 120);
  }`;

// Replace renderDefaultRobot in jsContent
const renderStartRegex = /\/\/ Helper to render Default State[\s\S]*?previewEl\.style\.transform = 'translateY\(0\)';\s*\}, 120\);\s*\}/;
if (renderStartRegex.test(jsContent)) {
  jsContent = jsContent.replace(renderStartRegex, newRenderDefaultRobot.trim());
  console.log('Successfully replaced renderDefaultRobot in webdev-services-showcase.js');
} else {
  console.error('Could not find renderDefaultRobot in jsContent!');
  process.exit(1);
}

// Write updated JS to all destinations
fs.writeFileSync(jsPath, jsContent, 'utf8');
const mirrorJs1 = path.join(rootDir, 'public_html (1)', 'assets', 'js', 'webdev-services-showcase.js');
const mirrorJs2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'js', 'webdev-services-showcase.js');
if (fs.existsSync(mirrorJs1)) fs.writeFileSync(mirrorJs1, jsContent, 'utf8');
if (fs.existsSync(mirrorJs2)) fs.writeFileSync(mirrorJs2, jsContent, 'utf8');
console.log('Synchronized JS across all mirrors.');


// 2. Update assets/css/color.css
const cssPath = path.join(rootDir, 'assets', 'css', 'color.css');
let cssContent = fs.readFileSync(cssPath, 'utf8');

// Define the updated and expanded CSS block for .vg-split-services-section, robot animations, anti-truncation, and zero-overlap top alignment
const newCssBlock = `
/* ==========================================================================
   VG DIGITAL FALCON — SPLIT-SCREEN SERVICE SHOWCASE & 3D DTAPEX ROBOT MASCOT
   ========================================================================== */
@media (min-width: 992px) {
  .vg-split-services-section .vg-services-heading {
    white-space: nowrap !important;
  }
}

.vg-split-services-section .vg-services-sub {
  color: #cbd5e1 !important;
  text-align: center !important;
}

.vg-split-services-section {
  position: relative !important;
  background-color: #07070F !important;
  background: radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.08) 0%, rgba(0, 240, 255, 0.04) 40%, #07070F 75%) !important;
  padding: 36px 0 32px !important;
  border-bottom: 1px solid rgba(168, 85, 247, 0.15) !important;
}

/* Vertical Spacing between Section Header Subtitle and Split Container */
.vg-split-showcase-row {
  margin-top: 1.85rem !important;
}

/* Tight 2-Column x 4-Row Mini-Grid on Left (Desktop) */
.vg-service-nav-list {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

@media (max-width: 575px) {
  .vg-service-nav-list {
    grid-template-columns: 1fr;
  }
}

.vg-service-nav-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 9px 12px;
  border-radius: 12px;
  min-height: 52px;
  background: rgba(14, 8, 30, 0.65);
  border: 1px solid rgba(168, 85, 247, 0.18);
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
  cursor: pointer;
  user-select: none;
  outline: none;
  position: relative;
  overflow: hidden;
}

.vg-service-nav-item:hover {
  background: rgba(22, 12, 46, 0.88);
  border-color: rgba(56, 189, 248, 0.45);
  transform: translateY(-2px);
}

.vg-service-nav-item:hover .vg-nav-title {
  color: #ffffff;
}

.vg-service-nav-item:hover .vg-nav-chevron {
  color: #00F0FF;
  transform: translateX(2px);
}

/* Active Service Item with Electric Cyan & Cyber Violet Glow */
.vg-service-nav-item.is-active {
  background: linear-gradient(135deg, rgba(26, 14, 56, 0.95) 0%, rgba(14, 8, 32, 0.95) 100%) !important;
  border-color: #00F0FF !important;
  border-left: 3.5px solid #00F0FF !important;
  box-shadow: 0 0 16px rgba(0, 240, 255, 0.25), inset 0 0 12px rgba(168, 85, 247, 0.18) !important;
  transform: translateY(-2px) scale(1.015) !important;
}

.vg-service-nav-item.is-active .vg-nav-title {
  color: #ffffff !important;
  text-shadow: 0 0 8px rgba(0, 240, 255, 0.4);
}

.vg-service-nav-item.is-active .vg-nav-icon-wrap {
  background: rgba(0, 240, 255, 0.16) !important;
  border-color: rgba(0, 240, 255, 0.5) !important;
  box-shadow: 0 0 12px rgba(0, 240, 255, 0.45) !important;
}

.vg-service-nav-item.is-active .vg-nav-chevron {
  color: #00F0FF !important;
  transform: translateX(3px) !important;
}

.vg-nav-item-left {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
  flex: 1;
}

.vg-nav-icon-wrap {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.09);
  color: var(--item-color, #00F0FF);
  transition: all 0.25s ease;
  flex-shrink: 0;
}

.vg-nav-text-wrap {
  display: flex;
  flex-direction: column;
  gap: 2px;
  text-align: left !important;
  min-width: 0;
  flex: 1;
}

/* NO TRUNCATION: E-Commerce Website Development and all titles wrap naturally without ellipsis */
.vg-nav-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #cbd5e1;
  margin: 0;
  line-height: 1.25;
  text-align: left !important;
  white-space: normal !important;
  word-break: normal;
  overflow: visible !important;
  text-overflow: clip !important;
  transition: color 0.25s ease;
}

.vg-nav-chevron {
  color: #64748b;
  font-size: 10px;
  transition: transform 0.25s ease, color 0.25s ease;
  flex-shrink: 0;
  margin-left: 6px;
}

/* Service Tech Tags */
.vg-service-tag {
  display: inline-flex;
  align-items: center;
  font-size: 9.5px;
  font-weight: 700;
  padding: 1.5px 6px;
  border-radius: 9999px;
  letter-spacing: 0.2px;
  width: fit-content;
  line-height: 1.2;
}

.vg-service-tag.tag-cyan {
  background: rgba(0, 240, 255, 0.12);
  border: 1px solid rgba(0, 240, 255, 0.35);
  color: #00F0FF;
}

.vg-service-tag.tag-violet {
  background: rgba(168, 85, 247, 0.14);
  border: 1px solid rgba(168, 85, 247, 0.4);
  color: #c084fc;
}

.vg-service-tag.tag-sky {
  background: rgba(56, 189, 248, 0.14);
  border: 1px solid rgba(56, 189, 248, 0.35);
  color: #38bdf8;
}

.vg-service-tag.tag-amber {
  background: rgba(245, 158, 11, 0.14);
  border: 1px solid rgba(245, 158, 11, 0.35);
  color: #fbbf24;
}

.vg-service-tag.tag-rose {
  background: rgba(244, 63, 94, 0.14);
  border: 1px solid rgba(244, 63, 94, 0.35);
  color: #fb7185;
}

.vg-service-tag.tag-indigo {
  background: rgba(129, 140, 248, 0.14);
  border: 1px solid rgba(129, 140, 248, 0.35);
  color: #a78bfa;
}

.vg-service-tag.tag-emerald {
  background: rgba(16, 185, 129, 0.14);
  border: 1px solid rgba(16, 185, 129, 0.35);
  color: #34d399;
}

.vg-service-tag.tag-purple {
  background: rgba(192, 132, 252, 0.14);
  border: 1px solid rgba(192, 132, 252, 0.4);
  color: #e879f9;
}

/* Right Column Live Preview Panel — Exactly Aligned with Left Column Top */
.vg-service-preview-sticky {
  position: relative !important;
  top: 0 !important;
  margin-top: 0 !important;
  z-index: 10;
  height: 100%;
}

.vg-service-preview-card {
  background: rgba(13, 8, 28, 0.88) !important;
  border: 1px solid rgba(168, 85, 247, 0.3) !important;
  box-shadow: 0 16px 45px rgba(0, 0, 0, 0.65), 0 0 25px rgba(168, 85, 247, 0.10) !important;
  border-radius: 18px !important;
  padding: 22px 24px !important;
  min-height: 290px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  transition: opacity 0.2s cubic-bezier(0.16, 1, 0.3, 1), transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  text-align: left !important;
  position: relative;
  margin-top: 0 !important;
}

/* Waving AI Robot Character Layout */
.vg-robot-showcase-view {
  width: 100%;
}

.vg-robot-flex-wrap {
  display: flex;
  align-items: center;
  gap: 24px;
}

.vg-robot-character-col {
  flex: 0 0 180px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 3D DTAPEX-STYLE ROBOT STAGE & KINETIC VISUALS */
.dtapex-robot-stage {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 180px;
}

.dtapex-robot-halo {
  position: absolute;
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0, 240, 255, 0.22) 0%, rgba(168, 85, 247, 0.16) 45%, transparent 75%);
  filter: blur(18px);
  top: 15px;
  left: 15px;
  pointer-events: none;
}

.dtapex-speech-bubble {
  position: absolute;
  top: -8px;
  right: -2px;
  background: linear-gradient(135deg, #00F0FF, #38BDF8);
  color: #07070F;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 11px;
  font-weight: 800;
  box-shadow: 0 4px 14px rgba(0, 240, 255, 0.45);
  z-index: 5;
  display: flex;
  align-items: center;
  gap: 4px;
  animation: dtapexSpeechBounce 2.4s ease-in-out infinite;
}

@keyframes dtapexSpeechBounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.dtapex-wave-hand {
  font-size: 12px;
  display: inline-block;
}

/* DTAPEX 3D ROBOT MASCOT KEYFRAME ANIMATIONS (60 FPS) */
@keyframes dtapexRobotHover {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-9px) rotate(1.2deg);
  }
}

@keyframes dtapexRobotWave {
  0%, 100% {
    transform: rotate(0deg);
  }
  15% {
    transform: rotate(26deg);
  }
  30% {
    transform: rotate(-12deg);
  }
  45% {
    transform: rotate(28deg);
  }
  60% {
    transform: rotate(-8deg);
  }
  75% {
    transform: rotate(18deg);
  }
}

@keyframes dtapexEyeBlink {
  0%, 93%, 97%, 100% {
    transform: scaleY(1);
  }
  95% {
    transform: scaleY(0.1);
  }
}

@keyframes dtapexReactorPulse {
  0%, 100% {
    transform: scale(1);
    opacity: 0.9;
  }
  50% {
    transform: scale(1.18);
    opacity: 1;
    filter: drop-shadow(0 0 6px #00F0FF);
  }
}

@keyframes dtapexRingRotate {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

@keyframes dtapexFlameFlicker {
  0%, 100% {
    opacity: 0.85;
    transform: scaleY(1);
  }
  50% {
    opacity: 0.55;
    transform: scaleY(0.85);
  }
}

@keyframes dtapexShadow {
  0%, 100% {
    transform: scale(1);
    opacity: 0.24;
  }
  50% {
    transform: scale(0.85);
    opacity: 0.14;
  }
}

/* Bind Keyframes to SVG Elements */
.dtapex-robot-floater {
  animation: dtapexRobotHover 3.4s ease-in-out infinite;
}

.dtapex-waving-arm {
  transform-origin: 172px 142px;
  animation: dtapexRobotWave 2.2s ease-in-out infinite;
}

.dtapex-robot-eyes {
  transform-origin: 120px 84px;
  animation: dtapexEyeBlink 3.8s infinite;
}

.dtapex-reactor-pulse {
  transform-origin: 120px 165px;
  animation: dtapexReactorPulse 2s ease-in-out infinite;
}

.dtapex-reactor-ring {
  transform-origin: 120px 165px;
  animation: dtapexRingRotate 8s linear infinite;
}

.dtapex-ion-flame {
  transform-origin: 120px 220px;
  animation: dtapexFlameFlicker 1.6s ease-in-out infinite;
}

.dtapex-robot-shadow {
  transform-origin: 120px 265px;
  animation: dtapexShadow 3.4s ease-in-out infinite;
}

.vg-robot-intro-col {
  flex: 1;
  text-align: left;
}

.vg-robot-pill-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 10px;
  border-radius: 9999px;
  background: rgba(0, 240, 255, 0.10);
  border: 1px solid rgba(0, 240, 255, 0.3);
  font-size: 11px;
  color: #00F0FF;
  font-weight: 700;
}

.vg-robot-intro-title {
  font-size: 20px;
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 6px 0;
  line-height: 1.3;
}

.vg-robot-intro-sub {
  font-size: 13.5px;
  line-height: 1.55;
  color: #cbd5e1;
  margin: 0 0 14px 0;
}

.vg-robot-features-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 14px;
}

.vg-robot-feature-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 11.5px;
  color: #cbd5e1;
  font-weight: 600;
}

.vg-robot-action-prompt {
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 700;
  color: #00F0FF;
  background: rgba(0, 240, 255, 0.08);
  border: 1px dashed rgba(0, 240, 255, 0.35);
  padding: 6px 12px;
  border-radius: 8px;
}

.vg-reset-robot-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.vg-reset-robot-btn:hover {
  background: rgba(0, 240, 255, 0.15);
  border-color: #00F0FF;
  color: #00F0FF;
  transform: rotate(-45deg);
}

/* Selected Service Details (Compact Layout) */
.vg-preview-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  border: 1px solid;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
  transition: all 0.3s ease;
}

.vg-preview-title {
  font-size: 20px;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  line-height: 1.25;
  text-align: left !important;
}

.vg-preview-badge-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 11px;
  color: #94a3b8;
  font-weight: 600;
}

.vg-preview-overview {
  font-size: 13.5px;
  line-height: 1.55;
  color: #cbd5e1;
  text-align: left !important;
  margin: 12px 0 14px 0;
}

.vg-preview-deliverables-wrap {
  margin-bottom: 14px;
}

.vg-deliverables-title {
  font-size: 11.5px;
  font-weight: 700;
  color: #ffffff;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-bottom: 10px;
  text-align: left !important;
  display: flex;
  align-items: center;
}

.vg-metrics-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}

.vg-metric-tile {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(168, 85, 247, 0.2);
  border-radius: 10px;
  padding: 8px 10px;
  text-align: left !important;
  transition: all 0.25s ease;
  display: flex;
  flex-direction: column;
}

.vg-metric-tile:hover {
  border-color: #00F0FF;
  background: rgba(0, 240, 255, 0.05);
  transform: translateY(-2px);
}

.vg-metric-icon {
  font-size: 15px;
  margin-bottom: 4px;
  text-align: left !important;
}

.vg-metric-val {
  font-size: 12px;
  font-weight: 800;
  color: #ffffff;
  display: block;
  margin-bottom: 2px;
  text-align: left !important;
}

.vg-metric-desc {
  font-size: 10.5px;
  line-height: 1.35;
  color: #94a3b8;
  display: block;
  text-align: left !important;
}

.vg-preview-footer {
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.07);
}

.vg-preview-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 20px;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 700;
  color: #ffffff !important;
  text-decoration: none !important;
  background: linear-gradient(135deg, #00F0FF 0%, #A855F7 100%);
  box-shadow: 0 6px 20px rgba(0, 240, 255, 0.35);
  transition: all 0.3s ease;
}

.vg-preview-cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(0, 240, 255, 0.5);
  color: #ffffff !important;
}

.vg-preview-guarantee {
  font-size: 12px;
  color: #94a3b8;
  display: inline-flex;
  align-items: center;
}

@media (max-width: 991px) {
  .vg-service-preview-sticky {
    position: static !important;
    margin-top: 12px !important;
  }
  .vg-robot-flex-wrap {
    flex-direction: column;
    text-align: center;
  }
  .vg-robot-intro-col {
    text-align: center;
  }
  .vg-robot-features-row {
    justify-content: center;
  }
  .vg-metrics-grid {
    grid-template-columns: 1fr;
  }
  .vg-service-preview-card {
    padding: 20px 18px !important;
  }
}
`;

// Locate the block in color.css starting at media (min-width: 992px) with .vg-split-services-section
const cssMatchStart = cssContent.indexOf('@media (min-width: 992px) {\r\n  .vg-split-services-section .vg-services-heading') !== -1
  ? cssContent.indexOf('@media (min-width: 992px) {\r\n  .vg-split-services-section .vg-services-heading')
  : cssContent.indexOf('@media (min-width: 992px) {\n  .vg-split-services-section .vg-services-heading');

if (cssMatchStart !== -1) {
  cssContent = cssContent.substring(0, cssMatchStart) + newCssBlock.trim() + '\n';
  console.log('Successfully updated color.css');
} else {
  // Append or replace from .vg-split-services-section
  const altIndex = cssContent.indexOf('.vg-split-services-section {');
  if (altIndex !== -1) {
    // Find preceding comment if any
    const commentIdx = cssContent.lastIndexOf('/*', altIndex);
    const replaceFrom = commentIdx !== -1 && commentIdx > altIndex - 200 ? commentIdx : altIndex;
    cssContent = cssContent.substring(0, replaceFrom) + newCssBlock.trim() + '\n';
    console.log('Successfully updated color.css via altIndex');
  } else {
    cssContent += '\n' + newCssBlock.trim() + '\n';
    console.log('Appended newCssBlock to color.css');
  }
}

// Write color.css to root and mirrors
fs.writeFileSync(cssPath, cssContent, 'utf8');
const mirrorCss1 = path.join(rootDir, 'public_html (1)', 'assets', 'css', 'color.css');
const mirrorCss2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'css', 'color.css');
if (fs.existsSync(mirrorCss1)) fs.writeFileSync(mirrorCss1, cssContent, 'utf8');
if (fs.existsSync(mirrorCss2)) fs.writeFileSync(mirrorCss2, cssContent, 'utf8');
console.log('Synchronized CSS across all mirrors.');


// 3. Update HTML files (web-development.html and mirrors)
const htmlFiles = [
  path.join(rootDir, 'web-development.html'),
  path.join(rootDir, 'public_html (1)', 'web-development.html'),
  path.join(rootDir, 'public_html (1)', 'website', 'web-development.html')
];

htmlFiles.forEach((file) => {
  if (!fs.existsSync(file)) return;
  let html = fs.readFileSync(file, 'utf8');

  // Update header and column structure in web-development.html
  // Replace <div class="text-center mx-auto mb-3" style="max-width: 1100px;">
  html = html.replace(
    /<div class="text-center mx-auto mb-[0-9]" style="max-width: 1100px;">/g,
    '<div class="text-center mx-auto mb-4 pb-1" style="max-width: 1100px;">'
  );

  // Ensure margin-bottom: 0 on subtitle
  html = html.replace(
    /style="color: #cbd5e1; font-size: 15px; line-height: 1\.7; margin-top: 12px; max-width: 1060px; margin-left: auto; margin-right: auto;"/g,
    'style="color: #cbd5e1; font-size: 15px; line-height: 1.7; margin-top: 12px; max-width: 1060px; margin-left: auto; margin-right: auto; margin-bottom: 0;"'
  );

  // Update row class to include vg-split-showcase-row
  html = html.replace(
    /<div class="row g-4 align-items-start">/g,
    '<div class="row g-4 align-items-start vg-split-showcase-row">'
  );

  // Update column widths from col-lg-5 / col-lg-7 to col-lg-6 / col-lg-6
  html = html.replace(
    /<div class="col-lg-5 col-xl-5">/g,
    '<div class="col-lg-6 col-xl-6">'
  );
  html = html.replace(
    /<div class="col-lg-7 col-xl-7">/g,
    '<div class="col-lg-6 col-xl-6">'
  );

  fs.writeFileSync(file, html, 'utf8');
  console.log(`Updated HTML in: ${file}`);
});

console.log('All upgrades applied successfully!');

