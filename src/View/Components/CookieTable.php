<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;

class CookieTable extends Component
{
    public Collection $technologiesByCategory;

    public array $categories;

    public function __construct(
        protected ComplihanceDataRepository $dataRepository,
        public ?string $category = null,
    ) {
        $this->technologiesByCategory = collect($this->dataRepository->technologies())
            ->map(fn (array $technology) => [
                'name' => $technology['key'],
                'technology' => $technology['technology'] ?? [
                        'type' => 'cookie',
                        'label' => 'Cookie',
                    ],
                'category' => $technology['category'],
                'vendor' => $technology['vendor'] ?? null,
                'duration' => $technology['duration'] ?? null,
                'description' => $technology['description'] ?? null,
            ])
            ->when(
                $this->category,
                fn (Collection $technologies) => $technologies->filter(
                    fn (array $technology) => $technology['category'] === $this->category
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
