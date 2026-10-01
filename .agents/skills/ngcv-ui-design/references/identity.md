# Nubian Geographic identity

## Locked tokens

These three are the brand. They are never redefined — not in a dark-mode
block, not in a component, not in a template.

| Token | Hex | Role |
|---|---|---|
| `--ngcv-navy` | `#020C30` | Deep Nile Navy — structure, the masthead band, text on light surfaces |
| `--ngcv-gold` | `#FFDE00` | Nubian Gold — accents only: rules, kickers, active states, CTA text on navy |
| `--ngcv-blue` | `#4172CE` | Interactive Blue — links, focus, informational |

Supporting tones (`--ngcv-navy-soft`, `--ngcv-nile`, `--ngcv-sand`,
`--ngcv-earth`, `--ngcv-earth-dark`) are derived and used sparingly.

**Gold is an accent, never a wash.** If gold covers more than roughly 5% of a
viewport it stops being an accent and starts shouting.

## What must never be invented

This is the hard constraint of the whole design system.

- No hieroglyphs, glyph-like marks, or pseudo-Egyptian ornament.
- No archaeological decoration, excavation motifs, or artefact styling.
- No "generic Nubian pattern" CSS — no repeating tribal-looking gradients.
- No fake parchment, papyrus, sand, or ancient-paper textures.
- No decorative archaeological icons, borders, or corner flourishes.
- No invented cultural symbols of any kind.
- No slogans or taglines of our own invention.
- No modification of the logo, and no re-drawing of it inside the plugin. The
  logo belongs to the theme header.

If a proposal needs a motif and none exists, the answer is a precise geometric
element, not an invented cultural one.

## What the identity actually comes from

The Nubian Geographic character is carried by:

1. **Editorial composition** — generous but disciplined measure, clear
   hierarchy, sectioning a reader can navigate by eye.
2. **Photography** — documentary images treated as evidence, not decoration.
3. **Typography** — Inter/Cairo at a considered scale.
4. **Precise geometry** — hairline rules, a 3px gold rule token, small
   diamonds, 2–3px radii. Archival and technical, never soft.
5. **Deep navy surfaces** — the navy band is the site's signature surface.
6. **Restrained gold accents** — used sparingly and deliberately.
7. **Whitespace as structure** — space separates sections; it is not leftover.

## Permitted motifs

Abstract geometry only:

- a broken or solid gold hairline rule (`--ngcv-rule`, 3px);
- a small rotated square (45°) used as a marker;
- hairline grids and column rules;
- a two-tone gold rule with a deliberate gap.

The section divider is the recurring marker: a full-width hairline, a small
gold diamond breaking it, and the heading. Geometric and abstract by
construction.

## Surface semantics

Token names describe *role*, never colour. `--ngcv-surface-raised` is correct
in both light and dark; `--ngcv-white` becoming `#242429` is a naming defect
that misleads the next reader.

| Semantic token | Light | Dark |
|---|---|---|
| `--ngcv-surface-page` | `#FFFFFF` | `#16161B` |
| `--ngcv-surface-raised` | `#FFFFFF` | `#242429` |
| `--ngcv-surface-warm` | `#FAF7F0` | `#1E1E24` |
| `--ngcv-surface-sand` | `#F4EEE1` | `#1A1A1F` |
| `--ngcv-ink` | `#111827` | `#F2F3F7` |
| `--ngcv-muted` | `#5B6472` | `#B9C0CE` |
| `--ngcv-hairline` | `#E4E7EC` | `#33333D` |
| `--ngcv-hairline-strong` | `#CBD2DC` | `#4B4B57` |

In dark mode the *page* surface is darker than the *raised* surface, so cards
recede forward instead of dissolving into the background.

## Dark mode

Driven by the theme's own `body.digital_newspaper_dark_mode` class — NGCV adds
no independent colour-scheme switch and never uses `prefers-color-scheme`
alone, because the user has already chosen a theme mode.

Rules:

- No `filter: invert()`. Re-tune semantic tokens instead.
- The three brand tokens stay untouched.
- Navy is used as a *surface* in dark mode (softened to `--ngcv-navy-soft`),
  never as text — so components that use navy for text must flip to
  `--ngcv-ink`.
- Any small text using an earth/sand tone must be re-tuned for AA contrast on
  the dark surface. This is the most commonly missed step.
- Keep the dark block short: it is one pattern ("navy as text ⇒ flips to ink")
  repeated, not dozens of one-off overrides.