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
    ) {}

    public function render(): string
    {
        if (! config('complihance.consent_mode.enabled', true)) {
            return '';
        }

        $currentConsentMode = null;

        $cookieName = config('complihance.cookie_name', 'complihance_consent');
        $cookie = $this->request->cookies->get($cookieName);

        if ($cookie) {
            $decoded = json_decode($cookie, true);

            if (is_array($decoded)) {
                $categories = $decoded['categories'] ?? [];

                if (is_array($categories)) {
                    $currentConsentMode = $this->consentMode->fromCategories($categories);
                }
            }
        }

        return $this->view->make('complihance::consent-mode', [
            'defaultConsentMode' => $this->consentMode->defaultPayload(),
            'currentConsentMode' => $currentConsentMode,
        ])->render();
    }
}
