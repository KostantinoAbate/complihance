<div
    data-complihance-backdrop
    class="complihance-backdrop"
></div>

<div
    data-complihance-banner
    class="complihance-banner"
>
    <button
        type="button"
        data-complihance-reject
        aria-label="{{ __('Close cookie banner') }}"
        class="complihance-banner__close"
    >
        ×
    </button>

    <div class="complihance-banner__content">
        @if (! empty($texts['eyebrow']))
            <p class="complihance-eyebrow">
                {{ $texts['eyebrow'] }}
            </p>
        @endif

        @if (! empty($texts['title']))
            <h2 class="complihance-title">
                {{ $texts['title'] }}
            </h2>
        @endif

        @foreach (($texts['description'] ?? []) as $paragraph)
            <p class="complihance-description">
                @complihanceHtml($paragraph)
            </p>
        @endforeach

        @if (! empty(config('complihance.cookie_policy_url')) && ! empty($texts['cookie_policy_label']))
            <p class="complihance-policy-link">
                <a
                    href="{{ config('complihance.cookie_policy_url') }}"
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

        <div class="complihance-categories">
            @foreach ($categories as $key => $category)
                @php
                    $required = (bool) ($category['required'] ?? false);
                    $vendors = $vendorsByCategory[$key] ?? [];
                    $categoryInputId = 'cookie-category-' . $loop->iteration;
                @endphp

                <div
                    class="complihance-card"
                    data-complihance-accordion
                >
                    <button
                        type="button"
                        class="complihance-accordion__trigger complihance-row complihance-row--center"
                        data-complihance-accordion-trigger
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                    >
                        <input
                            class="complihance-checkbox"
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
                            class="complihance-label {{ $required ? 'complihance-label--disabled' : '' }}"
                            onclick="event.stopPropagation()"
                        >
                            {{ $category['label'] ?? $key }}
                        </label>

                        <span
                            class="complihance-accordion__icon"
                            data-complihance-accordion-icon
                        >
                            {{ $loop->first ? '−' : '+' }}
                        </span>
                    </button>

                    <div
                        data-complihance-accordion-panel
                        class="complihance-accordion__panel {{ $loop->first ? 'complihance-accordion__panel--open' : '' }}"
                    >
                        <div class="complihance-accordion__inner">
                            @if (! empty($category['description']))
                                <p class="complihance-help">
                                    {{ $category['description'] }}
                                </p>
                            @endif

                            @if ($granularConsentEnabled && ! empty($vendors))
                                <div class="complihance-vendors">
                                    @foreach ($vendors as $vendorKey => $vendor)
                                        <label class="complihance-vendor">
                                            <input
                                                class="complihance-checkbox"
                                                type="checkbox"
                                                name="vendors[]"
                                                value="{{ $vendorKey }}"
                                                data-complihance-vendor="{{ $vendorKey }}"
                                                data-complihance-vendor-category="{{ $key }}"
                                                @checked($required)
                                                @disabled($required)
                                            >

                                            <span>
                                                <strong class="complihance-vendor__title">
                                                    {{ $vendor['label'] ?? $vendorKey }}
                                                </strong>

                                                @if (! empty($vendor['description']))
                                                    <small class="complihance-vendor__description">
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

        <div class="complihance-actions">
            <div class="complihance-actions__group">
                <button
                    type="button"
                    data-complihance-reject
                    class="complihance-button complihance-button--ghost"
                >
                    {{ $texts['actions']['reject'] ?? __('Reject all') }}
                </button>

                <button
                    type="button"
                    data-complihance-save
                    class="complihance-button complihance-button--secondary"
                >
                    {{ $texts['actions']['save'] ?? __('Save preferences') }}
                </button>
            </div>

            <button
                type="button"
                data-complihance-accept-all
                class="complihance-button complihance-button--primary"
            >
                {{ $texts['actions']['accept_all'] ?? __('Accept all') }}
            </button>
        </div>
    </form>
</div>
