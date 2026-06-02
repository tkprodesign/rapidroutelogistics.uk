---
name: Story section homepage
description: Cinematic "Who We Are / How We Work" section on the homepage with scroll animations
---

## Location
`index.php` — inserted between the hero section (line ~79) and the brand-context section. The section uses class `rrl-story-section`.

## Animation system
- `.rrl-story-left`: starts at opacity:0 + translateX(-28px). JS in home.js adds `rrl-story-left-visible` class via IntersectionObserver (threshold 0.2)
- `.rrl-process-step`: starts at opacity:0 + translateX(24px). JS adds `rrl-step-visible` class with staggered transitionDelay (0.13s × index)
- Both animations defined in home.css (appended to end of file)

**Why:** index.js global rrl-reveal only applies to `.heading`, `.col`, `.ups-branch-card`, etc. — not to the custom story section elements. Custom observers in home.js handle these.

**How to apply:** If adding new animated elements to the story section, either add them to home.js observers OR add `rrl-reveal` class (handled by global index.js observer).
