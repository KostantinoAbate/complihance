<?php

namespace KostantinoAbate\Complihance\Services\Rendering\Resolver;

use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;

class PreferencesVisibilityResolver
{
    public function __construct(
        protected CurrentConsentResolver $currentConsentResolver,
    ) {}

    public function shouldShow(): bool
    {
        if (! config('complihance.banner.enabled', true)) {
            return false;
        }

        return $this->currentConsentResolver->resolveFromCookie() !== null;
    }
}
