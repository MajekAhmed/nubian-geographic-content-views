# Motion

NGCV feels alive because of **precise geometry and considered rhythm**, not
because things move. The motion budget is small and every item in it must
earn its place.

## Ceiling

**420ms absolute maximum.** Most interactions land between 150ms and 300ms.
Anything slower is a delay the reader pays on every hover.

| Interaction | Duration | Easing |
|---|---|---|
| colour / background / border | `--ngcv-motion-fast` (0.16s) | `ease` |
| elevation, lift, image scale | `--ngcv-motion-med` (0.28s) | `ease` |
| entrance (hero, section reveal) | 0.38–0.42s | `cubic-bezier(0.22, 0.61, 0.36, 1)` |

Reuse the two tokens. Do not introduce a third duration mid-file.

## What may animate

- **Hero entrance** — one-time, `opacity` + a short `translateY`. Distance is
  small (≤16px). Runs once, never on scroll-back.
- **Section reveal** — one-time, same treatment. Applied by a single
  `IntersectionObserver` adding `.is-visible`.
- **Card hover** — `translateY` of −2 to −4px, elevation token, and a small
  image `scale` (≤1.04). All three together read as one gesture.
- **Title underline sweep** — `background-size` from `0% 2px` to `100% 2px`.
  No layout shift, no reflow.
- **Category / pagination / button states** — colour transitions only.
- **TOC active state** — colour and weight, with a persistent gold underline
  so state is *also* conveyed without motion.

## What must never animate

- `.ngcv-prose` and everything inside it — no transform, no opacity fade, no
  scroll-linked effect. Text that moves while being read is hostile.
- Anything longer than 420ms.
- Repeated, looping, or attention-seeking animation.
- Anything that shifts layout on hover (use transform and shadow, never
  `padding`, `margin` or `border-width`).
- Motion as the *only* signal of state. Every hover/reveal state must also be
  legible when animation does not run.

## Elevation philosophy

Cards are **flat at rest** — a hairline border and the page surface define them.
Elevation appears only on interaction. A permanently raised card grid reads as a
generic SaaS template; a flat grid that lifts on hover reads as print.

This is why the shared card component has no rest-state shadow token.

## Reduced motion

Every entrance is declared inside a `no-preference` guard rather than being
cancelled by a later `reduce` override:

```css
@media (prefers-reduced-motion: no-preference) {
	.ngcv-reveal {
		opacity: 0;
		transform: translateY(14px);
		transition: opacity var(--ngcv-motion-slow), transform var(--ngcv-motion-slow);
	}
	.ngcv-reveal.is-visible {
		opacity: 1;
		transform: none;
	}
}
```

This ordering matters. Under `reduce`, `.ngcv-reveal` simply has no base rule,
so content is visible immediately with zero flash and zero JavaScript
dependency. The reveal pattern must be **progressive enhancement**: the
`.is-visible` class is added by JS, and if JS never runs, CSS must not leave
content hidden. Therefore the hiding rule must be gated on a JS-set root
class (or the `no-preference` + JS pairing) — never an unconditional
`opacity: 0`.

Smooth in-page scrolling for TOC links is skipped entirely under `reduce`, so
the browser's native instant jump is used — which is also the more accessible
behaviour.

## JavaScript budget

One `IntersectionObserver`, one pass, no polling, no scroll listener. Feature
detect it. If `IntersectionObserver` is missing, leave the content visible and
skip the class toggling entirely.