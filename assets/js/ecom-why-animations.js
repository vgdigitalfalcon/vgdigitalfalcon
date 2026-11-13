/**
 * VG Digital Falcon - Ecommerce SEO Why Section Looping Micro-Animations
 * Standalone, lightweight, high-performance 60fps animation engine
 * Handles:
 * 1. Product Page SEO: SERP #1 Climbing Product Card & 5-Star Schema Twinkle
 * 2. Technical Platform SEO: Looping Neon Core Web Vitals Speed Gauge (38 -> 99/100)
 * 3. Ethical Authority: Cyber Violet Backlink Network & Verified Authority Shield
 */

(function () {
  'use strict';

  function initWhyAnimations() {
    var whySection = document.querySelector('.vg-seo-why-section');
    if (!whySection) return;

    var speedCard = whySection.querySelector('.vg-pillar-speed-card');
    var serpCard = whySection.querySelector('.vg-pillar-serp-card');
    var authCard = whySection.querySelector('.vg-pillar-auth-card');

    var isVisible = false;
    var rafId = null;

    // --- 1. Speed Gauge Controller ---
    var gaugeFillArc = whySection.querySelector('.vg-gauge-fill-arc');
    var gaugeNumEl = whySection.querySelector('.vg-gauge-num');
    var gaugeStateBadge = whySection.querySelector('.vg-gauge-state-badge');
    var gaugeStateText = whySection.querySelector('.vg-gauge-state-text');
    var gaugeStateIcon = whySection.querySelector('.vg-gauge-state-icon');
    var vitalPills = whySection.querySelectorAll('.vg-vital-val');

    // Arc math: circumference of radius 50 arc (180 deg) is pi * 50 = 157.08
    var TOTAL_ARC = 157.08;
    var MIN_SCORE = 38;
    var MAX_SCORE = 99;

    function getArcOffset(score) {
      // score: 0 to 100
      var fraction = Math.min(Math.max(score / 100, 0), 1);
      return TOTAL_ARC * (1 - fraction);
    }

    // Ease-out cubic
    function easeOutCubic(t) {
      return 1 - Math.pow(1 - t, 3);
    }

    var speedCycleDuration = 6800; // ms
    var speedCycleStartTime = null;

    function updateSpeedGauge(timestamp) {
      if (!speedCycleStartTime) speedCycleStartTime = timestamp;
      var elapsed = (timestamp - speedCycleStartTime) % speedCycleDuration;

      var currentScore = MIN_SCORE;
      var isFast = false;
      var isSurging = false;

      if (elapsed < 1200) {
        // Phase 1: Sluggish/Slow state (37 - 39)
        var jitter = Math.sin(elapsed / 100) * 0.8;
        currentScore = Math.round(MIN_SCORE + jitter);
        isFast = false;
        isSurging = false;
      } else if (elapsed < 3000) {
        // Phase 2: Rapid surge from 38 up to 99
        var progress = (elapsed - 1200) / 1800;
        var eased = easeOutCubic(progress);
        currentScore = Math.round(MIN_SCORE + (MAX_SCORE - MIN_SCORE) * eased);
        isSurging = true;
        isFast = currentScore >= 90;
      } else if (elapsed < 5800) {
        // Phase 3: Pinned at 99 with glowing peak state
        currentScore = MAX_SCORE;
        isFast = true;
        isSurging = false;
      } else {
        // Phase 4: Smooth rewind to restart
        var resetProgress = (elapsed - 5800) / 1000;
        var rewindEase = resetProgress * resetProgress;
        currentScore = Math.round(MAX_SCORE - (MAX_SCORE - MIN_SCORE) * rewindEase);
        isFast = false;
        isSurging = false;
      }

      // Update Arc offset
      if (gaugeFillArc) {
        var offset = getArcOffset(currentScore);
        gaugeFillArc.style.strokeDashoffset = offset.toFixed(2);
      }

      // Update Numeric Score
      if (gaugeNumEl) {
        gaugeNumEl.textContent = currentScore;
      }

      // Update State Badge & Telemetry
      if (gaugeStateBadge && gaugeStateText) {
        if (isFast) {
          gaugeStateBadge.classList.add('is-fast');
          gaugeStateBadge.classList.remove('is-poor', 'is-surging');
          gaugeStateText.textContent = 'LIGHTNING FAST';
          if (gaugeStateIcon) gaugeStateIcon.className = 'fa-solid fa-bolt me-1 text-cyan vg-gauge-state-icon';
        } else if (isSurging) {
          gaugeStateBadge.classList.add('is-surging');
          gaugeStateBadge.classList.remove('is-fast', 'is-poor');
          gaugeStateText.textContent = 'OPTIMIZING ENGINE...';
          if (gaugeStateIcon) gaugeStateIcon.className = 'fa-solid fa-arrows-rotate fa-spin me-1 text-cyan vg-gauge-state-icon';
        } else {
          gaugeStateBadge.classList.add('is-poor');
          gaugeStateBadge.classList.remove('is-fast', 'is-surging');
          gaugeStateText.textContent = 'POOR • LCP 4.2s';
          if (gaugeStateIcon) gaugeStateIcon.className = 'fa-solid fa-triangle-exclamation me-1 text-danger vg-gauge-state-icon';
        }
      }

      // Vital pills color toggle
      if (vitalPills && vitalPills.length >= 3) {
        for (var i = 0; i < vitalPills.length; i++) {
          if (isFast) {
            vitalPills[i].classList.add('is-green');
            vitalPills[i].classList.remove('is-warn');
          } else {
            vitalPills[i].classList.remove('is-green');
            vitalPills[i].classList.add('is-warn');
          }
        }
      }
    }

    // --- 2. SERP Rank #1 Climb Controller ---
    var serpListing = whySection.querySelector('.vg-serp-hero-listing');
    var serpRankPill = whySection.querySelector('.vg-hero-rank-pill');
    var serpRankText = whySection.querySelector('.vg-rank-text');
    var serpRankIcon = whySection.querySelector('.vg-rank-icon');
    var dummyRow2 = whySection.querySelector('.vg-dummy-2');

    var serpCycleDuration = 6800; // ms
    var serpCycleStartTime = null;

    function updateSerpClimb(timestamp) {
      if (!serpListing) return;
      if (!serpCycleStartTime) serpCycleStartTime = timestamp - 800; // Staggered start
      var elapsed = (timestamp - serpCycleStartTime) % serpCycleDuration;

      // Card translation distance: from bottom (+48px) to top (0px)
      var maxOffset = 52; // px
      var currentY = maxOffset;
      var isRankOne = false;

      if (elapsed < 1000) {
        // Bottom position (Rank #3)
        currentY = maxOffset;
        isRankOne = false;
      } else if (elapsed < 2600) {
        // Smoothly glides up past #2 to #1
        var climbProgress = (elapsed - 1000) / 1600;
        var climbEased = easeOutCubic(climbProgress);
        currentY = maxOffset * (1 - climbEased);
        isRankOne = climbProgress > 0.85;
      } else if (elapsed < 5600) {
        // Locked at #1 spot with gold aura and star pulse
        currentY = 0;
        isRankOne = true;
      } else {
        // Smooth reset back down
        var resetProgress = (elapsed - 5600) / 1200;
        currentY = maxOffset * (resetProgress * resetProgress);
        isRankOne = false;
      }

      serpListing.style.transform = 'translateY(' + currentY.toFixed(2) + 'px)';

      if (isRankOne) {
        serpListing.classList.add('is-rank-1');
        if (serpRankPill) serpRankPill.classList.add('is-gold');
        if (serpRankText) serpRankText.textContent = '#1 Rank';
        if (serpRankIcon) serpRankIcon.className = 'fa-solid fa-crown me-1 text-gold vg-rank-icon';
        if (dummyRow2) dummyRow2.style.opacity = '0.35';
      } else {
        serpListing.classList.remove('is-rank-1');
        if (serpRankPill) serpRankPill.classList.remove('is-gold');
        if (serpRankText) serpRankText.textContent = '#3 Spot';
        if (serpRankIcon) serpRankIcon.className = 'fa-solid fa-arrow-up me-1 text-cyan vg-rank-icon';
        if (dummyRow2) dummyRow2.style.opacity = '0.7';
      }
    }

    // --- Main Animation Loop ---
    function animationLoop(timestamp) {
      if (!isVisible) return;
      updateSpeedGauge(timestamp);
      updateSerpClimb(timestamp);
      rafId = requestAnimationFrame(animationLoop);
    }

    // --- Intersection Observer for High Performance ---
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              if (!isVisible) {
                isVisible = true;
                rafId = requestAnimationFrame(animationLoop);
              }
            } else {
              isVisible = false;
              if (rafId) {
                cancelAnimationFrame(rafId);
                rafId = null;
              }
            }
          });
        },
        { threshold: 0.15 }
      );
      observer.observe(whySection);
    } else {
      isVisible = true;
      rafId = requestAnimationFrame(animationLoop);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWhyAnimations);
  } else {
    initWhyAnimations();
  }
})();

