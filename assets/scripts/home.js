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

    // ── TESTIMONIAL CAROUSEL ────────────────────────────────────────────
    var testimonialCarousel = document.querySelector('.testimonial-carousel');
    if (testimonialCarousel) {
        var track = testimonialCarousel.querySelector('.testimonial-track');
        var cards = testimonialCarousel.querySelectorAll('.testimonial-card');
        var dots = testimonialCarousel.querySelectorAll('.dot');
        var prevBtn = testimonialCarousel.querySelector('.carousel-btn.prev');
        var nextBtn = testimonialCarousel.querySelector('.carousel-btn.next');
        var currentIndex = 0;
        var totalCards = cards.length;
        var autoplayInterval;

        function goToSlide(index) {
            if (index < 0) index = totalCards - 1;
            if (index >= totalCards) index = 0;
            currentIndex = index;
            track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';
            dots.forEach(function(dot, i) {
                dot.classList.toggle('active', i === currentIndex);
            });
        }

        function nextSlide() {
            goToSlide(currentIndex + 1);
        }

        function prevSlide() {
            goToSlide(currentIndex - 1);
        }

        function startAutoplay() {
            autoplayInterval = setInterval(nextSlide, 5000);
        }

        function stopAutoplay() {
            clearInterval(autoplayInterval);
        }

        if (prevBtn) prevBtn.addEventListener('click', function() { stopAutoplay(); prevSlide(); startAutoplay(); });
        if (nextBtn) nextBtn.addEventListener('click', function() { stopAutoplay(); nextSlide(); startAutoplay(); });
        
        dots.forEach(function(dot, index) {
            dot.addEventListener('click', function() { stopAutoplay(); goToSlide(index); startAutoplay(); });
        });

        startAutoplay();
        testimonialCarousel.addEventListener('mouseenter', stopAutoplay);
        testimonialCarousel.addEventListener('mouseleave', startAutoplay);
    }

    // ── STICKY TRACKING BAR ─────────────────────────────────────────────
    var stickyBar = document.getElementById('stickyTrackingBar');
    var closeBarBtn = document.getElementById('closeTrackingBar');
    var heroSectionEl = document.querySelector('.hero');
    
    if (stickyBar && heroSectionEl) {
        var barDismissed = sessionStorage.getItem('trackingBarDismissed');
        
        function showStickyBar() {
            if (!barDismissed) {
                stickyBar.classList.add('visible');
            }
        }
        
        function hideStickyBar() {
            stickyBar.classList.remove('visible');
        }
        
        var barObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (!entry.isIntersecting) {
                    showStickyBar();
                } else {
                    hideStickyBar();
                }
            });
        }, { threshold: 0.1 });
        
        barObserver.observe(heroSectionEl);
        
        if (closeBarBtn) {
            closeBarBtn.addEventListener('click', function() {
                hideStickyBar();
                sessionStorage.setItem('trackingBarDismissed', 'true');
            });
        }
    }

    // ── TOAST NOTIFICATION SYSTEM ──────────────────────────────────────
    window.Toast = {
        container: null,
        
        init: function() {
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.className = 'toast-container';
                document.body.appendChild(this.container);
            }
        },
        
        show: function(message, options) {
            options = options || {};
            var type = options.type || 'info';
            var title = options.title || '';
            var duration = options.duration || 5000;
            
            this.init();
            
            var toast = document.createElement('div');
            toast.className = 'toast';
            
            var iconMap = {
                success: 'check_circle',
                error: 'error',
                warning: 'warning',
                info: 'info'
            };
            
            toast.innerHTML = 
                '<div class="toast-icon ' + type + '">' +
                    '<span class="material-symbols-outlined">' + iconMap[type] + '</span>' +
                '</div>' +
                '<div class="toast-content">' +
                    (title ? '<div class="toast-title">' + title + '</div>' : '') +
                    '<div class="toast-message">' + message + '</div>' +
                '</div>' +
                '<button class="toast-close" aria-label="Close notification">' +
                    '<span class="material-symbols-outlined">close</span>' +
                '</button>' +
                '<div class="toast-progress">' +
                    '<div class="toast-progress-bar"></div>' +
                '</div>';
            
            this.container.appendChild(toast);
            
            requestAnimationFrame(function() {
                toast.classList.add('show');
            });
            
            var timeoutId = setTimeout(function() {
                removeToast();
            }, duration);
            
            var closeBtn = toast.querySelector('.toast-close');
            closeBtn.addEventListener('click', function() {
                clearTimeout(timeoutId);
                removeToast();
            });
            
            function removeToast() {
                toast.classList.remove('show');
                setTimeout(function() {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 400);
            }
        },
        
        success: function(message, options) {
            options = options || {};
            options.type = 'success';
            this.show(message, options);
        },
        
        error: function(message, options) {
            options = options || {};
            options.type = 'error';
            this.show(message, options);
        },
        
        warning: function(message, options) {
            options = options || {};
            options.type = 'warning';
            this.show(message, options);
        },
        
        info: function(message, options) {
            options = options || {};
            options.type = 'info';
            this.show(message, options);
        }
    };

    // ── SCROLL ANIMATIONS OBSERVER ─────────────────────────────────────
    var scrollElements = document.querySelectorAll('.fade-up, .fade-down, .fade-left, .fade-right, .fade-scale, .stagger-children');
    
    if (window.IntersectionObserver && scrollElements.length > 0) {
        var scrollObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    scrollObserver.unobserve(entry.target);
                }
            });
        }, { 
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });
        
        scrollElements.forEach(function(el) {
            scrollObserver.observe(el);
        });
    }
});
