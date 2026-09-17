<?php

namespace WebbyCrown\SeoKitStatamic\Tags;

use Statamic\Tags\Tags;
use WebbyCrown\SeoKitStatamic\Support\SeoData;

class SeoKit extends Tags
{
    protected static $handle = 'seo_kit';

    /**
     * {{ seo_kit:head }} — title, meta, OG/Twitter, JSON-LD.
     */
    public function head(): string
    {
        $seo = SeoData::resolve($this->context->all());
        $jsonLd = config('seo-kit.json_ld.enabled', true)
            ? SeoData::jsonLdGraph($seo)
            : null;

        return view('seo-kit::head', [
            'seo' => $seo,
            'json_ld' => $jsonLd,
            'json_ld_json' => $jsonLd
                ? json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP)
                : null,
        ])->render();
    }

    /**
     * {{ seo_kit:json_ld }} — JSON-LD script only.
     */
    public function jsonLd(): string
    {
        if (! config('seo-kit.json_ld.enabled', true)) {
            return '';
        }

        $seo = SeoData::resolve($this->context->all());
        $graph = SeoData::jsonLdGraph($seo);
        $json = json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

        return '<script type="application/ld+json">'.$json.'</script>';
    }

    /**
     * Alias: {{ seo_kit:json_ld }} via snake method naming in Antlers.
     */
    public function json_ld(): string
    {
        return $this->jsonLd();
    }
}
