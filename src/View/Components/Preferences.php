<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Preferences extends Component
{
    public function render(): View
    {
        return view('complihance::components.preferences');
    }
}
