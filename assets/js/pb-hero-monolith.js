/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D BEVELED OBSIDIAN CRYSTAL GLASS MONOLITH
 * File: assets/js/pb-hero-monolith.js
 * 
 * Luxury Beveled Crystal / Obsidian Glass Sculpture:
 * - Single sleek vertical 3D frosted-obsidian crystal monolith / prism
 * - Internal 3D glowing neon energy core / spine (Electric Cyan #00F0FF to Cyber Violet #A855F7)
 * - Volumetric internal glow refracting through dark frosted glass
 * - Subtle 3D angled glass facets / architectural louvers across the front face
 * - Floating 3D Halo Crown: 3 ultra-thin tilted neon rings levitating above the top bevel
 * - Soft cyan specular reflection cast from the crown onto the top glass pillar
 * - Sharp 1px Electric Cyan & Cyber Violet beveled edge specular highlights
 * - Gliding diagonal frosted-glass specular sweep & ambient ground reflection glow
 * - Slow subtle 3D oscillation (+/- 12 deg) with silky cursor parallax
 * - Scoped strictly to position: absolute; inset: 0; z-index: 0; (pointer-events: none)
 * ============================================================================
 */

(function () {
  'use strict';

  class ObsidianCrystalMonolith {
    constructor(container) {
      this.container = container;
      this.hero = container.closest('.vg-pb-hero-section') || container.parentElement;

      this.width = 0;
      this.height = 0;
      this.time = 0;
      this.animId = null;
      this.isIntersecting = true;
      this.isTabActive = true;

      // Mouse Parallax with Damping
      this.mouse = {
        targetX: 0,
        targetY: 0,
        currentX: 0,
        currentY: 0
      };

      if (typeof THREE !== 'undefined') {
        this.initThree();
      } else {
        this.initCanvasFallback();
      }
    }

    /* =========================================================================
       1. THREE.JS LUXURY PHYSICAL CRYSTAL/OBSIDIAN IMPLEMENTATION
       ========================================================================= */
    initThree() {
      this.isThree = true;
      this.width = this.hero.clientWidth || window.innerWidth;
      this.height = this.hero.clientHeight || 750;

      // Scene & Camera
      this.scene = new THREE.Scene();
      this.camera = new THREE.PerspectiveCamera(38, this.width / this.height, 0.1, 100);
      this.camera.position.set(0, 0, 7.8);

      // WebGL Renderer
      this.renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true,
        powerPreference: 'high-performance'
      });
      this.renderer.setSize(this.width, this.height);
      this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
      this.renderer.toneMapping = THREE.ACESFilmicToneMapping;
      this.renderer.toneMappingExposure = 1.35;
      this.renderer.domElement.className = 'vg-pb-monolith-canvas';
      this.renderer.domElement.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;z-index:0;pointer-events:none;';
      this.container.appendChild(this.renderer.domElement);

      // Master Monolith Group (Contains Monolith, Core, Facets, and Crown)
      this.monolithGroup = new THREE.Group();
      this.monolithGroup.position.set(0, 0.08, 0);
      this.scene.add(this.monolithGroup);

      // 3D Beveled Monolith Outer Geometry
      const w = 1.38;
      const h = 3.65;
      const d = 0.52;
      const r = 0.08;

      const shape = new THREE.Shape();
      const x = -w / 2, y = -h / 2;
      shape.moveTo(x + r, y);
      shape.lineTo(x + w - r, y);
      shape.quadraticCurveTo(x + w, y, x + w, y + r);
      shape.lineTo(x + w, y + h - r);
      shape.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
      shape.lineTo(x + r, y + h);
      shape.quadraticCurveTo(x, y + h, x, y + h - r);
      shape.lineTo(x, y + r);
      shape.quadraticCurveTo(x, y, x + r, y);

      const extrudeSettings = {
        steps: 1,
        depth: d,
        bevelEnabled: true,
        bevelThickness: 0.075,
        bevelSize: 0.075,
        bevelOffset: 0,
        bevelSegments: 4
      };

      const geometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);
      geometry.center();

      // Physical Frosted Obsidian Crystal Glass Material
      const obsidianMaterial = new THREE.MeshPhysicalMaterial({
        color: 0x070410,          // Deep obsidian dark base
        emissive: 0x090416,       // Subtle dark violet crystal tint
        roughness: 0.12,          // Frosted polished crystal
        metalness: 0.82,
        reflectivity: 0.98,
        clearcoat: 1.0,           // Glossy clearcoat
        clearcoatRoughness: 0.08,
        transmission: 0.52,       // Allows internal glowing energy spine to shine through
        ior: 1.58,                // Crystal refraction index
        transparent: true,
        opacity: 0.92
      });

      this.monolith = new THREE.Mesh(geometry, obsidianMaterial);
      this.monolithGroup.add(this.monolith);

      // -----------------------------------------------------------------------
      // A. INTERNAL 3D GLOWING ENERGY SPINE & VOLUMETRIC CORE
      // -----------------------------------------------------------------------
      // Generate Cyan-to-Violet internal gradient texture
      const coreCanvas = document.createElement('canvas');
      coreCanvas.width = 64;
      coreCanvas.height = 512;
      const cctx = coreCanvas.getContext('2d');
      const coreGrad = cctx.createLinearGradient(0, 0, 0, 512);
      coreGrad.addColorStop(0, '#00F0FF');    // Electric Cyan at top
      coreGrad.addColorStop(0.35, '#38bdf8');
      coreGrad.addColorStop(0.65, '#c084fc');
      coreGrad.addColorStop(1, '#A855F7');    // Cyber Violet at bottom
      cctx.fillStyle = coreGrad;
      cctx.fillRect(0, 0, 64, 512);

      const coreTexture = new THREE.CanvasTexture(coreCanvas);

      // 1. Sleek Central Glowing Spine Cylinder
      const spineGeo = new THREE.CylinderGeometry(0.045, 0.045, 3.2, 16);
      const spineMat = new THREE.MeshBasicMaterial({
        map: coreTexture,
        transparent: true,
        opacity: 0.95
      });
      this.spineMesh = new THREE.Mesh(spineGeo, spineMat);
      this.spineMesh.position.set(0, 0, 0);
      this.monolithGroup.add(this.spineMesh);

      // 2. Volumetric Soft Inner Glow Capsule
      const innerGlowGeo = new THREE.CylinderGeometry(0.18, 0.18, 3.25, 16);
      const innerGlowMat = new THREE.MeshBasicMaterial({
        map: coreTexture,
        transparent: true,
        opacity: 0.28,
        blending: THREE.AdditiveBlending,
        depthWrite: false
      });
      this.innerGlowMesh = new THREE.Mesh(innerGlowGeo, innerGlowMat);
      this.innerGlowMesh.position.set(0, 0, 0);
      this.monolithGroup.add(this.innerGlowMesh);

      // -----------------------------------------------------------------------
      // B. SUBTLE 3D ANGLED GLASS FACETS / ARCHITECTURAL LOUVERS (FRONT FACE)
      // -----------------------------------------------------------------------
      // Architectural chevron / angled facet cut lines across front face
      const facetGroup = new THREE.Group();
      const facetMatCyan = new THREE.LineBasicMaterial({
        color: 0x00F0FF,
        transparent: true,
        opacity: 0.35,
        linewidth: 1
      });
      const facetMatViolet = new THREE.LineBasicMaterial({
        color: 0xA855F7,
        transparent: true,
        opacity: 0.28,
        linewidth: 1
      });

      const facetZ = d / 2 + 0.082; // Exactly on front chamfer plane
      const numFacets = 6;
      const facetSpacing = 0.48;

      for (let i = 0; i < numFacets; i++) {
        const fy = -1.2 + i * facetSpacing;
        const fPoints = [
          new THREE.Vector3(-w * 0.42, fy - 0.06, facetZ),
          new THREE.Vector3(0, fy + 0.04, facetZ + 0.012),
          new THREE.Vector3(w * 0.42, fy - 0.06, facetZ)
        ];
        const fGeo = new THREE.BufferGeometry().setFromPoints(fPoints);
        const fLine = new THREE.Line(fGeo, i % 2 === 0 ? facetMatCyan : facetMatViolet);
        facetGroup.add(fLine);
      }
      this.monolithGroup.add(facetGroup);

      // Sharp 1px Beveled Edge Rim-Light Outlines (EdgesGeometry)
      const edgesGeo = new THREE.EdgesGeometry(geometry, 18);
      const edgesMat = new THREE.LineBasicMaterial({
        color: 0x00F0FF,
        transparent: true,
        opacity: 0.75,
        linewidth: 1
      });
      this.monolithEdges = new THREE.LineSegments(edgesGeo, edgesMat);
      this.monolithGroup.add(this.monolithEdges);

      // Secondary Violet Edge Accent Lines
      const edgesMatViolet = new THREE.LineBasicMaterial({
        color: 0xA855F7,
        transparent: true,
        opacity: 0.65,
        linewidth: 1
      });
      this.monolithEdgesViolet = new THREE.LineSegments(edgesGeo, edgesMatViolet);
      this.monolithEdgesViolet.scale.set(1.002, 1.002, 1.002);
      this.monolithGroup.add(this.monolithEdgesViolet);

      // -----------------------------------------------------------------------
      // C. FLOATING 3D HALO CROWN ABOVE THE MONOLITH
      // -----------------------------------------------------------------------
      this.haloGroup = new THREE.Group();
      // Positioned directly above the top bevel of the monolith
      this.haloBaseY = h / 2 + 0.32;
      this.haloGroup.position.set(0, this.haloBaseY, 0);
      this.monolithGroup.add(this.haloGroup);

      // 1. Lower Halo Ring (Electric Cyan)
      const ring1Geo = new THREE.TorusGeometry(0.72, 0.012, 16, 64);
      const ring1Mat = new THREE.MeshBasicMaterial({
        color: 0x00F0FF,
        transparent: true,
        opacity: 0.88,
        blending: THREE.AdditiveBlending
      });
      this.halo1 = new THREE.Mesh(ring1Geo, ring1Mat);
      this.halo1.rotation.x = Math.PI * 0.42;
      this.halo1.rotation.y = 0.12;
      this.haloGroup.add(this.halo1);

      // 2. Middle Halo Ring (Cyber Violet)
      const ring2Geo = new THREE.TorusGeometry(0.54, 0.010, 16, 64);
      const ring2Mat = new THREE.MeshBasicMaterial({
        color: 0xA855F7,
        transparent: true,
        opacity: 0.82,
        blending: THREE.AdditiveBlending
      });
      this.halo2 = new THREE.Mesh(ring2Geo, ring2Mat);
      this.halo2.position.set(0, 0.14, 0);
      this.halo2.rotation.x = Math.PI * 0.38;
      this.halo2.rotation.y = -0.16;
      this.haloGroup.add(this.halo2);

      // 3. Top Halo Ring (Cyan-Violet Blend)
      const ring3Geo = new THREE.TorusGeometry(0.36, 0.008, 16, 48);
      const ring3Mat = new THREE.MeshBasicMaterial({
        color: 0x38BDF8,
        transparent: true,
        opacity: 0.75,
        blending: THREE.AdditiveBlending
      });
      this.halo3 = new THREE.Mesh(ring3Geo, ring3Mat);
      this.halo3.position.set(0, 0.26, 0);
      this.halo3.rotation.x = Math.PI * 0.45;
      this.halo3.rotation.y = 0.08;
      this.haloGroup.add(this.halo3);

      // Halo Crown PointLight (Casting soft cyan reflection onto top glass pillar)
      this.haloLight = new THREE.PointLight(0x00F0FF, 3.5, 4.5);
      this.haloLight.position.set(0, this.haloBaseY + 0.1, 0.2);
      this.monolithGroup.add(this.haloLight);

      // -----------------------------------------------------------------------
      // D. GROUND REFLECTION & FLOOR GLOW
      // -----------------------------------------------------------------------
      const groundCanvas = document.createElement('canvas');
      groundCanvas.width = 256;
      groundCanvas.height = 256;
      const gctx = groundCanvas.getContext('2d');

      const grad = gctx.createRadialGradient(128, 128, 15, 128, 128, 120);
      grad.addColorStop(0, 'rgba(0, 0, 0, 0.88)');
      grad.addColorStop(0.35, 'rgba(168, 85, 247, 0.32)'); // Cyber Violet reflection
      grad.addColorStop(0.65, 'rgba(0, 240, 255, 0.24)');  // Electric Cyan reflection
      grad.addColorStop(1, 'rgba(7, 7, 15, 0)');
      gctx.fillStyle = grad;
      gctx.fillRect(0, 0, 256, 256);

      const groundTexture = new THREE.CanvasTexture(groundCanvas);
      const groundGeo = new THREE.PlaneGeometry(4.4, 2.8);
      const groundMat = new THREE.MeshBasicMaterial({
        map: groundTexture,
        transparent: true,
        opacity: 0.85,
        depthWrite: false
      });
      const groundPlane = new THREE.Mesh(groundGeo, groundMat);
      groundPlane.rotation.x = -Math.PI / 2;
      groundPlane.position.set(0, -1.95, 0);
      this.scene.add(groundPlane);

      // Inverted Mirrored Base Mirage
      const mirageMat = new THREE.MeshPhysicalMaterial({
        color: 0x070410,
        roughness: 0.32,
        metalness: 0.8,
        transparent: true,
        opacity: 0.20
      });
      this.reflectionMonolith = new THREE.Mesh(geometry, mirageMat);
      this.reflectionMonolith.position.set(0, -3.95, 0);
      this.reflectionMonolith.scale.set(1.0, -0.92, 1.0);
      this.scene.add(this.reflectionMonolith);

      // Studio Lighting Architecture
      const ambient = new THREE.AmbientLight(0x0e081c, 1.35);
      this.scene.add(ambient);

      // Cyan Studio Rim Light
      this.cyanLight = new THREE.PointLight(0x00F0FF, 5.2, 14);
      this.cyanLight.position.set(-3.2, 1.8, 2.8);
      this.scene.add(this.cyanLight);

      // Violet Studio Rim Light
      this.violetLight = new THREE.PointLight(0xA855F7, 5.4, 14);
      this.violetLight.position.set(3.2, -1.2, 2.6);
      this.scene.add(this.violetLight);

      // Top Directional Specular Sheen (Gliding slowly diagonally)
      this.topLight = new THREE.DirectionalLight(0xffffff, 1.4);
      this.topLight.position.set(0, 4.5, 3.5);
      this.scene.add(this.topLight);

      this.bindEvents();
      this.setupObserver();
      this.startThree();
    }

    startThree() {
      const animate = () => {
        if (!this.isIntersecting || !this.isTabActive) {
          this.animId = null;
          return;
        }

        this.time += 0.016;

        this.mouse.currentX += (this.mouse.targetX - this.mouse.currentX) * 0.05;
        this.mouse.currentY += (this.mouse.targetY - this.mouse.currentY) * 0.05;

        // Subtle 3D rotation (+/- 12 degrees)
        const rotY = Math.sin(this.time * 0.45) * 0.21 + this.mouse.currentX * 0.16;
        const rotX = 0.08 + Math.cos(this.time * 0.3) * 0.02 - this.mouse.currentY * 0.12;
        const rotZ = Math.sin(this.time * 0.25) * 0.015;

        if (this.monolithGroup) {
          this.monolithGroup.rotation.set(rotX, rotY, rotZ);
        }
        if (this.reflectionMonolith) {
          this.reflectionMonolith.rotation.set(-rotX, rotY, -rotZ);
        }

        // Internal Energy Spine Breathing Pulse
        if (this.innerGlowMesh) {
          const pulse = Math.sin(this.time * 1.8) * 0.08 + 0.32;
          this.innerGlowMesh.material.opacity = pulse;
        }

        // Floating 3D Halo Crown Levitation Physics
        if (this.haloGroup) {
          // Gentle floating levitation loop
          const levitateY = Math.sin(this.time * 1.2) * 0.06;
          this.haloGroup.position.y = this.haloBaseY + levitateY;

          // Subtle ring differential rotations
          if (this.halo1) this.halo1.rotation.z += 0.0035;
          if (this.halo2) this.halo2.rotation.z -= 0.0045;
          if (this.halo3) this.halo3.rotation.z += 0.0055;
        }

        // Halo Light Soft Dynamic Sheen
        if (this.haloLight) {
          this.haloLight.intensity = 3.2 + Math.sin(this.time * 1.5) * 0.8;
        }

        // Gliding studio rim-light paths
        if (this.cyanLight) {
          this.cyanLight.position.x = -3.2 + Math.sin(this.time * 0.6) * 0.8;
          this.cyanLight.position.y = 1.8 + Math.cos(this.time * 0.5) * 0.6;
        }
        if (this.violetLight) {
          this.violetLight.position.x = 3.2 + Math.cos(this.time * 0.55) * 0.8;
          this.violetLight.position.y = -1.2 + Math.sin(this.time * 0.65) * 0.6;
        }
        if (this.topLight) {
          this.topLight.position.x = Math.sin(this.time * 0.4) * 2.2;
        }

        this.renderer.render(this.scene, this.camera);
        this.animId = requestAnimationFrame(animate);
      };

      this.animId = requestAnimationFrame(animate);
    }

    /* =========================================================================
       2. PROCEDURAL 3D CANVAS IMPLEMENTATION (ZERO DEPENDENCIES, CRYSTAL PRECISION)
       ========================================================================= */
    initCanvasFallback() {
      this.isThree = false;
      this.canvas = document.createElement('canvas');
      this.canvas.className = 'vg-pb-monolith-canvas';
      this.canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;z-index:0;pointer-events:none;';
      this.container.appendChild(this.canvas);
      this.ctx = this.canvas.getContext('2d');

      this.dpr = Math.min(window.devicePixelRatio || 1, 2);
      this.resizeCanvas();
      this.bindEvents();
      this.setupObserver();
      this.startCanvas();
    }

    resizeCanvas() {
      if (!this.canvas) return;
      this.width = this.hero.clientWidth || window.innerWidth;
      this.height = this.hero.clientHeight || 750;

      this.canvas.width = Math.floor(this.width * this.dpr);
      this.canvas.height = Math.floor(this.height * this.dpr);
      this.canvas.style.width = this.width + 'px';
      this.canvas.style.height = this.height + 'px';

      this.ctx.setTransform(1, 0, 0, 1, 0, 0);
      this.ctx.scale(this.dpr, this.dpr);
    }

    startCanvas() {
      const render = () => {
        if (!this.isIntersecting || !this.isTabActive) {
          this.animId = null;
          return;
        }

        this.time += 0.016;

        this.mouse.currentX += (this.mouse.targetX - this.mouse.currentX) * 0.05;
        this.mouse.currentY += (this.mouse.targetY - this.mouse.currentY) * 0.05;

        this.ctx.clearRect(0, 0, this.width, this.height);

        // Optimal Center Placement in open gap between headline and audit form
        const isDesktop = this.width >= 992;
        const centerX = isDesktop ? this.width * 0.50 : this.width * 0.50;
        const centerY = isDesktop ? this.height * 0.50 : this.height * 0.40;
        const scale = isDesktop ? 1.0 : (this.width < 576 ? 0.72 : 0.85);

        // Crystal Monolith Dimensions
        const mw = 108 * scale;
        const mh = 296 * scale;
        const bevel = 10 * scale;

        // Subtle 3D rotation (+/- 12 degrees)
        const rotY = Math.sin(this.time * 0.45) * 0.21 + this.mouse.currentX * 0.16; // +/- 12 deg
        const cosRot = Math.cos(rotY);
        const sinRot = Math.sin(rotY);

        const currentW = mw * Math.abs(cosRot) + 26 * Math.abs(sinRot);
        const tiltX = this.mouse.currentY * 8;

        // 1. Soft Ambient Cyan/Violet Ground Reflection Glow
        const groundY = centerY + mh * 0.52 + 10;
        const groundRadiusX = currentW * 1.6;
        const groundRadiusY = 34 * scale;

        const groundGlow = this.ctx.createRadialGradient(
          centerX, groundY, 4,
          centerX, groundY, groundRadiusX
        );
        groundGlow.addColorStop(0, 'rgba(0, 0, 0, 0.88)');
        groundGlow.addColorStop(0.32, 'rgba(168, 85, 247, 0.26)'); // Ambient Cyber Violet glow
        groundGlow.addColorStop(0.65, 'rgba(0, 240, 255, 0.19)');  // Ambient Electric Cyan glow
        groundGlow.addColorStop(1, 'rgba(7, 7, 15, 0)');

        this.ctx.fillStyle = groundGlow;
        this.ctx.beginPath();
        this.ctx.ellipse(centerX, groundY, groundRadiusX, groundRadiusY, 0, 0, Math.PI * 2);
        this.ctx.fill();

        // Faint Ground Inverted Mirage Reflection
        this.ctx.save();
        this.ctx.globalAlpha = 0.18;
        this.ctx.translate(centerX, groundY + 2);
        this.ctx.scale(1, -0.62);
        this.drawCrystalPrism(0, mh * 0.5, currentW, mh, bevel, rotY, true, scale);
        this.ctx.restore();

        // 2. Main 3D Beveled Obsidian Crystal Monolith
        this.ctx.save();
        this.ctx.translate(centerX, centerY + tiltX);
        this.drawCrystalPrism(0, 0, currentW, mh, bevel, rotY, false, scale);
        this.ctx.restore();

        // 3. Floating 3D Halo Crown Above the Monolith
        this.drawHaloCrown(centerX, centerY + tiltX - mh * 0.5, scale);

        this.animId = requestAnimationFrame(render);
      };

      this.animId = requestAnimationFrame(render);
    }

    drawCrystalPrism(x, y, w, h, b, rotY, isReflection, scale) {
      const ctx = this.ctx;
      const hw = w / 2;
      const hh = h / 2;

      // Obsidian Crystal Body Gradient with Smoky Violet Base
      const bodyGrad = ctx.createLinearGradient(x - hw, y - hh, x + hw, y + hh);
      bodyGrad.addColorStop(0, 'rgba(22, 11, 40, 0.96)');
      bodyGrad.addColorStop(0.35, 'rgba(13, 6, 24, 0.98)');
      bodyGrad.addColorStop(0.7, 'rgba(6, 3, 12, 0.98)');
      bodyGrad.addColorStop(1, 'rgba(4, 2, 8, 0.98)');

      // Draw Main Chamfered Rounded Prism Body
      ctx.beginPath();
      ctx.roundRect(x - hw, y - hh, w, h, [b, b, b, b]);
      ctx.fillStyle = bodyGrad;
      ctx.fill();

      // -----------------------------------------------------------------------
      // A. INTERNAL 3D GLOWING NEON ENERGY SPINE / CORE (CYAN TO VIOLET)
      // -----------------------------------------------------------------------
      if (!isReflection) {
        ctx.save();
        ctx.beginPath();
        ctx.roundRect(x - hw, y - hh, w, h, [b, b, b, b]);
        ctx.clip(); // Keep glow inside the crystal body

        const spinePulse = Math.sin(this.time * 1.8) * 0.15 + 0.85;

        // 1. Soft Volumetric Inner Glow
        const spineGlowGrad = ctx.createRadialGradient(x, y, 4, x, y, hw * 0.85);
        spineGlowGrad.addColorStop(0, `rgba(0, 240, 255, ${0.28 * spinePulse})`);
        spineGlowGrad.addColorStop(0.45, `rgba(168, 85, 247, ${0.22 * spinePulse})`);
        spineGlowGrad.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = spineGlowGrad;
        ctx.fillRect(x - hw, y - hh, w, h);

        // 2. Central Sleek Vertical Energy Spine (Gradient Cyan -> Violet)
        const spineGrad = ctx.createLinearGradient(x, y - hh * 0.85, x, y + hh * 0.85);
        spineGrad.addColorStop(0, '#00F0FF');   // Electric Cyan at top
        spineGrad.addColorStop(0.4, '#38bdf8');
        spineGrad.addColorStop(0.7, '#c084fc');
        spineGrad.addColorStop(1, '#A855F7');   // Cyber Violet at bottom

        ctx.strokeStyle = spineGrad;
        ctx.lineWidth = 3.5 * scale;
        ctx.lineCap = 'round';
        ctx.shadowColor = '#00F0FF';
        ctx.shadowBlur = 14 * spinePulse;
        ctx.beginPath();
        ctx.moveTo(x, y - hh * 0.82);
        ctx.lineTo(x, y + hh * 0.82);
        ctx.stroke();
        ctx.shadowBlur = 0; // reset
        ctx.restore();
      }

      // -----------------------------------------------------------------------
      // B. INNER 3D CRYSTAL CHAMFER FACET & ANGLED ARCHITECTURAL LOUVERS
      // -----------------------------------------------------------------------
      const innerB = Math.max(4, b - 3);
      const innerMargin = 7;
      if (w > innerMargin * 2 && h > innerMargin * 2) {
        ctx.beginPath();
        ctx.roundRect(x - hw + innerMargin, y - hh + innerMargin, w - innerMargin * 2, h - innerMargin * 2, [innerB, innerB, innerB, innerB]);
        const innerGrad = ctx.createLinearGradient(x - hw, y - hh, x + hw, y + hh);
        innerGrad.addColorStop(0, 'rgba(18, 9, 34, 0.72)');
        innerGrad.addColorStop(0.5, 'rgba(8, 4, 16, 0.82)');
        innerGrad.addColorStop(1, 'rgba(4, 2, 10, 0.85)');
        ctx.fillStyle = innerGrad;
        ctx.fill();

        // Subtle 1px Chamfer Crease Line defining the beveled transition
        if (!isReflection) {
          ctx.strokeStyle = 'rgba(255, 255, 255, 0.10)';
          ctx.lineWidth = 1;
          ctx.stroke();
        }
      }

      if (!isReflection) {
        // C. Subtle 3D Angled Glass Facets / Architectural Chevron Louvers
        ctx.save();
        ctx.beginPath();
        ctx.roundRect(x - hw, y - hh, w, h, [b, b, b, b]);
        ctx.clip();

        const numLouvers = 6;
        const louverStep = (h * 0.75) / numLouvers;
        const louverStartY = y - h * 0.36;

        for (let i = 0; i < numLouvers; i++) {
          const ly = louverStartY + i * louverStep;
          const lGrad = ctx.createLinearGradient(x - hw * 0.75, ly, x + hw * 0.75, ly);
          if (i % 2 === 0) {
            lGrad.addColorStop(0, 'rgba(0, 240, 255, 0.04)');
            lGrad.addColorStop(0.5, 'rgba(0, 240, 255, 0.32)');
            lGrad.addColorStop(1, 'rgba(0, 240, 255, 0.04)');
          } else {
            lGrad.addColorStop(0, 'rgba(168, 85, 247, 0.04)');
            lGrad.addColorStop(0.5, 'rgba(168, 85, 247, 0.28)');
            lGrad.addColorStop(1, 'rgba(168, 85, 247, 0.04)');
          }

          ctx.beginPath();
          ctx.moveTo(x - hw * 0.72, ly - 4 * scale);
          ctx.lineTo(x, ly + 3 * scale);
          ctx.lineTo(x + hw * 0.72, ly - 4 * scale);
          ctx.strokeStyle = lGrad;
          ctx.lineWidth = 1.0;
          ctx.stroke();
        }

        // Soft Diagonal Frosted-Glass Specular Sweep gliding slowly across the monolith
        const sweepCycle = (this.time * 0.38) % (Math.PI * 2);
        const sweepOffset = Math.sin(sweepCycle) * (w + h * 0.55);

        const sweepGrad = ctx.createLinearGradient(
          x - hw + sweepOffset - 50, y - hh,
          x - hw + sweepOffset + 50, y + hh
        );
        sweepGrad.addColorStop(0, 'rgba(255, 255, 255, 0)');
        sweepGrad.addColorStop(0.40, 'rgba(0, 240, 255, 0.06)');
        sweepGrad.addColorStop(0.50, 'rgba(255, 255, 255, 0.16)'); // Specular frosted sheen
        sweepGrad.addColorStop(0.60, 'rgba(168, 85, 247, 0.08)');
        sweepGrad.addColorStop(1, 'rgba(255, 255, 255, 0)');

        ctx.fillStyle = sweepGrad;
        ctx.fill();
        ctx.restore();

        // Sharp 1px Beveled Edge Rim-Light: Electric Cyan (#00F0FF) on Left Facets
        const cyanAlpha = Math.max(0.50, 0.90 + Math.sin(this.time * 0.6) * 0.25);
        const cyanGrad = ctx.createLinearGradient(x - hw, y - hh, x - hw, y + hh);
        cyanGrad.addColorStop(0, `rgba(0, 240, 255, ${cyanAlpha * 0.85})`);
        cyanGrad.addColorStop(0.35, `rgba(0, 240, 255, ${cyanAlpha})`);
        cyanGrad.addColorStop(0.7, `rgba(0, 240, 255, ${cyanAlpha * 0.95})`);
        cyanGrad.addColorStop(1, `rgba(0, 240, 255, 0.25)`);

        // Outer Crisp 1px Cyan Contour Line
        ctx.beginPath();
        ctx.moveTo(x - hw + b, y - hh);
        ctx.lineTo(x - hw, y - hh + b);
        ctx.lineTo(x - hw, y + hh - b);
        ctx.lineTo(x - hw + b, y + hh);
        ctx.strokeStyle = cyanGrad;
        ctx.lineWidth = 1.2;
        ctx.stroke();

        // Inner Chamfer 1px Highlight
        ctx.beginPath();
        ctx.moveTo(x - hw + innerMargin, y - hh + innerMargin + innerB);
        ctx.lineTo(x - hw + innerMargin, y + hh - innerMargin - innerB);
        ctx.strokeStyle = `rgba(0, 240, 255, ${cyanAlpha * 0.5})`;
        ctx.lineWidth = 1;
        ctx.stroke();

        // Sharp 1px Beveled Edge Rim-Light: Cyber Violet (#A855F7) on Right Facets
        const violetAlpha = Math.max(0.50, 0.92 + Math.cos(this.time * 0.55) * 0.22);
        const violetGrad = ctx.createLinearGradient(x + hw, y - hh, x + hw, y + hh);
        violetGrad.addColorStop(0, `rgba(168, 85, 247, 0.25)`);
        violetGrad.addColorStop(0.35, `rgba(168, 85, 247, ${violetAlpha * 0.95})`);
        violetGrad.addColorStop(0.7, `rgba(168, 85, 247, ${violetAlpha})`);
        violetGrad.addColorStop(1, `rgba(168, 85, 247, ${violetAlpha * 0.85})`);

        // Outer Crisp 1px Violet Contour Line
        ctx.beginPath();
        ctx.moveTo(x + hw - b, y - hh);
        ctx.lineTo(x + hw, y - hh + b);
        ctx.lineTo(x + hw, y + hh - b);
        ctx.lineTo(x + hw - b, y + hh);
        ctx.strokeStyle = violetGrad;
        ctx.lineWidth = 1.2;
        ctx.stroke();

        // Top Crown Bevel Soft Reflection from Halo Rings
        const topHaloReflection = ctx.createRadialGradient(x, y - hh + 4, 2, x, y - hh + 4, hw * 0.8);
        topHaloReflection.addColorStop(0, 'rgba(0, 240, 255, 0.45)');
        topHaloReflection.addColorStop(0.6, 'rgba(168, 85, 247, 0.20)');
        topHaloReflection.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = topHaloReflection;
        ctx.beginPath();
        ctx.ellipse(x, y - hh + 4, hw * 0.75, 5 * scale, 0, 0, Math.PI * 2);
        ctx.fill();
      }
    }

    // -------------------------------------------------------------------------
    // C. FLOATING 3D HALO CROWN ABOVE THE MONOLITH (CANVAS RENDERING)
    // -------------------------------------------------------------------------
    drawHaloCrown(cx, topY, scale) {
      const ctx = this.ctx;
      const levitate = Math.sin(this.time * 1.2) * (5 * scale);
      const baseY = topY - 18 * scale + levitate;

      ctx.save();

      // Ring 1: Lower Halo (Electric Cyan #00F0FF)
      const r1Y = baseY;
      const r1Rx = 48 * scale;
      const r1Ry = 13 * scale;
      const r1Rot = -0.10 + Math.sin(this.time * 0.6) * 0.03;

      ctx.save();
      ctx.translate(cx, r1Y);
      ctx.rotate(r1Rot);
      ctx.beginPath();
      ctx.ellipse(0, 0, r1Rx, r1Ry, 0, 0, Math.PI * 2);
      ctx.strokeStyle = 'rgba(0, 240, 255, 0.90)';
      ctx.lineWidth = 1.6;
      ctx.shadowColor = '#00F0FF';
      ctx.shadowBlur = 10;
      ctx.stroke();
      ctx.restore();

      // Ring 2: Middle Halo (Cyber Violet #A855F7)
      const r2Y = baseY - 16 * scale;
      const r2Rx = 36 * scale;
      const r2Ry = 9.5 * scale;
      const r2Rot = 0.08 - Math.cos(this.time * 0.55) * 0.025;

      ctx.save();
      ctx.translate(cx, r2Y);
      ctx.rotate(r2Rot);
      ctx.beginPath();
      ctx.ellipse(0, 0, r2Rx, r2Ry, 0, 0, Math.PI * 2);
      ctx.strokeStyle = 'rgba(168, 85, 247, 0.85)';
      ctx.lineWidth = 1.4;
      ctx.shadowColor = '#A855F7';
      ctx.shadowBlur = 8;
      ctx.stroke();
      ctx.restore();

      // Ring 3: Top Halo (Luminous Cyan-Violet Blend)
      const r3Y = baseY - 29 * scale;
      const r3Rx = 24 * scale;
      const r3Ry = 6.5 * scale;
      const r3Rot = -0.05 + Math.sin(this.time * 0.7) * 0.02;

      ctx.save();
      ctx.translate(cx, r3Y);
      ctx.rotate(r3Rot);
      ctx.beginPath();
      ctx.ellipse(0, 0, r3Rx, r3Ry, 0, 0, Math.PI * 2);
      ctx.strokeStyle = 'rgba(56, 189, 248, 0.80)';
      ctx.lineWidth = 1.2;
      ctx.shadowColor = '#38bdf8';
      ctx.shadowBlur = 6;
      ctx.stroke();
      ctx.restore();

      ctx.restore();
    }

    bindEvents() {
      const onMouseMove = (e) => {
        const rect = this.hero.getBoundingClientRect();
        const clientX = e.clientX - rect.left;
        const clientY = e.clientY - rect.top;

        this.mouse.targetX = (clientX / rect.width) * 2 - 1;
        this.mouse.targetY = -((clientY / rect.height) * 2 - 1);
      };

      const onMouseLeave = () => {
        this.mouse.targetX = 0;
        this.mouse.targetY = 0;
      };

      window.addEventListener('mousemove', onMouseMove, { passive: true });
      this.hero.addEventListener('mouseleave', onMouseLeave, { passive: true });

      window.addEventListener('resize', () => {
        if (this.isThree && this.renderer && this.camera) {
          this.width = this.hero.clientWidth || window.innerWidth;
          this.height = this.hero.clientHeight || 750;
          this.camera.aspect = this.width / this.height;
          this.camera.updateProjectionMatrix();
          this.renderer.setSize(this.width, this.height);
        } else if (!this.isThree) {
          this.resizeCanvas();
        }
      }, { passive: true });

      document.addEventListener('visibilitychange', () => {
        this.isTabActive = !document.hidden;
        if (this.isTabActive && !this.animId) {
          if (this.isThree) this.startThree();
          else this.startCanvas();
        }
      });
    }

    setupObserver() {
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            this.isIntersecting = entry.isIntersecting;
            if (this.isIntersecting && !this.animId) {
              if (this.isThree) this.startThree();
              else this.startCanvas();
            } else {
              if (this.animId) {
                cancelAnimationFrame(this.animId);
                this.animId = null;
              }
            }
          });
        }, { threshold: 0.08 });
        observer.observe(this.hero);
      }
    }
  }

  // Auto-initialize on DOM ready
  function initMonolith() {
    const container = document.getElementById('vg-pb-monolith-wrap');
    if (container) {
      new ObsidianCrystalMonolith(container);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMonolith);
  } else {
    initMonolith();
  }
})();
