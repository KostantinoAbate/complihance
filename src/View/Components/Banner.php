<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Banner extends Component
{
    public function shouldRender(): bool
    {
        return config('complihance.banner.enabled', true)
            && ! request()->cookies->has(config('complihance.cookie_name', 'complihance_consent'));
    }

    public function render(): View
    {
        return view('complihance::components.banner');
    }
}
