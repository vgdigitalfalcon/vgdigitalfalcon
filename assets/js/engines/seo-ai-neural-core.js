/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D TRANSLUCENT GLASS OCTAHEDRON & LIVE LASER CORE
 * File: assets/js/seo-ai-neural-core.js
 * 
 * Features:
 * 1. 3D Translucent Glass Octahedron / AI Core:
 *    - True 3D Mathematical Octahedron with 6 vertices & 8 translucent glass facets
 *    - Iridescent specular reflection with depth-sorted face rendering
 *    - Inner rotating neon energy crystal in active accent color
 *    - Hovering floating animation with subtle 3D mouse parallax
 * 2. 3D Perspective Radar Grid Floor:
 *    - Receding perspective elliptical radar rings
 *    - Sweeping 360-degree radar scanner beam with fading tail
 *    - Expanding shockwave ripple rings on pillar transitions
 * 3. Live Laser Connector System:
 *    - Dynamic SVG / Canvas laser beam shooting directly from 3D Glass Core
 *      to the active compact HUD module (Pillar 01 to 06)
 *    - Traveling laser photon packet + pinpoint target reticle at the module
 * 4. Automated Synchronization Controller (Zero User Clicking Required):
 *    - Cycles through Pillar 01 to 06 every 2.8s
 *    - Updates the active compact module with glowing border & metric pulse
 *    - Dynamically displays the 3 detailed bullet points in the Central HUD card
 *    - Supports instant hover / click interaction with smooth resume
 * ============================================================================
 */

(function () {
  'use strict';

  // 6 Intelligence Pillars Data (with their 3 detailed bullet points)
  const PILLARS_DATA = [
    {
      id: 'ai-module-1',
      pillarNum: 'PILLAR 01',
      name: 'AI Keyword Intelligence',
      metric: '99.4% Intent Match',
      accent: '#00F0FF',
      accentRgb: '0, 240, 255',
      bandwidth: '14.2 Gbps',
      latency: '0.12ms',
      bullets: [
        'High-intent commercial query mapping',
        'Semantic topical entity clusters',
        'Zero-click & conversational query capture'
      ]
    },
    {
      id: 'ai-module-2',
      pillarNum: 'PILLAR 02',
      name: 'Competitor Intelligence',
      metric: 'Real-Time Tracking',
      accent: '#A855F7',
      accentRgb: '168, 85, 247',
      bandwidth: '18.6 Gbps',
      latency: '0.14ms',
      bullets: [
        'Real-time competitor ranking shifts',
        'Backlink velocity & gap intelligence',
        'Authority share-of-voice profiling'
      ]
    },
    {
      id: 'ai-module-3',
      pillarNum: 'PILLAR 03',
      name: 'AI Content Intelligence',
      metric: '100% E-E-A-T Verified',
      accent: '#10B981',
      accentRgb: '16, 185, 129',
      bandwidth: '11.5 Gbps',
      latency: '0.15ms',
      bullets: [
        'Information gain score optimization',
        'Algorithmic content decay prevention',
        'Natural tone & E-E-A-T guideline adherence'
      ]
    },
    {
      id: 'ai-module-4',
      pillarNum: 'PILLAR 04',
      name: 'Technical SEO Intelligence',
      metric: '99.8% Healthy Ratio',
      accent: '#F59E0B',
      accentRgb: '245, 158, 11',
      bandwidth: '22.4 Gbps',
      latency: '0.09ms',
      bullets: [
        'Continuous crawl budget surveillance',
        'Instant Core Web Vitals anomaly alerts',
        'Schema & structured data auto-validation'
      ]
    },
    {
      id: 'ai-module-5',
      pillarNum: 'PILLAR 05',
      name: 'Search Experience Optimization',
      metric: 'Authority Cited',
      accent: '#38BDF8',
      accentRgb: '56, 189, 248',
      bandwidth: '16.8 Gbps',
      latency: '0.11ms',
      bullets: [
        'Google SGE & Gemini AI Overview targeting',
        'Perplexity & ChatGPT citation readiness',
        'Zero-click answer snippet authority'
      ]
    },
    {
      id: 'ai-module-6',
      pillarNum: 'PILLAR 06',
      name: 'Predictive SEO Insights',
      metric: '90 Days Ahead',
      accent: '#EC4899',
      accentRgb: '236, 72, 153',
      bandwidth: '19.1 Gbps',
      latency: '0.13ms',
      bullets: [
        'Search volume spike trend prediction',
        'Preemptive algorithm shock protection',
        'Emerging niche topic pre-ranking'
      ]
    }
  ];

  /* --------------------------------------------------------------------------
   * 3D Glass Octahedron & Radar Floor Canvas Engine
   * -------------------------------------------------------------------------- */
  class GlassOctahedronCore {
    constructor(canvas) {
      this.canvas = canvas;
      this.ctx = canvas.getContext('2d', { alpha: true });
      if (!this.ctx) return;

      this.hub = canvas.closest('.vg-ai-neural-hub') || canvas.parentElement;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);

      // Core parameters
      this.radius = 65; // Octahedron outer radius
      this.rotX = 0.35;
      this.rotY = 0;
      this.rotZ = 0.1;
      this.targetRotSpeedY = 0.014;
      this.targetRotSpeedX = 0.003;

      // Mouse Parallax
      this.mouseX = 0;
      this.mouseY = 0;
      this.targetMouseX = 0;
      this.targetMouseY = 0;

      // Radar floor
      this.radarAngle = 0;
      this.radarRipples = [];

      // Active state & laser target
      this.activePillarIndex = 0;
      this.activeAccent = '#00F0FF';
      this.activeAccentRgb = '0, 240, 255';
      this.laserProgress = 0; // for pulse traveling along laser

      // Laser endpoint in canvas coordinates (if on desktop)
      this.laserTarget = null;

      this.initOctahedronGeometry();
      this.resize();
      this.bindEvents();
    }

    initOctahedronGeometry() {
      // 6 Canonical Octahedron Vertices
      // Top (0), Bottom (1), Right (2), Front (3), Left (4), Back (5)
      this.baseVertices = [
        { x: 0, y: -1, z: 0 },  // 0: Top
        { x: 0, y: 1, z: 0 },   // 1: Bottom
        { x: 1, y: 0, z: 0 },   // 2: Right
        { x: 0, y: 0, z: 1 },   // 3: Front
        { x: -1, y: 0, z: 0 },  // 4: Left
        { x: 0, y: 0, z: -1 }   // 5: Back
      ];

      // 8 Triangular Faces (indices into baseVertices)
      this.faces = [
        [0, 2, 3], // Top Right Front
        [0, 3, 4], // Top Left Front
        [0, 4, 5], // Top Left Back
        [0, 5, 2], // Top Right Back
        [1, 3, 2], // Bottom Right Front
        [1, 4, 3], // Bottom Left Front
        [1, 5, 4], // Bottom Left Back
        [1, 2, 5]  // Bottom Right Back
      ];

      // Inner Crystal Vertices (smaller inverted octahedron)
      this.innerScale = 0.42;
    }

    resize() {
      const rect = this.canvas.parentElement.getBoundingClientRect();
      this.width = rect.width || 380;
      this.height = Math.min(270, Math.max(220, rect.height || 250));

      this.canvas.width = this.width * this.dpr;
      this.canvas.height = this.height * this.dpr;
      this.canvas.style.width = this.width + 'px';
      this.canvas.style.height = this.height + 'px';

      this.ctx.scale(this.dpr, this.dpr);

      // Adjust radius based on screen size
      this.radius = Math.max(50, Math.min(68, this.width * 0.18));
    }

    bindEvents() {
      window.addEventListener('resize', () => this.resize(), { passive: true });

      if (this.hub) {
        this.hub.addEventListener('mousemove', (e) => {
          const rect = this.canvas.getBoundingClientRect();
          const cx = rect.left + rect.width / 2;
          const cy = rect.top + rect.height / 2;
          this.targetMouseX = (e.clientX - cx) * 0.0003;
          this.targetMouseY = (e.clientY - cy) * 0.0003;
        }, { passive: true });

        this.hub.addEventListener('mouseleave', () => {
          this.targetMouseX = 0;
          this.targetMouseY = 0;
        }, { passive: true });
      }
    }

    rotate3D(v, rotX, rotY, rotZ) {
      // Rotate Y
      const cosY = Math.cos(rotY), sinY = Math.sin(rotY);
      const x1 = v.x * cosY + v.z * sinY;
      const z1 = -v.x * sinY + v.z * cosY;

      // Rotate X
      const cosX = Math.cos(rotX), sinX = Math.sin(rotX);
      const y2 = v.y * cosX - z1 * sinX;
      const z2 = v.y * sinX + z1 * cosX;

      // Rotate Z
      const cosZ = Math.cos(rotZ), sinZ = Math.sin(rotZ);
      const x3 = x1 * cosZ - y2 * sinZ;
      const y3 = x1 * sinZ + y2 * cosZ;

      return { x: x3, y: y3, z: z2 };
    }

    setActivePillar(index, data) {
      this.activePillarIndex = index;
      this.activeAccent = data.accent;
      this.activeAccentRgb = data.accentRgb;

      // Trigger radar ripple
      this.radarRipples.push({
        radius: 10,
        maxRadius: 130,
        alpha: 0.8,
        color: data.accent
      });
    }

    render(time) {
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;
      const cx = w / 2;
      const cy = h / 2 - 15; // slightly above radar floor

      ctx.clearRect(0, 0, w, h);

      // Smooth mouse parallax
      this.mouseX += (this.targetMouseX - this.mouseX) * 0.06;
      this.mouseY += (this.targetMouseY - this.mouseY) * 0.06;

      this.rotY += this.targetRotSpeedY + this.mouseX;
      this.rotX += this.targetRotSpeedX + this.mouseY;

      // Hover floating offset
      const hoverY = Math.sin(time * 0.0025) * 6;
      const coreY = cy + hoverY;

      /* 1. Render 3D Perspective Radar Floor */
      this.drawRadarFloor(ctx, cx, cy + 65, time);

      /* 2. Holographic Core Aura */
      const auraPulse = Math.sin(time * 0.003) * 0.1 + 0.9;
      const auraRadius = this.radius * 1.5 * auraPulse;
      const auraGrad = ctx.createRadialGradient(cx, coreY, 0, cx, coreY, auraRadius);
      auraGrad.addColorStop(0, 'rgba(' + this.activeAccentRgb + ', 0.32)');
      auraGrad.addColorStop(0.4, 'rgba(168, 85, 247, 0.18)');
      auraGrad.addColorStop(0.8, 'rgba(0, 240, 255, 0.04)');
      auraGrad.addColorStop(1, 'rgba(6, 2, 16, 0)');

      ctx.fillStyle = auraGrad;
      ctx.beginPath();
      ctx.arc(cx, coreY, auraRadius, 0, Math.PI * 2);
      ctx.fill();

      /* 3. Project 3D Vertices */
      const fov = 320;
      const R = this.radius;

      // Transform outer vertices
      const projVertices = this.baseVertices.map(bv => {
        const scaled = { x: bv.x * R, y: bv.y * R, z: bv.z * R };
        const rot = this.rotate3D(scaled, this.rotX, this.rotY, this.rotZ);
        const scale = fov / (fov + rot.z);
        return {
          x: cx + rot.x * scale,
          y: coreY + rot.y * scale,
          z: rot.z,
          scale: scale
        };
      });

      // Transform inner crystal vertices (counter-rotation)
      const rInner = R * this.innerScale;
      const projInner = this.baseVertices.map(bv => {
        const scaled = { x: bv.x * rInner, y: bv.y * rInner, z: bv.z * rInner };
        const rot = this.rotate3D(scaled, -this.rotX * 1.2, -this.rotY * 1.5, this.rotZ);
        const scale = fov / (fov + rot.z);
        return {
          x: cx + rot.x * scale,
          y: coreY + rot.y * scale,
          z: rot.z,
          scale: scale
        };
      });

      /* 4. Calculate Face Normals & Sort */
      const faceData = this.faces.map((f, idx) => {
        const p0 = projVertices[f[0]];
        const p1 = projVertices[f[1]];
        const p2 = projVertices[f[2]];

        // Cross product 2D for winding / depth
        const normalZ = (p1.x - p0.x) * (p2.y - p0.y) - (p1.y - p0.y) * (p2.x - p0.x);
        const avgZ = (p0.z + p1.z + p2.z) / 3;

        return {
          idx: idx,
          indices: f,
          p0: p0,
          p1: p1,
          p2: p2,
          isFront: normalZ < 0, // Inverted Y-axis in canvas
          avgZ: avgZ
        };
      });

      // Render Back Faces (Translucent Glass Rear)
      ctx.save();
      for (const face of faceData) {
        if (!face.isFront) {
          ctx.beginPath();
          ctx.moveTo(face.p0.x, face.p0.y);
          ctx.lineTo(face.p1.x, face.p1.y);
          ctx.lineTo(face.p2.x, face.p2.y);
          ctx.closePath();

          ctx.fillStyle = 'rgba(168, 85, 247, 0.08)';
          ctx.fill();

          ctx.strokeStyle = 'rgba(168, 85, 247, 0.22)';
          ctx.lineWidth = 1;
          ctx.stroke();
        }
      }
      ctx.restore();

      /* 5. Render Inner Glowing Energy Crystal */
      this.drawInnerCrystal(ctx, projInner, coreY, time);

      /* 6. Render Front Faces (Iridescent Specular Glass) */
      ctx.save();
      // Sort front faces back to front
      const frontFaces = faceData.filter(f => f.isFront);
      frontFaces.sort((a, b) => a.avgZ - b.avgZ);

      for (const face of frontFaces) {
        ctx.beginPath();
        ctx.moveTo(face.p0.x, face.p0.y);
        ctx.lineTo(face.p1.x, face.p1.y);
        ctx.lineTo(face.p2.x, face.p2.y);
        ctx.closePath();

        // Iridescent Glass Gradient fill
        const grad = ctx.createLinearGradient(face.p0.x, face.p0.y, face.p2.x, face.p2.y);
        grad.addColorStop(0, 'rgba(0, 240, 255, 0.28)');
        grad.addColorStop(0.5, 'rgba(168, 85, 247, 0.18)');
        grad.addColorStop(1, 'rgba(255, 255, 255, 0.35)');

        ctx.fillStyle = grad;
        ctx.fill();

        // Glass Glowing Edge
        ctx.strokeStyle = 'rgba(0, 240, 255, 0.75)';
        ctx.lineWidth = 1.6;
        ctx.shadowColor = '#00F0FF';
        ctx.shadowBlur = 8;
        ctx.stroke();
        ctx.shadowBlur = 0;
      }
      ctx.restore();

      /* 7. Draw Vertex Flare Highlights */
      ctx.save();
      for (const v of projVertices) {
        if (v.z > -20) {
          ctx.fillStyle = '#FFFFFF';
          ctx.shadowColor = '#00F0FF';
          ctx.shadowBlur = 10;
          ctx.beginPath();
          ctx.arc(v.x, v.y, 2.2 * v.scale, 0, Math.PI * 2);
          ctx.fill();
        }
      }
      ctx.restore();

      /* 8. Draw Live Laser Core Emitter Node */
      ctx.save();
      ctx.fillStyle = '#FFFFFF';
      ctx.shadowColor = this.activeAccent;
      ctx.shadowBlur = 14;
      ctx.beginPath();
      ctx.arc(cx, coreY, 4, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    }

    drawInnerCrystal(ctx, projInner, coreY, time) {
      ctx.save();
      // Draw inner octahedron wireframe
      ctx.strokeStyle = this.activeAccent;
      ctx.lineWidth = 1.4;
      ctx.shadowColor = this.activeAccent;
      ctx.shadowBlur = 12;

      for (const face of this.faces) {
        const p0 = projInner[face[0]];
        const p1 = projInner[face[1]];
        const p2 = projInner[face[2]];

        ctx.beginPath();
        ctx.moveTo(p0.x, p0.y);
        ctx.lineTo(p1.x, p1.y);
        ctx.lineTo(p2.x, p2.y);
        ctx.closePath();
        ctx.fillStyle = 'rgba(' + this.activeAccentRgb + ', 0.18)';
        ctx.fill();
        ctx.stroke();
      }

      // Hot white nucleus center
      ctx.fillStyle = '#FFFFFF';
      ctx.beginPath();
      ctx.arc(projInner[0].x * 0.1 + (this.width / 2) * 0.9, coreY, 3, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    }

    drawRadarFloor(ctx, cx, floorY, time) {
      ctx.save();

      // Perspective scale factor
      const aspect = 0.32; // ellipse vertical compression for 3D floor
      const numRings = 4;
      const baseR = this.radius * 2.2;

      // 1. Concentric Elliptical Radar Rings
      for (let i = 1; i <= numRings; i++) {
        const r = (baseR / numRings) * i;
        ctx.beginPath();
        ctx.ellipse(cx, floorY, r, r * aspect, 0, 0, Math.PI * 2);
        ctx.strokeStyle = i === numRings ? 'rgba(0, 240, 255, 0.28)' : 'rgba(168, 85, 247, 0.16)';
        ctx.lineWidth = 1;
        if (i % 2 === 1) ctx.setLineDash([4, 4]);
        else ctx.setLineDash([]);
        ctx.stroke();
      }

      // 2. Radial Crosshair Lines
      ctx.setLineDash([2, 4]);
      ctx.strokeStyle = 'rgba(0, 240, 255, 0.18)';
      const angles = [0, Math.PI * 0.25, Math.PI * 0.5, Math.PI * 0.75, Math.PI, Math.PI * 1.25, Math.PI * 1.5, Math.PI * 1.75];
      for (const a of angles) {
        const px = cx + Math.cos(a) * baseR;
        const py = floorY + Math.sin(a) * baseR * aspect;
        ctx.beginPath();
        ctx.moveTo(cx, floorY);
        ctx.lineTo(px, py);
        ctx.stroke();
      }

      // 3. Sweeping Radar Scanner Beam
      this.radarAngle += 0.024;
      const sweepEndAngle = this.radarAngle;
      const sweepStartAngle = this.radarAngle - 0.45;

      const beamX = cx + Math.cos(sweepEndAngle) * baseR;
      const beamY = floorY + Math.sin(sweepEndAngle) * baseR * aspect;

      // Scanner beam line
      ctx.setLineDash([]);
      ctx.strokeStyle = 'rgba(0, 240, 255, 0.65)';
      ctx.lineWidth = 1.5;
      ctx.beginPath();
      ctx.moveTo(cx, floorY);
      ctx.lineTo(beamX, beamY);
      ctx.stroke();

      // Scanner fading sector
      ctx.beginPath();
      ctx.moveTo(cx, floorY);
      ctx.arc(cx, floorY, baseR, sweepStartAngle, sweepEndAngle);
      ctx.closePath();
      ctx.fillStyle = 'rgba(0, 240, 255, 0.06)';
      ctx.fill();

      // 4. Update & Draw Radar Ripples
      for (let i = this.radarRipples.length - 1; i >= 0; i--) {
        const rip = this.radarRipples[i];
        rip.radius += 1.8;
        rip.alpha -= 0.016;

        if (rip.alpha <= 0 || rip.radius >= rip.maxRadius) {
          this.radarRipples.splice(i, 1);
          continue;
        }

        ctx.beginPath();
        ctx.ellipse(cx, floorY, rip.radius, rip.radius * aspect, 0, 0, Math.PI * 2);
        ctx.strokeStyle = rip.color;
        ctx.globalAlpha = Math.max(0, rip.alpha);
        ctx.lineWidth = 1.4;
        ctx.stroke();
        ctx.globalAlpha = 1.0;
      }

      ctx.restore();
    }
  }

  /* --------------------------------------------------------------------------
   * Live Laser Connector & Synchronization Controller
   * -------------------------------------------------------------------------- */
  class ExecutiveNeuralHubManager {
    constructor(hubElement, coreInstance) {
      this.hub = hubElement;
      this.core = coreInstance;
      this.modules = [];
      this.currentIndex = 0;
      this.timer = null;
      this.cycleDuration = 2800; // 2.8s per pillar
      this.isLockedByHover = false;

      // Central HUD Element Refs
      this.pillarTagEl = document.getElementById('vgHudPillarTag');
      this.pillarNameEl = document.getElementById('vgHudPillarName');
      this.metricValEl = document.getElementById('vgHudMetricVal');
      this.bandwidthEl = document.getElementById('vgHudBandwidth');
      this.latencyEl = document.getElementById('vgHudLatency');
      this.bulletsListEl = document.getElementById('vgHudBulletsList');
      this.laserSvg = document.getElementById('vgAiLiveLaserSvg');
      this.laserPath = document.getElementById('vgLaserPath');
      this.laserGlow = document.getElementById('vgLaserGlow');
      this.laserPhoton = document.getElementById('vgLaserPhoton');
      this.laserImpact = document.getElementById('vgLaserImpact');

      this.initModules();
      this.startLoop();
    }

    initModules() {
      PILLARS_DATA.forEach((p, idx) => {
        const modEl = document.getElementById(p.id);
        if (modEl) {
          this.modules.push({
            el: modEl,
            data: p,
            index: idx
          });

          // Click or hover immediately activates pillar
          modEl.addEventListener('mouseenter', () => {
            this.isLockedByHover = true;
            this.activatePillar(idx);
          });
          modEl.addEventListener('mouseleave', () => {
            this.isLockedByHover = false;
          });
          modEl.addEventListener('click', () => {
            this.activatePillar(idx);
          });
        }
      });
    }

    activatePillar(index) {
      this.currentIndex = index;
      const item = this.modules[index];
      if (!item) return;

      const data = item.data;

      // 1. Highlight Active Compact Module
      this.modules.forEach(m => m.el.classList.remove('vg-module-active'));
      item.el.classList.add('vg-module-active');

      // 2. Update Central HUD Card Details
      if (this.pillarTagEl) {
        this.pillarTagEl.textContent = data.pillarNum;
        this.pillarTagEl.style.color = data.accent;
        this.pillarTagEl.style.borderColor = data.accent;
      }
      if (this.pillarNameEl) {
        this.pillarNameEl.textContent = data.name;
        this.pillarNameEl.style.color = data.accent;
      }
      if (this.metricValEl) {
        this.metricValEl.textContent = data.metric;
        this.metricValEl.style.color = data.accent;
        this.metricValEl.style.textShadow = '0 0 10px rgba(' + data.accentRgb + ', 0.6)';
      }
      if (this.bandwidthEl) this.bandwidthEl.textContent = data.bandwidth;
      if (this.latencyEl) this.latencyEl.textContent = data.latency;

      // 3. Render the 3 Detailed Bullet Points with smooth micro-animation
      if (this.bulletsListEl) {
        this.bulletsListEl.style.opacity = '0';
        this.bulletsListEl.style.transform = 'translateY(4px)';

        setTimeout(() => {
          this.bulletsListEl.innerHTML = data.bullets.map(b => 
            '<div class="vg-hud-bullet-item">' +
              '<i class="fa-solid fa-circle-check" style="color: ' + data.accent + '"></i>' +
              '<span>' + b + '</span>' +
            '</div>'
          ).join('');

          this.bulletsListEl.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
          this.bulletsListEl.style.opacity = '1';
          this.bulletsListEl.style.transform = 'translateY(0)';
        }, 120);
      }

      // 4. Fire Live Laser Connector to the target module
      this.updateLiveLaser(item.el, data);

      // 5. Update 3D Canvas Octahedron
      if (this.core) {
        this.core.setActivePillar(index, data);
      }
    }

    updateLiveLaser(targetModEl, data) {
      if (!this.laserSvg || !this.laserPath || window.innerWidth < 992) {
        if (this.laserSvg) this.laserSvg.style.display = 'none';
        return;
      }
      this.laserSvg.style.display = 'block';

      const hubRect = this.hub.getBoundingClientRect();
      const canvas = document.getElementById('vgAiNeuralCoreCanvas');
      if (!canvas) return;

      const canvasRect = canvas.getBoundingClientRect();
      const modRect = targetModEl.getBoundingClientRect();

      // Source = Center of 3D Glass Octahedron
      const srcX = canvasRect.left - hubRect.left + canvasRect.width / 2;
      const srcY = canvasRect.top - hubRect.top + canvasRect.height / 2 - 15;

      // Target = Laser port of the module
      const isLeft = this.currentIndex < 3;
      const targetX = isLeft 
        ? (modRect.right - hubRect.left - 6) 
        : (modRect.left - hubRect.left + 6);
      const targetY = modRect.top - hubRect.top + modRect.height / 2;

      // Smooth curved laser pathway
      const midX = (srcX + targetX) / 2;
      const d = `M ${srcX} ${srcY} Q ${midX} ${srcY} ${targetX} ${targetY}`;

      this.laserPath.setAttribute('d', d);
      this.laserPath.setAttribute('stroke', data.accent);

      if (this.laserGlow) {
        this.laserGlow.setAttribute('d', d);
        this.laserGlow.setAttribute('stroke', data.accent);
      }

      // Position impact pinpoint
      if (this.laserImpact) {
        this.laserImpact.setAttribute('cx', targetX);
        this.laserImpact.setAttribute('cy', targetY);
        this.laserImpact.setAttribute('fill', data.accent);
      }

      // Re-trigger laser beam pulse animation
      this.laserPath.classList.remove('laser-flash');
      void this.laserPath.offsetWidth;
      this.laserPath.classList.add('laser-flash');
    }

    startLoop() {
      this.activatePillar(0);

      this.timer = setInterval(() => {
        if (!this.isLockedByHover) {
          const nextIndex = (this.currentIndex + 1) % this.modules.length;
          this.activatePillar(nextIndex);
        }
      }, this.cycleDuration);
    }

    destroy() {
      if (this.timer) clearInterval(this.timer);
    }
  }

  /* --------------------------------------------------------------------------
   * Initialization on DOM Ready
   * -------------------------------------------------------------------------- */
  
  /* --------------------------------------------------------------------------
   * 3D Parallax Card Tilt & Depth with Spotlight Border
   * -------------------------------------------------------------------------- */
  function initServiceCardParallax3D() {
    const cards = document.querySelectorAll('.vg-service-card-new');
    cards.forEach((card) => {
      let rect = null;

      function updateRect() {
        rect = card.getBoundingClientRect();
      }

      function handleMouseMove(e) {
        if (!rect) updateRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        // Set spotlight coordinates for Cyan/Violet gradient border
        card.style.setProperty('--spotlight-x', x + 'px');
        card.style.setProperty('--spotlight-y', y + 'px');

        // Compute 3D tilt angles based on cursor offset
        const normX = (x / rect.width) * 2 - 1;
        const normY = (y / rect.height) * 2 - 1;

        const maxTilt = 10;
        const tiltX = -normY * maxTilt;
        const tiltY = normX * maxTilt;

        card.style.transform = 'perspective(1000px) rotateX(' + tiltX.toFixed(2) + 'deg) rotateY(' + tiltY.toFixed(2) + 'deg) translateY(-8px)';
      }

      function handleMouseLeave() {
        rect = null;
        card.style.transition = 'transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease, box-shadow 0.3s ease';
        card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)';
        setTimeout(() => {
          card.style.transition = 'transform 0.15s ease-out, border-color 0.3s ease, box-shadow 0.3s ease';
        }, 450);
      }

      card.addEventListener('mouseenter', updateRect, { passive: true });
      card.addEventListener('mousemove', handleMouseMove, { passive: true });
      card.addEventListener('mouseleave', handleMouseLeave, { passive: true });
    });
  }

  function initExecutiveNeuralCore() {
    initServiceCardParallax3D();

    const canvas = document.getElementById('vgAiNeuralCoreCanvas');
    const hub = document.querySelector('.vg-ai-neural-hub');
    if (!canvas || !hub) return;

    // 1. Initialize 3D Glass Octahedron
    const core = new GlassOctahedronCore(canvas);

    // 2. Initialize Synchronization Controller
    const manager = new ExecutiveNeuralHubManager(hub, core);

    // 3. Laser Resize Handler
    window.addEventListener('resize', () => {
      setTimeout(() => {
        if (manager.modules[manager.currentIndex]) {
          manager.updateLiveLaser(
            manager.modules[manager.currentIndex].el,
            manager.modules[manager.currentIndex].data
          );
        }
      }, 100);
    }, { passive: true });

    // 4. 60FPS Animation Loop with Intersection Observer
    let isVisible = true;
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        isVisible = entry.isIntersecting;
      });
    }, { threshold: 0.05 });
    observer.observe(hub);

    function animate(time) {
      if (isVisible) {
        core.render(time);
      }
      requestAnimationFrame(animate);
    }
    requestAnimationFrame(animate);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initExecutiveNeuralCore);
  } else {
    initExecutiveNeuralCore();
  }
})();
