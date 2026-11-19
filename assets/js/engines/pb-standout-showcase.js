/**
 * ============================================================================
 * VG DIGITAL FALCON — STANDOUT SHOWCASE CONTROLLER
 * File: assets/js/pb-standout-showcase.js
 * 
 * Interactive 3D Showcase for 'How VG Digital Falcon Stands Out in Personal Branding?'
 * - Left 3D Tilted Card: rotateY(8deg) perspective with dynamic sparkline & metrics
 * - Right List: 6 sleek horizontal glass rows with smooth accordion expand/collapse
 * - Auto-cycle every 3.5 seconds with pause on hover & instant manual click switching
 * - High-performance Canvas 2D sparkline with morphing bezier trajectories
 * - Includes IntersectionObserver to completely prevent background layout shifts
 * ============================================================================
 */

(function () {
  'use strict';

  const STANDOUT_DATA = [
    {
      num: '01',
      title: 'Hyper-Targeted Personal Branding Strategies',
      metricVal: '+380%',
      growthPill: 'Inbound Leads Surge',
      metricSub: 'Targeted Industry Voice & Market Authority',
      wavePoints: [25, 40, 55, 75, 110, 160, 240, 380]
    },
    {
      num: '02',
      title: 'LinkedIn & Social Media Mastery',
      metricVal: '2.4M+',
      growthPill: 'High-Intent Impressions',
      metricSub: 'Profile Conversion Optimization & Executive Reach',
      wavePoints: [30, 65, 85, 140, 190, 230, 280, 360]
    },
    {
      num: '03',
      title: 'Performance-Backed ROI Guarantee',
      metricVal: '4.8x',
      growthPill: 'Pipeline ROI Multiple',
      metricSub: 'Tangible Business Inquiries & Enterprise Deals',
      wavePoints: [20, 45, 70, 95, 150, 210, 310, 420]
    },
    {
      num: '04',
      title: 'Excellence for Startups & Executives',
      metricVal: '100%',
      growthPill: 'Tailored Tier Alignment',
      metricSub: 'Flexible High-Value Positioning Without Retainer Bloat',
      wavePoints: [35, 55, 80, 120, 160, 220, 290, 370]
    },
    {
      num: '05',
      title: 'Your Brand, Your Voice—Amplified',
      metricVal: '10x',
      growthPill: 'Authentic Differentiation',
      metricSub: 'Distinctive Narrative That Cuts Through Generic Noise',
      wavePoints: [28, 50, 90, 130, 175, 240, 330, 440]
    }
  ];

  class StandoutShowcaseController {
    constructor() {
      this.section = document.getElementById('how-we-stand-out');
      if (!this.section) return;

      this.card3D = document.getElementById('vgStandout3DCard');
      this.canvas = document.getElementById('vg-standout-sparkline-canvas');
      this.metricValEl = document.getElementById('vgStandoutMetricVal');
      this.growthTextEl = document.getElementById('vgStandoutGrowthText');
      this.metricSubEl = document.getElementById('vgStandoutMetricSub');
      this.pillarNumEl = document.getElementById('vgStandoutPillarNum');
      this.pillarTitleEl = document.getElementById('vgStandoutPillarTitle');
      this.progressFillEl = document.getElementById('vgStandoutProgressFill');
      this.items = Array.from(document.querySelectorAll('.vg-standout-item'));

      this.currentIndex = 0;
      this.cycleInterval = 3500; // 3.5 seconds per slide
      this.lastSwitchTime = performance.now();
      this.isPaused = false;
      this.isIntersecting = false;
      this.isTabActive = !document.hidden;
      this.time = 0;
      this.animId = null;

      // Current sparkline curve points for smooth morphing
      this.currentCurve = [...STANDOUT_DATA[0].wavePoints];
      this.targetCurve = [...STANDOUT_DATA[0].wavePoints];

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
      const h = Math.floor(rect.height || 140);

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

      if ('ResizeObserver' in window && this.canvas && this.canvas.parentElement) {
        const ro = new ResizeObserver(() => {
          this.resizeCanvas();
        });
        ro.observe(this.canvas.parentElement);
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
      this.currentIndex = (index + STANDOUT_DATA.length) % STANDOUT_DATA.length;
      const data = STANDOUT_DATA[this.currentIndex];

      // Update Left Stage Metrics
      if (this.metricValEl) {
        this.metricValEl.style.opacity = '0';
        this.metricValEl.style.transform = 'translateY(4px)';
        setTimeout(() => {
          this.metricValEl.textContent = data.metricVal;
          this.metricValEl.style.opacity = '1';
          this.metricValEl.style.transform = 'translateY(0)';
        }, 110);
      }
      if (this.growthTextEl) this.growthTextEl.textContent = data.growthPill;
      if (this.metricSubEl) this.metricSubEl.textContent = data.metricSub;
      if (this.pillarNumEl) this.pillarNumEl.textContent = `PILLAR ${data.num}`;
      if (this.pillarTitleEl) this.pillarTitleEl.textContent = data.title;

      // Target wave curve for morphing
      this.targetCurve = [...data.wavePoints];

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
      const stageWrap = document.querySelector('.vg-standout-stage-container');
      const listWrap = document.getElementById('vgStandoutList');

      [stageWrap, listWrap].forEach(el => {
        if (el) {
          el.addEventListener('mouseenter', () => { this.isPaused = true; });
          el.addEventListener('mouseleave', () => {
            this.isPaused = false;
            this.lastSwitchTime = performance.now();
          });
        }
      });

      // Subtle 3D perspective mouse tilt on desktop without layout thrashing
      if (this.card3D && window.innerWidth >= 992) {
        let isTicking = false;
        this.section.addEventListener('mousemove', (e) => {
          if (isTicking) return;
          isTicking = true;
          requestAnimationFrame(() => {
            isTicking = false;
            const w = window.innerWidth;
            const h = window.innerHeight;
            const dx = (e.clientX - w / 2) / (w / 2);
            const dy = (e.clientY - h / 2) / (h / 2);
            this.card3D.style.transform = `rotateY(${8 + dx * 4}deg) rotateX(${2 - dy * 3}deg)`;
          });
        }, { passive: true });

        this.section.addEventListener('mouseleave', () => {
          this.card3D.style.transform = 'rotateY(8deg) rotateX(2deg)';
        }, { passive: true });
      }

      window.addEventListener('resize', () => {
        this.resizeCanvas();
      }, { passive: true });
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
          if (this.progressFillEl) {
            this.progressFillEl.style.width = (progress * 100) + '%';
          }

          if (elapsed >= this.cycleInterval) {
            this.setActivePillar(this.currentIndex + 1, true);
          }
        } else {
          if (this.progressFillEl) {
            this.progressFillEl.style.width = '100%';
          }
        }

        // Morph current curve toward target curve
        for (let i = 0; i < this.currentCurve.length; i++) {
          this.currentCurve[i] += (this.targetCurve[i] - this.currentCurve[i]) * 0.12;
        }

        // Draw animated sparkline
        if (this.ctx) {
          this.drawSparkline();
        }

        this.animId = requestAnimationFrame(render);
      };

      this.animId = requestAnimationFrame(render);
    }

    drawSparkline() {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;
      const paddingX = 14;
      const paddingBottom = 16;
      const paddingTop = 20;

      ctx.clearRect(0, 0, w, h);

      const usableW = w - paddingX * 2;
      const usableH = h - paddingTop - paddingBottom;
      const numPoints = this.currentCurve.length;
      const stepX = usableW / (numPoints - 1);

      // Max value for scaling
      const maxVal = 460;

      // Calculate pixel coordinates
      const points = this.currentCurve.map((val, i) => {
        const x = paddingX + i * stepX;
        const undulation = Math.sin(this.time * 2.2 + i * 0.7) * 3.5;
        const normY = Math.min(1, Math.max(0, val / maxVal));
        const y = h - paddingBottom - (normY * usableH) + undulation;
        return { x, y };
      });

      // 1. Subtle Background Grid Lines
      ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
      ctx.lineWidth = 1;
      for (let g = 0; g < 3; g++) {
        const gy = paddingTop + (usableH / 2) * g;
        ctx.beginPath();
        ctx.moveTo(paddingX, gy);
        ctx.lineTo(w - paddingX, gy);
        ctx.stroke();
      }

      // 2. Gradient Area Fill beneath curve
      const areaGrad = ctx.createLinearGradient(0, paddingTop, 0, h - paddingBottom);
      areaGrad.addColorStop(0, 'rgba(0, 240, 255, 0.28)');
      areaGrad.addColorStop(0.5, 'rgba(168, 85, 247, 0.14)');
      areaGrad.addColorStop(1, 'rgba(7, 7, 15, 0)');

      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 0; i < points.length - 1; i++) {
        const xc = (points[i].x + points[i + 1].x) / 2;
        const yc = (points[i].y + points[i + 1].y) / 2;
        ctx.quadraticCurveTo(points[i].x, points[i].y, xc, yc);
      }
      ctx.lineTo(points[points.length - 1].x, points[points.length - 1].y);
      ctx.lineTo(points[points.length - 1].x, h - paddingBottom);
      ctx.lineTo(points[0].x, h - paddingBottom);
      ctx.closePath();
      ctx.fillStyle = areaGrad;
      ctx.fill();

      // 3. Main Glowing Neon Sparkline Path
      const strokeGrad = ctx.createLinearGradient(paddingX, 0, w - paddingX, 0);
      strokeGrad.addColorStop(0, '#00F0FF');
      strokeGrad.addColorStop(0.5, '#38BDF8');
      strokeGrad.addColorStop(1, '#A855F7');

      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 0; i < points.length - 1; i++) {
        const xc = (points[i].x + points[i + 1].x) / 2;
        const yc = (points[i].y + points[i + 1].y) / 2;
        ctx.quadraticCurveTo(points[i].x, points[i].y, xc, yc);
      }
      ctx.lineTo(points[points.length - 1].x, points[points.length - 1].y);
      ctx.strokeStyle = strokeGrad;
      ctx.lineWidth = 2.4;
      ctx.shadowColor = '#00F0FF';
      ctx.shadowBlur = 10;
      ctx.stroke();
      ctx.shadowBlur = 0; // reset

      // 4. Highlight Nodes & Apex Beacon
      points.forEach((p, i) => {
        if (i === points.length - 1) {
          // Apex glowing pulse beacon
          const pulseR = 5 + Math.sin(this.time * 4) * 2;
          ctx.beginPath();
          ctx.arc(p.x, p.y, pulseR + 4, 0, Math.PI * 2);
          ctx.fillStyle = 'rgba(0, 240, 255, 0.25)';
          ctx.fill();

          ctx.beginPath();
          ctx.arc(p.x, p.y, 4.5, 0, Math.PI * 2);
          ctx.fillStyle = '#00F0FF';
          ctx.shadowColor = '#00F0FF';
          ctx.shadowBlur = 12;
          ctx.fill();
          ctx.shadowBlur = 0;
        } else if (i % 2 === 1) {
          ctx.beginPath();
          ctx.arc(p.x, p.y, 2.5, 0, Math.PI * 2);
          ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
          ctx.fill();
        }
      });
    }
  }

  // Initialize on DOM Ready
  function initStandoutShowcase() {
    new StandoutShowcaseController();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStandoutShowcase);
  } else {
    initStandoutShowcase();
  }
})();
