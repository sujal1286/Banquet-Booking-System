---
name: Opulent Hall Booking
colors:
  surface: '#fbf9f5'
  surface-dim: '#dbdad6'
  surface-bright: '#fbf9f5'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f5f3ef'
  surface-container: '#efeeea'
  surface-container-high: '#eae8e4'
  surface-container-highest: '#e4e2de'
  on-surface: '#1b1c1a'
  on-surface-variant: '#424845'
  inverse-surface: '#30312e'
  inverse-on-surface: '#f2f0ed'
  outline: '#727975'
  outline-variant: '#c2c8c4'
  surface-tint: '#4b645b'
  primary: '#000805'
  on-primary: '#ffffff'
  primary-container: '#0a231c'
  on-primary-container: '#728c82'
  inverse-primary: '#b1cdc1'
  secondary: '#735c00'
  on-secondary: '#ffffff'
  secondary-container: '#fed65b'
  on-secondary-container: '#745c00'
  tertiary: '#0a0500'
  on-tertiary: '#ffffff'
  tertiary-container: '#2a1c00'
  on-tertiary-container: '#a3813e'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#cde9dd'
  primary-fixed-dim: '#b1cdc1'
  on-primary-fixed: '#072019'
  on-primary-fixed-variant: '#334c43'
  secondary-fixed: '#ffe088'
  secondary-fixed-dim: '#e9c349'
  on-secondary-fixed: '#241a00'
  on-secondary-fixed-variant: '#574500'
  tertiary-fixed: '#ffdea5'
  tertiary-fixed-dim: '#e9c176'
  on-tertiary-fixed: '#261900'
  on-tertiary-fixed-variant: '#5d4201'
  background: '#fbf9f5'
  on-background: '#1b1c1a'
  surface-variant: '#e4e2de'
typography:
  headline-xl:
    fontFamily: Newsreader
    fontSize: 48px
    fontWeight: '300'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Newsreader
    fontSize: 36px
    fontWeight: '400'
    lineHeight: 44px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Newsreader
    fontSize: 28px
    fontWeight: '400'
    lineHeight: 36px
  headline-sm:
    fontFamily: Newsreader
    fontSize: 22px
    fontWeight: '500'
    lineHeight: 28px
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Inter
    fontSize: 15px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: 20px
    letterSpacing: 0.05em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.08em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 2rem
  margin: 3rem
  space-xs: 0.375rem
  space-sm: 0.75rem
  space-md: 1.5rem
  space-lg: 2.5rem
  space-xl: 4rem
---

## Brand & Style
This design system establishes a luxurious, elegant, and sophisticated aesthetic tailored for a high-end banquet hall booking system. The target audience comprises discerning clients, event planners, and luxury hosts seeking seamless curation of prestigious venues. 

The UI evokes feelings of exclusivity, timeless elegance, and effortless prestige. We adopt a refined variation of Minimalism fused with editorial classicism, characterized by generous whitespace, restrained ornamentation, and high-contrast typography.

## Colors
The palette is anchored by a deep emerald green (`#0A231C`) conveying richness and heritage, complemented by a warm champagne gold (`#D4AF37`) for critical accents and interactive states. The foundational background utilizes a clean, warm cream (`#FBF9F5`), ensuring exceptional readability while maintaining a soft, premium ambiance compared to stark white.

## Typography
Typography bridges classical editorial authority with modern functional clarity. Headlines utilize **Newsreader**, bringing a literary, high-end editorial grace to venue names and primary section titles. Body and interface copy rely on **Inter** for clean legibility across complex booking calendars and pricing matrices. 

Large headings above 32px employ fluid scaling on mobile devices to prevent awkward wrapping while preserving impact.

## Layout & Spacing
The layout relies on a generous 12-column fluid grid system characterized by wide gutters and expansive outer margins that simulate the breathing room of high-end print lookbooks. 

Content is structured with intentional asymmetry and ample vertical whitespace to direct focus toward high-resolution venue imagery. Breakpoints adhere to standard device form factors: mobile devices collapse multi-column floorplan selectors and booking summaries into single-column vertical flows with condensed 1.5rem margins.

## Elevation & Depth
Depth is communicated through subtle tonal layering and ultra-low-opacity ambient shadows rather than harsh drop shadows. Surfaces elevate using warm cream shifts combined with gossamer-thin champagne gold outlines (`#D4AF37` at 15% opacity). Interactive elements lift slightly on hover via diffused, emerald-tinted shadows that maintain the sophisticated, grounded atmosphere.

## Shapes
The design system employs a restrained, soft shape language (`roundedness` level 1). UI elements feature subtle radii (0.25rem for inputs, 0.5rem for cards) to maintain crisp architectural precision while softening the digital interface. Avoid overly pill-shaped containers except for specific status tags, ensuring the aesthetic remains sharp and formal.

## Components
- **Buttons:** Primary actions utilize solid deep emerald fills with champagne gold typography and subtle borders. Secondary buttons use transparent backgrounds with thin gold outlines, shifting to solid gold on hover.
- **Chips & Tags:** Pill-shaped indicators used for venue amenities (e.g., "Valet", "Ballroom", "AV Suite") featuring soft cream backgrounds and dark emerald text.
- **Lists:** Clean typographic lists with generous row spacing, utilizing hairline dividers in translucent gold.
- **Checkboxes & Radio Buttons:** Custom-styled square and circular selectors featuring deep emerald borders that fill with champagne gold upon selection.
- **Input Fields:** Minimalist text inputs featuring bottom-border-only styling or very soft cream background fills with refined focus rings in champagne gold.
- **Cards:** Venue preview cards featuring edge-to-edge photography, overlaid titles in Newsreader serif, and subtle gold border framing on hover.
- **Booking Calendar:** A dedicated component featuring high-density date grids with gold selection markers and clear availability indicators for peak banquet dates.