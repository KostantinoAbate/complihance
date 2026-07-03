<?php

namespace KostantinoAbate\Complihance\Support;

use KostantinoAbate\Complihance\Services\ComplihanceDataRepository;

class GranularConsent
{
    public static function enabled(): bool
    {
        return (bool) config('complihance.granular_consent.enabled', false);
    }

    public static function vendors(): array
    {
        if (! self::enabled()) {
            return [];
        }

        return collect(
            app(ComplihanceDataRepository::class)
                ->vendors()
        )
            ->keyBy('key')
            ->all();
    }
}
