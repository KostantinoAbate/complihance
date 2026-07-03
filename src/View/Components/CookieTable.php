<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use KostantinoAbate\Complihance\Services\ComplihanceDataRepository;

class CookieTable extends Component
{
    public Collection $cookiesByCategory;

    public array $categories;

    public function __construct(
        protected ComplihanceDataRepository $dataRepository,
        public ?string $category = null,
    ) {
        $this->cookiesByCategory = collect($this->dataRepository->cookies())
            ->map(fn (array $cookie) => [
                'name' => $cookie['key'],
                'category' => $cookie['category'],
                'vendor' => $cookie['vendor'] ?? null,
                'duration' => $cookie['duration'] ?? null,
                'description' => $cookie['description'] ?? null,
            ])
            ->when(
                $this->category,
                fn (Collection $cookies) => $cookies->filter(
                    fn (array $cookie) => $cookie['category'] === $this->category
                )
            )
            ->groupBy('category');

        $this->categories = collect($this->dataRepository->categories())
            ->keyBy('key')
            ->all();
    }

    public function render(): View
    {
        return view('complihance::components.cookie-table');
    }
}
