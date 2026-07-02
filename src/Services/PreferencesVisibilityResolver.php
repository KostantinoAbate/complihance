<?php

namespace KostantinoAbate\Complihance\Services;

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
