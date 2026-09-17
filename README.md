# SEO Kit for Statamic

A free Statamic 5 add-on for starter kits: entry SEO fields, Open Graph / Twitter meta, JSON-LD, and an XML sitemap.

It is built for WebbyCrown-style kits that already use `seo_title`, `seo_description`, `seo_image`, `seo_canonical`, and `seo_robots`. Drop `{{ seo_kit:head }}` into your layout instead of hand-rolled meta tags.

**Reference kit:** [Urban Bank](https://github.com/webbycrown/urban-bank-statamic-theme) ships with `{{ seo_kit:head }}` in `partials/head.antlers.html`.

This is **not** a full SEO suite (no audit UI or content scoring). For advanced workflows see SEO Pro or Advanced SEO on the Statamic Marketplace.

## Requirements

- Statamic 5 (`statamic/cms: ^5.0`)
- PHP 8.1+
- Composer

## Installation

### From GitHub (VCS)

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/webbycrown/seo-kit-statamic"
  }
]
```

```bash
composer require webbycrown/seo-kit-statamic:^1.0
```

### Local path development

```json
"repositories": [
  {
    "type": "path",
    "url": "addons/webbycrown/seo-kit-statamic",
    "options": { "symlink": false }
  }
]
```

```bash
composer require webbycrown/seo-kit-statamic:@dev
```

### Publish (optional)

```bash
php artisan vendor:publish --tag=seo-kit-config
php artisan vendor:publish --tag=seo-kit-fieldsets
php artisan vendor:publish --tag=seo-kit-blueprints
php artisan vendor:publish --tag=seo-kit-views
php please stache:refresh
```

On boot the add-on copies the `seo` fieldset and creates a `seo_kit` global set when missing.

## Layout drop-in

In your layout `<head>` (keep charset, viewport, CSS, and scripts as they are):

```antlers
{{ seo_kit:head }}
```

That outputs:

- `<title>`, description, keywords, robots, canonical
- Open Graph + Twitter Card tags
- JSON-LD (`WebSite`, `Organization`, plus `WebPage` or `BlogPosting`)

JSON-LD only:

```antlers
{{ seo_kit:json_ld }}
```

## Entry fields

Import the fieldset on collection blueprints:

```yaml
-
  handle: seo_section
  field:
    type: section
    display: SEO
-
  import: seo
```

Handles: `seo_title`, `seo_description`, `seo_keywords`, `seo_image`, `seo_canonical`, `seo_robots`.

## Globals

CP → Globals → **SEO Kit** (`seo_kit`):

- Site name, default description, default social image, Twitter handle
- Organization name / URL / logo (JSON-LD)

## Sitemap

`GET /sitemap.xml`

Includes configured collections (default `pages`, `blog`). Entries with `noindex` in `seo_robots` are skipped.

```php
// config/seo-kit.php
'sitemap' => [
    'enabled' => true,
    'collections' => ['pages', 'blog'],
    'cache_ttl' => 3600,
],
```

Set `collections` to `[]` to include every published collection that has a route.

## Configuration

| Key | Purpose |
| --- | --- |
| `default_robots` | Fallback robots string |
| `sitemap.*` | Enable, collections, cache TTL |
| `json_ld.enabled` | Toggle JSON-LD in `seo_kit:head` |
| `json_ld.article_collections` | Collections that emit `BlogPosting` (default `blog`, `blogs`, `articles`, `posts`) |

## Support

- GitHub Issues: https://github.com/webbycrown/seo-kit-statamic/issues
- Changelog: [CHANGELOG.md](CHANGELOG.md)
- Third-party notes: [THIRD_PARTY.md](THIRD_PARTY.md)

Community support via GitHub Issues unless you have a separate WebbyCrown agreement.

---

Made by [WebbyCrown Solutions](https://www.webbycrown.com/)
