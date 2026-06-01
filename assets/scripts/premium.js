(function () {
  'use strict';

  var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var coarse  = window.matchMedia && window.matchMedia('(pointer: coarse)').matches;

  // ── 1. SCROLL PROGRESS BAR ──────────────────────────────────────────
  var progressEl = document.createElement('div');
  progressEl.className = 'rrl-scroll-progress';
  document.body.appendChild(progressEl);

  var header = document.querySelector('header');

  var scrollTicking = false;
  function onScrollTick() {
    var y = window.scrollY || window.pageYOffset;
    var docH = document.documentElement.scrollHeight - window.innerHeight;
    progressEl.style.transform = docH > 0 ? 'scaleX(' + (y / docH) + ')' : 'scaleX(0)';
    if (header) {
      if (y > 72) header.classList.add('scrolled');
      else        header.classList.remove('scrolled');
    }
    scrollTicking = false;
  }

  window.addEventListener('scroll', function () {
    if (!scrollTicking) {
      requestAnimationFrame(onScrollTick);
      scrollTicking = true;
    }
  }, { passive: true });

  onScrollTick();

  // ── 2. COUNTER ANIMATIONS ──────────────────────────────────────────
  if (window.IntersectionObserver) {
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length) {
      var cObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target;
          var target = parseInt(el.getAttribute('data-count'), 10);
          var dur = 1600, t0 = null;
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
      }, { threshold: 0.5 });
      counters.forEach(function (el) { cObs.observe(el); });
    }
  }

  // ── Desktop-only enhancements ────────────────────────────────────────
  if (coarse || reduced) return;

  // ── 3. CUSTOM CURSOR ──────────────────────────────────────────────
  var dot  = document.createElement('div');
  dot.className  = 'rrl-cursor-dot';
  var ring = document.createElement('div');
  ring.className = 'rrl-cursor-ring';
  document.body.appendChild(dot);
  document.body.appendChild(ring);
  document.body.classList.add('rrl-cursor-enabled');

  var mx = -200, my = -200;
  var rx = -200, ry = -200;
  var rafCursor = null;
  var ringReady = false;

  document.addEventListener('mousemove', function (e) {
    mx = e.clientX;
    my = e.clientY;

    // Snap ring to cursor on very first move to avoid fly-in
    if (!ringReady) {
      rx = mx; ry = my;
      ringReady = true;
      dot.style.opacity  = '1';
      ring.style.opacity = '1';
    }

    dot.style.transform = 'translate(' + (mx - 4) + 'px,' + (my - 4) + 'px)';
    if (!rafCursor) rafCursor = requestAnimationFrame(lerpRing);
  });

  function lerpRing() {
    rx += (mx - rx) * 0.13;
    ry += (my - ry) * 0.13;
    ring.style.transform = 'translate(' + (rx - 18) + 'px,' + (ry - 18) + 'px)';
    if (Math.abs(mx - rx) > 0.25 || Math.abs(my - ry) > 0.25) {
      rafCursor = requestAnimationFrame(lerpRing);
    } else {
      rafCursor = null;
    }
  }

  document.addEventListener('mouseleave', function () {
    dot.style.opacity  = '0';
    ring.style.opacity = '0';
    ringReady = false;
  });

  document.addEventListener('mouseover', function (e) {
    var el = e.target && e.target.closest('a, button, input, select, textarea, [role="button"], .dtp, label, .col');
    document.body.classList.toggle('rrl-cursor-hover', !!el);
  }, { passive: true });

  // ── 4. MAGNETIC BUTTONS ──────────────────────────────────────────
  var magnets = document.querySelectorAll('header .cta .dtp, .banner-1 .right a, .rrl-magnetic');
  magnets.forEach(function (btn) {
    btn.addEventListener('mousemove', function (e) {
      var r  = btn.getBoundingClientRect();
      var dx = (e.clientX - (r.left + r.width  / 2)) * 0.26;
      var dy = (e.clientY - (r.top  + r.height / 2)) * 0.26;
      btn.style.transform = 'translate(' + dx + 'px,' + dy + 'px)';
    });
    btn.addEventListener('mouseleave', function () {
      btn.style.transition = 'transform 0.52s cubic-bezier(0.22,0.61,0.36,1)';
      btn.style.transform  = '';
      setTimeout(function () { btn.style.transition = ''; }, 540);
    });
  });

  // ── 5. 3-D CARD TILT ──────────────────────────────────────────────
  var tiltCards = document.querySelectorAll(
    'section.why-choose-us .col, section.cards-container .col'
  );
  tiltCards.forEach(function (card) {
    card.addEventListener('mouseenter', function () {
      card.style.willChange = 'transform';
    });
    card.addEventListener('mousemove', function (e) {
      var r = card.getBoundingClientRect();
      var x = (e.clientX - r.left)  / r.width  - 0.5;
      var y = (e.clientY - r.top)   / r.height - 0.5;
      card.style.transition = 'transform 0.08s linear, box-shadow 0.08s linear';
      card.style.transform  =
        'perspective(700px) rotateY(' + (x * 8) + 'deg) rotateX(' + (-y * 5) + 'deg) translateY(-5px) scale(1.01)';
    });
    card.addEventListener('mouseleave', function () {
      card.style.transition = 'transform 0.52s cubic-bezier(0.22,0.61,0.36,1), box-shadow 0.52s ease';
      card.style.transform  = '';
      setTimeout(function () {
        card.style.transition  = '';
        card.style.willChange  = '';
      }, 560);
    });
  });

})();
