<?php

namespace KostantinoAbate\Complihance\Data;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * Result returned after storing consent preferences.
 */
readonly class StoredConsentResult
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public array $payload,
        public Cookie $consentCookie,
        public Cookie $anonymousCookie,
    ) {}
}
