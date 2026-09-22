# NGCV — Phase 1 Audit (nubian-geographic-content-views)

Date: 2026-09-17 · Status: **Audit complete — awaiting approval for Phase 2**
Target: WordPress + Digital Newspaper **1.1.18** (BlazeThemes) + Polylang + Rank Math

## 0. Audit method and what could / could not be verified

This workspace contains **no WordPress installation** (only `AGENTS.md`). Therefore:

- **Verified (hard evidence):** the full source of the active theme, downloaded
  byte-exact from WordPress.org as `digital-newspaper.1.1.18.zip` (1,633,336 bytes),
  extracted to `_audit/digital-newspaper-1.1.18/`. Every statement about the theme
  below cites that source. Polylang and Rank Math integration points verified from
  their official developer documentation (public APIs).
- **NOT verifiable from here (needs live site access):** the active plugin list,
  Polylang runtime configuration (languages, URL method), Rank Math settings
  (breadcrumbs on/off, schema module), Page ID 2509 (existence, template, content),
  posts-per-page, menu assignments, LiteSpeed settings, and actual article content.
  These are listed in §14 with the exact check needed for each.

No code was written. Nothing was changed. No content was touched.

## 1. Theme profile — Digital Newspaper 1.1.18

- **Classic PHP theme** (no FSE/block templates). `style.css` header: Version 1.1.18,
  Tested up to 6.9, Author BlazeThemes. Ships `style-rtl.css` and registers RTL via
  `wp_style_add_data( 'digital-newspaper-style', 'rtl', 'replace' )`.
- Supports: `title-tag`, `post-thumbnails`, `custom-logo`, `html5`, `automatic-feed-links`.
- Image sizes registered: `digital-newspaper-featured` 1020×700c, `-list` 600×400c,
  `-thumb` 300×200c, `-small` 150×95c, `-grid` 400×250c (all hard-crop).
- Text domain `digital-newspaper`; ships `wpml-config.xml` (admin-texts for
  `theme_mods_digital-newspaper`: ticker title, button text, block titles…).
  Polylang String Translation reads `wpml-config.xml` too, so those customizer
  strings are translatable without any theme change.
- **No Polylang-specific code in the theme** (no `pll_*` calls anywhere). The theme
  is multilingual-neutral: it relies on `language_attributes()` for `dir`/`lang`
  and on `is_rtl()` (used once, in the ticker).

## 2. Template hierarchy (actual, from source)

Root templates: `index.php` (blog home + global fallback + front-page sections),
`archive.php`, `single.php`, `page.php`, `search.php`, `404.php`,
`sidebar.php`, `sidebar-left.php`, `comments.php`. **No `home.php`, no
`front-page.php`** (front page logic lives inside `index.php`/`page.php` via
`homepage_content_order` customizer + banner hooks).

Template parts: `template-parts/content.php` (archive/home card),
`content-single.php`, `content-page.php`, `content-search.php`, `content-none.php`,
plus front-page section parts (banner, ticker, news-grid/list/filter/carousel).

## 3. Common page shell (all content templates)

```
get_header()
  <html language_attributes()> → Polylang sets lang/dir
  <body body_class() schema_body_attributes()>
    #page.site → skip-link → #primary
    header#masthead (top header + main header, menus menu-1/menu-2)
    footer.php: #colophon (footer widgets, bottom footer, menu-3) → wp_footer()
#theme-content
  do_action('digital_newspaper_before_main_content')
  <main id="primary" class="site-main width-{layout}">
    .digital-newspaper-container > .row
      .secondary-left-sidebar  → get_sidebar('left')
      .primary-content
        do_action('digital_newspaper_before_inner_content')  ← breadcrumb renders here
        .post-inner-wrapper → loop / content part
      .secondary-sidebar → get_sidebar()
get_footer()
```

## 4. Article rendering flow — single post

`single.php` → shell above → `template-parts/content-single.php`:

```
<article {schema Article microdata} id="post-N" post_class()>
  header.entry-header
    the_category()                       ← category chips
    the_title( <h1 class="entry-title"> )
    .entry-meta: posted_by · posted_on · comments · read-time (str_word_count/100)
    digital_newspaper_post_thumbnail()   ← full-size featured image
  div.entry-content {schema articleBody}
    the_content( 'Continue reading…' )   ← full WP pipeline, wp_link_pages()
  footer.entry-footer → tags_list + entry_footer (edit link)
  the_post_navigation() (prev/next)
  comments_template() if open
  do_action('digital_newspaper_single_post_append_hook')
     └─ hooked: digital_newspaper_single_related_posts
        (2 posts, category__in, exclude current, customizer on/off,
         filterable via digital_newspaper_query_args_filter)
```

## 5. Articles page (the "Stories & Insights" page entity)

Current flow (any static page, incl. ID 2509 if it exists as a page):
`page.php` → shell → `template-parts/content-page.php`:
`article.post-… > header.entry-header > h1.entry-title` + thumbnail +
`.entry-content > the_content()`. Sidebar layout from customizer
`page_sidebar_layout` (default shell shows left+right sidebars).
Body classes include `page page-id-2509 …`.

⚠️ Unverified live: whether 2509 is a **regular static page** (then a registered
page template applies) or the **assigned posts page** in Settings→Reading
(`is_home()` — page templates do NOT load for the posts page; routing must
handle `is_home()` instead). Also unverified: what its content currently
contains (intro text? shortcode/block list?), and its Polylang translation ID.

## 6. Archive rendering flow (category/tag/date — used by category archives)

`archive.php` → shell → `header.page-header` with `the_archive_title()`
(h1.page-title) + `the_archive_description()` → `.post-inner-wrapper.news-list-wrap`
loop → `template-parts/content.php` per post (card: figure 600×400 thumb + category
overlay `ul.post-categories`, h2.post-title, meta, the_excerpt) →
`do_action('digital_newspaper_pagination_link_hook')`
→ `digital_newspaper_pagination_fnc()`: `paginate_links(type='list')` when
`archive_pagination_type === 'number'`, else `get_the_posts_navigation()`.
Posts-per-page comes from Settings→Reading.

## 7. Breadcrumb system (critical integration point)

`digital_newspaper_breadcrumb_html()` — hooked at
`digital_newspaper_before_inner_content` (priority 10) — is gated by:

- customizer `site_breadcrumb_option` (**default: true**)
- skipped on front page / blog home
- customizer `site_breadcrumb_type` (**default: 'default'**):
  - `rankmath` → `rank_math_the_breadcrumbs()` (guarded by function_exists)
  - `yoast` → `yoast_breadcrumb()`; `bcn` → `bcn_display()`
  - `default` → Breadcrumb Trail library v1.1.0 (Justin Tadlock, bundled,
    `div` container, microdata markup)

Consequence: the plugin must reproduce **exactly this switch** inside its own
templates (same settings, same guards) so there is always one breadcrumb and
Rank Math (if configured) remains the trail + its BreadcrumbList schema owner.

## 8. SEO / schema status quo

- Theme microdata is **ON by default** (`site_schema_ready` = true):
  `<body itemscope itemtype=…Blog|WebPage|SearchResultsPage>` and
  `<article itemscope itemtype=…Article>` + `itemprop name` (h1) and articleBody
  (entry-content). Rank Math additionally emits JSON-LD (Article, BreadcrumbList).
  ⇒ the site already carries two schema layers; **the plugin must add none** and
  must not remove the theme's (out of scope; theme untouched).
- `<title>`, canonical, meta description, robots, sitemap: owned by Rank Math via
  `wp_head()`; the theme supports `title-tag`. Plugin templates must keep calling
  theme `get_header()/get_footer()` so `wp_head()/wp_footer()` run untouched.

## 9. Enqueue inventory (all global today)

Styles (every page): fontawesome 5.15.3, slick.css, Google fonts via local
`wptt-webfont-loader` (families from customizer typography; defaults include
**Jost**, **Inter**), `digital-newspaper-style` (style.css [+ style-rtl.css when
RTL]) **plus large inline style** defining CSS custom properties:
`--digital-newspaper-global-preset-color-1..12`, gradients, `--site-bk-color`,
typography vars `--site-title --block-title --post-title --meta --content --menu
--submenu --single-title --single-meta --single-content`, header padding, etc.
main.css, add.css, loader.css, responsive.css.
Scripts (every page, footer, jQuery-based): slick, marquee, navigation.js,
theme.js (localized `digitalNewspaperObject`: nonce, ajaxUrl, sticky, livesearch),
waypoint. Plus an AJAX live-search endpoint `digital_newspaper_search_posts_content`.

⇒ Plugin assets must be conditional (archive/single contexts only) and must
**inherit** the theme's typography/color vars rather than loading new webfonts.

## 10. Menus, sidebars, body classes

- Menus: `menu-1` Top Header, `menu-2` Main Header, `menu-3` Bottom Footer.
  (Polylang language switcher is normally attached as a menu item — untouched.)
- Sidebars: `sidebar-1`, `left-sidebar`, `header-toggle-sidebar`,
  `front-right-sidebar`, `front-left-sidebar`, footer columns 1–4; 9 custom widgets.
- Body classes (theme-added): `hfeed`, `digital_newspaper_main_body
  digital_newspaper_font_typography`, `site-{boxed|full-width}`,
  `header-width--*`, `block-title--*`, sidebar layout classes
  (`right-sidebar|left-sidebar|both-sidebar|no-sidebar`…), `sidebar-sticky`,
  plus search-popup class. WordPress/Polylang add `rtl|ltr`, `language-*`.
  The plugin will add its own `ngcv-*` context classes only.

## 11. Polylang — verified API + design constraints

Guards mandatory (`function_exists`) for every call. Used by plugin:
- `pll_current_language( 'slug' )` — current language slug (ar/en)
- `pll_get_post( $id, $slug )` — translation post/page ID or **0/null if none**
- `pll_get_post_translations( $id )` — full translation map
- `pll_get_post_language( $id )` — a post's language
- `pll_is_translated_post_type( 'post' )` — sanity check
- `pll_the_languages( [ 'raw' => 1, 'post_id' => …, 'hide_if_no_translation' => 1,
  'echo' => 0 ] )` — build the language link for the current article/page;
  `no_translation` flag prevents broken links.

Queries: Polylang auto-filters `WP_Query` by current language; the plugin will
still pass `'lang' => $slug` explicitly for determinism, and will never call
`pll_save_post_translations` / `pll_set_post_language` (read-only adapter).
⚠️ Unverified live: installed languages, URL method (/ar/ directories vs ?lang=),
default language — none of these affect the plugin if only official APIs are used.

## 12. Conflicts & risks identified

| # | Risk | Severity | Mitigation |
|---|------|----------|------------|
| R1 | Two breadcrumbs if plugin prints its own trail | High | Reuse the theme's breadcrumb switch (§7); never print a second trail |
| R2 | Third schema layer (plugin JSON-LD/microdata) | High | Plugin emits zero schema; only semantic HTML |
| R3 | Duplicate `<h1>` (page title + card titles) | Med | Cards use h2/h3 only; single template has exactly one h1 |
| R4 | Page-template approach fails if 2509 is the posts page | Med | Router supports both: registered page template (static page) AND `template_include` guard for `is_home()` |
| R5 | Theme read-time uses `str_word_count` (breaks on Arabic) | Low | Plugin computes its own UTF-8-aware count (`\p{L}\p{N}`), only if displayed |
| R6 | Global theme CSS/JS (slick, marquee, waypoint) heavy baseline | Info | Out of scope; plugin adds only conditional assets |
| R7 | Theme writes post meta `post_width_layout` during render (extras.php) | Info | Theme behavior on any single view; plugin must not depend on or extend it |
| R8 | LiteSpeed Cache page cache | Med | No per-user output, no session-dependent HTML in templates; assets versioned via `filemtime` |
| R9 | `the_content` filters (Rank Math TOC? FooGallery?) must keep running | High | Plugin calls `the_content()` verbatim; render-time heading-ID injection is optional, filtered, and never persisted |
| R10 | Theme AJAX search / `pre_get_posts` filter | Low | Theme's filter is gated on `$_GET['digitalNewspaperargs']`; plugin adds none |
| R11 | AI/automation workflow (REST, WP-Cron, post hooks) | High | Plugin registers no REST routes, no cron, no save_post hooks, no meta writes |

## 13. Proposed routing strategy (no theme edits)

1. **Archive (Articles page):** register a real **page template**
   `ngcv-articles-archive.php` ("NGCV — Articles Archive") via the
   `theme_page_templates` filter (classic theme ⇒ works). Admin assigns it once to
   the Articles page (2509) and its Polylang translation. WordPress then routes
   natively — no URL change, page keeps its ID/slug/content. Content of the page
   (if any intro exists) renders above the grid.
   - Optional fallback (filterable, default off): a configurable page-ID detector
     (`apply_filters( 'ngcv_articles_page_ids', array() )`) for hosts where the
     template can't be assigned; verified IDs only, never hard-coded guesses.
2. **Single article:** `template_include` filter that returns the plugin's
   `single-article.php` **only** when `is_singular('post')` (and Polylang is
   active/`post` is translated type). Pages, CPTs, attachments unaffected.
3. **Safety rails:** if the plugin is deactivated, WP falls back to theme
   templates instantly — nothing breaks. Uninstall removes nothing but the plugin.
4. **Assets:** enqueued only inside the two routed contexts; body classes
   `ngcv-active ngcv-archive|ngcv-single ngcv-lang-{slug}` for scoping.

## 14. Live-verification checklist (before/with Phase 2)

1. Active plugins list (Polylang, Rank Math, LiteSpeed, FooGallery, Smush, WPForms, GenerateBlocks, automation plugins) + versions.
2. Page 2509: exists? static page or posts page? assigned template? content (blocks/shortcodes)? Polylang translation counterpart ID?
3. Polylang: languages (ar default?), URL method, language switcher location.
4. Rank Math: breadcrumbs enabled + type set to 'rankmath' in theme customizer? schema module on?
5. Settings→Reading: posts per page; blog page assignment.
6. Menus: which location holds the language switcher.
7. One sample Arabic article + one English article: blocks used, headings, galleries/embeds.
8. LiteSpeed: page cache on? (affects nothing if templates are cookie-free).

## 15. Proposed plugin architecture (Phase 2 blueprint)

```
nubian-geographic-content-views/
├── nubian-geographic-content-views.php   bootstrap: constants, autoload, lifecycle guards
├── uninstall.php                          no-op (no options/transients persisted)
├── readme.txt
├── includes/
│   ├── class-plugin.php            orchestrator; context detection; body classes
│   ├── class-template-router.php   page-template registration + template_include (single + is_home fallback)
│   ├── class-archive.php           secondary WP_Query (posts, paged, lang-aware) + pagination
│   ├── class-single.php            single-view helpers: meta, TOC prep, related, language link
│   ├── class-polylang.php          read-only Polylang adapter (all function_exists-guarded)
│   └── class-assets.php            conditional enqueue; filemtime versioning; no new webfonts
├── templates/
│   ├── archive-articles.php        get_header() … get_footer() shell preserved
│   ├── single-article.php          one h1; the_content() verbatim
│   └── parts/ article-card.php · article-meta.php · breadcrumbs.php (theme switch reuse)
│            language-switcher.php · related-articles.php · table-of-contents.php · pagination.php
├── assets/css/ tokens.css · archive.css · single.css · responsive.css   (--ngcv-* vars, logical props)
├── assets/js/  content-views.js        progressive enhancement only (TOC toggle, no-JS safe)
└── languages/                          text domain: nubian-geographic-content-views
```

Decisions already locked by the brief: presentation-only · no new taxonomy ·
no meta writes · no REST/cron · vanilla CSS/JS · CSS Grid cards · `the_content()`
untouched · Rank Math = SEO owner · Polylang = language owner · theme untouched.

## 17. Live-site corroboration (from user-provided `wp-content.zip`)

The user supplied `wp-content.zip` (truncated archive; readable portion covers
mu-plugins, languages, upgrade-temp-backup, litespeed, fonts, upgrade, themes,
and the start of `plugins/wp-smushit/`). Findings that CONFIRM audit assumptions:

- **Theme:** `themes/digital-newspaper/` present (plus inactive spares:
  `twentytwentyfive`, `blogzee`, `generatepress`, `hostinger-blog`).
- **Arabic (ar) locale fully installed** (ar.po/ar.mo/admin-ar + JSON packs).
- **Plugin set corroborated** by language packs / files: **Polylang**,
  **Rank Math** (`seo-by-rank-math`), **Google Site Kit**, **Akismet**,
  **All-in-One WP Migration**, **Smush** (`wp-smushit`, file evidence),
  **WP Migrate DB Pro** (mu-plugin compatibility shim:
  `mu-plugins/wp-migrate-db-pro-compatibility.php`).
- **LiteSpeed Cache actively configured** (`litespeed/` with UCSS, VPI, CSS
  optimization, avatar cache, auto-backup data).
- **Automation component confirmed:** `upgrade/nubian-geographic-automation-bridge-v1.1.0/`
  package (`nubian-geographic-automation-bridge.php`).
- **Typography finding:** `wp-content/fonts/` contains only Latin families
  (Outfit, Inter, Jost, Manrope, Poppins, Montserrat, DM Sans) — **no Arabic
  font files are stored in wp-content**. Arabic rendering therefore relies on
  runtime font loading (theme customizer wptt loader) and/or system fallbacks.
  The plugin's decision to *inherit* typography (add no fonts) is confirmed as
  the safe behavior; browsers fall back automatically per glyph coverage.
- Hostinger hosting context (hostinger-blog theme present) — consistent with
  the brief's "Hostinger tools".

Still not in scope of any wp-content copy (database-bound, feature-detected by
the plugin): Page 2509 (ID/template/content), Polylang languages & URL method,
Rank Math breadcrumb/schema settings, posts-per-page, menu assignments.

### 17.1 Deep offline audit — exact versions (extraction from `wp-content.zip`)

The archive was truncated mid-`wp-smushit`, but everything before the cut
extracted cleanly to `_audit/wp-content-extract/`. Verified with hard evidence:

| Component | Exact version | Evidence |
|---|---|---|
| Digital Newspaper (live copy) | **1.1.18, byte-identical to the official WordPress.org package** | SHA-256 equality on 13 render-critical files: style.css, functions.php, single.php, archive.php, page.php, header.php, footer.php, template-parts/content-single.php, inc/extras/helpers.php, inc/extras/extras.php, inc/hooks/inner-hooks.php, inc/template-tags.php, inc/template-functions.php |
| Nubian Geographic Automation Bridge | **v1.1.0** (single-file plugin) | `upgrade/nubian-geographic-automation-bridge-v1.1.0/...` main file header |
| Smush | **4.3.0** (Requires WP ≥ 6.4, PHP ≥ 7.4) | `plugins/wp-smushit/wp-smush.php` header |
| WP Migrate (Lite/Pro) compat shim | **v1.3** | `mu-plugins/wp-migrate-db-pro-compatibility.php` header |
| LiteSpeed Cache + Quic.cloud CDN | active (UCSS/VPI/CSS/avatar state + `qc.*` files present) | `litespeed/` contents |

**Automation Bridge v1.1.0 — collision analysis (read in full):** it registers
ONLY `rest_api_init` routes — `POST /wp-json/ng/v1/create-draft` (creates a
draft via `wp_insert_post`, then `pll_set_post_language`, then assigns
**existing** category/tag IDs) and `POST /wp-json/ng/v1/link-translations`
(`pll_set_post_language` for `en`/`ar` + `pll_save_post_translations`). It has
**no** `the_content`, `template_include`, front-end, cron, or save-post hooks.
⇒ **Zero interference** with NGCV (which registers no REST/cron/save hooks).
Bonus intel: languages are the slugs **`en` and `ar`**, and the bridge's
draft→language→terms ordering means every published post carries a proper
Polylang language — exactly what NGCV's `lang`-scoped archive/related queries
assume. (Bridge hard-codes `en`/`ar` in `link_translations`; NGCV never
hard-codes slugs, so it stays compatible if more languages are added.)

**LiteSpeed static-protect rules** (`wp-content/litespeed/.htaccess`,
`.../robots.txt`): scoped **only to `/wp-content/litespeed/`** (internal files
denied; generated `css|js|ucss|ccss|lqip|optimax|crawler` whitelisted). No
effect on page URLs, the plugin's templates, or `/page/N/` pagination. UCSS is
enabled, so new NGCV templates get their unique CSS generated lazily on first
visit — a purge after deployment is the only recommended step.

**Environment floor:** Smush 4.3.0 requires WP ≥ 6.4 / PHP ≥ 7.4 ⇒ NGCV's
requirements (WP ≥ 5.9 / PHP ≥ 7.2) are safely below the site's floor.


## 18. Phase 2–5 implementation notes (built after audit approval)

Delivered exactly per §15 blueprint: bootstrap + 6 classes + 2 templates +
7 parts + 4 CSS files + 1 vanilla JS + uninstall + readme + POT.
Key behaviors implemented as audited:

- Archive routed by a **real assignable page template** (`theme_page_templates`
  filter + priority-99 `template_include` swap — required because classic-theme
  page templates resolve inside the theme folder only). Optional `is_home()`
  fallback strictly gated by verified IDs via `ngcv_articles_page_ids`.
- Single routed for `is_singular('post')` only; feeds/embeds/trackbacks never
  touched; admin/REST/cron untouched.
- Both templates reproduce the theme's exact shell (`#theme-content` →
  `before_main_content` → `main#primary.site-main.width-X` → container/row →
  `secondary-left-sidebar` / `primary-content` (fires the theme breadcrumb hook)
  / `secondary-sidebar`) via `NGCV_Plugin::shell_open()/shell_close()`, so the
  theme's configured sidebar layouts and breadcrumbs apply unchanged.
- Article content: single full pipeline run via
  `apply_filters( 'the_content', get_the_content() )` with a late-priority,
  render-time-only h2/h3 ID injection (reuses existing IDs; never persists);
  `wp_link_pages`, tags, `the_post_navigation`, `comments_template` preserved.
- Breadcrumb part is a fallback ONLY (renders if the theme hook produced
  nothing) — always exactly one trail; Rank Math-aware.
- Polylang adapter is read-only; missing translations are never linked;
  switcher hides when < 2 usable languages; archive/related queries pass
  `'lang' => current-slug` explicitly.
- Reading time: Unicode-aware (`\p{L}\p{N}`), 180 wpm default (filterable);
  theme's `str_word_count` (Arabic-broken) not used.
- Zero schema/SEO output; assets conditional per context; `filemtime`
  versioning; `prefers-reduced-motion`, `:focus-visible`, logical CSS
  properties throughout (single system for RTL/LTR).





