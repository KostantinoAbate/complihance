<?php

namespace KostantinoAbate\Complihance\DTO;

class ConsentRequestContext
{
    public function __construct(
        public readonly ?string $sessionId,
        public readonly ?string $anonymousId,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
        public readonly ?string $subjectType,
        public readonly mixed $subjectId,
        public readonly mixed $subject,
        public readonly bool $isSecure,
    ) {}
}
