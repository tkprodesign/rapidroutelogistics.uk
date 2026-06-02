/**
 * home.js - lightweight homepage interactions.
 * No framework/runtime dependencies: designed for static PHP hosting and low-end devices.
 */
document.addEventListener('DOMContentLoaded', function () {
    // Static hero setup avoids third-party slider and timed motion weight.
    var slides = Array.prototype.slice.call(document.querySelectorAll('.hero .swiper-slide'));
    if (slides.length > 1) {
        var activeIndex = Math.max(0, slides.findIndex(function (slide) { return slide.classList.contains('is-active'); }));
        slides.forEach(function (slide, index) {
            slide.classList.toggle('is-active', index === activeIndex);
            slide.setAttribute('aria-hidden', index === activeIndex ? 'false' : 'true');
        });

        // Keep the hero static to avoid background cross-fade/scale work on slower devices.
        // Additional slides remain in the markup for content fallback, but do not auto-animate.
    }

    if (!window.__headerNavBound) {
        var body = document.body;
        var nav = document.querySelector('header .container .left nav');
        var menuToggleBtn = document.querySelector('header #menuToggleBtn');
        if (menuToggleBtn && nav && body) {
            menuToggleBtn.addEventListener('click', function () {
                var isOpen = menuToggleBtn.classList.toggle('active');
                body.classList.toggle('active-nav', isOpen);
                nav.classList.toggle('active', isOpen);
                menuToggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        }
    }

    // Business / Personal service tabs.
    var toggle = document.querySelector('.services-alt .toggle');
    var btnBusiness = document.querySelector('.services-alt .btn1');
    var btnPersonal = document.querySelector('.services-alt .btn2');
    var gBusiness = document.querySelector('.services-alt .g1');
    var gPersonal = document.querySelector('.services-alt .g2');

    if (toggle && btnBusiness && btnPersonal && gBusiness && gPersonal) {
        var setActiveTab = function (type) {
            var isPersonal = type === 'personal';
            toggle.style.setProperty('--after-left', isPersonal ? 'calc(50% - 1px)' : '3px');
            btnPersonal.classList.toggle('active', isPersonal);
            btnBusiness.classList.toggle('active', !isPersonal);
            gPersonal.classList.toggle('active', isPersonal);
            gBusiness.classList.toggle('active', !isPersonal);
            btnPersonal.setAttribute('aria-pressed', isPersonal ? 'true' : 'false');
            btnBusiness.setAttribute('aria-pressed', isPersonal ? 'false' : 'true');
        };

        btnPersonal.addEventListener('click', function (e) { e.preventDefault(); setActiveTab('personal'); });
        btnBusiness.addEventListener('click', function (e) { e.preventDefault(); setActiveTab('business'); });
        setActiveTab(btnPersonal.classList.contains('active') ? 'personal' : 'business');
    }

    // Important updates accordion: keep one panel open at a time.
    var sectionIM = document.querySelector('.important-updates');
    if (sectionIM) {
        var allDetails = Array.prototype.slice.call(sectionIM.querySelectorAll('details'));
        allDetails.forEach(function (target) {
            target.addEventListener('toggle', function () {
                var icon = target.querySelector('.accordion-icon');
                if (target.open) {
                    if (icon) {
                        icon.textContent = 'keyboard_arrow_down';
                        icon.classList.add('active');
                    }
                    target.classList.add('is-active');
                    allDetails.forEach(function (other) {
                        if (other !== target && other.open) other.open = false;
                    });
                } else {
                    if (icon) {
                        icon.textContent = 'chevron_right';
                        icon.classList.remove('active');
                    }
                    target.classList.remove('is-active');
                }
            });
        });
    }
});
