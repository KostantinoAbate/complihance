<?php

use Illuminate\Support\Facades\Http;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\SitemapUrlResolver;

it('resolves urls from sitemap xml', function () {
    Http::fake([
        'https://example.com/sitemap.xml' => Http::response(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://example.com/one</loc>
    </url>
    <url>
        <loc>https://example.com/two</loc>
    </url>
</urlset>
XML),
    ]);

    $urls = app(SitemapUrlResolver::class)->resolve('https://example.com/sitemap.xml');

    expect($urls)->toBe([
        'https://example.com/one',
        'https://example.com/two',
    ]);
});

it('resolves urls from sitemap index', function () {
    Http::fake([
        'https://example.com/sitemap.xml' => Http::response(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>https://example.com/pages.xml</loc>
    </sitemap>
</sitemapindex>
XML),
        'https://example.com/pages.xml' => Http::response(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://example.com/a</loc>
    </url>
    <url>
        <loc>https://example.com/b</loc>
    </url>
</urlset>
XML),
    ]);

    $urls = app(SitemapUrlResolver::class)->resolve('https://example.com/sitemap.xml');

    expect($urls)->toBe([
        'https://example.com/a',
        'https://example.com/b',
    ]);
});

it('applies sitemap url limit', function () {
    Http::fake([
        'https://example.com/sitemap.xml' => Http::response(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>https://example.com/one</loc></url>
    <url><loc>https://example.com/two</loc></url>
    <url><loc>https://example.com/three</loc></url>
</urlset>
XML),
    ]);

    $urls = app(SitemapUrlResolver::class)->resolve('https://example.com/sitemap.xml', 2);

    expect($urls)->toBe([
        'https://example.com/one',
        'https://example.com/two',
    ]);
});
