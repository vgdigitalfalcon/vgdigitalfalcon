/**
 * VG Digital Falcon - 3D Rotating Holographic Growth Sphere & Ascending Data Beams
 * High-Performance Interactive 60fps HTML5 2D Canvas 3D Projection Engine
 * Replaces old overlapping orbit text badges with a sleek, non-overlapping luxury 3D visual.
 */

(function () {
  'use strict';

  function initGrowthSphere() {
    var stageWrap = document.getElementById('vgDmStageWrap');
    if (!stageWrap) return;

    // Check prefers-reduced-motion
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      stageWrap.innerHTML = '<div class="vg-dm-static-glow"></div>';
      return;
    }

    var canvas = document.createElement('canvas');
    canvas.className = 'vg-dm-matrix-canvas';
    canvas.setAttribute('aria-hidden', 'true');
    stageWrap.innerHTML = '';
    stageWrap.appendChild(canvas);

    var ctx = canvas.getContext('2d');
    if (!ctx) return;

    var width = 0;
    var height = 0;
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var animationFrameId = null;
    var isVisible = true;

    var mouseX = 0;
    var mouseY = 0;
    var targetMouseX = 0;
    var targetMouseY = 0;

    var rotY = 0;
    var time = 0;

    // Sphere parameters
    var latBands = 10;
    var lonBands = 16;
    var pointsPerRing = 48;

    // Ascending 3D light beams (vertical data streams)
    var BEAM_COUNT = 42;
    var beams = [];
    for (var i = 0; i < BEAM_COUNT; i++) {
      var angle = Math.random() * Math.PI * 2;
      var radiusDist = 0.35 + Math.random() * 1.35; // Multiplier of sphere radius
      beams.push({
        angle: angle,
        radiusMult: radiusDist,
        x: 0,
        z: 0,
        y: (Math.random() - 0.5) * 600,
        speed: 1.2 + Math.random() * 2.2,
        length: 45 + Math.random() * 65,
        width: 1.0 + Math.random() * 1.2,
        isCyan: Math.random() > 0.45,
        pulseOffset: Math.random() * Math.PI * 2
      });
    }

    // Floating background ambient particles
    var PARTICLE_COUNT = 32;
    var ambientParticles = [];
    for (var pIdx = 0; pIdx < PARTICLE_COUNT; pIdx++) {
      ambientParticles.push({
        x: Math.random(),
        y: Math.random(),
        vx: (Math.random() - 0.5) * 0.0003,
        vy: -0.0002 - Math.random() * 0.0004,
        size: 1.0 + Math.random() * 1.8,
        alpha: 0.15 + Math.random() * 0.35,
        isCyan: Math.random() > 0.5
      });
    }

    function resize() {
      var rect = stageWrap.getBoundingClientRect();
      width = rect.width;
      height = rect.height;

      if (width === 0 || height === 0) {
        width = window.innerWidth;
        height = Math.max(window.innerHeight * 0.8, 560);
      }

      dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = Math.floor(width * dpr);
      canvas.height = Math.floor(height * dpr);
      canvas.style.width = width + 'px';
      canvas.style.height = height + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    window.addEventListener('resize', resize, { passive: true });
    resize();

    // Subtle parallax on mouse move
    window.addEventListener('mousemove', function (e) {
      var rect = stageWrap.getBoundingClientRect();
      if (rect.top <= window.innerHeight && rect.bottom >= 0) {
        targetMouseX = ((e.clientX - rect.left) / width - 0.5) * 35;
        targetMouseY = ((e.clientY - rect.top) / height - 0.5) * 25;
      }
    }, { passive: true });

    // Intersection observer to pause rendering when offscreen
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        isVisible = entries[0].isIntersecting;
        if (isVisible && !animationFrameId) {
          render();
        }
      }, { threshold: 0.05 });
      observer.observe(stageWrap);
    }

    function render() {
      if (!isVisible) {
        animationFrameId = null;
        return;
      }

      time += 0.018;
      rotY += 0.0042;
      mouseX += (targetMouseX - mouseX) * 0.06;
      mouseY += (targetMouseY - mouseY) * 0.06;

      ctx.clearRect(0, 0, width, height);

      // Centered with subtle mouse parallax
      var cx = width * 0.5 + mouseX;
      var cy = height * 0.5 + mouseY;

      // Responsive sphere radius
      var sphereRadius = Math.max(160, Math.min(width * 0.24, height * 0.36, 270));
      var focalLength = 650;

      // 3D rotation angles (continuous Y-axis spin + smooth forward isometric tilt)
      var rotX = 0.38 + mouseY * 0.003;
      var cosY = Math.cos(rotY);
      var sinY = Math.sin(rotY);
      var cosX = Math.cos(rotX);
      var sinX = Math.sin(rotX);

      // Perspective projection helper
      function project3D(x, y, z) {
        // Rotate around Y axis
        var x1 = x * cosY + z * sinY;
        var z1 = -x * sinY + z * cosY;

        // Rotate around X axis
        var y2 = y * cosX - z1 * sinX;
        var z2 = y * sinX + z1 * cosX;

        // Perspective scale factor
        var distance = focalLength + z2 + sphereRadius * 0.3;
        var scale = focalLength / Math.max(distance, 80);

        return {
          px: cx + x1 * scale,
          py: cy + y2 * scale,
          z: z2,
          scale: scale,
          // Depth factor: 0 (furthest back) to 1 (closest front)
          depth: Math.max(0, Math.min(1, (z2 + sphereRadius * 1.1) / (sphereRadius * 2.2)))
        };
      }

      // 1. Ambient Background Core Glow
      ctx.save();
      var coreGlow = ctx.createRadialGradient(cx, cy, 10, cx, cy, sphereRadius * 1.6);
      coreGlow.addColorStop(0, 'rgba(168, 85, 247, 0.16)');
      coreGlow.addColorStop(0.4, 'rgba(34, 211, 238, 0.06)');
      coreGlow.addColorStop(0.75, 'rgba(124, 58, 237, 0.02)');
      coreGlow.addColorStop(1, 'rgba(7, 2, 18, 0)');
      ctx.fillStyle = coreGlow;
      ctx.beginPath();
      ctx.arc(cx, cy, sphereRadius * 1.6, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();

      // 2. Ambient Floating Background Particles
      ctx.save();
      for (var p = 0; p < ambientParticles.length; p++) {
        var ap = ambientParticles[p];
        ap.x += ap.vx;
        ap.y += ap.vy;
        if (ap.x < 0) ap.x = 1;
        if (ap.x > 1) ap.x = 0;
        if (ap.y < 0) ap.y = 1;
        if (ap.y > 1) ap.y = 0;

        var pulse = 0.7 + Math.sin(time * 2 + p) * 0.3;
        ctx.fillStyle = ap.isCyan
          ? 'rgba(34, 211, 238, ' + (ap.alpha * pulse) + ')'
          : 'rgba(192, 132, 252, ' + (ap.alpha * pulse) + ')';
        ctx.beginPath();
        ctx.arc(ap.x * width, ap.y * height, ap.size, 0, Math.PI * 2);
        ctx.fill();
      }
      ctx.restore();

      // 3. Ascending 3D Vertical Light Beams (Rising data streams)
      ctx.save();
      for (var bIdx = 0; bIdx < beams.length; bIdx++) {
        var b = beams[bIdx];
        b.y -= b.speed;
        if (b.y < -sphereRadius * 1.8) {
          b.y = sphereRadius * 1.8;
          b.angle = Math.random() * Math.PI * 2;
          b.radiusMult = 0.4 + Math.random() * 1.35;
        }

        var bx = Math.cos(b.angle) * (sphereRadius * b.radiusMult);
        var bz = Math.sin(b.angle) * (sphereRadius * b.radiusMult);

        var head = project3D(bx, b.y, bz);
        var tail = project3D(bx, b.y + b.length, bz);

        if (head.scale <= 0 || tail.scale <= 0) continue;

        var beamAlpha = (0.2 + head.depth * 0.65) * (0.8 + Math.sin(time * 3 + b.pulseOffset) * 0.2);
        var grad = ctx.createLinearGradient(head.px, head.py, tail.px, tail.py);
        if (b.isCyan) {
          grad.addColorStop(0, 'rgba(34, 211, 238, ' + (beamAlpha * 0.9) + ')');
          grad.addColorStop(0.3, 'rgba(6, 182, 212, ' + (beamAlpha * 0.5) + ')');
          grad.addColorStop(1, 'rgba(6, 182, 212, 0)');
        } else {
          grad.addColorStop(0, 'rgba(192, 132, 252, ' + (beamAlpha * 0.9) + ')');
          grad.addColorStop(0.3, 'rgba(168, 85, 247, ' + (beamAlpha * 0.5) + ')');
          grad.addColorStop(1, 'rgba(168, 85, 247, 0)');
        }

        ctx.strokeStyle = grad;
        ctx.lineWidth = b.width * head.scale * (head.depth > 0.5 ? 1.4 : 0.85);
        ctx.beginPath();
        ctx.moveTo(head.px, head.py);
        ctx.lineTo(tail.px, tail.py);
        ctx.stroke();

        // Tip spark on ascending beams in front
        if (head.depth > 0.4) {
          ctx.fillStyle = b.isCyan ? '#ffffff' : '#f5d0fe';
          ctx.beginPath();
          ctx.arc(head.px, head.py, 1.2 * head.scale, 0, Math.PI * 2);
          ctx.fill();
        }
      }
      ctx.restore();

      // 4. 3D Rotating Holographic Sphere - Latitude Rings
      ctx.save();
      var latStep = (Math.PI * 0.84) / (latBands + 1);
      var startLat = -Math.PI * 0.42 + latStep;

      for (var latIdx = 0; latIdx < latBands; latIdx++) {
        var lat = startLat + latIdx * latStep;
        var ringRadius = sphereRadius * Math.cos(lat);
        var ringY = sphereRadius * Math.sin(lat);

        // Latitude color theme (cyan near top/equator, royal-violet toward base)
        var isUpper = latIdx >= latBands / 2;
        var colorBase = isUpper ? '34, 211, 238' : '168, 85, 247';

        // Sample points along the latitude ring
        var ringPoints = [];
        for (var j = 0; j <= pointsPerRing; j++) {
          var theta = (j / pointsPerRing) * Math.PI * 2;
          var rx = ringRadius * Math.cos(theta);
          var rz = ringRadius * Math.sin(theta);
          ringPoints.push(project3D(rx, ringY, rz));
        }

        // Draw segmented ring with depth-dependent alpha
        for (var k = 0; k < ringPoints.length - 1; k++) {
          var pt1 = ringPoints[k];
          var pt2 = ringPoints[k + 1];
          var avgDepth = (pt1.depth + pt2.depth) * 0.5;
          var latAlpha = 0.10 + avgDepth * 0.55;

          ctx.strokeStyle = 'rgba(' + colorBase + ', ' + latAlpha + ')';
          ctx.lineWidth = 0.7 + avgDepth * 0.9;
          ctx.beginPath();
          ctx.moveTo(pt1.px, pt1.py);
          ctx.lineTo(pt2.px, pt2.py);
          ctx.stroke();
        }
      }
      ctx.restore();

      // 5. 3D Rotating Holographic Sphere - Longitude Meridians
      ctx.save();
      for (var m = 0; m < lonBands; m++) {
        var lonAngle = (m / lonBands) * Math.PI * 2;
        var meridianPoints = [];

        for (var mj = 0; mj <= 28; mj++) {
          var mLat = -Math.PI * 0.48 + (mj / 28) * (Math.PI * 0.96);
          var mr = sphereRadius * Math.cos(mLat);
          var mY = sphereRadius * Math.sin(mLat);
          var mx = mr * Math.cos(lonAngle);
          var mz = mr * Math.sin(lonAngle);
          meridianPoints.push(project3D(mx, mY, mz));
        }

        for (var mk = 0; mk < meridianPoints.length - 1; mk++) {
          var mpt1 = meridianPoints[mk];
          var mpt2 = meridianPoints[mk + 1];
          var mAvgDepth = (mpt1.depth + mpt2.depth) * 0.5;
          var mAlpha = 0.08 + mAvgDepth * 0.50;

          // Alternate meridian tones for rich holographic shimmer
          var mColor = m % 2 === 0 ? '168, 85, 247' : '34, 211, 238';
          ctx.strokeStyle = 'rgba(' + mColor + ', ' + mAlpha + ')';
          ctx.lineWidth = 0.6 + mAvgDepth * 0.8;
          ctx.beginPath();
          ctx.moveTo(mpt1.px, mpt1.py);
          ctx.lineTo(mpt2.px, mpt2.py);
          ctx.stroke();
        }
      }
      ctx.restore();

      // 6. Glowing Vertex Intersections & Data Pulse Nodes
      ctx.save();
      for (var vb = 0; vb < latBands; vb += 2) {
        var vLat = startLat + vb * latStep;
        var vRingRadius = sphereRadius * Math.cos(vLat);
        var vRingY = sphereRadius * Math.sin(vLat);

        for (var vm = 0; vm < lonBands; vm += 2) {
          var vLonAngle = (vm / lonBands) * Math.PI * 2;
          var vx = vRingRadius * Math.cos(vLonAngle);
          var vz = vRingRadius * Math.sin(vLonAngle);
          var vProj = project3D(vx, vRingY, vz);

          // Only draw visible vertex dots facing towards front
          if (vProj.depth > 0.35) {
            var isCyanNode = (vb + vm) % 4 === 0;
            var nodeAlpha = (vProj.depth - 0.35) * 1.5;
            var vPulse = 0.8 + Math.sin(time * 3 + vb + vm) * 0.2;
            var dotRadius = (1.2 + vProj.depth * 1.6) * vPulse;

            ctx.fillStyle = isCyanNode
              ? 'rgba(34, 211, 238, ' + Math.min(1, nodeAlpha) + ')'
              : 'rgba(216, 180, 254, ' + Math.min(1, nodeAlpha) + ')';

            ctx.beginPath();
            ctx.arc(vProj.px, vProj.py, dotRadius, 0, Math.PI * 2);
            ctx.fill();
          }
        }
      }
      ctx.restore();

      // 7. Outer Holographic Equatorial Resonance Halo
      ctx.save();
      var haloRadius = sphereRadius * 1.24;
      var haloPoints = [];
      var haloTilt = -0.22; // Dynamic tilt relative to equator
      for (var hj = 0; hj <= pointsPerRing; hj++) {
        var hTheta = (hj / pointsPerRing) * Math.PI * 2 + rotY * 0.4;
        var hx = haloRadius * Math.cos(hTheta);
        var hz = haloRadius * Math.sin(hTheta);
        var hy = hx * Math.sin(haloTilt);
        haloPoints.push(project3D(hx, hy, hz));
      }

      for (var hk = 0; hk < haloPoints.length - 1; hk++) {
        var hpt1 = haloPoints[hk];
        var hpt2 = haloPoints[hk + 1];
        var hAvgDepth = (hpt1.depth + hpt2.depth) * 0.5;
        if (hAvgDepth > 0.25) {
          ctx.strokeStyle = 'rgba(168, 85, 247, ' + ((hAvgDepth - 0.25) * 0.5) + ')';
          ctx.lineWidth = 1.0;
          ctx.beginPath();
          ctx.moveTo(hpt1.px, hpt1.py);
          ctx.lineTo(hpt2.px, hpt2.py);
          ctx.stroke();
        }
      }
      ctx.restore();

      animationFrameId = requestAnimationFrame(render);
    }

    render();
  }

  
  // Interactive Funnel-Stage Filter Tabs for Service Pillars
  function initPillarFilters() {
    var filterTabs = document.querySelectorAll('.vg-dm-filter-tab');
    var bentoCards = document.querySelectorAll('.vg-dm-bento-card');
    if (!filterTabs.length || !bentoCards.length) return;

    filterTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var filter = this.getAttribute('data-filter');

        // Update active tab state and aria attributes
        filterTabs.forEach(function (t) {
          t.classList.remove('active');
          t.setAttribute('aria-selected', 'false');
        });
        this.classList.add('active');
        this.setAttribute('aria-selected', 'true');

        // Filter bento cards smoothly
        bentoCards.forEach(function (card) {
          var cardCategories = (card.getAttribute('data-category') || '').split(' ');
          if (filter === 'all' || cardCategories.indexOf(filter) !== -1) {
            card.classList.remove('is-filtered-out');
            card.classList.add('is-highlighted');
          } else {
            card.classList.add('is-filtered-out');
            card.classList.remove('is-highlighted');
          }
        });
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { initGrowthSphere(); initPillarFilters(); });
  } else {
    initGrowthSphere();
  }
})();
