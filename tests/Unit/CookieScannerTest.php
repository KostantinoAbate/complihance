<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use KostantinoAbate\Complihance\Models\CookieScan;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\BrowserScanner;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\JsonWriter;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\Patterns\TechnologyMatcher;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\Scanner;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\SetCookieHeaderParser;

beforeEach(function () {
    Config::set(
        'complihance.data.technologies_path',
        storage_path('framework/testing/complihance/technologies.json')
    );

    File::delete(config('complihance.data.technologies_path'));
});

it('classifies core cookies correctly', function () {
    $matcher = app(TechnologyMatcher::class);

    expect($matcher->match('cookie', 'complihance_consent')['category'])->toBe('necessary')
        ->and($matcher->match('cookie', 'complihance_consent')['vendor'])->toBe('Complihance')
        ->and($matcher->match('cookie', 'complihance_anonymous_id')['category'])->toBe('necessary')
        ->and($matcher->match('cookie', 'complihance_anonymous_id')['vendor'])->toBe('Complihance');
});

it('never writes complihance anonymous id as unclassified', function () {
    app(JsonWriter::class)->addMissingTechnologies([
        [
            'type' => 'cookie',
            'name' => 'complihance_anonymous_id',
        ],
    ]);

    $technologies = json_decode(File::get(config('complihance.data.technologies_path')), true);

    expect($technologies['complihance_anonymous_id']['category'])->toBe('necessary')
        ->and($technologies['complihance_anonymous_id']['vendor'])->toBe('Complihance')
        ->and($technologies['complihance_anonymous_id']['technology']['label'])->toBe('Cookie');
});

it('throws a clean error when browser scanner cannot run without playwright', function () {
    $scanner = app(BrowserScanner::class);

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

it('stores a scan session when scanning http headers', function () {
    Carbon::setTestNow('2026-07-06 10:00:00');

    $scanner = app(Scanner::class);

    $result = $scanner->scan(
        urls: ['https://example.com'],
        httpHeaderOnly: true,
        acceptConsent: false,
    );

    $scan = CookieScan::query()
        ->where('uuid', $result['scan_uuid'])
        ->firstOrFail();

    expect($result)
        ->toHaveKeys([
            'scan_id',
            'scan_uuid',
            'stored',
            'added_to_technologies_json',
            'detected',
        ])
        ->and(CookieScan::query()->count())->toBe(1)
        ->and($scan->uuid)->toBe($result['scan_uuid'])
        ->and($scan->urls)->toBe(['https://example.com'])
        ->and($scan->options)->toBe([
            'http_header_only' => true,
            'accept_consent' => false,
            'setup_script' => null,
        ])
        ->and($scan->status)->toBe('completed')
        ->and($scan->summary['scan_uuid'])->toBe($result['scan_uuid'])
        ->and($scan->started_at->toDateTimeString())->toBe('2026-07-06 10:00:00')
        ->and($scan->finished_at->toDateTimeString())->toBe('2026-07-06 10:00:00');

    Carbon::setTestNow();
});
