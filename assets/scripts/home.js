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

/* ===== PREMIUM SCROLL EXPERIENCE: skeletal globe + cinematic scene choreography ===== */
(function() {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const canvas = document.querySelector('.premium-globe-canvas');
    const shell = document.querySelector('.premium-scroll-shell');
    const scenes = Array.from(document.querySelectorAll('.premium-scene'));
    const tiles = Array.from(document.querySelectorAll('.service-tile'));

    if (!canvas || !shell || reduceMotion || scenes.length === 0) return;

    const ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) return;

    const state = {
        dpr: Math.min(window.devicePixelRatio || 1, 2),
        width: 900,
        height: 900,
        scroll: window.scrollY || 0,
        lastScroll: window.scrollY || 0,
        velocity: 0,
        rotationX: -.24,
        rotationY: 0,
        rotationZ: .08,
        targetOpacity: 0,
        opacity: 0,
        sceneProgress: 0,
        activeScene: null,
        ticking: false
    };

    const selectedScenes = new Set(['hero', 'brand', 'stats', 'services']);

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function smoothstep(value) {
        const v = clamp(value, 0, 1);
        return v * v * (3 - 2 * v);
    }

    function resizeCanvas() {
        const box = canvas.getBoundingClientRect();
        state.width = Math.max(320, box.width || 900);
        state.height = Math.max(320, box.height || 900);
        state.dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(state.width * state.dpr);
        canvas.height = Math.round(state.height * state.dpr);
        ctx.setTransform(state.dpr, 0, 0, state.dpr, 0, 0);
    }

    function getSceneProgress(el) {
        const rect = el.getBoundingClientRect();
        const vh = window.innerHeight || 1;
        const total = rect.height + vh;
        return clamp((vh - rect.top) / total, 0, 1);
    }

    function computeActiveScene() {
        let best = null;
        let bestVisibility = 0;

        scenes.forEach((scene) => {
            const rect = scene.getBoundingClientRect();
            const vh = window.innerHeight || 1;
            const visible = clamp((Math.min(rect.bottom, vh) - Math.max(rect.top, 0)) / Math.min(vh, Math.max(rect.height, 1)), 0, 1);
            const progress = getSceneProgress(scene);
            const aura = Math.sin(progress * Math.PI);
            scene.style.setProperty('--scene-aura', (aura * .9).toFixed(3));
            scene.style.setProperty('--scene-drift', `${((progress - .5) * -70).toFixed(2)}px`);
            scene.style.setProperty('--scene-content-y', `${((1 - aura) * 18).toFixed(2)}px`);
            scene.style.setProperty('--scene-content-opacity', clamp(.58 + aura * .42, 0, 1).toFixed(3));
            scene.style.setProperty('--scene-light-x', `${(62 + progress * 24).toFixed(2)}%`);
            scene.style.setProperty('--scene-light-y', `${(24 + Math.sin(progress * Math.PI) * 28).toFixed(2)}%`);

            if (visible > bestVisibility) {
                bestVisibility = visible;
                best = { scene, progress, visible };
            }
        });

        state.activeScene = best;
        const name = best && best.scene ? best.scene.dataset.premiumScene : '';
        state.sceneProgress = best ? best.progress : 0;
        state.targetOpacity = selectedScenes.has(name) ? smoothstep(bestVisibility) : 0;

        const xMap = { hero: 72, brand: 78, stats: 28, services: 50 };
        const yMap = { hero: 48, brand: 45, stats: 42, services: 48 };
        const scaleMap = { hero: 1.05, brand: .82, stats: .72, services: .95 };
        shell.style.setProperty('--premium-globe-x', `${xMap[name] || 72}%`);
        shell.style.setProperty('--premium-globe-y', `${yMap[name] || 50}%`);
        shell.style.setProperty('--premium-globe-scale', `${scaleMap[name] || .86}`);
        shell.style.setProperty('--premium-field-opacity', `${state.targetOpacity * .72}`);
        shell.style.setProperty('--premium-field-x', `${(state.sceneProgress - .5) * 80}px`);
        shell.style.setProperty('--premium-field-y', `${(state.sceneProgress - .5) * -50}px`);
        shell.style.setProperty('--premium-field-rotate', `${state.sceneProgress * 42}deg`);

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

    function projectPoint(lat, lon, radius) {
        const cosLat = Math.cos(lat);
        let x = radius * cosLat * Math.cos(lon);
        let y = radius * Math.sin(lat);
        let z = radius * cosLat * Math.sin(lon);

        const cx = Math.cos(state.rotationX);
        const sx = Math.sin(state.rotationX);
        const cy = Math.cos(state.rotationY);
        const sy = Math.sin(state.rotationY);
        const cz = Math.cos(state.rotationZ);
        const sz = Math.sin(state.rotationZ);

        let y1 = y * cx - z * sx;
        let z1 = y * sx + z * cx;
        let x2 = x * cy + z1 * sy;
        let z2 = -x * sy + z1 * cy;
        let x3 = x2 * cz - y1 * sz;
        let y3 = x2 * sz + y1 * cz;

        const perspective = 1.9 / (1.9 + z2 / radius);
        return {
            x: state.width / 2 + x3 * perspective,
            y: state.height / 2 + y3 * perspective,
            z: z2,
            alpha: clamp((z2 / radius + 1.3) / 2.3, .13, 1)
        };
    }

    function drawPolyline(points, alpha) {
        ctx.beginPath();
        points.forEach((p, index) => {
            if (index === 0) ctx.moveTo(p.x, p.y);
            else ctx.lineTo(p.x, p.y);
        });
        const depth = points.reduce((sum, p) => sum + p.alpha, 0) / points.length;
        ctx.strokeStyle = `rgba(119, 247, 220, ${alpha * depth * state.opacity})`;
        ctx.stroke();
    }

    function drawGlobe() {
        ctx.clearRect(0, 0, state.width, state.height);
        if (state.opacity < .01) return;

        const radius = Math.min(state.width, state.height) * .34;
        const routePulse = .5 + Math.sin((state.scroll * .012) + state.sceneProgress * Math.PI * 2) * .5;

        ctx.lineWidth = 1;
        for (let lat = -60; lat <= 60; lat += 15) {
            const points = [];
            for (let lon = 0; lon <= 360; lon += 6) {
                points.push(projectPoint(lat * Math.PI / 180, lon * Math.PI / 180, radius));
            }
            drawPolyline(points, .26);
        }

        for (let lon = 0; lon < 180; lon += 15) {
            const points = [];
            for (let lat = -84; lat <= 84; lat += 6) {
                points.push(projectPoint(lat * Math.PI / 180, lon * Math.PI / 180, radius));
            }
            drawPolyline(points, .22);
        }

        ctx.lineWidth = 1.8;
        [[-32, -92, 18, -10], [26, -18, 48, 72], [-2, 32, -34, 126]].forEach((route, index) => {
            const [latA, lonA, latB, lonB] = route;
            const points = [];
            for (let i = 0; i <= 44; i += 1) {
                const t = i / 44;
                const arc = Math.sin(t * Math.PI) * .42;
                points.push(projectPoint((latA + (latB - latA) * t + arc * 18) * Math.PI / 180, (lonA + (lonB - lonA) * t) * Math.PI / 180, radius * (1 + arc * .18)));
            }
            drawPolyline(points, (.38 + routePulse * .18) * (index === 1 ? 1.2 : 1));
        });

        const glow = ctx.createRadialGradient(state.width / 2, state.height / 2, radius * .2, state.width / 2, state.height / 2, radius * 1.15);
        glow.addColorStop(0, `rgba(119, 247, 220, ${.08 * state.opacity})`);
        glow.addColorStop(.66, `rgba(34, 180, 150, ${.04 * state.opacity})`);
        glow.addColorStop(1, 'rgba(34, 180, 150, 0)');
        ctx.fillStyle = glow;
        ctx.beginPath();
        ctx.arc(state.width / 2, state.height / 2, radius * 1.15, 0, Math.PI * 2);
        ctx.fill();
    }

    function update() {
        state.ticking = false;
        state.scroll = window.scrollY || 0;
        state.velocity += ((state.scroll - state.lastScroll) - state.velocity) * .18;
        state.lastScroll = state.scroll;

        computeActiveScene();

        state.opacity += (state.targetOpacity - state.opacity) * .12;
        shell.style.setProperty('--premium-globe-opacity', state.opacity.toFixed(3));
        shell.style.setProperty('--premium-shell-opacity', clamp(.32 + state.opacity * .68, 0, 1).toFixed(3));

        const directionForce = clamp(state.velocity / 120, -1, 1);
        state.rotationY += .006 + state.sceneProgress * .004 + directionForce * .014;
        state.rotationX += ((state.sceneProgress - .5) * .42 - state.rotationX) * .045;
        state.rotationZ += (directionForce * .12 - state.rotationZ) * .035;

        drawGlobe();
        requestAnimationFrame(update);
    }

    function requestTick() {
        if (!state.ticking) {
            state.ticking = true;
            requestAnimationFrame(computeActiveScene);
        }
    }

    resizeCanvas();
    computeActiveScene();
    update();

    window.addEventListener('resize', () => {
        resizeCanvas();
        computeActiveScene();
    }, { passive: true });
    window.addEventListener('scroll', requestTick, { passive: true });
})();
