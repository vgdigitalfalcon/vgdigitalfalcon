/**
 * ============================================================================
 * VG DIGITAL FALCON — SMO VIRAL PULSE & SOCIAL CONSTELLATION ENGINE
 * File: assets/js/smo-viral-pulse.js
 * 
 * Concept:
 * An interactive, high-performance HTML5 Canvas animation engine tailored
 * specifically for Social Media Optimization (SMO).
 * 
 * Features:
 * - Concentric viral resonance ripples radiating from influencer hubs
 * - Multi-platform social network nodes (Instagram, Facebook, YouTube, X, LinkedIn, WhatsApp)
 * - Luminous engagement packet streams (shares, likes, comments, retweets)
 * - Drifting social reaction micro-particles (hearts, thumbs-up, comment sparks)
 * - Interactive cursor magnetic resonance & ripple burst on click/move
 * - 60fps lightweight Canvas 2D with IntersectionObserver and prefers-reduced-motion compliance
 * ============================================================================
 */

(function () {
  'use strict';

  class SmoViralPulseEngine {
    constructor(container) {
      this.container = container;
      this.hero = container.closest('#smo-hero') || container.closest('.vg-smo-hero-section') || container.parentElement;

      this.canvas = document.createElement('canvas');
      this.canvas.className = 'vg-smo-canvas';
      this.canvas.setAttribute('aria-hidden', 'true');
      this.canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;z-index:0;pointer-events:none;';
      this.container.appendChild(this.canvas);
      this.ctx = this.canvas.getContext('2d', { alpha: true });

      if (!this.ctx) return;

      this.width = 0;
      this.height = 0;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);
      this.time = 0;
      this.animId = null;
      this.isIntersecting = true;
      this.isTabActive = true;
      this.prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      // Platform Identity Palette
      this.platforms = [
        { name: 'Instagram', color: '#E1306C', glow: 'rgba(225, 48, 108, 0.45)', icon: '♥' },
        { name: 'YouTube',   color: '#FF0000', glow: 'rgba(255, 0, 0, 0.4)',      icon: '▶' },
        { name: 'LinkedIn',  color: '#0A66C2', glow: 'rgba(10, 102, 194, 0.45)',  icon: 'in' },
        { name: 'Twitter',   color: '#00F0FF', glow: 'rgba(0, 240, 255, 0.45)',   icon: '✦' },
        { name: 'WhatsApp',  color: '#25D366', glow: 'rgba(37, 211, 102, 0.45)',  icon: '●' },
        { name: 'Facebook',  color: '#1877F2', glow: 'rgba(24, 119, 242, 0.45)',  icon: 'f' }
      ];

      // Social reaction emojis / symbols
      this.reactionSymbols = ['♥', '★', '💬', '⚡', '↗', '👍', '●'];

      // Entities
      this.nodes = [];
      this.packets = [];
      this.ripples = [];
      this.reactions = [];

      // Mouse Parallax & Interaction
      this.mouse = {
        x: -9999,
        y: -9999,
        targetX: -9999,
        targetY: -9999,
        isHovered: false
      };

      this.init();
    }

    init() {
      this.resize();
      this.buildNetwork();
      this.bindEvents();
      this.setupObserver();

      if (!this.prefersReducedMotion) {
        this.start();
      } else {
        this.renderStatic();
      }
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
    }

    buildNetwork() {
      this.nodes = [];
      this.packets = [];
      this.ripples = [];
      this.reactions = [];

      const isMobile = this.width < 768;
      const count = isMobile ? 14 : 26;

      // 1. Create primary platform hubs
      for (let i = 0; i < count; i++) {
        const platform = this.platforms[i % this.platforms.length];
        const isHub = i < 6;

        const marginX = this.width * 0.08;
        const marginY = this.height * 0.12;

        this.nodes.push({
          x: marginX + Math.random() * (this.width - marginX * 2),
          y: marginY + Math.random() * (this.height - marginY * 2),
          vx: (Math.random() - 0.5) * (isHub ? 0.35 : 0.6),
          vy: (Math.random() - 0.5) * (isHub ? 0.35 : 0.6),
          radius: isHub ? (isMobile ? 5 : 7) : (isMobile ? 2.5 : 4),
          baseRadius: isHub ? (isMobile ? 5 : 7) : (isMobile ? 2.5 : 4),
          platform: platform,
          isHub: isHub,
          pulsePhase: Math.random() * Math.PI * 2,
          pulseSpeed: 0.03 + Math.random() * 0.03,
          connections: []
        });
      }

      // Connect nodes within reach
      const maxDist = isMobile ? 120 : 180;
      const maxDistSq = maxDist * maxDist;

      for (let i = 0; i < this.nodes.length; i++) {
        for (let j = i + 1; j < this.nodes.length; j++) {
          const dx = this.nodes[i].x - this.nodes[j].x;
          const dy = this.nodes[i].y - this.nodes[j].y;
          const distSq = dx * dx + dy * dy;

          if (distSq < maxDistSq) {
            this.nodes[i].connections.push(j);

            // Add dynamic engagement packet along this link
            if (Math.random() > 0.4) {
              this.packets.push({
                fromIdx: i,
                toIdx: j,
                progress: Math.random(),
                speed: 0.004 + Math.random() * 0.008,
                color: this.nodes[i].platform.color,
                size: 2.2 + Math.random() * 1.5
              });
            }
          }
        }
      }

      // 2. Initial Viral Wave Ripples
      const rippleCount = isMobile ? 2 : 4;
      for (let i = 0; i < rippleCount; i++) {
        const hubNode = this.nodes[i % 6];
        this.ripples.push({
          x: hubNode.x,
          y: hubNode.y,
          radius: 10 + i * 35,
          maxRadius: 180 + Math.random() * 80,
          speed: 0.75 + Math.random() * 0.45,
          color: hubNode.platform.color,
          alpha: 0.6
        });
      }

      // 3. Floating Reaction Micro-particles
      const reactionCount = isMobile ? 8 : 16;
      for (let i = 0; i < reactionCount; i++) {
        this.reactions.push(this.createReactionParticle(false));
      }
    }

    createReactionParticle(bottomOnly) {
      return {
        x: Math.random() * this.width,
        y: bottomOnly ? this.height + 20 : Math.random() * this.height,
        vy: -0.4 - Math.random() * 0.6,
        vx: (Math.random() - 0.5) * 0.35,
        alpha: 0.15 + Math.random() * 0.4,
        size: 11 + Math.random() * 6,
        symbol: this.reactionSymbols[Math.floor(Math.random() * this.reactionSymbols.length)],
        color: this.platforms[Math.floor(Math.random() * this.platforms.length)].color,
        swayPhase: Math.random() * Math.PI * 2,
        swaySpeed: 0.02 + Math.random() * 0.02
      };
    }

    bindEvents() {
      window.addEventListener('resize', () => {
        this.resize();
        this.buildNetwork();
      }, { passive: true });

      if (this.hero) {
        this.hero.addEventListener('mousemove', (e) => {
          const rect = this.hero.getBoundingClientRect();
          this.mouse.targetX = e.clientX - rect.left;
          this.mouse.targetY = e.clientY - rect.top;
          this.mouse.isHovered = true;
        }, { passive: true });

        this.hero.addEventListener('mouseleave', () => {
          this.mouse.isHovered = false;
          this.mouse.targetX = -9999;
          this.mouse.targetY = -9999;
        }, { passive: true });

        // Click to spawn a viral ripple wave
        this.hero.addEventListener('click', (e) => {
          const rect = this.hero.getBoundingClientRect();
          const clickX = e.clientX - rect.left;
          const clickY = e.clientY - rect.top;

          this.ripples.push({
            x: clickX,
            y: clickY,
            radius: 5,
            maxRadius: 260,
            speed: 2.2,
            color: '#00F0FF',
            alpha: 0.8
          });

          // Also burst 4 reactions from click location
          for (let i = 0; i < 4; i++) {
            this.reactions.push({
              x: clickX + (Math.random() - 0.5) * 20,
              y: clickY + (Math.random() - 0.5) * 20,
              vy: -1.2 - Math.random() * 1.0,
              vx: (Math.random() - 0.5) * 1.5,
              alpha: 0.8,
              size: 14 + Math.random() * 5,
              symbol: this.reactionSymbols[Math.floor(Math.random() * this.reactionSymbols.length)],
              color: '#C084FC',
              swayPhase: Math.random() * Math.PI * 2,
              swaySpeed: 0.04
            });
          }
        }, { passive: true });
      }

      document.addEventListener('visibilitychange', () => {
        this.isTabActive = !document.hidden;
        if (this.isTabActive && this.isIntersecting && !this.animId && !this.prefersReducedMotion) {
          this.start();
        } else if (!this.isTabActive && this.animId) {
          this.stop();
        }
      });
    }

    setupObserver() {
      if ('IntersectionObserver' in window && this.hero) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            this.isIntersecting = entry.isIntersecting;
            if (this.isIntersecting && this.isTabActive && !this.animId && !this.prefersReducedMotion) {
              this.start();
            } else if (!this.isIntersecting && this.animId) {
              this.stop();
            }
          });
        }, { threshold: 0.05 });
        observer.observe(this.hero);
      }
    }

    start() {
      if (this.animId) return;
      const loop = () => {
        this.update();
        this.render();
        this.animId = requestAnimationFrame(loop);
      };
      this.animId = requestAnimationFrame(loop);
    }

    stop() {
      if (this.animId) {
        cancelAnimationFrame(this.animId);
        this.animId = null;
      }
    }

    update() {
      this.time += 0.016;

      // Smooth mouse lerp
      if (this.mouse.isHovered) {
        this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.12;
        this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.12;
      } else {
        this.mouse.x = -9999;
        this.mouse.y = -9999;
      }

      // 1. Update Nodes & Gentle Drift
      for (let i = 0; i < this.nodes.length; i++) {
        const node = this.nodes[i];
        node.x += node.vx;
        node.y += node.vy;

        // Bounce boundaries
        if (node.x < 15 || node.x > this.width - 15) node.vx *= -1;
        if (node.y < 15 || node.y > this.height - 15) node.vy *= -1;

        // Pulse phase
        node.pulsePhase += node.pulseSpeed;

        // Mouse magnetic attraction
        if (this.mouse.isHovered) {
          const dx = this.mouse.x - node.x;
          const dy = this.mouse.y - node.y;
          const distSq = dx * dx + dy * dy;
          if (distSq < 16000 && distSq > 100) {
            const force = (1 - distSq / 16000) * 0.5;
            node.x += dx * force * 0.05;
            node.y += dy * force * 0.05;
          }
        }
      }

      // 2. Update Packets
      for (let i = 0; i < this.packets.length; i++) {
        const p = this.packets[i];
        p.progress += p.speed;
        if (p.progress >= 1) {
          p.progress = 0;
          // Reverse direction occasionally
          const temp = p.fromIdx;
          p.fromIdx = p.toIdx;
          p.toIdx = temp;
        }
      }

      // 3. Update Viral Resonance Ripples
      for (let i = this.ripples.length - 1; i >= 0; i--) {
        const r = this.ripples[i];
        r.radius += r.speed;
        r.alpha = Math.max(0, 0.6 * (1 - r.radius / r.maxRadius));

        if (r.radius >= r.maxRadius) {
          // Recycle natural ripples
          const hubNode = this.nodes[Math.floor(Math.random() * Math.min(6, this.nodes.length))];
          r.x = hubNode.x;
          r.y = hubNode.y;
          r.radius = 4;
          r.color = hubNode.platform.color;
        }
      }

      // 4. Update Reactions
      for (let i = this.reactions.length - 1; i >= 0; i--) {
        const rx = this.reactions[i];
        rx.y += rx.vy;
        rx.swayPhase += rx.swaySpeed;
        rx.x += Math.sin(rx.swayPhase) * 0.45;

        if (rx.y < -30) {
          // Recycle
          Object.assign(rx, this.createReactionParticle(true));
        }
      }
    }

    render() {
      this.ctx.clearRect(0, 0, this.width, this.height);

      const isMobile = this.width < 768;
      const maxConnectDist = isMobile ? 120 : 180;
      const maxDistSq = maxConnectDist * maxConnectDist;

      // 1. Draw Viral Resonance Waves (Concentric Rings)
      for (let i = 0; i < this.ripples.length; i++) {
        const r = this.ripples[i];
        if (r.alpha <= 0.01) continue;

        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.arc(r.x, r.y, r.radius, 0, Math.PI * 2);
        this.ctx.strokeStyle = r.color;
        this.ctx.globalAlpha = r.alpha * 0.4;
        this.ctx.lineWidth = 1.4;
        this.ctx.stroke();

        // Inner soft echo ring
        if (r.radius > 20) {
          this.ctx.beginPath();
          this.ctx.arc(r.x, r.y, r.radius * 0.7, 0, Math.PI * 2);
          this.ctx.strokeStyle = r.color;
          this.ctx.globalAlpha = r.alpha * 0.2;
          this.ctx.lineWidth = 1;
          this.ctx.stroke();
        }
        this.ctx.restore();
      }

      // 2. Draw Network Lines between connected nodes
      for (let i = 0; i < this.nodes.length; i++) {
        const n1 = this.nodes[i];
        for (let k = 0; k < n1.connections.length; k++) {
          const j = n1.connections[k];
          const n2 = this.nodes[j];

          const dx = n1.x - n2.x;
          const dy = n1.y - n2.y;
          const distSq = dx * dx + dy * dy;

          if (distSq < maxDistSq) {
            const ratio = 1 - Math.sqrt(distSq) / maxConnectDist;
            const lineAlpha = ratio * 0.25;

            this.ctx.save();
            const grad = this.ctx.createLinearGradient(n1.x, n1.y, n2.x, n2.y);
            grad.addColorStop(0, n1.platform.color);
            grad.addColorStop(1, n2.platform.color);

            this.ctx.beginPath();
            this.ctx.moveTo(n1.x, n1.y);
            this.ctx.lineTo(n2.x, n2.y);
            this.ctx.strokeStyle = grad;
            this.ctx.globalAlpha = lineAlpha;
            this.ctx.lineWidth = ratio * 1.5;
            this.ctx.stroke();
            this.ctx.restore();
          }
        }
      }

      // 3. Draw Traveling Engagement Packets
      for (let i = 0; i < this.packets.length; i++) {
        const p = this.packets[i];
        const n1 = this.nodes[p.fromIdx];
        const n2 = this.nodes[p.toIdx];
        if (!n1 || !n2) continue;

        const px = n1.x + (n2.x - n1.x) * p.progress;
        const py = n1.y + (n2.y - n1.y) * p.progress;

        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.arc(px, py, p.size, 0, Math.PI * 2);
        this.ctx.fillStyle = p.color;
        this.ctx.shadowColor = p.color;
        this.ctx.shadowBlur = 8;
        this.ctx.globalAlpha = 0.85;
        this.ctx.fill();
        this.ctx.restore();
      }

      // 4. Draw Nodes with Glowing Auras
      for (let i = 0; i < this.nodes.length; i++) {
        const node = this.nodes[i];
        const pulse = 1 + Math.sin(node.pulsePhase) * 0.22;
        const radius = node.radius * pulse;

        this.ctx.save();

        // Outer Aura
        const auraGrad = this.ctx.createRadialGradient(node.x, node.y, 0, node.x, node.y, radius * 3.5);
        auraGrad.addColorStop(0, node.platform.color);
        auraGrad.addColorStop(1, 'transparent');
        this.ctx.beginPath();
        this.ctx.arc(node.x, node.y, radius * 3.5, 0, Math.PI * 2);
        this.ctx.fillStyle = auraGrad;
        this.ctx.globalAlpha = node.isHub ? 0.35 : 0.18;
        this.ctx.fill();

        // Core Solid Node
        this.ctx.beginPath();
        this.ctx.arc(node.x, node.y, radius, 0, Math.PI * 2);
        this.ctx.fillStyle = node.platform.color;
        this.ctx.shadowColor = node.platform.color;
        this.ctx.shadowBlur = node.isHub ? 14 : 7;
        this.ctx.globalAlpha = 0.95;
        this.ctx.fill();

        // Inner Bright Nucleus
        this.ctx.beginPath();
        this.ctx.arc(node.x, node.y, radius * 0.45, 0, Math.PI * 2);
        this.ctx.fillStyle = '#FFFFFF';
        this.ctx.globalAlpha = 0.9;
        this.ctx.fill();

        this.ctx.restore();
      }

      // 5. Draw Drifting Social Reaction Micro-particles
      this.ctx.save();
      this.ctx.textAlign = 'center';
      this.ctx.textBaseline = 'middle';
      for (let i = 0; i < this.reactions.length; i++) {
        const rx = this.reactions[i];
        this.ctx.font = `600 ${rx.size}px "Plus Jakarta Sans", sans-serif`;
        this.ctx.fillStyle = rx.color;
        this.ctx.shadowColor = rx.color;
        this.ctx.shadowBlur = 6;
        this.ctx.globalAlpha = rx.alpha;
        this.ctx.fillText(rx.symbol, rx.x, rx.y);
      }
      this.ctx.restore();
    }

    renderStatic() {
      this.ctx.clearRect(0, 0, this.width, this.height);
      // Static graceful rendering for reduced motion
      for (let i = 0; i < this.nodes.length; i++) {
        const n = this.nodes[i];
        this.ctx.beginPath();
        this.ctx.arc(n.x, n.y, n.radius, 0, Math.PI * 2);
        this.ctx.fillStyle = n.platform.color;
        this.ctx.globalAlpha = 0.6;
        this.ctx.fill();
      }
    }
  }

  // Initialize engine on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', function () {
    const stage = document.getElementById('vgSmoStageWrap');
    if (stage) {
      new SmoViralPulseEngine(stage);
    }
  });

})();
