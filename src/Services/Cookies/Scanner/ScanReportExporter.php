<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Database\Eloquent\Collection;
use JsonException;
use KostantinoAbate\Complihance\Models\CookieScan;
use KostantinoAbate\Complihance\Models\CookieScanResult;
use RuntimeException;

class ScanReportExporter
{
    /**
     * Export the given scan and its results to a JSON file.
     *
     * @throws JsonException
     */
    public function exportJson(CookieScan $scan, string $path): string
    {
        $this->ensureDirectoryExists($path);

        file_put_contents(
            $path,
            json_encode([
                'scan' => [
                    'id' => $scan->id,
                    'uuid' => $scan->uuid,
                    'status' => $scan->status,
                    'urls' => $scan->urls,
                    'options' => $scan->options,
                    'summary' => $scan->summary,
                    'started_at' => $scan->started_at?->toISOString(),
                    'finished_at' => $scan->finished_at?->toISOString(),
                ],
                'results' => $scan->results()->get()->toArray(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        );

        return $path;
    }

    /**
     * Export the given scan results to a CSV file.
     *
     * @throws JsonException
     */
    public function exportCsv(CookieScan $scan, string $path): string
    {
        $this->ensureDirectoryExists($path);

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException("Unable to open scan report file for writing: $path");
        }

        fputcsv($handle, [
            'type',
            'key',
            'name',
            'domain',
            'path',
            'url',
            'vendor',
            'category',
            'secure',
            'http_only',
            'same_site',
            'expires_at',
            'metadata',
        ]);

        /** @var Collection<int, CookieScanResult> $results */
        $results = $scan->results()->get();

        foreach ($results as $result) {
            fputcsv($handle, [
                $result->type,
                $result->key,
                $result->name,
                $result->domain,
                $result->path,
                $result->url,
                $result->vendor,
                $result->category,
                (int) $result->secure,
                (int) $result->http_only,
                $result->same_site,
                $result->expires_at,
                $result->metadata
                    ? json_encode($result->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
                    : null,
            ]);
        }

        fclose($handle);

        return $path;
    }

    /**
     * Ensure that the target report directory exists.
     */
    protected function ensureDirectoryExists(string $path): void
    {
        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create scan report directory: $directory");
        }
    }
}
