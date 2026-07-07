<?php

namespace KostantinoAbate\Complihance\Services\Cookies\Scanner;

use Illuminate\Support\Facades\File;
use KostantinoAbate\Complihance\Services\Cookies\Scanner\Patterns\TechnologyMatcher;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;

class JsonWriter
{
    public function __construct(
        protected TechnologyMatcher $matcher,
        protected ComplihanceDataRepository $repository,
    ) {}

    public function addMissingTechnologies(array $items): int
    {
        $path = $this->repository->technologiesPath();

        $this->ensureFileExists($path);

        $rawTechnologies = $this->repository->rawTechnologies();
        $technologies = $this->normalizeDefinitions($rawTechnologies);

        $added = 0;

        foreach ($items as $item) {
            $key = $this->definitionKey($item);

            if ($key === null || array_key_exists($key, $technologies)) {
                continue;
            }

            $technologies[$key] = $this->matcherDefinition($item)
                ?? $this->fallbackTechnologyDefinition($item);

            $added++;
        }

        if ($added === 0 && $technologies === $rawTechnologies) {
            return 0;
        }

        ksort($technologies);

        File::put($path, $this->encode($technologies));

        return $added;
    }

    public function ensureCoreTechnologies(): int
    {
        return $this->addMissingTechnologyDefinitions([
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

    public function addMissingTechnologyDefinitions(array $definitions): int
    {
        $path = $this->repository->technologiesPath();

        $this->ensureFileExists($path);

        $technologies = $this->normalizeDefinitions($this->repository->rawTechnologies());

        $added = 0;

        foreach ($definitions as $key => $definition) {
            if (array_key_exists($key, $technologies)) {
                continue;
            }

            $technologies[$key] = $this->normalizeDefinition($definition, $key);
            $added++;
        }

        if ($added === 0) {
            return 0;
        }

        ksort($technologies);

        File::put($path, $this->encode($technologies));

        return $added;
    }

    protected function matcherDefinition(array $item): ?array
    {
        $type = $item['type'] ?? 'cookie';
        $value = $this->definitionKey($item);

        if ($value === null) {
            return null;
        }

        $match = $this->matcher->match($type, $value);

        if ($match === null) {
            return null;
        }

        unset($match['matched_key'], $match['matched_pattern']);

        return $this->normalizeDefinition($match, $value);
    }

    protected function fallbackTechnologyDefinition(array $item): array
    {
        $type = $item['type'] ?? 'cookie';

        return [
            'category' => $item['category'] ?? 'unclassified',
            'vendor' => $item['vendor'] ?? null,
            'technology' => $this->technology($type),
            'pattern' => $this->patternFor($item),
            'translations' => [
                'en' => [
                    'name' => $this->nameFor($item),
                    'description' => null,
                    'duration' => $this->durationFor($type),
                ],
            ],
        ];
    }

    protected function definitionKey(array $item): ?string
    {
        return match ($item['type'] ?? 'cookie') {
            'cookie' => $item['name'] ?? $item['key'] ?? null,
            'local_storage', 'session_storage' => $item['key'] ?? null,
            'script' => $item['src'] ?? $item['key'] ?? null,
            default => $item['key'] ?? null,
        };
    }

    protected function technology(string $type): array
    {
        return [
            'type' => $type,
            'label' => match ($type) {
                'cookie' => 'Cookie',
                'local_storage' => 'Local Storage',
                'session_storage' => 'Session Storage',
                'script' => 'Script',
                default => ucfirst(str_replace('_', ' ', $type)),
            },
        ];
    }

    protected function patternFor(array $item): string
    {
        $value = $this->definitionKey($item) ?? '';

        return '^'.preg_quote($value, '/').'$';
    }

    protected function nameFor(array $item): string
    {
        return $item['vendor']
            ?? $item['name']
            ?? $item['key']
            ?? $item['src']
            ?? 'Unknown technology';
    }

    protected function durationFor(string $type): string
    {
        return match ($type) {
            'local_storage' => 'Persistent',
            'session_storage' => 'Session',
            'script' => 'N/A',
            default => 'Session',
        };
    }

    protected function ensureFileExists(string $path): void
    {
        File::ensureDirectoryExists(dirname($path));

        if (! File::exists($path)) {
            File::put($path, $this->encode([]));
        }
    }

    protected function encode(array $data): string
    {
        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ).PHP_EOL;
    }

    protected function normalizeDefinitions(array $technologies): array
    {
        return collect($technologies)
            ->map(fn (array $technology, string $key) => $this->normalizeDefinition($technology, $key))
            ->all();
    }

    protected function normalizeDefinition(array $technology, string $key): array
    {
        $translations = $technology['translations'] ?? [];

        $rootDescription = $technology['description'] ?? null;
        $rootDuration = $technology['duration'] ?? null;

        unset($technology['description'], $technology['duration']);

        $type = $technology['technology']['type'] ?? 'cookie';

        $technology['technology'] = $technology['technology'] ?? $this->technology($type);

        $translations['en'] = [
            'name' => $translations['en']['name'] ?? $key,
            'description' => $translations['en']['description'] ?? $rootDescription,
            'duration' => $translations['en']['duration'] ?? $rootDuration ?? $this->durationFor($type),
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

        $technology['translations'] = $translations;

        return $technology;
    }
}
