/**
 * ============================================================================
 * VG DIGITAL FALCON — 3D FROSTED-GLASS EXECUTIVE OUTCOME CARD
 * File: assets/js/pb-executive-card.js
 * 
 * Interactive Persona Switching & 3D Parallax Tilt for 'Why Wait' Section:
 * - 3 Selectable Audience Tabs (Entrepreneurs, Executives, Job Seekers)
 * - Dynamic Outcome Card Content & Metrics Update with smooth cross-fade
 * - 3D Perspective Mouse Tilt with Physics Damping
 * ============================================================================
 */

(function () {
  'use strict';

  const PERSONA_DATA = {
    entrepreneurs: {
      badge: 'HIGH-VALUE CLIENT ATTRACTION',
      tag: 'FOUNDER ROI',
      title: 'Founder & Startup CEO Blueprint',
      metric1Val: '+380%',
      metric1Lbl: 'Inbound Pipeline',
      metric2Val: '4.6x',
      metric2Lbl: 'Deal Conversion',
      metric3Val: '100%',
      metric3Lbl: 'Founder IP Equity',
      quote: 'Turn your personal name into a multi-million client magnet that replaces outbound cold pitching with qualified inbound meetings.',
      status: 'Live Founder Growth Engine • 90-Day Sprint'
    },
    executives: {
      badge: 'THOUGHT LEADERSHIP & BOARD REACH',
      tag: 'C-SUITE IMPACT',
      title: 'Executive & Managing Director Profile',
      metric1Val: '+450%',
      metric1Lbl: 'CXO Network Reach',
      metric2Val: 'Top 1%',
      metric2Lbl: 'Industry Voice',
      metric3Val: '$25k+',
      metric3Lbl: 'Keynote & Advisory',
      quote: 'Elevate your digital stature to command board seats, international keynote invitations, and top-tier industry advisory retainers.',
      status: 'Executive Influence Engine • Direct CXO Access'
    },
    'job-seekers': {
      badge: 'RECRUITER RADAR MAGNET',
      tag: 'CAREER ACCELERATION',
      title: 'VP & Specialist Career Velocity',
      metric1Val: '14+',
      metric1Lbl: 'Inbound Offers/Qtr',
      metric2Val: '3.2x',
      metric2Lbl: 'Salary Leverage',
      metric3Val: '98%',
      metric3Lbl: 'Interview Inbound',
      quote: 'Skip the endless resume portals. Have elite headhunters, venture capital firms, and scaleups pitch dream leadership roles to you.',
      status: 'Career Magnet Engine • Zero Cold Applications'
    }
  };

  class PBExecutiveCardManager {
    constructor() {
      this.section = document.getElementById('why-wait');
      if (!this.section) return;

      this.tabs = this.section.querySelectorAll('.vg-pb-persona-tab');
      this.card = document.getElementById('vgPbOutcomeCard');
      if (!this.card) return;

      // Card Element References
      this.badgeEl = document.getElementById('vgOutcomeBadge');
      this.tagEl = document.getElementById('vgOutcomeTag');
      this.titleEl = document.getElementById('vgOutcomeTitle');
      this.m1Val = document.getElementById('vgOutcomeM1Val');
      this.m1Lbl = document.getElementById('vgOutcomeM1Lbl');
      this.m2Val = document.getElementById('vgOutcomeM2Val');
      this.m2Lbl = document.getElementById('vgOutcomeM2Lbl');
      this.m3Val = document.getElementById('vgOutcomeM3Val');
      this.m3Lbl = document.getElementById('vgOutcomeM3Lbl');
      this.quoteEl = document.getElementById('vgOutcomeQuote');
      this.statusEl = document.getElementById('vgOutcomeStatus');
      this.innerWrap = document.getElementById('vgOutcomeCardInner');

      this.currentPersona = 'entrepreneurs';
      this.isPaused = false;
      this.timer = null;

      this.initTabs();
      this.initParallax();
      this.startAutoCycle();
    }

    initTabs() {
      this.tabs.forEach((tab) => {
        tab.addEventListener('click', (e) => {
          e.preventDefault();
          const persona = tab.getAttribute('data-persona');
          if (persona && persona !== this.currentPersona) {
            this.switchPersona(persona);
          }
        });

        // Hover support for instant preview on desktop
        tab.addEventListener('mouseenter', () => {
          this.isPaused = true;
          const persona = tab.getAttribute('data-persona');
          if (persona && persona !== this.currentPersona) {
            this.switchPersona(persona);
          }
        });

        tab.addEventListener('mouseleave', () => {
          this.isPaused = false;
        });
      });

      if (this.card) {
        this.card.addEventListener('mouseenter', () => { this.isPaused = true; });
        this.card.addEventListener('mouseleave', () => { this.isPaused = false; });
      }
    }

    switchPersona(personaKey) {
      const data = PERSONA_DATA[personaKey];
      if (!data) return;

      this.currentPersona = personaKey;

      // Update Tab Active State
      this.tabs.forEach((t) => {
        if (t.getAttribute('data-persona') === personaKey) {
          t.classList.add('is-active');
        } else {
          t.classList.remove('is-active');
        }
      });

      // Smooth Cross-Fade Animation
      if (this.innerWrap) {
        this.innerWrap.style.opacity = '0';
        this.innerWrap.style.transform = 'translateY(6px)';

        setTimeout(() => {
          if (this.badgeEl) this.badgeEl.textContent = data.badge;
          if (this.tagEl) this.tagEl.textContent = data.tag;
          if (this.titleEl) this.titleEl.textContent = data.title;
          if (this.m1Val) this.m1Val.textContent = data.metric1Val;
          if (this.m1Lbl) this.m1Lbl.textContent = data.metric1Lbl;
          if (this.m2Val) this.m2Val.textContent = data.metric2Val;
          if (this.m2Lbl) this.m2Lbl.textContent = data.metric2Lbl;
          if (this.m3Val) this.m3Val.textContent = data.metric3Val;
          if (this.m3Lbl) this.m3Lbl.textContent = data.metric3Lbl;
          if (this.quoteEl) this.quoteEl.textContent = `"${data.quote}"`;
          if (this.statusEl) this.statusEl.textContent = data.status;

          this.innerWrap.style.opacity = '1';
          this.innerWrap.style.transform = 'translateY(0)';
        }, 150);
      }
    }

    initParallax() {
      if (!this.card) return;

      this.card.addEventListener('mousemove', (e) => {
        const rect = this.card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotX = -((y - centerY) / centerY) * 7;
        const rotY = ((x - centerX) / centerX) * 8;

        this.card.style.transform = `perspective(1000px) rotateX(${rotX}deg) rotateY(${rotY}deg) translateY(-2px)`;
        this.card.style.setProperty('--card-glow-x', `${(x / rect.width) * 100}%`);
        this.card.style.setProperty('--card-glow-y', `${(y / rect.height) * 100}%`);
      }, { passive: true });

      this.card.addEventListener('mouseleave', () => {
        this.card.style.transform = 'perspective(1000px) rotateX(2deg) rotateY(-3deg) translateY(0)';
      }, { passive: true });
    }

    startAutoCycle() {
      const keys = Object.keys(PERSONA_DATA);
      this.timer = setInterval(() => {
        if (this.isPaused) return;

        // Check if section is in viewport
        const rect = this.section.getBoundingClientRect();
        const inView = rect.top < window.innerHeight && rect.bottom > 0;
        if (!inView) return;

        const idx = keys.indexOf(this.currentPersona);
        const nextIdx = (idx + 1) % keys.length;
        this.switchPersona(keys[nextIdx]);
      }, 5500);
    }
  }

  function init() {
    new PBExecutiveCardManager();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
