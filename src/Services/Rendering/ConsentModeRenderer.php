<?php

namespace KostantinoAbate\Complihance\Services\Rendering;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;
use KostantinoAbate\Complihance\Services\Consent\Resolver\CurrentConsentResolver;

class ConsentModeRenderer
{
    public function __construct(
        protected ViewFactory $view,
        protected Request $request,
        protected ConsentModeService $consentMode,
        protected CurrentConsentResolver $currentConsentResolver,
    ) {}

    /**
     * Render the Google Consent Mode bootstrap script.
     */
    public function render(): string
    {
        if (! config('complihance.consent_mode.enabled', true)) {
            return '';
        }

        return $this->view->make('complihance::consent-mode', [
            'defaultConsentMode' => $this->consentMode->defaultPayload(),
            'currentConsentMode' => $this->currentConsentMode(),
        ])->render();
    }

    /**
     * Resolve the current Consent Mode payload from the active consent.
     *
     * @return array<string, string>|null
     */
    protected function currentConsentMode(): ?array
    {
        $consent = $this->currentConsentResolver->resolve($this->request);

        if (! $consent || $consent->revoked_at) {
            return null;
        }

        return $this->consentMode->fromCategories(
            array_values(array_unique($consent->accepted_categories ?? [])),
        );
    }
}
