(function () {
  'use strict';

  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var header = document.querySelector('header');

  // Single, cheap scroll listener for progress + header elevation.
  var progressEl = document.querySelector('.rrl-scroll-progress');
  if (!progressEl) {
    progressEl = document.createElement('div');
    progressEl.className = 'rrl-scroll-progress';
    progressEl.setAttribute('aria-hidden', 'true');
    document.body.appendChild(progressEl);
  }

  var scrollTicking = false;
  function onScrollTick() {
    var y = window.scrollY || window.pageYOffset;
    var docH = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
    progressEl.style.transform = 'scaleX(' + Math.min(1, y / docH).toFixed(4) + ')';

    if (header) {
      var isScrolled = y > 48;
      header.classList.toggle('scrolled', isScrolled);
      header.classList.toggle('is-scrolled', isScrolled);
    }

    scrollTicking = false;
  }

  window.addEventListener('scroll', function () {
    if (!scrollTicking) {
      scrollTicking = true;
      requestAnimationFrame(onScrollTick);
    }
  }, { passive: true });
  onScrollTick();

  // Counters: run once, no duplicate homepage observer.
  if (!reduced && window.IntersectionObserver) {
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length) {
      var cObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          if (el.dataset.counted === 'true') return;
          el.dataset.counted = 'true';
          var target = parseInt(el.getAttribute('data-count'), 10) || 0;
          var dur = 1300;
          var t0 = null;
          function tick(ts) {
            if (!t0) t0 = ts;
            var p = Math.min((ts - t0) / dur, 1);
            var e = 1 - Math.pow(1 - p, 3);
            el.textContent = Math.round(e * target).toLocaleString();
            if (p < 1) requestAnimationFrame(tick);
          }
          requestAnimationFrame(tick);
          cObs.unobserve(el);
        });
      }, { threshold: 0.45 });
      counters.forEach(function (el) { cObs.observe(el); });
    }
  }
})();
