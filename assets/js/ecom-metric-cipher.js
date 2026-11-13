/**
 * ============================================================================
 * VG DIGITAL FALCON — AI LASER SCAN & CIPHER DECRYPT METRICS ENGINE
 * File: assets/js/ecom-metric-cipher.js
 * 
 * Features:
 * 1. Laser Scan & Cipher Decrypt:
 *    - Detects scroll into view via IntersectionObserver.
 *    - An Electric Cyan (#00F0FF) laser beam sweeps across each card.
 *    - High-speed cipher decryption scrambles cyber glyphs into final crisp metrics
 *      (250, 3,200, 1.2M+, 3X).
 *    - Locks in with a Cyber Violet (#A855F7) glow flash.
 * 2. 3D Glass Perspective Tilt & Interactive Specular Glare:
 *    - Real-time 3D cursor tracking with dynamic tilt (rotateX, rotateY) and
 *      specular glare spotlight on mouse movement.
 *    - Smooth spring-back on mouse leave.
 * ============================================================================
 */

(function () {
  'use strict';

  const GLYPHS = '0123456789ABCDEF#@%&<>X$/!*+';

  function initMetricsCipher() {
    const section = document.querySelector('.vg-ecom-stats-section');
    if (!section) return;

    const cards = section.querySelectorAll('.vg-ecom-counter-card');
    let hasTriggered = false;

    // Laser Scan & Cipher Decryption Runner
    function runCipherDecryption(card, index) {
      const valueEl = card.querySelector('.vg-ecom-cipher-val');
      if (!valueEl) return;

      const targetText = card.getAttribute('data-cipher-target') || valueEl.textContent.trim();
      const targetLen = targetText.length;
      const duration = 750; // ms
      const startTime = performance.now();

      // Trigger Laser Beam Sweep across card
      card.classList.add('is-scanning');

      function updateCipher(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Calculate how many characters are locked in from left to right
        const lockedCount = Math.floor(progress * targetLen);

        let output = '';
        for (let i = 0; i < targetLen; i++) {
          if (i < lockedCount || progress >= 1) {
            output += targetText[i];
          } else {
            // Random cipher glyph
            output += GLYPHS[Math.floor(Math.random() * GLYPHS.length)];
          }
        }

        valueEl.textContent = output;

        if (progress < 1) {
          requestAnimationFrame(updateCipher);
        } else {
          // Lock-in Complete: Set final text and trigger Cyber Violet Glow Flash!
          valueEl.textContent = targetText;
          card.classList.remove('is-scanning');
          card.classList.add('is-locked');
          valueEl.classList.add('is-flash');

          setTimeout(() => {
            valueEl.classList.remove('is-flash');
          }, 800);
        }
      }

      requestAnimationFrame(updateCipher);
    }

    // IntersectionObserver to trigger when section enters viewport
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !hasTriggered) {
            hasTriggered = true;
            cards.forEach((card, index) => {
              setTimeout(() => {
                runCipherDecryption(card, index);
              }, index * 180); // Stagger cards: 0ms, 180ms, 360ms, 540ms
            });
            observer.unobserve(section);
          }
        });
      }, {
        threshold: 0.25,
        rootMargin: '0px 0px -40px 0px'
      });

      observer.observe(section);
    } else {
      // Fallback
      cards.forEach((card, index) => {
        setTimeout(() => runCipherDecryption(card, index), index * 180);
      });
    }

    // 3D Glass Perspective Tilt & Interactive Glare Tracking
    cards.forEach(card => {
      const glare = card.querySelector('.vg-card-glass-glare');

      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const xNorm = (x / rect.width) - 0.5; // -0.5 to 0.5
        const yNorm = (y / rect.height) - 0.5;

        // 3D Tilt Angle (max ~12 deg)
        const tiltX = -yNorm * 12;
        const tiltY = xNorm * 12;

        card.style.transform = `perspective(800px) rotateX(${tiltX.toFixed(2)}deg) rotateY(${tiltY.toFixed(2)}deg) translateY(-5px) scale3d(1.02, 1.02, 1.02)`;

        if (glare) {
          glare.style.opacity = '1';
          glare.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(0, 240, 255, 0.22) 0%, rgba(168, 85, 247, 0.08) 45%, transparent 70%)`;
        }
      }, { passive: true });

      card.addEventListener('mouseleave', () => {
        card.style.transform = 'perspective(800px) rotateX(0deg) rotateY(0deg) translateY(0) scale3d(1, 1, 1)';
        if (glare) {
          glare.style.opacity = '0';
        }
      }, { passive: true });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMetricsCipher);
  } else {
    initMetricsCipher();
  }
})();
