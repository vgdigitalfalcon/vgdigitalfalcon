/* Wavestek Power Solutions — Main JS */

document.addEventListener('DOMContentLoaded', function () {

  /* ---- Sticky Header ---- */
  const header = document.getElementById('mainHeader');
  window.addEventListener('scroll', () => {
    header && header.classList.toggle('scrolled', window.scrollY > 60);
  });

  /* ---- Hero Swiper ---- */
  if (document.querySelector('.hero-swiper')) {
    new Swiper('.hero-swiper', {
      loop: true,
      autoplay: { delay: 5000, disableOnInteraction: false },
      pagination: { el: '.swiper-pagination', clickable: true },
      navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
      effect: 'fade',
      fadeEffect: { crossFade: true },
      speed: 800,
    });
  }

  /* ---- Testimonial Swiper ---- */
  if (document.querySelector('.testimonial-swiper')) {
    new Swiper('.testimonial-swiper', {
      loop: true,
      autoplay: { delay: 4500 },
      slidesPerView: 1,
      spaceBetween: 24,
      pagination: { el: '.testimonial-pagination', clickable: true },
      breakpoints: {
        768: { slidesPerView: 2 },
        1024: { slidesPerView: 3 }
      }
    });
  }

  /* ---- Counter Animation ---- */
  function animateCounter(el) {
    const target = parseInt(el.dataset.target, 10);
    const suffix = el.dataset.suffix || '';
    const duration = 1800;
    const start = performance.now();
    function step(now) {
      const elapsed = now - start;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * target) + suffix;
      if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  const counters = document.querySelectorAll('.counter-num[data-target]');
  if (counters.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting && !e.target.dataset.done) {
          e.target.dataset.done = '1';
          animateCounter(e.target);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(c => io.observe(c));
  }

  /* ---- Product Thumbnail Swap ---- */
  const thumbs = document.querySelectorAll('.product-thumb');
  const mainImg = document.querySelector('.product-main-img');
  thumbs.forEach(t => {
    t.addEventListener('click', () => {
      thumbs.forEach(x => x.classList.remove('active'));
      t.classList.add('active');
      if (mainImg) mainImg.src = t.querySelector('img').src;
    });
  });

  /* ---- Lazy Load Images ---- */
  if ('loading' in HTMLImageElement.prototype) {
    document.querySelectorAll('img[data-src]').forEach(img => {
      img.src = img.dataset.src;
    });
  } else {
    const lazyIO = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.src = e.target.dataset.src;
          lazyIO.unobserve(e.target);
        }
      });
    });
    document.querySelectorAll('img[data-src]').forEach(img => lazyIO.observe(img));
  }

  /* ---- Scroll Reveal ---- */
  const reveals = document.querySelectorAll('.reveal');
  if (reveals.length) {
    const revealIO = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('revealed');
          revealIO.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });
    reveals.forEach(r => revealIO.observe(r));
  }

});

/* ---- Back To Top ---- */
(function () {
  const btn = document.getElementById('backToTop');
  if (!btn) return;
  window.addEventListener('scroll', () => btn.classList.toggle('visible', window.scrollY > 400));
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
})();

/* ---- Active Nav Highlight ---- */
(function () {
  const path = window.location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
    const href = (link.getAttribute('href') || '').split('/').pop();
    if (href && href === path) link.classList.add('active');
  });
})();
