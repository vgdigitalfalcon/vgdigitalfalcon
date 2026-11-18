/**
 * ============================================================================
 * VG DIGITAL FALCON — EXECUTIVE BRANDING SPLIT-SCREEN 3D SHOWCASE
 * File: assets/js/pb-showcase.js
 * 
 * Interactive 3D Showcase for 'Why Is Personal Branding Essential For Your Success?'
 * - Left 3D Stage: Rotating 3D Neon Gyroscope rings & morphing holographic emblems
 * - Right List: 6 sleek compact glass rows with smooth accordion expand/collapse
 * - Auto-cycle every 5s with pause on hover & instant manual click switching
 * - High-performance Canvas 2D engine with 3D perspective projection
 * - Includes IntersectionObserver to completely prevent background layout shifts
 * ============================================================================
 */

(function () {
  'use strict';

  // Crisp Neon SVG Emblems for Left Stage (52x52)
  const EMBLEMS = [
    // 01: Industry Leader Crown
    `<svg width="52" height="52" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="emblemGrad1" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse">
          <stop stop-color="#00F0FF"/>
          <stop offset="1" stop-color="#A855F7"/>
        </linearGradient>
      </defs>
      <path d="M4 18H20V20H4V18ZM4.5 15L3 7L8 10L12 4L16 10L21 7L19.5 15H4.5Z" stroke="url(#emblemGrad1)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="12" cy="11" r="1.5" fill="#00F0FF"/>
      <circle cx="8" cy="10" r="1" fill="#A855F7"/>
      <circle cx="16" cy="10" r="1" fill="#A855F7"/>
    </svg>`,

    // 02: Build Trust Shield
    `<svg width="52" height="52" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="emblemGrad2" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse">
          <stop stop-color="#38BDF8"/>
          <stop offset="1" stop-color="#A855F7"/>
        </linearGradient>
      </defs>
      <path d="M12 3L20 6.5V12C20 16.5 16.5 20 12 21.5C7.5 20 4 16.5 4 12V6.5L12 3Z" stroke="url(#emblemGrad2)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M9 12L11 14L15 9.5" stroke="#00F0FF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="12" cy="12" r="6" stroke="rgba(0,240,255,0.25)" stroke-width="1" stroke-dasharray="2 2"/>
    </svg>`,

    // 03: High Growth Rocket
    `<svg width="52" height="52" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="emblemGrad3" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse">
          <stop stop-color="#00F0FF"/>
          <stop offset="1" stop-color="#C084FC"/>
        </linearGradient>
      </defs>
      <path d="M14.5 3.5C14.5 3.5 19.5 4.5 20.5 9.5C21.5 14.5 16.5 18 16.5 18L13 14.5L10.5 17L9.5 15.5L12 13L8.5 9.5C8.5 9.5 12 4.5 14.5 3.5Z" stroke="url(#emblemGrad3)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M6 18L3 21" stroke="#00F0FF" stroke-width="2" stroke-linecap="round"/>
      <path d="M9.5 20.5L8.5 18.5" stroke="#A855F7" stroke-width="1.8" stroke-linecap="round"/>
      <path d="M15.5 8.5C15.5 9.3 14.8 10 14 10C13.2 10 12.5 9.3 12.5 8.5C12.5 7.7 13.2 7 14 7C14.8 7 15.5 7.7 15.5 8.5Z" fill="#00F0FF"/>
    </svg>`,

    // 04: Global Network Orbit
    `<svg width="52" height="52" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="12" cy="12" r="3.5" stroke="#00F0FF" stroke-width="2"/>
      <circle cx="4" cy="9" r="2" stroke="#A855F7" stroke-width="1.8"/>
      <circle cx="20" cy="9" r="2" stroke="#A855F7" stroke-width="1.8"/>
      <circle cx="7" cy="19" r="2" stroke="#00F0FF" stroke-width="1.8"/>
      <circle cx="17" cy="19" r="2" stroke="#00F0FF" stroke-width="1.8"/>
      <path d="M6 9.5L9.5 11M14.5 11L18 9.5M8.5 18L10.5 14.5M15.5 18L13.5 14.5" stroke="rgba(255,255,255,0.4)" stroke-width="1.4" stroke-dasharray="2 2"/>
    </svg>`,

    // 05: Dominate Niche Target Radar
    `<svg width="52" height="52" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="emblemGrad5" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse">
          <stop stop-color="#00F0FF"/>
          <stop offset="1" stop-color="#A855F7"/>
        </linearGradient>
      </defs>
      <circle cx="12" cy="12" r="9" stroke="url(#emblemGrad5)" stroke-width="1.8"/>
      <circle cx="12" cy="12" r="5" stroke="#00F0FF" stroke-width="1.5"/>
      <circle cx="12" cy="12" r="1.5" fill="#A855F7"/>
      <path d="M12 2V6M12 18V22M2 12H6M18 12H22" stroke="rgba(0, 240, 255, 0.75)" stroke-width="1.5" stroke-linecap="round"/>
    </svg>`,

    // 06: Future-Proof Diamond Armor
    `<svg width="52" height="52" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <linearGradient id="emblemGrad6" x1="0" y1="0" x2="24" y2="24" gradientUnits="userSpaceOnUse">
          <stop stop-color="#00F0FF"/>
          <stop offset="1" stop-color="#A855F7"/>
        </linearGradient>
      </defs>
      <path d="M12 2L3 7L12 22L21 7L12 2Z" stroke="url(#emblemGrad6)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M3 7H21M12 2V22M7 7L12 22M17 7L12 22" stroke="rgba(0, 240, 255, 0.65)" stroke-width="1.2" stroke-linejoin="round"/>
    </svg>`
  ];

  const PILLARS_DATA = [
    {
      num: '01',
      title: 'Become An Industry Leader',
      metric: 'Top 1% Industry Authority',
      summary: 'Positions your profile & insights as the benchmark in your industry—unlocking media features and speaking invitations.',
      tag: 'AUTHORITY MATRIX'
    },
    {
      num: '02',
      title: 'Build Trust with Audience',
      metric: '10x Inbound Trust & Credibility',
      summary: 'Proves high-level competence upfront, eliminating "starting from scratch" with prospective clients and investors.',
      tag: 'CREDIBILITY CORE'
    },
    {
      num: '03',
      title: 'High-Growth Opportunities',
      metric: '24/7 Organic Pipeline',
      summary: 'Acts as an automated inbound engine that consistently attracts partnerships, advisory roles, and high-tier contracts.',
      tag: 'GROWTH ENGINE'
    },
    {
      num: '04',
      title: 'Expand Your Network',
      metric: 'Direct Access to CXOs',
      summary: 'Opens doors to curated masterminds, C-level peers, and strategic collaborators who actively search for top voices.',
      tag: 'ELITE NETWORK'
    },
    {
      num: '05',
      title: 'Dominate Your Niche',
      metric: 'Uncontested Market USP',
      summary: 'Sharpens your distinct value proposition so you become the immediate, authoritative choice over any competitor.',
      tag: 'MARKET DOMINANCE'
    },
    {
      num: '06',
      title: 'Future-Proof Your Career',
      metric: 'Lifelong Career Capital',
      summary: 'Creates persistent personal equity that stays with you forever across business ventures, board seats, and market shifts.',
      tag: 'CAREER CAPITAL'
    }
  ];

  class SplitShowcaseController {
    constructor() {
      this.section = document.getElementById('why-essential');
      if (!this.section) return;

      this.canvas = document.getElementById('vg-pb-showcase-canvas');
      this.emblemEl = document.getElementById('vgStageEmblem');
      this.pillarLabel = document.getElementById('vgStagePillarLabel');
      this.tagEl = document.getElementById('vgStageTag');
      this.metricEl = document.getElementById('vgStageMetric');
      this.titleEl = document.getElementById('vgStageTitle');
      this.summaryEl = document.getElementById('vgStageSummary');
      this.progressBar = document.getElementById('vgStageProgressBar');
      this.items = Array.from(document.querySelectorAll('.vg-pb-showcase-item'));

      this.currentIndex = 0;
      this.cycleInterval = 5000; // 5 seconds per slide
      this.lastSwitchTime = performance.now();
      this.isPaused = false;
      this.isIntersecting = false;
      this.isTabActive = !document.hidden;
      this.time = 0;
      this.animId = null;

      // Particles for 3D orbital background
      this.particles = [];
      for (let i = 0; i < 20; i++) {
        this.particles.push({
          angle: Math.random() * Math.PI * 2,
          radius: 50 + Math.random() * 75,
          speed: (0.005 + Math.random() * 0.008) * (Math.random() > 0.5 ? 1 : -1),
          yOffset: (Math.random() - 0.5) * 35,
          size: 1.0 + Math.random() * 1.6,
          alpha: 0.3 + Math.random() * 0.5
        });
      }

      this.initCanvas();
      this.bindEvents();
      this.setupObserver();
      this.setActivePillar(0, false);
    }

    initCanvas() {
      if (!this.canvas) return;
      this.ctx = this.canvas.getContext('2d');
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);
      this.resizeCanvas();
    }

    resizeCanvas() {
      if (!this.canvas) return;
      const rect = this.canvas.parentElement.getBoundingClientRect();
      const w = Math.floor(rect.width || 360);
      const h = Math.floor(rect.height || 230);

      this.width = w;
      this.height = h;
      this.canvas.width = w * this.dpr;
      this.canvas.height = h * this.dpr;
      this.canvas.style.width = w + 'px';
      this.canvas.style.height = h + 'px';

      this.ctx.setTransform(1, 0, 0, 1, 0, 0);
      this.ctx.scale(this.dpr, this.dpr);
    }

    setupObserver() {
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            this.isIntersecting = entry.isIntersecting;
            if (this.isIntersecting && this.isTabActive) {
              this.lastSwitchTime = performance.now();
              if (!this.animId) this.startLoop();
            } else {
              if (this.animId) {
                cancelAnimationFrame(this.animId);
                this.animId = null;
              }
            }
          });
        }, { threshold: 0.10 });
        observer.observe(this.section);
      } else {
        this.isIntersecting = true;
        this.startLoop();
      }

      document.addEventListener('visibilitychange', () => {
        this.isTabActive = !document.hidden;
        if (this.isTabActive && this.isIntersecting && !this.animId) {
          this.lastSwitchTime = performance.now();
          this.startLoop();
        } else if (!this.isTabActive && this.animId) {
          cancelAnimationFrame(this.animId);
          this.animId = null;
        }
      });
    }

    setActivePillar(index, resetTimer = true) {
      this.currentIndex = (index + PILLARS_DATA.length) % PILLARS_DATA.length;
      const data = PILLARS_DATA[this.currentIndex];

      // Update Left Stage Info
      if (this.pillarLabel) this.pillarLabel.textContent = `PILLAR ${data.num} OF 06`;
      if (this.tagEl) this.tagEl.textContent = data.tag;
      if (this.metricEl) this.metricEl.innerHTML = `<i class="fa-solid fa-chart-line"></i> <span>${data.metric}</span>`;
      if (this.titleEl) this.titleEl.textContent = data.title;
      if (this.summaryEl) this.summaryEl.textContent = data.summary;

      // Update Center Emblem with smooth scale fade
      if (this.emblemEl) {
        this.emblemEl.style.opacity = '0';
        this.emblemEl.style.transform = 'scale(0.88) translateZ(0)';
        setTimeout(() => {
          this.emblemEl.innerHTML = EMBLEMS[this.currentIndex];
          this.emblemEl.style.opacity = '1';
          this.emblemEl.style.transform = 'scale(1) translateZ(0)';
        }, 140);
      }

      // Update Right List Items
      this.items.forEach((item, i) => {
        if (i === this.currentIndex) {
          item.classList.add('is-active');
        } else {
          item.classList.remove('is-active');
        }
      });

      if (resetTimer) {
        this.lastSwitchTime = performance.now();
      }
    }

    bindEvents() {
      // Click & Hover on right list rows
      this.items.forEach((item, index) => {
        item.addEventListener('click', () => {
          this.setActivePillar(index, true);
        });
        item.addEventListener('mouseenter', () => {
          this.isPaused = true;
          this.setActivePillar(index, false);
        });
      });

      // Pause auto-cycle when hovering over stage or list
      const stageWrap = document.querySelector('.vg-pb-showcase-stage-wrap');
      const listWrap = document.getElementById('vgPbShowcaseList');

      [stageWrap, listWrap].forEach(el => {
        if (el) {
          el.addEventListener('mouseenter', () => { this.isPaused = true; });
          el.addEventListener('mouseleave', () => {
            this.isPaused = false;
            this.lastSwitchTime = performance.now();
          });
        }
      });

      window.addEventListener('resize', () => {
        this.resizeCanvas();
      }, { passive: true });

      // Automatically re-measure and adapt canvas dimensions on dynamic card resize
      if (typeof ResizeObserver !== 'undefined' && this.canvas && this.canvas.parentElement) {
        this.resizeObserver = new ResizeObserver(() => {
          this.resizeCanvas();
        });
        this.resizeObserver.observe(this.canvas.parentElement);
      }
    }

    startLoop() {
      const render = (now) => {
        if (!this.isIntersecting || !this.isTabActive) {
          this.animId = null;
          return;
        }

        this.time += 0.016;

        // Auto-cycle logic (ONLY runs when section is visible in viewport)
        if (!this.isPaused) {
          const elapsed = now - this.lastSwitchTime;
          const progress = Math.min(1, elapsed / this.cycleInterval);
          if (this.progressBar) {
            this.progressBar.style.width = (progress * 100) + '%';
          }

          if (elapsed >= this.cycleInterval) {
            this.setActivePillar(this.currentIndex + 1, true);
          }
        } else {
          if (this.progressBar) {
            this.progressBar.style.width = '100%';
          }
        }

        // Draw 3D Gyroscope & Orbital Light Rings
        if (this.ctx) {
          this.drawStage();
        }

        this.animId = requestAnimationFrame(render);
      };

      this.animId = requestAnimationFrame(render);
    }

    drawStage() {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;
      const cx = w / 2;
      const cy = h / 2 - 4;

      ctx.clearRect(0, 0, w, h);

      // 1. Ambient Background Core Aura
      const auraGrad = ctx.createRadialGradient(cx, cy, 10, cx, cy, 105);
      auraGrad.addColorStop(0, 'rgba(0, 240, 255, 0.16)');
      auraGrad.addColorStop(0.45, 'rgba(168, 85, 247, 0.12)');
      auraGrad.addColorStop(1, 'rgba(7, 7, 15, 0)');
      ctx.fillStyle = auraGrad;
      ctx.beginPath();
      ctx.arc(cx, cy, 105, 0, Math.PI * 2);
      ctx.fill();

      // 2. Circulating 3D Sparkle Particles
      this.particles.forEach(p => {
        p.angle += p.speed;
        const px = cx + Math.cos(p.angle) * p.radius;
        const py = cy + Math.sin(p.angle) * (p.radius * 0.42) + p.yOffset;

        ctx.fillStyle = p.speed > 0 ? `rgba(0, 240, 255, ${p.alpha})` : `rgba(168, 85, 247, ${p.alpha})`;
        ctx.beginPath();
        ctx.arc(px, py, p.size, 0, Math.PI * 2);
        ctx.fill();
      });

      // 3. Ring 1: Outer Electric Cyan 3D Orbital Ring
      const rot1 = this.time * 0.65;
      ctx.save();
      ctx.translate(cx, cy);
      ctx.rotate(0.24); // 3D tilt
      ctx.beginPath();
      ctx.ellipse(0, 0, 90, 36, rot1 * 0.5, 0, Math.PI * 2);
      ctx.strokeStyle = 'rgba(0, 240, 255, 0.75)';
      ctx.lineWidth = 1.6;
      ctx.shadowColor = '#00F0FF';
      ctx.shadowBlur = 10;
      ctx.stroke();

      // Orbiting cyan satellite bead
      const bead1X = Math.cos(rot1) * 90;
      const bead1Y = Math.sin(rot1) * 36;
      ctx.fillStyle = '#00F0FF';
      ctx.beginPath();
      ctx.arc(bead1X, bead1Y, 3.0, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();

      // 4. Ring 2: Middle Cyber Violet 3D Orbital Ring
      const rot2 = -this.time * 0.55;
      ctx.save();
      ctx.translate(cx, cy);
      ctx.rotate(-0.35); // Counter 3D tilt
      ctx.beginPath();
      ctx.ellipse(0, 0, 76, 28, rot2 * 0.5, 0, Math.PI * 2);
      ctx.strokeStyle = 'rgba(168, 85, 247, 0.70)';
      ctx.lineWidth = 1.4;
      ctx.shadowColor = '#A855F7';
      ctx.shadowBlur = 8;
      ctx.stroke();

      // Orbiting violet satellite bead
      const bead2X = Math.cos(rot2) * 76;
      const bead2Y = Math.sin(rot2) * 28;
      ctx.fillStyle = '#A855F7';
      ctx.beginPath();
      ctx.arc(bead2X, bead2Y, 2.6, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();

      // 5. Ring 3: Inner Reticle Focus Ring
      ctx.save();
      ctx.translate(cx, cy);
      ctx.beginPath();
      ctx.ellipse(0, 0, 56, 20, this.time * 0.35, 0, Math.PI * 2);
      ctx.strokeStyle = 'rgba(255, 255, 255, 0.22)';
      ctx.lineWidth = 1;
      ctx.setLineDash([3, 5]);
      ctx.stroke();
      ctx.restore();
    }
  }

  // Initialize on DOM Ready
  function initShowcase() {
    new SplitShowcaseController();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShowcase);
  } else {
    initShowcase();
  }
})();
