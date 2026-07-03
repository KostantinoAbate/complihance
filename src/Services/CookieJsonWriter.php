<?php

namespace KostantinoAbate\Complihance\Services;

use Illuminate\Support\Facades\File;

class CookieJsonWriter
{
    public function __construct(
        protected KnownCookieMatcher $matcher,
        protected ComplihanceDataRepository $repository,
    ) {}

    public function addMissingCookies(array $cookieNames): int
    {
        $path = $this->repository->cookiesPath();

        $this->ensureFileExists($path);

        $cookies = $this->repository->rawCookies();

        $added = 0;

        foreach ($cookieNames as $cookieName) {
            if (array_key_exists($cookieName, $cookies)) {
                continue;
            }

            $cookies[$cookieName] = $this->matcher->match($cookieName)
                ?? $this->fallbackCookieDefinition($cookieName);

            $added++;
        }

        if ($added === 0) {
            return 0;
        }

        ksort($cookies);

        File::put($path, $this->encode($cookies));

        return $added;
    }

    public function ensureCoreCookies(): int
    {
        return $this->addMissingCookieDefinitions([
            'complihance_consent' => [
                'category' => 'necessary',
                'vendor' => 'Complihance',
                'duration' => '12 months',
                'pattern' => '^complihance_consent$',
                'translations' => [
                    'en' => [
                        'name' => 'Consent cookie',
                        'description' => 'Stores the user cookie consent preferences.',
                    ],
                    'it' => [
                        'name' => 'Cookie di consenso',
                        'description' => 'Memorizza le preferenze di consenso cookie dell’utente.',
                    ],
                ],
            ],

            'complihance_anonymous_id' => [
                'category' => 'necessary',
                'vendor' => 'Complihance',
                'duration' => '12 months',
                'pattern' => '^complihance_anonymous_id$',
                'translations' => [
                    'en' => [
                        'name' => 'Anonymous visitor identifier',
                        'description' => 'Stores an anonymous identifier used to associate consent records with anonymous visitors.',
                    ],
                    'it' => [
                        'name' => 'Identificativo anonimo visitatore',
                        'description' => 'Memorizza un identificativo anonimo usato per associare i consensi ai visitatori anonimi.',
                    ],
                ],
            ],
        ]);
    }

    public function addMissingCookieDefinitions(array $definitions): int
    {
        $path = $this->repository->cookiesPath();

        $this->ensureFileExists($path);

        $cookies = $this->repository->rawCookies();

        $added = 0;

        foreach ($definitions as $cookieName => $definition) {
            if (array_key_exists($cookieName, $cookies)) {
                continue;
            }

            $cookies[$cookieName] = $definition;
            $added++;
        }

        if ($added === 0) {
            return 0;
        }

        ksort($cookies);

        File::put($path, $this->encode($cookies));

        return $added;
    }

    protected function ensureFileExists(string $path): void
    {
        File::ensureDirectoryExists(dirname($path));

        if (! File::exists($path)) {
            File::put($path, $this->encode([]));
        }
    }

    protected function fallbackCookieDefinition(string $cookieName): array
    {
        return [
            'category' => 'unclassified',
            'vendor' => null,
            'duration' => 'Session',
            'pattern' => '^'.preg_quote($cookieName, '/').'$',
            'translations' => [
                'en' => [
                    'name' => $cookieName,
                    'description' => null,
                ],
                'it' => [
                    'name' => $cookieName,
                    'description' => null,
                ],
            ],
        ];
    }

    protected function encode(array $data): string
    {
        return json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ).PHP_EOL;
    }
}
