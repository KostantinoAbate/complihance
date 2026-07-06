<?php

namespace KostantinoAbate\Complihance\Services;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Request;

class ConsentModeRenderer
{
    public function __construct(
        protected ViewFactory $view,
        protected Request $request,
        protected ConsentModeService $consentMode,
        protected CurrentConsentResolver $currentConsentResolver,
    ) {}

    public function render(): string
    {
        if (! config('complihance.consent_mode.enabled', true)) {
            return '';
        }

        $currentConsentMode = null;

        $consent = $this->currentConsentResolver->resolve($this->request);

        if ($consent && ! $consent->revoked_at) {
            $currentConsentMode = $this->consentMode->fromCategories(
                array_values(array_unique($consent->accepted_categories ?? []))
            );
        }

        return $this->view->make('complihance::consent-mode', [
            'defaultConsentMode' => $this->consentMode->defaultPayload(),
            'currentConsentMode' => $currentConsentMode,
        ])->render();
    }
}
