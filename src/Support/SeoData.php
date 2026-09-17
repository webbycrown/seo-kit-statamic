<?php

namespace WebbyCrown\SeoKitStatamic\Support;

use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Site;
use Statamic\Fields\Value;

class SeoData
{
    public static function globals(): array
    {
        try {
            $set = GlobalSet::findByHandle('seo_kit');
            $variables = $set?->inCurrentSite() ?? $set?->inDefaultSite();

            if (! $variables) {
                return [];
            }

            return $variables->data()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function string($value): ?string
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if (is_array($value)) {
            $value = $value['value'] ?? $value[0] ?? null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return is_string($value) ? trim($value) : (string) $value;
    }

    public static function assetPath($value): ?string
    {
        if ($value instanceof Value) {
            $value = $value->value();
        }

        if (is_object($value) && method_exists($value, 'url')) {
            return $value->url();
        }

        if (is_array($value)) {
            $first = $value[0] ?? $value;
            if (is_object($first) && method_exists($first, 'url')) {
                return $first->url();
            }
            if (is_string($first)) {
                $value = $first;
            } elseif (is_array($first) && isset($first['url'])) {
                return $first['url'];
            } else {
                return null;
            }
        }

        if (! is_string($value) || $value === '') {
            return null;
        }

        return self::normalizeAssetPath($value);
    }

    public static function normalizeAssetPath(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        if (str_contains($path, 'assets')) {
            return str_starts_with($path, '/') ? $path : '/'.$path;
        }

        return '/assets/'.ltrim($path, '/');
    }

    public static function absoluteUrl(?string $pathOrUrl): ?string
    {
        if (! $pathOrUrl) {
            return null;
        }

        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return $pathOrUrl;
        }

        if (str_starts_with($pathOrUrl, '//')) {
            return (request()->isSecure() ? 'https:' : 'http:').$pathOrUrl;
        }

        $base = rtrim(config('app.url') ?: Site::current()->absoluteUrl(), '/');

        return $base.'/'.ltrim($pathOrUrl, '/');
    }

    public static function resolve(array $context = []): array
    {
        $globals = self::globals();
        $entry = $context['page'] ?? $context['entry'] ?? null;

        $title = self::string($context['seo_title'] ?? null)
            ?? self::string($context['title'] ?? null)
            ?? self::string($globals['site_name'] ?? null)
            ?? Site::current()->name();

        $siteName = self::string($globals['site_name'] ?? null)
            ?? Site::current()->name();

        $description = self::string($context['seo_description'] ?? null)
            ?? self::string($context['excerpt'] ?? null)
            ?? self::string($globals['default_description'] ?? null)
            ?? '';

        $keywords = self::string($context['seo_keywords'] ?? null);

        $robots = self::string($context['seo_robots'] ?? null)
            ?? config('seo-kit.default_robots', 'index, follow');

        $canonical = self::string($context['seo_canonical'] ?? null)
            ?? self::string($context['permalink'] ?? null)
            ?? self::string($context['current_full_url'] ?? null)
            ?? self::string($context['current_url'] ?? null)
            ?? url()->current();

        $canonical = self::absoluteUrl($canonical) ?? $canonical;

        $imagePath = self::assetPath($context['seo_image'] ?? null)
            ?? self::assetPath($globals['default_image'] ?? null)
            ?? self::assetPath($globals['organization_logo'] ?? null);

        $imageUrl = self::absoluteUrl($imagePath);

        $twitter = self::string($globals['twitter_handle'] ?? null);
        if ($twitter) {
            $twitter = ltrim($twitter, '@');
        }

        $twitterSite = $twitter ? '@'.$twitter : null;

        $collection = null;
        if ($entry instanceof Entry) {
            $collection = $entry->collectionHandle();
        } elseif (isset($context['collection'])) {
            $collection = is_object($context['collection']) && method_exists($context['collection'], 'handle')
                ? $context['collection']->handle()
                : self::string($context['collection']);
        } elseif (isset($context['collection_handle'])) {
            $collection = self::string($context['collection_handle']);
        }

        $articleCollections = config('seo-kit.json_ld.article_collections', []);
        $isArticle = $collection && in_array($collection, $articleCollections, true);

        $orgName = self::string($globals['organization_name'] ?? null) ?? $siteName;
        $orgUrl = self::absoluteUrl(self::string($globals['organization_url'] ?? null))
            ?? rtrim(config('app.url') ?: Site::current()->absoluteUrl(), '/');
        $orgLogo = self::absoluteUrl(
            self::assetPath($globals['organization_logo'] ?? null)
                ?? $imagePath
        );

        $pageUrl = $canonical;
        $updated = null;
        if ($entry instanceof Entry) {
            $updated = optional($entry->lastModified())->toAtomString();
        }

        return [
            'title' => $title,
            'site_name' => $siteName,
            'description' => $description,
            'keywords' => $keywords,
            'robots' => $robots,
            'canonical' => $canonical,
            'image' => $imageUrl,
            'twitter_handle' => $twitter,
            'twitter_site' => $twitterSite,
            'og_type' => $isArticle ? 'article' : 'website',
            'collection' => $collection,
            'is_article' => $isArticle,
            'organization_name' => $orgName,
            'organization_url' => $orgUrl,
            'organization_logo' => $orgLogo,
            'page_url' => $pageUrl,
            'date_modified' => $updated,
            'date_published' => $entry instanceof Entry
                ? optional($entry->date())->toAtomString()
                : null,
        ];
    }

    public static function jsonLdGraph(array $seo): array
    {
        $graph = [];

        $siteUrl = $seo['organization_url']
            ?? (function_exists('config') ? config('app.url') : null);

        if (! $siteUrl) {
            try {
                $siteUrl = Site::current()->absoluteUrl();
            } catch (\Throwable $e) {
                $siteUrl = $seo['page_url'] ?? '';
            }
        }

        $siteUrl = rtrim((string) $siteUrl, '/');

        $graph[] = [
            '@type' => 'WebSite',
            'name' => $seo['site_name'],
            'url' => $siteUrl,
        ];

        $org = [
            '@type' => 'Organization',
            'name' => $seo['organization_name'],
            'url' => $seo['organization_url'],
        ];

        if (! empty($seo['organization_logo'])) {
            $org['logo'] = $seo['organization_logo'];
        }

        $graph[] = $org;

        if (! empty($seo['page_url'])) {
            if ($seo['is_article']) {
                $article = [
                    '@type' => 'BlogPosting',
                    'headline' => $seo['title'],
                    'description' => $seo['description'],
                    'url' => $seo['page_url'],
                    'mainEntityOfPage' => $seo['page_url'],
                ];
                if (! empty($seo['image'])) {
                    $article['image'] = [$seo['image']];
                }
                if (! empty($seo['date_published'])) {
                    $article['datePublished'] = $seo['date_published'];
                }
                if (! empty($seo['date_modified'])) {
                    $article['dateModified'] = $seo['date_modified'];
                }
                $article['publisher'] = [
                    '@type' => 'Organization',
                    'name' => $seo['organization_name'],
                    'url' => $seo['organization_url'],
                ];
                $graph[] = $article;
            } else {
                $page = [
                    '@type' => 'WebPage',
                    'name' => $seo['title'],
                    'description' => $seo['description'],
                    'url' => $seo['page_url'],
                ];
                if (! empty($seo['image'])) {
                    $page['image'] = $seo['image'];
                }
                $graph[] = $page;
            }
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }
}
