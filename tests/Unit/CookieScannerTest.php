<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\BrowserCookieScanner;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\CookieJsonWriter;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\KnownCookieMatcher;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\SetCookieHeaderParser;

beforeEach(function () {
    Config::set('complihance.data.cookies_path', storage_path('framework/testing/complihance/cookies.json'));

    File::delete(config('complihance.data.cookies_path'));
});

it('classifies core cookies correctly', function () {
    $matcher = app(KnownCookieMatcher::class);

    expect($matcher->match('complihance_consent')['category'])->toBe('necessary')
        ->and($matcher->match('complihance_consent')['vendor'])->toBe('Complihance')
        ->and($matcher->match('complihance_anonymous_id')['category'])->toBe('necessary')
        ->and($matcher->match('complihance_anonymous_id')['vendor'])->toBe('Complihance');
});

it('never writes complihance anonymous id as unclassified', function () {
    app(CookieJsonWriter::class)->addMissingCookies([
        'complihance_anonymous_id',
    ]);

    $cookies = json_decode(File::get(config('complihance.data.cookies_path')), true);

    expect($cookies['complihance_anonymous_id']['category'])->toBe('necessary')
        ->and($cookies['complihance_anonymous_id']['vendor'])->toBe('Complihance');
});

it('throws a clean error when browser scanner cannot run without playwright', function () {
    $scanner = app(BrowserCookieScanner::class);

    expect(fn () => $scanner->scan(['https://example.test']))
        ->toThrow(RuntimeException::class, 'Browser cookie scanning failed');
});

it('parses set cookie header with expires containing comma', function () {
    $cookie = app(SetCookieHeaderParser::class)->parse(
        'session_id=abc123; Expires=Wed, 21 Oct 2026 07:28:00 GMT; Path=/; HttpOnly; Secure; SameSite=Lax',
        'https://example.test'
    );

    expect($cookie)->not->toBeNull()
        ->and($cookie['name'])->toBe('session_id')
        ->and($cookie['path'])->toBe('/')
        ->and($cookie['url'])->toBe('https://example.test')
        ->and($cookie['secure'])->toBeTrue()
        ->and($cookie['http_only'])->toBeTrue()
        ->and($cookie['same_site'])->toBe('Lax')
        ->and($cookie['expires_at']->toISOString())->toBe('2026-10-21T07:28:00.000000Z');
});
