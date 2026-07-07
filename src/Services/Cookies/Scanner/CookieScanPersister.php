<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Str;
use KostantinoAbate\Complihance\Models\CookieScan;
use KostantinoAbate\Complihance\Models\CookieScanResult;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\Patterns\KnownCookieMatcher;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\Patterns\KnownScriptMatcher;

class CookieScanPersister
{
    public function __construct(
        protected KnownCookieMatcher $matcher,
        protected KnownScriptMatcher $scriptMatcher,
    ) {}

    public function start(array $urls, array $options = []): CookieScan
    {
        return CookieScan::query()->create([
            'uuid' => (string) Str::uuid(),
            'urls' => array_values($urls),
            'options' => $options,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function storeCookie(CookieScan $scan, array $cookie): CookieScanResult
    {
        $match = ! empty($cookie['name'])
            ? $this->matcher->match($cookie['name'])
            : null;

        $identityHash = hash('sha256', implode('|', [
            $scan->id,
            'cookie',
            $cookie['name'] ?? '',
            $cookie['domain'] ?? '',
            $cookie['path'] ?? '/',
        ]));

        return CookieScanResult::query()->updateOrCreate(
            ['identity_hash' => $identityHash],
            [
                'scan_id' => $scan->id,
                'type' => 'cookie',
                'key' => $cookie['name'] ?? null,
                'name' => $cookie['name'] ?? null,
                'domain' => $cookie['domain'] ?? null,
                'path' => $cookie['path'] ?? '/',
                'url' => $cookie['url'] ?? null,
                'vendor' => $match['vendor'] ?? null,
                'category' => $match['category'] ?? 'unclassified',
                'secure' => $cookie['secure'] ?? false,
                'http_only' => $cookie['http_only'] ?? false,
                'same_site' => $cookie['same_site'] ?? null,
                'expires_at' => $cookie['expires_at'] ?? null,
                'metadata' => $cookie['metadata'] ?? null,
            ]
        );
    }

    public function storeScript(CookieScan $scan, array $script): CookieScanResult
    {
        $src = $script['src'] ?? '';
        $match = $src !== '' ? $this->scriptMatcher->match($src) : null;

        $identityHash = hash('sha256', implode('|', [
            $scan->id,
            'script',
            $src,
            $script['url'] ?? '',
        ]));

        return CookieScanResult::query()->updateOrCreate(
            ['identity_hash' => $identityHash],
            [
                'scan_id' => $scan->id,
                'type' => 'script',
                'key' => $src,
                'value_preview' => $src,
                'url' => $script['url'] ?? null,
                'vendor' => $match['vendor'] ?? null,
                'category' => $match['category'] ?? 'unclassified',
                'secure' => false,
                'http_only' => false,
                'metadata' => [
                    'src' => $src,
                    'matched_pattern' => $match['pattern'] ?? null,
                ],
            ]
        );
    }

    public function complete(CookieScan $scan, array $summary): void
    {
        $scan->update([
            'status' => 'completed',
            'summary' => $summary,
            'finished_at' => now(),
        ]);
    }

    public function fail(CookieScan $scan, string $message): void
    {
        $scan->update([
            'status' => 'failed',
            'summary' => [
                'error' => $message,
            ],
            'finished_at' => now(),
        ]);
    }

    public function storeStorageItem(CookieScan $scan, array $item): CookieScanResult
    {
        $type = $item['type'] ?? 'local_storage';

        $identityHash = hash('sha256', implode('|', [
            $scan->id,
            $type,
            $item['key'] ?? '',
            $item['url'] ?? '',
        ]));

        return CookieScanResult::query()->updateOrCreate(
            ['identity_hash' => $identityHash],
            [
                'scan_id' => $scan->id,
                'type' => $type,
                'key' => $item['key'] ?? null,
                'value_preview' => $item['value_preview'] ?? null,
                'url' => $item['url'] ?? null,
                'secure' => false,
                'http_only' => false,
                'metadata' => $item['metadata'] ?? null,
            ]
        );
    }
}
