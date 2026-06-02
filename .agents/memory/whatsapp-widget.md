---
name: WhatsApp widget fix
description: The WhatsApp floating button had a broken icon (invisible) and wrong position (overlapping Smartsupp)
---

## Rule
The WhatsApp icon MUST use `<img src="/assets/images/whatsapp-icon.svg">` inside `.rrl-whatsapp-widget__icon`, NOT an inline SVG.

**Why:** The CSS rule `.rrl-whatsapp-widget__icon svg { fill: currentColor; }` overrides inline SVG presentation attributes, making both the circle and the path render white-on-white (invisible). An `<img>` tag is immune to CSS `fill:` inheritance.

**How to apply:** 
- Icon HTML: `<img src="/assets/images/whatsapp-icon.svg" alt="" width="42" height="42">`
- Icon container: `background: transparent !important` (SVG file has its own green circle)
- Position: `bottom: 120px !important` on desktop, `bottom: 110px !important` on mobile ≤760px (above Smartsupp which appears at ~80px from bottom)
- Smartsupp script is in footer.html at the end; WhatsApp widget is also in footer.html just before it
