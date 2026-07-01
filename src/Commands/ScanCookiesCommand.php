<?php

namespace KostantinoAbate\Complihance\Commands;

use Illuminate\Console\Command;
use KostantinoAbate\Complihance\Services\CookieScanner;

class ScanCookiesCommand extends Command
{
    protected $signature = 'complihance:scan-cookies
        {url* : URLs to scan}
        {--no-consent : Do not automatically accept the Complihance banner before scanning}
        {--http-header-only : Scan only Set-Cookie headers without executing JavaScript}';

    protected $description = 'Scan URLs, store detected cookies and add missing cookies to the cookie config file.';

    public function handle(CookieScanner $scanner): int
    {
        $result = $scanner->scan(
            urls: $this->argument('url'),
            httpHeaderOnly: (bool) $this->option('http-header-only'),
            acceptConsent: ! (bool) $this->option('no-consent'),
        );

        $this->components->info("Detected {$result['detected']} unique cookie(s).");
        $this->components->info("Stored {$result['stored']} scan result(s).");
        $this->components->info("Added {$result['added_to_config']} cookie(s) to config/complihance-cookies.php.");

        return self::SUCCESS;
    }
}
