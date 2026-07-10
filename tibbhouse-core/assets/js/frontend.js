/* Tibb House — Frontend JS: preloader, FAQ accordion, scroll reveal */
(function () {
  'use strict';

  /* ── Preloader ── */
  function initPreloader() {
    var el = document.getElementById('tibbhouse-preloader');
    if (!el) return;

    function dismiss() {
      el.classList.add('th-fade-out');
      setTimeout(function () { el.remove(); }, 700);
    }

    if (document.readyState === 'complete') {
      setTimeout(dismiss, 600);
    } else {
      window.addEventListener('load', function () {
        setTimeout(dismiss, 600);
      });
      // Safety fallback
      setTimeout(dismiss, 3500);
    }
  }

  /* ── FAQ Accordion ── */
  function initFAQ() {
    var items = document.querySelectorAll('.th-faq-item');
    items.forEach(function (item) {
      var trigger = item.querySelector('.th-faq-trigger');
      var body    = item.querySelector('.th-faq-body');
      if (!trigger || !body) return;

      trigger.addEventListener('click', function () {
        var isOpen = item.classList.contains('open');

        // Close all
        items.forEach(function (i) {
          i.classList.remove('open');
          var b = i.querySelector('.th-faq-body');
          if (b) b.style.maxHeight = '0';
        });

        // Open clicked if it was closed
        if (!isOpen) {
          item.classList.add('open');
          body.style.maxHeight = body.scrollHeight + 'px';
        }
      });
    });
  }

  /* ── Scroll Reveal ── */
  function initScrollReveal() {
    var els = document.querySelectorAll('.th-reveal');
    if (!els.length) return;

    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              entry.target.classList.add('visible');
              observer.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
      );
      els.forEach(function (el) { observer.observe(el); });
    } else {
      // Fallback: just make everything visible
      els.forEach(function (el) { el.classList.add('visible'); });
    }
  }

  /* ── Stagger archive cards ── */
  function initStagger() {
    var cards = document.querySelectorAll('.th-archive-card, .th-related-card');
    cards.forEach(function (card, i) {
      card.style.animationDelay = (i * 0.07) + 's';
    });
  }

  /* ── Init ── */
  function init() {
    initPreloader();
    initFAQ();
    initScrollReveal();
    initStagger();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
