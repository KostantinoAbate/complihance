<?php

namespace KostantinoAbate\Complihance\Commands;

use Illuminate\Console\Command;
use InvalidArgumentException;
use KostantinoAbate\Complihance\Actions\Cookies\ScanCookiesAction;
use KostantinoAbate\Complihance\Models\CookieScan;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\ScanReportExporter;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\SitemapUrlResolver;

class ScanCookiesCommand extends Command
{
    protected $signature = 'complihance:scan-cookies
        {url* : URLs to scan}
        {--no-consent : Do not automatically accept the Complihance banner before scanning}
        {--http-header-only : Scan only Set-Cookie headers without executing JavaScript}
        {--report= : Export report (json,csv)}
        {--sitemap : Treat the provided URL as a sitemap XML}
        {--sitemap-limit= : Maximum number of sitemap URLs to scan}
        {--setup-script= : Path to a Playwright setup script for authenticated scans}
        {--output= : Output file path}';

    protected $description = 'Scan URLs, store detected technologies and add missing definitions to technologies.json.';

    public function handle(
        ScanCookiesAction $scanCookies,
        ScanReportExporter $exporter,
        SitemapUrlResolver $sitemapResolver,
    ): int {
        $urls = $this->argument('url');

        if ($this->option('sitemap')) {
            $limit = $this->option('sitemap-limit')
                ? (int) $this->option('sitemap-limit')
                : (int) config('complihance.scanner.sitemap_limit', 100);

            $resolvedUrls = [];

            foreach ($urls as $sitemapUrl) {
                $resolvedUrls = [
                    ...$resolvedUrls,
                    ...$sitemapResolver->resolve($sitemapUrl, $limit),
                ];
            }

            $urls = array_slice(array_values(array_unique($resolvedUrls)), 0, $limit);

            $this->components->info('Resolved '.count($urls).' URL(s) from sitemap.');

            if ($urls === []) {
                $this->components->error('No URLs resolved from sitemap.');

                return self::FAILURE;
            }
        }

        $result = $scanCookies->execute(
            urls: $urls,
            httpHeaderOnly: (bool) $this->option('http-header-only'),
            acceptConsent: ! (bool) $this->option('no-consent'),
            setupScript: $this->option('setup-script'),
        );

        $this->components->info("Scan UUID: {$result['scan_uuid']}");
        $this->components->info("Detected {$result['detected']} technolog(y/ies).");
        $this->components->info("Stored {$result['stored']} scan result(s).");
        $this->components->info("Added {$result['added_to_technologies_json']} technolog(y/ies) to resources/vendor/complihance/technologies.json.");

        $report = $this->option('report');

        if ($report) {
            $scan = CookieScan::findOrFail($result['scan_id']);

            $path = $this->option('output')
                ?: storage_path(
                    'app/complihance/scans/'.
                    $scan->uuid.'.'.$report
                );

            match ($report) {
                'json' => $exporter->exportJson($scan, $path),
                'csv' => $exporter->exportCsv($scan, $path),
                default => throw new InvalidArgumentException(
                    'Supported reports: json, csv'
                ),
            };

            $this->components->info("Report exported: {$path}");
        }

        return self::SUCCESS;
    }
}
