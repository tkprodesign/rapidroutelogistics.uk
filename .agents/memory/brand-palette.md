---
name: Brand palette & CSS architecture
description: Covers the Rapid Route Logistics brand colors and CSS load order to avoid regression
---

## Brand colors
- Primary teal: `#1A9B82`
- Dark brand: `#14232b`
- Deep dark: `#0d1920`
- NEVER use: `#ffc300` (UPS yellow), `#ffb500` (UPS amber), `#005eb8` (UPS blue)

## CSS load order (homepage)
1. `main.css` — global base styles
2. `home.css` — homepage-specific (hero, brand-context, stats, why-choose-us, tools, services, cards)
3. `ts/main.css` + `ts/home.css` — tablet ≤1120px overrides
4. `ms/main.css` + `ms/home.css` — mobile ≤760px overrides

**Why:** page-specific overrides must go in the page CSS (`home.css`, `shipping.css`, etc.), not `main.css`.

## Typography
- `'Barlow Condensed'` for hero numbers and display text
- `'Barlow'` for body across all pages (including dashboard)
- Loaded via Google Fonts in each page head
