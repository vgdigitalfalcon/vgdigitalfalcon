/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D FROSTED-GLASS AUTHORITY RIBBON / SILK WAVE
 * File: assets/js/pb-authority-ribbon.js
 * 
 * Soft-Luxury Architectural 3D Art (Widget-Free, Zero Harsh Edges):
 * - Multi-layered 3D undulating frosted-glass silk ribbons
 * - Luminous floating translucent glass light arcs (#00F0FF Electric Cyan to #A855F7 Cyber Violet)
 * - Razor-thin specular crest highlights simulating precision frosted curved glass
 * - Interactive 3D cursor-reactive perspective parallax with smooth damping
 * - Centered gracefully in the open space between headline and audit form
 * - Strictly z-index: 0, zero text overlap, lightweight 60fps Canvas 3D
 * ============================================================================
 */

(function () {
  'use strict';

  class PBAuthorityRibbon {
    constructor(container) {
      this.container = container;
      this.hero = container.closest('#personal-branding-hero') || container.parentElement;

      this.canvas = document.createElement('canvas');
      this.canvas.className = 'vg-pb-ribbon-canvas';
      this.canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;z-index:0;pointer-events:none;';
      this.container.appendChild(this.canvas);
      this.ctx = this.canvas.getContext('2d');

      this.width = 0;
      this.height = 0;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);
      this.time = 0;
      this.animId = null;
      this.isIntersecting = true;
      this.isTabActive = true;

      // Camera & 3D Settings
      this.fov = 550; // Focal length

      // Cursor 3D Parallax Tilt with Damping
      this.mouse = {
        targetX: 0,
        targetY: 0,
        currentX: 0,
        currentY: 0
      };

      // Configuration of the 3D Frosted-Glass Ribbons
      this.ribbons = [
        {
          // Ribbon 1: Primary Sovereign Silk Wave
          pointsCount: 48,
          baseWidth: 54,
          speed: 0.009,
          phase: 0,
          yOffset: 15,
          zBase: 20,
          colorA: 'rgba(0, 240, 255, 0.45)',
          colorB: 'rgba(168, 85, 247, 0.40)',
          colorC: 'rgba(192, 132, 252, 0.25)',
          crestColor: 'rgba(255, 255, 255, 0.85)',
          alpha: 0.85
        },
        {
          // Ribbon 2: Counter Harmony Silk Arc
          pointsCount: 44,
          baseWidth: 42,
          speed: 0.0075,
          phase: Math.PI * 0.75,
          yOffset: -25,
          zBase: -30,
          colorA: 'rgba(168, 85, 247, 0.35)',
          colorB: 'rgba(0, 240, 255, 0.35)',
          colorC: 'rgba(56, 189, 248, 0.20)',
          crestColor: 'rgba(0, 240, 255, 0.75)',
          alpha: 0.7
        },
        {
          // Ribbon 3: Deep Ethereal Translucent Veil
          pointsCount: 40,
          baseWidth: 68,
          speed: 0.006,
          phase: Math.PI * 1.4,
          yOffset: 35,
          zBase: -80,
          colorA: 'rgba(124, 58, 237, 0.25)',
          colorB: 'rgba(232, 121, 249, 0.22)',
          colorC: 'rgba(0, 240, 255, 0.15)',
          crestColor: 'rgba(192, 132, 252, 0.60)',
          alpha: 0.55
        }
      ];

      // Floating Glass Light Arcs (Delicate luminous trajectories)
      this.arcs = [
        {
          radiusX: 220,
          radiusY: 70,
          tiltZ: 0.35,
          speed: 0.008,
          phase: 0.2,
          color: '#00F0FF',
          width: 1.5,
          glow: 'rgba(0, 240, 255, 0.4)'
        },
        {
          radiusX: 250,
          radiusY: 85,
          tiltZ: -0.42,
          speed: -0.0065,
          phase: 1.8,
          color: '#C084FC',
          width: 1.4,
          glow: 'rgba(192, 132, 252, 0.4)'
        },
        {
          radiusX: 180,
          radiusY: 55,
          tiltZ: 0.15,
          speed: 0.011,
          phase: 3.4,
          color: '#38BDF8',
          width: 1.2,
          glow: 'rgba(56, 189, 248, 0.35)'
        }
      ];

      // Ambient Volumetric Glass Motes (Soft luminescence)
      this.motes = [];
      const moteCount = 24;
      for (let i = 0; i < moteCount; i++) {
        this.motes.push({
          x: (Math.random() - 0.5) * 380,
          y: (Math.random() - 0.5) * 220,
          z: (Math.random() - 0.5) * 240,
          vx: (Math.random() - 0.5) * 0.2,
          vy: (Math.random() - 0.5) * 0.25,
          radius: 1.0 + Math.random() * 1.8,
          color: Math.random() > 0.5 ? '#00F0FF' : '#C084FC',
          alpha: 0.2 + Math.random() * 0.45
        });
      }

      this.resize();
      this.bindEvents();
      this.setupObserver();
      this.start();
    }

    resize() {
      if (!this.canvas || !this.hero) return;
      this.width = this.hero.clientWidth || window.innerWidth;
      this.height = this.hero.clientHeight || 750;

      this.canvas.width = Math.floor(this.width * this.dpr);
      this.canvas.height = Math.floor(this.height * this.dpr);
      this.canvas.style.width = this.width + 'px';
      this.canvas.style.height = this.height + 'px';

      this.ctx.setTransform(1, 0, 0, 1, 0, 0);
      this.ctx.scale(this.dpr, this.dpr);
    }

    bindEvents() {
      window.addEventListener('resize', () => this.resize(), { passive: true });

      this.hero.addEventListener('mousemove', (e) => {
        const rect = this.hero.getBoundingClientRect();
        const nx = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        const ny = ((e.clientY - rect.top) / rect.height) * 2 - 1;
        this.mouse.targetX = nx;
        this.mouse.targetY = ny;
      }, { passive: true });

      this.hero.addEventListener('mouseleave', () => {
        this.mouse.targetX = 0;
        this.mouse.targetY = 0;
      }, { passive: true });

      document.addEventListener('visibilitychange', () => {
        this.isTabActive = !document.hidden;
        if (this.isTabActive && this.isIntersecting && !this.animId) {
          this.start();
        }
      });
    }

    setupObserver() {
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            this.isIntersecting = entry.isIntersecting;
            if (this.isIntersecting && this.isTabActive && !this.animId) {
              this.start();
            }
          });
        }, { threshold: 0.05 });
        observer.observe(this.hero);
      }
    }

    start() {
      if (this.animId) return;
      const loop = () => {
        if (!this.isIntersecting || !this.isTabActive) {
          this.animId = null;
          return;
        }
        this.update();
        this.render();
        this.animId = requestAnimationFrame(loop);
      };
      this.animId = requestAnimationFrame(loop);
    }

    update() {
      this.time += 1;

      // Smooth cursor parallax interpolation
      this.mouse.currentX += (this.mouse.targetX - this.mouse.currentX) * 0.045;
      this.mouse.currentY += (this.mouse.targetY - this.mouse.currentY) * 0.045;

      // Update motes
      for (let i = 0; i < this.motes.length; i++) {
        const m = this.motes[i];
        m.x += m.vx;
        m.y += m.vy;
        if (m.x > 220) m.x = -220;
        if (m.x < -220) m.x = 220;
        if (m.y > 130) m.y = -130;
        if (m.y < -130) m.y = 130;
      }
    }

    // 3D Perspective Projection helper with 3D rotation
    project(x, y, z, rotX, rotY) {
      // Rotate around Y axis
      const cosY = Math.cos(rotY);
      const sinY = Math.sin(rotY);
      const x1 = x * cosY + z * sinY;
      const z1 = -x * sinY + z * cosY;

      // Rotate around X axis
      const cosX = Math.cos(rotX);
      const sinX = Math.sin(rotX);
      const y2 = y * cosX - z1 * sinX;
      const z2 = y * sinX + z1 * cosX;

      // Perspective scale factor
      const scale = this.fov / (this.fov + z2);

      // Centered cleanly in the open middle area of hero
      const centerX = this.width * 0.50 + (this.mouse.currentX * 18);
      const centerY = this.height * 0.50 + (this.mouse.currentY * 12);

      return {
        x: centerX + x1 * scale,
        y: centerY + y2 * scale,
        scale: scale,
        z: z2
      };
    }

    render() {
      this.ctx.clearRect(0, 0, this.width, this.height);

      const rotY = this.mouse.currentX * 0.22;
      const rotX = -this.mouse.currentY * 0.16;

      // 1. Ambient Volumetric Luminescence Glow behind Ribbons
      const bgCenterX = this.width * 0.50;
      const bgCenterY = this.height * 0.50;
      const bgGlow = this.ctx.createRadialGradient(
        bgCenterX, bgCenterY, 30,
        bgCenterX, bgCenterY, Math.min(this.width * 0.35, 360)
      );
      bgGlow.addColorStop(0, 'rgba(168, 85, 247, 0.12)');
      bgGlow.addColorStop(0.5, 'rgba(0, 240, 255, 0.06)');
      bgGlow.addColorStop(1, 'rgba(7, 7, 15, 0)');
      this.ctx.fillStyle = bgGlow;
      this.ctx.fillRect(0, 0, this.width, this.height);

      // 2. Render Floating Glass Light Arcs (Back depth)
      this.renderGlassArcs(rotX, rotY);

      // 3. Render 3D Frosted-Glass Silk Ribbons
      for (let r = 0; r < this.ribbons.length; r++) {
        this.renderRibbon(this.ribbons[r], rotX, rotY, r);
      }

      // 4. Render Ambient Floating Motes (Volumetric Glass Dust)
      this.renderMotes(rotX, rotY);
    }

    renderRibbon(cfg, rotX, rotY, index) {
      const pts = cfg.pointsCount;
      const upperPts = [];
      const lowerPts = [];
      const spinePts = [];

      // Ribbon length spans gracefully across open center space
      // Controlled width prevents overlap with left text or right form
      const halfSpan = Math.min(this.width * 0.22, 210);
      const tNow = this.time * cfg.speed + cfg.phase;

      for (let i = 0; i <= pts; i++) {
        const u = i / pts; // 0 to 1
        const x3D = -halfSpan + u * (halfSpan * 2);

        // Parametric harmonic sine/cosine equations for organic silk wave motion
        const wave1 = Math.sin(u * Math.PI * 2.2 + tNow) * 48;
        const wave2 = Math.cos(u * Math.PI * 3.4 - tNow * 0.8) * 22;
        const y3D = cfg.yOffset + wave1 + wave2;

        const zWave = Math.sin(u * Math.PI * 2.0 + tNow * 1.1) * 75;
        const z3D = cfg.zBase + zWave;

        // Taper ribbon width softly at endpoints for zero harsh edges
        const taper = Math.sin(u * Math.PI); // 0 at edges, 1 at center
        const currentWidth = cfg.baseWidth * Math.pow(taper, 0.7);

        // Ribbon normal orientation vector (twist angle)
        const twistAngle = Math.sin(u * Math.PI * 2.5 + tNow * 0.6) * 0.65;
        const dx = Math.sin(twistAngle) * (currentWidth * 0.5);
        const dy = Math.cos(twistAngle) * (currentWidth * 0.5);

        // Project upper, lower, and spine points to 2D
        const pUpper = this.project(x3D - dx * 0.3, y3D - dy, z3D - 8, rotX, rotY);
        const pLower = this.project(x3D + dx * 0.3, y3D + dy, z3D + 8, rotX, rotY);
        const pSpine = this.project(x3D, y3D, z3D, rotX, rotY);

        upperPts.push(pUpper);
        lowerPts.push(pLower);
        spinePts.push(pSpine);
      }

      // Render Frosted-Glass Ribbon Body (Segmented Quads with Organic Gradient)
      this.ctx.save();
      this.ctx.globalAlpha = cfg.alpha;

      for (let i = 0; i < pts; i++) {
        const u = i / pts;
        const taper = Math.sin(u * Math.PI);

        this.ctx.beginPath();
        this.ctx.moveTo(upperPts[i].x, upperPts[i].y);
        this.ctx.lineTo(upperPts[i + 1].x, upperPts[i + 1].y);
        this.ctx.lineTo(lowerPts[i + 1].x, lowerPts[i + 1].y);
        this.ctx.lineTo(lowerPts[i].x, lowerPts[i].y);
        this.ctx.closePath();

        // Dynamic multi-stop gradient along the ribbon segment
        const segGrad = this.ctx.createLinearGradient(
          upperPts[i].x, upperPts[i].y,
          lowerPts[i + 1].x, lowerPts[i + 1].y
        );

        const edgeAlpha = Math.max(0.04, taper * 0.45);
        if (index === 0) {
          segGrad.addColorStop(0, `rgba(0, 240, 255, ${edgeAlpha * 1.1})`);
          segGrad.addColorStop(0.5, `rgba(168, 85, 247, ${edgeAlpha * 0.95})`);
          segGrad.addColorStop(1, `rgba(192, 132, 252, ${edgeAlpha * 0.7})`);
        } else if (index === 1) {
          segGrad.addColorStop(0, `rgba(168, 85, 247, ${edgeAlpha * 1.0})`);
          segGrad.addColorStop(0.5, `rgba(0, 240, 255, ${edgeAlpha * 0.9})`);
          segGrad.addColorStop(1, `rgba(56, 189, 248, ${edgeAlpha * 0.65})`);
        } else {
          segGrad.addColorStop(0, `rgba(124, 58, 237, ${edgeAlpha * 0.85})`);
          segGrad.addColorStop(0.5, `rgba(232, 121, 249, ${edgeAlpha * 0.75})`);
          segGrad.addColorStop(1, `rgba(0, 240, 255, ${edgeAlpha * 0.5})`);
        }

        this.ctx.fillStyle = segGrad;
        this.ctx.fill();
      }

      // Render Razor-Thin Luminous Specular Crest Highlight (Precision frosted glass edge)
      this.ctx.beginPath();
      for (let i = 0; i <= pts; i++) {
        if (i === 0) {
          this.ctx.moveTo(upperPts[i].x, upperPts[i].y);
        } else {
          // Smooth curve interpolation
          const xc = (upperPts[i].x + upperPts[i - 1].x) / 2;
          const yc = (upperPts[i].y + upperPts[i - 1].y) / 2;
          this.ctx.quadraticCurveTo(upperPts[i - 1].x, upperPts[i - 1].y, xc, yc);
        }
      }
      this.ctx.strokeStyle = cfg.crestColor;
      this.ctx.lineWidth = 1.3;
      this.ctx.shadowColor = '#00F0FF';
      this.ctx.shadowBlur = 8;
      this.ctx.stroke();

      // Soft Lower Rim Glow
      this.ctx.beginPath();
      for (let i = 0; i <= pts; i++) {
        if (i === 0) {
          this.ctx.moveTo(lowerPts[i].x, lowerPts[i].y);
        } else {
          const xc = (lowerPts[i].x + lowerPts[i - 1].x) / 2;
          const yc = (lowerPts[i].y + lowerPts[i - 1].y) / 2;
          this.ctx.quadraticCurveTo(lowerPts[i - 1].x, lowerPts[i - 1].y, xc, yc);
        }
      }
      this.ctx.strokeStyle = 'rgba(168, 85, 247, 0.45)';
      this.ctx.lineWidth = 0.9;
      this.ctx.shadowBlur = 4;
      this.ctx.stroke();

      this.ctx.restore();
    }

    renderGlassArcs(rotX, rotY) {
      this.ctx.save();
      for (let a = 0; a < this.arcs.length; a++) {
        const arc = this.arcs[a];
        const segCount = 36;
        const angleOffset = this.time * arc.speed + arc.phase;

        this.ctx.beginPath();
        let firstPt = null;

        for (let i = 0; i <= segCount; i++) {
          const theta = angleOffset + (i / segCount) * Math.PI * 1.5; // Open arc, not closed ring
          const x3D = Math.cos(theta) * arc.radiusX;
          const y3D = Math.sin(theta) * arc.radiusY + (Math.cos(theta) * arc.tiltZ * 50);
          const z3D = Math.sin(theta) * (arc.radiusX * arc.tiltZ);

          const pt = this.project(x3D, y3D, z3D, rotX, rotY);
          if (i === 0) {
            firstPt = pt;
            this.ctx.moveTo(pt.x, pt.y);
          } else {
            this.ctx.lineTo(pt.x, pt.y);
          }
        }

        // Tapered luminous arc stroke
        const grad = this.ctx.createLinearGradient(
          firstPt.x, firstPt.y,
          firstPt.x + arc.radiusX * 0.8, firstPt.y + arc.radiusY
        );
        grad.addColorStop(0, 'rgba(0, 240, 255, 0)');
        grad.addColorStop(0.3, arc.color);
        grad.addColorStop(0.7, '#FFFFFF');
        grad.addColorStop(1, 'rgba(168, 85, 247, 0)');

        this.ctx.strokeStyle = grad;
        this.ctx.lineWidth = arc.width;
        this.ctx.shadowColor = arc.glow;
        this.ctx.shadowBlur = 6;
        this.ctx.stroke();
      }
      this.ctx.restore();
    }

    renderMotes(rotX, rotY) {
      this.ctx.save();
      for (let i = 0; i < this.motes.length; i++) {
        const m = this.motes[i];
        const pt = this.project(m.x, m.y, m.z, rotX, rotY);

        if (pt.scale > 0) {
          const r = m.radius * pt.scale;
          this.ctx.beginPath();
          this.ctx.arc(pt.x, pt.y, Math.max(0.5, r), 0, Math.PI * 2);
          this.ctx.fillStyle = m.color;
          this.ctx.globalAlpha = m.alpha * pt.scale;
          this.ctx.shadowColor = m.color;
          this.ctx.shadowBlur = 5;
          this.ctx.fill();
        }
      }
      this.ctx.restore();
    }
  }

  // Auto-initialize when DOM is ready
  function init() {
    const wrap = document.getElementById('vg-pb-ribbon-wrap');
    if (wrap) {
      new PBAuthorityRibbon(wrap);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
