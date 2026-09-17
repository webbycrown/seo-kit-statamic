<?php

namespace WebbyCrown\SeoKitStatamic\Tests;

use PHPUnit\Framework\TestCase;
use WebbyCrown\SeoKitStatamic\Support\SeoData;

class SeoDataTest extends TestCase
{
    public function test_normalize_asset_path_prefixes_when_needed(): void
    {
        $this->assertSame('/assets/logo.png', SeoData::normalizeAssetPath('logo.png'));
        $this->assertSame('/assets/image/logo.png', SeoData::normalizeAssetPath('image/logo.png'));
        $this->assertSame('/assets/images/x.png', SeoData::normalizeAssetPath('/assets/images/x.png'));
        $this->assertSame('/assets/images/x.png', SeoData::normalizeAssetPath('assets/images/x.png'));
        $this->assertSame('https://cdn.example/a.png', SeoData::normalizeAssetPath('https://cdn.example/a.png'));
    }

    public function test_absolute_url_joins_app_url(): void
    {
        // Without Laravel app bootstrap, config() may be unavailable — skip if so.
        if (! function_exists('config')) {
            $this->markTestSkipped('Laravel config helper not available.');
        }

        config(['app.url' => 'https://example.test']);

        $this->assertSame(
            'https://example.test/assets/logo.png',
            SeoData::absoluteUrl('/assets/logo.png')
        );
        $this->assertSame(
            'https://cdn.example/x.png',
            SeoData::absoluteUrl('https://cdn.example/x.png')
        );
    }

    public function test_json_ld_graph_includes_website_organization_and_webpage(): void
    {
        $seo = [
            'title' => 'About',
            'site_name' => 'Demo Site',
            'description' => 'About us',
            'page_url' => 'https://example.test/about',
            'canonical' => 'https://example.test/about',
            'image' => 'https://example.test/assets/og.png',
            'is_article' => false,
            'organization_name' => 'Demo Org',
            'organization_url' => 'https://example.test',
            'organization_logo' => 'https://example.test/assets/logo.png',
            'date_published' => null,
            'date_modified' => null,
        ];

        $graph = SeoData::jsonLdGraph($seo);

        $this->assertSame('https://schema.org', $graph['@context']);
        $types = array_column($graph['@graph'], '@type');
        $this->assertContains('WebSite', $types);
        $this->assertContains('Organization', $types);
        $this->assertContains('WebPage', $types);
        $this->assertNotContains('BlogPosting', $types);
    }

    public function test_json_ld_graph_uses_blog_posting_for_articles(): void
    {
        $seo = [
            'title' => 'Hello Post',
            'site_name' => 'Demo Site',
            'description' => 'Post body',
            'page_url' => 'https://example.test/blog/hello',
            'canonical' => 'https://example.test/blog/hello',
            'image' => null,
            'is_article' => true,
            'organization_name' => 'Demo Org',
            'organization_url' => 'https://example.test',
            'organization_logo' => null,
            'date_published' => '2026-09-17T00:00:00+00:00',
            'date_modified' => '2026-09-17T12:00:00+00:00',
        ];

        $graph = SeoData::jsonLdGraph($seo);
        $types = array_column($graph['@graph'], '@type');

        $this->assertContains('BlogPosting', $types);
        $this->assertNotContains('WebPage', $types);
    }

    public function test_string_helper_trims_and_nulls_empty(): void
    {
        $this->assertSame('Hello', SeoData::string('  Hello  '));
        $this->assertNull(SeoData::string(''));
        $this->assertNull(SeoData::string(null));
    }
}
