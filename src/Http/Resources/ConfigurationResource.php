<?php

namespace KostantinoAbate\Complihance\Http\Resources;

use Illuminate\Support\Collection;

class ConfigurationResource
{
    public static function make(array $configuration): array
    {
        return [
            'categories' => self::categories($configuration['categories'] ?? []),
            'vendors' => self::vendors($configuration['vendors'] ?? []),
            'granular_consent' => [
                'enabled' => (bool) ($configuration['granular_consent']['enabled'] ?? false),
            ],
            'consent_mode' => [
                'enabled' => (bool) ($configuration['consent_mode']['enabled'] ?? false),
            ],
            'policy' => [
                'version' => $configuration['policy']['version'] ?? null,
            ],
            'cookies' => [
                'version' => $configuration['cookies']['version'] ?? null,
            ],
        ];
    }

    protected static function categories(array|Collection $categories): array
    {
        return collect($categories)
            ->map(fn (array $category, string|int $key) => [
                'key' => $category['key'] ?? $key,
                'label' => $category['label'] ?? $key,
                'description' => $category['description'] ?? null,
                'required' => (bool) ($category['required'] ?? false),
            ])
            ->values()
            ->all();
    }

    protected static function vendors(array|Collection $vendors): array
    {
        return collect($vendors)
            ->map(fn (array $vendor, string|int $key) => [
                'key' => $vendor['key'] ?? $key,
                'label' => $vendor['label'] ?? $vendor['name'] ?? $key,
                'description' => $vendor['description'] ?? null,
                'category' => $vendor['category'] ?? null,
            ])
            ->values()
            ->all();
    }
}
