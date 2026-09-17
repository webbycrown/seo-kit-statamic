<?php

namespace WebbyCrown\SeoKitStatamic;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\GlobalSet;
use Statamic\Providers\AddonServiceProvider;
use Symfony\Component\Yaml\Yaml;
use WebbyCrown\SeoKitStatamic\Http\Controllers\SitemapController;
use WebbyCrown\SeoKitStatamic\Tags\SeoKit;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        'seo_kit' => SeoKit::class,
    ];

    public function register()
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/../config/seo-kit.php', 'seo-kit');
    }

    public function bootAddon()
    {
        parent::bootAddon();

        $this->publishes([
            __DIR__.'/../config/seo-kit.php' => config_path('seo-kit.php'),
        ], 'seo-kit-config');

        $this->publishes([
            __DIR__.'/../resources/fieldsets' => resource_path('fieldsets'),
        ], 'seo-kit-fieldsets');

        $this->publishes([
            __DIR__.'/../resources/blueprints' => resource_path('blueprints'),
        ], 'seo-kit-blueprints');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/seo-kit'),
        ], 'seo-kit-views');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'seo-kit');

        $this->registerRoutes();
        $this->ensureFieldset();
        $this->ensureGlobals();
    }

    protected function registerRoutes(): void
    {
        if (! config('seo-kit.sitemap.enabled', true)) {
            return;
        }

        Route::middleware('web')->group(function () {
            Route::get('/sitemap.xml', [SitemapController::class, 'index'])
                ->name('seo-kit.sitemap');
        });
    }

    protected function ensureFieldset(): void
    {
        $target = resource_path('fieldsets/seo.yaml');
        $source = __DIR__.'/../resources/fieldsets/seo.yaml';

        if (File::exists($target) || ! File::exists($source)) {
            return;
        }

        File::ensureDirectoryExists(dirname($target));
        File::copy($source, $target);
    }

    protected function ensureGlobals(): void
    {
        try {
            if (GlobalSet::findByHandle('seo_kit')) {
                return;
            }

            $blueprintSource = __DIR__.'/../resources/blueprints/globals/seo_kit.yaml';
            $blueprintTarget = resource_path('blueprints/globals/seo_kit.yaml');

            if (! File::exists($blueprintTarget) && File::exists($blueprintSource)) {
                File::ensureDirectoryExists(dirname($blueprintTarget));
                File::copy($blueprintSource, $blueprintTarget);
            }

            $globalsPath = base_path('content/globals');
            File::ensureDirectoryExists($globalsPath);

            $yamlFile = $globalsPath.'/seo_kit.yaml';
            if (! File::exists($yamlFile)) {
                File::put($yamlFile, Yaml::dump([
                    'title' => 'SEO Kit',
                    'data' => [
                        'site_name' => null,
                        'default_description' => null,
                        'organization_name' => null,
                        'organization_url' => null,
                        'twitter_handle' => null,
                    ],
                ], 4, 2));
            }

            $set = GlobalSet::make('seo_kit')->title('SEO Kit');
            $set->save();
        } catch (\Throwable $e) {
            // Host site may not be ready during package discovery.
        }
    }
}
