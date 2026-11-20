/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D FIBER-OPTIC ASCENT TRAILS (SKYROCKET MOMENTUM)
 * File: assets/js/pm-ascent-trails.js
 * 
 * Vercel / Linear Luxury Minimalism:
 * - 100% widget-free, pure architectural 3D light art
 * - Sleek, razor-thin luminous fiber-optic curves (#00F0FF to #A855F7)
 * - Exponential ascent trajectories symbolizing skyrocket revenue momentum
 * - Smooth interactive cursor-reactive 3D perspective parallax
 * - Strictly z-index: 0, zero text overlap, optimized 60fps Canvas 3D
 * ============================================================================
 */

(function () {
  'use strict';

  class PMAscentTrails {
    constructor(container) {
      this.container = container;
      this.hero = container.closest('#performance-marketing-hero') || container.parentElement;

      this.canvas = document.createElement('canvas');
      this.canvas.className = 'vg-pm-ascent-canvas';
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

      // Mouse 3D Parallax Tilt
      this.mouse = {
        targetX: 0,
        targetY: 0,
        currentX: 0,
        currentY: 0
      };

      // 3D Fiber-Optic Trails Definition (Center-Bottom Perspective toward Top-Right)
      this.trails = [
        {
          // Trail 1: Central Primary Surge
          p0: { x: -80, y: 220, z: -40 },
          p1: { x: -40, y: 120, z: -20 },
          p2: { x: 40, y: -40, z: 20 },
          p3: { x: 220, y: -210, z: 60 },
          width: 1.8,
          alpha: 0.9,
          colorA: '#00F0FF',
          colorB: '#A855F7',
          colorC: '#C084FC'
        },
        {
          // Trail 2: Upper Accelerator Ribbon
          p0: { x: -50, y: 200, z: 20 },
          p1: { x: -10, y: 100, z: 40 },
          p2: { x: 70, y: -60, z: 80 },
          p3: { x: 250, y: -230, z: 120 },
          width: 1.5,
          alpha: 0.75,
          colorA: '#38BDF8',
          colorB: '#818CF8',
          colorC: '#C084FC'
        },
        {
          // Trail 3: Lower Supporting Trajectory
          p0: { x: -110, y: 240, z: -80 },
          p1: { x: -70, y: 140, z: -60 },
          p2: { x: 10, y: -20, z: -30 },
          p3: { x: 190, y: -190, z: 0 },
          width: 1.4,
          alpha: 0.7,
          colorA: '#00F0FF',
          colorB: '#7C3AED',
          colorC: '#A855F7'
        },
        {
          // Trail 4: Deep Background Echo
          p0: { x: -30, y: 210, z: -120 },
          p1: { x: 10, y: 110, z: -90 },
          p2: { x: 90, y: -50, z: -50 },
          p3: { x: 270, y: -220, z: -10 },
          width: 1.2,
          alpha: 0.5,
          colorA: '#06B6D4',
          colorB: '#A855F7',
          colorC: '#E879F9'
        },
        {
          // Trail 5: Outer Harmonic Ascent
          p0: { x: -130, y: 250, z: 50 },
          p1: { x: -90, y: 160, z: 70 },
          p2: { x: -20, y: 0, z: 100 },
          p3: { x: 160, y: -170, z: 140 },
          width: 1.3,
          alpha: 0.6,
          colorA: '#22D3EE',
          colorB: '#A855F7',
          colorC: '#F472B6'
        },
        {
          // Trail 6: Razor-Thin Whisper Trail
          p0: { x: -70, y: 230, z: -10 },
          p1: { x: -20, y: 130, z: 10 },
          p2: { x: 60, y: -30, z: 40 },
          p3: { x: 240, y: -200, z: 80 },
          width: 1.0,
          alpha: 0.45,
          colorA: '#00F0FF',
          colorB: '#C084FC',
          colorC: '#FFFFFF'
        }
      ];

      // Streaming Fiber Photons
      this.photons = [];
      const photonCount = 18;
      for (let i = 0; i < photonCount; i++) {
        this.photons.push({
          trailIndex: i % this.trails.length,
          t: Math.random(),
          speed: 0.0028 + Math.random() * 0.0035,
          length: 0.08 + Math.random() * 0.06,
          size: 1.4 + Math.random() * 1.6,
          alpha: 0.6 + Math.random() * 0.4
        });
      }

      // Micro Ambient Dust
      this.dust = [];
      for (let i = 0; i < 28; i++) {
        this.dust.push({
          x: (Math.random() - 0.4) * 450,
          y: (Math.random() - 0.5) * 450,
          z: (Math.random() - 0.5) * 300,
          vx: (Math.random() - 0.5) * 0.25,
          vy: -0.2 - Math.random() * 0.35,
          size: 1 + Math.random() * 1.5,
          alpha: 0.2 + Math.random() * 0.5
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

      // Cursor movement over Hero Section
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
      const renderLoop = () => {
        if (!this.isIntersecting || !this.isTabActive) {
          this.animId = null;
          return;
        }
        this.update();
        this.render();
        this.animId = requestAnimationFrame(renderLoop);
      };
      this.animId = requestAnimationFrame(renderLoop);
    }

    update() {
      this.time += 0.016;

      // Silky smooth cursor parallax interpolation
      this.mouse.currentX += (this.mouse.targetX - this.mouse.currentX) * 0.05;
      this.mouse.currentY += (this.mouse.targetY - this.mouse.currentY) * 0.05;

      // Update streaming photons
      for (let i = 0; i < this.photons.length; i++) {
        const p = this.photons[i];
        p.t += p.speed;
        if (p.t > 1) {
          p.t = 0;
          p.speed = 0.0028 + Math.random() * 0.0035;
        }
      }

      // Update ambient dust
      for (let i = 0; i < this.dust.length; i++) {
        const d = this.dust[i];
        d.x += d.vx;
        d.y += d.vy;
        if (d.y < -240) {
          d.y = 240;
          d.x = (Math.random() - 0.4) * 450;
        }
      }
    }

    // 3D Point evaluation on Cubic Bezier
    getBezierPoint(p0, p1, p2, p3, t) {
      const it = 1 - t;
      const it2 = it * it;
      const it3 = it2 * it;
      const t2 = t * t;
      const t3 = t2 * t;

      return {
        x: it3 * p0.x + 3 * it2 * t * p1.x + 3 * it * t2 * p2.x + t3 * p3.x,
        y: it3 * p0.y + 3 * it2 * t * p1.y + 3 * it * t2 * p2.y + t3 * p3.y,
        z: it3 * p0.z + 3 * it2 * t * p1.z + 3 * it * t2 * p2.z + t3 * p3.z
      };
    }

    // 3D Projection with Cursor Rotation
    project(point, originX, originY, focalLength, rotX, rotY) {
      // Rotate around Y axis
      const cosY = Math.cos(rotY);
      const sinY = Math.sin(rotY);
      const x1 = point.x * cosY + point.z * sinY;
      const z1 = -point.x * sinY + point.z * cosY;

      // Rotate around X axis
      const cosX = Math.cos(rotX);
      const sinX = Math.sin(rotX);
      const y2 = point.y * cosX - z1 * sinX;
      const z2 = point.y * sinX + z1 * cosX;

      const perspective = focalLength / (focalLength + z2 + 250);
      return {
        x: originX + x1 * perspective,
        y: originY + y2 * perspective,
        z: z2,
        scale: perspective
      };
    }

    render() {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;

      ctx.clearRect(0, 0, w, h);

      // Center the origin in the open center-bottom perspective space
      // On desktop, push slightly toward the right-center (x: ~50% to 55%) and bottom-center (y: ~56%)
      const isMobile = w < 992;
      const originX = isMobile ? w * 0.5 : w * 0.52;
      const originY = isMobile ? h * 0.55 : h * 0.58;
      const focalLength = isMobile ? 480 : 640;

      // 3D Rotations based on mouse
      const rotY = this.mouse.currentX * 0.22;
      const rotX = -this.mouse.currentY * 0.16;

      ctx.save();
      ctx.globalCompositeOperation = 'screen';

      // 1. Subtle 3D Depth Floor Telemetry Lines (Linear / Luxury minimal)
      const gridSegments = 4;
      ctx.lineWidth = 1;
      for (let i = -gridSegments; i <= gridSegments; i++) {
        const gx = i * 45;
        const ptA = this.project({ x: gx, y: 220, z: -160 }, originX, originY, focalLength, rotX, rotY);
        const ptB = this.project({ x: gx + 30, y: 160, z: 120 }, originX, originY, focalLength, rotX, rotY);

        const gridAlpha = Math.max(0, 0.08 - Math.abs(i) * 0.015);
        ctx.strokeStyle = `rgba(168, 85, 247, ${gridAlpha})`;
        ctx.beginPath();
        ctx.moveTo(ptA.x, ptA.y);
        ctx.lineTo(ptB.x, ptB.y);
        ctx.stroke();
      }

      // 2. Render Ambient Dust Photons
      for (let i = 0; i < this.dust.length; i++) {
        const d = this.dust[i];
        const proj = this.project(d, originX, originY, focalLength, rotX, rotY);
        if (proj.scale > 0) {
          const dSize = d.size * proj.scale;
          ctx.fillStyle = `rgba(0, 240, 255, ${d.alpha * 0.45 * proj.scale})`;
          ctx.beginPath();
          ctx.arc(proj.x, proj.y, Math.max(0.6, dSize), 0, Math.PI * 2);
          ctx.fill();
        }
      }

      // 3. Render 3D Fiber-Optic Ascent Trails (Dual-Pass: Bloom + Razor Core)
      const samples = 48;

      this.trails.forEach((trail) => {
        // Sample points along the 3D Bezier curve
        const projectedPoints = [];
        for (let i = 0; i <= samples; i++) {
          const t = i / samples;
          // Add subtle dynamic wave ripple along curve
          const wave = Math.sin(this.time * 2 + t * 4) * 3;
          const p3d = this.getBezierPoint(trail.p0, trail.p1, trail.p2, trail.p3, t);
          p3d.x += wave * 0.5;
          p3d.z += wave;

          const proj = this.project(p3d, originX, originY, focalLength, rotX, rotY);
          projectedPoints.push(proj);
        }

        if (projectedPoints.length < 2) return;

        const startPt = projectedPoints[0];
        const endPt = projectedPoints[projectedPoints.length - 1];

        // Create linear gradient along the trajectory
        const grad = ctx.createLinearGradient(startPt.x, startPt.y, endPt.x, endPt.y);
        grad.addColorStop(0, 'rgba(0, 240, 255, 0)');
        grad.addColorStop(0.15, `rgba(0, 240, 255, ${trail.alpha * 0.5})`);
        grad.addColorStop(0.55, `rgba(168, 85, 247, ${trail.alpha * 0.85})`);
        grad.addColorStop(0.9, `rgba(192, 132, 252, ${trail.alpha})`);
        grad.addColorStop(1, 'rgba(255, 255, 255, 0)');

        // Pass A: Luminous Soft Bloom
        ctx.save();
        ctx.strokeStyle = grad;
        ctx.lineWidth = trail.width * 3.5;
        ctx.globalAlpha = trail.alpha * 0.35;
        ctx.beginPath();
        ctx.moveTo(projectedPoints[0].x, projectedPoints[0].y);
        for (let i = 1; i < projectedPoints.length; i++) {
          ctx.lineTo(projectedPoints[i].x, projectedPoints[i].y);
        }
        ctx.stroke();
        ctx.restore();

        // Pass B: Razor-Thin Core Fiber
        ctx.save();
        ctx.strokeStyle = grad;
        ctx.lineWidth = trail.width;
        ctx.globalAlpha = trail.alpha;
        ctx.beginPath();
        ctx.moveTo(projectedPoints[0].x, projectedPoints[0].y);
        for (let i = 1; i < projectedPoints.length; i++) {
          ctx.lineTo(projectedPoints[i].x, projectedPoints[i].y);
        }
        ctx.stroke();
        ctx.restore();
      });

      // 4. Render Fiber Photons (Streaming Light Packets)
      this.photons.forEach((photon) => {
        const trail = this.trails[photon.trailIndex];
        if (!trail) return;

        // Head and tail points on 3D curve
        const headT = Math.min(1, Math.max(0, photon.t));
        const tailT = Math.max(0, headT - photon.length);

        const head3d = this.getBezierPoint(trail.p0, trail.p1, trail.p2, trail.p3, headT);
        const tail3d = this.getBezierPoint(trail.p0, trail.p1, trail.p2, trail.p3, tailT);

        const headProj = this.project(head3d, originX, originY, focalLength, rotX, rotY);
        const tailProj = this.project(tail3d, originX, originY, focalLength, rotX, rotY);

        if (headProj.scale > 0 && tailProj.scale > 0) {
          // Fade photon in during ascent, fade out at peak
          const fade = Math.sin(headT * Math.PI);
          const pAlpha = photon.alpha * fade * headProj.scale;

          if (pAlpha > 0.02) {
            const photonGrad = ctx.createLinearGradient(tailProj.x, tailProj.y, headProj.x, headProj.y);
            photonGrad.addColorStop(0, 'rgba(0, 240, 255, 0)');
            photonGrad.addColorStop(1, `rgba(255, 255, 255, ${pAlpha})`);

            // Light trail
            ctx.strokeStyle = photonGrad;
            ctx.lineWidth = (trail.width + 1.2) * headProj.scale;
            ctx.beginPath();
            ctx.moveTo(tailProj.x, tailProj.y);
            ctx.lineTo(headProj.x, headProj.y);
            ctx.stroke();

            // Head beacon
            ctx.fillStyle = `rgba(0, 240, 255, ${pAlpha})`;
            ctx.beginPath();
            ctx.arc(headProj.x, headProj.y, photon.size * headProj.scale, 0, Math.PI * 2);
            ctx.fill();

            // Intense white core
            ctx.fillStyle = `rgba(255, 255, 255, ${pAlpha * 0.9})`;
            ctx.beginPath();
            ctx.arc(headProj.x, headProj.y, (photon.size * 0.5) * headProj.scale, 0, Math.PI * 2);
            ctx.fill();
          }
        }
      });

      ctx.restore();
    }
  }

  // Initialize once DOM is ready
  function init() {
    const stage = document.getElementById('vgPmSurgeStageWrap');
    if (stage) {
      new PMAscentTrails(stage);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
