/**
 * index.js - Developer Global Logic
 * Updated for Developer Responsive Footer (Details/Summary Version)
 */

// --- HEADER SWIPER ---
// Disabled: homepage hero is intentionally static to avoid timed motion/cross-fade work.

// --- GLOBAL SELECTORS ---
const body = document.querySelector('body');
const nav = document.querySelector('header .container .left nav');
const menuToggleBtn = document.querySelector('header #menuToggleBtn');

// --- FOOTER ACCORDION LOGIC ---
const footerSections = document.querySelectorAll('footer .footer-section');

/**
 * Handle the accordion toggle
 * Manages mutual exclusion on mobile and prevents closing on desktop
 */
const handleFooterAccordion = (e) => {
    const section = e.currentTarget.closest('.footer-section');
    
    // 1. Desktop Check (1140px / 960px threshold)
    // If we are on desktop, we prevent the "close" action so columns stay visible
    if (window.innerWidth >= 960) {
        if (section.hasAttribute('open')) {
            e.preventDefault(); // Art stays static on desktop
        }
        return;
    }

    // 2. Mobile Logic: Mutual Exclusion
    // When one opens, we close the others for a "Pro" feel
    if (!section.hasAttribute('open')) {
        footerSections.forEach(otherSection => {
            if (otherSection !== section) {
                otherSection.removeAttribute('open');
            }
        });
    }
};

/**
 * Function to ensure all sections are OPEN on desktop resize
 */
const resetFooterState = () => {
    if (window.innerWidth >= 960) {
        footerSections.forEach(s => s.setAttribute('open', ''));
    }
};

if (footerSections.length > 0) {
    footerSections.forEach(section => {
        const summary = section.querySelector('summary');
        if (summary) {
            // We listen to the summary click to intercept the toggle
            summary.addEventListener('click', handleFooterAccordion);
        }
    });

    // Run once on load to ensure desktop is expanded
    resetFooterState();
    
    // Watch for window resize
    window.addEventListener('resize', resetFooterState);
}

// --- MOBILE NAV TOGGLE ---
if (menuToggleBtn && nav && body) {
    window.__headerNavBound = true;

    menuToggleBtn.addEventListener('click', () => {
        menuToggleBtn.classList.toggle('active');
        body.classList.toggle('active-nav');
        nav.classList.toggle('active');
        menuToggleBtn.setAttribute('aria-expanded', menuToggleBtn.classList.contains('active') ? 'true' : 'false');
    });

    nav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            menuToggleBtn.classList.remove('active');
            body.classList.remove('active-nav');
            nav.classList.remove('active');
            menuToggleBtn.setAttribute('aria-expanded', 'false');
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 960) {
            menuToggleBtn.classList.remove('active');
            body.classList.remove('active-nav');
            nav.classList.remove('active');
            menuToggleBtn.setAttribute('aria-expanded', 'false');
        }
    });
}

// --- GLOBAL CHAT WIDGET HELPERS ---
window.openChatWidget = function () {
    if (typeof window.smartsupp === 'function') {
        window.smartsupp('chat:open');
        return true;
    }

    if (window._smartsupp && window._smartsupp.api && typeof window._smartsupp.api.open === 'function') {
        window._smartsupp.api.open();
        return true;
    }

    return false;
};

const chatLinks = document.querySelectorAll('.js-open-live-chat');
if (chatLinks.length > 0) {
    chatLinks.forEach((link) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            if (!window.openChatWidget()) {
                window.location.href = '/support/';
            }
        });
    });
}

// --- BOX OUTLINE FUNCTIONS ---
window.outlineBox = function(id) {
    const outlineDoc = document.querySelector(id);
    if (outlineDoc) outlineDoc.classList.add("outline-input-field");
};

window.removeOutlineBox = function(id) {
    const outlineDoc = document.querySelector(id);
    if (outlineDoc) outlineDoc.classList.remove("outline-input-field");
};

// --- PASSWORD VISIBILITY FUNCTION ---
window.togglePassVisibility = function(cid, oid, piid, acid = null, aoid = null, cpiid = null) {
    const closedEye = document.getElementById(cid);
    const openEye = document.getElementById(oid);
    const passwordInput = document.getElementById(piid);

    if (closedEye && openEye && passwordInput) {
        closedEye.classList.toggle("display-none");
        openEye.classList.toggle("display-none");
        passwordInput.type = (passwordInput.type === "password") ? "text" : "password";
    }

    if (acid && aoid && cpiid) {
        const altClosedEye = document.getElementById(acid);
        const altOpenEye = document.getElementById(aoid);
        const confirmInput = document.getElementById(cpiid);

        if (altClosedEye && altOpenEye && confirmInput) {
            altClosedEye.classList.toggle("display-none");
            altOpenEye.classList.toggle("display-none");
            confirmInput.type = (confirmInput.type === "password") ? "text" : "password";
        }
    }
};






// --- PAGE TRANSITIONS ---
(function () {
    // Clear exit class on back/forward navigation
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            document.body.classList.remove('page-exit');
        }
    });

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[href]');
        if (!link) return;

        // Skip special links
        if (
            link.classList.contains('js-open-support-chat') ||
            link.classList.contains('js-open-live-chat')
        ) return;

        var href = link.getAttribute('href');
        if (!href) return;
        if (
            href.startsWith('#') ||
            href.startsWith('javascript:') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:')
        ) return;
        if (link.getAttribute('target') === '_blank') return;
        if (link.hasAttribute('download')) return;

        try {
            var url = new URL(href, window.location.href);
            if (url.origin !== window.location.origin) return;
            // Don't animate same-page navigations
            if (url.pathname === window.location.pathname && !url.search && !url.hash) return;
        } catch (err) {
            return;
        }

        e.preventDefault();
        var dest = href;
        document.body.classList.add('page-exit');
        setTimeout(function () {
            window.location.href = dest;
        }, 270);
    }, true);
})();

// --- SCROLL REVEAL ---
(function () {
    if (!window.IntersectionObserver) return;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var el = entry.target;
                el.classList.add('rrl-visible');
                observer.unobserve(el);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -36px 0px'
    });

    function observe(el, delay) {
        if (!el || el.classList.contains('rrl-reveal')) return;
        el.classList.add('rrl-reveal');
        if (delay) el.style.transitionDelay = delay + 's';
        observer.observe(el);
    }

    document.querySelectorAll('section:not(.hero)').forEach(function (section) {
        // Section headings
        var heading = section.querySelector('.container .heading');
        if (heading) observe(heading, 0);

        // Cards with stagger
        var cols = section.querySelectorAll('.content .col, .container > .col');
        cols.forEach(function (col, i) {
            observe(col, parseFloat((i * 0.09).toFixed(2)));
        });

        // Other key elements
        var single = [
            '.ups-branch-card',
            '.container .left',
            '.container .right',
            '.container .inner',
        ];
        single.forEach(function (sel) {
            section.querySelectorAll(sel).forEach(function (el, i) {
                observe(el, parseFloat((i * 0.07).toFixed(2)));
            });
        });
    });

    // Banner, tools, about-us as whole blocks
    document.querySelectorAll(
        'section.banner-1, section.tools, section.about-us, section.important-updates, section.cards-container'
    ).forEach(function (section) {
        observe(section, 0);
    });
})();

// --- MOBILE MENU: ESC KEY TO CLOSE ---
(function () {
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' && e.keyCode !== 27) return;
        var btn = document.querySelector('#menuToggleBtn');
        var nav = document.querySelector('header .container .left nav');
        if (btn && btn.classList.contains('active')) {
            btn.classList.remove('active');
            btn.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('active-nav');
            if (nav) nav.classList.remove('active');
        }
    });
})();
