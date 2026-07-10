/* Tibb House Theme — Navigation & UI JS */
(function () {
  'use strict';

  /* ── Sticky nav shadow on scroll ── */
  function initNavScroll() {
    var nav = document.getElementById('tibbhouse-nav');
    if (!nav) return;
    window.addEventListener('scroll', function () {
      nav.classList.toggle('scrolled', window.scrollY > 20);
    }, { passive: true });
  }

  /* ── Mobile menu toggle ── */
  function initMobileMenu() {
    var toggle = document.getElementById('th-nav-toggle');
    var wrap   = document.getElementById('th-nav-menu-wrap');
    if (!toggle || !wrap) return;

    toggle.addEventListener('click', function () {
      var isOpen = wrap.classList.toggle('open');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      toggle.innerHTML = isOpen
        ? '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
        : '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6"  x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';
    });

    // Close on outside click
    document.addEventListener('click', function (e) {
      if (!nav.contains(e.target)) {
        wrap.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });

    var nav = document.getElementById('tibbhouse-nav');
  }

  /* ── Init ── */
  function init() {
    initNavScroll();
    initMobileMenu();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
