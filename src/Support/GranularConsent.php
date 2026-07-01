<?php

namespace KostantinoAbate\Complihance\Support;

class GranularConsent
{
    public static function enabled(): bool
    {
        return (bool) config('complihance.granular_consent.enabled', false);
    }

    public static function vendors(): array
    {
        if (! self::enabled()) {
            return [];
        }

        $vendors = [];

        foreach (config('complihance.categories', []) as $categoryKey => $category) {
            foreach (($category['vendors'] ?? []) as $vendorKey => $vendor) {
                $vendors[$vendorKey] = [
                    ...$vendor,
                    'category' => $categoryKey,
                    'category_required' => (bool) ($category['required'] ?? false),
                ];
            }
        }

        return $vendors;
    }
}
