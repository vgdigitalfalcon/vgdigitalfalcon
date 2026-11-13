/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D ISOMETRIC WIREFRAME ARCHITECTURE & CODE PARTICLE MESH
 * File: assets/js/webdev-hero-canvas.js
 * 
 * Animation Concept:
 * An interactive, lightweight 3D background animation exclusively inside the
 * Hero section of the Website Development page (scoped to position: absolute;
 * inset: 0; z-index: 0; inside .vg-webdev-hero).
 * 
 * Features:
 * 1. 3D Isometric Wireframe Architecture:
 *    - Floating translucent 3D geometric blocks, wireframe cubes, and multi-tiered
 *      architectural stack layers representing full-stack engineering, cloud scalability,
 *      and clean code structure.
 *    - Translucent glass face fills with crisp glowing wireframe edges.
 * 2. Glowing Data Highway Lines & Transmission Pulses:
 *    - Dynamic pipeline pulses traveling between architectural components.
 * 3. Ambient 3D Code Particle Mesh:
 *    - Floating syntax tokens ('</>', '{ }', '=>', '01', 'API', 'DB') & telemetry sparks.
 * 4. Color Palette:
 *    - Electric Cyan (#38BDF8) & Cyber Indigo/Violet (#8B5CF6) with low opacity (35%–50%)
 *      ensuring foreground text and lead forms stay 100% sharp, distinct, and readable.
 * 5. Interactivity & 60fps Optimization:
 *    - Continuous slow isometric rotation + smooth mouse parallax with lerp.
 *    - Automatic pause on scroll-out (IntersectionObserver) & tab switch.
 *    - Reduced particle density on mobile devices.
 * ============================================================================
 */

(function () {
  'use strict';

  class WebDevIsometricArchitectureMesh {
    constructor(canvas) {
      this.canvas = canvas;
      this.ctx = canvas.getContext('2d', { alpha: true });
      if (!this.ctx) return;

      this.hero = canvas.closest('.vg-webdev-hero') || canvas.parentElement;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);

      // Color Palette: Electric Cyan & Cyber Indigo/Violet (Low Opacity Guard)
      this.colors = {
        cyan: { r: 56, g: 189, b: 248 },       // #38BDF8 (Frontend / Cloud)
        indigo: { r: 139, g: 92, b: 246 },     // #8B5CF6 (Backend / Architecture)
        violet: { r: 168, g: 85, b: 247 },     // #A855F7 (API / Full-stack)
        white: { r: 241, g: 245, b: 249 }      // Crisp highlight
      };

      // Camera & 3D Projection Settings
      this.fov = 680;
      this.basePitch = 0.52; // ~30 degrees isometric tilt
      this.yaw = 0.35;       // Current continuous rotation angle
      this.rotSpeed = 0.0018; // Slow ambient rotation

      // Mouse Parallax (interpolated)
      this.mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };
      this.camRot = { x: 0, y: 0, targetX: 0, targetY: 0 };

      // State
      this.width = 0;
      this.height = 0;
      this.time = 0;
      this.animId = null;
      this.isPaused = false;
      this.isIntersecting = true;
      this.prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      // Scene Entities
      this.blocks = [];
      this.layers = [];
      this.dataLines = [];
      this.pulses = [];
      this.codeParticles = [];

      this.init();
    }

    init() {
      this.resize();
      this.buildArchitecture();
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

      this.ctx.setTransform(1, 0, 0, 1, 0, 0);
      this.ctx.scale(this.dpr, this.dpr);

      // Re-build architecture tailored to screen width
      this.buildArchitecture();
    }

    buildArchitecture() {
      this.blocks = [];
      this.layers = [];
      this.dataLines = [];
      this.pulses = [];
      this.codeParticles = [];

      const isMobile = this.width < 768;
      const isTablet = this.width < 1100;

      // Dynamic scale factor based on container width
      const scaleFactor = isMobile ? 0.65 : (isTablet ? 0.85 : 1.05);

      // ----------------------------------------------------------------------
      // 1. Multi-Tiered Architecture Stack Platforms (Horizontal Wireframe Plates)
      // Representing: Presentation UI Layer, API Microservices Layer, DB/Cloud Layer
      // ----------------------------------------------------------------------
      const layerConfigs = [
        { y: -130 * scaleFactor, size: 280 * scaleFactor, color: this.colors.cyan, name: 'UI / CLIENT LAYER' },
        { y: 0, size: 340 * scaleFactor, color: this.colors.indigo, name: 'API / MICROSERVICES' },
        { y: 130 * scaleFactor, size: 380 * scaleFactor, color: this.colors.violet, name: 'DATA & CLOUD INFRA' }
      ];

      layerConfigs.forEach((cfg, idx) => {
        this.layers.push({
          y: cfg.y,
          size: cfg.size,
          half: cfg.size / 2,
          color: cfg.color,
          gridDivs: isMobile ? 3 : 4,
          bobOffset: idx * 1.4,
          bobSpeed: 0.02 + idx * 0.005,
          name: cfg.name
        });
      });

      // ----------------------------------------------------------------------
      // 2. 3D Isometric Geometric Wireframe Blocks (Modular Microservices)
      // ----------------------------------------------------------------------
      // Primary architecture cluster blocks
      const blockDefs = [
        // Central Cloud Core
        { x: 0, y: 0, z: 0, w: 90, h: 55, d: 90, color: this.colors.cyan, hasInner: true },
        
        // Frontend / Edge Blocks (Upper Tier)
        { x: -160, y: -110, z: -70, w: 75, h: 42, d: 75, color: this.colors.cyan, hasInner: false },
        { x: 170, y: -95, z: 80, w: 65, h: 48, d: 65, color: this.colors.indigo, hasInner: false },
        { x: -60, y: -160, z: 120, w: 55, h: 36, d: 55, color: this.colors.violet, hasInner: false },

        // Microservice & Gateway Modules (Middle Tier)
        { x: -210, y: 20, z: 110, w: 80, h: 50, d: 80, color: this.colors.indigo, hasInner: true },
        { x: 220, y: 15, z: -100, w: 85, h: 52, d: 85, color: this.colors.cyan, hasInner: false },
        { x: 130, y: 40, z: 160, w: 70, h: 44, d: 70, color: this.colors.violet, hasInner: false },

        // Database & Scalability Blocks (Lower Tier)
        { x: -140, y: 125, z: -130, w: 95, h: 45, d: 95, color: this.colors.indigo, hasInner: false },
        { x: 160, y: 140, z: -40, w: 85, h: 48, d: 85, color: this.colors.cyan, hasInner: false },
        { x: 0, y: 160, z: 150, w: 100, h: 42, d: 100, color: this.colors.violet, hasInner: true }
      ];

      // Limit block count on mobile for max frame rate
      const activeBlockDefs = isMobile ? blockDefs.slice(0, 5) : blockDefs;

      activeBlockDefs.forEach((def, i) => {
        const w = def.w * scaleFactor;
        const h = def.h * scaleFactor;
        const d = def.d * scaleFactor;

        this.blocks.push({
          id: i,
          baseX: def.x * scaleFactor,
          baseY: def.y * scaleFactor,
          baseZ: def.z * scaleFactor,
          x: def.x * scaleFactor,
          y: def.y * scaleFactor,
          z: def.z * scaleFactor,
          w: w,
          h: h,
          d: d,
          color: def.color,
          hasInner: def.hasInner,
          floatPhase: i * 0.75,
          floatSpeed: 0.022 + (i % 3) * 0.007,
          rotInner: 0,
          rotInnerSpeed: 0.015
        });
      });

      // ----------------------------------------------------------------------
      // 3. Connective Data Highway Lines (Pillars & Cross-bus Data Pipelines)
      // ----------------------------------------------------------------------
      const numBlocks = this.blocks.length;
      for (let i = 0; i < numBlocks; i++) {
        for (let j = i + 1; j < numBlocks; j++) {
          const b1 = this.blocks[i];
          const b2 = this.blocks[j];
          const dx = b1.baseX - b2.baseX;
          const dy = b1.baseY - b2.baseY;
          const dz = b1.baseZ - b2.baseZ;
          const dist = Math.sqrt(dx * dx + dy * dy + dz * dz);

          // Connect blocks within reasonable architecture distance
          const maxDist = isMobile ? 220 * scaleFactor : 290 * scaleFactor;
          if (dist < maxDist) {
            this.dataLines.push({
              source: i,
              target: j,
              color: i % 2 === 0 ? this.colors.cyan : this.colors.indigo,
              alpha: Math.min(0.38, (1 - dist / maxDist) * 0.45)
            });
          }
        }
      }

      // Add 4-6 active transmission pulses traveling along data lines
      const pulseCount = isMobile ? 4 : 7;
      for (let p = 0; p < pulseCount; p++) {
        const lineIdx = p % Math.max(1, this.dataLines.length);
        this.pulses.push({
          lineIdx: lineIdx,
          progress: Math.random(),
          speed: 0.008 + Math.random() * 0.014,
          color: p % 2 === 0 ? this.colors.cyan : this.colors.white
        });
      }

      // ----------------------------------------------------------------------
      // 4. Ambient 3D Code Particle Mesh (Syntax Tokens & Telemetry Sparks)
      // ----------------------------------------------------------------------
      const codeTokens = ['{ }', '</>', '01', '=>', 'API', 'const', 'DB', 'async', 'git', '::', '[]'];
      const particleCount = isMobile ? 24 : 48;
      const spread = (Math.max(this.width, 900) * 0.65) * scaleFactor;

      for (let k = 0; k < particleCount; k++) {
        const isToken = k % 3 === 0;
        this.codeParticles.push({
          x: (Math.random() - 0.5) * spread * 2,
          y: (Math.random() - 0.5) * 600 * scaleFactor,
          z: (Math.random() - 0.5) * 550 * scaleFactor,
          vx: (Math.random() - 0.5) * 0.28,
          vy: (Math.random() - 0.5) * 0.24,
          vz: (Math.random() - 0.5) * 0.22,
          token: isToken ? codeTokens[k % codeTokens.length] : null,
          color: k % 2 === 0 ? this.colors.cyan : this.colors.indigo,
          size: isToken ? 10 : 2.2,
          pulse: Math.random() * Math.PI * 2,
          pulseSpeed: 0.02 + Math.random() * 0.03,
          alpha: isToken ? 0.38 : 0.45
        });
      }
    }

    bindEvents() {
      // Debounced smooth resize
      let resizeTimer = null;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => this.resize(), 120);
      }, { passive: true });

      // Subtle mouse parallax exclusively bound to hero section
      if (this.hero) {
        this.hero.addEventListener('mousemove', (e) => {
          const rect = this.hero.getBoundingClientRect();
          const nx = ((e.clientX - rect.left) / this.width - 0.5) * 2;
          const ny = ((e.clientY - rect.top) / this.height - 0.5) * 2;
          this.camRot.targetY = nx * 0.26;
          this.camRot.targetX = -ny * 0.18;
        }, { passive: true });

        this.hero.addEventListener('mouseleave', () => {
          this.camRot.targetX = 0;
          this.camRot.targetY = 0;
        }, { passive: true });
      }
    }

    setupObserver() {
      // Pause 60fps render loop when hero is scrolled off-screen
      if ('IntersectionObserver' in window && this.hero) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            this.isIntersecting = entry.isIntersecting;
            if (entry.isIntersecting) {
              if (!this.prefersReducedMotion && !document.hidden) {
                this.start();
              }
            } else {
              this.stop();
            }
          });
        }, { threshold: 0.04 });
        observer.observe(this.hero);
      }

      // Browser tab visibility auto-pause
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          this.stop();
        } else if (this.isIntersecting && !this.prefersReducedMotion) {
          this.start();
        }
      });

      // Reduced motion media query listener
      if (window.matchMedia) {
        const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (motionQuery.addEventListener) {
          motionQuery.addEventListener('change', (e) => {
            this.prefersReducedMotion = e.matches;
            if (this.prefersReducedMotion) {
              this.stop();
              this.update();
              this.render();
            } else {
              this.start();
            }
          });
        }
      }
    }

    // ------------------------------------------------------------------------
    // 3D Isometric Projection Helper
    // Projects 3D world coords (x,y,z) to 2D screen space (px, py, scale)
    // ------------------------------------------------------------------------
    project3D(x, y, z, totalPitch, totalYaw, cx, cy) {
      // 1. Yaw rotation (around Y axis)
      const cosY = Math.cos(totalYaw);
      const sinY = Math.sin(totalYaw);
      const x1 = x * cosY + z * sinY;
      const z1 = -x * sinY + z * cosY;

      // 2. Pitch rotation (around X axis)
      const cosP = Math.cos(totalPitch);
      const sinP = Math.sin(totalPitch);
      const y2 = y * cosP - z1 * sinP;
      const z2 = y * sinP + z1 * cosP;

      // 3. Isometric-perspective camera projection
      const fov = this.fov;
      const depth = fov + z2;
      if (depth < 15) return null;

      const scale = fov / depth;
      return {
        x: cx + x1 * scale,
        y: cy + y2 * scale,
        z: z2,
        scale: scale
      };
    }

    update() {
      this.time += 0.02;

      // Continuous ambient rotation + smooth mouse parallax interpolation
      this.yaw += this.rotSpeed;
      this.camRot.x += (this.camRot.targetX - this.camRot.x) * 0.05;
      this.camRot.y += (this.camRot.targetY - this.camRot.y) * 0.05;

      // Floating bobbing motion for 3D architecture blocks
      for (let i = 0; i < this.blocks.length; i++) {
        const b = this.blocks[i];
        b.y = b.baseY + Math.sin(this.time * b.floatSpeed * 30 + b.floatPhase) * 12;
        if (b.hasInner) {
          b.rotInner += b.rotInnerSpeed;
        }
      }

      // Transmission pulse progress
      for (let p = 0; p < this.pulses.length; p++) {
        const pulse = this.pulses[p];
        pulse.progress += pulse.speed;
        if (pulse.progress >= 1) {
          pulse.progress = 0;
          if (this.dataLines.length > 0) {
            pulse.lineIdx = Math.floor(Math.random() * this.dataLines.length);
          }
        }
      }

      // Code particle movement & boundary wrapping
      const spreadX = this.width * 0.7;
      const spreadY = 380;
      const spreadZ = 340;

      for (let k = 0; k < this.codeParticles.length; k++) {
        const cp = this.codeParticles[k];
        cp.x += cp.vx;
        cp.y += cp.vy;
        cp.z += cp.vz;
        cp.pulse += cp.pulseSpeed;

        if (cp.x < -spreadX) cp.x = spreadX;
        if (cp.x > spreadX) cp.x = -spreadX;
        if (cp.y < -spreadY) cp.y = spreadY;
        if (cp.y > spreadY) cp.y = -spreadY;
        if (cp.z < -spreadZ) cp.z = spreadZ;
        if (cp.z > spreadZ) cp.z = -spreadZ;
      }
    }

    render() {
      const ctx = this.ctx;
      ctx.clearRect(0, 0, this.width, this.height);

      const cx = this.width / 2;
      const cy = this.height / 2;
      const totalPitch = this.basePitch + this.camRot.x;
      const totalYaw = this.yaw + this.camRot.y;

      // ----------------------------------------------------------------------
      // 1. Draw Multi-Tiered Architecture Stack Platforms (Plates)
      // ----------------------------------------------------------------------
      ctx.save();
      for (let l = 0; l < this.layers.length; l++) {
        const layer = this.layers[l];
        const ly = layer.y + Math.sin(this.time * layer.bobSpeed * 30 + layer.bobOffset) * 6;
        const half = layer.half;
        const divs = layer.gridDivs;
        const step = layer.size / divs;

        const c = layer.color;
        ctx.strokeStyle = `rgba(${c.r}, ${c.g}, ${c.b}, 0.16)`;
        ctx.lineWidth = 1;

        // Draw grid lines on isometric plane
        for (let i = 0; i <= divs; i++) {
          const offset = -half + i * step;

          // Lines along Z
          const p1 = this.project3D(offset, ly, -half, totalPitch, totalYaw, cx, cy);
          const p2 = this.project3D(offset, ly, half, totalPitch, totalYaw, cx, cy);
          if (p1 && p2) {
            ctx.beginPath();
            ctx.moveTo(p1.x, p1.y);
            ctx.lineTo(p2.x, p2.y);
            ctx.stroke();
          }

          // Lines along X
          const p3 = this.project3D(-half, ly, offset, totalPitch, totalYaw, cx, cy);
          const p4 = this.project3D(half, ly, offset, totalPitch, totalYaw, cx, cy);
          if (p3 && p4) {
            ctx.beginPath();
            ctx.moveTo(p3.x, p3.y);
            ctx.lineTo(p4.x, p4.y);
            ctx.stroke();
          }
        }

        // Perimeter glowing border
        const c1 = this.project3D(-half, ly, -half, totalPitch, totalYaw, cx, cy);
        const c2 = this.project3D(half, ly, -half, totalPitch, totalYaw, cx, cy);
        const c3 = this.project3D(half, ly, half, totalPitch, totalYaw, cx, cy);
        const c4 = this.project3D(-half, ly, half, totalPitch, totalYaw, cx, cy);

        if (c1 && c2 && c3 && c4) {
          ctx.beginPath();
          ctx.moveTo(c1.x, c1.y);
          ctx.lineTo(c2.x, c2.y);
          ctx.lineTo(c3.x, c3.y);
          ctx.lineTo(c4.x, c4.y);
          ctx.closePath();
          ctx.fillStyle = `rgba(${c.r}, ${c.g}, ${c.b}, 0.025)`;
          ctx.fill();
          ctx.strokeStyle = `rgba(${c.r}, ${c.g}, ${c.b}, 0.28)`;
          ctx.lineWidth = 1.2;
          ctx.stroke();
        }
      }
      ctx.restore();

      // ----------------------------------------------------------------------
      // 2. Draw Data Highway Connecting Lines
      // ----------------------------------------------------------------------
      ctx.save();
      for (let j = 0; j < this.dataLines.length; j++) {
        const line = this.dataLines[j];
        const b1 = this.blocks[line.source];
        const b2 = this.blocks[line.target];
        if (!b1 || !b2) continue;

        const p1 = this.project3D(b1.x, b1.y, b1.z, totalPitch, totalYaw, cx, cy);
        const p2 = this.project3D(b2.x, b2.y, b2.z, totalPitch, totalYaw, cx, cy);
        if (!p1 || !p2) continue;

        const c = line.color;
        ctx.strokeStyle = `rgba(${c.r}, ${c.g}, ${c.b}, ${line.alpha})`;
        ctx.lineWidth = 1.1;
        ctx.beginPath();
        ctx.moveTo(p1.x, p1.y);
        ctx.lineTo(p2.x, p2.y);
        ctx.stroke();
      }
      ctx.restore();

      // ----------------------------------------------------------------------
      // 3. Draw Transmission Pulses (Data Packets)
      // ----------------------------------------------------------------------
      ctx.save();
      for (let p = 0; p < this.pulses.length; p++) {
        const pulse = this.pulses[p];
        const line = this.dataLines[pulse.lineIdx];
        if (!line) continue;

        const b1 = this.blocks[line.source];
        const b2 = this.blocks[line.target];
        if (!b1 || !b2) continue;

        const curX = b1.x + (b2.x - b1.x) * pulse.progress;
        const curY = b1.y + (b2.y - b1.y) * pulse.progress;
        const curZ = b1.z + (b2.z - b1.z) * pulse.progress;

        const pt = this.project3D(curX, curY, curZ, totalPitch, totalYaw, cx, cy);
        if (!pt) continue;

        const c = pulse.color;
        const r = 2.4 * pt.scale;

        ctx.fillStyle = `rgba(${c.r}, ${c.g}, ${c.b}, 0.85)`;
        ctx.shadowColor = '#38bdf8';
        ctx.shadowBlur = 8;
        ctx.beginPath();
        ctx.arc(pt.x, pt.y, r, 0, Math.PI * 2);
        ctx.fill();
        ctx.shadowBlur = 0;
      }
      ctx.restore();

      // ----------------------------------------------------------------------
      // 4. Draw 3D Isometric Wireframe Architecture Blocks (Sort by depth)
      // ----------------------------------------------------------------------
      const renderableBlocks = [];
      for (let b = 0; b < this.blocks.length; b++) {
        const block = this.blocks[b];
        const centerProj = this.project3D(block.x, block.y, block.z, totalPitch, totalYaw, cx, cy);
        if (centerProj) {
          renderableBlocks.push({ block, centerProj });
        }
      }

      // Painter's algorithm: sort blocks back-to-front
      renderableBlocks.sort((a, b) => b.centerProj.z - a.centerProj.z);

      for (let rb = 0; rb < renderableBlocks.length; rb++) {
        const { block, centerProj } = renderableBlocks[rb];
        this.renderWireframeBox(block, totalPitch, totalYaw, cx, cy);
      }

      // ----------------------------------------------------------------------
      // 5. Draw 3D Code Particle Mesh (Syntax Tokens & Telemetry Sparks)
      // ----------------------------------------------------------------------
      ctx.save();
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';

      for (let k = 0; k < this.codeParticles.length; k++) {
        const cp = this.codeParticles[k];
        const pt = this.project3D(cp.x, cp.y, cp.z, totalPitch, totalYaw, cx, cy);
        if (!pt) continue;

        const c = cp.color;
        const alpha = (0.28 + Math.sin(cp.pulse) * 0.12) * Math.min(1.2, pt.scale);

        if (cp.token) {
          // Render code syntax token
          const fontSize = Math.max(9, Math.floor(cp.size * pt.scale * 1.1));
          ctx.font = `600 ${fontSize}px 'JetBrains Mono', Consolas, Monaco, monospace`;
          ctx.fillStyle = `rgba(${c.r}, ${c.g}, ${c.b}, ${Math.min(0.48, alpha)})`;
          ctx.fillText(cp.token, pt.x, pt.y);
        } else {
          // Render spark particle
          const r = Math.max(1.2, cp.size * pt.scale);
          ctx.fillStyle = `rgba(${c.r}, ${c.g}, ${c.b}, ${Math.min(0.5, alpha)})`;
          ctx.beginPath();
          ctx.arc(pt.x, pt.y, r, 0, Math.PI * 2);
          ctx.fill();
        }
      }
      ctx.restore();
    }

    // ------------------------------------------------------------------------
    // Wireframe Box Renderer with Translucent Glass Faces & Glowing Edges
    // ------------------------------------------------------------------------
    renderWireframeBox(block, totalPitch, totalYaw, cx, cy) {
      const ctx = this.ctx;
      const hw = block.w / 2;
      const hh = block.h / 2;
      const hd = block.d / 2;

      // 8 Vertices of the 3D cuboid relative to block center (x, y, z)
      const rawVerts = [
        { x: block.x - hw, y: block.y - hh, z: block.z - hd }, // 0: Top-left-back
        { x: block.x + hw, y: block.y - hh, z: block.z - hd }, // 1: Top-right-back
        { x: block.x + hw, y: block.y - hh, z: block.z + hd }, // 2: Top-right-front
        { x: block.x - hw, y: block.y - hh, z: block.z + hd }, // 3: Top-left-front
        { x: block.x - hw, y: block.y + hh, z: block.z - hd }, // 4: Bottom-left-back
        { x: block.x + hw, y: block.y + hh, z: block.z - hd }, // 5: Bottom-right-back
        { x: block.x + hw, y: block.y + hh, z: block.z + hd }, // 6: Bottom-right-front
        { x: block.x - hw, y: block.y + hh, z: block.z + hd }  // 7: Bottom-left-front
      ];

      const projVerts = rawVerts.map(v => this.project3D(v.x, v.y, v.z, totalPitch, totalYaw, cx, cy));
      for (let i = 0; i < 8; i++) {
        if (!projVerts[i]) return; // Culled or behind camera
      }

      const c = block.color;

      // 6 Faces defined by vertex indices (Top, Bottom, Front, Back, Left, Right)
      const faces = [
        [0, 1, 2, 3], // Top
        [4, 5, 6, 7], // Bottom
        [3, 2, 6, 7], // Front
        [0, 1, 5, 4], // Back
        [0, 3, 7, 4], // Left
        [1, 2, 6, 5]  // Right
      ];

      // 1. Draw Translucent Glass Face Fills
      ctx.save();
      for (let f = 0; f < faces.length; f++) {
        const idx = faces[f];
        ctx.beginPath();
        ctx.moveTo(projVerts[idx[0]].x, projVerts[idx[0]].y);
        ctx.lineTo(projVerts[idx[1]].x, projVerts[idx[1]].y);
        ctx.lineTo(projVerts[idx[2]].x, projVerts[idx[2]].y);
        ctx.lineTo(projVerts[idx[3]].x, projVerts[idx[3]].y);
        ctx.closePath();

        // Shading variance for top face vs side faces (isometric lighting effect)
        const faceAlpha = f === 0 ? 0.075 : (f === 2 || f === 5 ? 0.045 : 0.03);
        ctx.fillStyle = `rgba(${c.r}, ${c.g}, ${c.b}, ${faceAlpha})`;
        ctx.fill();
      }
      ctx.restore();

      // 2. Draw Crisp Glowing 3D Wireframe Edges (12 Edges)
      const edges = [
        [0, 1], [1, 2], [2, 3], [3, 0], // Top rim
        [4, 5], [5, 6], [6, 7], [7, 4], // Bottom rim
        [0, 4], [1, 5], [2, 6], [3, 7]  // Vertical pillars
      ];

      ctx.save();
      ctx.strokeStyle = `rgba(${c.r}, ${c.g}, ${c.b}, 0.42)`;
      ctx.lineWidth = 1.25;
      ctx.beginPath();

      for (let e = 0; e < edges.length; e++) {
        const vA = projVerts[edges[e][0]];
        const vB = projVerts[edges[e][1]];
        ctx.moveTo(vA.x, vA.y);
        ctx.lineTo(vB.x, vB.y);
      }
      ctx.stroke();

      // 3. Highlight Corner Micro-Nodes (Hardware LED styling)
      ctx.fillStyle = `rgba(${this.colors.white.r}, ${this.colors.white.g}, ${this.colors.white.b}, 0.75)`;
      for (let vi = 0; vi < 8; vi++) {
        const vp = projVerts[vi];
        const dotR = 1.6 * vp.scale;
        ctx.beginPath();
        ctx.arc(vp.x, vp.y, dotR, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();

      // 4. Render Nested Inner Core Cube (For microservice container visualization)
      if (block.hasInner) {
        this.renderInnerCore(block, totalPitch, totalYaw, cx, cy);
      }
    }

    // ------------------------------------------------------------------------
    // Nested Container Microservice Core (Rotating inner wireframe cube)
    // ------------------------------------------------------------------------
    renderInnerCore(block, totalPitch, totalYaw, cx, cy) {
      const ctx = this.ctx;
      const innerW = block.w * 0.42;
      const innerH = block.h * 0.42;
      const innerD = block.d * 0.42;
      const hw = innerW / 2;
      const hh = innerH / 2;
      const hd = innerD / 2;

      // Rotate inner core independently
      const rot = block.rotInner;
      const cosR = Math.cos(rot);
      const sinR = Math.sin(rot);

      const rawInner = [
        [-hw, -hh, -hd], [hw, -hh, -hd], [hw, -hh, hd], [-hw, -hh, hd],
        [-hw, hh, -hd],  [hw, hh, -hd],  [hw, hh, hd],  [-hw, hh, hd]
      ];

      const projInner = [];
      for (let i = 0; i < 8; i++) {
        const p = rawInner[i];
        // Rotate in local XZ
        const rx = p[0] * cosR + p[2] * sinR;
        const rz = -p[0] * sinR + p[2] * cosR;
        const pt = this.project3D(block.x + rx, block.y + p[1], block.z + rz, totalPitch, totalYaw, cx, cy);
        if (!pt) return;
        projInner.push(pt);
      }

      ctx.save();
      ctx.strokeStyle = `rgba(56, 189, 248, 0.48)`;
      ctx.lineWidth = 1;
      ctx.beginPath();

      const innerEdges = [
        [0, 1], [1, 2], [2, 3], [3, 0],
        [4, 5], [5, 6], [6, 7], [7, 4],
        [0, 4], [1, 5], [2, 6], [3, 7]
      ];

      for (let e = 0; e < innerEdges.length; e++) {
        const a = projInner[innerEdges[e][0]];
        const b = projInner[innerEdges[e][1]];
        ctx.moveTo(a.x, a.y);
        ctx.lineTo(b.x, b.y);
      }
      ctx.stroke();

      // Glowing central micro-spark
      const center = this.project3D(block.x, block.y, block.z, totalPitch, totalYaw, cx, cy);
      if (center) {
        ctx.fillStyle = 'rgba(255, 255, 255, 0.85)';
        ctx.shadowColor = '#38bdf8';
        ctx.shadowBlur = 6;
        ctx.beginPath();
        ctx.arc(center.x, center.y, 2 * center.scale, 0, Math.PI * 2);
        ctx.fill();
        ctx.shadowBlur = 0;
      }
      ctx.restore();
    }

    start() {
      if (this.animId) cancelAnimationFrame(this.animId);
      if (this.prefersReducedMotion) {
        this.update();
        this.render();
        return;
      }
      this.isPaused = false;
      const loop = () => {
        if (!this.isPaused) {
          this.update();
          this.render();
          this.animId = requestAnimationFrame(loop);
        }
      };
      this.animId = requestAnimationFrame(loop);
    }

    stop() {
      this.isPaused = true;
      if (this.animId) {
        cancelAnimationFrame(this.animId);
        this.animId = null;
      }
    }
  }

  // Auto-initialize when DOM is ready
  function initWebDevHeroCanvas() {
    const canvas = document.getElementById('vg-webdev-hero-canvas');
    if (!canvas) return;

    if (!canvas._webDevArchitectureMeshInstance) {
      canvas._webDevArchitectureMeshInstance = new WebDevIsometricArchitectureMesh(canvas);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWebDevHeroCanvas);
  } else {
    initWebDevHeroCanvas();
  }

  window.VG_WebDevArchitectureMesh = WebDevIsometricArchitectureMesh;
})();
