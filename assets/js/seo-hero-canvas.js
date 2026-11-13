/**
 * ============================================================================
 * VG DIGITAL FALCON — SEO HERO 3D SEARCH & BACKLINK NODE NETWORK
 * File: assets/js/seo-hero-canvas.js
 * 
 * Concept:
 * An interactive, lightweight 3D constellation mesh representing search crawling,
 * backlink nodes, and organic indexing. Glowing Cyan & Neon Violet nodes connected
 * by dynamic light beams with crawling signal packets and subtle cursor parallax.
 * Strictly scoped inside .vg-seo-hero (SEO Optimization page only).
 * ============================================================================
 */

(function () {
  'use strict';

  class SeoBacklinkNodeNetwork {
    constructor(canvas) {
      this.canvas = canvas;
      this.ctx = canvas.getContext('2d', { alpha: true });
      if (!this.ctx) return;

      this.hero = canvas.closest('.vg-seo-hero') || canvas.parentElement;
      this.dpr = Math.min(window.devicePixelRatio || 1, 2);

      // Colors
      this.cyan = { r: 56, g: 189, b: 248 };     // #38BDF8 (Electric Cyan)
      this.violet = { r: 168, g: 85, b: 247 };  // #A855F7 (Soft Neon Violet)

      // Animation parameters
      this.fov = 420;
      this.maxConnectDist = 145; // Max link distance
      this.maxConnectDistSq = this.maxConnectDist * this.maxConnectDist;
      this.nodeCount = 55;

      // State
      this.width = 0;
      this.height = 0;
      this.nodes = [];
      this.packets = []; // Crawl signal packets traveling across links
      this.time = 0;
      this.animId = null;
      this.isPaused = false;
      this.isIntersecting = true;
      this.prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      // Mouse & Parallax
      this.mouse = { x: 0, y: 0, targetX: 0, targetY: 0 };
      this.rot = { x: 0, y: 0, targetX: 0, targetY: 0 };

      this.init();
    }

    init() {
      this.resize();
      this.buildNodes();
      this.bindEvents();
      this.setupObserver();
      this.start();
    }

    resize() {
      if (!this.hero) return;
      this.width = this.hero.clientWidth || window.innerWidth;
      this.height = this.hero.clientHeight || 700;

      this.canvas.width = this.width * this.dpr;
      this.canvas.height = this.height * this.dpr;
      this.canvas.style.width = this.width + 'px';
      this.canvas.style.height = this.height + 'px';

      this.ctx.setTransform(1, 0, 0, 1, 0, 0);
      this.ctx.scale(this.dpr, this.dpr);

      // Responsive node density
      if (this.width < 768) {
        this.nodeCount = 32;
        this.maxConnectDist = 110;
      } else if (this.width < 1200) {
        this.nodeCount = 48;
        this.maxConnectDist = 135;
      } else {
        this.nodeCount = 60;
        this.maxConnectDist = 155;
      }
      this.maxConnectDistSq = this.maxConnectDist * this.maxConnectDist;

      // Re-populate if node count changed
      if (this.nodes.length !== this.nodeCount) {
        this.buildNodes();
      }
    }

    buildNodes() {
      this.nodes = [];
      this.packets = [];

      const spreadX = this.width * 1.1;
      const spreadY = this.height * 1.1;
      const spreadZ = 360;

      for (let i = 0; i < this.nodeCount; i++) {
        const isHub = Math.random() < 0.22; // ~22% are Authority Hub nodes
        const isCyan = Math.random() > 0.45;

        this.nodes.push({
          x: (Math.random() - 0.5) * spreadX,
          y: (Math.random() - 0.5) * spreadY,
          z: (Math.random() - 0.5) * spreadZ,
          vx: (Math.random() - 0.5) * 0.35,
          vy: (Math.random() - 0.5) * 0.35,
          vz: (Math.random() - 0.5) * 0.25,
          radius: isHub ? (3.2 + Math.random() * 1.6) : (1.8 + Math.random() * 1.2),
          isHub,
          color: isCyan ? this.cyan : this.violet,
          pulse: Math.random() * Math.PI * 2,
          pulseSpeed: 0.025 + Math.random() * 0.03,
          projX: 0,
          projY: 0,
          scale: 1,
          alpha: 1
        });
      }
    }

    bindEvents() {
      // Resize with debounce
      let resizeTimeout = null;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => this.resize(), 100);
      }, { passive: true });

      // Parallax mouse tracker on Hero section
      if (this.hero) {
        this.hero.addEventListener('mousemove', (e) => {
          const rect = this.hero.getBoundingClientRect();
          const normX = (e.clientX - rect.left) / rect.width - 0.5;
          const normY = (e.clientY - rect.top) / rect.height - 0.5;
          this.rot.targetY = normX * 0.32; // subtle horizontal tilt
          this.rot.targetX = -normY * 0.24; // subtle vertical tilt
        }, { passive: true });

        this.hero.addEventListener('mouseleave', () => {
          this.rot.targetX = 0;
          this.rot.targetY = 0;
        }, { passive: true });
      }
    }

    setupObserver() {
      // Pause render loop when scrolled off-screen to save 60fps GPU/battery
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
        }, { threshold: 0.05 });
        observer.observe(this.hero);
      }

      // Tab visibility change listener: pause loop when tab is hidden
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          this.stop();
        } else if (this.isIntersecting && !this.prefersReducedMotion) {
          this.start();
        }
      });

      // Reduced motion listener
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

    spawnPacket(sourceIdx, targetIdx) {
      if (this.packets.length >= 6) return;
      this.packets.push({
        source: sourceIdx,
        target: targetIdx,
        progress: 0,
        speed: 0.015 + Math.random() * 0.02
      });
    }

    update() {
      this.time += 0.02;

      // Smooth camera rotation interpolation (LERP)
      this.rot.x += (this.rot.targetX - this.rot.x) * 0.05;
      this.rot.y += (this.rot.targetY - this.rot.y) * 0.05;

      const cosY = Math.cos(this.rot.y);
      const sinY = Math.sin(this.rot.y);
      const cosX = Math.cos(this.rot.x);
      const sinX = Math.sin(this.rot.x);

      const cx = this.width / 2;
      const cy = this.height / 2;

      const boundX = this.width * 0.6;
      const boundY = this.height * 0.6;
      const boundZ = 200;

      // Update nodes
      for (let i = 0; i < this.nodes.length; i++) {
        const n = this.nodes[i];

        // Drift motion
        n.x += n.vx;
        n.y += n.vy;
        n.z += n.vz;

        // Soft bounce boundaries
        if (n.x < -boundX) { n.x = -boundX; n.vx *= -1; }
        if (n.x > boundX) { n.x = boundX; n.vx *= -1; }
        if (n.y < -boundY) { n.y = -boundY; n.vy *= -1; }
        if (n.y > boundY) { n.y = boundY; n.vy *= -1; }
        if (n.z < -boundZ) { n.z = -boundZ; n.vz *= -1; }
        if (n.z > boundZ) { n.z = boundZ; n.vz *= -1; }

        // Pulse
        n.pulse += n.pulseSpeed;

        // 3D Rotation (Y-axis then X-axis)
        const rx1 = n.x * cosY + n.z * sinY;
        const rz1 = -n.x * sinY + n.z * cosY;
        const ry2 = n.y * cosX - rz1 * sinX;
        const rz2 = n.y * sinX + rz1 * cosX;

        // Perspective Projection
        const fov = this.fov;
        const distance = fov + rz2;
        if (distance > 10) {
          n.scale = fov / distance;
          n.projX = cx + rx1 * n.scale;
          n.projY = cy + ry2 * n.scale;
          // Depth alpha fade (faded when deep in background)
          n.alpha = Math.min(Math.max((rz2 + 250) / 450, 0.25), 0.85);
        } else {
          n.scale = 0;
        }
      }

      // Update crawl packets
      for (let p = this.packets.length - 1; p >= 0; p--) {
        const pkt = this.packets[p];
        pkt.progress += pkt.speed;
        if (pkt.progress >= 1) {
          this.packets.splice(p, 1);
        }
      }
    }

    render() {
      const ctx = this.ctx;
      ctx.clearRect(0, 0, this.width, this.height);

      const nodes = this.nodes;
      const count = nodes.length;

      // 1. Draw Connecting Beams (Backlink Mesh)
      ctx.lineWidth = 1;
      for (let i = 0; i < count; i++) {
        const a = nodes[i];
        if (a.scale <= 0) continue;

        for (let j = i + 1; j < count; j++) {
          const b = nodes[j];
          if (b.scale <= 0) continue;

          const dx = a.projX - b.projX;
          const dy = a.projY - b.projY;
          const distSq = dx * dx + dy * dy;

          if (distSq < this.maxConnectDistSq) {
            const dist = Math.sqrt(distSq);
            const factor = 1 - dist / this.maxConnectDist;
            const lineAlpha = factor * 0.32 * Math.min(a.alpha, b.alpha);

            if (lineAlpha > 0.02) {
              // Soft gradient beam between connected nodes
              const grad = ctx.createLinearGradient(a.projX, a.projY, b.projX, b.projY);
              grad.addColorStop(0, `rgba(${a.color.r}, ${a.color.g}, ${a.color.b}, ${lineAlpha * 0.9})`);
              grad.addColorStop(1, `rgba(${b.color.r}, ${b.color.g}, ${b.color.b}, ${lineAlpha * 0.9})`);

              ctx.strokeStyle = grad;
              ctx.beginPath();
              ctx.moveTo(a.projX, a.projY);
              ctx.lineTo(b.projX, b.projY);
              ctx.stroke();

              // Chance to spawn a crawler signal packet
              if (Math.random() < 0.0008 && this.packets.length < 5) {
                this.spawnPacket(i, j);
              }
            }
          }
        }
      }

      // 2. Draw Crawl Packets (Googlebot Crawl / Data Signals)
      for (let p = 0; p < this.packets.length; p++) {
        const pkt = this.packets[p];
        const a = nodes[pkt.source];
        const b = nodes[pkt.target];
        if (!a || !b || a.scale <= 0 || b.scale <= 0) continue;

        const curX = a.projX + (b.projX - a.projX) * pkt.progress;
        const curY = a.projY + (b.projY - a.projY) * pkt.progress;
        const pktRadius = 2.4 * ((a.scale + b.scale) / 2);

        // Bright electric cyan pulse
        ctx.fillStyle = 'rgba(56, 189, 248, 0.95)';
        ctx.shadowColor = '#38BDF8';
        ctx.shadowBlur = 8;
        ctx.beginPath();
        ctx.arc(curX, curY, pktRadius, 0, Math.PI * 2);
        ctx.fill();
        ctx.shadowBlur = 0; // reset
      }

      // 3. Draw Nodes (Authority Hubs & Keywords)
      for (let i = 0; i < count; i++) {
        const n = nodes[i];
        if (n.scale <= 0) continue;

        const r = n.radius * n.scale * (1 + Math.sin(n.pulse) * 0.12);
        const alpha = n.alpha * 0.70; // 50% - 70% opacity range as requested

        // Outer glow halo for Hubs
        if (n.isHub) {
          const haloRadius = r * 3.2;
          const haloGrad = ctx.createRadialGradient(n.projX, n.projY, r * 0.5, n.projX, n.projY, haloRadius);
          haloGrad.addColorStop(0, `rgba(${n.color.r}, ${n.color.g}, ${n.color.b}, ${alpha * 0.55})`);
          haloGrad.addColorStop(1, `rgba(${n.color.r}, ${n.color.g}, ${n.color.b}, 0)`);

          ctx.fillStyle = haloGrad;
          ctx.beginPath();
          ctx.arc(n.projX, n.projY, haloRadius, 0, Math.PI * 2);
          ctx.fill();
        }

        // Core Node
        ctx.fillStyle = `rgba(${n.color.r}, ${n.color.g}, ${n.color.b}, ${alpha})`;
        ctx.beginPath();
        ctx.arc(n.projX, n.projY, r, 0, Math.PI * 2);
        ctx.fill();

        // Bright node center pinpoint
        if (n.isHub || r > 2.2) {
          ctx.fillStyle = `rgba(255, 255, 255, ${alpha * 0.85})`;
          ctx.beginPath();
          ctx.arc(n.projX, n.projY, r * 0.42, 0, Math.PI * 2);
          ctx.fill();
        }
      }
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
  function initSeoHeroCanvas() {
    const canvas = document.getElementById('vg-seo-hero-canvas');
    if (!canvas) return;

    if (!canvas._seoNetworkInstance) {
      canvas._seoNetworkInstance = new SeoBacklinkNodeNetwork(canvas);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSeoHeroCanvas);
  } else {
    initSeoHeroCanvas();
  }

  // Export to window
  window.VG_SeoHeroNetwork = SeoBacklinkNodeNetwork;
})();

