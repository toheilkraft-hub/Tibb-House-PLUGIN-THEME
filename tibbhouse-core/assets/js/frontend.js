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

  /* ── Lightbox ── */
  function initLightbox() {
    // Build DOM once
    if ( document.getElementById('th-lightbox') ) return;

    var lb = document.createElement('div');
    lb.id = 'th-lightbox';
    lb.setAttribute('role', 'dialog');
    lb.setAttribute('aria-modal', 'true');
    lb.setAttribute('aria-label', 'Image viewer');
    lb.innerHTML =
      '<div class="th-lightbox-inner">' +
        '<button class="th-lightbox-close" id="th-lightbox-close" aria-label="Close">&times;</button>' +
        '<img id="th-lightbox-img" src="" alt="">' +
        '<div class="th-lightbox-nav">' +
          '<button class="th-lightbox-prev" id="th-lightbox-prev" aria-label="Previous image">&#8592;</button>' +
          '<button class="th-lightbox-next" id="th-lightbox-next" aria-label="Next image">&#8594;</button>' +
        '</div>' +
        '<div id="th-lightbox-caption" aria-live="polite"></div>' +
      '</div>';
    document.body.appendChild(lb);

    var items      = [];
    var current    = 0;
    var opener     = null; // element that triggered open — restored on close

    // All focusable controls inside the lightbox (for focus trap)
    function getFocusable() {
      return Array.from( lb.querySelectorAll( 'button:not([disabled])' ) );
    }

    function open(index, triggerEl) {
      items   = Array.from( document.querySelectorAll('.th-gallery-item') );
      if ( !items.length ) return;
      opener  = triggerEl || null;
      current = index;
      show(current);
      lb.classList.add('open');
      document.body.style.overflow = 'hidden';
      // Move focus into dialog
      document.getElementById('th-lightbox-close').focus();
    }

    function close() {
      lb.classList.remove('open');
      document.body.style.overflow = '';
      // Restore focus to the element that opened the lightbox
      if ( opener && typeof opener.focus === 'function' ) {
        opener.focus();
      }
      opener = null;
    }

    function show(index) {
      var item = items[index];
      if (!item) return;
      var img  = document.getElementById('th-lightbox-img');
      var cap  = document.getElementById('th-lightbox-caption');
      img.style.opacity = '0';
      img.src = item.dataset.full || item.querySelector('img').src;
      img.alt = item.dataset.alt  || '';
      cap.textContent = item.dataset.alt || '';
      img.onload = function() { img.style.opacity = '1'; };
      document.getElementById('th-lightbox-prev').disabled = index <= 0;
      document.getElementById('th-lightbox-next').disabled = index >= items.length - 1;
    }

    // Open on gallery item click
    document.addEventListener('click', function(e) {
      var btn = e.target.closest('.th-gallery-item');
      if (btn) {
        items = Array.from( document.querySelectorAll('.th-gallery-item') );
        open( items.indexOf(btn), btn );
      }
    });

    document.getElementById('th-lightbox-close').addEventListener('click', close);

    // Click outside inner panel
    lb.addEventListener('click', function(e) {
      if ( e.target === lb ) close();
    });

    document.getElementById('th-lightbox-prev').addEventListener('click', function() {
      if (current > 0) { current--; show(current); }
    });

    document.getElementById('th-lightbox-next').addEventListener('click', function() {
      if (current < items.length - 1) { current++; show(current); }
    });

    document.addEventListener('keydown', function(e) {
      if ( !lb.classList.contains('open') ) return;

      if ( e.key === 'Escape' ) {
        e.preventDefault();
        close();
        return;
      }
      if ( e.key === 'ArrowLeft' )  { e.preventDefault(); if (current > 0) { current--; show(current); } }
      if ( e.key === 'ArrowRight' ) { e.preventDefault(); if (current < items.length - 1) { current++; show(current); } }

      // Focus trap: keep Tab/Shift+Tab cycling inside focusable controls
      if ( e.key === 'Tab' ) {
        var focusable = getFocusable().filter(function(el) { return !el.disabled; });
        if ( !focusable.length ) { e.preventDefault(); return; }
        var first = focusable[0];
        var last  = focusable[focusable.length - 1];
        if ( e.shiftKey ) {
          if ( document.activeElement === first ) { e.preventDefault(); last.focus(); }
        } else {
          if ( document.activeElement === last )  { e.preventDefault(); first.focus(); }
        }
      }
    });
  }

  function init() {
    initPreloader();
    initFAQ();
    initScrollReveal();
    initStagger();
    initLightbox();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
