<?php

namespace KostantinoAbate\Complihance\Services;

use KostantinoAbate\Complihance\Support\GranularConsent;

class VendorConsentResolver
{
    public function resolve(array $categories, array $vendors): array
    {
        $acceptedCategories = collect($categories);
        $selectedVendors = collect($vendors);

        return collect(GranularConsent::vendors())
            ->filter(function (array $vendor, string $vendorKey) use ($acceptedCategories, $selectedVendors) {
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
