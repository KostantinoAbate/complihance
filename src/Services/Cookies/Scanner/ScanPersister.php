<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Str;
use KostantinoAbate\Complihance\Models\CookieScan;
use KostantinoAbate\Complihance\Models\CookieScanResult;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\Patterns\TechnologyMatcher;

class ScanPersister
{
    public function __construct(
        protected TechnologyMatcher $matcher,
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
        $name = $cookie['name'] ?? '';
        $match = $name !== ''
            ? $this->matcher->match('cookie', $name)
            : null;

        $identityHash = hash('sha256', implode('|', [
            $scan->id,
            'cookie',
            $name,
            $cookie['domain'] ?? '',
            $cookie['path'] ?? '/',
        ]));

        return CookieScanResult::query()->updateOrCreate(
            ['identity_hash' => $identityHash],
            [
                'scan_id' => $scan->id,
                'type' => 'cookie',
                'key' => $name ?: null,
                'name' => $name ?: null,
                'domain' => $cookie['domain'] ?? null,
                'path' => $cookie['path'] ?? '/',
                'url' => $cookie['url'] ?? null,
                'vendor' => $match['vendor'] ?? null,
                'category' => $match['category'] ?? 'unclassified',
                'secure' => $cookie['secure'] ?? false,
                'http_only' => $cookie['http_only'] ?? false,
                'same_site' => $cookie['same_site'] ?? null,
                'expires_at' => $cookie['expires_at'] ?? null,
                'metadata' => [
                    ...($cookie['metadata'] ?? []),
                    'matched_key' => $match['matched_key'] ?? null,
                    'matched_pattern' => $match['matched_pattern'] ?? null,
                ],
            ]
        );
    }

    public function storeStorageItem(CookieScan $scan, array $item): CookieScanResult
    {
        $type = $item['type'] ?? 'local_storage';
        $key = $item['key'] ?? '';

        $match = $key !== ''
            ? $this->matcher->match($type, $key)
            : null;

        $identityHash = hash('sha256', implode('|', [
            $scan->id,
            $type,
            $key,
            $item['url'] ?? '',
        ]));

        return CookieScanResult::query()->updateOrCreate(
            ['identity_hash' => $identityHash],
            [
                'scan_id' => $scan->id,
                'type' => $type,
                'key' => $key ?: null,
                'value_preview' => $item['value_preview'] ?? null,
                'url' => $item['url'] ?? null,
                'vendor' => $match['vendor'] ?? null,
                'category' => $match['category'] ?? 'unclassified',
                'secure' => false,
                'http_only' => false,
                'metadata' => [
                    ...($item['metadata'] ?? []),
                    'matched_key' => $match['matched_key'] ?? null,
                    'matched_pattern' => $match['matched_pattern'] ?? null,
                ],
            ]
        );
    }

    public function storeScript(CookieScan $scan, array $script): CookieScanResult
    {
        $src = $script['src'] ?? '';

        $match = $src !== ''
            ? $this->matcher->match('script', $src)
            : null;

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
                'key' => $src ?: null,
                'value_preview' => $src ?: null,
                'url' => $script['url'] ?? null,
                'vendor' => $match['vendor'] ?? null,
                'category' => $match['category'] ?? 'unclassified',
                'secure' => false,
                'http_only' => false,
                'metadata' => [
                    'src' => $src,
                    'matched_key' => $match['matched_key'] ?? null,
                    'matched_pattern' => $match['matched_pattern'] ?? null,
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
}
