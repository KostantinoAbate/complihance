<?php

namespace KostantinoAbate\Complihance\Services;

use Illuminate\Support\Facades\Http;
use KostantinoAbate\Complihance\Models\CookieScanResult;

class CookieScanner
{
    public function __construct(
        protected CookieConfigWriter $configWriter,
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
            CookieScanResult::updateOrCreate(
                [
                    'name' => $cookie['name'],
                    'domain' => $cookie['domain'],
                    'path' => $cookie['path'],
                ],
                [
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

        $addedToConfig = $this->configWriter->addMissingCookies($detectedCookieNames);

        $this->configWriter->ensureCoreCookies();

        return [
            'stored' => $stored,
            'added_to_config' => $addedToConfig,
            'detected' => count($detectedCookieNames),
        ];
    }

    protected function scanHttpHeaders(array $urls): array
    {
        $cookies = [];

        foreach ($urls as $url) {
            $response = Http::withOptions([
                'allow_redirects' => true,
            ])->get($url);

            $setCookies = $response->header('Set-Cookie');

            if (! $setCookies) {
                continue;
            }

            foreach ($this->parseSetCookieHeaders((array) $setCookies) as $cookie) {
                $cookie['url'] = $url;
                $cookies[] = $cookie;
            }
        }

        return $cookies;
    }

    protected function parseSetCookieHeaders(array $headers): array
    {
        $cookies = [];

        foreach ($headers as $header) {
            foreach (explode(',', $header) as $rawCookie) {
                $parts = array_map('trim', explode(';', $rawCookie));
                $nameValue = array_shift($parts);

                if (! str_contains($nameValue, '=')) {
                    continue;
                }

                [$name] = explode('=', $nameValue, 2);

                $cookie = [
                    'name' => $name,
                    'domain' => null,
                    'path' => '/',
                    'secure' => false,
                    'http_only' => false,
                    'same_site' => null,
                    'expires_at' => null,
                ];

                foreach ($parts as $part) {
                    if (strtolower($part) === 'secure') {
                        $cookie['secure'] = true;

                        continue;
                    }

                    if (strtolower($part) === 'httponly') {
                        $cookie['http_only'] = true;

                        continue;
                    }

                    if (! str_contains($part, '=')) {
                        continue;
                    }

                    [$key, $value] = explode('=', $part, 2);

                    match (strtolower($key)) {
                        'domain' => $cookie['domain'] = $value,
                        'path' => $cookie['path'] = $value,
                        'samesite' => $cookie['same_site'] = $value,
                        'expires' => $cookie['expires_at'] = rescue(fn () => now()->parse($value), null, false),
                        default => null,
                    };
                }

                $cookies[] = $cookie;
            }
        }

        return $cookies;
    }
}
