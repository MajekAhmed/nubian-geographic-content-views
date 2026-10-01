---
name: ngcv-ui-design
description: Use when creating, changing, or reviewing any NGCV presentation layer code (templates under templates/, stylesheets under assets/css/, the shared component stylesheets, or the reveal enhancement in assets/js/content-views.js). Encodes the permanent design rules for the Nubian Geographic article archive and single article: the locked identity, the centred editorial container, the typography and spacing scales, the motion limits, the responsive breakpoints, the RTL/LTR rules, and the accessibility floor. Also use when judging whether a proposed visual change fits the Nubian Geographic art direction.
---

# NGCV UI Design

Permanent design rules for the Nubian Geographic Content Views presentation
layer (archive + single article). These rules are not preferences — a change
that breaks one of them is a defect, not a redesign.

Read only the reference files you actually need for the task at hand:

- `references/layout-principles.md` — container, columns, reading measure, section rhythm
- `references/typography-scale.md` — type scale, Arabic rules, measure
- `references/motion.md` — duration ceiling, allowed effects, reduced motion
- `references/responsive.md` — the four breakpoints
- `references/rtl-ltr.md` — logical properties, mirror rules, documented exceptions
- `references/identity.md` — the locked brand, and what must never be invented

## Scope boundary

This skill governs **presentation only**. Never, while applying this skill:

- change a WordPress query, `WP_Query` args, posts-per-page, or ordering;
- change URLs, slugs, permalinks, routing, or the template-router contract;
- change taxonomies, categories, tags, or the Polylang adapter's behaviour;
- emit, remove, or alter SEO metadata, canonical tags, or structured data
  (Rank Math stays the SEO authority);
- alter article content, or the `the_content()` pipeline output;
- modify the active theme (Digital Newspaper) in any way — no theme CSS, no
  theme PHP, no theme templates, no theme settings;
- change WordPress reading settings or unrelated plugin settings.

NGCV owns its own geometry. Solve layout inside `.ngcv-context`; never reach
outside it.

## Hard rules

### 1. Identity is locked

| Token | Value | Never |
|---|---|---|
| `--ngcv-navy` | `#020C30` Deep Nile Navy | never redefined |
| `--ngcv-gold` | `#FFDE00` Nubian Gold | never redefined, incl. dark mode |
| `--ngcv-blue` | `#4172CE` Interactive Blue | never redefined |

Do not add a fourth brand hue. Do not introduce an invented colour palette.

**Never invent cultural material.** No hieroglyphs, no pseudo-archaeological
marks, no "generic Nubian pattern" CSS, no fake parchment or ancient textures,
no decorative archaeological icons, no invented slogans, no logo
modifications. The Nubian character comes from editorial composition,
photography, typography, precise geometry, spacing and restrained gold
accents — never from stereotype. Abstract geometric motifs only (rules,
diamonds, hairline grids).

Type: Inter for Latin, Cairo for Arabic. No new font families.

### 2. No new dependencies

No Elementor, Divi, Bootstrap, Tailwind, React, Vue, animation library, icon
library, or UI framework. Icons are inline `data:` SVG masks written by hand
(existing pattern). Motion is CSS transitions/animations; JS is plain
vanilla, in `assets/js/content-views.js` only. Never add a second JS file.

### 3. Centred editorial container

The plugin must not inherit its content width from the theme. `.ngcv-container`
owns the measure: fluid, centred, gutter-bounded, max-width-capped, built from
CSS logical properties. Avoid both failure modes: a narrow ribbon adrift in dead
space, and a full-width text wall. Reason explicitly about 1440 / 1600 / 1920.

Reading text never uses the full container width. `--ngcv-measure` targets
640–760px at desktop.
### 4. Typography

One hierarchy, no duplicated LTR/RTL systems. Arabic: never force
`text-transform: uppercase`, never apply positive Latin letter-spacing, keep
line-height ≥ 1.7. Body text is never animated, never transformed.

### 5. Motion

Ceiling **420ms**; most interactions 150–300ms. Motion is subtle, fast and
purposeful. Nothing animates while reading. Never animate `.ngcv-prose`. Card
elevation appears on interaction, not at rest. Every entrance is gated by
`prefers-reduced-motion: no-preference`; under `reduce`, functionality must be
identical minus movement. One `IntersectionObserver` maximum.

### 6. RTL/LTR

CSS logical properties only: `margin-inline`, `padding-block`, `inset-inline`,
`border-inline-start`, `inline-size`, `block-size`. A single layout must serve
both directions. No duplicated RTL layout file. Physical `left`/`right`/`float`
is an exception that must be documented where it appears. Grid reading order
must follow the document: reading column at inline-start, rail at inline-end,
with explicit placement so it holds in both directions.

### 7. Accessibility is a floor, not a feature

Semantic landmarks, exactly one meaningful `<h1>`, visible focus on everything
focusable, screen-reader labels, keyboard operability, ≥44px touch targets on
small screens, WCAG AA contrast in light **and** dark, reduced-motion support,
logical RTL/LTR reading order. Motion is never the only indicator of state.

### 8. Architecture

Tokens in `assets/css/tokens.css` only. Components in
`assets/css/components/*.css`. Context composition in `assets/css/archive.css`
and `assets/css/single.css`. Breakpoints in `assets/css/responsive.css`.

One authoritative implementation per component. When a rule is needed in both
the archive and the single article, it belongs in a shared component file — a
duplicated selector is a defect. Prefer semantic token names over colour
names (`--ngcv-surface-raised`, not `--ngcv-white`).

Before considering a change complete, verify selector structure
semantically, not just that braces balance.

## Review checklist

When reviewing NGCV UI work, confirm each of these:

1. No query, URL, taxonomy, Polylang, Rank Math, or content change.
2. Brand tokens unchanged; no invented cultural decoration.
3. `.ngcv-container` used; centred at 1440/1600/1920 with balanced gutters.
4. Reading measure within 640–760px; not full-width.
5. Type scale consistent; Arabic free of forced uppercase/tracking.
6. All durations ≤420ms; nothing animates prose; reduced-motion honoured.
7. Logical properties throughout; RTL mirrors without a second layout.
8. Focus visible; one H1; landmarks intact; touch targets ≥44px.
9. No new dependency; JS still only in `content-views.js`.
10. No duplicated component CSS; semantic token names.