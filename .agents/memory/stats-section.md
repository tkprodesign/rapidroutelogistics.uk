---
name: Stats counter section — homepage
description: Animated stats section added between brand-context and why-choose-us on homepage
---

## HTML (index.php)
Section ID: `#rrl-stats`, class `rrl-stats`
4 stat items: 15000+ Shipments, 99.2% On-Time, 50+ Destinations, 24/7 Support
Counter animation triggered by `data-count` attribute on `.stat-number` spans

## CSS (home.css — appended at end)
Dark cinematic section, 4-col grid, teal `#1A9B82` numbers, `Barlow Condensed` font
Scroll-triggered via `.in-view` class (add nth-child transition-delay for cascade)
Mobile: 2-col grid, border-bottom separators

## JS (home.js — appended at end)
IntersectionObserver with threshold 0.25, cubic ease-out counter animation, fires once then disconnects

**Why:** Added as empty space between the dark brand-context editorial card and the why-choose-us feature grid to break rhythm and establish credibility.
