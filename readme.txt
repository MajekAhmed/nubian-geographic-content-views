=== Nubian Geographic – Content Views ===
Contributors: nubianceographic
Tags: editorial, magazine, multilingual, rtl-language-support, accessibility-ready
Requires at least: 5.9
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 1.2.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional editorial presentation for the Nubian Geographic articles archive and single articles. Presentation layer only.

== Description ==

This plugin provides the presentation layer (archive + single-article views) for the
existing Nubian Geographic WordPress article system. It is deliberately a
**presentation-only** component:

* It does **not** create or modify posts, pages, categories, tags, media, or authors.
* It does **not** create new taxonomies, meta fields, REST routes or cron jobs.
* It stores two presentation-only settings (Articles page ID and archive hero
  image attachment ID) and nothing else; see **Configuration** below.
* It does **not** change URLs, slugs, permalinks, or the sitemap.
* It does **not** output SEO metadata, canonical tags, or structured data — Rank Math remains the SEO authority.
* It does **not** touch Polylang translations; it only reads existing language relationships (read-only).
* It renders article content through WordPress' native `the_content()` pipeline, so Gutenberg blocks, galleries, embeds, and shortcodes are fully preserved.
* Templates call the active theme's `get_header()` / `get_footer()`, so the Digital Newspaper header, footer, menus, and sidebars remain intact.
* Deactivating the plugin instantly restores the theme's default presentation — nothing breaks.
* Uninstalling performs **no destructive database operation** (the two settings
  above are the only stored values; content is never touched).

Templates can be overridden from a child theme by placing copies in
`<child-theme>/ngcv-templates/…` using the same relative paths.

== Design ==

The views follow the “Future Heritage / التراث في المستقبل” art direction:

* **Archive** — a navy masthead (existing page title, existing description as
  the editorial deck, live article count, language switcher), a precise-line
  category navigation built from the existing taxonomy, a **featured story**
  taken from the first post of the page's existing query, and an editorially
  varied discovery grid (a wide lead panel plus periodic portrait-format
  cards) with native pagination.
* **Single article** — category chip, compact language-counterpart link, strong
  title hierarchy, documentary image plate with its real caption, an optional
  sticky table of contents on wide screens, a comfortable reading measure,
  structured article details (language and length — both real values only),
  topics, previous/next, related articles and the language switcher.
* **Typography** — Inter for English, Cairo for Arabic (self-hosted through the
  theme's local webfont downloader when available), with system fallbacks.
* **Direction** — all layout uses CSS logical properties, so RTL mirrors without
  a second stylesheet; uppercase/tracking details are disabled for Arabic.
* Arabic UI strings for this plugin ship in `languages/…-ar.mo`.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` (or install the zip via Plugins → Add New → Upload).
2. Network/normal activation as usual.
3. In **Pages → Edit**, assign the **“NGCV — Articles Archive”** page template to the
   Articles / Stories & Insights page (English) **and** to its Polylang Arabic
   translation page. This is the primary route. The page keeps its ID,
   slug, URL, and existing content — only its presentation changes.
4. Single articles (post type `post`) automatically use the plugin's article view.
   No configuration is needed; pages and other post types are never affected.

== Configuration ==

Open **Settings → NGCV Content Views** (one screen, WordPress Settings API):

* **Articles page** (`ngcv_articles_page_id`) — only needed when the Articles
  page is the WordPress Posts page (Settings → Reading). Static pages should
  use the “NGCV — Articles Archive” page template instead. When Polylang is
  active, the selected page's translation in the current language is resolved
  automatically.
* **Archive hero image** (`ngcv_archive_hero_image_id`) — optional. Overrides
  the Articles page featured image in the archive hero band. Resolution order:
  setting image → page featured image → plain navy background. Leave empty to
  fall back. The image applies to all languages.

Optional filters for developers:

* `ngcv_use_single_template` (bool, default true) — disable the single-article override.
* `ngcv_articles_page_ids` (array, default empty) — legacy fallback: treat the listed
  page IDs as the articles archive. Use only for verified IDs.
* `ngcv_enable_toc` (bool, default true) — disable the table of contents.
* `ngcv_toc_min_items` (int, default 3) — minimum h2/h3 count before a TOC is shown.
* `ngcv_reading_time_wpm` (int, default 180) — reading speed used for the read time.
* `ngcv_related_query_args` (array) — modify the related-articles query.
* `ngcv_archive_query_args` (array) — modify the archive grid query.
* `ngcv_show_category_nav` (bool, default true) — hide the category chip navigation.
* `ngcv_template_path` (string) — relocate a template file.
* `ngcv_show_featured_story` (bool, default true) — disable the featured story on
  archive page 1 (the grid then starts with a wide lead card again).
* `ngcv_archive_lead_card` (bool, default true) — disable the wide lead card.
* `ngcv_archive_tall_card` (bool, default true) — disable the portrait-format cards.
* `ngcv_archive_card_variant` (string, $position) — force a card variant.
* `ngcv_webfonts_enabled` (bool, default true) — do not load Inter/Cairo.
* `ngcv_webfont_families` (array) — change the requested Google Fonts families.
* `ngcv_webfont_url` (string) — point the font stylesheet at your own hosting.
* `ngcv_inherit_theme_typography` (bool, default false) — use the theme
  customizer's families inside the plugin views instead of the brand families.
* `ngcv_counterpart_links` (array, $post_id) — change the header language links.
* `ngcv_articles_page_setting` (int) — override the Articles page ID at render.
* `ngcv_archive_hero_image_setting` (int) — override the hero image ID at render.
* `ngcv_enable_posts_page_archive` (bool, default true) — opt out of treating
  the WordPress Posts page as an NGCV archive target.

== Configuration notes ==

* **Media** — the plugin registers two additional image sizes (`ngcv-card`,
  `ngcv-card-tall`; `ngcv-hero` already existed). They apply to newly uploaded
  media. For existing libraries, run a thumbnail regeneration once
  (Smush or any regeneration tool) so the portrait crop exists; until then the
  browser falls back to the closest available size, so nothing breaks.
* **Fonts** — when the active theme provides the `wptt_get_webfont_url()`
  helper (Digital Newspaper does), the Inter/Cairo CSS and font files are
  downloaded once into `/wp-content/fonts` and served locally. Otherwise the
  Google Fonts stylesheet is used with a `preconnect` hint.
* **Page cache** — pages are identical for every visitor, so any page cache
  (including LiteSpeed) can cache them safely. Purge once after deploying.

== Frequently Asked Questions ==

= Does it duplicate breadcrumbs or SEO schema? =
No. Breadcrumbs are rendered through the active theme's own breadcrumb pipeline
(which already supports Rank Math, Yoast, Breadcrumb NavXT, or its built-in trail),
so there is always exactly one breadcrumb. The plugin outputs **zero** SEO meta
and **zero** structured data.

= Does it work with Arabic (RTL)? =
Yes. `<html dir/lang>` is controlled by WordPress + Polylang as before. All plugin
CSS uses logical properties (inline/block, start/end) so one component set serves
both directions.

= Does it work with LiteSpeed Cache? =
Yes. Pages are identical for all visitors (no cookies/session logic), and plugin
assets are standard enqueued CSS/JS cacheable by any page cache.

= What happens if I deactivate or delete the plugin? =
WordPress falls back to the theme's default `single.php` / `page.php` templates.
No content, URL, or SEO state is lost.

== Changelog ==

= 1.2.1 =
* Visual redesign of the archive and single-article presentation. Layout,
  hierarchy, reading experience, responsiveness, editorial polish and motion
  only: no content, query, taxonomy, translation, URL, permalink or SEO
  behaviour changed, and no theme file was modified.
* NGCV container system (`.ngcv-container`): NGCV no longer depends on the
  active theme's content width. The container is fluid, gutter-bounded,
  centred and capped, using CSS logical properties only, so the same
  composition serves LTR and RTL without a duplicated page layout.
* Single-article reading grid: the reading column sits on a centred
  `1fr | rail | measure | 1fr` track (42rem measure), with the table-of-contents
  rail in the slack beside it. The rail is sticky only at ≥64rem, where it is
  genuinely a rail; below that the same semantic `<nav><ol>` collapses into a
  toggle. Articles with no table of contents reserve the rail track, so both
  shapes share one optical axis.
* Shared component stylesheets under `assets/css/components/` — `layout.css`
  (container, section rhythm, editorial divider), `card.css` (one authoritative
  card for the archive grid, the lead variant and related articles) and
  `actions.css` (buttons, pagination, share controls). The duplicated card CSS
  that previously existed in both `archive.css` and `single.css` is removed.
* Archive: editorial hero, scrollable category bar, dominant featured story,
  3/2/1-column discovery grid, centred pagination, and the editorial divider
  (hairline + centred gold diamond) replacing the repeated gold underline.
* Motion: a single pure-CSS entrance keyframe in
  `assets/css/components/layout.css`, inside a
  `prefers-reduced-motion: no-preference` guard, so the archive keeps shipping
  zero JavaScript and the entrance behaves identically with or without JS. One
  `IntersectionObserver` in `assets/js/content-views.js` drives only the
  table-of-contents scroll-spy. Nothing animates `.ngcv-prose` or body text,
  and under reduced motion TOC links fall back to an instant native jump.
* Dark mode keeps semantic surface tokens (`--ngcv-surface-*`, `--ngcv-ink`,
  `--ngcv-hairline`); no `filter: invert()`, and Nubian Gold `#FFDE00` is
  preserved as the identity colour.
* Fix: the archive "Editorial polish" block was nested inside
  `.ngcv-cat-link:hover::after`; the selector is closed correctly and the rules
  restored in the shared card component.
* Fix: a missing closing brace in `single.css` nested the hero plate, reading
  grid, table of contents and prose rules inside the article-meta rule, so the
  browser discarded them.
* Fix: the table-of-contents entry number was a sibling of the block-level link
  and so rendered on its own line; the counter now sits on the link.
* Fix: the dark-mode previous/next hover rule targeted `.ngcv-nav-link`, a class
  the markup never emits (WordPress core's `.nav-links a` is used instead).
* Restrained print treatment: navigation, share controls and the table of
  contents are dropped and the article is kept readable.
* New `ngcv-ui-design` skill under `.agents/skills/` recording the permanent
  design rules, with references for layout, typography, motion, responsive
  behaviour, RTL/LTR and identity.

= 1.2.0 =
* Archive hero image setting: optional image picker on the NGCV settings
  screen (`ngcv_archive_hero_image_id`, sanitized to a real image attachment
  ID, media library loaded on the NGCV screen only). Resolution order:
  setting image → Articles page featured image → plain navy background.
* Articles page setting: the NGCV settings screen selects the Articles page
  (`ngcv_articles_page_id`, sanitized to a published page or 0) so the archive
  view also renders when that page is the WordPress Posts page
  (Settings → Reading). Page-template routing is unchanged.
* Archive redesign: navy hero masthead with the existing page title, existing
  description as the deck, live article count, precise-line category navigation
  (`ngcv-cat-nav--bar`), featured story from the page's existing query and a
  rhythm-driven discovery grid with native pagination.
* Single-article redesign: category chip, compact language-counterpart link,
  documentary image plate with caption, optional sticky table of contents,
  article-details panel and restyled related articles.
* Review fixes: valid grid semantics (labelled section instead of an invalid
  list wrapper), card titles at h3 under the section h2, and TOC list semantics.
* Sharing: every single article now offers Facebook, X, WhatsApp, Telegram and
  LinkedIn share links, a copy-link control (with visible and screen-reader
  feedback) and the device share sheet (Web Share API) where the browser
  supports it. No third-party scripts, cookies or tracking; the article
  permalink and title are the only data sent.
* Routing fix: the WordPress Posts page (Settings → Reading) now always renders
  the NGCV archive view — with or without the page template. Static pages keep
  using the “NGCV — Articles Archive” page template. Opt out with the
  `ngcv_enable_posts_page_archive` filter (default true); the Articles-page
  setting and the legacy `ngcv_articles_page_ids` filter are unchanged.
* No content, query, taxonomy, translation, URL or SEO behaviour changed;
  Polylang relationships, Rank Math ownership, the_content pipeline and
  RTL/LTR logical layout are untouched.

= 1.1.1 =
* Dark mode compatibility for the single-article view: surfaces, text,
  headings, links, table of contents, blockquotes, tables, code, article
  details, topics, previous/next navigation, related articles, comments
  and the language switcher now adapt to the theme's dark-mode toggle
  (body.digital_newspaper_dark_mode). Light-mode appearance is unchanged;
  all rules are scoped to the single-article context.
* Dark mode compatibility for the articles archive: cards, featured panel,
  category navigation, pagination and the masthead adapt to dark mode.
  The navy masthead is kept, softened to the navy-soft derivative, with
  the gold rule preserved as brand identity.
* No content, query, taxonomy, translation, URL or SEO behaviour changed;
  Polylang relationships, Rank Math ownership, the_content pipeline and
  RTL/LTR logical layout are untouched.

= 1.1.0 =
* Archive: editorial redesign — navy masthead with the existing page title,
  existing description as a deck, live article count and language switcher;
  precise-line category navigation; **featured story** from the page's existing
  query; varied discovery grid (wide lead panel + portrait-format cards);
  restyled native pagination.
* Single article: category chip + language-counterpart link in the header,
  documentary image plate with caption, optional sticky table of contents on
  wide screens (container query, graceful fallback), article-details panel with
  real language/length data, restyled topics, previous/next, related articles
  and language switcher. Card titles in related sections now use h3.
* Brand typography: Inter (English) and Cairo (Arabic), self-hosted through the
  theme's webfont loader when available, with filters to disable or replace.
* New image size `ngcv-card-tall` (portrait 4:5) and accurate `sizes`
  attributes on card/hero/feature images (fewer bytes, no layout shift).
* Bilingual: logical-property layout throughout, RTL glyph/flip refinements,
  uppercase and letter-spacing disabled for Arabic, and Arabic UI strings
  shipped in `languages/nubian-geographic-content-views-ar.mo`.
* Accessibility: removed the invalid `role="list"` from the card grid, section
  landmarks are properly labelled, orphan meta separators removed, article
  details rendered as a description list.
* No content, query, taxonomy, translation, URL or SEO behaviour changed;
  `the_content` pipeline, Polylang relationships and Rank Math ownership are
  untouched, and the plugin still outputs zero structured data.

= 1.0.0 =
* Initial release: articles archive (page template) and single-article presentation.
* Semantic, server-rendered, accessible markup; optional heading-based table of contents.
* Polylang-aware (read-only), Rank Math-safe, RTL/LTR via logical CSS.
