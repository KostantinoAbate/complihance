<?php

namespace KostantinoAbate\Complihance\View\Components\Concerns;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;

trait ResolvesConsentDisplayData
{
    /**
     * Resolve localized texts, categories and optional vendors grouped by category.
     *
     * @throws FileNotFoundException
     */
    protected function resolveConsentDisplayData(): void
    {
        $this->texts = $this->dataRepository->texts();

        $this->categories = collect($this->dataRepository->categories())
            ->keyBy('key')
            ->all();

        $this->granularConsentEnabled = (bool) config('complihance.granular_consent.enabled', false);

        $this->vendorsByCategory = collect($this->dataRepository->vendors())
            ->filter(function (array $vendor): bool {
                $categoryKey = $vendor['category'] ?? null;

                if (! is_string($categoryKey) || $categoryKey === '') {
                    return false;
                }

                return ! ($this->categories[$categoryKey]['required'] ?? false);
            })
            ->groupBy('category')
            ->map(fn (Collection $vendors): array => $vendors->keyBy('key')->all())
            ->all();
    }
}
