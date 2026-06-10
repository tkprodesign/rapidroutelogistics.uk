/**
 * home.js - Homepage interactions.
 * Scroll progress, hero parallax, stats counter, story step reveals, tabs, accordion.
 */
document.addEventListener('DOMContentLoaded', function () {

    // ── STATIC HERO SETUP ─────────────────────────────────────────────────
    var slides = Array.prototype.slice.call(document.querySelectorAll('.hero .swiper-slide'));
    if (slides.length > 1) {
        var activeIndex = Math.max(0, slides.findIndex(function (slide) { return slide.classList.contains('is-active'); }));
        slides.forEach(function (slide, index) {
            slide.classList.toggle('is-active', index === activeIndex);
            slide.setAttribute('aria-hidden', index === activeIndex ? 'false' : 'true');
        });
    }

    if (!window.__headerNavBound) {
        var bodyEl = document.body;
        var navEl = document.querySelector('header .container .left nav');
        var menuBtn = document.querySelector('header #menuToggleBtn');
        if (menuBtn && navEl && bodyEl) {
            menuBtn.addEventListener('click', function () {
                var isOpen = !menuBtn.classList.contains('active');
                menuBtn.classList.toggle('active', isOpen);
                bodyEl.classList.toggle('active-nav', isOpen);
                navEl.classList.toggle('active', isOpen);
                menuBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                menuBtn.setAttribute('aria-label', isOpen ? 'Close navigation menu' : 'Open navigation menu');
            });
        }
    }

    // ── SCROLL PROGRESS BAR ───────────────────────────────────────────────
    var progressBar = document.querySelector('.rrl-scroll-progress');
    if (progressBar) {
        var rafProgress = null;
        window.addEventListener('scroll', function () {
            if (!rafProgress) {
                rafProgress = requestAnimationFrame(function () {
                    var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    var docHeight = document.documentElement.scrollHeight - window.innerHeight;
                    var ratio = docHeight > 0 ? Math.min(scrollTop / docHeight, 1) : 0;
                    progressBar.style.transform = 'scaleX(' + ratio + ')';
                    rafProgress = null;
                });
            }
        }, { passive: true });
    }

    // ── HERO PARALLAX ─────────────────────────────────────────────────────
    var heroSection = document.querySelector('.hero');
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (heroSection && !prefersReduced) {
        var heroSlides = heroSection.querySelectorAll('.swiper-slide');
        var rafParallax = null;
        window.addEventListener('scroll', function () {
            if (!rafParallax) {
                rafParallax = requestAnimationFrame(function () {
                    var sy = window.pageYOffset;
                    if (sy < window.innerHeight * 1.6) {
                        var shift = (sy * 0.26).toFixed(1);
                        heroSlides.forEach(function (slide) {
                            slide.style.backgroundPositionY = 'calc(50% + ' + shift + 'px)';
                        });
                    }
                    rafParallax = null;
                });
            }
        }, { passive: true });
    }

    // ── STATS COUNTER ─────────────────────────────────────────────────────
    function animateCount(el, target, duration) {
        var startTime = null;
        function step(ts) {
            if (!startTime) startTime = ts;
            var progress = Math.min((ts - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            var current = Math.round(eased * target);
            el.textContent = current.toLocaleString();
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target.toLocaleString();
            }
        }
        requestAnimationFrame(step);
    }

    var statsSection = document.getElementById('rrl-stats');
    if (statsSection && window.IntersectionObserver) {
        var statsObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                    entry.target.querySelectorAll('.stat-number[data-count]').forEach(function (el) {
                        var target = parseInt(el.getAttribute('data-count'), 10);
                        animateCount(el, target, 1800);
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3, rootMargin: '0px 0px -40px 0px' });
        statsObserver.observe(statsSection);
    }

    // ── STORY STEP REVEALS ────────────────────────────────────────────────
    if (window.IntersectionObserver) {
        var stepObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('rrl-step-visible');
                    stepObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -24px 0px' });

        document.querySelectorAll('.rrl-process-step').forEach(function (step, i) {
            step.style.transitionDelay = (i * 0.13) + 's';
            stepObserver.observe(step);
        });

        var storyLeft = document.querySelector('.rrl-story-left');
        if (storyLeft) {
            var leftObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('rrl-story-left-visible');
                        leftObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.2 });
            leftObserver.observe(storyLeft);
        }
    }

    // ── BUSINESS / PERSONAL SERVICE TABS ─────────────────────────────────
    var toggle = document.querySelector('.services-alt .toggle');
    var btnBusiness = document.querySelector('.services-alt .btn1');
    var btnPersonal = document.querySelector('.services-alt .btn2');
    var gBusiness = document.querySelector('.services-alt .g1');
    var gPersonal = document.querySelector('.services-alt .g2');

    if (toggle && btnBusiness && btnPersonal && gBusiness && gPersonal) {
        var setActiveTab = function (type) {
            var isPersonal = type === 'personal';
            toggle.dataset.active = isPersonal ? 'personal' : 'business';
            btnPersonal.classList.toggle('active', isPersonal);
            btnBusiness.classList.toggle('active', !isPersonal);
            btnPersonal.setAttribute('aria-selected', isPersonal ? 'true' : 'false');
            btnBusiness.setAttribute('aria-selected', isPersonal ? 'false' : 'true');
            btnPersonal.setAttribute('tabindex', isPersonal ? '0' : '-1');
            btnBusiness.setAttribute('tabindex', isPersonal ? '-1' : '0');
            gPersonal.classList.toggle('active', isPersonal);
            gBusiness.classList.toggle('active', !isPersonal);
            gPersonal.hidden = !isPersonal;
            gBusiness.hidden = isPersonal;
        };

        btnPersonal.addEventListener('click', function () { setActiveTab('personal'); });
        btnBusiness.addEventListener('click', function () { setActiveTab('business'); });
        toggle.addEventListener('keydown', function (e) {
            if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
            e.preventDefault();
            var nextType = btnBusiness.classList.contains('active') ? 'personal' : 'business';
            setActiveTab(nextType);
            (nextType === 'personal' ? btnPersonal : btnBusiness).focus();
        });
        setActiveTab(btnPersonal.classList.contains('active') ? 'personal' : 'business');
    }

    // ── IMPORTANT UPDATES ACCORDION ──────────────────────────────────────
    var sectionIM = document.querySelector('.important-updates');
    if (sectionIM) {
        var allDetails = Array.prototype.slice.call(sectionIM.querySelectorAll('details'));
        allDetails.forEach(function (target) {
            target.addEventListener('toggle', function () {
                var icon = target.querySelector('.accordion-icon');
                if (target.open) {
                    if (icon) { icon.textContent = 'keyboard_arrow_down'; icon.classList.add('active'); }
                    target.classList.add('is-active');
                    allDetails.forEach(function (other) { if (other !== target && other.open) other.open = false; });
                } else {
                    if (icon) { icon.textContent = 'chevron_right'; icon.classList.remove('active'); }
                    target.classList.remove('is-active');
                }
            });
        });
    }
});
