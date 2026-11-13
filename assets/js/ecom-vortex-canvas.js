/**
 * ============================================================================
 * VG DIGITAL FALCON — ECOMMERCE SEO HERO EXECUTIVE AURORA & PERSPECTIVE GRID
 * File: assets/js/ecom-vortex-canvas.js
 * 
 * Concept: Fortune-500 / Stripe-Style Executive Background Animation
 * 
 * Features:
 * 1. Smooth Flowing Aurora Mesh Wave:
 *    - Velvety, low-opacity 3D flowing light wave using Deep Cyber Violet (#A855F7)
 *      and Electric Cyan (#00F0FF) at strictly 18%–25% opacity against #07070F backdrop.
 *    - Gliding smoothly at a slow, refined, executive pace.
 *    - Delicate silk sheen filaments tracing undulating wave ridges.
 * 2. Subtle Architectural Perspective Grid:
 *    - Razor-thin, softly fading dark-glass coordinate grid (rgba(255, 255, 255, 0.03)).
 *    - Soft perspective vanishing plane with radial vignette falloff.
 *    - Occasional soft cyan light pulses (rgba(0, 240, 255, 0.35)) traveling smoothly
 *      along grid lines.
 *    - Absolutely NO sharp wireframe cubes, NO random spiral lines, NO floating text.
 * 3. Razor-Sharp Foreground:
 *    - Headline and $99 Free Audit card remain 100% crisp, legible, and high-contrast.
 * 4. 60fps Performance:
 *    - Continuous smooth animation with mouse parallax damping.
 *    - Automatically pauses via IntersectionObserver when scrolled out of view.
 * ============================================================================
 */

(function () {
  'use strict';

  class EcommerceExecutiveAurora {
    constructor(canvas) {
      this.canvas = canvas;
      this.ctx = canvas.getContext('2d', { alpha: true });
      if (!this.ctx) return;

      this.hero = canvas.closest('.vg-seo-hero') || canvas.parentElement;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);

      // Executive Color Palette (Strictly 18% - 25% Opacity Guard)
      this.colors = {
        cyan: { r: 0, g: 240, b: 255 },       // #00F0FF Electric Cyan
        violet: { r: 168, g: 85, b: 247 },   // #A855F7 Deep Cyber Violet
        indigo: { r: 99, g: 102, b: 241 },   // #6366F1 Deep Indigo
        slate: { r: 148, g: 163, b: 184 }
      };

      this.width = 0;
      this.height = 0;
      this.time = 0;
      this.animId = null;
      this.isIntersecting = true;
      this.isTabActive = true;

      // Mouse Parallax with Damping
      this.mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };

      // Perspective Grid Pulses
      this.gridPulses = [];

      this.init();
    }

    init() {
      this.resize();
      this.buildGridPulses();
      this.bindEvents();
      this.setupObserver();
      this.start();
    }

    resize() {
      if (!this.hero) return;
      this.width = this.hero.clientWidth || window.innerWidth;
      this.height = this.hero.clientHeight || 750;

      this.canvas.width = Math.floor(this.width * this.dpr);
      this.canvas.height = Math.floor(this.height * this.dpr);
      this.canvas.style.width = this.width + 'px';
      this.canvas.style.height = this.height + 'px';

      this.ctx.scale(this.dpr, this.dpr);
    }

    buildGridPulses() {
      // 5 Soft Cyan light pulses gliding quietly along perspective grid lines
      this.gridPulses = [];
      const pulseCount = 5;

      for (let i = 0; i < pulseCount; i++) {
        this.gridPulses.push({
          type: i % 2 === 0 ? 'longitudinal' : 'transverse',
          lineIndex: i,
          progress: Math.random(), // 0 to 1 along the line
          speed: 0.0018 + Math.random() * 0.0022, // Slow, refined pace
          length: 50 + Math.random() * 40,
          opacity: 0.22 + Math.random() * 0.12 // 22% - 34% soft glow
        });
      }
    }

    bindEvents() {
      window.addEventListener('resize', () => {
        this.resize();
      }, { passive: true });

      if (this.hero) {
        this.hero.addEventListener('mousemove', (e) => {
          const rect = this.hero.getBoundingClientRect();
          const normX = ((e.clientX - rect.left) / this.width - 0.5) * 2;
          const normY = ((e.clientY - rect.top) / this.height - 0.5) * 2;
          this.mouse.targetX = normX;
          this.mouse.targetY = normY;
        }, { passive: true });

        this.hero.addEventListener('mouseleave', () => {
          this.mouse.targetX = 0;
          this.mouse.targetY = 0;
        }, { passive: true });
      }

      document.addEventListener('visibilitychange', () => {
        this.isTabActive = !document.hidden;
        if (this.isTabActive && this.isIntersecting) {
          this.start();
        } else {
          this.stop();
        }
      });
    }

    setupObserver() {
      if ('IntersectionObserver' in window && this.hero) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            this.isIntersecting = entry.isIntersecting;
            if (this.isIntersecting && this.isTabActive) {
              this.start();
            } else {
              this.stop();
            }
          });
        }, { threshold: 0.1 });
        observer.observe(this.hero);
      }
    }

    start() {
      if (!this.animId) {
        this.lastTime = performance.now();
        this.render = this.render.bind(this);
        this.animId = requestAnimationFrame(this.render);
      }
    }

    stop() {
      if (this.animId) {
        cancelAnimationFrame(this.animId);
        this.animId = null;
      }
    }

    /* =========================================================================
       1. SUBTLE ARCHITECTURAL PERSPECTIVE GRID
       ========================================================================= */
    drawPerspectiveGrid() {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;

      // Horizon line in 3D perspective
      const horizonY = h * 0.38 + this.mouse.y * 12;
      const groundBottomY = h + 20;

      // Vanishing point on horizon
      const vpX = w * 0.5 + this.mouse.x * 28;
      const vpY = horizonY;

      ctx.save();

      // Longitudinal Lines (perspective lines radiating outward from vanishing point)
      const longLinesCount = w > 768 ? 22 : 12;
      const spreadWidth = w * 2.2;
      const startX = vpX - spreadWidth * 0.5;
      const stepX = spreadWidth / longLinesCount;

      ctx.lineWidth = 1;

      for (let i = 0; i <= longLinesCount; i++) {
        const bottomX = startX + i * stepX;
        
        // Linear fade: fully transparent near horizon, subtle (0.03) toward bottom
        const grad = ctx.createLinearGradient(vpX, horizonY, bottomX, groundBottomY);
        grad.addColorStop(0, 'rgba(255, 255, 255, 0)');
        grad.addColorStop(0.35, 'rgba(255, 255, 255, 0.015)');
        grad.addColorStop(1, 'rgba(255, 255, 255, 0.035)');

        ctx.strokeStyle = grad;
        ctx.beginPath();
        ctx.moveTo(vpX + (bottomX - vpX) * 0.15, horizonY + (groundBottomY - horizonY) * 0.15);
        ctx.lineTo(bottomX, groundBottomY);
        ctx.stroke();
      }

      // Transverse Lines (depth rings / horizontal grid rungs spaced with perspective)
      const transLinesCount = 14;
      for (let j = 1; j <= transLinesCount; j++) {
        const factor = Math.pow(j / transLinesCount, 2.1); // Perspective compression
        const currentY = horizonY + (groundBottomY - horizonY) * factor;
        const leftX = vpX + (startX - vpX) * factor;
        const rightX = vpX + ((startX + spreadWidth) - vpX) * factor;

        const alpha = factor * 0.032;
        ctx.strokeStyle = `rgba(255, 255, 255, ${alpha})`;
        ctx.beginPath();
        ctx.moveTo(Math.max(leftX, 0), currentY);
        ctx.lineTo(Math.min(rightX, w), currentY);
        ctx.stroke();

        // Subtle coordinate '+' ticks at intersections (Stripe style)
        if (j % 3 === 0 && w > 768) {
          for (let i = 2; i < longLinesCount; i += 3) {
            const bottomX = startX + i * stepX;
            const intersectX = vpX + (bottomX - vpX) * factor;
            if (intersectX > 40 && intersectX < w - 40) {
              const tickSize = 3;
              ctx.strokeStyle = `rgba(0, 240, 255, ${alpha * 1.5})`;
              ctx.beginPath();
              ctx.moveTo(intersectX - tickSize, currentY);
              ctx.lineTo(intersectX + tickSize, currentY);
              ctx.moveTo(intersectX, currentY - tickSize);
              ctx.lineTo(intersectX, currentY + tickSize);
              ctx.stroke();
            }
          }
        }
      }

      // Render Traveling Cyan Light Pulses along perspective lines
      this.gridPulses.forEach(p => {
        p.progress += p.speed;
        if (p.progress > 1) {
          p.progress = 0;
          p.lineIndex = Math.floor(Math.random() * longLinesCount);
        }

        const bottomX = startX + p.lineIndex * stepX;
        const t = p.progress;

        // Position along the longitudinal line
        const factor = Math.pow(t, 1.8);
        const px = vpX + (bottomX - vpX) * factor;
        const py = horizonY + (groundBottomY - horizonY) * factor;

        // Tail length in perspective
        const tailFactor = Math.max(0, Math.pow(Math.max(0, t - 0.08), 1.8));
        const tailX = vpX + (bottomX - vpX) * tailFactor;
        const tailY = horizonY + (groundBottomY - horizonY) * tailFactor;

        // Fade pulse near horizon and near bottom
        const fade = Math.sin(t * Math.PI) * p.opacity;

        const pulseGrad = ctx.createLinearGradient(tailX, tailY, px, py);
        pulseGrad.addColorStop(0, 'rgba(0, 240, 255, 0)');
        pulseGrad.addColorStop(1, `rgba(0, 240, 255, ${fade})`);

        ctx.strokeStyle = pulseGrad;
        ctx.lineWidth = 1.8;
        ctx.beginPath();
        ctx.moveTo(tailX, tailY);
        ctx.lineTo(px, py);
        ctx.stroke();

        // Soft glow head
        ctx.beginPath();
        ctx.arc(px, py, 1.4, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(0, 240, 255, ${fade * 1.3})`;
        ctx.fill();
      });

      ctx.restore();
    }

    /* =========================================================================
       2. SMOOTH FLOWING VELVET AURORA MESH WAVE (18% - 25% OPACITY)
       ========================================================================= */
    drawAuroraMeshWave() {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;
      const t = this.time;

      ctx.save();

      // Atmospheric Deep Light Pools (Stripe Executive Ambient Radiance)
      const glow1X = w * 0.35 + Math.sin(t * 0.25) * 50 + this.mouse.x * 20;
      const glow1Y = h * 0.48 + Math.cos(t * 0.2) * 35 + this.mouse.y * 15;
      const rad1 = Math.max(w * 0.38, 320);

      const grad1 = ctx.createRadialGradient(glow1X, glow1Y, 0, glow1X, glow1Y, rad1);
      grad1.addColorStop(0, 'rgba(168, 85, 247, 0.22)'); // 22% Cyber Violet
      grad1.addColorStop(0.5, 'rgba(120, 50, 220, 0.10)');
      grad1.addColorStop(1, 'rgba(7, 7, 15, 0)');

      ctx.fillStyle = grad1;
      ctx.beginPath();
      ctx.arc(glow1X, glow1Y, rad1, 0, Math.PI * 2);
      ctx.fill();

      const glow2X = w * 0.65 + Math.cos(t * 0.22) * 60 + this.mouse.x * 25;
      const glow2Y = h * 0.44 + Math.sin(t * 0.28) * 30 + this.mouse.y * 15;
      const rad2 = Math.max(w * 0.34, 300);

      const grad2 = ctx.createRadialGradient(glow2X, glow2Y, 0, glow2X, glow2Y, rad2);
      grad2.addColorStop(0, 'rgba(0, 240, 255, 0.19)'); // 19% Electric Cyan
      grad2.addColorStop(0.45, 'rgba(56, 189, 248, 0.08)');
      grad2.addColorStop(1, 'rgba(7, 7, 15, 0)');

      ctx.fillStyle = grad2;
      ctx.beginPath();
      ctx.arc(glow2X, glow2Y, rad2, 0, Math.PI * 2);
      ctx.fill();

      // Layered Flowing Aurora Waves
      // Wave 1: Deep Cyber Violet Velvet Wave
      this.renderFlowingRibbon({
        baseY: h * 0.52 + this.mouse.y * 14,
        amplitude1: 55,
        amplitude2: 32,
        freq1: 0.0016,
        freq2: 0.0034,
        speed1: 0.32,
        speed2: 0.45,
        colorStart: 'rgba(168, 85, 247, 0.23)', // 23% opacity
        colorMid: 'rgba(126, 34, 206, 0.12)',
        colorEnd: 'rgba(7, 7, 15, 0)',
        sheenColor: 'rgba(168, 85, 247, 0.25)',
        depthHeight: 180
      });

      // Wave 2: Electric Cyan Flowing Aurora Crest
      this.renderFlowingRibbon({
        baseY: h * 0.46 + this.mouse.y * 18,
        amplitude1: 48,
        amplitude2: 28,
        freq1: 0.0020,
        freq2: 0.0040,
        speed1: -0.38,
        speed2: 0.50,
        colorStart: 'rgba(0, 240, 255, 0.21)',  // 21% opacity
        colorMid: 'rgba(56, 189, 248, 0.09)',
        colorEnd: 'rgba(7, 7, 15, 0)',
        sheenColor: 'rgba(0, 240, 255, 0.28)',
        depthHeight: 160
      });

      // Wave 3: Subtle Violet-Cyan Harmonic Wave (Interweaving)
      this.renderFlowingRibbon({
        baseY: h * 0.58 + this.mouse.y * 10,
        amplitude1: 42,
        amplitude2: 24,
        freq1: 0.0018,
        freq2: 0.0030,
        speed1: 0.25,
        speed2: -0.30,
        colorStart: 'rgba(147, 51, 234, 0.18)', // 18% opacity
        colorMid: 'rgba(0, 240, 255, 0.08)',
        colorEnd: 'rgba(7, 7, 15, 0)',
        sheenColor: 'rgba(0, 240, 255, 0.20)',
        depthHeight: 190
      });

      ctx.restore();
    }

    renderFlowingRibbon(cfg) {
      const ctx = this.ctx;
      const w = this.width;
      const t = this.time;
      const step = 10; // 10px precision for silky bezier-like smooth curve

      const points = [];
      for (let x = 0; x <= w + step; x += step) {
        const y = cfg.baseY +
          Math.sin(x * cfg.freq1 + t * cfg.speed1) * cfg.amplitude1 +
          Math.cos(x * cfg.freq2 - t * cfg.speed2) * cfg.amplitude2 +
          Math.sin((x * 0.001) + t * 0.15) * 12;
        points.push({ x, y });
      }

      if (points.length < 2) return;

      // Filled Velvety Mesh Gradient Body
      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
      }
      ctx.lineTo(w, cfg.baseY + cfg.depthHeight);
      ctx.lineTo(0, cfg.baseY + cfg.depthHeight);
      ctx.closePath();

      const waveGrad = ctx.createLinearGradient(0, cfg.baseY - 40, 0, cfg.baseY + cfg.depthHeight);
      waveGrad.addColorStop(0, cfg.colorStart);
      waveGrad.addColorStop(0.45, cfg.colorMid);
      waveGrad.addColorStop(1, cfg.colorEnd);

      ctx.fillStyle = waveGrad;
      ctx.fill();

      // Delicate Silk Sheen Filament along Ridge
      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
      }
      ctx.strokeStyle = cfg.sheenColor;
      ctx.lineWidth = 1.3;
      ctx.stroke();
    }

    render(now) {
      if (!this.isIntersecting || !this.isTabActive) {
        this.animId = null;
        return;
      }

      const delta = (now - this.lastTime) * 0.001;
      this.lastTime = now;

      // Slow, refined executive flow pace
      this.time += Math.min(delta, 0.05) * 1.1;

      // Mouse Lerp Damping
      this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.04;
      this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.04;

      // Clear Canvas
      this.ctx.clearRect(0, 0, this.width, this.height);

      // Render 1: Subtle Architectural Perspective Grid & Light Pulses
      this.drawPerspectiveGrid();

      // Render 2: Smooth Flowing Velvet Aurora Mesh Wave (18% - 25% Opacity)
      this.drawAuroraMeshWave();

      this.animId = requestAnimationFrame(this.render);
    }
  }

  // Auto-initialize on DOM ready
  function initAurora() {
    const canvas = document.getElementById('vg-ecom-vortex-canvas');
    if (canvas) {
      new EcommerceExecutiveAurora(canvas);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAurora);
  } else {
    initAurora();
  }
})();
