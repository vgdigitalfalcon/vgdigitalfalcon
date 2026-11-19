/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D YOUTUBE ANALYTICS & ALGORITHM SURGE VISUAL
 * File: assets/js/yt-algorithm-surge.js
 * 
 * Features:
 * - 3D Perspective-tilted Dark Glass Analytics Grid (floating in center gap)
 * - Smooth Luminous 3D Neon Retention & View-Velocity Ribbon (#00F0FF to #A855F7)
 * - Exponential #1 Breakout Peak with Concentric 3D Algorithm Pulse Rings
 * - Interactive 3D Cursor Tilt Parallax (rotateX / rotateY with silky damping)
 * - Interactive 3D Vertical Laser Tracker with dynamic HUD telemetry
 * - Strictly z-index: 0, zero text overlap, tuned contrast (~48-52% opacity)
 * - Zero external dependencies, pure 60fps high-performance Canvas 3D
 * ============================================================================
 */

(function () {
  'use strict';

  class YouTubeAlgorithmSurge {
    constructor(container) {
      this.container = container;
      this.hero = container.closest('#youtube-seo-hero') || container.parentElement;

      this.canvas = document.createElement('canvas');
      this.canvas.className = 'vg-yt-surge-canvas';
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

      // Mouse Parallax & Laser Scrubbing
      this.mouse = {
        targetX: 0,
        targetY: 0,
        currentX: 0,
        currentY: 0,
        laserTargetT: 0.65,
        laserCurrentT: 0.65,
        isHovered: false
      };

      // Velocity Photons
      this.particles = [];
      const particleCount = 20;
      for (let i = 0; i < particleCount; i++) {
        this.particles.push({
          t: (i / particleCount) + Math.random() * 0.05,
          speed: 0.0035 + Math.random() * 0.003,
          size: 1.5 + Math.random() * 1.5,
          alpha: 0.4 + Math.random() * 0.6
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

      // Track cursor over hero section
      this.hero.addEventListener('mousemove', (e) => {
        const rect = this.hero.getBoundingClientRect();
        const nx = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        const ny = ((e.clientY - rect.top) / rect.height) * 2 - 1;

        this.mouse.targetX = nx;
        this.mouse.targetY = ny;
        this.mouse.isHovered = true;

        // Map mouse X to laser scrub position along curve (clamped between 0.08 and 0.95)
        const scrubT = Math.max(0.08, Math.min(0.95, (nx + 1) * 0.5));
        this.mouse.laserTargetT = scrubT;
      }, { passive: true });

      this.hero.addEventListener('mouseleave', () => {
        this.mouse.targetX = 0;
        this.mouse.targetY = 0;
        this.mouse.isHovered = false;
        this.mouse.laserTargetT = 0.65;
      }, { passive: true });

      // Handle visibility changes to save battery/GPU
      document.addEventListener('visibilitychange', () => {
        this.isTabActive = !document.hidden;
        if (this.isTabActive && !this.animId) {
          this.start();
        }
      });
    }

    setupObserver() {
      if ('IntersectionObserver' in window) {
        this.observer = new IntersectionObserver(
          (entries) => {
            entries.forEach((entry) => {
              this.isIntersecting = entry.isIntersecting;
              if (this.isIntersecting && !this.animId) {
                this.start();
              }
            });
          },
          { threshold: 0.05 }
        );
        this.observer.observe(this.hero);
      }
    }

    // 3D Perspective Projection Function
    project3D(x, y, z, cx, cy, rotX, rotY, rotZ, f) {
      // Rotate Y (Yaw)
      const cosY = Math.cos(rotY), sinY = Math.sin(rotY);
      const x1 = x * cosY + z * sinY;
      const z1 = -x * sinY + z * cosY;

      // Rotate X (Pitch)
      const cosX = Math.cos(rotX), sinX = Math.sin(rotX);
      const y2 = y * cosX - z1 * sinX;
      const z2 = y * sinX + z1 * cosX;

      // Rotate Z (Roll)
      const cosZ = Math.cos(rotZ), sinZ = Math.sin(rotZ);
      const x3 = x1 * cosZ - y2 * sinZ;
      const y3 = x1 * sinZ + y2 * cosZ;

      // Perspective scale factor
      const depth = z2 + 620;
      if (depth < 10) return null;
      const scale = f / depth;

      return {
        x: cx + x3 * scale,
        y: cy + y3 * scale,
        scale: scale,
        depth: depth
      };
    }

    // Parametric Retention & Algorithm Surge Curve definition: (x, y, z) in 3D object space
    getCurvePoint(t) {
      // t ranges from 0 to 1
      const startX = -260;
      const endX = 220;
      const x = startX + (endX - startX) * t;

      // Arch in Z-depth: curve curves slightly forward then backward
      const z = 110 - Math.sin(t * Math.PI) * 50 - t * 70;

      // Floor is at y = 110. Baseline retention stays ~85-90.
      // From t = 0.35, curve accelerates upward exponentially into breakout peak at y = -160.
      let y;
      if (t < 0.3) {
        y = 90 - Math.sin(t * 10) * 4;
      } else {
        const surgeT = (t - 0.3) / 0.7; // 0 to 1
        const surgeExp = Math.pow(surgeT, 2.3);
        y = 90 - surgeExp * 255;
      }

      return { x, y, z, floorY: 110 };
    }

    start() {
      const loop = () => {
        if (!this.isIntersecting || !this.isTabActive) {
          this.animId = null;
          return;
        }

        this.render();
        this.animId = requestAnimationFrame(loop);
      };

      this.animId = requestAnimationFrame(loop);
    }

    render() {
      const ctx = this.ctx;
      this.time += 0.018;

      // Clear Canvas
      ctx.clearRect(0, 0, this.width, this.height);

      // Smooth mouse lerp
      this.mouse.currentX += (this.mouse.targetX - this.mouse.currentX) * 0.06;
      this.mouse.currentY += (this.mouse.targetY - this.mouse.currentY) * 0.06;
      this.mouse.laserCurrentT += (this.mouse.laserTargetT - this.mouse.laserCurrentT) * 0.08;

      // Idle oscillation if mouse is not active
      const idleYaw = Math.sin(this.time * 0.45) * 0.08;
      const idlePitch = Math.cos(this.time * 0.35) * 0.04;

      // 3D Angles
      const rotX = 0.52 - (this.mouse.currentY * 0.18) + idlePitch;
      const rotY = -0.28 + (this.mouse.currentX * 0.22) + idleYaw;
      const rotZ = -this.mouse.currentX * 0.04;

      // Focal length and Center coordinates
      const f = 640;
      // Position stage in the open center gap corridor
      let cx = this.width >= 1200 ? this.width * 0.485 : this.width * 0.5;
      let cy = this.height * 0.46;

      // On smaller screens, scale down projection
      const globalScale = this.width < 768 ? 0.7 : this.width < 1200 ? 0.85 : 1.0;

      // Tuned global opacity for clean contrast (48% - 55% visual prominence)
      ctx.save();
      ctx.globalAlpha = 0.52;

      // -----------------------------------------------------------------------
      // 1. RENDER 3D DARK-GLASS GRID FLOOR
      // -----------------------------------------------------------------------
      const floorY = 110;
      const gridExtentX = 280 * globalScale;
      const gridExtentZ = 180 * globalScale;
      const stepX = 40 * globalScale;
      const stepZ = 30 * globalScale;

      ctx.save();
      ctx.lineWidth = 1;

      // Draw Grid Lines along Z
      for (let gx = -gridExtentX; gx <= gridExtentX; gx += stepX) {
        const p1 = this.project3D(gx, floorY, -gridExtentZ, cx, cy, rotX, rotY, rotZ, f);
        const p2 = this.project3D(gx, floorY, gridExtentZ, cx, cy, rotX, rotY, rotZ, f);

        if (p1 && p2) {
          const distFactor = Math.abs(gx) / gridExtentX;
          const strokeAlpha = 0.16 * (1 - distFactor * 0.6);
          ctx.strokeStyle = `rgba(168, 85, 247, ${strokeAlpha})`;
          ctx.beginPath();
          ctx.moveTo(p1.x, p1.y);
          ctx.lineTo(p2.x, p2.y);
          ctx.stroke();
        }
      }

      // Draw Grid Lines along X
      for (let gz = -gridExtentZ; gz <= gridExtentZ; gz += stepZ) {
        const p1 = this.project3D(-gridExtentX, floorY, gz, cx, cy, rotX, rotY, rotZ, f);
        const p2 = this.project3D(gridExtentX, floorY, gz, cx, cy, rotX, rotY, rotZ, f);

        if (p1 && p2) {
          const distFactor = Math.abs(gz) / gridExtentZ;
          const strokeAlpha = 0.14 * (1 - distFactor * 0.5);
          ctx.strokeStyle = `rgba(0, 240, 255, ${strokeAlpha})`;
          ctx.beginPath();
          ctx.moveTo(p1.x, p1.y);
          ctx.lineTo(p2.x, p2.y);
          ctx.stroke();
        }
      }

      // Glass Floor Glowing Boundary Frame
      const cNW = this.project3D(-gridExtentX, floorY, -gridExtentZ, cx, cy, rotX, rotY, rotZ, f);
      const cNE = this.project3D(gridExtentX, floorY, -gridExtentZ, cx, cy, rotX, rotY, rotZ, f);
      const cSE = this.project3D(gridExtentX, floorY, gridExtentZ, cx, cy, rotX, rotY, rotZ, f);
      const cSW = this.project3D(-gridExtentX, floorY, gridExtentZ, cx, cy, rotX, rotY, rotZ, f);

      if (cNW && cNE && cSE && cSW) {
        // Floor Glass Gradient Fill
        const floorGrad = ctx.createLinearGradient(cNW.x, cNW.y, cSE.x, cSE.y);
        floorGrad.addColorStop(0, 'rgba(168, 85, 247, 0.04)');
        floorGrad.addColorStop(0.5, 'rgba(10, 6, 24, 0.35)');
        floorGrad.addColorStop(1, 'rgba(0, 240, 255, 0.05)');

        ctx.fillStyle = floorGrad;
        ctx.beginPath();
        ctx.moveTo(cNW.x, cNW.y);
        ctx.lineTo(cNE.x, cNE.y);
        ctx.lineTo(cSE.x, cSE.y);
        ctx.lineTo(cSW.x, cSW.y);
        ctx.closePath();
        ctx.fill();

        // Floor Perimeter Rim Glow
        ctx.strokeStyle = 'rgba(168, 85, 247, 0.28)';
        ctx.stroke();
      }

      // Floor Timeline Axis Labels
      const timeTicks = [
        { label: '00:00 BASELINE', t: 0.05 },
        { label: '02:30 HOOK', t: 0.35 },
        { label: '05:00 RETENTION SPIKE', t: 0.65 },
        { label: '08:45 ALGO SURGE', t: 0.95 }
      ];

      ctx.font = '600 9px "Plus Jakarta Sans", sans-serif';
      ctx.textAlign = 'center';
      timeTicks.forEach((tick) => {
        const pt = this.getCurvePoint(tick.t);
        const proj = this.project3D(pt.x * globalScale, floorY + 12, (pt.z + 15) * globalScale, cx, cy, rotX, rotY, rotZ, f);
        if (proj) {
          ctx.fillStyle = 'rgba(192, 132, 252, 0.45)';
          ctx.fillText(tick.label, proj.x, proj.y);
        }
      });

      ctx.restore();

      // -----------------------------------------------------------------------
      // 2. RENDER 3D LUMINOUS NEON RETENTION & VIEW-VELOCITY RIBBON
      // -----------------------------------------------------------------------
      const curveSteps = 45;
      const curveProj = [];
      const floorProj = [];

      for (let i = 0; i <= curveSteps; i++) {
        const t = i / curveSteps;
        const pt = this.getCurvePoint(t);
        const pCurve = this.project3D(pt.x * globalScale, pt.y * globalScale, pt.z * globalScale, cx, cy, rotX, rotY, rotZ, f);
        const pFloor = this.project3D(pt.x * globalScale, pt.floorY * globalScale, pt.z * globalScale, cx, cy, rotX, rotY, rotZ, f);

        curveProj.push({ pt, proj: pCurve, t });
        floorProj.push({ pt, proj: pFloor, t });
      }

      // Volumetric Under-Curve Ribbon Fill
      ctx.save();
      ctx.beginPath();
      let first = true;
      for (let i = 0; i <= curveSteps; i++) {
        const cp = curveProj[i].proj;
        if (!cp) continue;
        if (first) {
          ctx.moveTo(cp.x, cp.y);
          first = false;
        } else {
          ctx.lineTo(cp.x, cp.y);
        }
      }
      for (let i = curveSteps; i >= 0; i--) {
        const fp = floorProj[i].proj;
        if (fp) ctx.lineTo(fp.x, fp.y);
      }
      ctx.closePath();

      // Translucent Cyber Violet to Cyan Area Gradient
      const startPt = curveProj[0].proj;
      const peakPt = curveProj[curveSteps].proj;
      if (startPt && peakPt) {
        const areaGrad = ctx.createLinearGradient(startPt.x, startPt.y, peakPt.x, peakPt.y);
        areaGrad.addColorStop(0, 'rgba(0, 240, 255, 0.08)');
        areaGrad.addColorStop(0.5, 'rgba(168, 85, 247, 0.16)');
        areaGrad.addColorStop(1, 'rgba(0, 240, 255, 0.28)');
        ctx.fillStyle = areaGrad;
        ctx.fill();
      }

      // Vertical Stems under the curve
      ctx.lineWidth = 1;
      for (let i = 5; i <= curveSteps; i += 5) {
        const cp = curveProj[i].proj;
        const fp = floorProj[i].proj;
        if (cp && fp) {
          const t = i / curveSteps;
          const stemAlpha = 0.12 + t * 0.25;
          ctx.strokeStyle = `rgba(0, 240, 255, ${stemAlpha})`;
          ctx.setLineDash([2, 3]);
          ctx.beginPath();
          ctx.moveTo(cp.x, cp.y);
          ctx.lineTo(fp.x, fp.y);
          ctx.stroke();
          ctx.setLineDash([]);
        }
      }
      ctx.restore();

      // Main 3D Neon Curve Ribbon (Multi-pass glow)
      ctx.save();
      // Pass 1: Broad soft neon bloom
      ctx.lineWidth = 9 * globalScale;
      ctx.beginPath();
      first = true;
      for (let i = 0; i <= curveSteps; i++) {
        const cp = curveProj[i].proj;
        if (!cp) continue;
        if (first) { ctx.moveTo(cp.x, cp.y); first = false; }
        else { ctx.lineTo(cp.x, cp.y); }
      }
      if (startPt && peakPt) {
        const glowGrad = ctx.createLinearGradient(startPt.x, startPt.y, peakPt.x, peakPt.y);
        glowGrad.addColorStop(0, 'rgba(0, 240, 255, 0.25)');
        glowGrad.addColorStop(0.5, 'rgba(168, 85, 247, 0.35)');
        glowGrad.addColorStop(1, 'rgba(0, 240, 255, 0.55)');
        ctx.strokeStyle = glowGrad;
        ctx.stroke();
      }

      // Pass 2: High-contrast core laser line
      ctx.lineWidth = 3.2 * globalScale;
      ctx.beginPath();
      first = true;
      for (let i = 0; i <= curveSteps; i++) {
        const cp = curveProj[i].proj;
        if (!cp) continue;
        if (first) { ctx.moveTo(cp.x, cp.y); first = false; }
        else { ctx.lineTo(cp.x, cp.y); }
      }
      if (startPt && peakPt) {
        const coreGrad = ctx.createLinearGradient(startPt.x, startPt.y, peakPt.x, peakPt.y);
        coreGrad.addColorStop(0, '#00F0FF');
        coreGrad.addColorStop(0.45, '#C084FC');
        coreGrad.addColorStop(0.85, '#A855F7');
        coreGrad.addColorStop(1, '#FFFFFF');
        ctx.strokeStyle = coreGrad;
        ctx.stroke();
      }
      ctx.restore();

      // -----------------------------------------------------------------------
      // 3. VELOCITY PARTICLES FLOWING ALONG 3D SURGE CURVE
      // -----------------------------------------------------------------------
      ctx.save();
      this.particles.forEach((p) => {
        p.t += p.speed;
        if (p.t > 1) p.t = 0;

        const pt = this.getCurvePoint(p.t);
        const proj = this.project3D(pt.x * globalScale, pt.y * globalScale, pt.z * globalScale, cx, cy, rotX, rotY, rotZ, f);

        if (proj) {
          const pSize = p.size * proj.scale * 1.2;
          ctx.fillStyle = p.t > 0.6 ? '#00F0FF' : '#C084FC';
          ctx.shadowColor = '#00F0FF';
          ctx.shadowBlur = 8;
          ctx.beginPath();
          ctx.arc(proj.x, proj.y, Math.max(1, pSize), 0, Math.PI * 2);
          ctx.fill();
        }
      });
      ctx.restore();

      // -----------------------------------------------------------------------
      // 4. BREAKOUT PEAK (#1 ALGORITHM RECOMMENDATION APEX) & PULSE RINGS
      // -----------------------------------------------------------------------
      const peakIndex = curveSteps;
      const peakData = curveProj[peakIndex];
      const peakP = peakData ? peakData.proj : null;
      const peak3D = peakData ? peakData.pt : null;

      if (peakP && peak3D) {
        ctx.save();

        // Emit Concentric 3D Pulse Rings in the horizontal XZ plane
        const ringCount = 3;
        for (let r = 0; r < ringCount; r++) {
          const ringPhase = (this.time * 0.75 + r * (1 / ringCount)) % 1;
          const radius = (15 + ringPhase * 65) * globalScale;
          const ringAlpha = (1 - ringPhase) * 0.65;

          ctx.strokeStyle = `rgba(0, 240, 255, ${ringAlpha})`;
          ctx.lineWidth = 1.8;
          ctx.beginPath();

          // Draw ring in 3D perspective
          const segments = 24;
          let ringFirst = true;
          for (let s = 0; s <= segments; s++) {
            const angle = (s / segments) * Math.PI * 2;
            const rx = peak3D.x * globalScale + Math.cos(angle) * radius;
            const rz = peak3D.z * globalScale + Math.sin(angle) * radius;
            const rProj = this.project3D(rx, peak3D.y * globalScale, rz, cx, cy, rotX, rotY, rotZ, f);
            if (rProj) {
              if (ringFirst) { ctx.moveTo(rProj.x, rProj.y); ringFirst = false; }
              else { ctx.lineTo(rProj.x, rProj.y); }
            }
          }
          ctx.stroke();
        }

        // Apex Glowing Beacon Star
        const beaconSize = 6 * globalScale;
        ctx.fillStyle = '#FFFFFF';
        ctx.shadowColor = '#00F0FF';
        ctx.shadowBlur = 18;
        ctx.beginPath();
        ctx.arc(peakP.x, peakP.y, beaconSize, 0, Math.PI * 2);
        ctx.fill();

        // Floating peak pill badge removed as requested

        ctx.restore();
      }

      // -----------------------------------------------------------------------
      // 5. INTERACTIVE 3D LASER TRACKER & TELEMETRY HUD
      // -----------------------------------------------------------------------
      const laserT = this.mouse.laserCurrentT;
      const laserPt = this.getCurvePoint(laserT);
      const laserCurveProj = this.project3D(laserPt.x * globalScale, laserPt.y * globalScale, laserPt.z * globalScale, cx, cy, rotX, rotY, rotZ, f);
      const laserFloorProj = this.project3D(laserPt.x * globalScale, laserPt.floorY * globalScale, laserPt.z * globalScale, cx, cy, rotX, rotY, rotZ, f);

      if (laserCurveProj && laserFloorProj) {
        ctx.save();

        // Vertical Laser Beam (Down to glass floor)
        const laserGrad = ctx.createLinearGradient(laserCurveProj.x, laserCurveProj.y, laserFloorProj.x, laserFloorProj.y);
        laserGrad.addColorStop(0, 'rgba(0, 240, 255, 0.9)');
        laserGrad.addColorStop(0.5, 'rgba(168, 85, 247, 0.6)');
        laserGrad.addColorStop(1, 'rgba(0, 240, 255, 0.15)');

        ctx.strokeStyle = laserGrad;
        ctx.lineWidth = 2;
        ctx.shadowColor = '#00F0FF';
        ctx.shadowBlur = 10;
        ctx.beginPath();
        ctx.moveTo(laserCurveProj.x, laserCurveProj.y);
        ctx.lineTo(laserFloorProj.x, laserFloorProj.y);
        ctx.stroke();

        // Floor Impact Beacon Circle
        ctx.strokeStyle = 'rgba(0, 240, 255, 0.45)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.ellipse(laserFloorProj.x, laserFloorProj.y, 14 * globalScale, 6 * globalScale, 0, 0, Math.PI * 2);
        ctx.stroke();

        // Laser Curve Bead
        ctx.fillStyle = '#FFFFFF';
        ctx.shadowColor = '#00F0FF';
        ctx.shadowBlur = 15;
        ctx.beginPath();
        ctx.arc(laserCurveProj.x, laserCurveProj.y, 4.5 * globalScale, 0, Math.PI * 2);
        ctx.fill();

        // Outer Ring on Bead
        ctx.strokeStyle = '#00F0FF';
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.arc(laserCurveProj.x, laserCurveProj.y, 8.5 * globalScale, 0, Math.PI * 2);
        ctx.stroke();

        // Floating Telemetry HUD Card
        const hudX = laserCurveProj.x + 16 * globalScale;
        const hudY = laserCurveProj.y - 32 * globalScale;
        const retentionVal = (78 + laserT * 21).toFixed(1);
        const velocityVal = (1.5 + Math.pow(laserT, 2) * 5.8).toFixed(1);

        const hudLine1 = `RETENTION: ${retentionVal}%`;
        const hudLine2 = `VELOCITY: ${velocityVal}X`;

        ctx.font = '700 10px "Plus Jakarta Sans", sans-serif';
        const line1W = ctx.measureText(hudLine1).width;
        const line2W = ctx.measureText(hudLine2).width;
        const cardW = Math.max(line1W, line2W) + 16;
        const cardH = 34;

        // HUD Dark Glass Box
        ctx.fillStyle = 'rgba(10, 6, 26, 0.88)';
        ctx.strokeStyle = 'rgba(168, 85, 247, 0.45)';
        ctx.lineWidth = 1;
        ctx.shadowColor = 'rgba(0, 0, 0, 0.6)';
        ctx.shadowBlur = 10;
        ctx.beginPath();
        ctx.roundRect(hudX, hudY, cardW, cardH, 6);
        ctx.fill();
        ctx.stroke();

        // HUD Text Lines
        ctx.shadowBlur = 0;
        ctx.textAlign = 'left';
        ctx.textBaseline = 'top';

        ctx.fillStyle = '#00F0FF';
        ctx.fillText(hudLine1, hudX + 8, hudY + 7);

        ctx.fillStyle = '#C084FC';
        ctx.fillText(hudLine2, hudX + 8, hudY + 20);

        ctx.restore();
      }

      ctx.restore(); // Restore globalAlpha
    }
  }

  // Auto-initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    const wrap = document.getElementById('vgYtSurgeStageWrap');
    if (wrap) {
      new YouTubeAlgorithmSurge(wrap);
    }
  });
})();
