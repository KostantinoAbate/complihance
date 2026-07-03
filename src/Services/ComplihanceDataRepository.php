<?php

namespace KostantinoAbate\Complihance\Services;

use Illuminate\Support\Facades\File;

class ComplihanceDataRepository
{
    public function categories(?string $locale = null): array
    {
        return $this->localizedItems(
            $this->readJson($this->categoriesPath()),
            $locale
        );
    }

    public function rawCategories(): array
    {
        return $this->readJson($this->categoriesPath());
    }

    public function cookies(?string $locale = null): array
    {
        return $this->localizedItems(
            $this->readJson($this->cookiesPath()),
            $locale
        );
    }

    public function rawCookies(): array
    {
        return $this->readJson($this->cookiesPath());
    }

    public function categoryKeys(): array
    {
        return array_keys($this->rawCategories());
    }

    public function requiredCategoryKeys(): array
    {
        return collect($this->rawCategories())
            ->filter(fn (array $category) => ($category['required'] ?? false) === true)
            ->keys()
            ->values()
            ->all();
    }

    public function consentModeMapping(): array
    {
        return collect($this->rawCategories())
            ->mapWithKeys(fn (array $category, string $key) => [
                $key => $category['consent_mode'] ?? [],
            ])
            ->filter()
            ->all();
    }

    public function cookiesPath(): string
    {
        return config('complihance.data.cookies_path')
            ?: resource_path('vendor/complihance/cookies.json');
    }

    public function categoriesPath(): string
    {
        return config('complihance.data.categories_path')
            ?: resource_path('vendor/complihance/categories.json');
    }

    public function texts(?string $locale = null): array
    {
        $data = $this->readJson($this->textsPath());

        $locale ??= app()->getLocale();
        $fallbackLocale = config('complihance.data.fallback_locale', 'en');

        return $data['translations'][$locale]
            ?? $data['translations'][$fallbackLocale]
            ?? [];
    }

    public function textsPath(): string
    {
        return config('complihance.data.texts_path')
            ?: resource_path('vendor/complihance/texts.json');
    }

    public function vendors(?string $locale = null): array
    {
        return collect($this->cookies($locale))
            ->filter(fn (array $cookie) => filled($cookie['vendor'] ?? null))
            ->groupBy('vendor')
            ->map(function ($cookies, string $vendor) {
                return [
                    'key' => str($vendor)->slug('_')->toString(),
                    'label' => $vendor,
                    'vendor' => $vendor,
                    'category' => $cookies->first()['category'] ?? null,
                    'cookies' => $cookies->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    protected function localizedItems(array $items, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return collect($items)
            ->map(function (array $item, string $key) use ($locale) {
                return [
                    'key' => $key,
                    ...$this->localizeItem($item, $locale),
                ];
            })
            ->values()
            ->all();
    }

    protected function localizeItem(array $item, string $locale): array
    {
        $fallbackLocale = config('complihance.data.fallback_locale', 'en');

        $translations = $item['translations'] ?? [];

        $translation = $translations[$locale]
            ?? $translations[$fallbackLocale]
            ?? [];

        unset($item['translations']);

        return [
            ...$item,
            ...$translation,
        ];
    }

    protected function readJson(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
