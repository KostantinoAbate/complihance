<?php

namespace KostantinoAbate\Complihance\Services\Consent;

use KostantinoAbate\Complihance\Services\Rendering\ComplihanceDataRepository;

class GranularConsent
{
    /**
     * Determine whether vendor-level consent is enabled.
     */
    public static function enabled(): bool
    {
        return (bool) config('complihance.granular_consent.enabled', false);
    }

    /**
     * Return configured vendors keyed by vendor key.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function vendors(): array
    {
        if (! self::enabled()) {
            return [];
        }

        return collect(
            app(ComplihanceDataRepository::class)->vendors()
        )
            ->keyBy('key')
            ->all();
    }
}
