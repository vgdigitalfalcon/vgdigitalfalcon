/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D ROTATING TECH SPHERE (TAG CLOUD)
 * File: assets/js/webdev-tech-sphere.js
 * 
 * Concept:
 * A clean, box-free 3D Rotating Tech Sphere (Tag Cloud) arranged in a smooth,
 * continuous 3D rotating sphere in the center of the 'Our Tech Stack' section.
 * 
 * Features:
 * - Transparent, glowing SVG icons & typography (React, Next.js, Tailwind CSS,
 *   Node.js, Python, Laravel, WordPress, Shopify, AWS, Cloudflare, TypeScript, Docker, etc.)
 * - Electric Cyan (#00F0FF) and Soft Violet (#A855F7) glow accents directly
 *   against dark #07070F background — zero white boxes, zero card borders.
 * - Fibonacci Golden Spiral distribution on a 3D sphere for equidistant spacing.
 * - Gentle automatic 3D rotation with smooth cursor reaction & mobile touch drag.
 * - Optimized 60fps performance with depth sorting and IntersectionObserver pause.
 * ============================================================================
 */

(function () {
  'use strict';

  // 14 Core Modern Engineering Technologies
  const TECH_ITEMS = [
    {
      name: 'React',
      color: '#00F0FF',
      glow: 'rgba(0, 240, 255, 0.65)',
      svg: `<svg viewBox="0 0 115.3 100" width="46" height="46"><ellipse cx="57.65" cy="50" rx="16.5" ry="46.5" transform="rotate(30 57.65 50)" fill="none" stroke="#00F0FF" stroke-width="4.5"/><ellipse cx="57.65" cy="50" rx="16.5" ry="46.5" transform="rotate(90 57.65 50)" fill="none" stroke="#00F0FF" stroke-width="4.5"/><ellipse cx="57.65" cy="50" rx="16.5" ry="46.5" transform="rotate(150 57.65 50)" fill="none" stroke="#00F0FF" stroke-width="4.5"/><circle cx="57.65" cy="50" r="7.5" fill="#00F0FF"/></svg>`
    },
    {
      name: 'Next.js',
      color: '#FFFFFF',
      glow: 'rgba(255, 255, 255, 0.6)',
      svg: `<svg viewBox="0 0 180 180" width="46" height="46"><circle cx="90" cy="90" r="86" fill="transparent" stroke="#ffffff" stroke-width="4"/><path d="M125 136l-48.5-62.8V136H63V44h13.5l48.5 62.8V44H138v92h-13z" fill="#ffffff"/><path d="M125 108l-23-29.8 8.6-11.2L138 102v6h-13z" fill="#00F0FF"/></svg>`
    },
    {
      name: 'Tailwind CSS',
      color: '#00F0FF',
      glow: 'rgba(0, 240, 255, 0.65)',
      svg: `<svg viewBox="0 0 54 33" width="46" height="46"><path fill="#00F0FF" d="M13.5 0C6.075 0 1.215 3.712 0 11.138c2.7-3.713 5.738-5.063 9.113-4.05 1.925.577 3.3 1.975 4.822 3.523C16.417 13.12 19.34 16.088 27 16.088c7.425 0 12.285-3.713 13.5-11.138-2.7 3.713-5.737 5.063-9.112 4.05-1.925-.577-3.3-1.975-4.823-3.523C24.083 2.968 21.16 0 13.5 0zM0 16.088C0 23.512 4.86 27.225 13.5 27.225c2.7-3.713 5.738-5.063 9.113-4.05 1.925.577 3.3 1.975 4.822 3.523 2.482 2.51 5.405 5.477 13.065 5.477 7.425 0 12.285-3.713 13.5-11.138-2.7 3.713-5.738 5.063-9.113 4.05-1.925-.577-3.3-1.975-4.822-3.523-2.483-2.51-5.406-5.477-13.065-5.477C19.575 16.088 14.715 19.8 13.5 27.225c2.7-3.713 5.738-5.063 9.113-4.05 1.925.577 3.3 1.975 4.822 3.523 2.482 2.51 5.405 5.477 13.065 5.477z"/></svg>`
    },
    {
      name: 'Node.js',
      color: '#22C55E',
      glow: 'rgba(34, 197, 94, 0.65)',
      svg: `<svg viewBox="0 0 256 289" width="46" height="46"><path fill="#22C55E" d="M128 0L8.2 69.2v150.6L128 289l119.8-69.2V69.2L128 0zm-15.5 220.5l-39.6-22.9v-45.7l39.6 22.9v45.7zm0-63.5l-39.6-22.9 39.6-22.9 39.6 22.9-39.6 22.9zm55.1 40.6v-45.7l39.6-22.9v45.7l-39.6 22.9z"/></svg>`
    },
    {
      name: 'Python',
      color: '#00F0FF',
      glow: 'rgba(0, 240, 255, 0.65)',
      svg: `<svg viewBox="0 0 110 110" width="46" height="46"><path fill="#00F0FF" d="M54 2C30 2 31 12 31 12l0 10 24 0 0 3.5-33.5 0C9 27.5 2 35 2 54.5c0 19 6.5 20.5 15 20.5l9 0 0-12.5c0-9.5 8-18 18-18l24 0c7 0 12.5-5.5 12.5-12.5l0-19C80.5 3 67 2 54 2zm-12.5 7.5c2.5 0 4.5 2 4.5 4.5s-2 4.5-4.5 4.5-4.5-2-4.5-4.5 2-4.5 4.5-4.5z"/><path fill="#A855F7" d="M56 108c24 0 23-10 23-10l0-10-24 0 0-3.5 33.5 0c12.5 0 19.5-7.5 19.5-27 0-19-6.5-20.5-15-20.5l-9 0 0 12.5c0 9.5-8 18-18 18l-24 0c-7 0-12.5 5.5-12.5 12.5l0 19C29.5 107 43 108 56 108zm12.5-7.5c-2.5 0-4.5-2-4.5-4.5s2-4.5 4.5-4.5 4.5 2 4.5 4.5-2 4.5-4.5 4.5z"/></svg>`
    },
    {
      name: 'Laravel',
      color: '#F43F5E',
      glow: 'rgba(244, 63, 94, 0.65)',
      svg: `<svg viewBox="0 0 128 128" width="46" height="46"><path fill="#F43F5E" d="M117.8 30.6L99.7 19.4a6 6 0 0 0-6.4 0L76.5 30a6 6 0 0 0-2.8 5.1v17.4l-11.4 6.6V39.4a6 6 0 0 0-2.8-5.1L42.7 23.1a6 6 0 0 0-6.4 0L19.5 33.7a6 6 0 0 0-2.8 5.1v24.6l-6.5 3.8a6 6 0 0 0-2.8 5.1v23.2a6 6 0 0 0 3.2 5.3l17.4 9.4a6 6 0 0 0 6.4 0l16.8-10.6a6 6 0 0 0 2.8-5.1v-17.4l11.4-6.6v19.7a6 6 0 0 0 2.8 5.1l16.8 11.2a6 6 0 0 0 6.4 0l23.2-10.6a6 6 0 0 0 2.8-5.1V58.7l6.5-3.8a6 6 0 0 0 2.8-5.1V26.6a6 6 0 0 0-3.2-5.3z"/></svg>`
    },
    {
      name: 'WordPress',
      color: '#00F0FF',
      glow: 'rgba(0, 240, 255, 0.65)',
      svg: `<svg viewBox="0 0 128 128" width="46" height="46"><circle cx="64" cy="64" r="58" fill="none" stroke="#00F0FF" stroke-width="5.5"/><path fill="#ffffff" d="M16.5 64c0 19.7 11.7 36.6 28.5 44.2L24.8 52.8C19.5 56.2 16.5 60 16.5 64zm76.5-3.3c0-7.2-2.6-12.2-4.8-16-2.9-4.8-5.6-8.9-5.6-13.7 0-5.3 4-10.3 9.8-10.3.2 0 .5 0 .7.1C84.3 14.5 74.6 11 64 11c-20.1 0-37.8 11.1-47.1 27.5 1.3 0 2.6.1 3.7.1 6.1 0 15.6-.8 15.6-.8 3.2-.2 3.5 4.5.3 4.8 0 0-3.2.4-6.8.6l21.6 64.2 13-39-9.2-25.2c-3.2-.2-6.3-.6-6.3-.6-3.2-.2-2.8-4.9.3-4.8 0 0 9.7.8 15.5.8 6.1 0 15.6-.8 15.6-.8 3.2-.2 3.5 4.5.3 4.8 0 0-3.2.4-6.8.6l21.4 63.6 5.9-19.8c2.8-9.4 4.9-16.1 4.9-21.9z"/></svg>`
    },
    {
      name: 'Shopify',
      color: '#95BF47',
      glow: 'rgba(149, 191, 71, 0.65)',
      svg: `<svg viewBox="0 0 100 114" width="46" height="46"><path fill="#95BF47" d="M82.2 26.5c-.3-.8-1-1.3-1.8-1.3l-12.7-.4c-.1-1.5-.7-10.4-5.3-15.1C58.3 5.4 52.9 5 49 5.3c-.6.1-1.2.2-1.7.3-6.2 2-10 6.6-11.8 14.2l-13.9 4.3c-.9.3-1.5 1-1.6 1.9L8.2 92.4c-.2 1.3.7 2.5 2 2.7l64.2 9.6c.4.1.8.1 1.2 0 .4-.1.8-.4 1-.7l22.2-74.8-16.6-2.7zM51.8 14.9c2.7-.2 5.8 1 7.7 3.3 1.9 2.3 2.6 6.3 2.9 8.6L44.8 32.2c1.4-6.4 3.9-9.8 7-17.3z"/><path fill="#ffffff" d="M50.4 42.6c-4.9 0-7.3 2.4-7.3 5.3 0 6.3 16.3 6.9 16.3 18.2 0 8.3-6.2 13.9-14.7 13.9-6.9 0-11.7-3.4-11.7-8.1 0-2.3 1.6-4.1 4.1-4.1 2.3 0 3.7 1.4 4.3 3.3.9 3 2.8 4.6 5.8 4.6 3.1 0 5-1.9 5-4.2 0-7.1-16.4-6.9-16.4-18.4 0-7.6 6.1-13.3 14.7-13.3 5.7 0 9.8 2.3 11 3.5.9 1 1.4 2.2 1.4 3.4 0 2.6-1.9 4.3-4.5 4.3-2.1 0-3.5-1.1-4.4-2.4-1-1.5-2.2-2-3.6-2z"/></svg>`
    },
    {
      name: 'AWS',
      color: '#FF9900',
      glow: 'rgba(255, 153, 0, 0.65)',
      svg: `<svg viewBox="0 0 100 60" width="50" height="46"><path fill="#ffffff" d="M29 35.5h-4.3l-1.5-6.8h-7.8l-1.5 6.8H9.6L16.8 6h5.3l6.9 29.5zm-6.6-11l-3.2-14.5-3.2 14.5h6.4zm23.2 11h-4.5l-4.2-18.3-4.2 18.3h-4.5l-6-29.5h4.6l4 21.6 4.3-18.7h3.8l4.2 18.7 4.1-21.6h4.5l-6 29.5zm27.8-8.7c0 5.6-3.8 9-9.8 9-5.4 0-9.2-3.1-9.7-7.8h4.5c.3 2.6 2.3 4.3 5.2 4.3 3.3 0 5.3-1.7 5.3-4.8 0-2.6-1.7-3.9-5.5-4.9-5.7-1.4-8.8-3.4-8.8-7.9 0-5.1 3.7-8.5 9.1-8.5 4.9 0 8.3 2.8 8.9 7.2h-4.4c-.4-2.2-2.1-3.7-4.5-3.7-2.9 0-4.6 1.7-4.6 4.5 0 2.4 1.6 3.6 5.4 4.5 5.7 1.5 8.9 3.5 8.9 8z"/><path fill="#FF9900" d="M69.8 45.4c-17.5 7.6-39.7 5.7-55.8-3.7-1.1-.6-.2-1.9.9-1.2 15.3 8.7 36.4 10.5 53.4 3.3 1.8-.8 3.2 1 1.5 1.6z"/><path fill="#FF9900" d="M73.5 43.1c-.8-.9-4.9-1.1-7.6-.8-.7.1-.9-.7-.2-1.1 4.2-2.3 9.4-1.2 10.1-.4.8.9-.4 6-4.4 8.7-.7.4-1.2.1-1-.6.7-2.3 2.3-5 3.1-5.8z"/></svg>`
    },
    {
      name: 'Cloudflare',
      color: '#F38020',
      glow: 'rgba(243, 128, 32, 0.65)',
      svg: `<svg viewBox="0 0 100 65" width="50" height="46"><path fill="#F38020" d="M69.8 28.5c-.7-8.9-8.1-15.8-17.1-15.8-6.9 0-12.9 4-15.7 9.8-1.5-.6-3.1-.9-4.8-.9-6.8 0-12.4 5.2-13 11.9C8.3 34.3 0 42.8 0 53.2 0 64.1 8.9 73 19.8 73h50.5c10.9 0 19.7-8.9 19.7-19.8 0-9.6-6.9-17.6-16-19.3l-4.2-5.4z" transform="scale(0.82) translate(8, -8)"/></svg>`
    },
    {
      name: 'TypeScript',
      color: '#3178C6',
      glow: 'rgba(49, 120, 198, 0.65)',
      svg: `<svg viewBox="0 0 100 100" width="46" height="46"><rect width="88" height="88" rx="16" fill="#3178C6" x="6" y="6"/><path fill="#ffffff" d="M30 40v35h-8V40h-11v-7h30v7H30zm42.5 13.5c-1.5-1-4-2-7.5-3-5-1.5-7.5-3.5-7.5-6.5 0-3.5 3-5.5 7.5-5.5 3.5 0 6.5 1 8.5 2.5l2.5-6c-3-2-7-3-11-3-9 0-15.5 5-15.5 12.5 0 5 3.5 9 10 11 5 1.5 7.5 3 7.5 6 0 3.5-3.5 6-8.5 6-4.5 0-8.5-1.5-11-3.5l-2.5 6.5c3.5 2.5 8.5 3.5 13.5 3.5 10 0 16.5-5.5 16.5-13.5 0-5.5-3.5-9-10-10.5z"/></svg>`
    },
    {
      name: 'Docker',
      color: '#00F0FF',
      glow: 'rgba(0, 240, 255, 0.65)',
      svg: `<svg viewBox="0 0 100 75" width="50" height="46"><path fill="#00F0FF" d="M92.5 31.6c-.6-.4-6-4.2-14.7-3.6-2.5-4.8-6.6-8.2-11.8-10.2l-2.4 4.5c4.1 1.7 7.5 4.5 9.7 8.5-1.8 1-4.4 2-7.8 2H2.2c-.8 3.5-.8 7.3 0 11.1 2.2 9.5 9.4 16.7 18.9 19.3 22.8 6.1 48.4-1.2 63.8-17.7 7.7-8.1 8.6-13 8.6-13.4-.3-.1-.6-.3-1-.5zm-47.8-3.4H39v-5.6h5.7v5.6zm7.8 0h-5.7v-5.6h5.7v5.6zm-15.6 0h-5.7v-5.6h5.7v5.6zm23.4 0h-5.7v-5.6h5.7v5.6zm-7.8-7.8h-5.7v-5.6h5.7v5.6zm-7.8 0h-5.7v-5.6h5.7v5.6zm15.6 0h-5.7v-5.6h5.7v5.6z"/></svg>`
    },
    {
      name: 'GraphQL',
      color: '#E10098',
      glow: 'rgba(225, 0, 152, 0.65)',
      svg: `<svg viewBox="0 0 100 100" width="46" height="46"><path fill="#E10098" d="M50 8l36.4 21v42L50 92 13.6 71V29L50 8zm0 7.8L18.4 30.6v38.8L50 84.2l31.6-14.8V30.6L50 15.8z"/><circle cx="50" cy="8" r="6" fill="#A855F7"/><circle cx="86.4" cy="29" r="6" fill="#A855F7"/><circle cx="86.4" cy="71" r="6" fill="#A855F7"/><circle cx="50" cy="92" r="6" fill="#A855F7"/><circle cx="13.6" cy="71" r="6" fill="#A855F7"/><circle cx="13.6" cy="29" r="6" fill="#A855F7"/><path fill="none" stroke="#00F0FF" stroke-width="2.8" d="M50 8v84M13.6 29l72.8 42M13.6 71l72.8-42"/></svg>`
    },
    {
      name: 'PostgreSQL',
      color: '#00F0FF',
      glow: 'rgba(0, 240, 255, 0.65)',
      svg: `<svg viewBox="0 0 100 100" width="46" height="46"><path fill="#00F0FF" d="M50 5C25.2 5 5 25.2 5 50s20.2 45 45 45 45-20.2 45-45S74.8 5 50 5zm17.8 62.4c-3.1 3.5-7.5 5.6-12.2 5.6-6.4 0-11.8-3.9-14-9.6l-5.4 3.1-2.8-4.8 5.4-3.1c-.6-1.8-.9-3.7-.9-5.6 0-1.9.3-3.8.9-5.6l-5.4-3.1 2.8-4.8 5.4 3.1c2.2-5.7 7.6-9.6 14-9.6 4.7 0 9.1 2.1 12.2 5.6l4.2-3.8 3.8 4.2-4.2 3.8c1.3 2.6 2 5.5 2 8.5s-.7 5.9-2 8.5l4.2 3.8-3.8 4.2-4.2-3.8z"/></svg>`
    }
  ];

  class InteractiveTechSphere {
    constructor(container) {
      this.container = container;
      this.stage = container.querySelector('#vg-tech-sphere-stage');
      if (!this.stage) return;

      this.tags = [];
      this.width = 0;
      this.height = 0;
      this.radius = 200;
      this.fov = 420;

      // Rotations & Angular Velocities
      this.rotX = 0.2;
      this.rotY = 0.3;
      this.baseVelX = 0.0032;  // Ambient auto-rotation speed
      this.baseVelY = 0.0016;
      this.velX = this.baseVelX;
      this.velY = this.baseVelY;
      this.targetVelX = this.baseVelX;
      this.targetVelY = this.baseVelY;

      // Animation State
      this.animId = null;
      this.isPaused = false;
      this.isIntersecting = true;
      this.isTouching = false;
      this.lastTouchX = 0;
      this.lastTouchY = 0;

      this.init();
    }

    init() {
      this.resize();
      this.buildTags();
      this.bindEvents();
      this.setupObserver();
      this.start();
    }

    resize() {
      const rect = this.container.getBoundingClientRect();
      this.width = rect.width || 750;
      this.height = rect.height || 480;

      const isMobile = window.innerWidth < 768;
      const isTablet = window.innerWidth < 1100;

      // Sphere radius tailored to screen width
      if (isMobile) {
        this.radius = Math.min(this.width * 0.38, 145);
        this.fov = 340;
      } else if (isTablet) {
        this.radius = Math.min(this.width * 0.34, 175);
        this.fov = 390;
      } else {
        this.radius = 195;
        this.fov = 440;
      }

      // Recompute tag vectors with updated radius
      this.distributeSpherePoints();
    }

    buildTags() {
      this.stage.innerHTML = '';
      this.tags = [];

      TECH_ITEMS.forEach((tech, idx) => {
        const el = document.createElement('div');
        el.className = 'vg-sphere-tag';
        el.setAttribute('data-tech', tech.name.toLowerCase());
        el.setAttribute('tabindex', '0');
        el.setAttribute('role', 'button');
        el.setAttribute('aria-label', `${tech.name} technology stack`);

        el.innerHTML = `
          <div class="vg-tag-icon-wrap" style="filter: drop-shadow(0 0 10px ${tech.glow});">
            ${tech.svg}
          </div>
          <span class="vg-tag-label">${tech.name}</span>
        `;

        this.stage.appendChild(el);

        this.tags.push({
          idx: idx,
          el: el,
          tech: tech,
          origX: 0,
          origY: 0,
          origZ: 0
        });
      });

      this.distributeSpherePoints();
    }

    // ------------------------------------------------------------------------
    // Fibonacci Golden Spiral Sphere Point Distribution
    // Mathematically guarantees equidistant placement across the 3D sphere surface
    // ------------------------------------------------------------------------
    distributeSpherePoints() {
      const count = this.tags.length;
      const r = this.radius;

      for (let i = 0; i < count; i++) {
        const phi = Math.acos(-1 + (2 * i + 1) / count);
        const theta = Math.sqrt(count * Math.PI) * phi;

        this.tags[i].origX = r * Math.cos(theta) * Math.sin(phi);
        this.tags[i].origY = r * Math.sin(theta) * Math.sin(phi);
        this.tags[i].origZ = r * Math.cos(phi);
      }
    }

    bindEvents() {
      let resizeTimer = null;
      window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => this.resize(), 120);
      }, { passive: true });

      // Cursor movement inside sphere container imparts smooth 3D tilt
      this.container.addEventListener('mousemove', (e) => {
        if (this.isTouching) return;
        const rect = this.container.getBoundingClientRect();
        const nx = ((e.clientX - rect.left) / this.width - 0.5) * 2;
        const ny = ((e.clientY - rect.top) / this.height - 0.5) * 2;

        this.targetVelX = nx * 0.016;
        this.targetVelY = -ny * 0.014;
      }, { passive: true });

      // Cursor leaves: gently return to ambient continuous rotation
      this.container.addEventListener('mouseleave', () => {
        this.targetVelX = this.baseVelX;
        this.targetVelY = this.baseVelY;
      }, { passive: true });

      // Mobile touch drag support
      this.container.addEventListener('touchstart', (e) => {
        if (!e.touches || !e.touches[0]) return;
        this.isTouching = true;
        this.lastTouchX = e.touches[0].clientX;
        this.lastTouchY = e.touches[0].clientY;
      }, { passive: true });

      this.container.addEventListener('touchmove', (e) => {
        if (!this.isTouching || !e.touches || !e.touches[0]) return;
        const dx = e.touches[0].clientX - this.lastTouchX;
        const dy = e.touches[0].clientY - this.lastTouchY;
        this.lastTouchX = e.touches[0].clientX;
        this.lastTouchY = e.touches[0].clientY;

        this.rotY += dx * 0.009;
        this.rotX -= dy * 0.009;
      }, { passive: true });

      this.container.addEventListener('touchend', () => {
        this.isTouching = false;
        this.targetVelX = this.baseVelX;
        this.targetVelY = this.baseVelY;
      }, { passive: true });
    }

    setupObserver() {
      // Pause render loop when scrolled off-screen
      if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            this.isIntersecting = entry.isIntersecting;
            if (entry.isIntersecting) {
              this.start();
            } else {
              this.stop();
            }
          });
        }, { threshold: 0.05 });
        observer.observe(this.container);
      }

      // Tab visibility pause
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          this.stop();
        } else if (this.isIntersecting) {
          this.start();
        }
      });
    }

    update() {
      // Smoothly interpolate towards target velocity
      this.velX += (this.targetVelX - this.velX) * 0.06;
      this.velY += (this.targetVelY - this.velY) * 0.06;

      this.rotY += this.velX;
      this.rotX += this.velY;

      const cosY = Math.cos(this.rotY);
      const sinY = Math.sin(this.rotY);
      const cosX = Math.cos(this.rotX);
      const sinX = Math.sin(this.rotX);

      const fov = this.fov;
      const r = this.radius;
      const cx = this.width / 2;
      const cy = this.height / 2;

      for (let i = 0; i < this.tags.length; i++) {
        const tag = this.tags[i];

        // 1. Rotate around Y axis (yaw)
        const x1 = tag.origX * cosY - tag.origZ * sinY;
        const z1 = tag.origX * sinY + tag.origZ * cosY;

        // 2. Rotate around X axis (pitch)
        const y2 = tag.origY * cosX - z1 * sinX;
        const z2 = tag.origY * sinX + z1 * cosX;

        // 3. Perspective Camera Scale
        const scale = (fov + z2) / fov;
        const clampedScale = Math.max(0.55, Math.min(1.42, scale));

        // 4. Depth-based Opacity (Front items crisp 100%, Back items soft 28%-38%)
        const depthNorm = (z2 + r) / (2 * r); // 0 at far back, 1 at front
        const alpha = Math.max(0.26, Math.min(1.0, 0.28 + depthNorm * 0.72));

        // 5. Projected 2D screen coordinates
        const screenX = cx + x1 * clampedScale;
        const screenY = cy + y2 * clampedScale;

        // Apply smooth 3D CSS transforms
        const el = tag.el;
        el.style.transform = `translate3d(${screenX.toFixed(2)}px, ${screenY.toFixed(2)}px, 0) translate(-50%, -50%) scale(${clampedScale.toFixed(3)})`;
        el.style.opacity = alpha.toFixed(3);
        el.style.zIndex = Math.round(clampedScale * 100);

        // Interactive guard: only front-facing nodes capture clicks
        el.style.pointerEvents = z2 > -20 ? 'auto' : 'none';

        // Add soft cyan/violet drop-shadow for front-facing nodes
        if (z2 > 20) {
          el.style.filter = `drop-shadow(0 0 12px ${tag.tech.glow})`;
        } else {
          el.style.filter = 'none';
        }
      }
    }

    start() {
      if (this.animId) cancelAnimationFrame(this.animId);
      this.isPaused = false;
      const loop = () => {
        if (!this.isPaused) {
          this.update();
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
  function initTechSphere() {
    const container = document.querySelector('.vg-tech-sphere-wrapper');
    if (!container) return;

    if (!container._techSphereInstance) {
      container._techSphereInstance = new InteractiveTechSphere(container);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTechSphere);
  } else {
    initTechSphere();
  }

  window.VG_TechSphere = InteractiveTechSphere;
})();

