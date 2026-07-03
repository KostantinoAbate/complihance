@if($cookiesByCategory->isNotEmpty())
    <div class="space-y-8">
        @foreach($cookiesByCategory as $categoryKey => $cookies)
            <section class="space-y-4">
                <h2 class="text-lg font-semibold">
                    {{ $categories[$categoryKey]['label'] ?? ucfirst($categoryKey) }}
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-sm">
                        <thead>
                        <tr>
                            <th class="border px-3 py-2 text-left">Cookie</th>
                            <th class="border px-3 py-2 text-left">Vendor</th>
                            <th class="border px-3 py-2 text-left">Durata</th>
                            <th class="border px-3 py-2 text-left">Descrizione</th>
                        </tr>
                        </thead>

                        <tbody>
                        @foreach($cookies as $cookie)
                            <tr>
                                <td class="border px-3 py-2">
                                    {{ $cookie['name'] }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $cookie['vendor'] ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $cookie['duration'] ?? '-' }}
                                </td>

                                <td class="border px-3 py-2">
                                    {{ $cookie['description'] ?? '-' }}
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
