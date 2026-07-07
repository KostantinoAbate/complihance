<?php

namespace KostantinoAbate\Complihance\Services\Rendering;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use JsonException;

class ComplihanceDataRepository
{
    /**
     * Get localized consent categories.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws FileNotFoundException
     */
    public function categories(?string $locale = null): array
    {
        return $this->localizedItems(
            $this->readJson($this->categoriesPath()),
            $locale,
        );
    }

    /**
     * Get raw consent categories from the configured JSON file.
     *
     * @return array<string, array<string, mixed>>
     *
     * @throws FileNotFoundException
     */
    public function rawCategories(): array
    {
        return $this->readJson($this->categoriesPath());
    }

    /**
     * Get localized known technologies.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws FileNotFoundException
     */
    public function technologies(?string $locale = null): array
    {
        return $this->localizedItems(
            $this->readJson($this->technologiesPath()),
            $locale,
        );
    }

    /**
     * Get raw known technologies from the configured JSON file.
     *
     * @return array<string, array<string, mixed>>
     *
     * @throws FileNotFoundException
     */
    public function rawTechnologies(): array
    {
        return $this->readJson($this->technologiesPath());
    }

    /**
     * Get all configured category keys.
     *
     * @return array<int, string>
     *
     * @throws FileNotFoundException
     *
     * @noinspection PhpUnused
     */
    public function categoryKeys(): array
    {
        return array_keys($this->rawCategories());
    }

    /**
     * Get the keys of categories that are always required.
     *
     * @return array<int, string>
     *
     * @throws FileNotFoundException
     *
     * @noinspection PhpUnused
     */
    public function requiredCategoryKeys(): array
    {
        return collect($this->rawCategories())
            ->filter(fn (array $category): bool => ($category['required'] ?? false) === true)
            ->keys()
            ->values()
            ->all();
    }

    /**
     * Get the Consent Mode mapping configured for each category.
     *
     * @return array<string, array<string, string>>
     *
     * @throws FileNotFoundException
     */
    public function consentModeMapping(): array
    {
        return collect($this->rawCategories())
            ->mapWithKeys(fn (array $category, string $key): array => [
                $key => $category['consent_mode'] ?? [],
            ])
            ->filter()
            ->all();
    }

    /**
     * Get the configured technologies JSON path.
     */
    public function technologiesPath(): string
    {
        return config('complihance.data.technologies_path')
            ?: resource_path('vendor/complihance/technologies.json');
    }

    /**
     * Get the configured categories JSON path.
     */
    public function categoriesPath(): string
    {
        return config('complihance.data.categories_path')
            ?: resource_path('vendor/complihance/categories.json');
    }

    /**
     * Get localized UI texts.
     *
     * @return array<string, string>
     *
     * @throws FileNotFoundException
     */
    public function texts(?string $locale = null): array
    {
        $data = $this->readJson($this->textsPath());

        $locale ??= app()->getLocale();
        $fallbackLocale = config('complihance.data.fallback_locale', 'en');

        return $data['translations'][$locale]
            ?? $data['translations'][$fallbackLocale]
            ?? [];
    }

    /**
     * Get the configured texts JSON path.
     */
    public function textsPath(): string
    {
        return config('complihance.data.texts_path')
            ?: resource_path('vendor/complihance/texts.json');
    }

    /**
     * Get technologies grouped by vendor.
     *
     * @return array<int, array<string, mixed>>
     *
     * @throws FileNotFoundException
     */
    public function vendors(?string $locale = null): array
    {
        return collect($this->technologies($locale))
            ->filter(fn (array $technology): bool => filled($technology['vendor'] ?? null))
            ->groupBy('vendor')
            ->map(function ($technologies, string $vendor): array {
                return [
                    'key' => str($vendor)->slug('_')->toString(),
                    'label' => $vendor,
                    'vendor' => $vendor,
                    'category' => $technologies->first()['category'] ?? null,
                    'technologies' => $technologies->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Localize keyed configuration items.
     *
     * @param  array<string, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function localizedItems(array $items, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return collect($items)
            ->map(function (array $item, string $key) use ($locale): array {
                return [
                    'key' => $key,
                    ...$this->localizeItem($item, $locale),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Apply the best available translation to a configuration item.
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function localizeItem(array $item, string $locale): array
    {
        $fallbackLocale = config('complihance.data.fallback_locale', 'en');
        $translations = $item['translations'] ?? [];

        $translation = $translations[$locale]
            ?? $translations[$fallbackLocale]
            ?? $translations['en']
            ?? [];

        unset($item['translations']);

        return [
            ...$item,
            ...$translation,
        ];
    }

    /**
     * Read and decode a JSON file.
     *
     * Invalid or missing files intentionally return an empty array so published
     * package assets can be overridden progressively by the host application.
     *
     * @return array<string, mixed>
     *
     * @throws FileNotFoundException
     */
    protected function readJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        try {
            $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }
}
