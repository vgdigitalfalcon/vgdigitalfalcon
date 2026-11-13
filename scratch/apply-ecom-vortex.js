const fs = require('fs');
const path = require('path');

const rootDir = path.resolve(__dirname, '..');

console.log('--- Applying Ecommerce Growth Vortex 3D Animation ---');

// 1. Create assets/js/ecom-vortex-canvas.js
const vortexJsCode = `/**
 * ============================================================================
 * VG DIGITAL FALCON — ECOMMERCE SEO HERO 3D GROWTH VORTEX
 * File: assets/js/ecom-vortex-canvas.js
 * 
 * Animation Concept:
 * A sleek 3D Holographic Product-to-Conversion Stream scoped strictly inside the
 * Hero section of ecommerce-seo-service.html (position: absolute; inset: 0; z-index: 0).
 * 
 * Features:
 * 1. Floating 3D Translucent Wireframe Cubes (Product Listings):
 *    - 3D perspective-projected wireframe cubes with translucent glass faces
 *      and glowing Electric Cyan (#00F0FF) & Cyber Violet (#A855F7) edges.
 *    - Each cube rotates smoothly on pitch, yaw, roll, drifting along the stream.
 * 2. Upward-Ascending 10X Sales Growth Spiral (The Vortex):
 *    - Helical neon light trails curving in 3D space in the center area,
 *      signifying 10X sales growth and revenue ascension.
 * 3. Search Particles & Conversion Sparks:
 *    - Glowing Cyan & Violet search query particles flowing along spiral trajectories.
 *    - Subtle Gold (#F59E0B) and Emerald (#10B981) conversion sparks bursting
 *      as products ascend into the conversion crest.
 * 4. Contrast Guard:
 *    - Controlled alpha (0.25 - 0.50) against #07070F backdrop ensuring foreground
 *      headline and audit form remain 100% sharp and readable.
 * 5. 60fps Performance & Interactivity:
 *    - Continuous smooth animation with cursor parallax (mouse damping).
 *    - Automatic pause on scroll-out (IntersectionObserver) & tab blur.
 * ============================================================================
 */

(function () {
  'use strict';

  class EcommerceGrowthVortex {
    constructor(canvas) {
      this.canvas = canvas;
      this.ctx = canvas.getContext('2d', { alpha: true });
      if (!this.ctx) return;

      this.hero = canvas.closest('.vg-seo-hero') || canvas.parentElement;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);

      // Color Palette: Electric Cyan, Cyber Violet, Gold, Emerald (Contrast Protected)
      this.colors = {
        cyan: { r: 0, g: 240, b: 255 },       // #00F0FF (Primary Search / Product Edge)
        sky: { r: 56, g: 189, b: 248 },       // #38BDF8
        violet: { r: 168, g: 85, b: 247 },   // #A855F7 (Cyber Violet)
        gold: { r: 245, g: 158, b: 11 },      // #F59E0B (Sales Spark)
        emerald: { r: 16, g: 185, b: 129 },   // #10B981 (Conversion Signal)
        white: { r: 248, g: 250, b: 252 }
      };

      // 3D Parameters
      this.fov = 480;
      this.width = 0;
      this.height = 0;
      this.centerX = 0;
      this.centerY = 0;

      // Animation State
      this.time = 0;
      this.animId = null;
      this.isIntersecting = true;
      this.isTabActive = true;

      // Mouse Parallax
      this.mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };
      this.camRot = { x: 0, y: 0 };

      // Scene Objects
      this.cubes = [];
      this.particles = [];
      this.conversionSparks = [];

      this.init();
    }

    init() {
      this.resize();
      this.buildProductCubes();
      this.buildParticles();
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

      // Place vortex focal center: middle space between text & audit card
      this.centerX = this.width > 991 ? this.width * 0.50 : this.width * 0.50;
      this.centerY = this.height * 0.52;
    }

    buildProductCubes() {
      // 8 Floating 3D Product Wireframe Cubes
      this.cubes = [];
      const cubeCount = this.width > 767 ? 8 : 5;

      for (let i = 0; i < cubeCount; i++) {
        const angle = (i / cubeCount) * Math.PI * 2;
        const radius = 110 + (i % 3) * 65;
        const size = 18 + (i % 3) * 7; // Cube dimensions
        
        this.cubes.push({
          baseRadius: radius,
          currentRadius: radius,
          angle: angle,
          orbitSpeed: 0.0035 + (i % 3) * 0.0018,
          y: (i - cubeCount / 2) * 55,
          yVelocity: -0.45 - (i % 2) * 0.25, // Upward ascending motion
          size: size,
          rotX: Math.random() * Math.PI,
          rotY: Math.random() * Math.PI,
          rotZ: Math.random() * Math.PI,
          rotSpeedX: 0.008 + Math.random() * 0.012,
          rotSpeedY: 0.010 + Math.random() * 0.014,
          rotSpeedZ: 0.006 + Math.random() * 0.008,
          colorType: i % 2 === 0 ? 'cyan' : 'violet',
          glowPulse: Math.random() * Math.PI * 2
        });
      }
    }

    buildParticles() {
      // 55 Search Stream Particles flowing up the vortex
      this.particles = [];
      const particleCount = this.width > 767 ? 55 : 30;

      for (let i = 0; i < particleCount; i++) {
        this.particles.push({
          progress: Math.random(), // 0 to 1 along vortex
          speed: 0.003 + Math.random() * 0.0045,
          arm: i % 3, // 3 Helical spiral arms
          offsetRadius: (Math.random() - 0.5) * 35,
          colorType: i % 3 === 0 ? 'cyan' : (i % 3 === 1 ? 'violet' : 'sky'),
          size: 1.5 + Math.random() * 2.2,
          alphaBase: 0.35 + Math.random() * 0.25
        });
      }

      // 18 Conversion Sparks (Gold & Emerald flashes at top conversion apex)
      this.conversionSparks = [];
      const sparkCount = this.width > 767 ? 18 : 10;
      for (let i = 0; i < sparkCount; i++) {
        this.conversionSparks.push({
          x: (Math.random() - 0.5) * 220,
          y: -150 - Math.random() * 120, // High in the vortex (10X Sales apex)
          z: (Math.random() - 0.5) * 200,
          size: 2 + Math.random() * 2.5,
          colorType: i % 2 === 0 ? 'gold' : 'emerald',
          life: Math.random(),
          speed: 0.012 + Math.random() * 0.016,
          symbol: i % 4 === 0 ? '+10X' : (i % 4 === 1 ? '$' : '•')
        });
      }
    }

    project(x, y, z) {
      // Rotate by camera parallax
      const cosY = Math.cos(this.camRot.y);
      const sinY = Math.sin(this.camRot.y);
      const cosX = Math.cos(this.camRot.x);
      const sinX = Math.sin(this.camRot.x);

      // Yaw (Y axis)
      const x1 = x * cosY - z * sinY;
      const z1 = z * cosY + x * sinY;

      // Pitch (X axis)
      const y2 = y * cosX - z1 * sinX;
      const z2 = z1 * cosX + y * sinX;

      // Perspective scale
      const depth = this.fov + z2;
      if (depth <= 10) return null; // Behind camera

      const scale = this.fov / depth;
      return {
        x: this.centerX + x1 * scale,
        y: this.centerY + y2 * scale,
        scale: scale,
        z: z2
      };
    }

    drawSpiralTracks() {
      // 3 Luminous 3D Helical Ribbon Tracks (The Ecommerce Growth Vortex)
      const ctx = this.ctx;
      const arms = 3;
      const segments = 45;

      for (let arm = 0; arm < arms; arm++) {
        const armOffset = (arm / arms) * Math.PI * 2;
        const color = arm === 0 ? this.colors.cyan : (arm === 1 ? this.colors.violet : this.colors.sky);

        ctx.beginPath();
        let first = true;

        for (let i = 0; i <= segments; i++) {
          const t = i / segments; // 0 (bottom) to 1 (top)
          const angle = t * Math.PI * 4.5 + armOffset + this.time * 0.6;
          // Widening funnel from bottom to top
          const radius = 60 + Math.pow(t, 1.35) * (this.width > 991 ? 260 : 180);
          const y = 200 - t * 440; // Rising from bottom to upper crest
          const x = Math.cos(angle) * radius;
          const z = Math.sin(angle) * radius;

          const p = this.project(x, y, z);
          if (!p) continue;

          if (first) {
            ctx.moveTo(p.x, p.y);
            first = false;
          } else {
            ctx.lineTo(p.x, p.y);
          }
        }

        // Faint glowing stroke (Controlled alpha: 0.16 - 0.28)
        ctx.strokeStyle = \`rgba(\${color.r}, \${color.g}, \${color.b}, 0.22)\`;
        ctx.lineWidth = 1.4;
        ctx.stroke();
      }
    }

    drawParticles() {
      const ctx = this.ctx;

      this.particles.forEach(p => {
        p.progress += p.speed;
        if (p.progress > 1) p.progress -= 1;

        const t = p.progress;
        const armOffset = (p.arm / 3) * Math.PI * 2;
        const angle = t * Math.PI * 4.5 + armOffset + this.time * 0.6;
        const radius = 60 + Math.pow(t, 1.35) * (this.width > 991 ? 260 : 180) + p.offsetRadius;
        const y = 200 - t * 440;
        const x = Math.cos(angle) * radius;
        const z = Math.sin(angle) * radius;

        const pt = this.project(x, y, z);
        if (!pt) return;

        const c = this.colors[p.colorType];
        const alpha = p.alphaBase * (0.35 + 0.65 * Math.sin(t * Math.PI)) * Math.min(pt.scale * 1.1, 1.0);

        ctx.beginPath();
        ctx.arc(pt.x, pt.y, Math.max(p.size * pt.scale, 0.8), 0, Math.PI * 2);
        ctx.fillStyle = \`rgba(\${c.r}, \${c.g}, \${c.b}, \${alpha})\`;
        ctx.shadowColor = \`rgba(\${c.r}, \${c.g}, \${c.b}, 0.8)\`;
        ctx.shadowBlur = 8 * pt.scale;
        ctx.fill();
        ctx.shadowBlur = 0; // reset
      });
    }

    drawConversionSparks() {
      const ctx = this.ctx;

      this.conversionSparks.forEach(s => {
        s.life += s.speed;
        if (s.life > 1) {
          s.life = 0;
          s.x = (Math.random() - 0.5) * 220;
          s.y = -140 - Math.random() * 140;
          s.z = (Math.random() - 0.5) * 200;
        }

        const alpha = Math.sin(s.life * Math.PI) * 0.55;
        const p = this.project(s.x, s.y - s.life * 30, s.z);
        if (!p) return;

        const c = this.colors[s.colorType];
        ctx.save();
        ctx.fillStyle = \`rgba(\${c.r}, \${c.g}, \${c.b}, \${alpha})\`;
        ctx.shadowColor = \`rgba(\${c.r}, \${c.g}, \${c.b}, 0.9)\`;
        ctx.shadowBlur = 10 * p.scale;

        if (s.symbol === '+10X' || s.symbol === '$') {
          ctx.font = \`bold \${Math.floor(10 * p.scale)}px monospace\`;
          ctx.fillText(s.symbol, p.x, p.y);
        } else {
          // Twinkle Diamond Spark
          const size = s.size * p.scale;
          ctx.beginPath();
          ctx.moveTo(p.x, p.y - size * 1.5);
          ctx.lineTo(p.x + size, p.y);
          ctx.lineTo(p.x, p.y + size * 1.5);
          ctx.lineTo(p.x - size, p.y);
          ctx.closePath();
          ctx.fill();
        }

        ctx.restore();
      });
    }

    drawCube(cube) {
      const s = cube.size;
      // Local 8 vertices of a 3D Box
      const localVerts = [
        { x: -s, y: -s, z: -s },
        { x:  s, y: -s, z: -s },
        { x:  s, y:  s, z: -s },
        { x: -s, y:  s, z: -s },
        { x: -s, y: -s, z:  s },
        { x:  s, y: -s, z:  s },
        { x:  s, y:  s, z:  s },
        { x: -s, y:  s, z:  s }
      ];

      // Cube's internal rotation matrices
      const cosX = Math.cos(cube.rotX), sinX = Math.sin(cube.rotX);
      const cosY = Math.cos(cube.rotY), sinY = Math.sin(cube.rotY);
      const cosZ = Math.cos(cube.rotZ), sinZ = Math.sin(cube.rotZ);

      // Global coordinates along orbital stream
      const cubeWorldX = Math.cos(cube.angle) * cube.currentRadius;
      const cubeWorldZ = Math.sin(cube.angle) * cube.currentRadius;
      const cubeWorldY = cube.y;

      // Project all 8 rotated vertices to 2D
      const projVerts = [];
      for (let i = 0; i < 8; i++) {
        const v = localVerts[i];
        // Rotate X
        const y1 = v.y * cosX - v.z * sinX;
        const z1 = v.z * cosX + v.y * sinX;
        // Rotate Y
        const x2 = v.x * cosY + z1 * sinY;
        const z2 = z1 * cosY - v.x * sinY;
        // Rotate Z
        const x3 = x2 * cosZ - y1 * sinZ;
        const y3 = y1 * cosZ + x2 * sinZ;

        const p = this.project(cubeWorldX + x3, cubeWorldY + y3, cubeWorldZ + z2);
        if (!p) return; // Behind camera
        projVerts.push(p);
      }

      if (projVerts.length < 8) return;

      const centerProj = this.project(cubeWorldX, cubeWorldY, cubeWorldZ);
      if (!centerProj) return;

      const ctx = this.ctx;
      const color = this.colors[cube.colorType];
      const alpha = Math.min(centerProj.scale * 0.48, 0.48);

      // 6 Faces (draw translucent glass faces)
      const faces = [
        [0, 1, 2, 3], // Front
        [4, 5, 6, 7], // Back
        [0, 1, 5, 4], // Top
        [2, 3, 7, 6], // Bottom
        [0, 3, 7, 4], // Left
        [1, 2, 6, 5]  // Right
      ];

      faces.forEach(f => {
        ctx.beginPath();
        ctx.moveTo(projVerts[f[0]].x, projVerts[f[0]].y);
        ctx.lineTo(projVerts[f[1]].x, projVerts[f[1]].y);
        ctx.lineTo(projVerts[f[2]].x, projVerts[f[2]].y);
        ctx.lineTo(projVerts[f[3]].x, projVerts[f[3]].y);
        ctx.closePath();
        ctx.fillStyle = \`rgba(\${color.r}, \${color.g}, \${color.b}, \${alpha * 0.16})\`;
        ctx.fill();
      });

      // 12 Edges (draw crisp glowing wireframe strokes)
      const edges = [
        [0, 1], [1, 2], [2, 3], [3, 0],
        [4, 5], [5, 6], [6, 7], [7, 4],
        [0, 4], [1, 5], [2, 6], [3, 7]
      ];

      ctx.beginPath();
      edges.forEach(e => {
        ctx.moveTo(projVerts[e[0]].x, projVerts[e[0]].y);
        ctx.lineTo(projVerts[e[1]].x, projVerts[e[1]].y);
      });
      ctx.strokeStyle = \`rgba(\${color.r}, \${color.g}, \${color.b}, \${alpha})\`;
      ctx.lineWidth = Math.max(1.1 * centerProj.scale, 0.7);
      ctx.stroke();

      // Glowing Core Hologram inside Product Box
      ctx.beginPath();
      ctx.arc(centerProj.x, centerProj.y, Math.max(3.2 * centerProj.scale, 1.2), 0, Math.PI * 2);
      ctx.fillStyle = \`rgba(\${color.r}, \${color.g}, \${color.b}, \${alpha * 1.5})\`;
      ctx.shadowColor = \`rgba(\${color.r}, \${color.g}, \${color.b}, 0.8)\`;
      ctx.shadowBlur = 8 * centerProj.scale;
      ctx.fill();
      ctx.shadowBlur = 0;
    }

    render() {
      if (!this.isIntersecting || !this.isTabActive) return;

      this.ctx.clearRect(0, 0, this.width, this.height);
      this.time += 0.016; // ~60fps increment

      // Smooth mouse parallax interpolation (lerp)
      this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.04;
      this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.04;
      this.camRot.y = this.mouse.x * 0.28;
      this.camRot.x = -this.mouse.y * 0.18;

      // 1. Draw Helical Vortex Streams
      this.drawSpiralTracks();

      // 2. Draw Floating Product Cubes
      this.cubes.forEach(cube => {
        // Orbit motion
        cube.angle += cube.orbitSpeed;
        cube.y += cube.yVelocity;
        
        // Loop when ascending above top
        if (cube.y < -220) {
          cube.y = 200;
          cube.currentRadius = cube.baseRadius;
        }

        // Tumble rotation
        cube.rotX += cube.rotSpeedX;
        cube.rotY += cube.rotSpeedY;
        cube.rotZ += cube.rotSpeedZ;

        this.drawCube(cube);
      });

      // 3. Draw Search Query Stream Particles
      this.drawParticles();

      // 4. Draw Conversion Sparks (Gold & Emerald flashes at 10X ascension)
      this.drawConversionSparks();

      this.animId = requestAnimationFrame(() => this.render());
    }

    start() {
      if (!this.animId) {
        this.render();
      }
    }

    stop() {
      if (this.animId) {
        cancelAnimationFrame(this.animId);
        this.animId = null;
      }
    }

    bindEvents() {
      // Cursor Parallax
      if (this.hero) {
        this.hero.addEventListener('mousemove', (e) => {
          const rect = this.hero.getBoundingClientRect();
          const normX = ((e.clientX - rect.left) / rect.width) * 2 - 1;
          const normY = ((e.clientY - rect.top) / rect.height) * 2 - 1;
          this.mouse.targetX = normX;
          this.mouse.targetY = normY;
        }, { passive: true });

        this.hero.addEventListener('mouseleave', () => {
          this.mouse.targetX = 0;
          this.mouse.targetY = 0;
        }, { passive: true });
      }

      // Responsive Resize (Debounced)
      let resizeTimer = null;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
          this.resize();
          this.buildProductCubes();
          this.buildParticles();
        }, 150);
      }, { passive: true });

      // Tab Visibility Pause
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
        }, { threshold: 0.05 });

        observer.observe(this.hero);
      }
    }
  }

  // Initialize on DOM load
  function initEcommerceVortex() {
    const canvas = document.getElementById('vg-ecom-vortex-canvas');
    if (canvas) {
      new EcommerceGrowthVortex(canvas);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEcommerceVortex);
  } else {
    initEcommerceVortex();
  }
})();
`;

const vortexJsPath = path.join(rootDir, 'assets', 'js', 'ecom-vortex-canvas.js');
fs.writeFileSync(vortexJsPath, vortexJsCode, 'utf8');
console.log('Created assets/js/ecom-vortex-canvas.js');

// Mirror JS to public_html (1)
const mirrorJs1 = path.join(rootDir, 'public_html (1)', 'assets', 'js', 'ecom-vortex-canvas.js');
const mirrorJs2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'js', 'ecom-vortex-canvas.js');
if (fs.existsSync(path.dirname(mirrorJs1))) fs.writeFileSync(mirrorJs1, vortexJsCode, 'utf8');
if (fs.existsSync(path.dirname(mirrorJs2))) fs.writeFileSync(mirrorJs2, vortexJsCode, 'utf8');
console.log('Mirrored ecom-vortex-canvas.js to public_html mirrors.');

// 2. Add .vg-ecom-vortex-canvas styles to assets/css/color.css
const cssPath = path.join(rootDir, 'assets', 'css', 'color.css');
let cssContent = fs.readFileSync(cssPath, 'utf8');

const canvasCss = `
/* Ecommerce SEO Growth Vortex 3D Canvas (Strictly Inside Hero) */
.vg-ecom-vortex-canvas {
  position: absolute !important;
  top: 0 !important;
  left: 0 !important;
  width: 100% !important;
  height: 100% !important;
  z-index: 0 !important;
  pointer-events: none !important;
  background: transparent !important;
}
`;

if (!cssContent.includes('.vg-ecom-vortex-canvas')) {
  cssContent += '\n' + canvasCss.trim() + '\n';
  fs.writeFileSync(cssPath, cssContent, 'utf8');
  console.log('Added .vg-ecom-vortex-canvas CSS to color.css');
}

const mirrorCss1 = path.join(rootDir, 'public_html (1)', 'assets', 'css', 'color.css');
const mirrorCss2 = path.join(rootDir, 'public_html (1)', 'website', 'assets', 'css', 'color.css');
if (fs.existsSync(mirrorCss1)) fs.writeFileSync(mirrorCss1, cssContent, 'utf8');
if (fs.existsSync(mirrorCss2)) fs.writeFileSync(mirrorCss2, cssContent, 'utf8');
console.log('Mirrored color.css across all mirrors.');

// 3. Update HTML in ecommerce-seo-service.html & mirrors
const htmlFiles = [
  path.join(rootDir, 'ecommerce-seo-service.html'),
  path.join(rootDir, 'public_html (1)', 'ecommerce-seo-service.html'),
  path.join(rootDir, 'public_html (1)', 'website', 'ecommerce-seo-service.html')
];

htmlFiles.forEach((file) => {
  if (!fs.existsSync(file)) return;
  let html = fs.readFileSync(file, 'utf8');

  // Replace old canvas with new vortex canvas
  html = html.replace(
    /<canvas id="vg-seo-hero-canvas" class="vg-seo-hero-canvas"><\/canvas>/g,
    '<!-- Interactive 3D Holographic Product-to-Conversion Growth Vortex Canvas -->\n            <canvas id="vg-ecom-vortex-canvas" class="vg-ecom-vortex-canvas"></canvas>'
  );

  // Replace seo-hero-canvas.js script with ecom-vortex-canvas.js
  html = html.replace(
    /<script src="assets\/js\/seo-hero-canvas\.js"><\/script>/g,
    '<script src="assets/js/ecom-vortex-canvas.js"></script>'
  );

  fs.writeFileSync(file, html, 'utf8');
  console.log('Updated HTML in:', file);
});

console.log('--- Ecommerce Growth Vortex Animation Applied Successfully ---');

