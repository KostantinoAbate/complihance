<?php

namespace KostantinoAbate\Complihance\Services\Rendering\Resolver;

use Illuminate\Http\Request;
use KostantinoAbate\Complihance\Facades\ComplihancePolicy;
use KostantinoAbate\Complihance\Services\Consent\Resolver\ConsentRequestContextResolver;
use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;

class BannerVisibilityResolver
{
    public function __construct(
        protected CurrentConsentResolver $currentConsentResolver,
        protected ConsentRequestContextResolver $contextResolver,
        protected Request $request,
    ) {}

    public function shouldShow(): bool
    {
        if (! config('complihance.banner.enabled', true)) {
            return false;
        }

        $decodedConsent = $this->decodedConsentCookie();

        if (! $decodedConsent) {
            return true;
        }

        if (! $this->currentConsentResolver->hasActiveConsent($decodedConsent)) {
            return true;
        }

        return $this->cookiePolicyRequiresAcceptance()
            || $this->cookieConfigurationChanged($decodedConsent);
    }

    protected function decodedConsentCookie(): ?array
    {
        $cookie = $this->request->cookies->get(
            config('complihance.cookie_name', 'complihance_consent')
        );

        if (! $cookie) {
            return null;
        }

        $decodedConsent = json_decode($cookie, true);

        return is_array($decodedConsent) ? $decodedConsent : null;
    }

    protected function cookiePolicyRequiresAcceptance(): bool
    {
        $context = $this->contextResolver->resolve($this->request);

        return ComplihancePolicy::requiresAcceptance(
            key: 'cookie',
            subject: $context->subject,
            anonymousId: $context->anonymousId,
            sessionId: $context->sessionId,
        );
    }

    protected function cookieConfigurationChanged(array $decodedConsent): bool
    {
        $currentCookieConfigurationVersion = config(
            'complihance.cookie_configuration_version'
        );

        $acceptedCookieConfigurationVersion = $decodedConsent['cookie_configuration_version'] ?? null;

        return $acceptedCookieConfigurationVersion !== $currentCookieConfigurationVersion;
    }
}
