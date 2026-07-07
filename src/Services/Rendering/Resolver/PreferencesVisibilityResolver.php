<?php

namespace KostantinoAbate\Complihance\Services\Rendering\Resolver;

use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;

/**
 * @noinspection PhpUnused
 */
class PreferencesVisibilityResolver
{
    public function __construct(
        protected CurrentConsentResolver $currentConsentResolver,
    ) {}

    /**
     * Determine whether the cookie preferences panel should be displayed.
     *
     * @noinspection PhpUnused
     */
    public function shouldShow(): bool
    {
        if (! config('complihance.banner.enabled', true)) {
            return false;
        }

        return $this->currentConsentResolver->resolveFromCookie() !== null;
    }
}
