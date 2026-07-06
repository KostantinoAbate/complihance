<?php

namespace KostantinoAbate\Complihance\Commands;

use Illuminate\Console\Command;
use KostantinoAbate\Complihance\Actions\Cookies\ScanCookiesAction;

class ScanCookiesCommand extends Command
{
    protected $signature = 'complihance:scan-cookies
        {url* : URLs to scan}
        {--no-consent : Do not automatically accept the Complihance banner before scanning}
        {--http-header-only : Scan only Set-Cookie headers without executing JavaScript}';

    protected $description = 'Scan URLs, store detected cookies and add missing cookies to the published cookies JSON file.';

    public function handle(ScanCookiesAction $scanCookies): int
    {
        $result = $scanCookies->execute(
            urls: $this->argument('url'),
            httpHeaderOnly: (bool) $this->option('http-header-only'),
            acceptConsent: ! (bool) $this->option('no-consent'),
        );

        $this->components->info("Detected {$result['detected']} unique cookie(s).");
        $this->components->info("Stored {$result['stored']} scan result(s).");
        $this->components->info("Added {$result['added_to_json']} cookie(s) to resources/vendor/complihance/cookies.json.");

        return self::SUCCESS;
    }
}
