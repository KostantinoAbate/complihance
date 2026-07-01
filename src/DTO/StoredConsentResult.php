<?php

namespace KostantinoAbate\Complihance\DTO;

use Symfony\Component\HttpFoundation\Cookie;

class StoredConsentResult
{
    public function __construct(
        public readonly array $payload,
        public readonly Cookie $consentCookie,
        public readonly Cookie $anonymousCookie,
    ) {}
}
