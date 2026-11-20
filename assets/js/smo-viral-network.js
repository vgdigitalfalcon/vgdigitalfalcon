/**
 * ============================================================================
 * VG DIGITAL FALCON — VIRAL SPREAD NETWORK (SMO HERO SOCIAL GRAPH ANIMATION)
 * File: assets/js/smo-viral-network.js
 * 
 * Concept:
 * An authentic, lightweight HTML5 2D Canvas social graph visualizing viral
 * content distribution. Circular avatar-nodes drift gently across dark space,
 * connected by dynamic network lines. Every 2.5 seconds, a viral cascade triggers
 * expanding concentric ripple rings and traveling light packets that illuminate
 * receiving nodes and propagate a secondary viral sharing wave.
 * ============================================================================
 */

(function () {
  'use strict';

  function initViralNetwork() {
    var canvas = document.getElementById('vg-smo-viral-canvas');
    var hero = document.getElementById('smo-hero') || (canvas ? canvas.parentElement : null);
    if (!canvas || !hero) return;

    var ctx = canvas.getContext('2d');
    if (!ctx) return;

    var width = 0;
    var height = 0;
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var animFrameId = null;
    var isVisible = true;

    // Platform accent palette: Instagram Pink, LinkedIn Blue, YouTube Red
    var PLATFORMS = [
      { name: 'instagram', hex: '#EC4899', rgb: [236, 72, 153] },
      { name: 'linkedin',  hex: '#3B82F6', rgb: [59, 130, 246] },
      { name: 'youtube',   hex: '#EF4444', rgb: [239, 68, 68] }
    ];

    var NODE_COUNT = 26; // 22 to 28 circular avatar-nodes
    var MAX_LINE_DIST = 160;
    var nodes = [];
    var ripples = [];
    var pulses = [];
    var lastCascadeTime = performance.now();
    var CASCADE_INTERVAL = 2500; // 2.5 seconds

    function resize() {
      var rect = hero.getBoundingClientRect();
      width = rect.width || hero.offsetWidth || window.innerWidth;
      height = rect.height || hero.offsetHeight || 600;
      if (width <= 0 || height <= 0) return;

      canvas.width = Math.floor(width * dpr);
      canvas.height = Math.floor(height * dpr);
      canvas.style.width = width + 'px';
      canvas.style.height = height + 'px';
      ctx.setTransform(1, 0, 0, 1, 0, 0);
      ctx.scale(dpr, dpr);
    }

    function createNode(index) {
      var platform = PLATFORMS[index % PLATFORMS.length];
      var baseR = 11 + Math.random() * 3; // 11 to 14px radius
      return {
        x: Math.random() * (width || 800),
        y: Math.random() * (height || 500),
        vx: (Math.random() - 0.5) * 0.32,
        vy: (Math.random() - 0.5) * 0.28,
        baseRadius: baseR,
        platform: platform,
        ambientOpacity: 0.32 + Math.random() * 0.08, // ~30-40% ambient opacity
        pulseIntensity: 0
      };
    }

    function initNodes() {
      nodes = [];
      for (var i = 0; i < NODE_COUNT; i++) {
        nodes.push(createNode(i));
      }
    }

    function dist(n1, n2) {
      var dx = n1.x - n2.x;
      var dy = n1.y - n2.y;
      return Math.sqrt(dx * dx + dy * dy);
    }

    function getNeighbors(node) {
      var neighbors = [];
      for (var i = 0; i < nodes.length; i++) {
        var other = nodes[i];
        if (other !== node && dist(node, other) < MAX_LINE_DIST) {
          neighbors.push(other);
        }
      }
      return neighbors;
    }

    function triggerCascade(sourceNode, wave) {
      if (!sourceNode) return;
      wave = wave || 1;

      // Light up the source node
      sourceNode.pulseIntensity = 1.0;

      // Concentric ripples (2 staggered rings)
      ripples.push({
        x: sourceNode.x,
        y: sourceNode.y,
        color: sourceNode.platform.rgb,
        currentR: sourceNode.baseRadius,
        maxR: wave === 1 ? 92 : 60,
        alpha: 0.75,
        speed: wave === 1 ? 1.6 : 1.3
      });
      ripples.push({
        x: sourceNode.x,
        y: sourceNode.y,
        color: sourceNode.platform.rgb,
        currentR: Math.max(2, sourceNode.baseRadius - 10),
        maxR: wave === 1 ? 76 : 50,
        alpha: 0.55,
        speed: wave === 1 ? 1.35 : 1.1
      });

      // Find connected neighbors and send traveling light pulses
      var neighbors = getNeighbors(sourceNode);
      if (wave === 1) {
        // Wave 1 sends pulses to up to 4 connected neighbors
        var count = Math.min(neighbors.length, 4);
        for (var i = 0; i < count; i++) {
          pulses.push({
            from: sourceNode,
            to: neighbors[i],
            progress: 0,
            speed: 0.022 + Math.random() * 0.008,
            color: sourceNode.platform.rgb,
            wave: 1
          });
        }
      } else {
        // Wave 2 sends to up to 2 further neighbors
        var count2 = Math.min(neighbors.length, 2);
        for (var j = 0; j < count2; j++) {
          pulses.push({
            from: sourceNode,
            to: neighbors[j],
            progress: 0,
            speed: 0.026 + Math.random() * 0.008,
            color: sourceNode.platform.rgb,
            wave: 2
          });
        }
      }
    }

    function drawAvatarSilhouette(x, y, r, rgb, alpha, pulse) {
      // 1. Dark glass circular body
      ctx.beginPath();
      ctx.arc(x, y, r, 0, Math.PI * 2);
      ctx.fillStyle = 'rgba(14, 9, 30, ' + (0.72 + pulse * 0.25) + ')';
      ctx.fill();

      // Border with platform accent color
      var strokeAlpha = Math.min(0.35 + pulse * 0.65, 1.0);
      ctx.strokeStyle = 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',' + strokeAlpha + ')';
      ctx.lineWidth = pulse > 0.1 ? 2.0 : 1.3;
      ctx.stroke();

      // Ambient / pulse glow
      if (pulse > 0.08) {
        ctx.save();
        ctx.shadowColor = 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',' + pulse + ')';
        ctx.shadowBlur = 14 * pulse;
        ctx.strokeStyle = 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',' + (pulse * 0.8) + ')';
        ctx.stroke();
        ctx.restore();
      }

      // 2. Miniature user silhouette icon inside (clipped to inner circle)
      ctx.save();
      ctx.beginPath();
      ctx.arc(x, y, r - 1, 0, Math.PI * 2);
      ctx.clip();

      var iconAlpha = Math.min(alpha + pulse * 0.5, 0.95);
      ctx.fillStyle = 'rgba(' + rgb[0] + ',' + rgb[1] + ',' + rgb[2] + ',' + iconAlpha + ')';

      // Silhouette Head
      var headRadius = r * 0.28;
      var headY = y - r * 0.22;
      ctx.beginPath();
      ctx.arc(x, headY, headRadius, 0, Math.PI * 2);
      ctx.fill();

      // Silhouette Body / Shoulders
      var bodyRadius = r * 0.48;
      var bodyY = y + r * 0.52;
      ctx.beginPath();
      ctx.arc(x, bodyY, bodyRadius, Math.PI, 0, false);
      ctx.fill();

      ctx.restore();
    }

    function update() {
      if (width <= 50 || height <= 50) return;

      // 1. Update node positions & decay pulses
      for (var i = 0; i < nodes.length; i++) {
        var n = nodes[i];
        n.x += n.vx;
        n.y += n.vy;

        // Soft bounce at boundaries
        var margin = 20;
        if (n.x < margin) { n.x = margin; n.vx *= -1; }
        else if (n.x > width - margin) { n.x = width - margin; n.vx *= -1; }
        if (n.y < margin) { n.y = margin; n.vy *= -1; }
        else if (n.y > height - margin) { n.y = height - margin; n.vy *= -1; }

        // Pulse intensity smooth decay
        if (n.pulseIntensity > 0.01) {
          n.pulseIntensity *= 0.95;
        } else {
          n.pulseIntensity = 0;
        }
      }

      // 2. Update ripples
      for (var rIdx = ripples.length - 1; rIdx >= 0; rIdx--) {
        var rip = ripples[rIdx];
        rip.currentR += rip.speed;
        rip.alpha = (1 - rip.currentR / rip.maxR) * 0.75;
        if (rip.currentR >= rip.maxR || rip.alpha <= 0) {
          ripples.splice(rIdx, 1);
        }
      }

      // 3. Update traveling light pulses
      for (var pIdx = pulses.length - 1; pIdx >= 0; pIdx--) {
        var p = pulses[pIdx];
        p.progress += p.speed;
        if (p.progress >= 1.0) {
          // Pulse reached receiving node!
          var targetNode = p.to;
          targetNode.pulseIntensity = 1.0;

          // If Wave 1, propagate a second wave outward like real social sharing!
          if (p.wave === 1) {
            triggerCascade(targetNode, 2);
          }

          pulses.splice(pIdx, 1);
        }
      }

      // 4. Cascade timer: trigger every 2.5s
      var now = performance.now();
      if (now - lastCascadeTime >= CASCADE_INTERVAL) {
        lastCascadeTime = now;
        if (nodes.length > 0) {
          var randomSeed = nodes[Math.floor(Math.random() * nodes.length)];
          triggerCascade(randomSeed, 1);
        }
      }
    }

    function render() {
      if (width <= 50 || height <= 50) return;
      ctx.clearRect(0, 0, width, height);

      // 1. Draw Network Lines between connected nodes
      for (var i = 0; i < nodes.length; i++) {
        for (var j = i + 1; j < nodes.length; j++) {
          var n1 = nodes[i];
          var n2 = nodes[j];
          var d = dist(n1, n2);
          if (d < MAX_LINE_DIST) {
            var factor = 1 - (d / MAX_LINE_DIST);
            var lineAlpha = factor * 0.14;

            // Illuminate line if connected nodes are pulsing
            var pulseBonus = Math.max(n1.pulseIntensity, n2.pulseIntensity);
            if (pulseBonus > 0.05) {
              lineAlpha = Math.min(lineAlpha + pulseBonus * 0.32, 0.6);
            }

            ctx.beginPath();
            ctx.moveTo(n1.x, n1.y);
            ctx.lineTo(n2.x, n2.y);
            ctx.strokeStyle = 'rgba(168, 85, 247, ' + lineAlpha + ')';
            ctx.lineWidth = pulseBonus > 0.2 ? 1.5 : 1.0;
            ctx.stroke();
          }
        }
      }

      // 2. Draw Concentric Ripples
      for (var rIdx = 0; rIdx < ripples.length; rIdx++) {
        var rip = ripples[rIdx];
        if (rip.alpha <= 0) continue;
        ctx.beginPath();
        ctx.arc(rip.x, rip.y, rip.currentR, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(' + rip.color[0] + ',' + rip.color[1] + ',' + rip.color[2] + ',' + rip.alpha + ')';
        ctx.lineWidth = 1.6;
        ctx.stroke();
      }

      // 3. Draw Traveling Light Packets (Signal Pulses)
      for (var pIdx = 0; pIdx < pulses.length; pIdx++) {
        var p = pulses[pIdx];
        var curX = p.from.x + (p.to.x - p.from.x) * p.progress;
        var curY = p.from.y + (p.to.y - p.from.y) * p.progress;

        ctx.save();
        ctx.shadowColor = 'rgba(' + p.color[0] + ',' + p.color[1] + ',' + p.color[2] + ', 0.9)';
        ctx.shadowBlur = 10;

        // Outer glow halo
        ctx.beginPath();
        ctx.arc(curX, curY, 5.5, 0, Math.PI * 2);
        ctx.fillStyle = 'rgba(' + p.color[0] + ',' + p.color[1] + ',' + p.color[2] + ', 0.55)';
        ctx.fill();

        // Bright core packet
        ctx.beginPath();
        ctx.arc(curX, curY, 2.5, 0, Math.PI * 2);
        ctx.fillStyle = '#ffffff';
        ctx.fill();

        ctx.restore();
      }

      // 4. Draw Circular Avatar-Nodes
      for (var nIdx = 0; nIdx < nodes.length; nIdx++) {
        var node = nodes[nIdx];
        var r = node.baseRadius + node.pulseIntensity * 2.2;
        drawAvatarSilhouette(node.x, node.y, r, node.platform.rgb, node.ambientOpacity, node.pulseIntensity);
      }
    }

    function loop() {
      if (isVisible) {
        update();
        render();
      }
      animFrameId = requestAnimationFrame(loop);
    }

    // Interactive Trigger: clicking near any node cascades from it
    hero.addEventListener('click', function (e) {
      var rect = hero.getBoundingClientRect();
      var clickX = e.clientX - rect.left;
      var clickY = e.clientY - rect.top;
      var closest = null;
      var minDist = 120;
      for (var i = 0; i < nodes.length; i++) {
        var d = Math.hypot(nodes[i].x - clickX, nodes[i].y - clickY);
        if (d < minDist) {
          minDist = d;
          closest = nodes[i];
        }
      }
      if (closest) {
        triggerCascade(closest, 1);
      }
    });

    // Visibility Observer: pause when off-screen
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        isVisible = entries[0].isIntersecting;
      }, { threshold: 0.05 });
      observer.observe(hero);
    }

    document.addEventListener('visibilitychange', function () {
      isVisible = !document.hidden;
    });

    // Resize listener with passive throttle
    var resizeTimer = null;
    window.addEventListener('resize', function () {
      if (resizeTimer) clearTimeout(resizeTimer);
      resizeTimer = setTimeout(function () {
        resize();
      }, 100);
    }, { passive: true });

    window.addEventListener('load', function () {
      resize();
    });

    // Initialize
    resize();
    initNodes();
    loop();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initViralNetwork);
  } else {
    initViralNetwork();
  }

// Interactive Step Pills & Social Feed Post Cards Sync
  function initFeedCardInteractions() {
    var pills = document.querySelectorAll('.vg-smo-step-pill[data-step]');
    var cards = document.querySelectorAll('.vg-smo-feed-card[data-step-card]');
    if (!pills.length || !cards.length) return;

    function activateStep(stepNum) {
      pills.forEach(function(p) {
        if (p.getAttribute('data-step') === stepNum) {
          p.classList.add('active');
        } else {
          p.classList.remove('active');
        }
      });
      cards.forEach(function(c) {
        if (c.getAttribute('data-step-card') === stepNum) {
          c.classList.add('vg-feed-highlight');
        } else {
          c.classList.remove('vg-feed-highlight');
        }
      });
    }

    pills.forEach(function(pill) {
      var step = pill.getAttribute('data-step');
      pill.addEventListener('mouseenter', function() {
        activateStep(step);
      });
      pill.addEventListener('click', function() {
        activateStep(step);
        var targetCard = document.querySelector('.vg-smo-feed-card[data-step-card="' + step + '"]');
        if (targetCard && window.innerWidth < 992) {
          targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
      });
    });

    // Interactive Heart Reaction
    document.querySelectorAll('.vg-feed-action-btn.like-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        var icon = btn.querySelector('i');
        var countSpan = btn.querySelector('span');
        var isLiked = btn.classList.toggle('liked');
        if (icon) {
          if (isLiked) {
            icon.style.color = '#EC4899';
            icon.style.transform = 'scale(1.25)';
            setTimeout(function() { icon.style.transform = 'scale(1)'; }, 200);
          } else {
            icon.style.color = '';
          }
        }
      });
    });

    // Interactive Bookmark
    document.querySelectorAll('.vg-feed-action-btn.bookmark-btn').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        var icon = btn.querySelector('i');
        var isSaved = btn.classList.toggle('saved');
        if (icon) {
          if (isSaved) {
            icon.className = 'fa-solid fa-bookmark';
            icon.style.color = '#F59E0B';
          } else {
            icon.className = 'fa-regular fa-bookmark';
            icon.style.color = '';
          }
        }
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFeedCardInteractions);
  } else {
    initFeedCardInteractions();
  }
})();