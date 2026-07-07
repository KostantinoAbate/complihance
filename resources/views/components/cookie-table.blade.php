@if ($technologiesByCategory->isNotEmpty())
    <div class="space-y-8">
        @foreach ($technologiesByCategory as $categoryKey => $technologies)
            <section class="space-y-4">
                <h2 class="text-lg font-semibold">
                    {{ $categories[$categoryKey]['label'] ?? ucfirst($categoryKey) }}
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                        <tr>
                            <th class="border px-3 py-2 text-left">{{ __('Cookie') }}</th>
                            <th class="border px-3 py-2 text-left">{{ __('Vendor') }}</th>
                            <th class="border px-3 py-2 text-left">{{ __('Technology') }}</th>
                            <th class="border px-3 py-2 text-left">{{ __('Duration') }}</th>
                            <th class="border px-3 py-2 text-left">{{ __('Description') }}</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach ($technologies as $technology)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $technology['name'] ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $technology['vendor'] ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $technology['technology']['label'] ?? __('Cookie') }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $technology['duration'] ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $technology['description'] ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>
@endif
