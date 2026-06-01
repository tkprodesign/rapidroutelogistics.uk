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
