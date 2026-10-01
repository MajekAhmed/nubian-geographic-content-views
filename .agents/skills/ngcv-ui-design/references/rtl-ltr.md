# RTL / LTR

One layout serves both directions. The Digital Newspaper theme already ships a
separate RTL stylesheet; NGCV must not.

## The rule

Every directional property is logical.

| Physical | Logical |
|---|---|
| `margin-left` / `margin-right` | `margin-inline-start` / `margin-inline-end` |
| `padding-top` / `padding-bottom` | `padding-block` |
| `left` / `right` | `inset-inline-start` / `inset-inline-end` |
| `border-left` | `border-inline-start` |
| `width` / `height` | `inline-size` / `block-size` |
| `text-align: left/right` | `text-align: start/end` |
| `float: left/right` | *exception* — see below |

`border-block`, `margin-block`, `border-inline` shorthands are preferred where
both edges are symmetric — they are direction-agnostic by construction.

## Grid placement must be explicit

CSS Grid line 1 is the inline-start edge in both directions, so auto-placement
can silently put the reading column in the narrow track. Always place
explicitly:

```css
.ngcv-article-main  { grid-column: 3; grid-row: 1; }
.ngcv-article-aside { grid-column: 1; grid-row: 1; }
```

The rail then sits at the inline-start side and the prose at the inline-start
of the measure, in LTR and RTL alike, with no direction-specific rule at all.

## Documented physical exceptions

Physical properties are permitted only where a physical concept is genuinely
involved, and each must carry a comment saying so.

1. **Floated media inside prose.** WordPress emits `.alignleft` and
   `.alignright`, which are *named* for physical sides. They are swapped for
   RTL and their margins rewritten with logical properties.

   ```css
   .ngcv-prose .alignleft  { float: left;  margin-inline: 0 1.2em; }
   [dir="rtl"] .ngcv-prose .alignleft  { float: right; margin-inline: 1.2em 0; }
   ```

2. **Code and preformatted text.** `direction: ltr` with
   `unicode-bidi: embed` — code is not prose and reads left-to-right in both
   layouts.

3. **Directional glyphs.** `.ngcv-arrow` is `scaleX(-1)` under `[dir="rtl"]`
   so the arrow points the way the reader travels.

4. **Mask fade direction.** `--ngcv-fade-dir` carries the gradient direction
   for the category-bar edge fade.

That is the complete list. Anything physical outside these four is a defect.

## Typography mirrors too

Arabic disables the Latin typographic conventions, alongside the layout:

- `text-transform: none` (no uppercase to disable)
- `letter-spacing: normal`
- negative Latin tracking reset to `normal`
- `line-height` increased, not reused
- `.ngcv-label` loses its uppercase + tracking treatment

Gate on `html[dir="rtl"]`, with `html[lang^="ar"]` as a belt-and-braces
alternative, matching the existing convention in the stylesheets.

## Testing both directions

A single mirrored layout still needs explicit verification, because mirroring
happens at layout time and a mistake is invisible in one direction:

- rail on the correct side, prose never in the narrow track;
- category bar scrolls from the correct edge and fades on the trailing edge;
- pagination arrows and the pagination order;
- sticky offsets (`inset-block-start`) unchanged;
- no horizontal overflow with long Arabic strings;
- share row order and wrap behaviour;
- the gold rule and motif alignment.

Document order is never reordered for RTL. Reading order must follow the
document in both directions — the rail is placed with grid, not by moving
DOM nodes.