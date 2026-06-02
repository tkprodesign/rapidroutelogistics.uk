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
      var isScrolled = y > 72;
      header.classList.toggle('scrolled', isScrolled);
      header.classList.toggle('is-scrolled', isScrolled);
      var logo = header.querySelector('#logo img');
      if (logo) {
        var targetLogo = isScrolled ? logo.getAttribute('data-dark-logo') : logo.getAttribute('data-light-logo');
        if (targetLogo && logo.getAttribute('src') !== targetLogo) {
          logo.setAttribute('src', targetLogo);
        }
      }
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

  // ── 3. PREMIUM CURSOR TRAIL ───────────────────────────────────────
  // Desktop-only, keeps the native cursor visible for accessibility, and
  // renders on a single canvas to avoid layout thrash from many DOM nodes.
  var cursorCanvas = document.createElement('canvas');
  var ctx = cursorCanvas.getContext && cursorCanvas.getContext('2d');

  if (ctx) {
    cursorCanvas.className = 'rrl-cursor-trail';
    cursorCanvas.setAttribute('aria-hidden', 'true');
    document.body.appendChild(cursorCanvas);
    document.body.classList.add('rrl-cursor-enabled');

    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var width = 0;
    var height = 0;
    var pointer = { x: window.innerWidth / 2, y: window.innerHeight / 2, active: false };
    var particles = [];
    var particleCount = 9;

    for (var i = 0; i < particleCount; i += 1) {
      particles.push({ x: pointer.x, y: pointer.y, delay: 0.13 + (i * 0.035), size: Math.max(4, 18 - i * 1.35) });
    }

    function resizeCursorCanvas() {
      width = window.innerWidth;
      height = window.innerHeight;
      cursorCanvas.width = Math.floor(width * dpr);
      cursorCanvas.height = Math.floor(height * dpr);
      cursorCanvas.style.width = width + 'px';
      cursorCanvas.style.height = height + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function setPointer(e) {
      pointer.x = e.clientX;
      pointer.y = e.clientY;
      pointer.active = true;
    }

    function hidePointer() {
      pointer.active = false;
    }

    function drawTrail() {
      ctx.clearRect(0, 0, width, height);

      var previous = pointer;
      particles.forEach(function (particle, index) {
        particle.x += (previous.x - particle.x) * particle.delay;
        particle.y += (previous.y - particle.y) * particle.delay;
        previous = particle;

        if (!pointer.active) return;

        var alpha = Math.max(0.03, 0.24 - index * 0.018);
        var gradient = ctx.createRadialGradient(particle.x, particle.y, 0, particle.x, particle.y, particle.size * 2.4);
        gradient.addColorStop(0, 'rgba(45, 212, 191,' + alpha + ')');
        gradient.addColorStop(0.45, 'rgba(26, 155, 130,' + (alpha * 0.55) + ')');
        gradient.addColorStop(1, 'rgba(26, 155, 130,0)');

        ctx.beginPath();
        ctx.fillStyle = gradient;
        ctx.arc(particle.x, particle.y, particle.size * 2.4, 0, Math.PI * 2);
        ctx.fill();

        if (index === 0) {
          ctx.beginPath();
          ctx.strokeStyle = 'rgba(20, 35, 43, 0.26)';
          ctx.lineWidth = 1;
          ctx.arc(particle.x, particle.y, 13, 0, Math.PI * 2);
          ctx.stroke();
        }
      });

      requestAnimationFrame(drawTrail);
    }

    resizeCursorCanvas();
    window.addEventListener('resize', resizeCursorCanvas, { passive: true });
    window.addEventListener('pointermove', setPointer, { passive: true });
    window.addEventListener('pointerleave', hidePointer, { passive: true });
    requestAnimationFrame(drawTrail);
  }

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
