<?php

namespace KostantinoAbate\Complihance\Actions\Cookies;

use KostantinoAbate\Complihance\Services\Cookies\Scanner\Scanner;
use Throwable;

class ScanCookiesAction
{
    public function __construct(
        protected Scanner $scanner,
    ) {}

    /**
     * Scan one or more URLs for cookies and related browser storage entries.
     *
     * @param array<int, string>|string $urls
     * @return array<string, mixed>
     * @throws Throwable
     */
    public function execute(
        array|string $urls,
        bool $httpHeaderOnly = false,
        bool $acceptConsent = true,
        ?string $setupScript = null,
    ): array {
        return $this->scanner->scan(
            urls: is_array($urls) ? $urls : [$urls],
            httpHeaderOnly: $httpHeaderOnly,
            acceptConsent: $acceptConsent,
            setupScript: $setupScript,
        );
    }
}
