/**
 * ============================================================================
 * VG DIGITAL FALCON — GLOBAL DTAPEX-STYLE 3D PARTICLE MESH & INTERACTIVE SPOTLIGHT
 * File: assets/js/dtapex-particles.js
 * 
 * Features:
 * 1. Global Fixed Background Canvas (z-index: -1) flowing behind all sections
 * 2. Interactive 3D Particle Orbit & Undulating Wave Grid (HTML5 Canvas, 60 FPS)
 * 3. Real-time Cursor Bending & Magnetic Tilt Physics across the entire viewport
 * 4. Color Palette: Neon Electric Cyan (#00F0FF) to Cyber Violet (#8B5CF6)
 * 5. Micro-Interactions: Cursor-following radial spotlight border on tech & comparison cards
 * 6. High-Contrast 100% Opacity Protection for all text and cards
 * ============================================================================
 */

(function () {
  'use strict';

  // ==========================================================================
  // 1. GLOBAL DTAPEX 3D PARTICLE MESH & WAVE ENGINE
  // ==========================================================================
  class GlobalDtapexParticleMesh {
    constructor(canvas, options = {}) {
      this.canvas = canvas;
      this.ctx = canvas.getContext('2d', { alpha: true });
      if (!this.ctx) return;

      this.options = Object.assign({
        cols: 32,
        rows: 20,
        spacingX: 62,
        spacingY: 54,
        fov: 460,
        cyan: { r: 0, g: 240, b: 255 },     // #00F0FF
        violet: { r: 139, g: 92, b: 246 }, // #8B5CF6
        orbitCount: 26,
        maxTilt: 0.32,
        bendRadius: 260,
        bendStrength: 55
      }, options);

      this.width = window.innerWidth;
      this.height = window.innerHeight;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);

      this.mouse = { x: -9999, y: -9999 };
      this.tilt = { x: 0, y: 0, targetX: 0, targetY: 0 };
      this.scrollY = 0;
      this.time = 0;
      this.animId = null;

      this.particles = [];
      this.orbitParticles = [];

      this.init();
    }

    init() {
      this.resize();
      this.buildGrid();
      this.buildOrbits();
      this.bindEvents();
      this.start();
    }

    resize() {
      this.width = window.innerWidth;
      this.height = window.innerHeight;

      this.canvas.width = this.width * this.dpr;
      this.canvas.height = this.height * this.dpr;
      this.canvas.style.width = this.width + 'px';
      this.canvas.style.height = this.height + 'px';

      this.ctx.scale(this.dpr, this.dpr);

      // Responsive density
      if (this.width < 768) {
        this.options.cols = 18;
        this.options.rows = 14;
        this.options.spacingX = Math.floor(this.width / 16);
        this.options.spacingY = 40;
      } else if (this.width < 1200) {
        this.options.cols = 26;
        this.options.rows = 18;
        this.options.spacingX = 54;
        this.options.spacingY = 48;
      } else {
        this.options.cols = 32;
        this.options.rows = 20;
        this.options.spacingX = 64;
        this.options.spacingY = 54;
      }

      this.buildGrid();
    }

    buildGrid() {
      this.particles = [];
      const { cols, rows, spacingX, spacingY } = this.options;
      const totalWidth = (cols - 1) * spacingX;
      const totalHeight = (rows - 1) * spacingY;
      const offsetX = -totalWidth / 2;
      const offsetY = -totalHeight / 2;

      for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
          const u = c / (cols - 1); // 0 to 1
          const v = r / (rows - 1); // 0 to 1
          this.particles.push({
            r, c,
            x0: offsetX + c * spacingX,
            y0: offsetY + r * spacingY,
            z0: 0,
            x: 0, y: 0, z: 0,
            screenX: 0, screenY: 0,
            scale: 1,
            u, v,
            baseRadius: 1.5 + (1 - Math.abs(u - 0.5) * 2) * 0.9
          });
        }
      }
    }

    buildOrbits() {
      this.orbitParticles = [];
      for (let i = 0; i < this.options.orbitCount; i++) {
        const radiusX = 180 + Math.random() * 320;
        const radiusY = 90 + Math.random() * 180;
        const speed = (0.007 + Math.random() * 0.011) * (Math.random() > 0.5 ? 1 : -1);
        const angle = Math.random() * Math.PI * 2;
        const tiltAngle = (Math.random() - 0.5) * 0.7;
        this.orbitParticles.push({
          radiusX, radiusY, speed, angle, tiltAngle,
          size: 1.6 + Math.random() * 2.4,
          colorRatio: Math.random() // 0 = cyan, 1 = violet
        });
      }
    }

    bindEvents() {
      // Resize listener
      let resizeTimer = null;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => this.resize(), 120);
      }, { passive: true });

      // Window-wide cursor tracking
      window.addEventListener('mousemove', (e) => {
        this.mouse.x = e.clientX;
        this.mouse.y = e.clientY;

        const nx = (e.clientX / this.width - 0.5) * 2;
        const ny = (e.clientY / this.height - 0.5) * 2;
        this.tilt.targetY = nx * this.options.maxTilt;
        this.tilt.targetX = -ny * this.options.maxTilt;
      }, { passive: true });

      // Window scroll tracking for wave progression
      window.addEventListener('scroll', () => {
        this.scrollY = window.pageYOffset || document.documentElement.scrollTop;
      }, { passive: true });
    }

    start() {
      if (this.animId) cancelAnimationFrame(this.animId);
      const loop = () => {
        this.update();
        this.render();
        this.animId = requestAnimationFrame(loop);
      };
      this.animId = requestAnimationFrame(loop);
    }

    update() {
      this.time += 0.02;

      // Smooth tilt interpolation
      this.tilt.x += (this.tilt.targetX - this.tilt.x) * 0.055;
      this.tilt.y += (this.tilt.targetY - this.tilt.y) * 0.055;

      const cosX = Math.cos(this.tilt.x + 0.38); // base perspective tilt
      const sinX = Math.sin(this.tilt.x + 0.38);
      const cosY = Math.cos(this.tilt.y);
      const sinY = Math.sin(this.tilt.y);

      const cx = this.width / 2;
      const cy = this.height / 2 + 15;
      const fov = this.options.fov;

      // Subtle scroll offset so the wave travels with page navigation
      const scrollFactor = (this.scrollY * 0.0006) % (Math.PI * 2);

      for (let i = 0; i < this.particles.length; i++) {
        const p = this.particles[i];

        // 3D Sine Wave Undulation
        const waveA = Math.sin(p.c * 0.28 + this.time * 1.3 + scrollFactor) * 36;
        const waveB = Math.cos(p.r * 0.32 + this.time * 1.0) * 24;
        const waveC = Math.sin((p.c + p.r) * 0.18 + this.time * 0.7) * 18;
        let z = waveA + waveB + waveC;

        let x = p.x0;
        let y = p.y0;

        // 3D Rotation with interactive tilt
        let rx = x * cosY - z * sinY;
        let rz = x * sinY + z * cosY;
        let ry = y * cosX - rz * sinX;
        rz = y * sinX + rz * cosX;

        // Project 3D to 2D
        const scale = fov / (fov + rz + 180);
        const screenX = cx + rx * scale;
        const screenY = cy + ry * scale;

        // Real-time Cursor Bending Physics
        const dx = screenX - this.mouse.x;
        const dy = screenY - this.mouse.y;
        const dist = Math.sqrt(dx * dx + dy * dy);

        if (dist < this.options.bendRadius) {
          const force = 1 - dist / this.options.bendRadius;
          const push = force * force * this.options.bendStrength;
          rz += push;
          ry -= push * 0.35;
        }

        p.screenX = cx + rx * (fov / (fov + rz + 180));
        p.screenY = cy + ry * (fov / (fov + rz + 180));
        p.scale = Math.max(0.2, Math.min(scale, 1.8));
        p.z = rz;
      }

      // Update orbiting particles
      for (let j = 0; j < this.orbitParticles.length; j++) {
        const op = this.orbitParticles[j];
        op.angle += op.speed;
        const ox = Math.cos(op.angle) * op.radiusX;
        const oy = Math.sin(op.angle) * op.radiusY;
        const oz = Math.sin(op.angle * 2) * 70;

        const rox = ox * cosY - oz * sinY;
        const roz = ox * sinY + oz * cosY;
        const roy = oy * cosX - roz * sinX;

        const oScale = fov / (fov + roz + 180);
        op.screenX = cx + rox * oScale;
        op.screenY = cy + roy * oScale;
        op.scale = oScale;
      }
    }

    render() {
      this.ctx.clearRect(0, 0, this.width, this.height);

      const { cols, rows, cyan, violet } = this.options;

      // Draw filament lines between grid neighbors
      this.ctx.lineWidth = 1;

      for (let r = 0; r < rows; r++) {
        for (let c = 0; c < cols; c++) {
          const idx = r * cols + c;
          const p = this.particles[idx];
          if (!p) continue;

          // Connect to right neighbor
          if (c < cols - 1) {
            const rightP = this.particles[idx + 1];
            this.drawFilament(p, rightP, cyan, violet);
          }

          // Connect to bottom neighbor
          if (r < rows - 1) {
            const bottomP = this.particles[idx + cols];
            this.drawFilament(p, bottomP, cyan, violet);
          }
        }
      }

      // Draw particle nodes
      for (let i = 0; i < this.particles.length; i++) {
        const p = this.particles[i];
        if (p.screenX < -20 || p.screenX > this.width + 20 || p.screenY < -20 || p.screenY > this.height + 20) continue;

        const t = Math.max(0, Math.min(1, p.u * 0.7 + p.v * 0.3));
        const cr = Math.round(cyan.r + (violet.r - cyan.r) * t);
        const cg = Math.round(cyan.g + (violet.g - cyan.g) * t);
        const cb = Math.round(cyan.b + (violet.b - cyan.b) * t);
        const alpha = Math.max(0.2, Math.min(0.95, p.scale * 0.8));

        const radius = Math.max(1.1, p.baseRadius * p.scale);

        // Core particle dot
        this.ctx.beginPath();
        this.ctx.arc(p.screenX, p.screenY, radius, 0, Math.PI * 2);
        this.ctx.fillStyle = `rgba(${cr}, ${cg}, ${cb}, ${alpha})`;
        this.ctx.fill();

        // Selective glow on elevated nodes
        if (p.scale > 1.1) {
          this.ctx.beginPath();
          this.ctx.arc(p.screenX, p.screenY, radius * 2.4, 0, Math.PI * 2);
          this.ctx.fillStyle = `rgba(${cr}, ${cg}, ${cb}, ${alpha * 0.3})`;
          this.ctx.fill();
        }
      }

      // Draw orbiting satellites
      for (let k = 0; k < this.orbitParticles.length; k++) {
        const op = this.orbitParticles[k];
        const ot = op.colorRatio;
        const or = Math.round(cyan.r + (violet.r - cyan.r) * ot);
        const og = Math.round(cyan.g + (violet.g - cyan.g) * ot);
        const ob = Math.round(cyan.b + (violet.b - cyan.b) * ot);
        const oAlpha = Math.max(0.35, Math.min(0.95, op.scale * 0.85));

        this.ctx.beginPath();
        this.ctx.arc(op.screenX, op.screenY, op.size * op.scale, 0, Math.PI * 2);
        this.ctx.fillStyle = `rgba(${or}, ${og}, ${ob}, ${oAlpha})`;
        this.ctx.shadowColor = `rgba(${or}, ${og}, ${ob}, 0.85)`;
        this.ctx.shadowBlur = 10;
        this.ctx.fill();
        this.ctx.shadowBlur = 0;
      }
    }

    drawFilament(p1, p2, cyan, violet) {
      const avgU = (p1.u + p2.u) * 0.5;
      const t = Math.max(0, Math.min(1, avgU));
      const cr = Math.round(cyan.r + (violet.r - cyan.r) * t);
      const cg = Math.round(cyan.g + (violet.g - cyan.g) * t);
      const cb = Math.round(cyan.b + (violet.b - cyan.b) * t);

      const avgScale = (p1.scale + p2.scale) * 0.5;
      const alpha = Math.max(0.06, Math.min(0.42, avgScale * 0.32));

      this.ctx.beginPath();
      this.ctx.moveTo(p1.screenX, p1.screenY);
      this.ctx.lineTo(p2.screenX, p2.screenY);
      this.ctx.strokeStyle = `rgba(${cr}, ${cg}, ${cb}, ${alpha})`;
      this.ctx.stroke();
    }
  }


  // ==========================================================================
  // 2. CURSOR-FOLLOWING RADIAL SPOTLIGHT BORDER & SURFACE GLOW (OPTIMIZED RAF)
  // ==========================================================================
  function initSpotlightCards() {
    const selector = [
      '.vg-bento-card',
      '.vg-telemetry-card',
      '.vg-founder-card',
      '.vg-vm-card',
      '.vg-value-card',
      '.vg-cap-card',
      '.vg-adv-card'
    ].join(',');

    const cards = document.querySelectorAll(selector);

    cards.forEach(card => {
      if (!card.classList.contains('vg-spotlight-card')) {
        card.classList.add('vg-spotlight-card');
      }

      let rafId = null;
      let rect = null;

      card.addEventListener('mouseenter', function () {
        rect = card.getBoundingClientRect();
      }, { passive: true });

      card.addEventListener('mousemove', function (e) {
        if (!rect) rect = card.getBoundingClientRect();
        if (rafId) return;
        const clientX = e.clientX;
        const clientY = e.clientY;
        rafId = requestAnimationFrame(() => {
          if (rect) {
            const x = clientX - rect.left;
            const y = clientY - rect.top;
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
          }
          rafId = null;
        });
      }, { passive: true });

      card.addEventListener('mouseleave', function () {
        if (rafId) {
          cancelAnimationFrame(rafId);
          rafId = null;
        }
        rect = null;
        card.style.setProperty('--mouse-x', '-500px');
        card.style.setProperty('--mouse-y', '-500px');
      }, { passive: true });
    });
  }


  // ==========================================================================
  // 3. MOUNT GLOBAL FIXED BACKGROUND CANVAS (z-index: -1)
  // ==========================================================================
  function initGlobalDtapexCanvas() {
    // Clean up any local section canvases so only one global fixed canvas exists
    document.querySelectorAll('.vg-dtapex-canvas:not(#vg-global-dtapex-canvas)').forEach(c => c.remove());

    let canvas = document.getElementById('vg-global-dtapex-canvas');
    if (!canvas) {
      const isAboutPage = document.querySelector('.vg-about-hero') || 
                          document.querySelector('.vg-bento-section') || 
                          window.location.pathname.includes('about');
      if (!isAboutPage) return;

      canvas = document.createElement('canvas');
      canvas.id = 'vg-global-dtapex-canvas';
      canvas.className = 'vg-global-dtapex-canvas';
      document.body.prepend(canvas);
    }

    if (!canvas._dtapexInstance) {
      canvas._dtapexInstance = new GlobalDtapexParticleMesh(canvas);
    }
  }


  // ==========================================================================
  // 4. DOM READY INITIALIZATION
  // ==========================================================================
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
      initGlobalDtapexCanvas();
      initSpotlightCards();
    });
  } else {
    initGlobalDtapexCanvas();
    initSpotlightCards();
  }

  // Export globally
  window.VG_Dtapex = {
    GlobalDtapexParticleMesh,
    initSpotlightCards,
    initGlobalDtapexCanvas
  };

})();
