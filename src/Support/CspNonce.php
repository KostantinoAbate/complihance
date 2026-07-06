<?php

namespace KostantinoAbate\Complihance\Support;

class CspNonce
{
    public static function value(): ?string
    {
        $resolver = config('complihance.csp_nonce_resolver');

        if (is_callable($resolver)) {
            return $resolver();
        }

        return config('complihance.csp_nonce');
    }

    public static function attribute(): string
    {
        $nonce = static::value();

        return $nonce ? ' nonce="'.e($nonce).'"' : '';
    }
}
