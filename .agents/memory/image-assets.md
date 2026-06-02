---
name: Image assets & replacements
description: Which image files are used where, their actual content after June 2026 replacements
---

## Current image state (June 2026)

**Warning:** Most images in this project were random/wrong Unsplash placeholders and have been partially replaced. Unsplash static URLs (images.unsplash.com/photo-ID) return unpredictable random photos — verify any downloaded image by reading it before deploying.

## Home images
| File | Used in | Current content |
|------|---------|-----------------|
| `home/mc1.jpg` | hero-1 background (CSS) | Warehouse with yellow bins ✅ |
| `home/mc5.jpg` | hero-2 background (CSS) | Warehouse shelves with pallets ✅ |
| `home/mc2.jpg` | Assisted Pickup service card | Handshake (business/service) ✅ |
| `home/mc6.jpg` | Same-Day Delivery card | Forklift in warehouse ✅ |
| `home/cd1.jpg` | Family Parcel + cards-container col 1 | Warehouse shelves (tall perspective) ✅ |
| `home/cd2.jpg` | Event Delivery + cards-container col 2 | Container port aerial view ✅ |
| `home/mc3.avif` | Not referenced anywhere | Unused |

## Service images
| File | Used for | Current content |
|------|---------|-----------------|
| `services/air-freight.jpg` | Business Logistics tile | Airplane wing in sky ✅ |
| `services/warehouse-solutions.jpg` | Bulk & Scheduled Deliveries tile | Warehouse with yellow bins ✅ |
| `services/road-freight.jpg` | Inter-City & Regional tile | Scania truck on road ✅ |
| `parcel-delivery.jpg` | Document & Priority Parcel tile | Container ship at port ✅ |

## Root / other images
| File | Used for | Current content |
|------|---------|-----------------|
| `parcel-delivery.jpg` | Document & Priority Parcel service tile | Container ship at port ✅ |
| `portal-bg.jpg` | Dashboard background | Unverified |
| `feature.jpg` | Unknown | Unverified |

## Branding (all correct)
- `branding/transparent/logo.png` — dark horizontal logo, transparent bg (header + login)
- `branding/transparent/logo-alt.png` — light/white horizontal logo (dark bg use, emails)
- `branding/transparent/icon-alt.png` — light icon mark only (footer badge)
- `whatsapp-icon.svg` — green circle + white WhatsApp path SVG (floating widget)

**Why:** Images were Unsplash placeholders from initial project build — stock photo IDs had been recycled/removed so they returned random popular photos instead of logistics images.
**How to apply:** Always `read` an image file to visually verify it before using a downloaded Unsplash URL. Never trust the URL alone.
