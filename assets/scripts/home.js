/**
 * home.js - Developer Homepage Specific Logic
 * Handles Service Tabs and Important Updates Accordion
 */
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('.swiper') && typeof Swiper === 'function') {
        new Swiper('.swiper', {
            loop: true,
            slidesPerView: 1,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            noSwipingSelector: 'input, textarea, button, .c-t-a',
            preventClicks: false,
            preventClicksPropagation: false,
        });
    }

    if (!window.__headerNavBound) {
        const body = document.body;
        const nav = document.querySelector('header .container .left nav');
        const menuToggleBtn = document.querySelector('header #menuToggleBtn');
        if (menuToggleBtn && nav && body) {
            menuToggleBtn.addEventListener('click', () => {
                menuToggleBtn.classList.toggle('active');
                body.classList.toggle('active-nav');
                nav.classList.toggle('active');
            });
        }
    }
    
    // --- SECTION 1: BUSINESS/PERSONAL TOGGLE ---
    const toggle = document.querySelector('.services-alt .toggle');
    const btnBusiness = document.querySelector('.services-alt .btn1');
    const btnPersonal = document.querySelector('.services-alt .btn2');
    const gBusiness = document.querySelector('.services-alt .g1');
    const gPersonal = document.querySelector('.services-alt .g2');

    // Safety Check: Only run if these elements exist on the current page
    if (toggle && btnBusiness && btnPersonal) {
        
        const setActiveTab = (type) => {
            if (type === 'personal') {
                toggle.style.setProperty('--after-left', 'calc(50% - 1px)');
                btnPersonal.classList.add('active');
                btnBusiness.classList.remove('active');
                gPersonal.classList.add('active');
                gBusiness.classList.remove('active');
            } else {
                toggle.style.setProperty('--after-left', '3px');
                btnBusiness.classList.add('active');
                btnPersonal.classList.remove('active');
                gBusiness.classList.add('active');
                gPersonal.classList.remove('active');
            }
        };

        btnPersonal.addEventListener('click', (e) => {
            e.preventDefault(); 
            setActiveTab('personal');
        });

        btnBusiness.addEventListener('click', (e) => {
            e.preventDefault(); 
            setActiveTab('business');
        });

        setActiveTab(btnPersonal.classList.contains('active') ? 'personal' : 'business');
    }

    // --- SECTION 2: IMPORTANT UPDATES (ACCORDION) ---
    const sectionIM = document.querySelector('.important-updates');
    
    if (sectionIM) {
        const allDetails = sectionIM.querySelectorAll('details');

        allDetails.forEach((target) => {
            // The 'toggle' event fires when the 'open' attribute changes
            target.addEventListener('toggle', () => {
                const icon = target.querySelector('.accordion-icon');
                
                if (target.open) {
                    // UI State: OPEN
                    if (icon) {
                        icon.textContent = 'keyboard_arrow_down';
                        icon.classList.add('active');
                    }
                    target.classList.add('is-active');

                    // Logic: Exclusive Toggle (Close all other open accordions)
                    allDetails.forEach((other) => {
                        if (other !== target && other.open) {
                            other.open = false; 
                            // This trigger's the 'other' element's toggle event, 
                            // ensuring its icon and classes are also reset.
                        }
                    });
                } else {
                    // UI State: CLOSED
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



/* ===== STATS COUNTER ANIMATION ===== */
(function() {
    const statsSection = document.getElementById('rrl-stats');
    if (!statsSection) return;

    function animateCount(el, target, duration) {
        const start = performance.now();
        const from = 0;
        function update(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(from + (target - from) * eased);
            el.textContent = value.toLocaleString();
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    }

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                statsSection.classList.add('in-view');
                statsSection.querySelectorAll('[data-count]').forEach(function(el) {
                    animateCount(el, parseInt(el.dataset.count, 10), 1800);
                });
                observer.disconnect();
            }
        });
    }, { threshold: 0.25 });

    observer.observe(statsSection);
})();

/* ===== PREMIUM SCROLL EXPERIENCE: lightweight scene choreography + service tile reveals ===== */
(function() {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const scenes = Array.from(document.querySelectorAll('.premium-scene'));
    const tiles = Array.from(document.querySelectorAll('.service-tile'));

    if (reduceMotion || scenes.length === 0) return;

    let ticking = false;

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function smoothstep(value) {
        const v = clamp(value, 0, 1);
        return v * v * (3 - 2 * v);
    }

    function getSceneProgress(el) {
        const rect = el.getBoundingClientRect();
        const vh = window.innerHeight || 1;
        const total = rect.height + vh;
        return clamp((vh - rect.top) / total, 0, 1);
    }

    function updatePremiumScenes() {
        ticking = false;

        scenes.forEach((scene) => {
            const rect = scene.getBoundingClientRect();
            const vh = window.innerHeight || 1;
            const visible = clamp((Math.min(rect.bottom, vh) - Math.max(rect.top, 0)) / Math.min(vh, Math.max(rect.height, 1)), 0, 1);
            const progress = getSceneProgress(scene);
            const aura = Math.sin(progress * Math.PI) * smoothstep(visible);

            scene.style.setProperty('--scene-aura', (aura * .9).toFixed(3));
            scene.style.setProperty('--scene-drift', `${((progress - .5) * -52).toFixed(2)}px`);
            scene.style.setProperty('--scene-content-y', `${((1 - aura) * 14).toFixed(2)}px`);
            scene.style.setProperty('--scene-content-opacity', clamp(.68 + aura * .32, 0, 1).toFixed(3));
            scene.style.setProperty('--scene-light-x', `${(58 + progress * 28).toFixed(2)}%`);
            scene.style.setProperty('--scene-light-y', `${(24 + Math.sin(progress * Math.PI) * 28).toFixed(2)}%`);
        });

        tiles.forEach((tile, index) => {
            const rect = tile.getBoundingClientRect();
            const vh = window.innerHeight || 1;
            const tileProgress = clamp((vh - rect.top) / (vh * .82 + rect.height), 0, 1);
            const eased = smoothstep(tileProgress);
            const stagger = index % 4;
            const local = clamp((eased - stagger * .045) / .82, 0, 1);

            tile.style.setProperty('--tile-y', `${(1 - local) * 64}px`);
            tile.style.setProperty('--tile-tilt', `${(1 - local) * 7}deg`);
            tile.style.setProperty('--tile-opacity', `${.18 + local * .82}`);
            tile.style.setProperty('--image-scale', `${1.16 - local * .08}`);
            tile.style.setProperty('--image-y', `${(1 - local) * -22}px`);
            tile.style.setProperty('--copy-y', `${(1 - local) * 34}px`);
            tile.style.setProperty('--copy-opacity', `${.22 + local * .78}`);
        });
    }

    function requestUpdate() {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(updatePremiumScenes);
        }
    }

    updatePremiumScenes();
    window.addEventListener('resize', requestUpdate, { passive: true });
    window.addEventListener('scroll', requestUpdate, { passive: true });
})();
