# Typography

## Families

- Latin: Inter, with `system-ui` fallbacks.
- Arabic: Cairo, then Noto Sans Arabic, then system fallbacks.

One switch (`html[lang^="ar"]` / `html[dir="rtl"]`) moves the whole plugin
surface to the Arabic family. Never ship a second Arabic stylesheet.

Role tokens exist so the theme customizer can hand typography back when
explicitly asked (`--ngcv-font-base`, `--ngcv-font-heading`,
`--ngcv-font-card-title`, `--ngcv-font-content`). A role token that no rule
consumes is dead weight — remove it rather than leaving it declared.

## The scale

Ratios stay small on purpose. An editorial publication is not a poster: the
gap between a card title and a section heading should be legible, not a leap.

| Role | Size | Line height | Weight |
|---|---|---|---|
| kicker / label | 0.75rem | 1.4 | 700 |
| metadata | 0.82rem | 1.5 | 400 |
| details label | 0.78rem | 1.5 | 600 |
| share / toc label | 0.8rem | 1.3 | 700 |
| body | 1.0625rem | 1.8 | 400 |
| lede / deck | 1.1875rem | 1.7 | 400 |
| `h4` in prose | 1.08em | 1.35 | 700 |
| `h3` in prose | 1.25em | 1.32 | 700 |
| `h2` in prose | 1.5em | 1.28 | 700 |
| card title | clamp 1.1 → 1.3rem | 1.32 | 700 |
| feature title | clamp 1.5 → 2.25rem | 1.2 | 700 |
| article `h1` | clamp 1.9 → 3.05rem | 1.16 | 700 |
| archive `h1` | clamp 1.9 → 3.4rem | 1.12 | 700 |

Use `clamp()` with `rem` endpoints so the scale responds to viewport without
stepping. Avoid jumps larger than ~1.6× between adjacent hierarchy levels.

## Measure

`--ngcv-measure` is the single source of truth for reading width, targeting
640–760px at desktop — roughly 65 to 75 characters per line in Inter at
1.0625rem. Wider loses the eye's return sweep; narrower forces ragged
justification.

`--ngcv-measure-wide` is for shorter non-body text that benefits from a little
air (lede, deck, caption) but must not become a wall.

Never apply `--ngcv-measure` to `.ngcv-prose` *and* also constrain it with a
container that is already narrower — that is how the 1.2.0 dead-space defect
arose: a capped prose inside an already-narrow track.

## Arabic rules

Arabic is not Latin with different glyphs. Specifically:

- **Never** force `text-transform: uppercase`. Arabic has no case.
- **Never** apply positive `letter-spacing`. It breaks the joining behaviour
  that makes Arabic script readable.
- Negative Latin tracking (`-0.01em`) must be reset to `normal` for Arabic.
- Line-height must be **greater** than Latin, not equal — Arabic script needs
  more vertical room for ascenders, descenders and diacritics. Body ≥ 1.8,
  headings ≥ 1.28.
- `text-wrap: balance` is safe; aggressive `hyphens: auto` is not.

These are enforced with `html[dir="rtl"]` / `html[lang^="ar"]` overrides next
to the base rule, never in a separate file.

## Rules

- Headings inside `.ngcv-prose` get `scroll-margin-block-start` so anchor
  targets from the table of contents clear the sticky header.
- `text-wrap: balance` on headings, `text-wrap: pretty` on body prose where
  supported.
- `overflow-wrap: break-word` on user-generated titles and excerpts — long
  Arabic and Latin words must never force horizontal overflow.
- Body text is never transformed, never animated, never transitioned.