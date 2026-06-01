---
name: Homepage image assets
description: What images are used where on the homepage and what they were replaced with
---

## Homepage images (assets/images/home/)
- `mc1.jpg` → hero-1 CSS background (`background: url(...)` in home.css); replaced with Unsplash warehouse photo
- `mc5.jpg` → hero-2 CSS background AND tools section `.left` background; replaced with Unsplash logistics photo
- `cd1.jpg` → service card 1 inline img ("Ship and Scale"); replaced with Unsplash courier photo
- `cd2.jpg` → service card 2 inline img ("Returns, Re-Delivery"); replaced with Unsplash trucks photo

**Why:** Original images were military/helicopter photos — completely off-brand for a civilian logistics company.

## tools section img tag
The `<img src="/assets/images/home/mc5.jpg">` in the tools section has `display: none` in CSS — it's decorative/legacy.
