/**
 * VG Digital Falcon - Omnichannel Digital Marketing Growth Matrix Engine
 * High-Performance Interactive 60fps Canvas Animation
 * Visualizes SEO, PPC, SMO, Content, Web UX, and Analytics converging into ROI.
 */

(function () {
  'use strict';

  function initGrowthMatrix() {
    const stageWrap = document.getElementById('vgDmStageWrap');
    if (!stageWrap) return;

    // Check prefers-reduced-motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      stageWrap.innerHTML = '<div class="vg-dm-static-glow"></div>';
      return;
    }

    const canvas = document.createElement('canvas');
    canvas.className = 'vg-dm-matrix-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    stageWrap.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let width = 0;
    let height = 0;
    let dpr = Math.min(window.devicePixelRatio || 1, 2);
    let animationFrameId = null;
    let isVisible = true;

    let mouseX = 0;
    let mouseY = 0;
    let targetMouseX = 0;
    let targetMouseY = 0;

    // Channels in the omnichannel matrix
    const channels = [
      { name: 'SEO', icon: '🔍', color: '#06b6d4', glow: 'rgba(6, 182, 212, 0.4)', angle: 0, distance: 180, speed: 0.008, stat: '+320% Organic' },
      { name: 'PPC', icon: '🎯', color: '#10b981', glow: 'rgba(16, 185, 129, 0.4)', angle: (Math.PI * 2) / 6, distance: 160, speed: 0.009, stat: '4.8x ROAS' },
      { name: 'SMO', icon: '⚡', color: '#c084fc', glow: 'rgba(192, 132, 252, 0.4)', angle: (Math.PI * 4) / 6, distance: 190, speed: 0.007, stat: '50K+ Leads' },
      { name: 'Content', icon: '📝', color: '#f59e0b', glow: 'rgba(245, 158, 11, 0.4)', angle: (Math.PI * 6) / 6, distance: 170, speed: 0.0085, stat: 'Top Authority' },
      { name: 'Web UX', icon: '💻', color: '#38bdf8', glow: 'rgba(56, 189, 248, 0.4)', angle: (Math.PI * 8) / 6, distance: 175, speed: 0.0075, stat: 'Sub-second Speed' },
      { name: 'Analytics', icon: '📊', color: '#ec4899', glow: 'rgba(236, 72, 153, 0.4)', angle: (Math.PI * 10) / 6, distance: 185, speed: 0.008, stat: '100% Attribution' }
    ];

    // Flying data packets
    const packets = [];
    for (let i = 0; i < 24; i++) {
      packets.push({
        channelIndex: Math.floor(Math.random() * channels.length),
        progress: Math.random(),
        speed: 0.008 + Math.random() * 0.007,
        direction: Math.random() > 0.3 ? 1 : -1, // mostly inward
        size: 2 + Math.random() * 2
      });
    }

    // Background floating particles
    const bgParticles = [];
    for (let i = 0; i < 40; i++) {
      bgParticles.push({
        x: Math.random(),
        y: Math.random(),
        vx: (Math.random() - 0.5) * 0.0004,
        vy: (Math.random() - 0.5) * 0.0004,
        size: 1 + Math.random() * 2,
        alpha: 0.15 + Math.random() * 0.3
      });
    }

    function resize() {
      const rect = stageWrap.getBoundingClientRect();
      width = rect.width;
      height = rect.height;

      if (width === 0 || height === 0) {
        width = window.innerWidth;
        height = Math.max(window.innerHeight * 0.75, 520);
      }

      dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = width * dpr;
      canvas.height = height * dpr;
      canvas.style.width = width + 'px';
      canvas.style.height = height + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    window.addEventListener('resize', resize, { passive: true });
    resize();

    // Mouse movement interaction
    window.addEventListener('mousemove', function (e) {
      const rect = canvas.getBoundingClientRect();
      targetMouseX = ((e.clientX - rect.left) / width - 0.5) * 40;
      targetMouseY = ((e.clientY - rect.top) / height - 0.5) * 40;
    }, { passive: true });

    // Intersection observer to throttle offscreen rendering
    const observer = new IntersectionObserver(function (entries) {
      isVisible = entries[0].isIntersecting;
      if (isVisible && !animationFrameId) {
        render();
      }
    }, { threshold: 0.05 });
    observer.observe(stageWrap);

    let time = 0;

    function render() {
      if (!isVisible) {
        animationFrameId = null;
        return;
      }

      time += 0.02;
      mouseX += (targetMouseX - mouseX) * 0.05;
      mouseY += (targetMouseY - mouseY) * 0.05;

      ctx.clearRect(0, 0, width, height);

      const centerX = width * 0.5 + mouseX;
      const centerY = height * 0.48 + mouseY;
      const baseRadius = Math.min(width, height) * 0.28;

      // 1. Draw Subtle Background Cyber Grid
      ctx.save();
      ctx.strokeStyle = 'rgba(168, 85, 247, 0.04)';
      ctx.lineWidth = 1;
      const gridSize = 45;
      const offsetX = (time * 8) % gridSize;
      for (let x = offsetX; x < width; x += gridSize) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, height);
        ctx.stroke();
      }
      for (let y = 0; y < height; y += gridSize) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(width, y);
        ctx.stroke();
      }
      ctx.restore();

      // 2. Background floating particles
      ctx.save();
      for (let i = 0; i < bgParticles.length; i++) {
        const p = bgParticles[i];
        p.x += p.vx;
        p.y += p.vy;
        if (p.x < 0) p.x = 1;
        if (p.x > 1) p.x = 0;
        if (p.y < 0) p.y = 1;
        if (p.y > 1) p.y = 0;

        ctx.fillStyle = `rgba(192, 132, 252, ${p.alpha * (0.6 + Math.sin(time + i) * 0.4)})`;
        ctx.beginPath();
        ctx.arc(p.x * width, p.y * height, p.size, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();

      // 3. Central Core Halo & Orbit Rings
      ctx.save();
      const ringGlow = ctx.createRadialGradient(centerX, centerY, 20, centerX, centerY, baseRadius * 1.35);
      ringGlow.addColorStop(0, 'rgba(124, 58, 237, 0.22)');
      ringGlow.addColorStop(0.5, 'rgba(6, 182, 212, 0.08)');
      ringGlow.addColorStop(1, 'rgba(7, 2, 18, 0)');
      ctx.fillStyle = ringGlow;
      ctx.beginPath();
      ctx.arc(centerX, centerY, baseRadius * 1.35, 0, Math.PI * 2);
      ctx.fill();

      // Orbital circles
      ctx.strokeStyle = 'rgba(168, 85, 247, 0.18)';
      ctx.setLineDash([4, 6]);
      ctx.lineWidth = 1.2;
      ctx.beginPath();
      ctx.arc(centerX, centerY, baseRadius * 0.85, 0, Math.PI * 2);
      ctx.stroke();

      ctx.strokeStyle = 'rgba(6, 182, 212, 0.15)';
      ctx.setLineDash([6, 8]);
      ctx.beginPath();
      ctx.arc(centerX, centerY, baseRadius * 1.15, 0, Math.PI * 2);
      ctx.stroke();
      ctx.setLineDash([]);
      ctx.restore();

      // 4. Calculate Satellite Positions
      const nodePositions = [];
      for (let i = 0; i < channels.length; i++) {
        const ch = channels[i];
        ch.angle += ch.speed;
        const currentDistance = (ch.distance / 180) * baseRadius;
        const nx = centerX + Math.cos(ch.angle) * currentDistance;
        const ny = centerY + Math.sin(ch.angle) * currentDistance * 0.72; // Isometric tilt
        nodePositions.push({ x: nx, y: ny, ch: ch });
      }

      // 5. Connecting Flow Circuits from Core to Nodes
      ctx.save();
      for (let i = 0; i < nodePositions.length; i++) {
        const pos = nodePositions[i];
        const grad = ctx.createLinearGradient(centerX, centerY, pos.x, pos.y);
        grad.addColorStop(0, 'rgba(192, 132, 252, 0.5)');
        grad.addColorStop(1, pos.ch.color);

        ctx.strokeStyle = grad;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
      }
      ctx.restore();

      // 6. Animate Flowing Data Packets
      ctx.save();
      for (let i = 0; i < packets.length; i++) {
        const pkt = packets[i];
        pkt.progress += pkt.speed;
        if (pkt.progress > 1) {
          pkt.progress = 0;
          pkt.channelIndex = Math.floor(Math.random() * channels.length);
        }

        const targetNode = nodePositions[pkt.channelIndex];
        let px, py;
        if (pkt.direction === 1) {
          // Channel to Core
          px = targetNode.x + (centerX - targetNode.x) * pkt.progress;
          py = targetNode.y + (centerY - targetNode.y) * pkt.progress;
        } else {
          // Core to Channel
          px = centerX + (targetNode.x - centerX) * pkt.progress;
          py = centerY + (targetNode.y - centerY) * pkt.progress;
        }

        ctx.fillStyle = '#ffffff';
        ctx.shadowColor = targetNode.ch.color;
        ctx.shadowBlur = 10;
        ctx.beginPath();
        ctx.arc(px, py, pkt.size, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();

      // 7. Central Falcon Growth Nexus (The Core)
      ctx.save();
      const corePulse = 36 + Math.sin(time * 2.5) * 4;

      // Outer Core Glow
      const coreGrad = ctx.createRadialGradient(centerX, centerY, 5, centerX, centerY, corePulse + 18);
      coreGrad.addColorStop(0, '#ffffff');
      coreGrad.addColorStop(0.35, '#38bdf8');
      coreGrad.addColorStop(0.7, '#7c3aed');
      coreGrad.addColorStop(1, 'rgba(7, 2, 18, 0)');

      ctx.fillStyle = coreGrad;
      ctx.beginPath();
      ctx.arc(centerX, centerY, corePulse + 18, 0, Math.PI * 2);
      ctx.fill();

      // Solid Core Disk
      ctx.fillStyle = '#070212';
      ctx.beginPath();
      ctx.arc(centerX, centerY, corePulse, 0, Math.PI * 2);
      ctx.fill();
      ctx.strokeStyle = '#38bdf8';
      ctx.lineWidth = 2.5;
      ctx.stroke();

      // Core Branding Glyph
      ctx.fillStyle = '#ffffff';
      ctx.font = '700 13px "Plus Jakarta Sans", sans-serif';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText('VG NEXUS', centerX, centerY - 6);
      ctx.fillStyle = '#38bdf8';
      ctx.font = '600 9px "Plus Jakarta Sans", sans-serif';
      ctx.fillText('AI ENGINE', centerX, centerY + 8);
      ctx.restore();

      // 8. Render Channel Satellites
      for (let i = 0; i < nodePositions.length; i++) {
        const node = nodePositions[i];
        const ch = node.ch;

        ctx.save();
        // Satellite Glow
        ctx.shadowColor = ch.color;
        ctx.shadowBlur = 14;

        // Satellite Circle
        ctx.fillStyle = '#0b1329';
        ctx.beginPath();
        ctx.arc(node.x, node.y, 22, 0, Math.PI * 2);
        ctx.fill();

        ctx.strokeStyle = ch.color;
        ctx.lineWidth = 2;
        ctx.stroke();

        // Node Label
        ctx.shadowBlur = 0;
        ctx.fillStyle = '#ffffff';
        ctx.font = '700 11px "Plus Jakarta Sans", sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(ch.name, node.x, node.y - 1);

        // Subtext Stat Badge below node
        ctx.fillStyle = 'rgba(7, 2, 18, 0.85)';
        ctx.beginPath();
        const statWidth = ctx.measureText(ch.stat).width + 12;
        ctx.roundRect(node.x - statWidth / 2, node.y + 26, statWidth, 18, 6);
        ctx.fill();
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
        ctx.lineWidth = 1;
        ctx.stroke();

        ctx.fillStyle = ch.color;
        ctx.font = '600 9.5px "Plus Jakarta Sans", sans-serif';
        ctx.fillText(ch.stat, node.x, node.y + 35);

        ctx.restore();
      }

      animationFrameId = requestAnimationFrame(render);
    }

    render();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGrowthMatrix);
  } else {
    initGrowthMatrix();
  }
})();
