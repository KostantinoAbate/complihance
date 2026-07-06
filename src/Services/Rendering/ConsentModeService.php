<?php

namespace KostantinoAbate\Complihance\Services\Rendering;

class ConsentModeService
{
    public function __construct(
        protected ComplihanceDataRepository $data,
    ) {}

    public function defaultPayload(): array
    {
        return config('complihance.consent_mode.default', []);
    }

    public function fromCategories(array $categories): array
    {
        $payload = $this->defaultPayload();

        foreach (
            $this->data->consentModeMapping() as $categoryKey => $consentModeKeys
        ) {
            $granted = array_is_list($categories)
                ? in_array($categoryKey, $categories, true)
                : (bool) ($categories[$categoryKey] ?? false);

            foreach ($consentModeKeys as $consentModeKey) {
                $payload[$consentModeKey] =
                    $granted
                        ? 'granted'
                        : 'denied';
            }
        }

        $payload['security_storage'] = 'granted';

        return $payload;
    }
}
