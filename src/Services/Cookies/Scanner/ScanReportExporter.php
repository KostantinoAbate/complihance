<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Facades\File;
use KostantinoAbate\Complihance\Models\CookieScan;

class ScanReportExporter
{
    public function exportJson(CookieScan $scan, string $path): string
    {
        File::ensureDirectoryExists(dirname($path));

        File::put(
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
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        return $path;
    }

    public function exportCsv(CookieScan $scan, string $path): string
    {
        File::ensureDirectoryExists(dirname($path));

        $handle = fopen($path, 'w');

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

        foreach ($scan->results as $result) {
            fputcsv($handle, [
                $result->type,
                $result->key,
                $result->name,
                $result->domain,
                $result->path,
                $result->url,
                $result->vendor,
                $result->category,
                $result->secure,
                $result->http_only,
                $result->same_site,
                $result->expires_at,
                $result->metadata
                    ? json_encode($result->metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : null,
            ]);
        }

        fclose($handle);

        return $path;
    }
}
