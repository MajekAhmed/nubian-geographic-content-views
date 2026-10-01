# Layout principles

## The container owns the geometry

NGCV must not inherit its content width from the active theme. The Digital
Newspaper theme sets `.digital-newspaper-container` to a ladder of max-widths
(540 → 620 → 720 → 820 → 960 → 1060 → 1140px) and, when sidebars are enabled,
caps `.primary-content` at 70%. Those are theme decisions for theme pages.
NGCV establishes its own measure inside `.ngcv-context`.

```css
.ngcv-container {
	inline-size: min(100% - (2 * var(--ngcv-gutter)), var(--ngcv-container-max));
	margin-inline: auto;
}
```

Fluid first, capped second. Never a rigid `inline-size: 82rem` alone — that
collapses to dead space on ultra-wide screens instead of using them.

### Reasoning about wide viewports

Take `--ngcv-container-max: 80rem` (1280px) and `--ngcv-gutter: clamp(1.25rem, 4vw, 4rem)`:

| Viewport | Effective width | Side margin each side |
|---|---|---|
| 1280px | 1152px | 64px |
| 1440px | 1280px | 80px |
| 1600px | 1280px | 160px |
| 1920px | 1280px | 320px |
| 2560px | 1280px | 640px |

At 1920px the 320px margins read as an intentional editorial margin, not as
waste — that is the point of the cap. Going wider than ~1400px of content
start makes a 3-column grid lose its editorial density; going narrower than
~1120px starts squeezing three columns into unreadable cards. Raise the cap
only if a section genuinely needs it (a full-bleed hero, for instance), and
then only for that section.

## Two failure modes to avoid

1. **The narrow ribbon** — a 672px reading column adrift inside a 1140px
   container, with ~470px of unexplained dead space. This is the defect that
   made the 1.2.0 single article feel broken.
2. **The full-width text wall** — the whole container handed to paragraph
   text. At >90 characters per line, reading quality collapses.

## Balanced reading area

The single-article reading area is solved with equal flexible gutters, not
with `margin: auto`:

```css
.ngcv-article-columns {
	display: grid;
	grid-template-columns:
		var(--ngcv-rail)          /* TOC, or an empty track when absent */
		minmax(0, 1fr)            /* flexible gutter, inline-start */
		minmax(0, var(--ngcv-measure))
		minmax(0, 1fr);           /* flexible gutter, inline-end */
}
```

Because the two `1fr` tracks are equal, the reading column is optically
centred in the viewport with no fragile offset arithmetic — and it stays
centred in RTL, because the pattern is symmetric and logical.

Column order is explicit, never auto-placed:

```css
.ngcv-article-main  { grid-column: 3; grid-row: 1; }
.ngcv-article-aside { grid-column: 1; grid-row: 1; }
```

Grid line 1 is the inline-start edge in both directions, so the rail is always
at the inline-start side and the prose always at inline-start of the measure.
This is what makes one stylesheet serve both directions.

## Supporting content alignment

Share, details, topics and related articles are **not** stretched across the
container. They align to the reading column's inline-start edge and inherit a
`max-inline-size: var(--ngcv-measure)`. Support content that spans the full
container fights the reading column and makes the page feel unbalanced.

## Section rhythm

Vertical rhythm comes from tokens, not per-component margins:

- `--ngcv-space-*` — the 4px-based scale for component internals.
- `--ngcv-section-gap` — the distance between major sections. Larger than the
  largest spacing step, because it separates *bands*, not elements.

A section is introduced by the editorial divider, not by a stack of ad-hoc
margins. See `identity.md` for the motif constraint.