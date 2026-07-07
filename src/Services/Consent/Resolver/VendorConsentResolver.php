<?php

namespace KostantinoAbate\Complihance\Services\Consent\Resolver;

use KostantinoAbate\Complihance\Services\Consent\GranularConsent;

class VendorConsentResolver
{
    /**
     * Resolve accepted vendors by intersecting selected vendors with accepted categories.
     *
     * @param  array<int, string>  $categories
     * @param  array<int, string>  $vendors
     * @return array<int, string>
     */
    public function resolve(array $categories, array $vendors): array
    {
        $acceptedCategories = collect($categories);
        $selectedVendors = collect($vendors);

        return collect(GranularConsent::vendors())
            ->filter(function (array $vendor, string $vendorKey) use ($acceptedCategories, $selectedVendors): bool {
                $categoryKey = $vendor['category'] ?? null;

                if (! $categoryKey || ! $acceptedCategories->contains($categoryKey)) {
                    return false;
                }

                return $selectedVendors->contains($vendorKey);
            })
            ->keys()
            ->values()
            ->all();
    }
}
