<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use JsonException;
use Throwable;

class Scanner
{
    public function __construct(
        protected JsonWriter $technologyWriter,
        protected BrowserScanner $browserScanner,
        protected SetCookieHeaderParser $setCookieHeaderParser,
        protected ScanPersister $persister,
    ) {}

    /**
     * Scan URLs and persist detected cookies, storage entries, and scripts.
     *
     * @param  array<int, string>  $urls
     * @return array<string, mixed>
     *
     * @throws Throwable
     * @throws FileNotFoundException
     * @throws JsonException
     */
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

            $this->technologyWriter->ensureCoreTechnologies();

            $technologyItems = [
                ...$cookies,
                ...$storageItems,
                ...$scripts,
            ];

            $addedToTechnologiesJson = $this->technologyWriter->addMissingTechnologies($technologyItems);

            $summary = [
                'scan_id' => $scan->getKey(),
                'scan_uuid' => $scan->getAttribute('uuid'),
                'stored' => $stored,
                'added_to_technologies_json' => $addedToTechnologiesJson,
                'detected' => count($detectedCookieNames) + count($storageItems) + count($scripts),
                'cookies_detected' => count($detectedCookieNames),
                'storage_detected' => count($storageItems),
                'scripts_detected' => count($scripts),
            ];

            $this->persister->complete($scan, $summary);

            return $summary;
        } catch (Throwable $e) {
            $this->persister->fail($scan, $e->getMessage());

            throw $e;
        }
    }

    /**
     * Scan only Set-Cookie headers without executing JavaScript.
     *
     * @param  array<int, string>  $urls
     * @return array<int, array<string, mixed>>
     */
    protected function scanHttpHeaders(array $urls): array
    {
        $cookies = [];

        foreach ($urls as $url) {
            $headers = @get_headers($url, true);

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
