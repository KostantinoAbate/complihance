<?php

namespace KostantinoAbate\Complihance\Http\Resources;

use Illuminate\Support\Collection;

class ConfigurationResource
{
    /**
     * Transform the frontend configuration payload.
     *
     * @param  array<string, mixed>  $configuration
     * @return array<string, mixed>
     */
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

    /**
     * Transform configured categories for frontend usage.
     *
     * @param  array<string, mixed>|Collection  $categories
     * @return array<int, array<string, mixed>>
     */
    protected static function categories(array|Collection $categories): array
    {
        return collect($categories)
            ->map(fn (array $category, string|int $key): array => [
                'key' => $category['key'] ?? $key,
                'label' => $category['label'] ?? $key,
                'description' => $category['description'] ?? null,
                'required' => (bool) ($category['required'] ?? false),
            ])
            ->values()
            ->all();
    }

    /**
     * Transform configured vendors for frontend usage.
     *
     * @param  array<string, mixed>|Collection  $vendors
     * @return array<int, array<string, mixed>>
     */
    protected static function vendors(array|Collection $vendors): array
    {
        return collect($vendors)
            ->map(fn (array $vendor, string|int $key): array => [
                'key' => $vendor['key'] ?? $key,
                'label' => $vendor['label'] ?? $vendor['name'] ?? $key,
                'description' => $vendor['description'] ?? null,
                'category' => $vendor['category'] ?? null,
            ])
            ->values()
            ->all();
    }
}
