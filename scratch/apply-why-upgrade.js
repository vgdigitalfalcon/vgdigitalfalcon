const fs = require('fs');
const path = require('path');

const cssSnippet = `

/* ==========================================================================
   VG DIGITAL FALCON - WHY ECOMMERCE SEO SECTION (AUTOMATIC LOOPING MICRO-SCENES)
   ========================================================================== */

/* 1. Enhanced Glass Card with Continuous Aurora Border Shimmer */
.vg-ecom-pillar-card {
  position: relative;
  background: rgba(13, 7, 28, 0.88) !important;
  border-radius: 20px !important;
  padding: 24px 22px !important;
  height: 100% !important;
  display: flex !important;
  flex-direction: column !important;
  box-shadow: 0 14px 40px rgba(0, 0, 0, 0.55) !important;
  backdrop-filter: blur(16px) !important;
  -webkit-backdrop-filter: blur(16px) !important;
  border: 1px solid rgba(168, 85, 247, 0.2) !important;
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease !important;
  overflow: hidden !important;
  z-index: 1 !important;
}

/* Continuous Aurora Border Shimmer */
.vg-ecom-pillar-card::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 20px;
  padding: 1.5px;
  background: linear-gradient(
    125deg,
    rgba(0, 240, 255, 0.7) 0%,
    rgba(168, 85, 247, 0.2) 25%,
    rgba(16, 185, 129, 0.5) 50%,
    rgba(168, 85, 247, 0.7) 75%,
    rgba(0, 240, 255, 0.3) 100%
  );
  background-size: 300% 300%;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  animation: vgAuroraShimmer 8s linear infinite;
  z-index: 2;
  opacity: 0.85;
}

/* Staggered phase offset for the 3 cards */
.col-lg-4:nth-child(1) .vg-ecom-pillar-card::before {
  animation-delay: 0s;
}
.col-lg-4:nth-child(2) .vg-ecom-pillar-card::before {
  animation-delay: -2.66s;
}
.col-lg-4:nth-child(3) .vg-ecom-pillar-card::before {
  animation-delay: -5.33s;
}

@keyframes vgAuroraShimmer {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.vg-ecom-pillar-card:hover {
  transform: translateY(-6px) !important;
  box-shadow: 0 22px 50px rgba(0, 0, 0, 0.7), 0 0 25px rgba(0, 240, 255, 0.22) !important;
}

.vg-ecom-pillar-card:hover::before {
  opacity: 1;
  animation-duration: 4s;
}

/* Card Header Strip */
.vg-pillar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.vg-pillar-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 19px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  margin-bottom: 0 !important;
}

.vg-pillar-telemetry-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 30px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  color: #94a3b8;
}

.vg-badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  display: inline-block;
  animation: vgDotBlink 2s ease-in-out infinite;
}
.vg-badge-dot.bg-cyan {
  background: #00F0FF;
  box-shadow: 0 0 8px #00F0FF;
}
.vg-badge-dot.bg-violet {
  background: #A855F7;
  box-shadow: 0 0 8px #A855F7;
}

@keyframes vgDotBlink {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(0.85); }
}

/* Common Mini-Scene Container */
.vg-pillar-mini-scene {
  background: rgba(7, 3, 18, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 14px;
  padding: 14px;
  margin-top: auto;
  position: relative;
  overflow: hidden;
  box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.5);
  min-height: 160px;
  display: flex;
  flex-direction: column;
}

/* ==========================================================================
   MINI-SCENE 1: SERP RANK #1 CLIMB & 5-STAR SCHEMA TWINKLE
   ========================================================================== */
.vg-serp-scene {
  justify-content: flex-start;
}

.vg-serp-searchbar {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.07);
  border-radius: 8px;
  padding: 5px 10px;
  font-size: 11px;
  margin-bottom: 10px;
  color: #cbd5e1;
}

.vg-serp-query {
  flex-grow: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
  font-family: 'Space Grotesk', sans-serif;
  color: #e2e8f0;
}

.vg-serp-vol {
  font-size: 9.5px;
  font-weight: 700;
  color: #00F0FF;
  background: rgba(0, 240, 255, 0.1);
  padding: 1px 6px;
  border-radius: 4px;
  border: 1px solid rgba(0, 240, 255, 0.25);
}

.vg-serp-feed {
  position: relative;
  height: 98px;
  overflow: hidden;
}

/* Competitor Dummy Rows */
.vg-serp-row-dummy {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 5px 8px;
  margin-bottom: 6px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.02);
  border: 1px dashed rgba(255, 255, 255, 0.06);
  opacity: 0.7;
  transition: opacity 0.4s ease;
}

.vg-serp-rank-num {
  font-size: 10px;
  font-weight: 700;
  color: #64748b;
  min-width: 16px;
}

.vg-dummy-body {
  flex-grow: 1;
}

.vg-dummy-line-title {
  height: 6px;
  width: 65%;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
  margin-bottom: 3px;
}

.vg-dummy-line-desc {
  height: 4px;
  width: 90%;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 2px;
}

/* Hero Climbing Product Card */
.vg-serp-hero-listing {
  position: absolute;
  left: 0;
  right: 0;
  top: 0;
  background: rgba(18, 10, 36, 0.95);
  border: 1px solid rgba(0, 240, 255, 0.35);
  border-radius: 10px;
  padding: 8px 10px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
  transition: transform 0.25s ease-out, border-color 0.3s ease, box-shadow 0.3s ease;
  z-index: 5;
  overflow: hidden;
}

.vg-serp-hero-listing.is-rank-1 {
  border-color: #00F0FF;
  box-shadow: 0 0 20px rgba(0, 240, 255, 0.35), 0 0 35px rgba(245, 158, 11, 0.2);
}

.vg-hero-listing-badge-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 5px;
}

.vg-hero-rank-pill {
  display: inline-flex;
  align-items: center;
  font-size: 9.5px;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 20px;
  background: rgba(0, 240, 255, 0.12);
  border: 1px solid rgba(0, 240, 255, 0.3);
  color: #00F0FF;
  transition: all 0.3s ease;
}

.vg-hero-rank-pill.is-gold {
  background: rgba(245, 158, 11, 0.18);
  border-color: rgba(245, 158, 11, 0.6);
  color: #FFD700;
  box-shadow: 0 0 10px rgba(245, 158, 11, 0.4);
}

.vg-schema-valid-pill {
  font-size: 9px;
  font-weight: 600;
  color: #94a3b8;
  display: inline-flex;
  align-items: center;
}

.vg-hero-product-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.vg-hero-prod-thumb {
  width: 32px;
  height: 32px;
  border-radius: 7px;
  background: rgba(0, 240, 255, 0.08);
  border: 1px solid rgba(0, 240, 255, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}

.vg-hero-prod-meta {
  flex-grow: 1;
  min-width: 0;
}

.vg-hero-prod-name {
  font-size: 11.5px;
  font-weight: 700;
  color: #ffffff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.25;
}

.vg-hero-stars-row {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 10px;
  margin-top: 2px;
  flex-wrap: wrap;
}

.vg-hero-stars {
  color: #F59E0B;
  letter-spacing: 0.5px;
  text-shadow: 0 0 8px rgba(245, 158, 11, 0.7);
  animation: vgStarsTwinkle 3s ease-in-out infinite;
}

@keyframes vgStarsTwinkle {
  0%, 100% { text-shadow: 0 0 6px rgba(245, 158, 11, 0.5); }
  50% { text-shadow: 0 0 14px rgba(245, 158, 11, 0.95), 0 0 6px #fff; }
}

.vg-hero-score {
  font-weight: 800;
  color: #fbbf24;
}

.vg-hero-reviews {
  color: #94a3b8;
  font-size: 9px;
}

.vg-hero-price {
  color: #10B981;
  font-weight: 700;
  font-size: 9.5px;
  margin-left: auto;
}

/* Laser Scanline Beam inside Hero Card */
.vg-serp-card-sweep {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 40px;
  background: linear-gradient(90deg, transparent, rgba(0, 240, 255, 0.35), transparent);
  transform: skewX(-20deg);
  animation: vgSerpBeamSweep 4s ease-in-out infinite;
  pointer-events: none;
}

@keyframes vgSerpBeamSweep {
  0% { left: -60px; }
  35%, 100% { left: 120%; }
}

/* ==========================================================================
   MINI-SCENE 2: NEON CORE WEB VITALS SPEED GAUGE
   ========================================================================== */
.vg-gauge-scene {
  justify-content: center;
  align-items: center;
}

.vg-gauge-wrap {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.vg-gauge-svg-box {
  position: relative;
  width: 160px;
  height: 90px;
  display: flex;
  justify-content: center;
}

.vg-speed-gauge-svg {
  width: 100%;
  height: 100%;
  overflow: visible;
}

.vg-gauge-fill-arc {
  transition: stroke-dashoffset 0.1s linear;
}

.vg-gauge-center-content {
  position: absolute;
  bottom: 6px;
  left: 0;
  right: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.vg-gauge-score-wrap {
  display: flex;
  align-items: baseline;
  justify-content: center;
  line-height: 1;
}

.vg-gauge-num {
  font-size: 26px;
  font-weight: 900;
  color: #ffffff;
  letter-spacing: -0.5px;
  font-family: 'Space Grotesk', sans-serif;
  text-shadow: 0 0 14px rgba(0, 240, 255, 0.4);
}

.vg-gauge-denom {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  margin-left: 2px;
}

.vg-gauge-state-badge {
  margin-top: 3px;
  display: inline-flex;
  align-items: center;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 9.5px;
  font-weight: 700;
  letter-spacing: 0.3px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #94a3b8;
  transition: all 0.3s ease;
}

.vg-gauge-state-badge.is-fast {
  background: rgba(0, 240, 255, 0.12);
  border-color: rgba(0, 240, 255, 0.4);
  color: #00F0FF;
  box-shadow: 0 0 10px rgba(0, 240, 255, 0.3);
}

.vg-gauge-state-badge.is-poor {
  background: rgba(239, 68, 68, 0.12);
  border-color: rgba(239, 68, 68, 0.4);
  color: #f87171;
}

.vg-gauge-state-badge.is-surging {
  background: rgba(168, 85, 247, 0.12);
  border-color: rgba(168, 85, 247, 0.4);
  color: #c084fc;
}

.vg-gauge-vitals-row {
  display: flex;
  gap: 8px;
  justify-content: center;
  margin-top: 8px;
  width: 100%;
}

.vg-vital-chip {
  flex: 1;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 6px;
  padding: 3px 6px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.vg-vital-name {
  font-size: 8.5px;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
}

.vg-vital-val {
  font-size: 10.5px;
  font-weight: 800;
  color: #cbd5e1;
  transition: color 0.3s ease;
}

.vg-vital-val.is-green {
  color: #10B981;
  text-shadow: 0 0 8px rgba(16, 185, 129, 0.5);
}

.vg-vital-val.is-warn {
  color: #fbbf24;
}

/* ==========================================================================
   MINI-SCENE 3: CYBER VIOLET BACKLINK NETWORK & VERIFIED AUTHORITY SHIELD
   ========================================================================== */
.vg-network-scene {
  justify-content: space-between;
}

.vg-network-svg {
  width: 100%;
  height: 105px;
  overflow: visible;
}

/* Streaming Laser Links */
.vg-network-link {
  stroke: url(#vgLaserLinkGrad);
  stroke-width: 1.5;
  stroke-dasharray: 6 6;
  animation: vgLaserStreamFlow 2.4s linear infinite;
}

.vg-link-1 { animation-duration: 2s; }
.vg-link-2 { animation-duration: 2.3s; animation-delay: -0.5s; }
.vg-link-3 { animation-duration: 2.6s; animation-delay: -1s; }
.vg-link-4 { animation-duration: 2.1s; animation-delay: -1.5s; }
.vg-link-5 { animation-duration: 1.8s; animation-delay: -0.3s; }

@keyframes vgLaserStreamFlow {
  from { stroke-dashoffset: 24; }
  to { stroke-dashoffset: 0; }
}

/* Outer Authority Nodes */
.vg-node-outer {
  fill: rgba(168, 85, 247, 0.12);
  stroke: rgba(168, 85, 247, 0.4);
  stroke-width: 1;
  animation: vgNodeBeaconPulse 3s ease-in-out infinite;
}

.vg-node-inner {
  fill: rgba(18, 10, 36, 0.95);
  stroke: rgba(0, 240, 255, 0.6);
  stroke-width: 1;
}

.vg-node-label {
  fill: #00F0FF;
  font-size: 8px;
  font-weight: 800;
  font-family: 'Space Grotesk', sans-serif;
  text-anchor: middle;
  dominant-baseline: central;
}

@keyframes vgNodeBeaconPulse {
  0%, 100% {
    r: 13;
    stroke-opacity: 0.4;
    fill-opacity: 0.1;
  }
  50% {
    r: 15;
    stroke-opacity: 0.9;
    stroke-color: #00F0FF;
    fill-opacity: 0.25;
  }
}

/* Shield Concentric Rings */
.vg-shield-ring {
  fill: none;
  stroke: rgba(168, 85, 247, 0.35);
  stroke-width: 1;
  stroke-dasharray: 4 4;
  transform-origin: 140px 65px;
  animation: vgShieldRingPulse 4s ease-in-out infinite;
}

.vg-ring-2 {
  stroke: rgba(0, 240, 255, 0.25) !important;
  animation-delay: -2s;
}

@keyframes vgShieldRingPulse {
  0% {
    transform: scale(0.85) rotate(0deg);
    opacity: 0.2;
  }
  50% {
    transform: scale(1.1) rotate(180deg);
    opacity: 0.7;
    stroke: rgba(0, 240, 255, 0.5);
  }
  100% {
    transform: scale(0.85) rotate(360deg);
    opacity: 0.2;
  }
}

/* Central Verified Authority Shield */
.vg-shield-crest {
  fill: rgba(14, 8, 32, 0.95);
  stroke: url(#vgLaserLinkGrad);
  stroke-width: 2;
  filter: drop-shadow(0 0 12px rgba(168, 85, 247, 0.6));
  animation: vgShieldGlowPulse 3s ease-in-out infinite;
}

@keyframes vgShieldGlowPulse {
  0%, 100% {
    filter: drop-shadow(0 0 8px rgba(168, 85, 247, 0.5));
    stroke: #A855F7;
  }
  50% {
    filter: drop-shadow(0 0 18px rgba(0, 240, 255, 0.8)) drop-shadow(0 0 30px rgba(168, 85, 247, 0.5));
    stroke: #00F0FF;
  }
}

.vg-shield-check {
  animation: vgCheckGlint 3s ease-in-out infinite;
}

@keyframes vgCheckGlint {
  0%, 100% { stroke: #00F0FF; stroke-width: 2.2; }
  50% { stroke: #ffffff; stroke-width: 2.8; filter: drop-shadow(0 0 6px #00F0FF); }
}

/* Shield Telemetry Strip */
.vg-shield-stats-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 6px;
  padding: 4px 8px;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.04);
}

.vg-shield-badge {
  font-size: 9.5px;
  color: #94a3b8;
  display: inline-flex;
  align-items: center;
}

.vg-shield-badge strong {
  color: #ffffff;
  margin-left: 2px;
}
`;

// Target HTML template for the 3 pillars section
const newWhySectionHtml = `        <!-- ==========================================================================
             WHY SEO SECTION (WHY DOES YOUR ONLINE STORE NEED AN ECOM SEO PARTNER)
             ========================================================================== -->
        <section class="vg-seo-why-section">
            <div class="container">
                <div class="text-center max-w-750 mx-auto mb-5">
                    <span class="vg-pill-badge wow fadeInUp" data-wow-delay=".1s"><i class="fa-solid fa-circle-question me-2"></i> Why SEO?</span>
                    <h2 class="text-white mt-2 wow fadeInUp" data-wow-delay=".2s" style="font-size: clamp(26px, 2.3vw, 36px); font-weight: 800; line-height: 1.25;">
                        Why Does Your Online Store Need a Trusted <br>
                        <span class="vg-gradient-text">Ecommerce SEO Partner?</span>
                    </h2>
                    <p class="wow fadeInUp mt-3" data-wow-delay=".3s" style="color: #cbd5e1; font-size: 15px; line-height: 1.7; text-align: center;">
                        Search Engine Optimization is not optional, it’s essential. Especially in ecommerce, where competition is fierce and visibility means everything. <br class="d-none d-lg-block">As a global performance-driven ecommerce SEO agency, we craft strategies that bring qualified buyers to your website through:
                    </p>
                </div>

                <!-- 3 Pillars Grid with Automatic Looping Micro-Scenes -->
                <div class="row g-4 justify-content-center mb-4">
                    <!-- Card 1: Product Page SEO with SERP Rank #1 Climb & 5-Star Schema -->
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".2s">
                        <div class="vg-ecom-pillar-card vg-pillar-serp-card">
                            <div class="vg-pillar-header">
                                <div class="vg-pillar-icon" style="color: #00F0FF; background: rgba(0, 240, 255, 0.08); border-color: rgba(0, 240, 255, 0.25);">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </div>
                                <div class="vg-pillar-telemetry-badge">
                                    <span class="vg-badge-dot bg-cyan"></span>
                                    <span>SERP #1 RANK</span>
                                </div>
                            </div>
                            <h4 class="text-white mt-1 mb-2" style="font-size: 18px; font-weight: 700;">Product Page SEO</h4>
                            <p style="color: #cbd5e1; font-size: 14px; margin: 0 0 16px 0; line-height: 1.6;">
                                <i class="fa-solid fa-circle-check text-cyan me-1"></i> Product page SEO optimization services.
                            </p>

                            <!-- Animated Mini-Scene: SERP Climb & Schema -->
                            <div class="vg-pillar-mini-scene vg-serp-scene">
                                <div class="vg-serp-searchbar">
                                    <i class="fa-solid fa-magnifying-glass text-cyan me-2" style="font-size: 10px;"></i>
                                    <span class="vg-serp-query">buy organic silk pillowcase</span>
                                    <span class="vg-serp-vol">44K/mo</span>
                                </div>

                                <div class="vg-serp-feed">
                                    <!-- Competitor #2 Dummy Slot -->
                                    <div class="vg-serp-row-dummy vg-dummy-2">
                                        <span class="vg-serp-rank-num">#2</span>
                                        <div class="vg-dummy-body">
                                            <div class="vg-dummy-line-title"></div>
                                            <div class="vg-dummy-line-desc"></div>
                                        </div>
                                    </div>

                                    <!-- Competitor #3 Dummy Slot -->
                                    <div class="vg-serp-row-dummy vg-dummy-3">
                                        <span class="vg-serp-rank-num">#3</span>
                                        <div class="vg-dummy-body">
                                            <div class="vg-dummy-line-title"></div>
                                            <div class="vg-dummy-line-desc"></div>
                                        </div>
                                    </div>

                                    <!-- Hero Climbing Product Card -->
                                    <div class="vg-serp-hero-listing">
                                        <div class="vg-hero-listing-badge-strip">
                                            <span class="vg-hero-rank-pill is-gold">
                                                <i class="fa-solid fa-crown text-gold me-1 vg-rank-icon"></i><span class="vg-rank-text">#1 Rank</span>
                                            </span>
                                            <span class="vg-schema-valid-pill">
                                                <i class="fa-solid fa-code me-1 text-cyan"></i>Schema.org Valid
                                            </span>
                                        </div>
                                        <div class="vg-hero-product-row">
                                            <div class="vg-hero-prod-thumb">
                                                <i class="fa-solid fa-bag-shopping text-cyan"></i>
                                            </div>
                                            <div class="vg-hero-prod-meta">
                                                <div class="vg-hero-prod-name">Pure Mulberry Silk Sleepcase</div>
                                                <div class="vg-hero-stars-row">
                                                    <span class="vg-hero-stars">★★★★★</span>
                                                    <span class="vg-hero-score">4.9</span>
                                                    <span class="vg-hero-reviews">(3.2k)</span>
                                                    <span class="vg-hero-price">$69.00</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="vg-serp-card-sweep"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Technical Platform SEO with Core Web Vitals Speed Gauge -->
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".3s">
                        <div class="vg-ecom-pillar-card vg-pillar-speed-card">
                            <div class="vg-pillar-header">
                                <div class="vg-pillar-icon" style="color: #A855F7; background: rgba(168, 85, 247, 0.08); border-color: rgba(168, 85, 247, 0.25);">
                                    <i class="fa-solid fa-gears"></i>
                                </div>
                                <div class="vg-pillar-telemetry-badge">
                                    <span class="vg-badge-dot bg-cyan"></span>
                                    <span>CORE WEB VITALS</span>
                                </div>
                            </div>
                            <h4 class="text-white mt-1 mb-2" style="font-size: 18px; font-weight: 700;">Technical Platform SEO</h4>
                            <p style="color: #cbd5e1; font-size: 14px; margin: 0 0 16px 0; line-height: 1.6;">
                                <i class="fa-solid fa-circle-check text-violet me-1"></i> Technical SEO for Shopify and WooCommerce
                            </p>

                            <!-- Animated Mini-Scene: Neon Speed Gauge (Sluggish to 99/100) -->
                            <div class="vg-pillar-mini-scene vg-gauge-scene">
                                <div class="vg-gauge-wrap">
                                    <div class="vg-gauge-svg-box">
                                        <svg viewBox="0 0 140 85" class="vg-speed-gauge-svg">
                                            <defs>
                                                <linearGradient id="vgGaugeGrad" x1="0%" y1="100%" x2="100%" y2="0%">
                                                    <stop offset="0%" stop-color="#EF4444" />
                                                    <stop offset="35%" stop-color="#F59E0B" />
                                                    <stop offset="65%" stop-color="#10B981" />
                                                    <stop offset="100%" stop-color="#00F0FF" />
                                                </linearGradient>
                                                <filter id="vgGaugeGlow" x="-20%" y="-20%" width="140%" height="140%">
                                                    <feGaussianBlur stdDeviation="2.5" result="blur" />
                                                    <feComposite in="SourceGraphic" in2="blur" operator="over" />
                                                </filter>
                                            </defs>
                                            <path class="vg-gauge-bg-arc" d="M 20 75 A 50 50 0 0 1 120 75" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="7" stroke-linecap="round" />
                                            <path class="vg-gauge-fill-arc" d="M 20 75 A 50 50 0 0 1 120 75" fill="none" stroke="url(#vgGaugeGrad)" stroke-width="7" stroke-linecap="round" stroke-dasharray="157.08" stroke-dashoffset="1.57" filter="url(#vgGaugeGlow)" />
                                        </svg>
                                        <div class="vg-gauge-center-content">
                                            <div class="vg-gauge-score-wrap">
                                                <span class="vg-gauge-num">99</span><span class="vg-gauge-denom">/100</span>
                                            </div>
                                            <span class="vg-gauge-state-badge is-fast">
                                                <i class="fa-solid fa-bolt me-1 text-cyan vg-gauge-state-icon"></i><span class="vg-gauge-state-text">LIGHTNING FAST</span>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="vg-gauge-vitals-row">
                                        <div class="vg-vital-chip">
                                            <span class="vg-vital-name">LCP</span>
                                            <span class="vg-vital-val is-green">0.5s</span>
                                        </div>
                                        <div class="vg-vital-chip">
                                            <span class="vg-vital-name">INP</span>
                                            <span class="vg-vital-val is-green">18ms</span>
                                        </div>
                                        <div class="vg-vital-chip">
                                            <span class="vg-vital-name">CLS</span>
                                            <span class="vg-vital-val is-green">0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Ethical Authority with Backlink Network & Verified Authority Shield -->
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay=".4s">
                        <div class="vg-ecom-pillar-card vg-pillar-auth-card">
                            <div class="vg-pillar-header">
                                <div class="vg-pillar-icon" style="color: #10B981; background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25);">
                                    <i class="fa-solid fa-link"></i>
                                </div>
                                <div class="vg-pillar-telemetry-badge">
                                    <span class="vg-badge-dot bg-violet"></span>
                                    <span>AUTHORITY SHIELD</span>
                                </div>
                            </div>
                            <h4 class="text-white mt-1 mb-2" style="font-size: 18px; font-weight: 700;">Ethical Authority</h4>
                            <p style="color: #cbd5e1; font-size: 14px; margin: 0 0 16px 0; line-height: 1.6;">
                                <i class="fa-solid fa-circle-check text-emerald me-1"></i> Ethical, white-hat link building.
                            </p>

                            <!-- Animated Mini-Scene: Backlink Network & Verified Authority Shield -->
                            <div class="vg-pillar-mini-scene vg-network-scene">
                                <svg viewBox="0 0 280 115" class="vg-network-svg" preserveAspectRatio="xMidYMid meet">
                                    <defs>
                                        <linearGradient id="vgLaserLinkGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#00F0FF" stop-opacity="0.95" />
                                            <stop offset="100%" stop-color="#A855F7" stop-opacity="0.95" />
                                        </linearGradient>
                                        <filter id="vgShieldGlow" x="-20%" y="-20%" width="140%" height="140%">
                                            <feGaussianBlur stdDeviation="3" result="glow" />
                                            <feComposite in="SourceGraphic" in2="glow" operator="over" />
                                        </filter>
                                    </defs>

                                    <!-- Streaming Backlink Laser Links -->
                                    <line x1="32" y1="26" x2="140" y2="58" class="vg-network-link vg-link-1" />
                                    <line x1="248" y1="26" x2="140" y2="58" class="vg-network-link vg-link-2" />
                                    <line x1="24" y1="92" x2="140" y2="58" class="vg-network-link vg-link-3" />
                                    <line x1="256" y1="92" x2="140" y2="58" class="vg-network-link vg-link-4" />
                                    <line x1="140" y1="12" x2="140" y2="58" class="vg-network-link vg-link-5" />

                                    <!-- Outer Authority Nodes -->
                                    <g class="vg-node-group">
                                        <circle cx="32" cy="26" r="13" class="vg-node-outer" />
                                        <circle cx="32" cy="26" r="9" class="vg-node-inner" />
                                        <text x="32" y="27" class="vg-node-label">DA 92</text>
                                    </g>
                                    <g class="vg-node-group">
                                        <circle cx="248" cy="26" r="13" class="vg-node-outer" />
                                        <circle cx="248" cy="26" r="9" class="vg-node-inner" />
                                        <text x="248" y="27" class="vg-node-label">DA 89</text>
                                    </g>
                                    <g class="vg-node-group">
                                        <circle cx="24" cy="92" r="12" class="vg-node-outer" />
                                        <circle cx="24" cy="92" r="8" class="vg-node-inner" />
                                        <text x="24" y="93" class="vg-node-label" style="font-size: 7px;">.EDU</text>
                                    </g>
                                    <g class="vg-node-group">
                                        <circle cx="256" cy="92" r="12" class="vg-node-outer" />
                                        <circle cx="256" cy="92" r="8" class="vg-node-inner" />
                                        <text x="256" y="93" class="vg-node-label">DA 86</text>
                                    </g>
                                    <g class="vg-node-group">
                                        <circle cx="140" cy="12" r="10" class="vg-node-outer" />
                                        <circle cx="140" cy="12" r="7" class="vg-node-inner" />
                                        <text x="140" y="13" class="vg-node-label" style="font-size: 6.5px;">PRESS</text>
                                    </g>

                                    <!-- Concentric Radar Rings -->
                                    <circle cx="140" cy="58" r="26" class="vg-shield-ring vg-ring-1" />
                                    <circle cx="140" cy="58" r="38" class="vg-shield-ring vg-ring-2" />

                                    <!-- Central Verified Authority Shield -->
                                    <g class="vg-center-shield-group" filter="url(#vgShieldGlow)">
                                        <path d="M 140 38 L 158 46 C 158 60 150 71 140 76 C 130 71 122 60 122 46 Z" class="vg-shield-crest" />
                                        <path d="M 133 56 L 138 61 L 148 50" fill="none" stroke="#00F0FF" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" class="vg-shield-check" />
                                    </g>
                                </svg>

                                <div class="vg-shield-stats-strip">
                                    <span class="vg-shield-badge">
                                        <i class="fa-solid fa-shield-halved text-violet me-1"></i>Spam: <strong>0%</strong>
                                    </span>
                                    <span class="vg-shield-badge">
                                        <i class="fa-solid fa-check-double text-emerald me-1"></i><strong>100% White-Hat</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="wow fadeInUp mt-4" data-wow-delay=".5s" style="color: #cbd5e1; font-size: 15px; line-height: 1.7; text-align: center; max-width: 980px; margin-left: auto; margin-right: auto;">
                    From local SEO for ecommerce businesses to international ecommerce SEO services, we ensure your store ranks, converts, and <br class="d-none d-lg-block">scales sustainably. Avoid the trap of low-quality SEO. Choose experts who focus on revenue, not just rankings.
                </p>
            </div>
        </section>`;

// 1. Update CSS in assets/css/color.css and its mirrors
const cssPaths = [
  'assets/css/color.css',
  'public_html (1)/assets/css/color.css',
  'public_html (1)/website/assets/css/color.css'
];

cssPaths.forEach(cssPath => {
  if (fs.existsSync(cssPath)) {
    let content = fs.readFileSync(cssPath, 'utf8');
    if (!content.includes('vg-pillar-mini-scene')) {
      content += cssSnippet;
      fs.writeFileSync(cssPath, content, 'utf8');
      console.log('Appended CSS to ' + cssPath);
    } else {
      console.log('CSS already exists in ' + cssPath);
    }
  }
});

// 2. Update HTML in all 3 html files
const htmlPaths = [
  'ecommerce-seo-service.html',
  'public_html (1)/ecommerce-seo-service.html',
  'public_html (1)/website/ecommerce-seo-service.html'
];

htmlPaths.forEach(htmlPath => {
  if (fs.existsSync(htmlPath)) {
    let content = fs.readFileSync(htmlPath, 'utf8');
    
    // Find the section between <section class="vg-seo-why-section"> and </section>
    const sectionStartRegex = /<section class="vg-seo-why-section">[\s\S]*?<\/section>/;
    if (sectionStartRegex.test(content)) {
      content = content.replace(sectionStartRegex, newWhySectionHtml.trim());
      
      // Ensure <script src="assets/js/ecom-why-animations.js"></script> is included
      if (!content.includes('assets/js/ecom-why-animations.js')) {
        content = content.replace(
          '<script src="assets/js/ecom-metric-cipher.js"></script>',
          '<script src="assets/js/ecom-metric-cipher.js"></script>\n    <script src="assets/js/ecom-why-animations.js"></script>'
        );
      }
      
      fs.writeFileSync(htmlPath, content, 'utf8');
      console.log('Updated HTML in ' + htmlPath);
    } else {
      console.error('Could not find vg-seo-why-section in ' + htmlPath);
    }
  }
});

