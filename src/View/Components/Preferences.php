<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\Http\Request;
use Illuminate\View\Component;
use Illuminate\View\View;
use KostantinoAbate\Complihance\Services\ComplihanceDataRepository;

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
        protected Request $request,
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
        $currentConsent = $this->currentConsent();
        $this->acceptedCategories = collect($currentConsent['accepted_categories'] ?? [])
            ->values()
            ->all();
        $this->acceptedVendors = collect($currentConsent['vendors'] ?? [])
            ->values()
            ->all();
    }

    protected function currentConsent(): array
    {
        $cookieName = config('complihance.cookie_name', 'complihance_consent');

        $cookie = $this->request->cookie($cookieName);

        if (! $cookie) {
            return [];
        }

        $decoded = json_decode($cookie, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function render(): View
    {
        return view('complihance::components.preferences');
    }
}
