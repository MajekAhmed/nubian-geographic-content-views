# Responsive

## The four breakpoints

| Range | Name | Archive grid | Single article |
|---|---|---|---|
| ≥1440px | wide desktop | 3 columns | rail + measure + gutters |
| 1024–1439px | desktop | 3 columns (narrower rail) | rail + measure + gutters |
| 768–1023px | tablet | 2 columns, lead card spans row | rail collapses to a toggle block |
| <768px | mobile | 1 column | 1 column, collapsible TOC |

These are `min-width` mobile-last queries, matching the existing
`assets/css/responsive.css` convention. Nothing lives in a `max-width` query
except the two genuinely corrective cases (the WordPress admin bar offset,
and the mobile-only tightening) — both already documented in the file.

## Container

`.ngcv-container` is fluid with a cap, so it needs no per-breakpoint override.
Only the cap and the gutter tokens move:

```css
--ngcv-container-max: 72rem;   /* base */
--ngcv-container-max: 76rem;   /* ≥1024px */
--ngcv-container-max: 80rem;   /* ≥1440px */
--ngcv-gutter: clamp(1.15rem, 3.5vw, 4rem);
```

The gutter is fluid, so the mobile container is always
`100% - 2 * gutter` and never touches the viewport edge.

## Grid

```css
.ngcv-grid {
	display: grid;
	grid-template-columns: minmax(0, 1fr);
	gap: var(--ngcv-space-4) var(--ngcv-space-3);
}
@media (min-width: 768px)  { .ngcv-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (min-width: 1024px) { .ngcv-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
```

`minmax(0, 1fr)` everywhere — a plain `1fr` will not shrink below its content
and is the classic cause of horizontal overflow from a long unbroken word.

The `lead` card variant spans the full row and splits `1.7fr / 1fr` at
tablet and above. It must stay a stacked flex column below 768px.

## Card media

`aspect-ratio` is fixed per context and never changes at a breakpoint that
would shift layout unexpectedly:

- standard / related card: `8 / 5`
- `lead` card: fills its column, `min-block-size` floor instead of a ratio
- archive hero plate: `21 / 9` at desktop, `16 / 9` below
- article plate: `16 / 9` mobile, `21 / 9` desktop

## TOC behaviour

- **≥1024px** — a true sticky rail, `max-block-size` bounded with
  `overflow-y: auto` so a long contents list never runs off-screen.
- **768–1023px** — the toggle button becomes useful and the list is
  collapsible; the block sits above the prose, full measure width.
- **<768px** — collapsible, full width, no shadow, flatter framing, tighter
  item padding.

## Category navigation

Horizontal scroll on small screens with an edge fade, so long taxonomies never
overflow the page:

```css
.ngcv-cat-nav--bar .ngcv-cat-list {
	overflow-x: auto;
	flex-wrap: nowrap;
	scrollbar-width: thin;
	mask-image: linear-gradient(to var(--ngcv-fade-dir, right), #000 85%, transparent);
}
```

`--ngcv-fade-dir` flips for RTL so the fade always sits on the trailing edge.
`scroll-snap-type: inline proximity` keeps it calm.

## Share controls

- Labels (`aria-label`) always present; visible text collapses to
  `screen-reader-text` below 768px so five networks plus two buttons fit one
  row without wrapping or overflowing.
- Every control is ≥44×44px on small screens.
- `flex-wrap: wrap` as the safety net.

## Sticky elements and the admin bar

`body.admin-bar` shifts `inset-block-start` on sticky items: 32px desktop,
46px below 783px. WordPress's admin bar height differs between breakpoints,
and a sticky bar that hides behind it is worse than a non-sticky one.

## Images

`sizes` must match the real layout, not a guess. At desktop a 3-up card is
~33% of a capped container, not 33vw — on a 1920px screen the container caps
while the viewport does not, so `33vw` would make the browser download a
candidate far larger than it renders. Keep `sizes` proportional to the
container, expressed in viewport terms.

## Never

- Never introduce a horizontal scrollbar at the page level. Test with a long
  unbroken Arabic *and* Latin string.
- Never let `100vw` be used for a full-bleed element — it includes the
  scrollbar gutter and overflows. Use `100%`.