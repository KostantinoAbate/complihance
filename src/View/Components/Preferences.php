<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;

class Preferences extends Component
{
    public array $texts;

    public array $categories;

    public bool $granularConsentEnabled;

    public array $vendorsByCategory;

    public array $acceptedCategories;

    public array $acceptedVendors;

    public function __construct(
        protected ComplihanceDataRepository $dataRepository,
        protected CurrentConsentResolver $currentConsentResolver,
    ) {
        $this->texts = $this->dataRepository->texts();

        $this->categories = collect($this->dataRepository->categories())
            ->keyBy('key')
            ->all();

        $this->granularConsentEnabled = (bool) config('complihance.granular_consent.enabled', false);

        $this->vendorsByCategory = collect($this->dataRepository->vendors())
            ->filter(function (array $vendor) {
                $categoryKey = $vendor['category'] ?? null;

                if (! $categoryKey) {
                    return false;
                }

                return ! (bool) ($this->categories[$categoryKey]['required'] ?? false);
            })
            ->groupBy('category')
            ->map(fn ($vendors) => $vendors->keyBy('key')->all())
            ->all();

        $currentConsent = $this->currentConsentResolver->resolve(request());

        $this->acceptedCategories = collect($currentConsent?->accepted_categories ?? [])
            ->values()
            ->all();

        $this->acceptedVendors = collect($currentConsent?->vendors ?? [])
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('complihance::components.preferences');
    }
}
