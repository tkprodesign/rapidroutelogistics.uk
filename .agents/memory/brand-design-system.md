---
name: RRL Brand Design System
description: Brand tokens, typography, color palette, CSS architecture for Rapid Route Logistics
---

## Typography
- Headings: `Barlow Condensed` weight 700-800, `letter-spacing: -0.02em` (h1), `-0.01em` (h2-h4)
- Body: `Barlow`, weight 400-600
- Google Fonts import in `assets/stylesheets/main.css` line 1

## Brand Colors
- Primary teal: `#1A9B82`
- Hover teal: `#22B496`
- Brand dark: `#14232b`
- Off-palette colors that were REMOVED: `#ffc400`, `#ffd64c`, `#005dad`, `#005a8c`, `#2a4d8f`, `#00204A`, `#10204c`, `#07122e`

## Card Geometry
- All card border-radius: 10px (reduced from 20-28px)
- Card shadows: `0 1px 3px rgba(0,0,0,0.05), 0 4px 14px rgba(18,35,43,0.07)` (crisp, not diffuse)
- Hero CTA inputs/buttons: 6px radius

## Hero Section
- Wave SVG divider hidden via `display: none !important`
- Diagonal bottom cut via `section.hero::after` with `clip-path: polygon(0 100%, 100% 0%, 100% 100%)`
- All 3 breakpoint waves hidden (mobile/tab CSS files)

## Footer Logo
- Using `rapid-route-logistics-icon-light.png` (light version for dark footer)
- No alpha channel, so styled as intentional badge: `border-radius: 18px`, `box-shadow: 0 0 0 1px rgba(26,155,130,0.28), 0 0 0 4px rgba(26,155,130,0.08), 0 8px 24px rgba(0,0,0,0.32)`

## CSS File Map
- `main.css`: global header, footer, typography, brand system, premium experience system
- `home.css`: homepage sections (brand geometry system appended at bottom)
- `mobile/home.css`, `tab/home.css`, `ts/home.css`, `ms/home.css`: responsive breakpoints
- `services.css`, `dashboard.css`, `forms.css`, `support.css`, `tracking.css`, `control-panel.css`: section-specific

**Why:** User gave "complete liberty" on design direction; decisions documented so future sessions stay consistent.
