/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D EXECUTIVE NETWORK CONSTELLATION
 * File: assets/js/pb-executive-constellation.js
 * 
 * Bespoke 3D Authority Art for LinkedIn Personal Branding:
 * - 3D Frosted-Glass Authority Core with dual-tone rim lighting (#00F0FF & #A855F7)
 * - 8 floating satellite executive network nodes in tilted 3D orbital planes
 * - Razor-thin luminous light filaments with streaming data pulses
 * - Subtle secondary constellation cross-links representing decision-maker reach
 * - Continuous slow 3D orbital drift with smooth cursor-reactive perspective parallax
 * - Strictly z-index: 0, zero text overlap, lightweight 60fps Canvas 3D engine
 * ============================================================================
 */

(function () {
  'use strict';

  class PBExecutiveConstellation {
    constructor(container) {
      this.container = container;
      this.hero = container.closest('#personal-branding-hero') || container.parentElement;

      this.canvas = document.createElement('canvas');
      this.canvas.className = 'vg-pb-constellation-canvas';
      this.canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;z-index:0;pointer-events:none;';
      this.container.appendChild(this.canvas);
      this.ctx = this.canvas.getContext('2d');

      this.width = 0;
      this.height = 0;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);
      this.time = 0;
      this.baseRotY = 0;
      this.animId = null;
      this.isIntersecting = true;
      this.isTabActive = true;

      // 3D Perspective Projection Camera
      this.fov = 520;

      // Interactive Mouse Parallax with Smooth Damping
      this.mouse = {
        targetX: 0,
        targetY: 0,
        currentX: 0,
        currentY: 0
      };

      // 1. Central Authority Core Config
      this.core = {
        outerRadius: 36,
        innerRadius: 20,
        pulseSpeed: 0.035,
        wireframeRings: [
          { tiltX: 0.35, tiltZ: 0.20, speed: 0.009, color: '#00F0FF' },
          { tiltX: -0.45, tiltZ: -0.30, speed: -0.007, color: '#C084FC' },
          { tiltX: 0.15, tiltZ: 0.85, speed: 0.011, color: '#38BDF8' }
        ]
      };

      // 2. Eight Executive Network Spheres / Nodes in 3D Orbits
      this.nodes = [
        {
          id: 'cxo_board',
          radius3D: 142,
          theta: 0.25,
          phi: 0.38,
          nodeRadius: 7.2,
          speed: 0.0058,
          colorA: '#00F0FF',
          colorB: '#38BDF8',
          label: 'Board & CXO'
        },
        {
          id: 'enterprise_buyer',
          radius3D: 168,
          theta: 1.15,
          phi: -0.28,
          nodeRadius: 6.8,
          speed: 0.0049,
          colorA: '#A855F7',
          colorB: '#C084FC',
          label: 'Key Decision Maker'
        },
        {
          id: 'strategic_partner',
          radius3D: 135,
          theta: 1.95,
          phi: 0.42,
          nodeRadius: 6.4,
          speed: 0.0062,
          colorA: '#00F0FF',
          colorB: '#A855F7',
          label: 'Strategic Partner'
        },
        {
          id: 'vc_investor',
          radius3D: 155,
          theta: 2.75,
          phi: -0.35,
          nodeRadius: 7.5,
          speed: 0.0051,
          colorA: '#C084FC',
          colorB: '#E879F9',
          label: 'Venture Capital'
        },
        {
          id: 'industry_media',
          radius3D: 125,
          theta: 3.55,
          phi: 0.25,
          nodeRadius: 6.0,
          speed: 0.0068,
          colorA: '#38BDF8',
          colorB: '#00F0FF',
          label: 'Industry Media'
        },
        {
          id: 'thought_leader',
          radius3D: 172,
          theta: 4.35,
          phi: -0.45,
          nodeRadius: 7.0,
          speed: 0.0046,
          colorA: '#A855F7',
          colorB: '#7C3AED',
          label: 'Global Peer'
        },
        {
          id: 'talent_executive',
          radius3D: 130,
          theta: 5.15,
          phi: 0.52,
          nodeRadius: 5.8,
          speed: 0.0065,
          colorA: '#00F0FF',
          colorB: '#C084FC',
          label: 'Elite Talent'
        },
        {
          id: 'zenith_influencer',
          radius3D: 115,
          theta: 5.95,
          phi: -0.22,
          nodeRadius: 6.5,
          speed: 0.0075,
          colorA: '#E879F9',
          colorB: '#00F0FF',
          label: 'Keynote Reach'
        }
      ];

      // Constellation Cross-Links between adjacent/influential nodes
      this.crossLinks = [
        [0, 1], [1, 2], [2, 3], [3, 4], [4, 5], [5, 6], [6, 7], [7, 0],
        [0, 3], [1, 5], [2, 6]
      ];

      // Streaming Light Pulses (Photons) traveling along the core-to-node filaments
      this.pulses = [];
      const pulseCount = 16;
      for (let i = 0; i < pulseCount; i++) {
        this.pulses.push({
          nodeIndex: i % this.nodes.length,
          t: Math.random(),
          speed: 0.0045 + Math.random() * 0.0045,
          direction: Math.random() > 0.35 ? 1 : -1, // mostly outbound, some inbound feedback
          length: 0.12 + Math.random() * 0.08,
          size: 1.4 + Math.random() * 1.4,
          color: Math.random() > 0.5 ? '#00F0FF' : '#C084FC'
        });
      }

      // Micro Ambient Luminescence Motes
      this.motes = [];
      for (let i = 0; i < 22; i++) {
        this.motes.push({
          x: (Math.random() - 0.5) * 360,
          y: (Math.random() - 0.5) * 260,
          z: (Math.random() - 0.5) * 260,
          vx: (Math.random() - 0.5) * 0.18,
          vy: (Math.random() - 0.5) * 0.22,
          radius: 0.8 + Math.random() * 1.5,
          color: Math.random() > 0.5 ? '#00F0FF' : '#A855F7',
          alpha: 0.2 + Math.random() * 0.4
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
      this.baseRotY += 0.0035; // Slow continuous 3D constellation orbital yaw

      // Smooth cursor parallax interpolation
      this.mouse.currentX += (this.mouse.targetX - this.mouse.currentX) * 0.045;
      this.mouse.currentY += (this.mouse.targetY - this.mouse.currentY) * 0.045;

      // Update streaming pulses
      for (let i = 0; i < this.pulses.length; i++) {
        const p = this.pulses[i];
        p.t += p.speed * p.direction;
        if (p.t > 1) p.t = 0;
        if (p.t < 0) p.t = 1;
      }

      // Update ambient motes
      for (let i = 0; i < this.motes.length; i++) {
        const m = this.motes[i];
        m.x += m.vx;
        m.y += m.vy;
        if (m.x > 220) m.x = -220;
        if (m.x < -220) m.x = 220;
        if (m.y > 150) m.y = -150;
        if (m.y < -150) m.y = 150;
      }
    }

    // 3D Perspective Projection Helper
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

      // Perspective Scale Factor
      const scale = this.fov / (this.fov + z2);

      // Centered strictly in the open middle gap of the hero section
      const centerX = this.width * 0.50 + (this.mouse.currentX * 16);
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

      const rotY = this.baseRotY + this.mouse.currentX * 0.28;
      const rotX = -this.mouse.currentY * 0.20 + Math.sin(this.time * 0.002) * 0.08;

      // Vertical floating oscillation of the core
      const coreY = Math.sin(this.time * 0.025) * 6;
      const projCore = this.project(0, coreY, 0, rotX, rotY);

      // 1. Ambient Volumetric Radial Backlight Glow
      const bgGlow = this.ctx.createRadialGradient(
        projCore.x, projCore.y, 15,
        projCore.x, projCore.y, Math.min(this.width * 0.28, 280)
      );
      bgGlow.addColorStop(0, 'rgba(168, 85, 247, 0.16)');
      bgGlow.addColorStop(0.45, 'rgba(0, 240, 255, 0.08)');
      bgGlow.addColorStop(1, 'rgba(7, 7, 15, 0)');
      this.ctx.fillStyle = bgGlow;
      this.ctx.fillRect(0, 0, this.width, this.height);

      // 2. Compute 3D Positions for All 8 Satellite Nodes
      const projectedNodes = [];
      for (let i = 0; i < this.nodes.length; i++) {
        const n = this.nodes[i];
        const thetaNow = n.theta + this.time * n.speed;
        const phiNow = n.phi + Math.sin(this.time * 0.015 + i) * 0.12;

        const x3D = n.radius3D * Math.cos(thetaNow) * Math.cos(phiNow);
        const y3D = coreY + n.radius3D * Math.sin(phiNow);
        const z3D = n.radius3D * Math.sin(thetaNow) * Math.cos(phiNow);

        const proj = this.project(x3D, y3D, z3D, rotX, rotY);
        projectedNodes.push({
          index: i,
          config: n,
          proj: proj,
          x3D: x3D,
          y3D: y3D,
          z3D: z3D
        });
      }

      // 3. Render Secondary Inter-Node Constellation Cross-Links (Whisper Graph)
      this.renderCrossLinks(projectedNodes);

      // 4. Render Nodes & Primary Filaments with Depth Sorting
      // Split into back items (z < 0) and front items (z >= 0) relative to core
      const backNodes = projectedNodes.filter(n => n.proj.z < 0);
      const frontNodes = projectedNodes.filter(n => n.proj.z >= 0);

      // A. Back Filaments & Back Nodes
      for (let i = 0; i < backNodes.length; i++) {
        this.renderFilament(projCore, backNodes[i]);
        this.renderNode(backNodes[i]);
      }

      // B. Central Frosted-Glass Authority Core
      this.renderAuthorityCore(projCore, rotX, rotY);

      // C. Front Filaments & Front Nodes
      for (let i = 0; i < frontNodes.length; i++) {
        this.renderFilament(projCore, frontNodes[i]);
        this.renderNode(frontNodes[i]);
      }

      // 5. Render Streaming Energy Pulses (Photons)
      this.renderPulses(projCore, projectedNodes);

      // 6. Render Ambient Luminescence Motes
      this.renderMotes(rotX, rotY);
    }

    // Render Central Frosted-Glass Authority Core
    renderAuthorityCore(projCore, rotX, rotY) {
      if (projCore.scale <= 0) return;
      const ctx = this.ctx;
      const rOuter = this.core.outerRadius * projCore.scale;
      const rInner = this.core.innerRadius * projCore.scale;

      ctx.save();

      // Outer Frosted Glass Halo & Volumetric Shell
      const shellGrad = ctx.createRadialGradient(
        projCore.x - rOuter * 0.25, projCore.y - rOuter * 0.25, rInner * 0.3,
        projCore.x, projCore.y, rOuter
      );
      shellGrad.addColorStop(0, 'rgba(0, 240, 255, 0.45)');
      shellGrad.addColorStop(0.45, 'rgba(168, 85, 247, 0.35)');
      shellGrad.addColorStop(0.85, 'rgba(15, 7, 32, 0.85)');
      shellGrad.addColorStop(1, 'rgba(0, 240, 255, 0.65)');

      ctx.beginPath();
      ctx.arc(projCore.x, projCore.y, rOuter, 0, Math.PI * 2);
      ctx.fillStyle = shellGrad;
      ctx.shadowColor = '#00F0FF';
      ctx.shadowBlur = 16;
      ctx.fill();

      // Razor-thin Specular Rim around Core Glass Sphere
      ctx.strokeStyle = 'rgba(0, 240, 255, 0.85)';
      ctx.lineWidth = 1.4;
      ctx.stroke();

      // Inner Luminous Pulsing Energy Core
      const pulseScale = 1 + Math.sin(this.time * this.core.pulseSpeed) * 0.08;
      const innerGrad = ctx.createRadialGradient(
        projCore.x, projCore.y, 0,
        projCore.x, projCore.y, rInner * pulseScale
      );
      innerGrad.addColorStop(0, '#FFFFFF');
      innerGrad.addColorStop(0.35, '#00F0FF');
      innerGrad.addColorStop(0.75, '#A855F7');
      innerGrad.addColorStop(1, 'rgba(168, 85, 247, 0)');

      ctx.beginPath();
      ctx.arc(projCore.x, projCore.y, rInner * pulseScale, 0, Math.PI * 2);
      ctx.fillStyle = innerGrad;
      ctx.shadowColor = '#A855F7';
      ctx.shadowBlur = 20;
      ctx.fill();

      // 3D Orbital Wireframe Glass Facet Rings around the Core
      for (let w = 0; w < this.core.wireframeRings.length; w++) {
        const ring = this.core.wireframeRings[w];
        const ringTheta = this.time * ring.speed;
        const ringRadius = rOuter * 1.25;

        ctx.beginPath();
        const segs = 32;
        for (let s = 0; s <= segs; s++) {
          const a = (s / segs) * Math.PI * 2;
          const rx = Math.cos(a) * ringRadius;
          const ry = Math.sin(a) * ringRadius * ring.tiltX;
          const rz = Math.sin(a) * ringRadius * ring.tiltZ;

          // Simple local 3D rotation
          const cosR = Math.cos(ringTheta);
          const sinR = Math.sin(ringTheta);
          const rxRot = rx * cosR - rz * sinR;
          const rzRot = rx * sinR + rz * cosR;

          const px = projCore.x + rxRot;
          const py = projCore.y + ry;

          if (s === 0) ctx.moveTo(px, py);
          else ctx.lineTo(px, py);
        }
        ctx.strokeStyle = ring.color;
        ctx.lineWidth = 1.0;
        ctx.globalAlpha = 0.65;
        ctx.shadowColor = ring.color;
        ctx.shadowBlur = 6;
        ctx.stroke();
      }

      ctx.restore();
    }

    // Render Primary Luminous Light Filament (Core -> Node)
    renderFilament(projCore, nodeItem) {
      const ctx = this.ctx;
      const np = nodeItem.proj;
      if (np.scale <= 0) return;

      ctx.save();
      // Alpha based on depth
      const depthAlpha = Math.min(1, Math.max(0.25, 0.55 + (np.z / 280) * 0.45));

      const grad = ctx.createLinearGradient(projCore.x, projCore.y, np.x, np.y);
      grad.addColorStop(0, 'rgba(0, 240, 255, ' + (depthAlpha * 0.85) + ')');
      grad.addColorStop(0.5, 'rgba(168, 85, 247, ' + (depthAlpha * 0.75) + ')');
      grad.addColorStop(1, 'rgba(192, 132, 252, ' + (depthAlpha * 0.95) + ')');

      ctx.beginPath();
      ctx.moveTo(projCore.x, projCore.y);
      ctx.lineTo(np.x, np.y);
      ctx.strokeStyle = grad;
      ctx.lineWidth = 1.1;
      ctx.shadowColor = '#00F0FF';
      ctx.shadowBlur = 5;
      ctx.stroke();
      ctx.restore();
    }

    // Render Secondary Inter-Node Constellation Cross-Links
    renderCrossLinks(nodes) {
      const ctx = this.ctx;
      ctx.save();
      for (let i = 0; i < this.crossLinks.length; i++) {
        const link = this.crossLinks[i];
        const nA = nodes[link[0]];
        const nB = nodes[link[1]];
        if (!nA || !nB || nA.proj.scale <= 0 || nB.proj.scale <= 0) continue;

        const avgZ = (nA.proj.z + nB.proj.z) / 2;
        const linkAlpha = Math.min(0.40, Math.max(0.12, 0.22 + (avgZ / 350) * 0.18));

        ctx.beginPath();
        ctx.moveTo(nA.proj.x, nA.proj.y);
        ctx.lineTo(nB.proj.x, nB.proj.y);
        ctx.strokeStyle = 'rgba(192, 132, 252, ' + linkAlpha + ')';
        ctx.lineWidth = 0.75;
        ctx.stroke();
      }
      ctx.restore();
    }

    // Render Individual Floating Network Node Sphere
    renderNode(nodeItem) {
      const ctx = this.ctx;
      const np = nodeItem.proj;
      if (np.scale <= 0) return;

      const r = nodeItem.config.nodeRadius * np.scale;
      ctx.save();

      // Outer Glow Halo
      ctx.beginPath();
      ctx.arc(np.x, np.y, r * 1.6, 0, Math.PI * 2);
      ctx.fillStyle = nodeItem.config.colorA;
      ctx.globalAlpha = 0.22;
      ctx.shadowColor = nodeItem.config.colorA;
      ctx.shadowBlur = 10;
      ctx.fill();

      // Frosted Glass Node Body
      const nodeGrad = ctx.createRadialGradient(
        np.x - r * 0.3, np.y - r * 0.3, r * 0.1,
        np.x, np.y, r
      );
      nodeGrad.addColorStop(0, '#FFFFFF');
      nodeGrad.addColorStop(0.35, nodeItem.config.colorA);
      nodeGrad.addColorStop(0.85, nodeItem.config.colorB);
      nodeGrad.addColorStop(1, 'rgba(15, 7, 32, 0.85)');

      ctx.beginPath();
      ctx.arc(np.x, np.y, r, 0, Math.PI * 2);
      ctx.fillStyle = nodeGrad;
      ctx.globalAlpha = 0.95;
      ctx.shadowColor = nodeItem.config.colorB;
      ctx.shadowBlur = 8;
      ctx.fill();

      // Razor-Thin Specular Rim
      ctx.strokeStyle = '#FFFFFF';
      ctx.lineWidth = 1.0;
      ctx.globalAlpha = 0.85;
      ctx.stroke();

      ctx.restore();
    }

    // Render Streaming Photons (Executive Energy Pulses)
    renderPulses(projCore, nodes) {
      const ctx = this.ctx;
      ctx.save();
      for (let i = 0; i < this.pulses.length; i++) {
        const pulse = this.pulses[i];
        const targetNode = nodes[pulse.nodeIndex];
        if (!targetNode || targetNode.proj.scale <= 0) continue;

        const np = targetNode.proj;
        // Position on filament
        const px = projCore.x + (np.x - projCore.x) * pulse.t;
        const py = projCore.y + (np.y - projCore.y) * pulse.t;
        const pulseScale = projCore.scale + (np.scale - projCore.scale) * pulse.t;

        const pRadius = pulse.size * pulseScale;

        // Tail gradient along filament
        const tailX = px - (np.x - projCore.x) * (pulse.length * 0.5);
        const tailY = py - (np.y - projCore.y) * (pulse.length * 0.5);

        const pulseGrad = ctx.createLinearGradient(tailX, tailY, px, py);
        pulseGrad.addColorStop(0, 'rgba(0, 240, 255, 0)');
        pulseGrad.addColorStop(0.6, pulse.color);
        pulseGrad.addColorStop(1, '#FFFFFF');

        ctx.beginPath();
        ctx.moveTo(tailX, tailY);
        ctx.lineTo(px, py);
        ctx.strokeStyle = pulseGrad;
        ctx.lineWidth = 1.8 * pulseScale;
        ctx.shadowColor = pulse.color;
        ctx.shadowBlur = 6;
        ctx.stroke();

        // Bright Head Bead
        ctx.beginPath();
        ctx.arc(px, py, pRadius, 0, Math.PI * 2);
        ctx.fillStyle = '#FFFFFF';
        ctx.shadowColor = pulse.color;
        ctx.shadowBlur = 8;
        ctx.fill();
      }
      ctx.restore();
    }

    // Render Ambient Micro-Luminescence Motes
    renderMotes(rotX, rotY) {
      const ctx = this.ctx;
      ctx.save();
      for (let i = 0; i < this.motes.length; i++) {
        const m = this.motes[i];
        const pt = this.project(m.x, m.y, m.z, rotX, rotY);
        if (pt.scale > 0) {
          const r = m.radius * pt.scale;
          ctx.beginPath();
          ctx.arc(pt.x, pt.y, Math.max(0.6, r), 0, Math.PI * 2);
          ctx.fillStyle = m.color;
          ctx.globalAlpha = m.alpha * pt.scale;
          ctx.shadowColor = m.color;
          ctx.shadowBlur = 4;
          ctx.fill();
        }
      }
      ctx.restore();
    }
  }

  // Auto-initialize when DOM is ready
  function init() {
    // Supports either constellation wrap or ribbon wrap ID for seamless backward compatibility
    const wrap = document.getElementById('vg-pb-constellation-wrap') || document.getElementById('vg-pb-ribbon-wrap');
    if (wrap) {
      new PBExecutiveConstellation(wrap);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
