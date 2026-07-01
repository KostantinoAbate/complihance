<?php

namespace KostantinoAbate\Complihance\Services;

class PreferencesVisibilityResolver
{
    public function shouldShow(): bool
    {
        if (! config('complihance.banner.enabled', true)) {
            return false;
        }

        return request()->cookies->has(
            config('complihance.cookie_name', 'complihance_consent')
        );
    }
}
