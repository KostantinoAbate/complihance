<div
    data-complihance-backdrop
    class="fixed inset-0 z-[9998] bg-black/40"
></div>

<div
    data-complihance-banner
    data-complihance-endpoint="{{ route('complihance.consent.store') }}"
    class="fixed inset-x-0 bottom-0 z-[9999] max-h-[85vh] overflow-y-auto rounded-t-2xl bg-white p-6 text-slate-900 shadow-2xl ring-1 ring-black/10 md:p-8"
>
    <button
        type="button"
        data-complihance-reject
        aria-label="Chiudi banner cookie"
        class="absolute right-5 top-4 text-3xl leading-none text-slate-900"
    >
        ×
    </button>

    <div class="pr-8">
        @if (! empty($texts['eyebrow']))
            <p class="mb-1 text-xs text-slate-500">
                {{ $texts['eyebrow'] }}
            </p>
        @endif

        @if (! empty($texts['title']))
            <h2 class="mb-2 text-xl font-bold leading-tight md:text-2xl">
                {{ $texts['title'] }}
            </h2>
        @endif

        @foreach (($texts['description'] ?? []) as $paragraph)
            <p class="mb-1.5 text-xs leading-6 text-slate-700">
                {!! $paragraph !!}
            </p>
        @endforeach

        @if (! empty($texts['cookie_policy_url']) && ! empty($texts['cookie_policy_label']))
            <p class="mb-5 text-xs">
                <a
                    href="{{ $texts['cookie_policy_url'] }}"
                    class="font-medium underline underline-offset-2"
                    target="_blank"
                    rel="noopener"
                >
                    {{ $texts['cookie_policy_label'] }}
                </a>
            </p>
        @endif
    </div>

    <form data-complihance-form>
        @csrf

        <div class="mb-5 grid gap-3">
            @foreach ($categories as $key => $category)
                @php
                    $required = (bool) ($category['required'] ?? false);
                    $vendors = $vendorsByCategory[$key] ?? [];
                    $categoryInputId = 'cookie-category-' . $loop->iteration;
                @endphp

                <div
                    class="rounded-xl border border-slate-200 p-4"
                    data-complihance-accordion
                >
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 text-left"
                        data-complihance-accordion-trigger
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                    >
                        <input
                            class="mt-0.5 size-4 shrink-0 accent-slate-900 disabled:accent-slate-300"
                            type="checkbox"
                            name="categories[]"
                            value="{{ $key }}"
                            id="{{ $categoryInputId }}"
                            data-complihance-category="{{ $key }}"
                            data-required="{{ $required ? 'true' : 'false' }}"
                            @checked($required)
                            @disabled($required)
                            onclick="event.stopPropagation()"
                        >

                        <label
                            for="{{ $categoryInputId }}"
                            class="cursor-pointer text-sm font-semibold {{ $required ? 'text-slate-500' : 'text-slate-900' }}"
                            onclick="event.stopPropagation()"
                        >
                            {{ $category['label'] ?? $key }}
                        </label>

                        <span
                            class="ml-auto text-xl leading-none text-slate-500"
                            data-complihance-accordion-icon
                        >
                            {{ $loop->first ? '−' : '+' }}
                        </span>
                    </button>

                    <div
                        data-complihance-accordion-panel
                        class="grid transition-[grid-template-rows] duration-300 ease-in-out {{ $loop->first ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}"
                    >
                        <div class="overflow-hidden">
                            @if (! empty($category['description']))
                                <p class="mt-3 pl-7 text-sm leading-6 text-slate-500">
                                    {{ $category['description'] }}
                                </p>
                            @endif

                            @if ($granularConsentEnabled && ! empty($vendors))
                                <div class="mt-4 ml-7 grid gap-2">
                                    @foreach ($vendors as $vendorKey => $vendor)
                                        <label class="flex items-start gap-3 rounded-lg bg-slate-50 p-3">
                                            <input
                                                class="mt-1 size-4 accent-slate-900 disabled:accent-slate-300"
                                                type="checkbox"
                                                name="vendors[]"
                                                value="{{ $vendorKey }}"
                                                data-complihance-vendor="{{ $vendorKey }}"
                                                data-complihance-vendor-category="{{ $key }}"
                                                @checked($required)
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
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex w-full flex-col gap-2 justify-between max-md:justify-center md:flex-row">
            <div class="flex flex-col gap-2 justify-center md:flex-row">
                <button
                    type="button"
                    data-complihance-reject
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                >
                    {{ $texts['actions']['reject'] ?? 'Rifiuta tutto' }}
                </button>

                <button
                    type="button"
                    data-complihance-save
                    class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-200"
                >
                    {{ $texts['actions']['save'] ?? 'Salva preferenze' }}
                </button>
            </div>

            <button
                type="button"
                data-complihance-accept-all
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
            >
                {{ $texts['actions']['accept_all'] ?? 'Accetta tutto' }}
            </button>
        </div>
    </form>
</div>
