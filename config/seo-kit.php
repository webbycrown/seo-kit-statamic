<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default robots
    |--------------------------------------------------------------------------
    */
    'default_robots' => env('SEO_KIT_DEFAULT_ROBOTS', 'index, follow'),

    /*
    |--------------------------------------------------------------------------
    | Sitemap
    |--------------------------------------------------------------------------
    |
    | Empty collections list = include every published collection that has a route.
    | Otherwise only the listed handles are included.
    |
    */
    'sitemap' => [
        'enabled' => env('SEO_KIT_SITEMAP_ENABLED', true),
        'collections' => [
            'pages',
            'blog',
        ],
        'cache_ttl' => (int) env('SEO_KIT_SITEMAP_CACHE', 3600),
    ],

    /*
    |--------------------------------------------------------------------------
    | JSON-LD
    |--------------------------------------------------------------------------
    */
    'json_ld' => [
        'enabled' => env('SEO_KIT_JSON_LD_ENABLED', true),
        'article_collections' => [
            'blog',
            'blogs',
            'articles',
            'posts',
        ],
    ],

];
