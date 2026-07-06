<?php

namespace KostantinoAbate\Complihance\Services;

use KostantinoAbate\Complihance\Models\CookieScanResult;

class CookieScanner
{
    public function __construct(
        protected CookieJsonWriter $cookieWriter,
        protected BrowserCookieScanner $browserScanner,
    ) {}

    public function scan(
        array $urls,
        bool $httpHeaderOnly = false,
        bool $acceptConsent = true,
    ): array {
        $cookies = $httpHeaderOnly
            ? $this->scanHttpHeaders($urls)
            : $this->browserScanner->scan($urls, $acceptConsent);

        $stored = 0;
        $detectedCookieNames = [];

        foreach ($cookies as $cookie) {
            $identityHash = hash('sha256', implode('|', [
                $cookie['name'] ?? '',
                $cookie['domain'] ?? '',
                $cookie['path'] ?? '/',
            ]));

            CookieScanResult::updateOrCreate(
                [
                    'identity_hash' => $identityHash,
                ],
                [
                    'name' => $cookie['name'],
                    'domain' => $cookie['domain'] ?? null,
                    'path' => $cookie['path'] ?? '/',
                    'url' => $cookie['url'],
                    'secure' => $cookie['secure'],
                    'http_only' => $cookie['http_only'],
                    'same_site' => $cookie['same_site'],
                    'expires_at' => $cookie['expires_at'],
                ]
            );

            $detectedCookieNames[] = $cookie['name'];
            $stored++;
        }

        $detectedCookieNames = array_unique($detectedCookieNames);

        $this->cookieWriter->ensureCoreCookies();

        $addedToJson = $this->cookieWriter->addMissingCookies($detectedCookieNames);

        return [
            'stored' => $stored,
            'added_to_json' => $addedToJson,
            'detected' => count($detectedCookieNames),
        ];
    }
}
