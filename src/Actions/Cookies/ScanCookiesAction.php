<?php

namespace KostantinoAbate\Complihance\Actions\Cookies;

use KostantinoAbate\Complihance\Services\Cookies\Scanner\CookieScanner;

class ScanCookiesAction
{
    public function __construct(
        protected CookieScanner $scanner,
    ) {}

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
