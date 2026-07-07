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

    /**
     * Create a new cookie scan record and mark it as running.
     *
     * @param array<int, string> $urls
     * @param array<string, mixed> $options
     */
    public function start(array $urls, array $options = []): CookieScan
    {
        return CookieScan::query()->forceCreate([
            'uuid' => (string) Str::uuid(),
            'urls' => array_values($urls),
            'options' => $options,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    /**
     * Store or update a detected HTTP cookie for the given scan.
     *
     * @param array<string, mixed> $cookie
     */
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

        $result = CookieScanResult::query()->firstOrNew([
            'identity_hash' => $identityHash,
        ]);

        return $result->forceFill([
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
        ]);
    }

    /**
     * Store or update a detected browser storage item for the given scan.
     *
     * @param array<string, mixed> $item
     */
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

        $result = CookieScanResult::query()->firstOrNew([
            'identity_hash' => $identityHash,
        ]);
        return $result->forceFill([
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
        ]);
    }

    /**
     * Store or update a detected script for the given scan.
     *
     * @param array<string, mixed> $script
     */
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

        $result = CookieScanResult::query()->firstOrNew([
            'identity_hash' => $identityHash
        ]);
        return $result->forceFill([
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
                ...($script['metadata'] ?? []),
                'src' => $src,
                'matched_key' => $match['matched_key'] ?? null,
                'matched_pattern' => $match['matched_pattern'] ?? null,
            ],
        ]);
    }

    /**
     * Mark the scan as completed and persist its summary.
     *
     * @param array<string, mixed> $summary
     */
    public function complete(CookieScan $scan, array $summary): void
    {
        $scan->forceFill([
            'status' => 'completed',
            'summary' => $summary,
            'finished_at' => now(),
        ])->save();
    }

    /**
     * Mark the scan as failed and persist the failure message.
     */
    public function fail(CookieScan $scan, string $message): void
    {
        $scan->forceFill([
            'status' => 'failed',
            'summary' => [
                'error' => $message,
            ],
            'finished_at' => now(),
        ])->save();
    }
}
