<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\View\Component;
use Illuminate\View\View;
use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;
use KostantinoAbate\Complihance\View\Components\Concerns\ResolvesConsentDisplayData;

class Preferences extends Component
{
    use ResolvesConsentDisplayData;

    /** @var array<string, string> */
    public array $texts;

    /** @var array<string, array<string, mixed>> */
    public array $categories;

    public bool $granularConsentEnabled;

    /** @var array<string, array<string, array<string, mixed>>> */
    public array $vendorsByCategory;

    /** @var array<int, string> */
    public array $acceptedCategories;

    /** @var array<int, string> */
    public array $acceptedVendors;

    /**
     * @throws FileNotFoundException
     */
    public function __construct(
        protected ComplihanceDataRepository $dataRepository,
        protected CurrentConsentResolver $currentConsentResolver,
    ) {
        $this->resolveConsentDisplayData();

        $currentConsent = $this->currentConsentResolver->resolve(request());

        $this->acceptedCategories = collect($currentConsent?->accepted_categories ?? [])
            ->values()
            ->all();

        $this->acceptedVendors = collect($currentConsent?->vendors ?? [])
            ->values()
            ->all();
    }

    /**
     * Render the cookie preferences component.
     */
    public function render(): View
    {
        return view('complihance::components.preferences');
    }
}
