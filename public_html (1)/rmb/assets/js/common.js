/* ============================================
   RMB Chennai Central — Common JS
   Loads header/footer partials and wires up
   shared, site-wide behaviour.
   ============================================ */

(function () {
  'use strict';

  /**
   * Loads an HTML partial into a target element.
   * @param {string} url - path to the partial file
   * @param {string} targetSelector - CSS selector for the mount point
   * @returns {Promise<void>}
   */
  function loadPartial(url, targetSelector) {
    var target = document.querySelector(targetSelector);
    if (!target) return Promise.resolve();

    return fetch(url)
      .then(function (res) {
        if (!res.ok) throw new Error('Failed to load ' + url);
        return res.text();
      })
      .then(function (html) {
        target.innerHTML = html;
      })
      .catch(function (err) {
        console.error(err);
        target.innerHTML = '<p class="text-center text-danger py-3">Unable to load content.</p>';
      });
  }

  /**
   * Highlights the nav link matching the current page's data-page attribute
   * (set via <body data-page="home"> etc. on each page).
   */
  function setActiveNavLink() {
    var currentPage = document.body.getAttribute('data-page');
    if (!currentPage) return;

    var links = document.querySelectorAll('.rmb-header .nav-link[data-page]');
    links.forEach(function (link) {
      if (link.getAttribute('data-page') === currentPage) {
        link.classList.add('active');
        link.setAttribute('aria-current', 'page');
      }
    });
  }

  /**
   * Collapses the mobile nav menu after a nav link is clicked,
   * so navigating on mobile doesn't leave the menu open.
   */
  function collapseMobileNavOnClick() {
    var navLinks = document.querySelectorAll('.rmb-header .nav-link:not(.dropdown-toggle), .rmb-header .dropdown-item');
    var collapseEl = document.getElementById('rmbNavbarNav');
    if (!collapseEl || typeof bootstrap === 'undefined') return;

    var bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });

    navLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        if (collapseEl.classList.contains('show')) {
          bsCollapse.hide();
        }
      });
    });
  }

  /**
   * Adds a subtle shadow/shrink effect to the header on scroll.
   */
  function initHeaderScrollEffect() {
    var header = document.querySelector('.rmb-header');
    if (!header) return;

    window.addEventListener('scroll', function () {
      if (window.scrollY > 10) {
        header.classList.add('is-scrolled');
      } else {
        header.classList.remove('is-scrolled');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var headerPromise = loadPartial('partials/header.html', '#site-header');
    var footerPromise = loadPartial('partials/footer.html', '#site-footer');

    headerPromise.then(function () {
      setActiveNavLink();
      collapseMobileNavOnClick();
      initHeaderScrollEffect();
    });
  });
})();