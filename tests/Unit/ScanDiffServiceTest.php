<?php

use Illuminate\Support\Str;
use KostantinoAbate\Complihance\Models\CookieScan;
use KostantinoAbate\Complihance\Models\CookieScanResult;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\ScanDiffService;

function createScanWithResults(array $results): CookieScan
{
    $scan = CookieScan::query()->create([
        'uuid' => (string) Str::uuid(),
        'urls' => ['https://example.com'],
        'options' => [],
        'status' => 'completed',
        'summary' => [],
        'started_at' => now(),
        'finished_at' => now(),
    ]);

    foreach ($results as $result) {
        CookieScanResult::query()->create([
            'scan_id' => $scan->id,
            'identity_hash' => hash('sha256', $scan->id.'|'.$result['type'].'|'.$result['key']),
            'type' => $result['type'],
            'key' => $result['key'],
            'name' => $result['name'] ?? null,
            'domain' => $result['domain'] ?? null,
            'path' => $result['path'] ?? null,
            'url' => $result['url'] ?? 'https://example.com',
            'vendor' => $result['vendor'] ?? null,
            'category' => $result['category'] ?? null,
            'secure' => $result['secure'] ?? false,
            'http_only' => $result['http_only'] ?? false,
            'same_site' => $result['same_site'] ?? null,
            'expires_at' => $result['expires_at'] ?? null,
            'metadata' => $result['metadata'] ?? null,
        ]);
    }

    return $scan;
}

it('detects added and removed scan results', function () {
    $from = createScanWithResults([
        [
            'type' => 'cookie',
            'key' => '_ga',
            'domain' => 'example.com',
            'path' => '/',
            'vendor' => 'Google Analytics',
            'category' => 'analytics',
        ],
    ]);

    $to = createScanWithResults([
        [
            'type' => 'cookie',
            'key' => '_fbp',
            'domain' => 'example.com',
            'path' => '/',
            'vendor' => 'Meta',
            'category' => 'marketing',
        ],
    ]);

    $diff = app(ScanDiffService::class)->diff($from, $to);

    expect($diff['summary']['added'])->toBe(1)
        ->and($diff['summary']['removed'])->toBe(1)
        ->and($diff['summary']['changed'])->toBe(0)
        ->and($diff['added'][0]['key'])->toBe('_fbp')
        ->and($diff['removed'][0]['key'])->toBe('_ga');
});

it('detects vendor and category changes', function () {
    $from = createScanWithResults([
        [
            'type' => 'script',
            'key' => 'https://example.com/tracker.js',
            'vendor' => null,
            'category' => 'unclassified',
        ],
    ]);

    $to = createScanWithResults([
        [
            'type' => 'script',
            'key' => 'https://example.com/tracker.js',
            'vendor' => 'Example Tracker',
            'category' => 'marketing',
        ],
    ]);

    $diff = app(ScanDiffService::class)->diff($from, $to);

    expect($diff['summary']['added'])->toBe(0)
        ->and($diff['summary']['removed'])->toBe(0)
        ->and($diff['summary']['changed'])->toBe(1)
        ->and($diff['changed'][0]['changes'])->toHaveKeys(['vendor', 'category']);
});

it('ignores volatile expires at by default', function () {
    $from = createScanWithResults([
        [
            'type' => 'cookie',
            'key' => '_ga',
            'domain' => 'example.com',
            'path' => '/',
            'expires_at' => '2026-07-07 10:00:00',
        ],
    ]);

    $to = createScanWithResults([
        [
            'type' => 'cookie',
            'key' => '_ga',
            'domain' => 'example.com',
            'path' => '/',
            'expires_at' => '2026-07-08 10:00:00',
        ],
    ]);

    $diff = app(ScanDiffService::class)->diff($from, $to);

    expect($diff['summary']['changed'])->toBe(0)
        ->and($diff['summary']['unchanged'])->toBe(1);
});

it('includes volatile expires at when requested', function () {
    $from = createScanWithResults([
        [
            'type' => 'cookie',
            'key' => '_ga',
            'domain' => 'example.com',
            'path' => '/',
            'expires_at' => '2026-07-07 10:00:00',
        ],
    ]);

    $to = createScanWithResults([
        [
            'type' => 'cookie',
            'key' => '_ga',
            'domain' => 'example.com',
            'path' => '/',
            'expires_at' => '2026-07-08 10:00:00',
        ],
    ]);

    $diff = app(ScanDiffService::class)->diff($from, $to, includeVolatile: true);

    expect($diff['summary']['changed'])->toBe(1)
        ->and($diff['changed'][0]['changes'])->toHaveKey('expires_at');
});
