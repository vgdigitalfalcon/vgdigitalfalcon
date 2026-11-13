/**
 * VG Digital Falcon - Ecommerce SEO Services Overview Slider
 * Automatic Focus Zoom & Continuous Auto-Scroll Engine
 * 
 * Features:
 * - Unstoppable automatic smooth scroll (slides every 2800ms)
 * - Automatic 3D center zoom (scale 1.08 + elevation + cyan glow)
 * - Zero mouse-hover freeze: never gets stuck when the cursor is resting on the screen
 * - Seamless infinite loop without getting stuck
 * - Touch & drag enabled with immediate auto-scroll resumption
 * - Responsive breakpoints (1 col mobile, 2 col tablet, 3 col desktop)
 */

(function () {
  'use strict';

  function initServicesSlider() {
    var sliderEl = document.querySelector('.vg-ecom-services-slider');
    if (!sliderEl) return;

    if (typeof Swiper === 'undefined') {
      setTimeout(initServicesSlider, 100);
      return;
    }

    // Destroy any previous Swiper instance if attached
    if (sliderEl.swiper) {
      try {
        sliderEl.swiper.destroy(true, true);
      } catch (e) {}
    }

    // Initialize Swiper with smooth transition settings
    var servicesSwiper = new Swiper('.vg-ecom-services-slider', {
      slidesPerView: 1,
      spaceBetween: 20,
      centeredSlides: true,
      loop: true,
      loopedSlides: 7,
      speed: 750,
      grabCursor: true,
      watchSlidesProgress: true,
      allowTouchMove: true,
      pagination: {
        el: '.vg-services-dot .dot',
        clickable: true
      },
      breakpoints: {
        576: {
          slidesPerView: 1.3,
          spaceBetween: 20,
          centeredSlides: true
        },
        768: {
          slidesPerView: 2,
          spaceBetween: 24,
          centeredSlides: true
        },
        992: {
          slidesPerView: 2.6,
          spaceBetween: 26,
          centeredSlides: true
        },
        1200: {
          slidesPerView: 3,
          spaceBetween: 30,
          centeredSlides: true
        }
      }
    });

    // Dedicated Independent Autoplay Timer
    // Guarantees automatic scrolling NEVER gets stuck or stops!
    var scrollInterval = 2800; // ms
    var autoTimer = null;
    var isUserInteracting = false;
    var isVisible = true;

    function autoSlideNext() {
      if (isUserInteracting || !isVisible) return;
      if (servicesSwiper && !servicesSwiper.destroyed) {
        servicesSwiper.slideNext(750);
      }
    }

    function startAutoScroll() {
      if (autoTimer) clearInterval(autoTimer);
      autoTimer = setInterval(autoSlideNext, scrollInterval);
    }

    function stopAutoScroll() {
      if (autoTimer) {
        clearInterval(autoTimer);
        autoTimer = null;
      }
    }

    // Only pause during ACTIVE touch drag, then resume immediately
    sliderEl.addEventListener('touchstart', function () {
      isUserInteracting = true;
    }, { passive: true });

    sliderEl.addEventListener('touchend', function () {
      setTimeout(function () {
        isUserInteracting = false;
        startAutoScroll();
      }, 1000);
    }, { passive: true });

    sliderEl.addEventListener('pointerdown', function () {
      isUserInteracting = true;
    }, { passive: true });

    window.addEventListener('pointerup', function () {
      if (isUserInteracting) {
        setTimeout(function () {
          isUserInteracting = false;
          startAutoScroll();
        }, 1000);
      }
    }, { passive: true });

    // Visibility management: auto-runs whenever visible on screen
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            isVisible = entry.isIntersecting;
            if (isVisible) {
              startAutoScroll();
            } else {
              stopAutoScroll();
            }
          });
        },
        { threshold: 0.05 }
      );
      observer.observe(sliderEl);
    } else {
      startAutoScroll();
    }

    document.addEventListener('visibilitychange', function () {
      if (document.hidden) {
        isVisible = false;
        stopAutoScroll();
      } else {
        isVisible = true;
        startAutoScroll();
      }
    });

    // Start immediately on load
    startAutoScroll();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initServicesSlider);
  } else {
    initServicesSlider();
  }
})();
