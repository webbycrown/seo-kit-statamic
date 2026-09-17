<?php

namespace WebbyCrown\SeoKitStatamic\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Collection;
use Statamic\Facades\Site;
use WebbyCrown\SeoKitStatamic\Support\SeoData;

class SitemapController
{
    public function index(): Response
    {
        $ttl = (int) config('seo-kit.sitemap.cache_ttl', 3600);
        $site = Site::current()->handle();

        $xml = Cache::remember("seo-kit.sitemap.{$site}", $ttl, function () {
            return $this->buildXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    protected function buildXml(): string
    {
        $urls = [];

        foreach ($this->collectionHandles() as $handle) {
            $collection = Collection::findByHandle($handle);
            if (! $collection) {
                continue;
            }

            $collection->queryEntries()
                ->whereStatus('published')
                ->get()
                ->each(function ($entry) use (&$urls) {
                    $robots = SeoData::string($entry->get('seo_robots'))
                        ?? config('seo-kit.default_robots', 'index, follow');

                    if (str_contains(strtolower((string) $robots), 'noindex')) {
                        return;
                    }

                    $loc = $entry->absoluteUrl();
                    if (! $loc) {
                        return;
                    }

                    $lastmod = optional($entry->lastModified())->toAtomString()
                        ?? optional($entry->date())->toAtomString();

                    $urls[] = [
                        'loc' => htmlspecialchars($loc, ENT_XML1 | ENT_COMPAT, 'UTF-8'),
                        'lastmod' => $lastmod
                            ? htmlspecialchars($lastmod, ENT_XML1 | ENT_COMPAT, 'UTF-8')
                            : null,
                    ];
                });
        }

        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '  <url>';
            $lines[] = '    <loc>'.$url['loc'].'</loc>';
            if (! empty($url['lastmod'])) {
                $lines[] = '    <lastmod>'.$url['lastmod'].'</lastmod>';
            }
            $lines[] = '  </url>';
        }

        $lines[] = '</urlset>';

        return implode("\n", $lines)."\n";
    }

    /**
     * @return list<string>
     */
    protected function collectionHandles(): array
    {
        $configured = config('seo-kit.sitemap.collections', []);

        if (is_array($configured) && count($configured) > 0) {
            return array_values(array_filter($configured));
        }

        return Collection::all()
            ->filter(function ($collection) {
                try {
                    $route = $collection->route(Site::current()->handle());

                    return filled($route);
                } catch (\Throwable $e) {
                    return $collection->routes()->filter()->isNotEmpty();
                }
            })
            ->map->handle()
            ->values()
            ->all();
    }
}
