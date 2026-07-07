<?php

namespace KostantinoAbate\Complihance\Services\Rendering\Resolver;

use Illuminate\Http\Request;
use JsonException;
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

    /**
     * Determine whether the cookie consent banner should be displayed.
     */
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

    /**
     * Decode the consent cookie payload from the current request.
     *
     * @return array<string, mixed>|null
     */
    protected function decodedConsentCookie(): ?array
    {
        $cookie = $this->request->cookies->get(
            config('complihance.cookie_name', 'complihance_consent')
        );

        if (! is_string($cookie) || $cookie === '') {
            return null;
        }

        try {
            $decodedConsent = json_decode($cookie, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decodedConsent) ? $decodedConsent : null;
    }

    /**
     * Determine whether the current cookie policy version still requires acceptance.
     */
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

    /**
     * Determine whether the accepted cookie configuration version is outdated.
     *
     * @param  array<string, mixed>  $decodedConsent
     */
    protected function cookieConfigurationChanged(array $decodedConsent): bool
    {
        $currentCookieConfigurationVersion = config('complihance.cookie_configuration_version');
        $acceptedCookieConfigurationVersion = $decodedConsent['cookie_configuration_version'] ?? null;

        return $acceptedCookieConfigurationVersion !== $currentCookieConfigurationVersion;
    }
}
