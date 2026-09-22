# Nubian Geographic — Content Views

<div align="center">

**"Future Heritage" — التراث في المستقبل**

A presentation layer for the Nubian Geographic editorial platform:
a magazine-grade articles archive and an immersive single-article
reading experience for WordPress.

[![Version](https://img.shields.io/badge/version-1.1.1-blue)](#-installation)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-green)](#-license)
[![WordPress](https://img.shields.io/badge/WordPress-5.9%2B-21759B?logo=wordpress)](#-requirements)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-777BB4?logo=php)](#-requirements)
[![Languages](https://img.shields.io/badge/languages-EN%20%7C%20AR-success)](#-bilingual-support)
[![Dark Mode](https://img.shields.io/badge/dark--mode-supported-8A2BE2)](#-dark-mode-compatibility)
[![Accessibility](https://img.shields.io/badge/a11y-WCAG%20AA-brightgreen)](#-features)
[![Polylang](https://img.shields.io/badge/Polylang-read--only-4172CE)](#-bilingual-support)

</div>

---

> 🌍 **ملاحظة بالعربية** — هذه الإضافة تقدّم طبقة عرض فقط (أرشيف المقالات
> + المقالة المفردة) للمنصة النوبية الجغرافية، وتدعم العربية والإنجليزية
> بالكامل (RTL/LTR). هذا الملف بالإنجليزية لأنه المعيار على GitHub، لكن
> واجهات الإضافة نفسها مترجمة بالكامل وتشمل ملفات ترجمة عربية جاهزة.

---

## 📑 Table of Contents

- [✨ Features](#-features)
- [📋 Requirements](#-requirements)
- [📥 Installation](#-installation)
- [🎨 Design Philosophy](#-design-philosophy)
- [🌍 Bilingual Support](#-bilingual-support)
- [🌓 Dark Mode Compatibility](#-dark-mode-compatibility)
- [🔧 Configuration](#-configuration)
- [📂 File Structure](#-file-structure)
- [📸 Screenshots](#-screenshots)
- [💡 Usage Examples](#-usage-examples)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [👥 Credits](#-credits)
- [📞 Support](#-support)

---

## ✨ Features

- ✅ **Articles archive** — a navy masthead with the page's own title and
  description, a live article count, category navigation, a featured
  story (page 1 only), an editorially varied discovery grid, and native
  crawlable pagination.
- ✅ **Single article view** — documentary image plate with its real
  caption, comfortable reading measure, structured article details
  (language + length), topics, previous/next navigation, related
  articles, and native comments.
- ✅ **Optional sticky table of contents** — appears only when the
  article column is wide enough (CSS container query), with a JS
  scroll-spy that marks the active heading.
- ✅ **Bilingual out of the box** — English (LTR) and Arabic (RTL) with
  logical-property layout; one component set serves both directions.
- ✅ **Dark mode compatibility** — adapts to the theme's dark-mode
  toggle for both the archive and the single article.
- ✅ **SEO-safe** — outputs **zero** SEO meta or structured data;
  Rank Math (or Yoast) remains the single SEO authority. Exactly one
  breadcrumb, one `<h1>`.
- ✅ **Polylang-aware (read-only)** — reads existing language
  relationships; never writes translations or language records.
- ✅ **Accessible** — WCAG AA contrast targets, visible focus rings,
  labelled sections, screen-reader helpers, reduced-motion support.
- ✅ **Self-hosted typography** — Inter (EN) + Cairo (AR) downloaded
  once via the theme's webfont loader; no third-party request at
  page view when self-hosting is available.
- ✅ **Presentation only** — creates or modifies no content, URLs,
  taxonomies, meta, or database rows. Deactivating restores the
  theme instantly.

---

## 📋 Requirements

| Requirement | Value             | Notes                                   |
| ----------- | ----------------- | --------------------------------------- |
| WordPress   | 5.9+              | Plugin header                           |
| PHP         | 7.2+              | Plugin header                           |
| Theme       | Digital Newspaper | Tested on 1.1.18; degrades gracefully   |
| Polylang    | Optional          | Read-only adapter                       |
| Rank Math   | Optional          | The plugin outputs no SEO               |

> **Important** — the plugin never writes to the database. There are no
> settings pages, no cron jobs, no REST routes, and uninstall performs
> no destructive operation.

---

## 📥 Installation

1. Download the latest ZIP (for example
   `nubian-geographic-content-views-1.1.1.zip`).
2. In WordPress go to **Plugins → Add New → Upload Plugin**, upload the
   ZIP, then **Activate**.
   - Or copy the `nubian-geographic-content-views/` folder to
     `/wp-content/plugins/` manually.
3. In **Pages → Edit**, assign the page template **"NGCV — Articles
   Archive"** to your Articles page (English) **and** to its Polylang
   Arabic translation.
4. Done — single articles (`post` post type) automatically use the
   plugin's article view. No configuration is required.
5. Purge your page cache (for example LiteSpeed) once after deploying.

> **Note** — the archive page keeps its ID, slug, URL, and existing
> content. Only the presentation changes.

---

## 🎨 Design Philosophy

The views follow the **"Future Heritage"** art direction: archival
precision with modern readability — gold accents on deep navy, sharp
2–4 px radii, and a recurring broken-gold "rule" motif. Nothing is
invented: every title, date, category, and count shown already exists
in WordPress for the current language.

### Brand palette

| Token            | Value                | Role                        |
| ---------------- | -------------------- | --------------------------- |
| Deep Nile Navy   | `#020C30`            | Structure, masthead, text   |
| Nubian Gold      | `#FFDE00`            | Accents, rules, chips, CTAs |
| Interactive Blue | `#4172CE`            | Interactive elements only   |
| Warm surfaces    | `#FAF7F0` / `#F4EEE1` | Reading surfaces           |

### Typography

| Script  | Family | Fallbacks                          |
| ------- | ------ | ---------------------------------- |
| English | Inter  | system-ui, Segoe UI, Roboto, Arial |
| Arabic  | Cairo  | Noto Sans Arabic, Tahoma           |

Arabic pages switch the whole plugin surface to Cairo via one CSS rule
keyed on `html[lang^="ar"]` / `html[dir="rtl"]`; uppercase and
letter-spacing (Latin conventions that break Arabic joining) are
disabled for Arabic.

---

## 🌍 Bilingual Support

- **Full RTL/LTR** — all layout uses CSS logical properties
  (`inline-size`, `margin-inline-start`, …), so one component set
  mirrors automatically. Arrows flip; uppercase/tracking off in RTL.
- **Arabic UI strings** ship in
  `languages/nubian-geographic-content-views-ar.mo`.
- **Polylang integration (read-only)** —
  - language-scoped archive queries (`lang` parameter),
  - a header **language counterpart** link on articles,
  - a compact **language switcher** that only links to translations
    that actually exist (suppressed when fewer than two languages).

> **Note** — `<html lang>`/`dir` stay owned by WordPress + Polylang.
> The plugin hard-codes no language or direction.

---

## 🌓 Dark Mode Compatibility

The Digital Newspaper theme toggles dark mode via a
`body.digital_newspaper_dark_mode` class (persisted in
`localStorage.themeMode`). This plugin respects it:

- Scoped token overrides for surfaces, hairlines, text, and accents.
  Light-mode appearance is **unchanged** — every dark rule requires the
  body class.
- **Archive**: cards, featured panel, category nav, pagination, and a
  softened navy masthead (`#0A1F44`) with the gold rule preserved.
- **Single article**: prose, headings, TOC, blockquotes, tables, code,
  article details, topics, previous/next, related, comments — all
  re-tuned for WCAG AA contrast on dark surfaces.
- Components that used navy as text flip to the dark ink; the brand
  tokens (`--ngcv-navy`, `--ngcv-gold`, `--ngcv-blue`) are never
  redefined.

---

## 🔧 Configuration

The plugin ships with sensible defaults and is fully filterable.

| Filter                          | Purpose                                  |
| ------------------------------- | ---------------------------------------- |
| `ngcv_use_single_template`      | Disable the single-article view          |
| `ngcv_articles_page_ids`        | Verified IDs for the posts-page fallback |
| `ngcv_archive_query_args`       | Modify the archive's secondary query     |
| `ngcv_show_featured_story`      | Turn the featured story off              |
| `ngcv_show_category_nav`        | Turn the category navigation off         |
| `ngcv_counterpart_links`        | Filter language-counterpart links        |
| `ngcv_webfonts_enabled`         | Disable the brand webfonts               |
| `ngcv_webfont_families`         | Replace the Google Fonts family list     |
| `ngcv_webfont_url`              | Provide a custom stylesheet URL          |
| `ngcv_inherit_theme_typography` | Hand typography back to the theme        |
| `ngcv_template_path`            | Override template resolution             |

```php
// Example: disable the featured story on the archive.
add_filter( 'ngcv_show_featured_story', '__return_false' );

// Example: enable the posts-page fallback for a verified page ID.
add_filter(
	'ngcv_articles_page_ids',
	function ( $ids ) {
		$ids[] = 42; // The real "Articles" page ID.
		return $ids;
	}
);
```

---

## 📂 File Structure

```text
nubian-geographic-content-views/
├── nubian-geographic-content-views.php  # Bootstrap + loader
├── uninstall.php                        # No-op uninstall
├── readme.txt                           # WordPress.org readme
├── includes/
│   ├── class-plugin.php                 # Context, body classes, shell
│   ├── class-template-router.php        # Template routing
│   ├── class-archive.php                # Archive data layer
│   ├── class-single.php                 # Single-article data layer
│   ├── class-assets.php                 # CSS/JS, image sizes, fonts
│   └── class-polylang.php               # Read-only Polylang adapter
├── templates/
│   ├── archive-articles.php             # Archive view
│   ├── single-article.php               # Single-article view
│   └── parts/                           # Card, featured, TOC, …
├── assets/
│   ├── css/  (tokens, archive, single, responsive)
│   └── js/   (content-views.js — TOC + scroll-spy)
└── languages/                           # pot + ar .po/.mo
```

---

## 📸 Screenshots

> **Note** — screenshots will be added here as the visual documentation
> matures. Planned captures:

| # | View                                    | Status      |
| - | --------------------------------------- | ----------- |
| 1 | Archive — masthead + discovery grid     | Placeholder |
| 2 | Archive — featured story + category nav | Placeholder |
| 3 | Single article — reading layout + TOC   | Placeholder |
| 4 | Single article — dark mode              | Placeholder |
| 5 | Arabic (RTL) archive + article          | Placeholder |

---

## 💡 Usage Examples

### Assign the archive presentation

**Pages → Edit → Page Attributes → Template → "NGCV — Articles
Archive"** on both language versions of the Articles page.

### Override a template from a child theme

Copy any plugin template into your child theme and edit freely:

```text
your-child-theme/
└── ngcv-templates/
    ├── archive-articles.php
    ├── single-article.php
    └── parts/article-card.php
```

### Responsive image sizes

```text
ngcv-card       768 × 480   landscape card (8:5)
ngcv-card-tall  720 × 900   portrait card  (4:5)
ngcv-hero      1600 × 900   documentary plate (16:9)
```

Regenerate thumbnails once after deployment so older media gains the
new crops (the browser falls back gracefully until then).

---

## 🤝 Contributing

Contributions are welcome.

1. Open an issue describing the change first.
2. Keep the project's guardrails in mind: presentation only, no
   content/query/SEO changes, WordPress APIs, escaped output.
3. Preserve bilingual behaviour (test both EN and AR).

---

## 📄 License

Released under the
[GPL-2.0-or-later](https://www.gnu.org/licenses/gpl-2.0.html)
license — the same license as WordPress itself.

---

## 👥 Credits

- **Nubian Geographic** — project owner and editorial direction.
- **Digital Newspaper** theme by BlazeThemes — the host theme whose
  shell and dark-mode system this plugin integrates with.
- [Polylang](https://wordpress.org/plugins/polylang/) — translation
  relationships (consumed read-only).
- [Rank Math](https://wordpress.org/plugins/seo-by-rank-math/) — SEO
  ownership (the plugin outputs none).
- [Inter](https://rsms.me/inter/) by Rasmus Andersson and
  [Cairo](https://fonts.google.com/specimen/Cairo) by Mohamed Gaber —
  the brand typefaces.

---

## 📞 Support

- 🐛 **Bug reports**: open a
  [GitHub issue](https://github.com/MajekAhmed/nubian-geographic-content-views/issues)
  with steps to reproduce, WordPress/PHP versions, and theme version.
- 💡 **Feature ideas**: the plugin is deliberately presentation-only;
  proposals that respect that boundary are preferred.
- 🌐 **Arabic support**: available — write your issue in Arabic or
  English.

---

## 🔖 Topics

`wordpress-plugin` · `nubian` · `cultural-heritage` · `documentation` ·
`bilingual` · `rtl` · `multilingual` · `dark-mode` · `accessibility` ·
`editorial` · `magazine` · `polylang` · `rank-math`


