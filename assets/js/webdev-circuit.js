/**
 * ============================================================================
 * VG DIGITAL FALCON — ENGINEERING DEPLOYMENT PIPELINE CIRCUIT TRACK
 * File: assets/js/webdev-circuit.js
 * 
 * Features:
 * - Detects when the pipeline section scrolls into view via IntersectionObserver.
 * - Triggers sequential laser ignition from Step 01 -> Step 02 -> Step 03 -> Step 04 -> Step 05 -> Step 06.
 * - Coordinates glowing energy pulses along the Electric Cyan & Cyber Violet circuit bus.
 * - Provides interactive hover highlighting across connected pipeline nodes.
 * ============================================================================
 */

(function () {
  'use strict';

  function initPipelineCircuit() {
    const section = document.querySelector('.vg-pipeline-section');
    if (!section) return;

    const cards = section.querySelectorAll('.vg-pipeline-card');
    const laserTrack = section.querySelector('.vg-pipeline-laser-bus');
    let hasAnimated = false;

    // Sequential trigger function
    function runSequentialPulse() {
      if (hasAnimated) return;
      hasAnimated = true;
      section.classList.add('vg-pipeline-active');

      cards.forEach((card, index) => {
        setTimeout(() => {
          card.classList.add('is-ignited');
          // Brief power surge highlight
          card.classList.add('is-pulsing');
          setTimeout(() => {
            card.classList.remove('is-pulsing');
          }, 600);
        }, index * 380); // Smooth sequential flow: 0ms, 380ms, 760ms, 1140ms, 1520ms, 1900ms
      });
    }

    // IntersectionObserver to start animation as user scrolls into view
    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting && !hasAnimated) {
            runSequentialPulse();
            observer.unobserve(section);
          }
        });
      }, {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
      });

      observer.observe(section);
    } else {
      // Fallback for older browsers
      runSequentialPulse();
    }

    // Interactive Hover: highlights node and intensifies surrounding circuit
    cards.forEach((card) => {
      card.addEventListener('mouseenter', () => {
        card.classList.add('is-hovered');
      });
      card.addEventListener('mouseleave', () => {
        card.classList.remove('is-hovered');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPipelineCircuit);
  } else {
    initPipelineCircuit();
  }
})();
