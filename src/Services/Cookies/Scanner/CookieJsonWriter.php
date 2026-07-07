<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Facades\File;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\Patterns\KnownCookieMatcher;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;

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

        $rawCookies = $this->repository->rawCookies();
        $cookies = $this->normalizeDefinitions($rawCookies);

        $added = 0;

        foreach ($cookieNames as $cookieName) {
            if (array_key_exists($cookieName, $cookies)) {
                continue;
            }

            $cookies[$cookieName] = $this->matcher->match($cookieName)
                ?? $this->fallbackCookieDefinition($cookieName);

            $added++;
        }

        if ($added === 0 && $cookies === $rawCookies) {
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
                'technology' => [
                    'type' => 'cookie',
                    'label' => 'Cookie',
                ],
                'pattern' => '^complihance_consent$',
                'translations' => [
                    'en' => [
                        'name' => 'Consent cookie',
                        'description' => 'Stores the user cookie consent preferences.',
                        'duration' => '12 months',
                    ],
                    'it' => [
                        'name' => 'Cookie di consenso',
                        'description' => 'Memorizza le preferenze di consenso cookie dell’utente.',
                        'duration' => '12 mesi',
                    ],
                ],
            ],

            'complihance_anonymous_id' => [
                'category' => 'necessary',
                'vendor' => 'Complihance',
                'technology' => [
                    'type' => 'cookie',
                    'label' => 'Cookie',
                ],
                'pattern' => '^complihance_anonymous_id$',
                'translations' => [
                    'en' => [
                        'name' => 'Anonymous visitor identifier',
                        'description' => 'Stores an anonymous identifier used to associate consent records with anonymous visitors.',
                        'duration' => '12 months',
                    ],
                    'it' => [
                        'name' => 'Identificativo anonimo visitatore',
                        'description' => 'Memorizza un identificativo anonimo usato per associare i consensi ai visitatori anonimi.',
                        'duration' => '12 mesi',
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
            'technology' => [
                'type' => 'cookie',
                'label' => 'Cookie',
            ],
            'pattern' => '^'.preg_quote($cookieName, '/').'$',
            'translations' => [
                'en' => [
                    'name' => $cookieName,
                    'description' => null,
                    'duration' => 'Session',
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

    protected function normalizeDefinitions(array $cookies): array
    {
        return collect($cookies)
            ->map(fn (array $cookie, string $cookieName) => $this->normalizeDefinition($cookie, $cookieName))
            ->all();
    }

    protected function normalizeDefinition(array $cookie, string $cookieName): array
    {
        $translations = $cookie['translations'] ?? [];

        $rootDescription = $cookie['description'] ?? null;
        $rootDuration = $cookie['duration'] ?? null;

        unset($cookie['description'], $cookie['duration']);

        $translations['en'] = [
            'name' => $translations['en']['name'] ?? $cookieName,
            'description' => $translations['en']['description'] ?? $rootDescription,
            'duration' => $translations['en']['duration'] ?? $rootDuration ?? 'Session',
        ];

        foreach ($translations as $locale => $translation) {
            if ($locale === 'en') {
                continue;
            }

            $translations[$locale] = [
                'name' => $translation['name'] ?? $translations['en']['name'],
                'description' => $translation['description'] ?? $translations['en']['description'],
                'duration' => $translation['duration'] ?? $translations['en']['duration'],
            ];
        }

        $cookie['technology'] = $cookie['technology'] ?? [
            'type' => 'cookie',
            'label' => 'Cookie',
        ];
        $cookie['translations'] = $translations;

        return $cookie;
    }
}
