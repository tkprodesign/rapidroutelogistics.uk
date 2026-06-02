---
name: Logo asset naming
description: Which branding logo file to use for which context — critical because dark/light naming is counterintuitive
---

## Rule
- `assets/images/branding/transparent/logo.png` — dark-colored horizontal logo, transparent bg. Use in header (white/light bg) and login page.
- `assets/images/branding/transparent/logo-alt.png` — light/white-colored logo, transparent bg. Use on dark backgrounds (e.g., dark hero overlays, banners).
- `assets/images/branding/transparent/icon-alt.png` — light/white icon mark only (no wordmark). Used in footer brand badge. Do NOT use as main header or login logo.
- `assets/images/branding/logo-horizontal-dark.png` — has white background baked in (not transparent). Avoid for header use.

**Why:** The "dark" suffix on logo-horizontal-dark.png is misleading — it does NOT mean the logo is designed for dark backgrounds. It means the logo has dark-colored text, but it comes with a white background rectangle. The transparent/ folder contains the proper transparent versions.

**How to apply:** Header and login page always use `transparent/logo.png`. Footer icon badge uses `transparent/icon-alt.png`. When brand-context dark card needs a logo, use `transparent/logo-alt.png`.
