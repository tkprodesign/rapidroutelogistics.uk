---
name: Premium Experience System
description: Page transitions, scroll reveal, mobile menu overlay, hamburger animation for RRL
---

## Page Transitions
- CSS: `@keyframes rrl-page-enter` (opacity 0→1, translateY 18px→0) on `body`, 0.58s cubic-bezier
- CSS: `body.page-exit` class: opacity 0, translateY -12px, 0.26s ease
- JS in `assets/scripts/index.js`: click intercept on internal links → add `page-exit` → navigate after 270ms
- Skips: `js-open-support-chat`, `js-open-live-chat`, `#` anchors, `mailto:`, `tel:`, `_blank`, external

## Scroll Reveal
- CSS class: `rrl-reveal` (opacity 0, translateY 32px) → `rrl-visible` (opacity 1, translateY 0), 0.75s cubic-bezier
- JS: IntersectionObserver with threshold 0.1, rootMargin -36px, adds `rrl-reveal` + `rrl-visible`
- Observes: section headings, `.col` cards (with stagger delay 0.09s per card), ups-branch-card, banner sections

## Mobile Menu
- Full-screen dark overlay: `position: fixed; top: 0; height: 100dvh; background: rgba(11,20,26,0.97); backdrop-filter: blur(32px); z-index: 88`
- Header has `z-index: 95` (sits ON TOP of overlay)
- Nav links: Barlow Condensed 36px, staggered opacity/translateY animation (delays 0.08s–0.32s per item)
- ESC key handler added in JS to close menu

## Hamburger Button
- 3-span design: l1 (top, 28px), l2 (middle, 20px), l3 (bottom, 28px)
- `.active` state: l1/l3 rotate 45°/-45° through center → clean X; l2 scaleX(0) + opacity 0
- Spans turn white (`background: #fff`) when active (visible against dark overlay)
- `z-index: 96` (always above overlay)
- Transition: 0.4s cubic-bezier(0.22, 0.61, 0.36, 1) for organic feel

**Why:** User requested "presidents feel" — elegant, seamless, flowing. Full-screen overlay + staggered animation is premium luxury pattern.

## Tier II Additions (Scroll Progress · Frosted Glass · Custom Cursor · Magnetic · 3D Tilt)

### Scroll Progress Bar
- CSS: `.rrl-scroll-progress` fixed at top, `transform-origin: left`, `scaleX()` driven by JS
- JS: `premium.js` — RAF-batched, passive scroll listener, `scrollY / (scrollHeight - innerHeight)`

### Frosted Glass Header
- JS: adds `.scrolled` class to `<header>` when `scrollY > 72`
- CSS: `header.scrolled` gets `backdrop-filter: blur(28px) saturate(180%)`, teal hairline border, soft shadow

### Custom Cursor (desktop / non-touch only)
- Two elements: `.rrl-cursor-dot` (8px, instant) + `.rrl-cursor-ring` (36px, lagged lerp @ 0.13)
- Ring snaps to cursor on first mousemove to avoid fly-in artifact
- Hover state: dot shrinks to 0, ring fills with teal bg tint + stronger border
- Gated on `(pointer: coarse)` — never activates on touch/mobile

### Magnetic Buttons
- Applies to: `header .cta .dtp`, `.banner-1 .right a`, `.rrl-magnetic`
- `mousemove` → `translate(dx*0.26, dy*0.26)`, `mouseleave` → spring return (0.52s ease)

### 3D Card Tilt
- Applies to: `section.why-choose-us .col`, `section.cards-container .col`
- `perspective(700px) rotateY(Xdeg) rotateX(Ydeg) translateY(-5px) scale(1.01)` on mousemove
- Fast transition (0.08s) on move, spring return (0.52s) on leave
- All GPU-accelerated via `transform` only, no layout properties

### Section Polish
- Brand context card (`.brand-context`): dark editorial, radial teal glow, gradient bottom line, white text
- Why-choose-us: teal gradient top-border on card hover, `border: 1px solid rgba(26,155,130,0.2)` on hover
- Cards container: `translateY(-6px)` lift + image zoom `scale(1.05)` on hover
- Banner-1: deep dark gradient + two radial teal glow orbs
- Hero accent: `linear-gradient(125deg, #1A9B82, #2DD4BF)` with `background-clip: text`

**Why:** `premium.js` is separated from `index.js` so Tier II features can be maintained independently without risk to core navigation / mobile menu logic.
