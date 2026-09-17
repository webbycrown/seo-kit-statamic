# Marketplace listing draft — SEO Kit

Use this copy when creating the product on https://statamic.com/dashboard (Addons).

## Product name

SEO Kit

## Short description

Meta tags, Open Graph, Twitter Card, JSON-LD, and XML sitemap for Statamic 5 starter kits.

## Long description

SEO Kit is a free companion add-on for Statamic starter sites. It ships the same `seo_*` field handles used by WebbyCrown kits, plus:

- `{{ seo_kit:head }}` for title, description, robots, canonical, Open Graph, and Twitter tags
- JSON-LD (`WebSite`, `Organization`, `WebPage` / `BlogPosting`)
- `/sitemap.xml` with noindex-aware entries and configurable collections

It is intentionally small — not an SEO audit suite. Pair it with SEO Pro or Advanced SEO if you need scoring, multi-site SEO graphs, or content analysis.

**Requires:** Statamic 5  
**Install:** `composer require webbycrown/seo-kit-statamic` (Packagist) or GitHub VCS — see README.

## Category

SEO

## Price

Free

## Screenshots to capture

1. CP → page blueprint SEO tab (`seo_title`, description, image, robots)
2. CP → Globals → SEO Kit (site defaults + organization)
3. Browser view-source of a page showing `og:title` and `application/ld+json`
4. Browser open of `/sitemap.xml`

## Composer package

`webbycrown/seo-kit-statamic`

## GitHub

https://github.com/webbycrown/seo-kit-statamic

## Compatibility

Statamic 5.x
