/* ============================================================
   VG Digital Falcon - Main JavaScript
   ============================================================ */
"use strict";

/* ---- PRELOADER ---- */
window.addEventListener('load', () => {
  const preloader = document.getElementById('preloader');
  if (preloader) {
    setTimeout(() => {
      preloader.style.opacity = '0';
      setTimeout(() => preloader.remove(), 600);
    }, 800);
  }
});

/* ---- NAVBAR SCROLL ---- */
const navbar = document.getElementById('mainNavbar');
function handleNavScroll() {
  if (!navbar) return;
  if (window.scrollY > 60) {
    navbar.classList.add('scrolled');
  } else {
    navbar.classList.remove('scrolled');
  }
}
window.addEventListener('scroll', handleNavScroll);
handleNavScroll();

/* ---- ACTIVE NAV LINK ---- */
const currentPage = window.location.pathname.split('/').pop() || 'index.html';
document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
  const href = link.getAttribute('href');
  if (href === currentPage || (currentPage === '' && href === 'index.html')) {
    link.classList.add('active');
  }
});

/* ---- SCROLL REVEAL ---- */
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.reveal, .reveal-left, .reveal-right').forEach(el => {
  revealObserver.observe(el);
});

/* ---- COUNTER ANIMATION ---- */
function animateCounter(el) {
  const target = parseFloat(el.dataset.target);
  const suffix = el.dataset.suffix || '';
  const duration = 2000;
  const start = performance.now();
  const initial = 0;

  function update(time) {
    const elapsed = time - start;
    const progress = Math.min(elapsed / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = initial + (target - initial) * eased;
    el.textContent = (target % 1 !== 0 ? current.toFixed(1) : Math.floor(current)) + suffix;
    if (progress < 1) requestAnimationFrame(update);
  }
  requestAnimationFrame(update);
}

const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting && !entry.target.dataset.counted) {
      entry.target.dataset.counted = true;
      animateCounter(entry.target);
    }
  });
}, { threshold: 0.5 });

document.querySelectorAll('[data-counter]').forEach(el => {
  counterObserver.observe(el);
});

/* ---- BACK TO TOP ---- */
const backToTop = document.getElementById('backToTop');
window.addEventListener('scroll', () => {
  if (!backToTop) return;
  if (window.scrollY > 400) {
    backToTop.classList.add('show');
  } else {
    backToTop.classList.remove('show');
  }
});
if (backToTop) {
  backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/* ---- COOKIE BANNER ---- */
const cookieBanner = document.getElementById('cookieBanner');
if (cookieBanner && !localStorage.getItem('vgdf_cookies')) {
  setTimeout(() => cookieBanner.classList.add('show'), 1500);
}
window.acceptCookies = () => {
  localStorage.setItem('vgdf_cookies', 'accepted');
  if (cookieBanner) cookieBanner.classList.remove('show');
};

/* ---- SIMPLE CAPTCHA ---- */
let captchaVerified = false;
let captchaAnswer = null;

function generateCaptcha() {
  const a = Math.floor(Math.random() * 12) + 2;
  const b = Math.floor(Math.random() * 12) + 1;
  const ops = ['+', '-', '×'];
  const op = ops[Math.floor(Math.random() * 2)]; // + or - only for simplicity
  captchaAnswer = op === '+' ? a + b : a - b;

  const qEl = document.getElementById('captchaQuestion');
  if (qEl) qEl.textContent = `${a} ${op} ${b} = ?`;

  captchaVerified = false;
  const inp = document.getElementById('captchaInput');
  const fb = document.getElementById('captchaFeedback');
  if (inp) inp.value = '';
  if (fb) { fb.textContent = ''; fb.className = ''; }
}

window.verifyCaptcha = () => {
  const inp = document.getElementById('captchaInput');
  const fb = document.getElementById('captchaFeedback');
  if (!inp || !fb) return;

  const userVal = parseInt(inp.value.trim(), 10);
  if (isNaN(userVal)) {
    fb.textContent = 'Please enter a number.';
    fb.className = 'text-danger small mt-1';
    captchaVerified = false;
    return;
  }
  if (userVal === captchaAnswer) {
    fb.textContent = '✓ Verified!';
    fb.className = 'text-success small mt-1 fw-bold';
    captchaVerified = true;
    inp.classList.add('is-valid');
    inp.classList.remove('is-invalid');
  } else {
    fb.textContent = '✗ Incorrect. Try again.';
    fb.className = 'text-danger small mt-1';
    captchaVerified = false;
    inp.classList.add('is-invalid');
    inp.classList.remove('is-valid');
    generateCaptcha();
  }
};

document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('captchaQuestion')) generateCaptcha();
});

/* ---- CONTACT FORM SUBMISSION ---- */
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!captchaVerified) {
      showToast('Please complete the CAPTCHA verification.', 'error');
      return;
    }
    const submitBtn = contactForm.querySelector('[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Sending...';
    submitBtn.disabled = true;

    // Collect form data
    const formData = {
      name: contactForm.querySelector('[name="name"]')?.value || '',
      email: contactForm.querySelector('[name="email"]')?.value || '',
      phone: contactForm.querySelector('[name="phone"]')?.value || '',
      service: contactForm.querySelector('[name="service"]')?.value || '',
      message: contactForm.querySelector('[name="message"]')?.value || '',
    };

    // Build mailto link as fallback (real email needs backend)
    const mailBody = `Name: ${formData.name}%0AEmail: ${formData.email}%0APhone: ${formData.phone}%0AService: ${formData.service}%0A%0AMessage:%0A${formData.message}`;
    const mailTo = `mailto:contact@vgdigitalfalcon.com,madan.n@vgdigitalfalcon.com,madannirmal20@gmail.com?subject=New Inquiry from ${formData.name}&body=${mailBody}`;

    // Simulate API call (replace with real endpoint)
    await new Promise(r => setTimeout(r, 1500));

    submitBtn.innerHTML = originalText;
    submitBtn.disabled = false;
    contactForm.reset();
    generateCaptcha();
    captchaVerified = false;

    showToast('Thank you! Your message has been sent. We\'ll respond within 24 hours.', 'success');

    // Open mail client as backup
    window.location.href = mailTo;
  });
}

/* ---- TOAST NOTIFICATION ---- */
function showToast(message, type = 'success') {
  let toast = document.getElementById('mainToast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'mainToast';
    toast.className = 'toast-success';
    document.body.appendChild(toast);
  }
  const icon = type === 'success' ? '✅' : '⚠️';
  const bg = type === 'success' ? '#22c55e' : '#f59e0b';
  toast.style.background = bg;
  toast.innerHTML = `<span style="font-size:1.2rem">${icon}</span> ${message}`;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 5000);
}

/* ---- SMOOTH SCROLL FOR ANCHOR LINKS ---- */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      e.preventDefault();
      const offset = 80;
      const top = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top, behavior: 'smooth' });
    }
  });
});

/* ---- TYPED TEXT ANIMATION ---- */
class TypeWriter {
  constructor(el, words, wait = 2800) {
    this.el = el;
    this.words = words;
    this.txt = '';
    this.wordIndex = 0;
    this.wait = wait;
    this.isDeleting = false;
    this.type();
  }
  type() {
    const current = this.wordIndex % this.words.length;
    const fullTxt = this.words[current];
    this.txt = this.isDeleting
      ? fullTxt.substring(0, this.txt.length - 1)
      : fullTxt.substring(0, this.txt.length + 1);
    this.el.innerHTML = `<span class="typed-text">${this.txt}</span><span class="cursor">|</span>`;
    let speed = 120;
    if (this.isDeleting) speed /= 2;
    if (!this.isDeleting && this.txt === fullTxt) {
      speed = this.wait;
      this.isDeleting = true;
    } else if (this.isDeleting && this.txt === '') {
      this.isDeleting = false;
      this.wordIndex++;
      speed = 400;
    }
    setTimeout(() => this.type(), speed);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const typedEl = document.getElementById('typedText');
  if (typedEl) {
    new TypeWriter(typedEl, [
      'Hospitals & Clinics',
      'Doctors & Specialists',
      'Medical Device Companies',
      'Private Health Insurers',
      'Healthcare Brands'
    ]);
  }
});

/* ---- PARTICLE BACKGROUND GENERATOR ---- */
function createParticles() {
  const container = document.querySelector('.hero-particles');
  if (!container) return;
  const count = 18;
  for (let i = 0; i < count; i++) {
    const p = document.createElement('div');
    p.className = 'hero-particle';
    const size = Math.random() * 80 + 20;
    p.style.cssText = `
      width:${size}px; height:${size}px;
      top:${Math.random() * 100}%;
      left:${Math.random() * 100}%;
      animation-duration:${Math.random() * 8 + 6}s;
      animation-delay:${Math.random() * 4}s;
      opacity:${Math.random() * 0.06 + 0.02};
    `;
    container.appendChild(p);
  }
}
document.addEventListener('DOMContentLoaded', createParticles);

/* ---- NAVBAR HAMBURGER ---- */
const navToggler = document.querySelector('.navbar-toggler');
const navCollapse = document.getElementById('mainNav');
if (navToggler && navCollapse) {
  document.addEventListener('click', (e) => {
    if (!navCollapse.contains(e.target) && !navToggler.contains(e.target)) {
      const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
      if (bsCollapse) bsCollapse.hide();
    }
  });
}