<?php

namespace KostantinoAbate\Complihance\Services;

class ConsentModeService
{
    public function defaultPayload(): array
    {
        return config('complihance.consent_mode.default', []);
    }

    public function fromCategories(array $categories): array
    {
        $payload = $this->defaultPayload();

        $mapping = config('complihance.consent_mode.mapping', []);

        foreach ($mapping as $categoryKey => $consentModeKeys) {
            $granted = (bool) ($categories[$categoryKey] ?? false);

            foreach ($consentModeKeys as $consentModeKey) {
                $payload[$consentModeKey] = $granted ? 'granted' : 'denied';
            }
        }

        // Sempre granted: consenso tecnico/sicurezza.
        $payload['security_storage'] = 'granted';

        return $payload;
    }
}
