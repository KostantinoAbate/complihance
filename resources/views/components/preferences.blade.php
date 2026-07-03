<div
    data-complihance-preferences
    data-complihance-endpoint="{{ route('complihance.consent.store') }}"
    class="rounded-2xl border border-slate-200 bg-white p-6 text-slate-900"
>
    <h2 class="mb-4 text-xl font-bold">
        {{ $texts['preferences']['title'] ?? 'Gestisci preferenze cookie' }}
    </h2>

    <form data-complihance-form>
        @csrf

        <div class="mb-5 grid gap-3">
            @foreach ($categories as $key => $category)
                @php
                    $required = (bool) ($category['required'] ?? false);
                    $vendors = $vendorsByCategory[$key] ?? [];
                    $categoryInputId = 'preferences-cookie-category-' . $loop->iteration;
                    $categoryChecked = $required || in_array($key, $acceptedCategories, true);
                @endphp

                <div class="rounded-xl border border-slate-200 p-4">
                    <label class="flex items-start gap-3">
                        <input
                            class="mt-1 size-4 accent-slate-900 disabled:accent-slate-300"
                            type="checkbox"
                            name="categories[]"
                            value="{{ $key }}"
                            id="{{ $categoryInputId }}"
                            data-complihance-category="{{ $key }}"
                            data-required="{{ $required ? 'true' : 'false' }}"
                            @checked($categoryChecked)
                            @disabled($required)
                        >

                        <span>
                            <strong class="block text-sm font-semibold {{ $required ? 'text-slate-500' : 'text-slate-900' }}">
                                {{ $category['label'] ?? $key }}
                            </strong>

                            @if (! empty($category['description']))
                                <small class="mt-1 block text-sm leading-6 text-slate-500">
                                    {{ $category['description'] }}
                                </small>
                            @endif
                        </span>
                    </label>

                    @if ($granularConsentEnabled && ! empty($vendors))
                        <div class="mt-4 ml-7 grid gap-2">
                            @foreach ($vendors as $vendorKey => $vendor)
                                @php
                                    $vendorChecked = $required || in_array($vendorKey, $acceptedVendors, true);
                                @endphp

                                <label class="flex items-start gap-3 rounded-lg bg-slate-50 p-3">
                                    <input
                                        class="mt-1 size-4 accent-slate-900 disabled:accent-slate-300"
                                        type="checkbox"
                                        name="vendors[]"
                                        value="{{ $vendorKey }}"
                                        data-complihance-vendor="{{ $vendorKey }}"
                                        data-complihance-vendor-category="{{ $key }}"
                                        @checked($vendorChecked)
                                        @disabled($required)
                                    >

                                    <span>
                                        <strong class="block text-sm font-semibold">
                                            {{ $vendor['label'] ?? $vendorKey }}
                                        </strong>

                                        @if (! empty($vendor['description']))
                                            <small class="mt-1 block text-xs leading-5 text-slate-500">
                                                {{ $vendor['description'] }}
                                            </small>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <button
            type="button"
            data-complihance-save
            class="hidden"
        >
            {{ $texts['preferences']['save'] ?? 'Salva preferenze' }}
        </button>

        <p
            data-complihance-preferences-feedback
            class="mt-3 hidden text-sm text-green-700"
        >
            {{ $texts['preferences']['saved'] ?? 'Preferenze aggiornate correttamente.' }}
        </p>
    </form>

    <button
        type="button"
        data-complihance-revoke
        class="mt-4 rounded-lg bg-red-800 px-4 py-2 text-sm font-semibold text-red-50 hover:bg-red-700"
    >
        {{ $texts['preferences']['revoke'] ?? 'Revoca consenso' }}
    </button>
</div>
