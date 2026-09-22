# NGCV — “Future Heritage” redesign (v1.1.0)

Date: 2026-09-19 · Scope: **presentation layer only** (archive + single article)
Platform: WordPress 5.9+ / PHP 7.2+ · Digital Newspaper 1.1.18 · Polylang · Rank Math

Audit delta + design plan + validation record for the redesign of the existing
`nubian-geographic-content-views` plugin. The platform audit remains in
`AUDIT.md`; below is what changed, what was deliberately **not** changed, and
what still needs a live site to confirm.

---

## 1. Audit delta (what the redesign found in the existing code)

| # | Finding in v1.0.0 | Resolution in v1.1.0 |
|---|---|---|
| A1 | No featured story: the archive was a uniform card grid | `NGCV_Archive::has_feature()` + `feature()` render the **first post of the page’s existing query** on page 1 only (no extra query, no invented content). The same post is skipped in the grid, so nothing is duplicated |
| A2 | 3×N identical cards — no editorial rhythm | PHP-driven variants: a wide `lead` panel (only when no featured story occupies the top) and a portrait `tall` card every 5th position |
| A3 | Typography inherited from the theme; **no Arabic webfont existed anywhere on the site** (verified: only Latin families in `wp-content/fonts`) | Inter (EN) + Cairo (AR) through the theme’s own `wptt_get_webfont_url()` loader (self-hosted in `/wp-content/fonts`), Google Fonts fallback + `preconnect`, fully filterable |
| A4 | `<div class="ngcv-grid" role="list">` contained `<article>` children (invalid list semantics) | Labelled `<section>` instead; card heading levels follow the surrounding hierarchy (h3 under a section h2) |
| A5 | No language counterpart in the article header (spec 5A) | `NGCV_Polylang::counterpart_link()` (read-only) + `parts/language-counterpart.php` |
| A6 | Nothing structured for “documentary details” (spec 5C) | `parts/article-details.php`: Language + Length (words · min read) — real values only, as a `<dl>` |
| A7 | TOC was a static block | Optional **sticky rail** at ≥60rem of *article* width (container query, `@supports`-guarded, falls back to the inline TOC) |
| A8 | Orphan `·` separators when author/date data was empty | Meta line is built from the fields that exist, then joined |
| A9 | Plugin UI strings had no Arabic translation → Arabic pages showed English labels | `languages/…-ar.po` + compiled `…-ar.mo` (33 entries, 6 Arabic plural forms) |
| A10 | Logo handling | **Not rendered by the plugin.** The logo belongs to the theme header (custom logo); re-drawing it inside the archive would duplicate/modify a finalised brand asset. No asset is touched |
| A11 | Search / in-archive filtering | **Not implemented** (needs new query logic). Reported in §8 |

Nothing was found that required changing content, queries, URLs, taxonomies,
translations, or SEO behaviour — so none of those were touched.

---

## 2. Design system added

* **Palette (locked to the brand):** Deep Nile Navy `#020C30`, Nubian Gold
  `#FFDE00`, Interactive Blue `#4172CE`, plus restrained warm surfaces
  (`#FAF7F0`, `#F4EEE1`) and derived earth tones. Gold is an accent (rules,
  kickers, chips, current page, CTA text on navy); navy carries the structure;
  blue is reserved for interactive/informational elements.
* **Geometry:** radii reduced to 2–4px (archival/precise, not “soft SaaS”); one
  shadow token used sparingly; a 3px gold “rule” token as the recurring motif,
  a small 45° diamond marker, and a broken gold hairline along the masthead.
* **Typography:** Inter / Cairo with plain system fallbacks; one CSS switch
  keyed off `html[lang^="ar"]` / `html[dir="rtl"]` moves the whole plugin
  surface to Cairo. Uppercase + letter-spacing (Latin conventions that break
  Arabic joining) are disabled for Arabic, negative tracking is reset, and
  Arabic line heights are loosened.
* **Accessibility:** focus-visible rings on every plugin interactive element;
  AA-checked colour pairs (blue on white 4.64:1, muted on white 5.87:1, gold on
  navy 13.7:1, navy on gold 13.7:1 — and the small kicker on the warm surface
  moved to a darker earth tone for 6.5:1); reduced-motion block;
  `screen-reader-text` pattern scoped to `.ngcv-context`.

## 3. Archive composition

`masthead (navy)` → `deck (existing page content)` → `category nav` →
`featured story` → `More stories` + varied grid → native pagination.

* **Masthead:** gold kicker, existing page title as the single `<h1>`, live
  article count (`found_posts`, language-scoped), existing language switcher
  (on-navy variant), broken gold motif along the bottom edge.
* **Deck:** the page’s own existing content, max 52rem, gold start-rule.
* **Category nav:** existing taxonomy only, underline-based active state instead
  of pills, still linking to the normal category archives.
* **Featured story:** media + body, stacked on mobile, side-by-side at ≥1024px
  (grid columns are inline-direction aware → mirrors in RTL). Real category
  chip, real title, real excerpt, real date, real read time, navy/gold CTA. A
  post without a featured image gets a navy geometric panel — not a fake image.
* **Grid:** 1 / 2 / 3 columns (<768 / ≥768 / ≥1024); the lead panel spans the
  row; tall cards every 5th position; hairline borders; gold top-rule for cards
  without images; images inside fixed `aspect-ratio` boxes (no layout shift);
  accurate `sizes` attributes; lazy loading; one keyboard target per card (the
  image link is `tabindex="-1" aria-hidden="true"`).

## 4. Single-article composition

`header` (category chips → title → standfirst → meta → language counterpart) →
`documentary image plate` → `[sticky TOC rail | reading column]` → `article
details` → `topics` → `previous/next` → `related articles` → `language
switcher` → `comments`.

* The article body still runs through **one** `apply_filters( 'the_content', … )`
  pass (Gutenberg, shortcodes, galleries, embeds, `wp_link_pages` intact);
  heading IDs are injected at render time only and never persisted.
* The container query puts the reading column at the inline-start and the TOC
  rail at the inline-end, so LTR and RTL are structurally identical.
* The plugin still outputs **no** SEO meta and **no** structured data.
## 5. Files changed

| File | Purpose |
|---|---|
| `nubian-geographic-content-views.php` | Version 1.1.0 |
| `includes/class-assets.php` | Adds the `ngcv-card-tall` image size, `image_sizes_attr()`, brand webfont loading (`enqueue_fonts`, `fonts_url`, `resource_hints`) and the opt-in theme-typography filter |
| `includes/class-archive.php` | `found_posts()`, `has_feature()`, `feature()`, `card_variant()` and a feature-aware `grid()` (rewind + skip + heading level) |
| `includes/class-single.php` | New Unicode-aware `word_count()`; `reading_time()` reuses it (no behaviour change) |
| `includes/class-polylang.php` | New read-only `counterpart_link()` |
| `templates/archive-articles.php` | Masthead, deck, category nav, feature, labelled grid section; unchanged query/pagination calls |
| `templates/single-article.php` | Header + counterpart, image plate, layout grid with optional sticky TOC, details/topics footer, unchanged body pipeline |
| `templates/parts/article-card.php` | Variant + heading-level aware card, accurate `sizes`, no-media state, chip label |
| `templates/parts/featured-article.php` | **New** — featured story |
| `templates/parts/article-details.php` | **New** — language / length details |
| `templates/parts/language-counterpart.php` | **New** — header language link |
| `templates/parts/article-meta.php` | Separator-safe meta line, chip styling |
| `templates/parts/related-articles.php` | Cards render as h3 under the section h2 |
| `templates/parts/table-of-contents.php` | Label styling only |
| `assets/css/tokens.css` | Rewritten: brand tokens, typography switch, shared primitives (chip, label, media box, breadcrumbs, language nav, sr-text) |
| `assets/css/archive.css` | Rewritten: masthead, deck, category nav, feature, grid/cards, empty state, pagination |
| `assets/css/single.css` | Rewritten: header, plate, layout + sticky TOC, prose, details, tags, navigation, related, comments |
| `assets/css/responsive.css` | Rewritten: breakpoints, rhythm, RTL refinements, reduced motion, overflow safety |
| `languages/…-ar.po` / `…-ar.mo` | **New** — Arabic UI strings (6 plural forms) |
| `languages/…-pot` | Regenerated (32 entries, accurate `file:line` references) |
| `readme.txt` | Design notes, new filters, configuration notes, 1.1.0 changelog |
| `_audit/tools/*.php` | Dev-only checkers (not shipped): POT/MO builder, MO verifier, class/CSS/API checks |

Unchanged by design: query logic, template routing, the plugin↔Polylang/Rank
Math integration surface, `uninstall.php`, `templates/parts/breadcrumbs.php`,
`templates/parts/language-switcher.php`, `templates/parts/pagination.php` and
`assets/js/content-views.js`.

## 6. Verification actually performed

Environment: Windows, PHP 8.3.33 CLI. **No WordPress installation exists in this
workspace**, so nothing below is a browser test.

| Check | Tool | Result |
|---|---|---|
| PHP syntax | `php -l` over all 20 shipped `.php` files | all pass |
| Class/method references from templates | `_audit/tools/check-api.php` | 25/25 resolve; all 9 `ngcv_get_template()` parts exist |
| Class coverage (PHP ↔ CSS) | `_audit/tools/check-classes.php` | no unintended gaps; leftovers are asset handles, custom properties, image-size names and concatenated variant classes |
| CSS structure | `_audit/tools/check-css.php` | braces balanced in all 4 files; no undeclared custom properties; `!important` limited to 6 declarations |
| gettext strings ↔ POT | `check-api.php` | all present (the single reported “missing” item is an `_x()` context, present as `msgctxt`) |
| Arabic MO validity | `_audit/tools/verify-mo.php` | magic `0x950412de`, 33 entries, `Plural-Forms: nplurals=6…`, 6 forms for “%s min read”, every value valid UTF-8 (verified by code point) |
| Contrast | manual WCAG AA calculation | see §2; one value corrected (kicker on the warm surface) |
| Fixed during review | — | grid-column/row placement for the TOC rail (auto-placement would have put the article text in the narrow column) |

**Not verified (needs the live site):** rendered pages in both languages, real
posts-per-page, real Polylang relationships and URL method, Rank Math output,
sidebar-layout interaction, LiteSpeed cache behaviour, `the_content` filter
output, and actual thumbnails for the new sizes.

## 7. Manual checks still required on the live site

1. Assign **“NGCV — Articles Archive”** to the Articles page (EN) and its Arabic
   translation; confirm the masthead shows the page’s own title/description.
2. Archive: page 1 (featured story + grid) and page 2+ (`?paged=2`, where the
   lead card returns) in EN and AR; check pagination links and the category nav.
3. Confirm the sticky TOC appears only when the primary column is wide enough,
   and that the layout still behaves with sidebars enabled.
4. Arabic: Cairo loads, no horizontal overflow, arrows/meta mirror correctly,
   plural strings read naturally.
5. Polylang: header counterpart link + end-of-article switcher point to the real
   translations; a post without a translation shows neither.
6. Rank Math: `<title>`, canonical, meta description, breadcrumbs and JSON-LD
   unchanged; exactly one breadcrumb trail and one `<h1>`.
7. Regenerate thumbnails once so `ngcv-card` / `ngcv-card-tall` exist for older
   media, then purge the page cache (LiteSpeed).
8. Long titles/excerpts, posts with no featured image, empty categories, and a
   post with no manual excerpt.

## 8. Unresolved issues / opportunities (reported, not implemented)

* **Search & in-archive filtering** — not supported today; adding it means new
  query logic plus a filter UI (outside the presentation-only remit).
* **Sharing controls** — none exist in the plugin or the theme, so none are
  rendered (inventing them would add markup and privacy considerations).
* **Media credit line** — only the attachment caption exists; no credit field
  was found, so no credit is displayed.
* **Fonts on a non-Digital-Newspaper theme** — falls back to the Google Fonts
  stylesheet (an extra third-party request) unless `ngcv_webfont_url` is
  filtered.
* **Container queries** — on browsers without support the TOC stays inline
  (safe), but the two-column article composition is unavailable.
