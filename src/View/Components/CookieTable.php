<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;

class CookieTable extends Component
{
    public function __construct(
        public ?string $category = null,
    ) {}

    public function cookies(): Collection
    {
        return collect(config('complihance-cookies.cookies', []))
            ->map(function (array $cookie, string $name) {
                return [
                    'name' => $name,
                    'category' => $cookie['category'] ?? 'unknown',
                    'vendor' => $cookie['vendor'] ?? null,
                    'duration' => $cookie['duration'] ?? null,
                    'description' => $cookie['description'] ?? null,
                ];
            })
            ->when($this->category, fn (Collection $cookies) => $cookies
                ->filter(fn (array $cookie) => $cookie['category'] === $this->category)
            )
            ->groupBy('category');
    }

    public function render(): View
    {
        return view('complihance::components.cookie-table');
    }
}
