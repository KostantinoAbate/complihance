<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\View\Component;
use KostantinoAbate\Complihance\Services\BannerVisibilityResolver;

class Banner extends Component
{
    public function __construct(
        protected BannerVisibilityResolver $visibilityResolver,
    ) {}

    public function shouldRender(): bool
    {
        return $this->visibilityResolver->shouldShow();
    }

    public function render()
    {
        return view('complihance::components.banner');
    }
}
