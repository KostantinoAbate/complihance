<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\View\Component;
use Illuminate\View\View;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;
use KostantinoAbate\Complihance\Services\Rendering\Resolver\BannerVisibilityResolver;
use KostantinoAbate\Complihance\View\Components\Concerns\ResolvesConsentDisplayData;

class Banner extends Component
{
    use ResolvesConsentDisplayData;

    /** @var array<string, string> */
    public array $texts;

    /** @var array<string, array<string, mixed>> */
    public array $categories;

    public bool $granularConsentEnabled;

    /** @var array<string, array<string, array<string, mixed>>> */
    public array $vendorsByCategory;

    /**
     * @throws FileNotFoundException
     */
    public function __construct(
        protected BannerVisibilityResolver $visibilityResolver,
        protected ComplihanceDataRepository $dataRepository,
    ) {
        $this->resolveConsentDisplayData();
    }

    /**
     * Determine whether the banner component should be rendered.
     */
    public function shouldRender(): bool
    {
        return $this->visibilityResolver->shouldShow();
    }

    /**
     * Render the cookie consent banner component.
     */
    public function render(): View|string
    {
        if (! $this->shouldRender()) {
            return '';
        }

        return view('complihance::components.banner');
    }
}
