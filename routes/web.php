<?php

use Illuminate\Support\Facades\Route;
use WebbyCrown\SeoKitStatamic\Http\Controllers\SitemapController;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('seo-kit.sitemap');
