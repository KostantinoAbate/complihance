<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

class CookieScanner
{
    public function __construct(
        protected CookieJsonWriter $cookieWriter,
        protected BrowserCookieScanner $browserScanner,
        protected SetCookieHeaderParser $setCookieHeaderParser,
        protected CookieScanPersister $persister,
    ) {}

    public function scan(
        array $urls,
        bool $httpHeaderOnly = false,
        bool $acceptConsent = true,
        ?string $setupScript = null,
    ): array {
        $scan = $this->persister->start($urls, [
            'http_header_only' => $httpHeaderOnly,
            'accept_consent' => $acceptConsent,
            'setup_script' => $setupScript,
        ]);

        try {
            $scanResult = $httpHeaderOnly
                ? [
                    'cookies' => $this->scanHttpHeaders($urls),
                    'storage' => [],
                    'scripts' => [],
                ]
                : $this->browserScanner->scan($urls, $acceptConsent, $setupScript);

            $cookies = $scanResult['cookies'] ?? [];
            $storageItems = $scanResult['storage'] ?? [];
            $scripts = $scanResult['scripts'] ?? [];

            $stored = 0;
            $detectedCookieNames = [];

            foreach ($cookies as $cookie) {
                $this->persister->storeCookie($scan, $cookie);

                if (! empty($cookie['name'])) {
                    $detectedCookieNames[] = $cookie['name'];
                }

                $stored++;
            }

            foreach ($storageItems as $storageItem) {
                $this->persister->storeStorageItem($scan, $storageItem);
                $stored++;
            }

            foreach ($scripts as $script) {
                $this->persister->storeScript($scan, $script);
                $stored++;
            }

            $detectedCookieNames = array_values(array_unique($detectedCookieNames));

            $this->cookieWriter->ensureCoreCookies();

            $addedToJson = $this->cookieWriter->addMissingCookies($detectedCookieNames);

            $summary = [
                'scan_id' => $scan->id,
                'scan_uuid' => $scan->uuid,
                'stored' => $stored,
                'added_to_json' => $addedToJson,
                'detected' => count($detectedCookieNames) + count($storageItems) + count($scripts),
                'cookies_detected' => count($detectedCookieNames),
                'storage_detected' => count($storageItems),
                'scripts_detected' => count($scripts),
            ];

            $this->persister->complete($scan, $summary);

            return $summary;
        } catch (\Throwable $e) {
            $this->persister->fail($scan, $e->getMessage());

            throw $e;
        }
    }

    protected function scanHttpHeaders(array $urls): array
    {
        $cookies = [];

        foreach ($urls as $url) {
            $headers = get_headers($url, true);

            if (! $headers || ! isset($headers['Set-Cookie'])) {
                continue;
            }

            $setCookies = is_array($headers['Set-Cookie'])
                ? $headers['Set-Cookie']
                : [$headers['Set-Cookie']];

            foreach ($setCookies as $setCookie) {
                $cookie = $this->setCookieHeaderParser->parse($setCookie, $url);

                if ($cookie !== null) {
                    $cookies[] = $cookie;
                }
            }
        }

        return $cookies;
    }
}
