<?php

namespace KostantinoAbate\Complihance\Services\Rendering;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class ConsentModeService
{
    public function __construct(
        protected ComplihanceDataRepository $data,
    ) {}

    /**
     * Get the default Consent Mode payload.
     *
     * @return array<string, string>
     */
    public function defaultPayload(): array
    {
        return config('complihance.consent_mode.default', []);
    }

    /**
     * Build a Consent Mode payload from accepted consent categories.
     *
     * Accepts both list-style category keys and associative category states.
     *
     * @param array<int|string, bool|string> $categories
     * @return array<string, string>
     * @throws FileNotFoundException
     */
    public function fromCategories(array $categories): array
    {
        $payload = $this->defaultPayload();

        foreach ($this->data->consentModeMapping() as $categoryKey => $consentModeKeys) {
            $granted = array_is_list($categories)
                ? in_array($categoryKey, $categories, true)
                : (bool) ($categories[$categoryKey] ?? false);

            foreach ($consentModeKeys as $consentModeKey) {
                $payload[$consentModeKey] = $granted ? 'granted' : 'denied';
            }
        }

        $payload['security_storage'] = 'granted';

        return $payload;
    }
}
