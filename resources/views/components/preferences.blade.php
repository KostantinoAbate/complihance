<div
    data-complihance-preferences
    class="complihance-preferences"
>
    <h2
        id="complihance-preferences-title"
        class="complihance-title"
    >
        {{ $texts['preferences']['title'] ?? __('Manage Cookie Preferences') }}
    </h2>

    <form data-complihance-form>
        @csrf

        <div class="complihance-categories">
            @foreach ($categories as $key => $category)
                @php
                    $required = (bool) ($category['required'] ?? false);
                    $vendors = $vendorsByCategory[$key] ?? [];
                    $categoryInputId = 'complihance-preferences-category-' . $loop->iteration;
                    $categoryChecked = $required || in_array($key, $acceptedCategories, true);
                @endphp

                <div class="complihance-card">
                    <label
                        class="complihance-row"
                        for="{{ $categoryInputId }}"
                    >
                        <input
                            class="complihance-checkbox"
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
                            <strong class="complihance-label {{ $required ? 'complihance-label--disabled' : '' }}">
                                {{ $category['label'] ?? $key }}
                            </strong>

                            @if (! empty($category['description']))
                                <small class="complihance-vendor__description">
                                    {{ $category['description'] }}
                                </small>
                            @endif
                        </span>
                    </label>

                    @if ($granularConsentEnabled && ! empty($vendors))
                        <div class="complihance-vendors">
                            @foreach ($vendors as $vendorKey => $vendor)
                                @php
                                    $vendorInputId = 'complihance-preferences-vendor-' . $loop->parent->iteration . '-' . $loop->iteration;
                                    $vendorChecked = $required || in_array($vendorKey, $acceptedVendors, true);
                                @endphp

                                <label
                                    class="complihance-vendor"
                                    for="{{ $vendorInputId }}"
                                >
                                    <input
                                        class="complihance-checkbox"
                                        type="checkbox"
                                        name="vendors[]"
                                        value="{{ $vendorKey }}"
                                        id="{{ $vendorInputId }}"
                                        data-complihance-vendor="{{ $vendorKey }}"
                                        data-complihance-vendor-category="{{ $key }}"
                                        @checked($vendorChecked)
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
            @endforeach
        </div>

        <button
            type="button"
            data-complihance-save
            class="complihance-hidden"
        >
            {{ $texts['preferences']['save'] ?? __('Save preferences') }}
        </button>

        <p
            data-complihance-preferences-feedback
            class="complihance-feedback complihance-hidden"
            role="status"
            aria-live="polite"
        >
            {{ $texts['preferences']['saved'] ?? __('Preferences correctly updated!') }}
        </p>
    </form>

    <button
        type="button"
        data-complihance-revoke
        class="complihance-button complihance-button--danger"
    >
        {{ $texts['preferences']['revoke'] ?? __('Withdraw consent') }}
    </button>
</div>
