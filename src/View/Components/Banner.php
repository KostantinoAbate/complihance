<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use KostantinoAbate\Complihance\Services\BannerVisibilityResolver;
use KostantinoAbate\Complihance\Services\ComplihanceDataRepository;

class Banner extends Component
{
    public array $texts;
    public array $categories;
    public bool $granularConsentEnabled;
    public array $vendorsByCategory;

    public function __construct(
        protected BannerVisibilityResolver $visibilityResolver,
        protected ComplihanceDataRepository $dataRepository,
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
    }

    public function shouldRender(): bool
    {
        return $this->visibilityResolver->shouldShow();
    }

    public function render(): View|string
    {
        if (! $this->shouldRender()) {
            return '';
        }

        return view('complihance::components.banner');
    }
}
