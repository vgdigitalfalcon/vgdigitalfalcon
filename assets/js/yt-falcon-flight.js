/**
 * =============================================================================
 * VG DIGITAL - YOUTUBE ALGORITHM AURA & FLUID RETENTION WAVE ENGINE
 * File: assets/js/yt-falcon-flight.js
 * =============================================================================
 * Concept: Soft, Tranquil, Luxury Algorithm Aura & Organic Undulating Retention Waves
 * 
 * Features:
 * 1. Deep Obsidian #0a0614 Base:
 *    - Soft atmospheric radiance pools in Royal Violet (#7c3aed),
 *      YouTube Rose (#f43f5e), and Gentle Cyan (#22d3ee).
 * 2. 3 Soft Organic Undulating Silk Waves:
 *    - Flowing gently from bottom-left to top-right at low opacity (0.22 - 0.38).
 *    - Wave 1: Royal Violet (#7c3aed) with luminous additive depth.
 *    - Wave 2: Soft Magenta-Rose (#f43f5e) YouTube subtle accent.
 *    - Wave 3: Gentle Cyan (#22d3ee) harmonic retention ribbon.
 * 3. Dotted Analytics Particles:
 *    - Floating creator telemetry nodes gliding along wave ridges with additive glow.
 * 4. Tranquil Hypnotic Flow:
 *    - Ultra-smooth 0.25x speed, zero harsh laser beams or highway perspective.
 *    - Gentle mouse parallax with lerp damping.
 * 5. High Performance:
 *    - 60fps HTML5 Canvas 2D, IntersectionObserver auto-pause, zero GC churn.
 * =============================================================================
 */

(function (window, document) {
  'use strict';

  class YtAlgorithmAuraWave {
    constructor(canvas) {
      this.canvas = canvas;
      this.ctx = canvas.getContext('2d', { alpha: true });
      if (!this.ctx) return;

      this.hero = canvas.closest('.vg-yt-hero-section') || canvas.parentElement || document.getElementById('youtube-seo-hero');
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);

      this.width = 0;
      this.height = 0;
      this.time = 0;
      this.animId = null;
      this.isIntersecting = true;
      this.isTabActive = true;

      // Mouse Parallax with Damping
      this.mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };

      // Dotted Analytics Particles
      this.particles = [];
      this.particleCount = window.innerWidth < 768 ? 24 : 45;

      this.render = this.render.bind(this);

      this.init();
    }

    init() {
      this.resize();
      this.buildParticles();
      this.bindEvents();
      this.setupObserver();
      this.start();
    }

    resize() {
      if (!this.hero) return;
      this.width = this.hero.clientWidth || window.innerWidth;
      this.height = this.hero.clientHeight || 780;

      this.canvas.width = Math.floor(this.width * this.dpr);
      this.canvas.height = Math.floor(this.height * this.dpr);
      this.canvas.style.width = this.width + 'px';
      this.canvas.style.height = this.height + 'px';

      this.ctx.scale(this.dpr, this.dpr);
    }

    buildParticles() {
      this.particles = [];
      // Understated monochrome royal-violet palette (#7C3AED / #6D28D9 / #8B5CF6)
      const colors = [
        { r: 124, g: 58,  b: 237 }, // #7C3AED Royal Violet
        { r: 109, g: 40,  b: 217 }, // #6D28D9 Deep Violet
        { r: 139, g: 92,  b: 246 }  // #8B5CF6 Lavender Violet
      ];

      for (let i = 0; i < this.particleCount; i++) {
        const col = colors[i % colors.length];
        this.particles.push({
          x: Math.random() * (this.width || 1200),
          y: Math.random() * (this.height || 800),
          vx: 0.16 + Math.random() * 0.22,  // Drifting gently to top-right
          vy: -(0.10 + Math.random() * 0.18),
          radius: 1.0 + Math.random() * 1.3,
          color: col,
          baseAlpha: 0.16 + Math.random() * 0.16,
          pulseSpeed: 0.7 + Math.random() * 1.0,
          pulseOffset: Math.random() * Math.PI * 2
        });
      }
    }

    bindEvents() {
      window.addEventListener('resize', () => {
        this.resize();
      }, { passive: true });

      window.addEventListener('load', () => {
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
        }, { threshold: 0 });
        observer.observe(this.hero);
      }
    }

    start() {
      if (!this.animId) {
        this.lastTime = performance.now();
        this.animId = requestAnimationFrame(this.render);
      }
    }

    stop() {
      if (this.animId) {
        cancelAnimationFrame(this.animId);
        this.animId = null;
      }
    }

    drawAmbientAura() {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;
      const t = this.time;

      ctx.save();

      // Deep Obsidian #0a0614 Dark-Luxury Base Canvas
      ctx.fillStyle = '#0a0614';
      ctx.fillRect(0, 0, w, h);

      // Subtle Understated Royal Violet Ambient Glow (#7C3AED / #6D28D9)
      const r1X = w * 0.48 + Math.sin(t * 0.25) * 40 + this.mouse.x * 15;
      const r1Y = h * 0.48 + Math.cos(t * 0.22) * 30 + this.mouse.y * 12;
      const r1Radius = Math.max(w * 0.42, 340);
      const grad1 = ctx.createRadialGradient(r1X, r1Y, 0, r1X, r1Y, r1Radius);
      grad1.addColorStop(0, 'rgba(124, 58, 237, 0.08)');
      grad1.addColorStop(0.55, 'rgba(109, 40, 217, 0.02)');
      grad1.addColorStop(1, 'rgba(10, 6, 20, 0)');
      ctx.fillStyle = grad1;
      ctx.beginPath();
      ctx.arc(r1X, r1Y, r1Radius, 0, Math.PI * 2);
      ctx.fill();

      ctx.restore();
    }

    renderSilkWave(cfg) {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;
      const t = this.time;
      const step = 8; // Silky curve precision

      const points = [];
      for (let x = 0; x <= w + step; x += step) {
        // Base diagonal line: flowing gently from bottom-left to top-right
        const diagProgress = x / w;
        const baseY = (h * cfg.startRatio) - (diagProgress * h * cfg.riseRatio) + (this.mouse.y * cfg.mouseInfluence);

        const y = baseY +
          Math.sin(x * cfg.freq1 + t * cfg.speed1) * cfg.amp1 +
          Math.cos(x * cfg.freq2 - t * cfg.speed2) * cfg.amp2 +
          Math.sin((x * 0.0008) + t * 0.15) * cfg.amp3;

        points.push({ x, y });
      }

      if (points.length < 2) return;

      ctx.save();

      // Body Gradient Fill
      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
      }
      ctx.lineTo(w, h + 80);
      ctx.lineTo(0, h + 80);
      ctx.closePath();

      const waveGrad = ctx.createLinearGradient(0, points[0].y - 30, w, points[points.length - 1].y + cfg.depthHeight);
      waveGrad.addColorStop(0, cfg.colorStart);
      waveGrad.addColorStop(0.5, cfg.colorMid);
      waveGrad.addColorStop(1, 'rgba(10, 6, 20, 0)');
      ctx.fillStyle = waveGrad;
      ctx.fill();

      // Silk Filament Line along Ridge
      ctx.beginPath();
      ctx.moveTo(points[0].x, points[0].y);
      for (let i = 1; i < points.length; i++) {
        ctx.lineTo(points[i].x, points[i].y);
      }
      ctx.strokeStyle = cfg.sheenColor;
      ctx.lineWidth = cfg.sheenWidth || 1.4;
      ctx.stroke();

      // Dotted Particle Ribbon along the Ridge
      if (cfg.showDottedRibbon) {
        ctx.fillStyle = cfg.dotColor || cfg.sheenColor;
        const dotInterval = 4; // every 4th point
        for (let i = 0; i < points.length; i += dotInterval) {
          const pt = points[i];
          const dotRadius = 1.0 + Math.sin(i * 0.3 + t * 1.5) * 0.4;
          ctx.beginPath();
          ctx.arc(pt.x, pt.y - 1, Math.max(0.6, dotRadius), 0, Math.PI * 2);
          ctx.fill();
        }
      }

      ctx.restore();
    }

    drawUndulatingWaves() {
      // Wave 1: Subtle Royal Violet Silk Thread (#7C3AED: 0.26 opacity)
      this.renderSilkWave({
        startRatio: 0.78,
        riseRatio: 0.52,
        mouseInfluence: 14,
        freq1: 0.0017,
        freq2: 0.0035,
        speed1: 0.20,
        speed2: 0.30,
        amp1: 46,
        amp2: 24,
        amp3: 12,
        colorStart: 'rgba(124, 58, 237, 0.26)', // Royal Violet #7C3AED
        colorMid: 'rgba(109, 40, 217, 0.08)',
        sheenColor: 'rgba(168, 85, 247, 0.32)',
        sheenWidth: 1.2,
        depthHeight: 180,
        showDottedRibbon: true,
        dotColor: 'rgba(192, 132, 252, 0.36)'
      });

      // Wave 2: Deep Indigo-Violet Silk Thread (#6D28D9: 0.24 opacity)
      this.renderSilkWave({
        startRatio: 0.70,
        riseRatio: 0.46,
        mouseInfluence: 16,
        freq1: 0.0022,
        freq2: 0.0040,
        speed1: -0.22,
        speed2: 0.32,
        amp1: 40,
        amp2: 22,
        amp3: 10,
        colorStart: 'rgba(109, 40, 217, 0.24)', // Deep Violet #6D28D9
        colorMid: 'rgba(91, 33, 182, 0.06)',
        sheenColor: 'rgba(147, 51, 234, 0.28)',
        sheenWidth: 1.1,
        depthHeight: 150,
        showDottedRibbon: true,
        dotColor: 'rgba(168, 85, 247, 0.32)'
      });

      // Wave 3: Soft Lavender-Violet Harmonic Silk Thread (#8B5CF6: 0.22 opacity)
      this.renderSilkWave({
        startRatio: 0.62,
        riseRatio: 0.50,
        mouseInfluence: 12,
        freq1: 0.0018,
        freq2: 0.0032,
        speed1: 0.18,
        speed2: -0.24,
        amp1: 34,
        amp2: 18,
        amp3: 8,
        colorStart: 'rgba(139, 92, 246, 0.22)', // Soft Lavender Violet #8B5CF6
        colorMid: 'rgba(124, 58, 237, 0.05)',
        sheenColor: 'rgba(192, 132, 252, 0.26)',
        sheenWidth: 1.0,
        depthHeight: 160,
        showDottedRibbon: true,
        dotColor: 'rgba(216, 180, 254, 0.30)'
      });
    }

    drawAnalyticsParticles() {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;
      const t = this.time;

      ctx.save();
      ctx.globalCompositeOperation = 'lighter';

      for (let i = 0; i < this.particles.length; i++) {
        const p = this.particles[i];

        // Motion upward and to the right (bottom-left to top-right flow)
        p.x += p.vx;
        p.y += p.vy;

        // Wrap around seamlessly
        if (p.x > w + 20) p.x = -20;
        if (p.y < -20) p.y = h + 20;

        const pulse = 0.8 + 0.2 * Math.sin(t * p.pulseSpeed + p.pulseOffset);
        const alpha = p.baseAlpha * pulse;

        ctx.fillStyle = `rgba(${p.color.r}, ${p.color.g}, ${p.color.b}, ${alpha})`;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
        ctx.fill();

        // Subtle proximity connections (neural algorithm telemetry)
        for (let j = i + 1; j < this.particles.length; j++) {
          const p2 = this.particles[j];
          const dx = p.x - p2.x;
          const dy = p.y - p2.y;
          const distSq = dx * dx + dy * dy;
          if (distSq < 2500) { // < 50px
            const lineAlpha = (1 - Math.sqrt(distSq) / 50) * 0.07;
            ctx.strokeStyle = `rgba(124, 58, 237, ${lineAlpha})`;
            ctx.lineWidth = 0.6;
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.stroke();
          }
        }
      }

      ctx.restore();
    }

    render(now) {
      if (!this.isIntersecting || !this.isTabActive) {
        this.animId = null;
        return;
      }

      const delta = (now - this.lastTime) * 0.001;
      this.lastTime = now;

      // Strictly tranquil 0.25x speed pace
      this.time += Math.min(delta, 0.05) * 0.25;

      // Mouse Lerp Damping
      this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.04;
      this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.04;

      // 1. Draw Obsidian Base & Ambient Radiance Pools
      this.drawAmbientAura();

      // 2. Draw 3 Undulating Silk Waves (Violet, Rose, Cyan)
      this.drawUndulatingWaves();

      // 3. Draw Dotted Analytics Wave Particles
      this.drawAnalyticsParticles();

      this.animId = requestAnimationFrame(this.render);
    }
  }

  // Export & Compatibility Hooks
  function initEngine() {
    var canvas = document.getElementById('vg-yt-flight-canvas');
    if (!canvas) {
      var container = document.getElementById('yt-retention-canvas-container');
      var hero = document.querySelector('.vg-yt-hero-section') || document.getElementById('youtube-seo-hero');
      if (container || hero) {
        canvas = document.createElement('canvas');
        canvas.id = 'vg-yt-flight-canvas';
        canvas.className = 'vg-yt-flight-canvas';
        if (container) container.appendChild(canvas);
        else hero.insertBefore(canvas, hero.firstChild);
      }
    }

    if (canvas && !canvas._ytAuraInstance) {
      canvas._ytAuraInstance = new YtAlgorithmAuraWave(canvas);
      window.VGYtGrowthTerrain = { initialized: true, instance: canvas._ytAuraInstance };
      window.VGFalconFlight = window.VGYtGrowthTerrain;
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEngine);
  } else {
    initEngine();
  }

  window.addEventListener('load', function () {
    if (!window.VGYtGrowthTerrain || !window.VGYtGrowthTerrain.initialized) {
      initEngine();
    }
  });

})(window, document);
