<?php

namespace KostantinoAbate\Complihance\Services;

use KostantinoAbate\Complihance\Facades\ComplihancePolicy;

class BannerVisibilityResolver
{
    public function shouldShow(): bool
    {
        if (! config('complihance.banner.enabled', true)) {
            return false;
        }

        $cookie = request()->cookies->get(
            config('complihance.cookie_name', 'complihance_consent')
        );

        if (! $cookie) {
            return true;
        }

        $decodedConsent = json_decode($cookie, true);

        if (! is_array($decodedConsent)) {
            return true;
        }

        return $this->cookiePolicyRequiresAcceptance()
            || $this->cookieConfigurationChanged($decodedConsent);
    }

    protected function cookiePolicyRequiresAcceptance(): bool
    {
        return ComplihancePolicy::requiresAcceptance(
            key: 'cookie'
        );
    }

    protected function cookieConfigurationChanged(array $decodedConsent): bool
    {
        $currentCookieConfigurationVersion = config('complihance.cookie_configuration_version');

        $acceptedCookieConfigurationVersion = $decodedConsent['cookie_configuration_version'] ?? null;

        return $acceptedCookieConfigurationVersion !== $currentCookieConfigurationVersion;
    }
}
