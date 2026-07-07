<?php

namespace KostantinoAbate\Complihance\View\Components;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Illuminate\View\View;
use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;

class CookieTable extends Component
{
    /** @var Collection<string, Collection<int, array<string, mixed>>> */
    public Collection $technologiesByCategory;

    /** @var array<string, array<string, mixed>> */
    public array $categories;

    /**
     * @throws FileNotFoundException
     */
    public function __construct(
        protected ComplihanceDataRepository $dataRepository,
        public ?string $category = null,
    ) {
        $this->technologiesByCategory = collect($this->dataRepository->technologies())
            ->map(fn (array $technology): array => [
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
                fn (Collection $technologies): Collection => $technologies->filter(
                    fn (array $technology): bool => $technology['category'] === $this->category,
                ),
            )
            ->groupBy('category');

        $this->categories = collect($this->dataRepository->categories())
            ->keyBy('key')
            ->all();
    }

    /**
     * Render the cookie table component.
     */
    public function render(): View
    {
        return view('complihance::components.cookie-table');
    }
}
