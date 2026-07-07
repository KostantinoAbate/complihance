<?php

namespace KostantinoAbate\Complihance\Support;

class CspNonce
{
    /**
     * Resolve the configured CSP nonce value.
     */
    public static function value(): ?string
    {
        $resolver = config('complihance.csp_nonce_resolver');

        if (is_callable($resolver)) {
            $nonce = $resolver();

            return is_string($nonce) && $nonce !== '' ? $nonce : null;
        }

        $nonce = config('complihance.csp_nonce');

        return is_string($nonce) && $nonce !== '' ? $nonce : null;
    }

    /**
     * Render the nonce attribute for inline script and style tags.
     */
    public static function attribute(): string
    {
        $nonce = static::value();

        return $nonce ? ' nonce="'.e($nonce).'"' : '';
    }
}
