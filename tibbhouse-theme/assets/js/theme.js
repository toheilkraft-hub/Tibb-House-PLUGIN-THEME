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
    var nav    = document.getElementById('tibbhouse-nav');
    var toggle = document.getElementById('th-nav-toggle');
    var wrap   = document.getElementById('th-nav-menu-wrap');
    if (!toggle || !wrap || !nav) return;

    function closeMenu() {
      wrap.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.querySelector('svg').setAttribute('data-state', 'closed');
      toggle.innerHTML =
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">' +
          '<line x1="3" y1="6"  x2="21" y2="6"/>' +
          '<line x1="3" y1="12" x2="21" y2="12"/>' +
          '<line x1="3" y1="18" x2="21" y2="18"/>' +
        '</svg>';
    }

    function openMenu() {
      wrap.classList.add('open');
      toggle.setAttribute('aria-expanded', 'true');
      toggle.innerHTML =
        '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true">' +
          '<line x1="18" y1="6" x2="6" y2="18"/>' +
          '<line x1="6" y1="6" x2="18" y2="18"/>' +
        '</svg>';
    }

    toggle.addEventListener('click', function (e) {
      e.stopPropagation(); /* prevent bubbling to document handler */
      wrap.classList.contains('open') ? closeMenu() : openMenu();
    });

    /* Also support touchstart for snappier mobile response */
    toggle.addEventListener('touchstart', function (e) {
      e.preventDefault();
      e.stopPropagation();
      wrap.classList.contains('open') ? closeMenu() : openMenu();
    }, { passive: false });

    /* Close when a menu link is tapped */
    wrap.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () { closeMenu(); });
    });

    /* Close on outside tap/click */
    document.addEventListener('click', function (e) {
      if (wrap.classList.contains('open') && !nav.contains(e.target)) {
        closeMenu();
      }
    });
    document.addEventListener('touchstart', function (e) {
      if (wrap.classList.contains('open') && !nav.contains(e.target)) {
        closeMenu();
      }
    }, { passive: true });
  }

  /* ── Reveal-on-scroll animations (respects prefers-reduced-motion) ── */
  function initReveal() {
    var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var revealEls   = document.querySelectorAll('.th-reveal-init');
    var staggerEls  = document.querySelectorAll('[data-stagger]');
    var sectionEls  = document.querySelectorAll('.th-section-fade');

    if (reduced || !('IntersectionObserver' in window)) {
      revealEls.forEach(function (el)  { el.classList.add('th-revealed'); });
      staggerEls.forEach(function (el) { el.classList.add('th-revealed'); });
      sectionEls.forEach(function (el) { el.classList.add('th-revealed'); });
      return;
    }

    /* Element-level observer — tight margin so elements reveal just as they enter */
    var elObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('th-revealed');
          elObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.06, rootMargin: '0px 0px -40px 0px' });

    /* Section-level observer — triggers a little earlier for a sweeping feel */
    var sectionObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('th-revealed');
          sectionObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.04, rootMargin: '0px 0px -20px 0px' });

    revealEls.forEach(function (el)  { elObserver.observe(el); });
    staggerEls.forEach(function (el) { elObserver.observe(el); });
    sectionEls.forEach(function (el) { sectionObserver.observe(el); });
  }

  /* ── Parallax on hero orbs ── */
  function initParallax() {
    var hero = document.querySelector('.th-home-hero');
    if (!hero) return;
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var orbs = hero.querySelectorAll('[data-parallax]');
    if (!orbs.length) return;

    var ticking = false;
    function update() {
      var sy = window.scrollY;
      var heroH = hero.offsetHeight;
      orbs.forEach(function (orb) {
        var speed = parseFloat(orb.getAttribute('data-parallax')) || 0.25;
        orb.style.transform = 'translateY(' + (sy * speed) + 'px)';
      });
      ticking = false;
    }

    window.addEventListener('scroll', function () {
      if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
  }

  /* ── Featured content carousels: prev/next scroll buttons ── */
  function initCarousels() {
    var buttons = document.querySelectorAll('[data-carousel-prev], [data-carousel-next]');
    if (!buttons.length) return;

    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var targetId = btn.getAttribute('data-carousel-prev') || btn.getAttribute('data-carousel-next');
        var track = document.getElementById(targetId);
        if (!track) return;

        var card = track.querySelector(':scope > *');
        var step = card ? card.getBoundingClientRect().width + 28 : 320;
        var dir = btn.hasAttribute('data-carousel-prev') ? -1 : 1;

        track.scrollBy({ left: dir * step, behavior: 'smooth' });
      });
    });
  }

  /* ── Init ── */
  function init() {
    initNavScroll();
    initMobileMenu();
    initReveal();
    initParallax();
    initCarousels();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
